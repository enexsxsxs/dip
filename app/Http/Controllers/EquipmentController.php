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
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
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
            ->with(['department', 'cabinet', 'group', 'equipmentType', 'equipmentCondition']);

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
            'documents',
            'equipmentType',
            'department',
            'cabinet',
            'group',
            'equipmentCondition',
            'supplier',
            'serviceOrganization',
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
        $equipment->documents()->where('type', $type)->get()->each(function (EquipmentDocument $doc) {
            Storage::disk('public')->delete($doc->document);
            $doc->delete();
        });
        $path = $file->store('equipment_documents', 'public');
        $equipment->documents()->create([
            'document' => $path,
            'name' => $labels[$type],
            'type' => $type,
            'uploaded_at' => now(),
        ]);
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
            'number' => 'required|integer|min:1|unique:equipment,number',
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'production_date' => 'nullable|date',
            'year_of_manufacture' => 'nullable|string|max:55',
            'date_accepted_to_accounting' => 'nullable|date',
            'inventory_number' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'cabinet_id' => 'nullable|exists:cabinets,id',
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
            'instruction_pdf' => 'nullable|string|max:100',
            'registration_certificate_pdf' => 'nullable|string|max:100',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'service_organization_id' => 'nullable|exists:service_organizations,id',
        ], [
            'document_registration_certificate.required' => 'Загрузите регистрационное удостоверение.',
            'document_instruction.required' => 'Загрузите инструкцию на русском языке.',
            'document_commissioning_act.required' => 'Загрузите акт ввода в эксплуатацию.',
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

        $equipment = Equipment::create($validated);
        $this->storeEquipmentImages($request->file('images'), $equipment);
        $this->storeEquipmentDocuments($request, $equipment);

        return redirect()->route('equipment.index')->with('success', 'Оборудование добавлено.');
    }

    /**
     * Форма редактирования оборудования (только для админа).
     */
    public function edit(Equipment $equipment): View
    {
        $equipment->load(['images', 'documents']);
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
            'number' => 'required|integer|min:1|unique:equipment,number,' . $equipment->id,
            'equipment_type_id' => 'nullable|exists:equipment_types,id',
            'name' => 'required|string|max:255',
            'serial_number' => 'nullable|string|max:100',
            'production_date' => 'nullable|date',
            'year_of_manufacture' => 'nullable|string|max:55',
            'date_accepted_to_accounting' => 'nullable|date',
            'inventory_number' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:departments,id',
            'cabinet_id' => 'nullable|exists:cabinets,id',
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
            'instruction_pdf' => 'nullable|string|max:100',
            'registration_certificate_pdf' => 'nullable|string|max:100',
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

        return redirect()->route('equipment.index')->with('success', 'Оборудование обновлено.');
    }

    private function storeEquipmentDocuments(Request $request, Equipment $equipment): void
    {
        $documentMap = [
            'registration_certificate' => ['document_registration_certificate', 'Регистрационное удостоверение'],
            'instruction' => ['document_instruction', 'Инструкция на русском языке'],
            'commissioning_act' => ['document_commissioning_act', 'Акт ввода в эксплуатацию'],
        ];
        foreach ($documentMap as $type => [$key, $label]) {
            $file = $request->file($key);
            if (!$file || !$file->isValid()) {
                continue;
            }
            $existing = $equipment->documents()->where('type', $type)->get();
            foreach ($existing as $doc) {
                Storage::disk('public')->delete($doc->document);
                $doc->delete();
            }
            $path = $file->store('equipment_documents', 'public');
            $equipment->documents()->create([
                'document' => $path,
                'name' => $label,
                'type' => $type,
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
     * Удаление оборудования (только для админа).
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        foreach ($equipment->images as $img) {
            Storage::disk('public')->delete($img->image);
        }
        foreach ($equipment->documents as $doc) {
            Storage::disk('public')->delete($doc->document);
        }
        $equipment->delete();
        return redirect()->route('equipment.index')->with('deleted', 'Оборудование удалено.');
    }
}
