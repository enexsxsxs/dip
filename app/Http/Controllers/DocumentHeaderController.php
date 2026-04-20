<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\DocumentHeader;
use App\Models\RequestLayout;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentHeaderController extends Controller
{
    public function index(): View
    {
        $headers = DocumentHeader::query()
            ->orderByDesc('id')
            ->paginate(20);

        return view('document-headers.index', compact('headers'));
    }

    public function create(): View
    {
        $initialSchema = $this->initialSchemaFromOldInput() ?? $this->defaultHeaderSchema();
        $headerRoleSigners = $this->headerRoleSignersForForm();

        return view('document-headers.create', compact('initialSchema', 'headerRoleSigners'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
        }

        $header = DocumentHeader::query()->create([
            'title' => $validated['title'],
            'schema' => $decoded,
        ]);

        ActivityLog::record(
            DocumentHeader::class,
            $header->id,
            'created',
            $header->title,
            'Создан макет шапки документа (PDF).',
        );

        return redirect()->route('document-headers.index')->with('success', 'Макет шапки сохранён.');
    }

    public function edit(DocumentHeader $documentHeader): View
    {
        $initialSchema = $this->initialSchemaFromOldInput() ?? ($documentHeader->schema ?? []);
        $headerRoleSigners = $this->headerRoleSignersForForm();

        return view('document-headers.edit', [
            'header' => $documentHeader,
            'initialSchema' => is_array($initialSchema) ? $initialSchema : [],
            'headerRoleSigners' => $headerRoleSigners,
        ]);
    }

    public function update(Request $request, DocumentHeader $documentHeader): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'schema' => ['required', 'string'],
        ]);

        $decoded = json_decode($validated['schema'], true);
        if (! is_array($decoded)) {
            return back()->withErrors(['schema' => 'Некорректный JSON в поле schema.'])->withInput();
        }

        $wasTrashed = $documentHeader->trashed();
        if ($wasTrashed) {
            $documentHeader->restore();
            ActivityLog::record(
                DocumentHeader::class,
                $documentHeader->id,
                'restored',
                $documentHeader->title,
                'Макет шапки восстановлен при сохранении правок.',
            );
        }

        $documentHeader->update([
            'title' => $validated['title'],
            'schema' => $decoded,
        ]);

        ActivityLog::record(
            DocumentHeader::class,
            $documentHeader->id,
            'updated',
            $documentHeader->title,
            'Изменён макет шапки документа (PDF).',
        );

        return redirect()->route('document-headers.index')->with('success', 'Макет шапки обновлён.');
    }

    public function destroy(DocumentHeader $documentHeader): RedirectResponse
    {
        if ($documentHeader->trashed()) {
            return redirect()->route('document-headers.index')->with('error', 'Макет шапки уже скрыт из списка.');
        }

        if (RequestLayout::query()->where('document_header_id', $documentHeader->id)->exists()) {
            return redirect()
                ->route('document-headers.index')
                ->with('error', 'Этот макет шапки используется в макетах заявок. Сначала отвяжите его или выберите другую шапку в макетах.');
        }

        $title = $documentHeader->title;
        $id = $documentHeader->id;
        $documentHeader->delete();

        ActivityLog::record(
            DocumentHeader::class,
            $id,
            'deleted',
            $title,
            'Макет шапки скрыт из списка; восстановить можно в разделе «Архив и журнал».',
        );

        return redirect()
            ->route('document-headers.index')
            ->with('deleted', 'Макет шапки скрыт из списка. Его можно восстановить в «Архив и журнал».');
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
     * @return array<string, mixed>
     */
    private function defaultHeaderSchema(): array
    {
        return [
            'sections' => [
                [
                    'align' => 'center',
                    'bold' => true,
                    'font_family' => 'DejaVu Serif',
                    'font_size_pt' => 11,
                    'lines' => [''],
                ],
            ],
        ];
    }

    /**
     * Имена подписантов для автоподстановки в шапке по role-токенам.
     *
     * @return array{sign_chief_doctor: string, sign_writeoff_head: string, sign_move_head: string, senior_nurse: string, admin: string, user: string}
     */
    private function headerRoleSignersForForm(): array
    {
        $pick = function (string $roleName, bool $onlyActive = true): string {
            $roleId = Role::query()->where('name', $roleName)->value('id');
            if ($roleId === null) {
                return '';
            }
            $q = User::query()->where('role_id', (int) $roleId);
            if ($onlyActive) {
                $q->where('is_active', true);
            }
            $u = $q->orderBy('last_name')->orderBy('first_name')->orderBy('patronymic')->first();

            return (string) ($u?->name ?? '');
        };

        return [
            'sign_chief_doctor' => $pick('sign_chief_doctor', false),
            'sign_writeoff_head' => $pick('sign_writeoff_head', false),
            'sign_move_head' => $pick('sign_move_head', false),
            'senior_nurse' => $pick('senior_nurse', true),
            'admin' => $pick('admin', true),
            'user' => $pick('user', true),
        ];
    }
}
