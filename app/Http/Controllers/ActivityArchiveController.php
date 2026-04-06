<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentDocument;
use App\Models\EquipmentDocumentType;
use App\Models\EquipmentImage;
use App\Models\EquipmentRequest;
use App\Models\EquipmentType;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ActivityArchiveController extends Controller
{
    /** Допустимые значения фильтра «тип объекта» (полный класс модели). */
    private const FILTER_ENTITY_TYPES = [
        Equipment::class,
        User::class,
        EquipmentRequest::class,
        EquipmentType::class,
        Department::class,
        Cabinet::class,
    ];

    /** Допустимые значения фильтра «действие». */
    private const FILTER_ACTIONS = [
        'created',
        'updated',
        'deleted',
        'restored',
        'deactivated',
        'rejected',
        'writeoff_approved',
        'move_approved',
        'revision_restored',
    ];

    /**
     * Журнал действий сотрудников и архивы (для администратора).
     */
    public function index(Request $request): View
    {
        $query = ActivityLog::query()->with('user');
        $this->applyArchiveFilters($query, $request);

        $entries = $query
            ->orderByDesc('occurred_at')
            ->orderByDesc('id')
            ->paginate(40)
            ->withQueryString();

        $trashedEquipment = Equipment::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get(['id', 'number', 'name', 'deleted_at']);

        $trashedEquipmentTypes = EquipmentType::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get(['id', 'name', 'deleted_at']);

        $trashedDepartments = Department::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get(['id', 'name', 'deleted_at']);

        $trashedCabinets = Cabinet::onlyTrashed()
            ->orderByDesc('deleted_at')
            ->get(['id', 'number', 'deleted_at']);

        $inactiveUsers = User::query()
            ->where('is_active', false)
            ->with('roleModel')
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->get();

        $userIds = ActivityLog::query()
            ->whereNotNull('user_id')
            ->distinct()
            ->pluck('user_id');

        $usersForFilter = User::query()
            ->whereIn('id', $userIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $entityLabels = [
            Equipment::class => 'Оборудование',
            User::class => 'Пользователь',
            EquipmentRequest::class => 'Заявка',
            EquipmentType::class => 'Вид оборудования',
            Department::class => 'Отдел',
            Cabinet::class => 'Кабинет',
        ];

        $actionLabels = [
            'created' => 'Создано',
            'updated' => 'Изменено',
            'deleted' => 'Удалено',
            'restored' => 'Восстановлено',
            'deactivated' => 'Уволен',
            'rejected' => 'Отклонено',
            'writeoff_approved' => 'Списание подтверждено',
            'move_approved' => 'Перемещение подтверждено',
            'revision_restored' => 'Восстановлена версия',
        ];

        $filteredCount = null;
        if ($this->archiveFiltersActive($request)) {
            $countQuery = ActivityLog::query();
            $this->applyArchiveFilters($countQuery, $request);
            $filteredCount = $countQuery->count();
        }

        return view('admin.activity-archive', [
            'entries' => $entries,
            'trashedEquipment' => $trashedEquipment,
            'trashedEquipmentTypes' => $trashedEquipmentTypes,
            'trashedDepartments' => $trashedDepartments,
            'trashedCabinets' => $trashedCabinets,
            'inactiveUsers' => $inactiveUsers,
            'usersForFilter' => $usersForFilter,
            'entityLabels' => $entityLabels,
            'actionLabels' => $actionLabels,
            'filteredCount' => $filteredCount,
        ]);
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_clear' => ['required', 'in:ОЧИСТИТЬ'],
        ], [
            'confirm_clear.in' => 'Введите слово ОЧИСТИТЬ заглавными буквами для подтверждения.',
        ]);

        ActivityLog::query()->delete();

        return redirect()
            ->route('admin.activity-archive')
            ->with('status', 'Весь журнал действий очищен.');
    }

    /**
     * Удалить все записи журнала, попадающие под текущие фильтры (как на странице поиска).
     */
    public function clearFiltered(Request $request): RedirectResponse
    {
        $request->validate([
            'confirm_clear_filtered' => ['required', 'in:ОЧИСТИТЬ'],
        ], [
            'confirm_clear_filtered.in' => 'Введите слово ОЧИСТИТЬ заглавными буквами.',
        ]);

        if (! $this->archiveFiltersActive($request)) {
            return redirect()
                ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
                ->with('error', 'Задайте хотя бы один фильтр, чтобы очистить выборку.');
        }

        $query = ActivityLog::query();
        $this->applyArchiveFilters($query, $request);
        $deleted = $query->delete();

        return redirect()
            ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
            ->with('status', 'Удалено записей журнала: '.$deleted.'.');
    }

    /**
     * Удалить выбранные по идентификаторам строки журнала.
     */
    public function deleteSelected(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:activity_logs,id'],
        ], [
            'ids.required' => 'Отметьте хотя бы одну запись.',
        ]);

        $deleted = ActivityLog::query()->whereIn('id', $request->input('ids'))->delete();

        return redirect()
            ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
            ->with('status', 'Удалено записей: '.$deleted.'.');
    }

    public function restore(int $id): RedirectResponse
    {
        $equipment = Equipment::onlyTrashed()->findOrFail($id);

        $equipment->restore();

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'restored',
            '№'.$equipment->number.' — '.$equipment->name,
            'Восстановление оборудования из архива удалений.',
        );

        return redirect()
            ->route('equipment.show', $equipment)
            ->with('success', 'Оборудование снова в общем списке.');
    }

    public function restoreEquipmentType(int $id): RedirectResponse
    {
        $type = EquipmentType::onlyTrashed()->findOrFail($id);
        $type->restore();

        ActivityLog::record(
            EquipmentType::class,
            $type->id,
            'restored',
            $type->name,
            'Восстановлен вид оборудования из архива справочников.',
        );

        return redirect()
            ->route('admin.activity-archive')
            ->with('status', 'Вид оборудования снова доступен в справочнике.');
    }

    public function restoreDepartment(int $id): RedirectResponse
    {
        $department = Department::onlyTrashed()->findOrFail($id);
        $department->restore();

        ActivityLog::record(
            Department::class,
            $department->id,
            'restored',
            $department->name,
            'Восстановлен отдел из архива справочников.',
        );

        return redirect()
            ->route('admin.activity-archive')
            ->with('status', 'Отдел снова доступен в справочнике.');
    }

    public function restoreCabinet(int $id): RedirectResponse
    {
        $cabinet = Cabinet::onlyTrashed()->findOrFail($id);
        $cabinet->restore();

        $title = 'Кабинет №'.$cabinet->number;
        ActivityLog::record(
            Cabinet::class,
            $cabinet->id,
            'restored',
            $title,
            'Восстановлен кабинет из архива справочников.',
        );

        return redirect()
            ->route('admin.activity-archive')
            ->with('status', 'Кабинет/помещение снова доступно в справочнике.');
    }

    /**
     * Восстановить карточку оборудования (поля, фото, документы) по снимку из записи «Изменено».
     */
    public function restoreRevision(Request $request, ActivityLog $activityLog): RedirectResponse
    {
        if ($activityLog->entity_type !== Equipment::class
            || $activityLog->action !== 'updated'
            || empty($activityLog->snapshot)
            || $activityLog->entity_id === null) {
            return redirect()
                ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
                ->with('error', 'Для этой записи нет восстанавливаемого снимка.');
        }

        $payload = json_decode($activityLog->snapshot, true);
        if (! is_array($payload) || empty($payload['equipment']) || ! is_array($payload['equipment'])) {
            return redirect()
                ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
                ->with('error', 'Снимок в журнале повреждён или устарел.');
        }

        $equipmentRow = $payload['equipment'];
        if (isset($equipmentRow['id']) && (int) $equipmentRow['id'] !== (int) $activityLog->entity_id) {
            return redirect()
                ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
                ->with('error', 'Запись журнала не соответствует карточке оборудования.');
        }

        $equipment = Equipment::query()->find($activityLog->entity_id);
        if (! $equipment || $equipment->trashed()) {
            return redirect()
                ->route('admin.activity-archive', $request->only($this->filterQueryKeys()))
                ->with('error', 'Карточка недоступна (удалена из списка). Сначала восстановите оборудование из архива удалений.');
        }

        $warning = null;

        DB::transaction(function () use ($equipment, $payload, $activityLog, &$warning) {
            $fillKeys = array_flip($equipment->getFillable());
            $attrs = array_intersect_key($payload['equipment'], $fillKeys);
            $equipment->fill($attrs);
            $equipment->save();

            $equipment->images()->get()->each(function (EquipmentImage $img) {
                Storage::disk('public')->delete($img->image);
                $img->delete();
            });
            foreach ($payload['images'] ?? [] as $item) {
                $path = is_array($item) ? ($item['path'] ?? '') : '';
                if ($path === '' || ! Storage::disk('public')->exists($path)) {
                    continue;
                }
                $equipment->images()->create(['image' => $path]);
            }

            $equipment->documents()->get()->each(function (EquipmentDocument $doc) {
                Storage::disk('public')->delete($doc->document);
                $doc->delete();
            });
            foreach ($payload['documents'] ?? [] as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $path = $item['path'] ?? '';
                if ($path === '' || ! Storage::disk('public')->exists($path)) {
                    continue;
                }
                $uploaded = $item['uploaded_at'] ?? null;
                $docTypeId = ! empty($item['document_type_id'])
                    ? (int) $item['document_type_id']
                    : EquipmentDocumentType::idForCode((string) ($item['type'] ?? 'instruction'));
                if ($docTypeId === null || $docTypeId === 0) {
                    continue;
                }
                $equipment->documents()->create([
                    'document' => $path,
                    'name' => $item['name'] ?? 'Документ',
                    'document_type_id' => $docTypeId,
                    'uploaded_at' => $uploaded ? Carbon::parse($uploaded) : now(),
                ]);
            }

            if ($equipment->images()->count() === 0) {
                $warning = 'Фото в снимке отсутствуют или файлы не найдены — откройте редактирование и добавьте от 1 до 5 фотографий.';
            }

            $when = $activityLog->occurred_at?->format('d.m.Y H:i') ?? '—';
            ActivityLog::record(
                Equipment::class,
                $equipment->id,
                'revision_restored',
                '№'.$equipment->number.' — '.$equipment->name,
                'Восстановление версии карточки по записи журнала №'.$activityLog->id.' от '.$when.'.',
            );
        });

        $equipment->refresh();

        $redirect = redirect()
            ->route('equipment.show', $equipment)
            ->with('success', 'Карточка возвращена к состоянию из выбранной записи журнала (поля, фото и документы по снимку).');

        if ($warning !== null) {
            $redirect->with('warning', $warning);
        }

        return $redirect;
    }

    /**
     * @return list<string>
     */
    private function filterQueryKeys(): array
    {
        return ['q', 'entity_type', 'action', 'user_id', 'date_from', 'date_to'];
    }

    private function archiveFiltersActive(Request $request): bool
    {
        if (trim((string) $request->input('q', '')) !== '') {
            return true;
        }
        $entityType = $request->input('entity_type');
        if (is_string($entityType) && $entityType !== '' && in_array($entityType, self::FILTER_ENTITY_TYPES, true)) {
            return true;
        }
        $action = $request->input('action');
        if (is_string($action) && $action !== '' && in_array($action, self::FILTER_ACTIONS, true)) {
            return true;
        }
        if ($request->filled('user_id')) {
            return true;
        }
        if ($request->filled('date_from') || $request->filled('date_to')) {
            return true;
        }

        return false;
    }

    private function applyArchiveFilters(Builder $query, Request $request): void
    {
        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('details', 'like', $term);
            });
        }

        $entityType = $request->input('entity_type');
        if (is_string($entityType) && $entityType !== '' && in_array($entityType, self::FILTER_ENTITY_TYPES, true)) {
            $query->where('entity_type', $entityType);
        }

        $action = $request->input('action');
        if (is_string($action) && $action !== '' && in_array($action, self::FILTER_ACTIONS, true)) {
            $query->where('action', $action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', (int) $request->input('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->input('date_to'));
        }
    }
}
