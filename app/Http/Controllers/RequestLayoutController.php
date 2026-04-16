<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RequestLayout;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RequestLayoutController extends Controller
{
    public function index(): View
    {
        $layouts = RequestLayout::query()
            ->orderByDesc('id')
            ->paginate(20);

        return view('report-layouts.index', compact('layouts'));
    }

    public function create(): View
    {
        $initialSchema = $this->initialSchemaFromOldInput() ?? $this->defaultLayoutSchema();
        $headerSourceLayouts = $this->headerSourceLayoutsList();
        $footerPickUsers = $this->footerPickUsersForLayoutForm();

        return view('report-layouts.create', compact('initialSchema', 'headerSourceLayouts', 'footerPickUsers'));
    }

    /**
     * Фрагмент schema.header для подстановки шапки из другого макета в редакторе.
     */
    public function headerJson(RequestLayout $layout): JsonResponse
    {
        $schema = is_array($layout->schema) ? $layout->schema : [];
        $header = (isset($schema['header']) && is_array($schema['header'])) ? $schema['header'] : [];

        return response()->json(['header' => $header]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'has_header' => ['sometimes', 'boolean'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
        }

        $layout = RequestLayout::query()->create([
            'title' => $validated['title'],
            'has_header' => $request->boolean('has_header'),
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
        $initialSchema = $this->initialSchemaFromOldInput() ?? ($layout->schema ?? []);
        $headerSourceLayouts = $this->headerSourceLayoutsList(excludeId: $layout->id);
        $footerPickUsers = $this->footerPickUsersForLayoutForm();

        return view('report-layouts.edit', [
            'layout' => $layout,
            'initialSchema' => is_array($initialSchema) ? $initialSchema : [],
            'headerSourceLayouts' => $headerSourceLayouts,
            'footerPickUsers' => $footerPickUsers,
        ]);
    }

    public function update(Request $request, RequestLayout $layout): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'has_header' => ['sometimes', 'boolean'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
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
     * Активные пользователи для выбора подписантов в подвале PDF (роли user и admin).
     * В БД нет столбца users.name — ФИО в частях; для подписи используется аксессор {@see User::getNameAttribute()}.
     *
     * @return array{head: list<array{id: int, name: string}>, engineer: list<array{id: int, name: string}>}
     */
    private function footerPickUsersForLayoutForm(): array
    {
        $pick = function (?int $roleId): array {
            if ($roleId === null) {
                return [];
            }

            return User::query()
                ->where('is_active', true)
                ->where('role_id', $roleId)
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->orderBy('patronymic')
                ->get(['id', 'last_name', 'first_name', 'patronymic'])
                ->map(fn (User $u) => [
                    'id' => (int) $u->id,
                    'name' => (string) $u->name,
                ])
                ->values()
                ->all();
        };

        $userRoleId = Role::query()->where('name', 'user')->value('id');
        $adminRoleId = Role::query()->where('name', 'admin')->value('id');

        return [
            'head' => $pick($userRoleId !== null ? (int) $userRoleId : null),
            'engineer' => $pick($adminRoleId !== null ? (int) $adminRoleId : null),
        ];
    }

    /**
     * @return list<array{id: int, title: string}>
     */
    private function headerSourceLayoutsList(?int $excludeId = null): array
    {
        $q = RequestLayout::query()->orderBy('title');
        if ($excludeId !== null) {
            $q->whereKeyNot($excludeId);
        }

        return $q->get(['id', 'title'])
            ->map(fn (RequestLayout $l) => [
                'id' => (int) $l->id,
                'title' => (string) $l->title,
            ])
            ->values()
            ->all();
    }

    private function defaultLayoutSchema(): array
    {
        return [
            'header' => [
                'sections' => [
                    [
                        'align' => 'center',
                        'bold' => true,
                        'font_family' => 'DejaVu Serif',
                        'font_size_pt' => 11,
                        'lines' => [
                            'ФЕДЕРАЛЬНОЕ АГЕНТСТВО',
                            'ПО ТЕХНИЧЕСКОМУ РЕГУЛИРОВАНИЮ И МЕТРОЛОГИИ',
                        ],
                    ],
                    [
                        'align' => 'center',
                        'bold' => true,
                        'font_family' => 'DejaVu Serif',
                        'font_size_pt' => 11,
                        'lines' => [
                            'ФБУ «Государственный региональный центр стандартизации,',
                            'метрологии и испытаний в Иркутской области»',
                        ],
                    ],
                    [
                        'align' => 'center',
                        'bold' => true,
                        'font_family' => 'DejaVu Serif',
                        'font_size_pt' => 14,
                        'lines' => [
                            'АКТ',
                            'контроля технического состояния',
                            'изделий медицинской техники',
                        ],
                    ],
                ],
            ],
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
