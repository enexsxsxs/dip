<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\RequestLayout;
use App\Models\RequestRecord;
use App\Models\User;
use App\Services\Reports\ReportDocumentRenderer;
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
            ->orderBy('registry_number')
            ->paginate(20);

        return view('report-requests.index', compact('records'));
    }

    public function create(): View
    {
        $layouts = RequestLayout::query()->orderBy('title')->get();
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

        $data = is_array($record->data) ? $record->data : [];
        $schema = is_array($record->layout?->schema) ? $record->layout->schema : [];
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

        return $renderer->renderPdfResponse($record);
    }

    /**
     * @param  Collection<int, RequestLayout>  $layouts
     * @return list<array<string, mixed>>
     */
    private function layoutsPayload(Collection $layouts): array
    {
        return $layouts->map(function (RequestLayout $layout) {
            $schema = $layout->schema ?? [];

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
                    'default_text' => (string) ($line['text'] ?? ''),
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
        $active = RequestLayout::query()->orderBy('title')->get();
        $current = RequestLayout::withTrashed()->find($record->request_layout_id);
        if ($current && ! $active->contains('id', $current->id)) {
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
}
