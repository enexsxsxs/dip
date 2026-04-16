<?php

namespace App\Http\Controllers;

use App\Models\Cabinet;
use App\Models\Department;
use App\Models\Equipment;
use App\Models\EquipmentCondition;
use App\Models\EquipmentType;
use App\Models\Group;
use App\Models\ServiceOrganization;
use App\Models\EquipmentDocument;
use App\Models\EquipmentDocumentType;
use App\Models\ActivityLog;
use App\Models\Supplier;
use App\Models\UtilizationState;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    private const SORTABLE_COLUMNS = [
        'number' => 'equipment.number',
        'name' => 'equipment.name',
        'serial_number' => 'equipment.serial_number',
        'production_date' => 'equipment.production_date',
        'year_of_manufacture' => 'equipment.year_of_manufacture',
        'date_accepted_to_accounting' => 'equipment.date_accepted_to_accounting',
        'inventory_number' => 'equipment.inventory_number',
        'ru_number' => 'equipment.ru_number',
        'ru_date' => 'equipment.ru_date',
        'grsi' => 'equipment.grsi',
        'equipment_type_name' => 'equipment_types.name',
        'department_name' => 'departments.name',
        'cabinet_number' => 'cabinets.number',
        'group_name' => 'groups.name',
        'condition_name' => 'equipment_conditions.name',
    ];

    /**
     * Список оборудования с поиском, сортировкой и фильтрами по столбцам.
     */
    public function index(Request $request): View
    {
        $search = $request->input('search', '');
        $sortBy = $request->input('sort_by', 'number');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $filterEtype = $request->input('filter_etype', []);
        $filterDepartment = $request->input('filter_department', []);
        $filterCabinet = $request->input('filter_cabinet', []);
        $filterGroup = $request->input('filter_group', []);
        $filterCondition = $request->input('filter_condition', []);
        if (!is_array($filterEtype)) {
            $filterEtype = $filterEtype ? explode(',', $filterEtype) : [];
        }
        if (!is_array($filterDepartment)) {
            $filterDepartment = $filterDepartment ? explode(',', $filterDepartment) : [];
        }
        if (!is_array($filterCabinet)) {
            $filterCabinet = $filterCabinet ? explode(',', $filterCabinet) : [];
        }
        if (!is_array($filterGroup)) {
            $filterGroup = $filterGroup ? explode(',', $filterGroup) : [];
        }
        if (!is_array($filterCondition)) {
            $filterCondition = $filterCondition ? explode(',', $filterCondition) : [];
        }

        $filterName = $request->input('filter_name', '');
        $filterSerial = $request->input('filter_serial', '');
        $filterYear = $request->input('filter_year', '');
        $filterInventory = $request->input('filter_inventory', '');
        $filterRu = $request->input('filter_ru', '');
        $filterGrsi = $request->input('filter_grsi', '');

        $query = Equipment::query()
            ->from('equipment as equipment')
            ->select('equipment.*')
            ->with(['department', 'cabinet', 'group', 'equipmentType', 'equipmentCondition', 'writeoffState', 'utilizationState']);

        if ($search !== '') {
            $term = '%' . trim($search) . '%';
            $query->where(function ($q) use ($term) {
                $q->where('equipment.name', 'like', $term)
                    ->orWhere('equipment.inventory_number', 'like', $term)
                    ->orWhere('equipment.serial_number', 'like', $term)
                    ->orWhere('equipment.number', 'like', $term)
                    ->orWhere('equipment.ru_number', 'like', $term)
                    ->orWhere('equipment.grsi', 'like', $term)
                    ->orWhere('equipment.year_of_manufacture', 'like', $term)
                    ->orWhereHas('equipmentType', fn ($q) => $q->where('name', 'like', $term))
                    ->orWhereHas('department', fn ($q) => $q->where('name', 'like', $term))
                    ->orWhereHas('group', fn ($q) => $q->where('name', 'like', $term))
                    ->orWhereHas('equipmentCondition', fn ($q) => $q->where('name', 'like', $term));
            });
        }

        if (!empty($filterEtype)) {
            $query->whereIn('equipment.equipment_type_id', $filterEtype);
        }
        if (!empty($filterDepartment)) {
            $query->whereIn('equipment.department_id', $filterDepartment);
        }
        if (!empty($filterCabinet)) {
            $query->whereIn('equipment.cabinet_id', $filterCabinet);
        }
        if (!empty($filterGroup)) {
            $query->whereIn('equipment.group_id', $filterGroup);
        }
        if (!empty($filterCondition)) {
            $query->whereIn('equipment.equipment_condition_id', $filterCondition);
        }
        if ($filterName !== '') {
            $query->where('equipment.name', 'like', '%' . trim($filterName) . '%');
        }
        if ($filterSerial !== '') {
            $query->where('equipment.serial_number', 'like', '%' . trim($filterSerial) . '%');
        }
        if ($filterYear !== '') {
            $query->where('equipment.year_of_manufacture', 'like', '%' . trim($filterYear) . '%');
        }
        if ($filterInventory !== '') {
            $query->where('equipment.inventory_number', 'like', '%' . trim($filterInventory) . '%');
        }
        if ($filterRu !== '') {
            $query->where('equipment.ru_number', 'like', '%' . trim($filterRu) . '%');
        }
        if ($filterGrsi !== '') {
            $query->where('equipment.grsi', 'like', '%' . trim($filterGrsi) . '%');
        }

        $orderColumn = self::SORTABLE_COLUMNS[$sortBy] ?? 'equipment.number';
        if (in_array($sortBy, ['equipment_type_name', 'department_name', 'cabinet_number', 'group_name', 'condition_name'], true)) {
            if ($sortBy === 'equipment_type_name') {
                $query->leftJoin('equipment_types', 'equipment.equipment_type_id', '=', 'equipment_types.id');
            } elseif ($sortBy === 'department_name') {
                $query->leftJoin('departments', 'equipment.department_id', '=', 'departments.id');
            } elseif ($sortBy === 'cabinet_number') {
                $query->leftJoin('cabinets', 'equipment.cabinet_id', '=', 'cabinets.id');
            } elseif ($sortBy === 'group_name') {
                $query->leftJoin('groups', 'equipment.group_id', '=', 'groups.id');
            } elseif ($sortBy === 'condition_name') {
                $query->leftJoin('equipment_conditions', 'equipment.equipment_condition_id', '=', 'equipment_conditions.id');
            }
        }
        $query->orderBy($orderColumn, $sortDir);

        $equipment = $query->paginate(15)->withQueryString();

        $equipmentTypes = EquipmentType::orderBy('name')->get(['id', 'name']);
        $departments = Department::orderBy('name')->get(['id', 'name']);
        $cabinets = Cabinet::orderBy('number')->get(['id', 'number']);
        $groups = Group::orderBy('name')->get(['id', 'name']);
        $conditions = EquipmentCondition::orderBy('name')->get(['id', 'name']);

        return view('equipment.index', [
            'equipment' => $equipment,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortDir' => $sortDir,
            'equipmentTypes' => $equipmentTypes,
            'departments' => $departments,
            'cabinets' => $cabinets,
            'groups' => $groups,
            'conditions' => $conditions,
            'filterEtype' => $filterEtype,
            'filterDepartment' => $filterDepartment,
            'filterCabinet' => $filterCabinet,
            'filterGroup' => $filterGroup,
            'filterCondition' => $filterCondition,
            'filterName' => $filterName,
            'filterSerial' => $filterSerial,
            'filterYear' => $filterYear,
            'filterInventory' => $filterInventory,
            'filterRu' => $filterRu,
            'filterGrsi' => $filterGrsi,
        ]);
    }

    /**
     * Карточка оборудования (просмотр).
     */
    public function show(Equipment $equipment): View
    {
        $equipment->load([
            'images',
            'documents.documentType',
            'equipmentType',
            'department',
            'cabinet',
            'group',
            'equipmentCondition',
            'supplier',
            'serviceOrganization',
            'writeoffState',
            'utilizationState',
        ]);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return view('equipment.show', [
            'equipment' => $equipment,
            'departments' => $departments,
        ]);
    }

    /**
     * Загрузка документа с карточки оборудования (PDF, Word, Excel).
     */
    public function storeDocument(Request $request, Equipment $equipment): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:instruction,registration_certificate,commissioning_act,ru_scan',
            'document' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ], [
            'document.mimes' => 'Допустимые форматы: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
        ]);
        $type = $request->input('type');
        $file = $request->file('document');
        $labels = [
            'instruction' => 'Инструкция на русском языке',
            'registration_certificate' => 'Регистрационное удостоверение',
            'commissioning_act' => 'Акт ввода в эксплуатацию',
            'ru_scan' => 'Регистрационное удостоверение',
        ];
        $equipment->load(['images', 'documents']);
        $snapshotBeforeJson = json_encode($this->buildEquipmentSnapshot($equipment), JSON_UNESCAPED_UNICODE);

        $typeId = EquipmentDocumentType::idForCode($type);
        if ($typeId === null) {
            return redirect()->back()->with('error', 'Неизвестный тип документа.');
        }
        $equipment->documents()->where('document_type_id', $typeId)->get()->each(function (EquipmentDocument $doc) {
            Storage::disk('public')->delete($doc->document);
            $doc->delete();
        });
        $path = $file->store('equipment_documents', 'public');
        $equipment->documents()->create([
            'document' => $path,
            'name' => $labels[$type],
            'document_type_id' => $typeId,
            'uploaded_at' => now(),
        ]);
        $equipment->refresh()->load(['images', 'documents.documentType']);
        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'updated',
            '№'.$equipment->number.' — '.$equipment->name,
            'Загружен документ: '.$labels[$type].'.',
            $snapshotBeforeJson,
        );

        return redirect()->route('equipment.show', $equipment)->with('success', 'Документ загружен.');
    }

    /**
     * Форма добавления оборудования (только для админа).
     */
    public function create(): View
    {
        return view('equipment.create', [
            'equipmentTypes' => EquipmentType::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'cabinets' => Cabinet::orderBy('number')->get(['id', 'number']),
            'groups' => Group::orderBy('name')->get(['id', 'name']),
            'conditions' => EquipmentCondition::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'serviceOrganizations' => ServiceOrganization::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Сохранение нового оборудования.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->mergeEmptyRelationIds($request);
        $request->validate([
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ], [
            'images.required' => 'Добавьте от 1 до 5 фотографий оборудования.',
            'images.min' => 'Нужна минимум 1 фотография.',
            'images.max' => 'Максимум 5 фотографий.',
        ]);
        $validated =         $request->validate([
            'document_registration_certificate' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'document_instruction' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'document_commissioning_act' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'number' => ['required', 'integer', 'min:1', Rule::unique('equipment', 'number')->whereNull('deleted_at')],
            'equipment_type_id' => ['nullable', Rule::exists('equipment_types', 'id')->whereNull('deleted_at')],
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'production_date' => 'nullable|date',
            'year_of_manufacture' => 'nullable|string|max:55',
            'date_accepted_to_accounting' => 'nullable|date',
            'inventory_number' => 'nullable|string|max:100',
            'department_id' => ['nullable', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'cabinet_id' => ['nullable', Rule::exists('cabinets', 'id')->whereNull('deleted_at')],
            'group_id' => 'nullable|exists:groups,id',
            'equipment_condition_id' => 'nullable|exists:equipment_conditions,id',
            'ru_number' => 'nullable|string|max:100',
            'ru_date' => 'nullable|date',
            'grsi' => 'nullable|string|max:255',
            'registration_certificate' => 'nullable|string|max:100',
            'date_of_registration' => 'nullable|string|max:20',
            'valid_until' => 'nullable|string|max:20',
            'valid_to' => 'nullable|string|max:20',
            'verification_period' => 'nullable|string|max:55',
            'last_verification_date' => 'nullable|string|max:20',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'service_organization_id' => 'nullable|exists:service_organizations,id',
        ], [
            'document_registration_certificate.required' => 'Загрузите регистрационное удостоверение.',
            'document_instruction.required' => 'Загрузите инструкцию на русском языке.',
            'document_commissioning_act.required' => 'Загрузите акт ввода в эксплуатацию.',
            'document_registration_certificate.mimes' => 'Допустимые форматы документов: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
            'document_instruction.mimes' => 'Допустимые форматы документов: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
            'document_commissioning_act.mimes' => 'Допустимые форматы документов: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
        ]);

        $validated['production_date'] = $request->filled('production_date') ? $request->input('production_date') : null;
        $validated['date_accepted_to_accounting'] = $request->filled('date_accepted_to_accounting') ? $request->input('date_accepted_to_accounting') : null;
        $validated['ru_date'] = $request->filled('ru_date') ? $request->input('ru_date') : null;
        $validated['equipment_type_id'] = $request->filled('equipment_type_id') ? $validated['equipment_type_id'] : null;
        $validated['department_id'] = $request->filled('department_id') ? $validated['department_id'] : null;
        $validated['cabinet_id'] = $request->filled('cabinet_id') ? $validated['cabinet_id'] : null;
        $validated['group_id'] = $request->filled('group_id') ? $validated['group_id'] : null;
        $validated['equipment_condition_id'] = $request->filled('equipment_condition_id') ? $validated['equipment_condition_id'] : null;
        $validated['supplier_id'] = $request->filled('supplier_id') ? $validated['supplier_id'] : null;
        $validated['service_organization_id'] = $request->filled('service_organization_id') ? $validated['service_organization_id'] : null;
        if (! $request->user()?->canAssignInventoryNumber()) {
            $validated['inventory_number'] = null;
        }

        $equipment = Equipment::create($validated);
        $this->storeEquipmentImages($request->file('images'), $equipment);
        $this->storeEquipmentDocuments($request, $equipment);

        $equipment->refresh();
        $this->logEquipmentAudit(
            $equipment,
            'created',
            null,
            'Создана карточка оборудования № '.$equipment->number.' пользователем '.(auth()->user()?->name ?? '—').'.'
        );

        return redirect()->route('equipment.index')->with('success', 'Оборудование добавлено.');
    }

    /**
     * Форма редактирования оборудования (только для админа).
     */
    public function edit(Equipment $equipment): View
    {
        $equipment->load(['images', 'documents.documentType']);
        return view('equipment.edit', [
            'equipment' => $equipment,
            'equipmentTypes' => EquipmentType::orderBy('name')->get(['id', 'name']),
            'departments' => Department::orderBy('name')->get(['id', 'name']),
            'cabinets' => Cabinet::orderBy('number')->get(['id', 'number']),
            'groups' => Group::orderBy('name')->get(['id', 'name']),
            'conditions' => EquipmentCondition::orderBy('name')->get(['id', 'name']),
            'suppliers' => Supplier::orderBy('name')->get(['id', 'name']),
            'serviceOrganizations' => ServiceOrganization::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Обновление оборудования.
     */
    public function update(Request $request, Equipment $equipment): RedirectResponse
    {
        $this->mergeEmptyRelationIds($request);
        $equipment->load('images');
        $currentCount = $equipment->images->count();
        $deleteIds = array_filter((array) $request->input('delete_images', []));
        $validDeleteIds = $equipment->images()->whereIn('id', $deleteIds)->pluck('id')->all();
        $newFiles = $request->file('images', []);
        $newCount = is_array($newFiles) ? count($newFiles) : 0;
        $afterCount = $currentCount - count($validDeleteIds) + $newCount;
        if ($afterCount < 1 || $afterCount > 5) {
            return redirect()->back()->withInput()->withErrors([
                'images' => 'Должно быть от 1 до 5 фотографий. Сейчас после изменений будет: ' . $afterCount . '.',
            ]);
        }
        $request->validate([
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'delete_images' => 'nullable|array',
            'delete_images.*' => 'exists:equipment_images,id',
        ]);
        $validated =         $request->validate([
            'document_registration_certificate' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'document_instruction' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'document_commissioning_act' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'number' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('equipment', 'number')->whereNull('deleted_at')->ignore($equipment->id),
            ],
            'equipment_type_id' => ['nullable', Rule::exists('equipment_types', 'id')->whereNull('deleted_at')],
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'production_date' => 'nullable|date',
            'year_of_manufacture' => 'nullable|string|max:55',
            'date_accepted_to_accounting' => 'nullable|date',
            'inventory_number' => 'nullable|string|max:100',
            'department_id' => ['nullable', Rule::exists('departments', 'id')->whereNull('deleted_at')],
            'cabinet_id' => ['nullable', Rule::exists('cabinets', 'id')->whereNull('deleted_at')],
            'group_id' => 'nullable|exists:groups,id',
            'equipment_condition_id' => 'nullable|exists:equipment_conditions,id',
            'ru_number' => 'nullable|string|max:100',
            'ru_date' => 'nullable|date',
            'grsi' => 'nullable|string|max:255',
            'registration_certificate' => 'nullable|string|max:100',
            'date_of_registration' => 'nullable|string|max:20',
            'valid_until' => 'nullable|string|max:20',
            'valid_to' => 'nullable|string|max:20',
            'verification_period' => 'nullable|string|max:55',
            'last_verification_date' => 'nullable|string|max:20',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'service_organization_id' => 'nullable|exists:service_organizations,id',
        ]);

        $validated['production_date'] = $request->filled('production_date') ? $request->input('production_date') : null;
        $validated['date_accepted_to_accounting'] = $request->filled('date_accepted_to_accounting') ? $request->input('date_accepted_to_accounting') : null;
        $validated['ru_date'] = $request->filled('ru_date') ? $request->input('ru_date') : null;
        $validated['equipment_type_id'] = $request->filled('equipment_type_id') ? $validated['equipment_type_id'] : null;
        $validated['department_id'] = $request->filled('department_id') ? $validated['department_id'] : null;
        $validated['cabinet_id'] = $request->filled('cabinet_id') ? $validated['cabinet_id'] : null;
        $validated['group_id'] = $request->filled('group_id') ? $validated['group_id'] : null;
        $validated['equipment_condition_id'] = $request->filled('equipment_condition_id') ? $validated['equipment_condition_id'] : null;
        $validated['supplier_id'] = $request->filled('supplier_id') ? $validated['supplier_id'] : null;
        $validated['service_organization_id'] = $request->filled('service_organization_id') ? $validated['service_organization_id'] : null;

        $equipment->load(['images', 'documents.documentType']);
        $snapshotBeforeJson = json_encode($this->buildEquipmentSnapshot($equipment), JSON_UNESCAPED_UNICODE);
        $before = $equipment->getAttributes();
        if (! $request->user()?->canAssignInventoryNumber()) {
            $validated['inventory_number'] = $equipment->inventory_number;
        }

        $equipment->update($validated);

        foreach ($validDeleteIds as $id) {
            $img = $equipment->images()->find($id);
            if ($img) {
                Storage::disk('public')->delete($img->image);
                $img->delete();
            }
        }
        $newFiles = $request->file('images', []);
        if (!empty($newFiles)) {
            $this->storeEquipmentImages($newFiles, $equipment);
        }
        $this->storeEquipmentDocuments($request, $equipment);

        $equipment->refresh();
        $this->logEquipmentUpdated($equipment, $before, $validDeleteIds, $newFiles, $snapshotBeforeJson);

        return redirect()->route('equipment.index')->with('success', 'Оборудование обновлено.');
    }

    /**
     * Присвоение/обновление инвентарного номера (только бухгалтер).
     */
    public function updateInventoryNumber(Request $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|integer',
            'inventory_number' => 'required|string|max:100',
        ], [
            'inventory_number.required' => 'Введите инвентарный номер.',
            'inventory_number.max' => 'Инвентарный номер не должен превышать 100 символов.',
        ]);

        $old = $equipment->inventory_number;
        $new = trim($validated['inventory_number']);
        if ($old === $new) {
            return redirect()->route('equipment.index')->with('status', 'Инвентарный номер не изменён.');
        }

        $equipment->update(['inventory_number' => $new]);
        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'updated',
            '№'.$equipment->number.' — '.$equipment->name,
            'Инвентарный номер изменён бухгалтером '.(auth()->user()?->name ?? '—').': '
                .($old ?: '—').' → '.$new.'.',
        );

        return redirect()->route('equipment.index')->with('success', 'Инвентарный номер сохранён.');
    }

    /**
     * Обновление даты принятия к учёту (только бухгалтер).
     */
    public function updateAcceptedToAccountingDate(Request $request, Equipment $equipment): RedirectResponse
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|integer',
            'date_accepted_to_accounting' => 'required|date',
        ], [
            'date_accepted_to_accounting.required' => 'Введите дату принятия к учёту.',
            'date_accepted_to_accounting.date' => 'Дата принятия к учёту указана некорректно.',
        ]);

        $old = $equipment->date_accepted_to_accounting?->format('Y-m-d');
        $new = (string) $validated['date_accepted_to_accounting'];

        if ($old === $new) {
            return redirect()->route('equipment.index')->with('status', 'Дата принятия к учёту не изменена.');
        }

        $equipment->update([
            'date_accepted_to_accounting' => $new,
        ]);

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'updated',
            '№'.$equipment->number.' — '.$equipment->name,
            'Дата принятия к учёту обновлена бухгалтером по основанию акта учёта: '
                .($old ? \Carbon\Carbon::parse($old)->format('d.m.Y') : '—')
                .' → '
                .\Carbon\Carbon::parse($new)->format('d.m.Y')
                .'.',
        );

        return redirect()->route('equipment.index')->with('success', 'Дата принятия к учёту сохранена.');
    }

    /**
     * Отметка утилизации (только для уже списанного оборудования), с обязательным актом утилизации.
     */
    public function markUtilized(Request $request, Equipment $equipment): RedirectResponse
    {
        $request->validate([
            'utilization_act' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ], [
            'utilization_act.required' => 'Прикрепите файл акта утилизации (PDF, Word или Excel).',
            'utilization_act.mimes' => 'Допустимые форматы акта: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
        ]);

        $equipment->loadMissing(['writeoffState', 'utilizationState']);

        if (! $equipment->isWrittenOff()) {
            return redirect()->back()->withErrors([
                'utilize' => 'Утилизировать можно только оборудование со статусом «Списано».',
            ]);
        }
        if ($equipment->isUtilized()) {
            return redirect()->back()->with('status', 'Это оборудование уже отмечено как утилизированное.');
        }

        $utilizedId = UtilizationState::query()->where('code', 'utilized')->value('id');
        if ($utilizedId === null) {
            return redirect()->back()->withErrors(['utilize' => 'Справочник состояний утилизации не настроен.']);
        }

        $typeId = EquipmentDocumentType::idForCode('utilization_act');
        if ($typeId === null) {
            return redirect()->back()->withErrors([
                'utilize' => 'В справочнике нет типа документа «акт утилизации». Выполните миграции.',
            ]);
        }

        $equipment->load(['images', 'documents']);
        $snapshotBeforeJson = json_encode($this->buildEquipmentSnapshot($equipment), JSON_UNESCAPED_UNICODE);

        $this->replaceEquipmentUtilizationAct($equipment, $request->file('utilization_act'), $typeId);

        $equipment->utilization_state_id = $utilizedId;
        $equipment->save();

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'utilized',
            '№'.$equipment->number.' — '.$equipment->name,
            'Утилизация с актом: пользователь '.(auth()->user()?->name ?? '—').' прикрепил акт утилизации.',
            $snapshotBeforeJson,
        );

        return redirect()->back()->with('success', 'Оборудование утилизировано, акт сохранён. Его можно открыть или скачать в карточке оборудования.');
    }

    /**
     * Замена акта утилизации у уже утилизированного оборудования.
     */
    public function storeUtilizationAct(Request $request, Equipment $equipment): RedirectResponse
    {
        $request->validate([
            'utilization_act' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
        ], [
            'utilization_act.required' => 'Выберите файл акта утилизации.',
            'utilization_act.mimes' => 'Допустимые форматы: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).',
        ]);

        if (! $equipment->isUtilized()) {
            return redirect()->back()->withErrors([
                'utilization_act' => 'Загрузить или заменить акт можно только для утилизированного оборудования.',
            ]);
        }

        $typeId = EquipmentDocumentType::idForCode('utilization_act');
        if ($typeId === null) {
            return redirect()->back()->withErrors([
                'utilization_act' => 'Тип документа «акт утилизации» не найден. Выполните миграции.',
            ]);
        }

        $equipment->load(['images', 'documents.documentType']);
        $snapshotBeforeJson = json_encode($this->buildEquipmentSnapshot($equipment), JSON_UNESCAPED_UNICODE);

        $this->replaceEquipmentUtilizationAct($equipment, $request->file('utilization_act'), $typeId);

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'updated',
            '№'.$equipment->number.' — '.$equipment->name,
            'Обновлён акт утилизации пользователем '.(auth()->user()?->name ?? '—').'.',
            $snapshotBeforeJson,
        );

        return redirect()->back()->with('success', 'Акт утилизации обновлён.');
    }

    private function replaceEquipmentUtilizationAct(Equipment $equipment, UploadedFile $file, int $typeId): void
    {
        $equipment->documents()->where('document_type_id', $typeId)->get()->each(function (EquipmentDocument $doc) {
            Storage::disk('public')->delete($doc->document);
            $doc->delete();
        });
        $path = $file->store('equipment_documents', 'public');
        $equipment->documents()->create([
            'document' => $path,
            'name' => 'Акт утилизации',
            'document_type_id' => $typeId,
            'uploaded_at' => now(),
        ]);
    }

    private function storeEquipmentDocuments(Request $request, Equipment $equipment): void
    {
        $documentMap = [
            'registration_certificate' => ['document_registration_certificate', 'Регистрационное удостоверение'],
            'instruction' => ['document_instruction', 'Инструкция на русском языке'],
            'commissioning_act' => ['document_commissioning_act', 'Акт ввода в эксплуатацию'],
        ];
        foreach ($documentMap as $typeCode => [$key, $label]) {
            $file = $request->file($key);
            if (!$file || !$file->isValid()) {
                continue;
            }
            $typeId = EquipmentDocumentType::idForCode($typeCode);
            if ($typeId === null) {
                continue;
            }
            $existing = $equipment->documents()->where('document_type_id', $typeId)->get();
            foreach ($existing as $doc) {
                Storage::disk('public')->delete($doc->document);
                $doc->delete();
            }
            $path = $file->store('equipment_documents', 'public');
            $equipment->documents()->create([
                'document' => $path,
                'name' => $label,
                'document_type_id' => $typeId,
                'uploaded_at' => now(),
            ]);
        }
    }

    private function storeEquipmentImages(array $files, Equipment $equipment): void
    {
        foreach ($files as $file) {
            if (!$file->isValid()) {
                continue;
            }
            $path = $file->store('equipment', 'public');
            $equipment->images()->create(['image' => $path]);
        }
    }

    private function mergeEmptyRelationIds(Request $request): void
    {
        $keys = [
            'equipment_type_id', 'department_id', 'cabinet_id', 'group_id',
            'equipment_condition_id', 'supplier_id', 'service_organization_id',
        ];
        $merge = [];
        foreach ($keys as $key) {
            $merge[$key] = $request->input($key) ?: null;
        }
        $request->merge($merge);
    }

    /**
     * Удаление из списка (мягкое): карточка и файлы сохраняются, запись в журнале, восстановление — у администратора.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        $equipment->load(['images', 'documents.documentType']);
        $snapshot = $this->buildEquipmentSnapshot($equipment);

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'deleted',
            '№'.$equipment->number.' — '.$equipment->name,
            'Удаление из списка оборудования пользователем '.(auth()->user()?->name ?? '—').'. Данные и файлы сохранены; можно восстановить в разделе «Архив и журнал».',
            json_encode($snapshot, JSON_UNESCAPED_UNICODE),
            null,
            (string) $equipment->number,
            $equipment->name,
        );

        $equipment->delete();

        return redirect()->route('equipment.index')->with('deleted', 'Оборудование скрыто из списка. Администратор может восстановить запись в «Архив и журнал».');
    }

    private function buildEquipmentSnapshot(Equipment $equipment): array
    {
        return [
            'equipment' => $equipment->getAttributes(),
            'images' => $equipment->images->map(fn ($i) => ['id' => $i->id, 'path' => $i->image])->values()->all(),
            'documents' => $equipment->documents->map(fn ($d) => [
                'id' => $d->id,
                'path' => $d->document,
                'document_type_id' => $d->document_type_id,
                'type' => $d->type,
                'name' => $d->name,
                'uploaded_at' => $d->uploaded_at?->format('Y-m-d H:i:s'),
            ])->values()->all(),
        ];
    }

    private function logEquipmentAudit(Equipment $equipment, string $action, ?string $snapshotJson, string $details): void
    {
        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            $action,
            '№'.$equipment->number.' — '.$equipment->name,
            $details,
            $snapshotJson,
        );
    }

    /**
     * @param  array<int, int>  $removedImageIds
     * @param  array<int, mixed>  $newImageFiles
     */
    private function logEquipmentUpdated(Equipment $equipment, array $before, array $removedImageIds, array $newImageFiles, string $snapshotBeforeJson): void
    {
        $lines = [];
        foreach ($equipment->getAttributes() as $key => $newVal) {
            if ($key === 'deleted_at') {
                continue;
            }
            if (! array_key_exists($key, $before)) {
                continue;
            }
            $oldVal = $before[$key];
            if ($oldVal == $newVal) {
                continue;
            }
            $lines[] = $key.': '.json_encode($oldVal, JSON_UNESCAPED_UNICODE).' → '.json_encode($newVal, JSON_UNESCAPED_UNICODE);
        }
        if ($removedImageIds !== []) {
            $lines[] = 'Удалены фото id: '.implode(', ', $removedImageIds);
        }
        if ($newImageFiles !== []) {
            $lines[] = 'Добавлено новых фото: '.count($newImageFiles);
        }

        $docKeys = ['document_registration_certificate', 'document_instruction', 'document_commissioning_act'];
        foreach ($docKeys as $dk) {
            if (request()->hasFile($dk)) {
                $lines[] = 'Загружен документ: '.$dk;
            }
        }

        if ($lines === []) {
            return;
        }

        ActivityLog::record(
            Equipment::class,
            $equipment->id,
            'updated',
            '№'.$equipment->number.' — '.$equipment->name,
            'Изменение карточки пользователем '.(auth()->user()?->name ?? '—').":\n".implode("\n", $lines),
            $snapshotBeforeJson,
        );
    }
}
