<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentHeader;
use App\Models\RequestLayout;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestLayoutController extends Controller
{
    public function index(): View
    {
        $layouts = $this->visibleLayoutsQueryForCurrentUser()
            ->with('documentHeader')
            ->orderByDesc('id')
            ->paginate(20);

        return view('report-layouts.index', compact('layouts'));
    }

    public function create(): View
    {
        $initialSchema = $this->initialSchemaFromOldInput() ?? $this->defaultLayoutSchema();
        $documentHeaders = $this->documentHeadersForSelect();
        $initialDocumentHeaderId = old('document_header_id');
        $footerPickUsers = $this->footerPickUsersForLayoutForm();

        return view('report-layouts.create', compact('initialSchema', 'documentHeaders', 'initialDocumentHeaderId', 'footerPickUsers'));
    }

    /**
     * Фрагмент schema.header (в т.ч. из привязанного макета шапки).
     */
    public function headerJson(RequestLayout $layout): JsonResponse
    {
        $this->ensureLayoutVisibleForCurrentUser($layout);

        $schema = $layout->effectiveSchema();
        $header = (isset($schema['header']) && is_array($schema['header'])) ? $schema['header'] : [];

        return response()->json(['header' => $header]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'has_header' => ['sometimes', 'boolean'],
            'document_header_id' => ['nullable', 'exists:document_headers,id'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
        }
        if ($this->isSeniorNurseLimitedByRapports() && ! $this->isRapportLayoutForSeniorNurse($validated['title'], $decoded)) {
            return back()->withErrors(['title' => 'Старшая медсестра может создавать только рапорты на списание или перемещение оборудования.'])->withInput();
        }

        if ($response = $this->validateLayoutHeader($request, $decoded)) {
            return $response;
        }

        $this->normalizeLayoutSchemaAfterSave($request, $decoded);

        $docHeaderId = null;
        if ($request->boolean('has_header') && $request->filled('document_header_id')) {
            $docHeaderId = (int) $request->input('document_header_id');
        }

        $layout = RequestLayout::query()->create([
            'title' => $validated['title'],
            'has_header' => $request->boolean('has_header'),
            'document_header_id' => $docHeaderId,
            'schema' => $decoded,
            'type' => 'pdf',
        ]);

        ActivityLog::record(
            RequestLayout::class,
            $layout->id,
            'created',
            $layout->title,
            'Создан макет заявки (PDF).',
        );

        return redirect()->route('report-layouts.index')->with('success', 'Макет сохранён.');
    }

    public function edit(RequestLayout $layout): View
    {
        $this->ensureLayoutVisibleForCurrentUser($layout);

        $initialSchema = $this->initialSchemaFromOldInput() ?? ($layout->schema ?? []);
        if (is_array($initialSchema) && $layout->document_header_id) {
            unset($initialSchema['header']);
        }
        $documentHeaders = $this->documentHeadersForSelect();
        $initialDocumentHeaderId = old('document_header_id', $layout->document_header_id);
        $footerPickUsers = $this->footerPickUsersForLayoutForm();

        return view('report-layouts.edit', [
            'layout' => $layout,
            'initialSchema' => is_array($initialSchema) ? $initialSchema : [],
            'documentHeaders' => $documentHeaders,
            'initialDocumentHeaderId' => $initialDocumentHeaderId,
            'footerPickUsers' => $footerPickUsers,
        ]);
    }

    public function update(Request $request, RequestLayout $layout): RedirectResponse
    {
        $this->ensureLayoutVisibleForCurrentUser($layout);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'has_header' => ['sometimes', 'boolean'],
            'document_header_id' => ['nullable', 'exists:document_headers,id'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
        }
        if ($this->isSeniorNurseLimitedByRapports() && ! $this->isRapportLayoutForSeniorNurse($validated['title'], $decoded)) {
            return back()->withErrors(['title' => 'Старшая медсестра может сохранять только рапорты на списание или перемещение оборудования.'])->withInput();
        }

        if ($response = $this->validateLayoutHeader($request, $decoded)) {
            return $response;
        }

        $this->normalizeLayoutSchemaAfterSave($request, $decoded);

        $docHeaderId = null;
        if ($request->boolean('has_header') && $request->filled('document_header_id')) {
            $docHeaderId = (int) $request->input('document_header_id');
        }

        $wasTrashed = $layout->trashed();
        if ($wasTrashed) {
            $layout->restore();
            ActivityLog::record(
                RequestLayout::class,
                $layout->id,
                'restored',
                $layout->title,
                'Макет заявки восстановлен при сохранении правок.',
            );
        }

        $layout->update([
            'title' => $validated['title'],
            'has_header' => $request->boolean('has_header'),
            'document_header_id' => $docHeaderId,
            'schema' => $decoded,
        ]);

        ActivityLog::record(
            RequestLayout::class,
            $layout->id,
            'updated',
            $layout->title,
            'Изменён макет заявки (PDF).',
        );

        return redirect()->route('report-layouts.index')->with('success', 'Макет обновлён.');
    }

    public function destroy(RequestLayout $layout): RedirectResponse
    {
        $this->ensureLayoutVisibleForCurrentUser($layout);

        if ($layout->trashed()) {
            return redirect()->route('report-layouts.index')->with('error', 'Макет уже скрыт из списка.');
        }

        $title = $layout->title;
        $id = $layout->id;
        $layout->delete();

        ActivityLog::record(
            RequestLayout::class,
            $id,
            'deleted',
            $title,
            'Макет заявки скрыт из списка; восстановить можно в разделе «Архив и журнал».',
        );

        return redirect()
            ->route('report-layouts.index')
            ->with('deleted', 'Макет скрыт из списка. Его можно восстановить или отредактировать в «Архив и журнал».');
    }

    /**
     * @return array<string, mixed>|null
     */
    private function initialSchemaFromOldInput(): ?array
    {
        $raw = old('schema');
        if (! is_string($raw) || $raw === '') {
            return null;
        }
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : null;
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function validateLayoutHeader(Request $request, array $decoded): ?RedirectResponse
    {
        if (! $request->boolean('has_header')) {
            return null;
        }
        if ($request->filled('document_header_id')) {
            return null;
        }
        if ($this->schemaHasEmbeddedHeader($decoded)) {
            return null;
        }

        return back()->withErrors([
            'document_header_id' => 'Выберите макет шапки из списка или отключите опцию «Нужна шапка заявления».',
        ])->withInput();
    }

    /**
     * @param  array<string, mixed>  $decoded
     */
    private function normalizeLayoutSchemaAfterSave(Request $request, array &$decoded): void
    {
        if (! $request->boolean('has_header')) {
            unset($decoded['header']);

            return;
        }
        if ($request->filled('document_header_id')) {
            unset($decoded['header']);
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaHasEmbeddedHeader(array $schema): bool
    {
        $h = $schema['header'] ?? null;
        if (! is_array($h)) {
            return false;
        }
        if (! empty($h['sections']) && is_array($h['sections'])) {
            return true;
        }
        if (! empty($h['blocks']) && is_array($h['blocks'])) {
            return true;
        }

        return false;
    }

    /**
     * @return list<array{id: int, title: string}>
     */
    private function documentHeadersForSelect(): array
    {
        return DocumentHeader::query()
            ->orderBy('title')
            ->get(['id', 'title'])
            ->map(fn (DocumentHeader $h) => [
                'id' => (int) $h->id,
                'title' => (string) $h->title,
            ])
            ->values()
            ->all();
    }

    /**
     * Пользователи для выбора подписантов в подвале PDF.
     * Заведующая: роль «Пользователь» (только активные) + подписанты отделения (списание/перемещение), в т.ч. неактивные учётки без входа.
     * Инженер: роль «Администратор» (активные).
     * В БД нет столбца users.name — ФИО в частях; для подписи используется аксессор {@see User::getNameAttribute()}.
     *
     * @return array{head: list<array{id: int, name: string}>, engineer: list<array{id: int, name: string}>}
     */
    private function footerPickUsersForLayoutForm(): array
    {
        $pickForRole = function (?int $roleId, bool $onlyActive): array {
            if ($roleId === null) {
                return [];
            }
            $q = User::query()
                ->where('role_id', $roleId)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('patronymic');

            if ($onlyActive) {
                $q->where('is_active', true);
            }

            return $q->get(['id', 'last_name', 'first_name', 'patronymic'])
                ->map(fn (User $u) => [
                    'id' => (int) $u->id,
                    'name' => (string) $u->name,
                ])
                ->values()
                ->all();
        };

        $userRoleId = Role::query()->where('name', 'user')->value('id');
        $writeoffHeadRoleId = Role::query()->where('name', 'sign_writeoff_head')->value('id');
        $moveHeadRoleId = Role::query()->where('name', 'sign_move_head')->value('id');
        $adminRoleId = Role::query()->where('name', 'admin')->value('id');

        $headUsers = $pickForRole($userRoleId !== null ? (int) $userRoleId : null, true);
        $headSigners = array_merge(
            $pickForRole($writeoffHeadRoleId !== null ? (int) $writeoffHeadRoleId : null, false),
            $pickForRole($moveHeadRoleId !== null ? (int) $moveHeadRoleId : null, false),
        );
        $headById = [];
        foreach (array_merge($headUsers, $headSigners) as $row) {
            $headById[$row['id']] = $row;
        }
        $headMerged = array_values($headById);
        usort($headMerged, function (array $a, array $b): int {
            return strcmp((string) $a['name'], (string) $b['name']);
        });

        return [
            'head' => $headMerged,
            'engineer' => $pickForRole($adminRoleId !== null ? (int) $adminRoleId : null, true),
        ];
    }

    private function isSeniorNurseLimitedByRapports(): bool
    {
        return auth()->user()?->role === 'senior_nurse';
    }

    private function visibleLayoutsQueryForCurrentUser(): Builder
    {
        $q = RequestLayout::query();
        if ($this->isSeniorNurseLimitedByRapports()) {
            $this->applySeniorNurseRapportFilter($q);
        }

        return $q;
    }

    private function applySeniorNurseRapportFilter(Builder $q): void
    {
        $q->where(function (Builder $sub): void {
            $sub->where('title', 'like', '%списан%')
                ->orWhere('title', 'like', '%перемещ%')
                ->orWhere('schema', 'like', '%sys.writeoff_equipment_list%')
                ->orWhere('schema', 'like', '%sys.move_equipment_list%');
        });
    }

    private function ensureLayoutVisibleForCurrentUser(RequestLayout $layout): void
    {
        if (! $this->isSeniorNurseLimitedByRapports()) {
            return;
        }
        $schema = is_array($layout->schema) ? $layout->schema : [];
        if (! $this->isRapportLayoutForSeniorNurse((string) $layout->title, $schema)) {
            abort(403, 'Старшей медсестре доступны только рапорты на списание и перемещение оборудования.');
        }
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function isRapportLayoutForSeniorNurse(string $title, array $schema): bool
    {
        $body = (string) ($schema['body_html'] ?? '');
        if (str_contains($body, 'sys.writeoff_equipment_list') || str_contains($body, 'sys.move_equipment_list')) {
            return true;
        }

        $text = mb_strtolower(trim($title).' '.trim((string) ($schema['document_title'] ?? '')).' '.trim($body), 'UTF-8');

        return str_contains($text, 'списани') || str_contains($text, 'перемещ');
    }

    /**
     * Без встроенной шапки — её выбирают отдельным макетом {@see DocumentHeader}.
     *
     * @return array<string, mixed>
     */
    private function defaultLayoutSchema(): array
    {
        return [
            'fields' => [],
            'document_title' => '',
            'document_subtitle' => '',
            'document_title_font_size_pt' => 18,
            'document_subtitle_font_size_pt' => 12,
            'body_default_font_family' => 'DejaVu Serif',
            'body_default_font_size_pt' => 11,
            'body_line_height' => 1.35,
            'body_html' => '<p>Текст заявки… Вставьте поля кнопками ниже.</p>',
            'pdf_footer' => [
                'style' => 'legacy',
            ],
        ];
    }
}
