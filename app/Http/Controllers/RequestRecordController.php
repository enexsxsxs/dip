<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RequestLayout;
use App\Models\RequestRecord;
use App\Models\Role;
use App\Models\User;
use App\Services\Reports\ReportDocumentRenderer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RequestRecordController extends Controller
{
    public function index(): View
    {
        $records = RequestRecord::query()
            ->with(['layout', 'author'])
            ->when($this->isSeniorNurseLimitedByRapports(), function (Builder $q): void {
                $q->whereHas('layout', function (Builder $layoutQ): void {
                    $this->applySeniorNurseRapportFilter($layoutQ);
                });
            })
            ->orderBy('registry_number')
            ->paginate(20);

        return view('report-requests.index', compact('records'));
    }

    public function create(): View
    {
        $layouts = $this->visibleLayoutsQueryForCurrentUser()->with('documentHeader')->orderBy('title')->get();
        $users = User::query()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get();
        $layoutsPayload = $this->layoutsPayload($layouts);
        $initialFormData = [];

        return view('report-requests.create', compact('layouts', 'users', 'layoutsPayload', 'initialFormData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'request_layout_id' => ['required', 'exists:request_layout,id'],
            'data' => ['required', 'string'],
            'recipient_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $decoded = json_decode($validated['data'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['data' => 'Некорректный JSON данных заявки.'])->withInput();
        }

        if (! empty($validated['recipient_user_id'])) {
            $decoded['recipient_user_id'] = (int) $validated['recipient_user_id'];
        }
        $layout = RequestLayout::withTrashed()->findOrFail((int) $validated['request_layout_id']);
        $this->ensureLayoutVisibleForCurrentUser($layout);

        $record = DB::transaction(function () use ($validated, $decoded, $request) {
            $next = 1 + (int) RequestRecord::withTrashed()->lockForUpdate()->max('registry_number');

            return RequestRecord::query()->create([
                'registry_number' => $next,
                'request_layout_id' => $validated['request_layout_id'],
                'data' => $decoded,
                'created_by' => $request->user()->id,
            ]);
        });

        $record->load('layout');
        ActivityLog::record(
            RequestRecord::class,
            $record->id,
            'created',
            $this->activityTitle($record),
            'Создана заявка по макету (PDF).',
        );

        return redirect()->route('report-requests.show', $record)->with('success', 'Заявка создана.');
    }

    public function show(RequestRecord $record): View
    {
        $record->load(['layout', 'author']);
        $this->ensureRecordVisibleForCurrentUser($record);

        $data = is_array($record->data) ? $record->data : [];
        $schema = $record->layout !== null ? $record->layout->effectiveSchema() : [];
        $layoutFields = is_array($schema['fields'] ?? null) ? $schema['fields'] : [];

        $recipientUser = null;
        if (! empty($data['recipient_user_id'])) {
            $recipientUser = User::query()->find((int) $data['recipient_user_id']);
        }

        $knownFieldIds = [];
        foreach ($layoutFields as $f) {
            if (! empty($f['id'])) {
                $knownFieldIds[(string) $f['id']] = true;
            }
        }

        $extraData = [];
        foreach ($data as $key => $value) {
            if ($key === 'recipient_user_id' || $key === 'header_overrides') {
                continue;
            }
            if (isset($knownFieldIds[(string) $key])) {
                continue;
            }
            $extraData[(string) $key] = $value;
        }
        ksort($extraData);

        $headerLineLabels = $this->headerOverrideLabelsFromSchema($schema);
        $headerOverrides = is_array($data['header_overrides'] ?? null) ? $data['header_overrides'] : [];

        return view('report-requests.show', compact(
            'record',
            'layoutFields',
            'data',
            'recipientUser',
            'extraData',
            'headerLineLabels',
            'headerOverrides',
        ));
    }

    public function edit(RequestRecord $record): View
    {
        $this->ensureRecordVisibleForCurrentUser($record);

        $layouts = $this->layoutsForEdit($record);
        $users = User::query()->where('is_active', true)->orderBy('last_name')->orderBy('first_name')->get();
        $layoutsPayload = $this->layoutsPayload($layouts);
        $initialFormData = $this->initialFormDataFromOldOrRecord($record);

        return view('report-requests.edit', compact('record', 'layouts', 'users', 'layoutsPayload', 'initialFormData'));
    }

    public function update(Request $request, RequestRecord $record): RedirectResponse
    {
        $validated = $request->validate([
            'request_layout_id' => ['required', 'exists:request_layout,id'],
            'data' => ['required', 'string'],
            'recipient_user_id' => ['nullable', 'exists:users,id'],
        ]);

        $decoded = json_decode($validated['data'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['data' => 'Некорректный JSON данных заявки.'])->withInput();
        }

        if (! empty($validated['recipient_user_id'])) {
            $decoded['recipient_user_id'] = (int) $validated['recipient_user_id'];
        } else {
            unset($decoded['recipient_user_id']);
        }
        $layout = RequestLayout::withTrashed()->findOrFail((int) $validated['request_layout_id']);
        $this->ensureLayoutVisibleForCurrentUser($layout);

        $wasTrashed = $record->trashed();
        if ($wasTrashed) {
            $record->restore();
            ActivityLog::record(
                RequestRecord::class,
                $record->id,
                'restored',
                $this->activityTitle($record),
                'Заявка по макету восстановлена при сохранении правок.',
            );
        }

        $record->update([
            'request_layout_id' => $validated['request_layout_id'],
            'data' => $decoded,
        ]);

        $record->load('layout');
        ActivityLog::record(
            RequestRecord::class,
            $record->id,
            'updated',
            $this->activityTitle($record),
            'Изменена заявка по макету (PDF).',
        );

        return redirect()->route('report-requests.show', $record)->with('success', 'Заявка обновлена.');
    }

    public function destroy(RequestRecord $record): RedirectResponse
    {
        $this->ensureRecordVisibleForCurrentUser($record);

        if ($record->trashed()) {
            return redirect()->route('report-requests.index')->with('error', 'Заявка уже скрыта из списка.');
        }

        $record->load('layout');
        $title = $this->activityTitle($record);
        $id = $record->id;
        $record->delete();

        ActivityLog::record(
            RequestRecord::class,
            $id,
            'deleted',
            $title,
            'Заявка по макету скрыта из списка; восстановить можно в разделе «Архив и журнал».',
        );

        return redirect()
            ->route('report-requests.index')
            ->with('deleted', 'Заявка скрыта из списка. Её можно восстановить или изменить в «Архив и журнал».');
    }

    public function pdf(RequestRecord $record, ReportDocumentRenderer $renderer): Response
    {
        $record->load(['layout', 'author']);
        $this->ensureRecordVisibleForCurrentUser($record);

        return $renderer->renderPdfResponse($record);
    }

    /**
     * @param  Collection<int, RequestLayout>  $layouts
     * @return list<array<string, mixed>>
     */
    private function layoutsPayload(Collection $layouts): array
    {
        return $layouts->map(function (RequestLayout $layout) {
            $schema = $layout->effectiveSchema();

            return [
                'id' => $layout->id,
                'title' => $layout->title,
                'has_header' => (bool) $layout->has_header,
                'header_editable_lines' => $this->editableHeaderLinesForPayload($schema),
                'fields' => is_array($schema['fields'] ?? null) ? $schema['fields'] : [],
                'body_default_font_family' => ($schema['body_default_font_family'] ?? '') === 'DejaVu Sans'
                    ? 'DejaVu Sans'
                    : 'DejaVu Serif',
                'body_default_font_size_pt' => is_numeric($schema['body_default_font_size_pt'] ?? null)
                    ? (int) round((float) $schema['body_default_font_size_pt'])
                    : 11,
                'body_line_height' => is_numeric($schema['body_line_height'] ?? null)
                    ? (float) $schema['body_line_height']
                    : 1.35,
            ];
        })->values()->all();
    }

    /**
     * Строки шапки макета, отмеченные как редактируемые в заявке (см. schema.header.sections[].lines).
     *
     * @param  array<string, mixed>  $schema
     * @return list<array{line_id: string, default_text: string, label: string}>
     */
    private function editableHeaderLinesForPayload(array $schema): array
    {
        $header = $schema['header'] ?? null;
        if (! is_array($header)) {
            return [];
        }
        $sections = $header['sections'] ?? null;
        if (! is_array($sections)) {
            return [];
        }
        $out = [];
        foreach ($sections as $si => $section) {
            if (! is_array($section)) {
                continue;
            }
            $lines = $section['lines'] ?? [];
            if (! is_array($lines)) {
                continue;
            }
            foreach ($lines as $li => $line) {
                if (! is_array($line)) {
                    continue;
                }
                if (empty($line['editable']) || empty($line['line_id'])) {
                    continue;
                }
                $out[] = [
                    'line_id' => (string) $line['line_id'],
                    'default_text' => $this->resolveEditableHeaderDefaultText($line),
                    'label' => 'Шапка: блок '.((int) $si + 1).', строка '.((int) $li + 1),
                ];
            }
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, string>
     */
    private function headerOverrideLabelsFromSchema(array $schema): array
    {
        $map = [];
        foreach ($this->editableHeaderLinesForPayload($schema) as $row) {
            $map[$row['line_id']] = $row['label'];
        }

        return $map;
    }

    /**
     * @return Collection<int, RequestLayout>
     */
    private function layoutsForEdit(RequestRecord $record): Collection
    {
        $active = $this->visibleLayoutsQueryForCurrentUser()->with('documentHeader')->orderBy('title')->get();
        $current = RequestLayout::withTrashed()->with('documentHeader')->find($record->request_layout_id);
        if ($current && $this->isLayoutVisibleForCurrentUser($current) && ! $active->contains('id', $current->id)) {
            return $active->concat([$current])->sortBy('title')->values();
        }

        return $active;
    }

    /**
     * @return array<string, mixed>
     */
    private function initialFormDataFromOldOrRecord(RequestRecord $record): array
    {
        $raw = old('data');
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? $decoded : ($record->data ?? []);
        }

        return is_array($record->data) ? $record->data : [];
    }

    private function activityTitle(RequestRecord $record): string
    {
        $layoutTitle = $record->layout?->title ?? '—';

        $no = $record->registry_number ?? $record->id;

        return 'Заявка №'.$no.' — '.$layoutTitle;
    }

    /**
     * @param  array<string, mixed>  $line
     */
    private function resolveEditableHeaderDefaultText(array $line): string
    {
        $roleKey = trim((string) ($line['role_key'] ?? ''));
        if ($roleKey !== '') {
            return $this->resolveSignerNameByRoleKey($roleKey);
        }

        $text = (string) ($line['text'] ?? '');
        if (preg_match('/^\{\{\s*role:([a-z0-9_]+)\s*\}\}$/i', trim($text), $m) === 1) {
            return $this->resolveSignerNameByRoleKey((string) ($m[1] ?? ''));
        }

        return $text;
    }

    private function resolveSignerNameByRoleKey(string $roleKey): string
    {
        $roleKey = trim($roleKey);
        if ($roleKey === '') {
            return '';
        }

        if ($roleKey === 'senior_nurse') {
            $u = auth()->user();
            if ($u !== null && $u->role === 'senior_nurse') {
                return (string) $u->name;
            }

            return (string) ($this->firstUserByRole('senior_nurse', true)?->name ?? '');
        }

        if ($roleKey === 'admin' || $roleKey === 'user') {
            return (string) ($this->firstUserByRole($roleKey, true)?->name ?? '');
        }

        if (in_array($roleKey, ['sign_chief_doctor', 'sign_writeoff_head', 'sign_move_head'], true)) {
            return (string) ($this->firstUserByRole($roleKey, false)?->name ?? '');
        }

        return '';
    }

    private function firstUserByRole(string $roleName, bool $onlyActive): ?User
    {
        $roleId = Role::query()->where('name', $roleName)->value('id');
        if ($roleId === null) {
            return null;
        }

        $q = User::query()->where('role_id', (int) $roleId);
        if ($onlyActive) {
            $q->where('is_active', true);
        }

        return $q->orderBy('last_name')->orderBy('first_name')->orderBy('patronymic')->first();
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

    private function isLayoutVisibleForCurrentUser(?RequestLayout $layout): bool
    {
        if ($layout === null || ! $this->isSeniorNurseLimitedByRapports()) {
            return $layout !== null;
        }
        $schema = is_array($layout->schema) ? $layout->schema : [];
        $body = (string) ($schema['body_html'] ?? '');
        if (str_contains($body, 'sys.writeoff_equipment_list') || str_contains($body, 'sys.move_equipment_list')) {
            return true;
        }
        $text = mb_strtolower(trim((string) $layout->title).' '.trim((string) ($schema['document_title'] ?? '')).' '.trim($body), 'UTF-8');

        return str_contains($text, 'списани') || str_contains($text, 'перемещ');
    }

    private function ensureLayoutVisibleForCurrentUser(RequestLayout $layout): void
    {
        if (! $this->isLayoutVisibleForCurrentUser($layout)) {
            abort(403, 'Старшей медсестре доступны только рапорты на списание и перемещение оборудования.');
        }
    }

    private function ensureRecordVisibleForCurrentUser(RequestRecord $record): void
    {
        if (! $this->isSeniorNurseLimitedByRapports()) {
            return;
        }
        $layout = $record->layout;
        if (! $layout instanceof RequestLayout) {
            $layout = RequestLayout::withTrashed()->find($record->request_layout_id);
        }
        if (! $this->isLayoutVisibleForCurrentUser($layout)) {
            abort(403, 'Старшей медсестре доступны только рапорты на списание и перемещение оборудования.');
        }
    }
}
