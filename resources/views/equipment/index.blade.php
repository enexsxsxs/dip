@php
    $currentSortBy = $sortBy ?? 'number';
    $currentSortDir = $sortDir ?? 'asc';
    $baseQuery = array_filter([
        'search' => $search ?? '',
        'sort_by' => $currentSortBy,
        'sort_dir' => $currentSortDir,
        'filter_etype' => $filterEtype ?? [],
        'filter_department' => $filterDepartment ?? [],
        'filter_cabinet' => $filterCabinet ?? [],
        'filter_group' => $filterGroup ?? [],
        'filter_condition' => $filterCondition ?? [],
        'filter_name' => $filterName ?? '',
        'filter_serial' => $filterSerial ?? '',
        'filter_year' => $filterYear ?? '',
        'filter_inventory' => $filterInventory ?? '',
        'filter_ru' => $filterRu ?? '',
        'filter_grsi' => $filterGrsi ?? '',
    ], fn($v) => $v !== '' && $v !== []);
    $etypeOptions = collect($equipmentTypes ?? [])->map(fn($t) => ['id' => $t->id, 'label' => $t->name])->values()->all();
    $deptOptions = collect($departments ?? [])->map(fn($d) => ['id' => $d->id, 'label' => $d->name])->values()->all();
    $cabinetOptions = collect($cabinets ?? [])->map(fn($c) => ['id' => $c->id, 'label' => $c->number])->values()->all();
    $groupOptions = collect($groups ?? [])->map(fn($g) => ['id' => $g->id, 'label' => $g->name])->values()->all();
    $conditionOptions = collect($conditions ?? [])->map(fn($c) => ['id' => $c->id, 'label' => $c->name])->values()->all();
    $canAssignInventoryNumber = auth()->user()?->canAssignInventoryNumber();
    $canManageUtilization = auth()->user()?->canManageUtilization();
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                Список оборудования
            </h2>
            <div class="flex flex-wrap items-center gap-3">
                @if(auth()->user()?->canManageEquipment())
                    <a href="{{ route('equipment.create') }}"
                       class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition shadow-md">
                        + Добавить оборудование
                    </a>
                @endif
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-teal-700 bg-teal-50 border-2 border-teal-200 hover:bg-teal-100 transition">
                    ← На главную
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if (session('status'))
            <div class="rounded-xl border border-teal-200 bg-teal-50 text-teal-800 px-4 py-3 text-sm">
                {{ session('status') }}
            </div>
        @endif
        @if ($errors->has('inventory_number'))
            <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first('inventory_number') }}
            </div>
        @endif
        @if ($errors->has('date_accepted_to_accounting'))
            <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first('date_accepted_to_accounting') }}
            </div>
        @endif
        @if ($errors->has('utilize'))
            <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first('utilize') }}
            </div>
        @endif
        @if ($errors->has('utilization_act'))
            <div class="rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm">
                {{ $errors->first('utilization_act') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-lg border-2 border-teal-100 p-5">
            <form method="get" action="{{ route('equipment.index') }}" class="flex flex-wrap items-end gap-4">
                <input type="hidden" name="sort_by" value="{{ $currentSortBy }}">
                <input type="hidden" name="sort_dir" value="{{ $currentSortDir }}">
                @foreach($baseQuery as $k => $v)
                    @if($k !== 'search' && $k !== 'sort_by' && $k !== 'sort_dir')
                        @if(is_array($v))
                            @foreach($v as $vv)
                                <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endif
                @endforeach
                <div class="flex-1 min-w-[220px]">
                    <label for="search" class="block text-base font-semibold text-slate-700 mb-2">Поиск</label>
                    <input type="search" id="search" name="search" value="{{ old('search', $search ?? '') }}"
                           placeholder="Название, инв. №, SN, №РУ, ГРСИ..."
                           class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                </div>
                <button type="submit" class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition">Искать</button>
                @if($search ?? '')
                    <a href="{{ route('equipment.index', array_merge(array_diff_key($baseQuery, ['search' => 1]), ['sort_by' => $currentSortBy, 'sort_dir' => $currentSortDir])) }}"
                       class="inline-flex items-center min-h-[48px] px-4 py-3 text-base font-medium text-slate-600 hover:text-teal-600">Сбросить поиск</a>
                @endif
            </form>
        </div>

        {{-- Настройка видимости столбцов --}}
        <div class="bg-white rounded-2xl shadow-lg border border-teal-100 p-4">
            <div class="text-sm font-semibold text-slate-800 mb-2">
                Какие столбцы показывать в таблице
            </div>
            <div class="text-xs text-slate-500 mb-3">
                Можно скрыть лишнее, а потом снова включить галочкой.
            </div>
            <div class="flex flex-wrap gap-3 text-xs text-slate-700">
                @if(auth()->user()?->canManageEquipment())
                    <label class="inline-flex items-center gap-1">
                        <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                               data-col-toggle="col-actions" checked>
                        <span>Действия</span>
                    </label>
                @endif
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-number" checked>
                    <span>№</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-type" checked>
                    <span>Вид оборудования</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-name" checked>
                    <span>Название</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-serial" checked>
                    <span>Заводской №</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-production-date" checked>
                    <span>Дата производства</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-year" checked>
                    <span>Год производства</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-accepted-date" checked>
                    <span>Дата принятия к учёту</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-inventory" checked>
                    <span>Инв. №</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-department" checked>
                    <span>Отделение</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-cabinet" checked>
                    <span>Кабинет</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-group" checked>
                    <span>Группа</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-condition" checked>
                    <span>Состояние</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-writeoff" checked>
                    <span>Списание</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-ru-number" checked>
                    <span>№ РУ</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-ru-date" checked>
                    <span>Дата РУ</span>
                </label>
                <label class="inline-flex items-center gap-1">
                    <input type="checkbox" class="rounded border-slate-300 text-teal-600"
                           data-col-toggle="col-grsi" checked>
                    <span>ГРСИ</span>
                </label>
            </div>
        </div>

        <div class="w-full">
            <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
                <div>
                    <table class="min-w-full divide-y divide-slate-200 text-[11px]">
                        <thead class="bg-slate-50">
                            <tr>
                                @if(auth()->user()?->canManageEquipment())
                                    <th data-col="col-actions" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">Действия</th>
                                @endif
                                <th data-col="col-number" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="№" sort-key="number" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-type" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Вид" sort-key="equipment_type_name" filter-type="checkboxes" filter-param="filter_etype" :options="$etypeOptions" :current-filter="$filterEtype ?? []" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-name" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Название" sort-key="name" filter-type="text" filter-param="filter_name" :current-filter-text="$filterName ?? ''" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-serial" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Зав. №" sort-key="serial_number" filter-type="text" filter-param="filter_serial" :current-filter-text="$filterSerial ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-production-date" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Дата пр-ва" sort-key="production_date" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-year" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Год пр-ва" sort-key="year_of_manufacture" filter-type="text" filter-param="filter_year" :current-filter-text="$filterYear ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-accepted-date" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Принятие" sort-key="date_accepted_to_accounting" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-inventory" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Инв. №" sort-key="inventory_number" filter-type="text" filter-param="filter_inventory" :current-filter-text="$filterInventory ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-department" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Отдел" sort-key="department_name" filter-type="checkboxes" filter-param="filter_department" :options="$deptOptions" :current-filter="$filterDepartment ?? []" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-cabinet" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Каб." sort-key="cabinet_number" filter-type="checkboxes" filter-param="filter_cabinet" :options="$cabinetOptions" :current-filter="$filterCabinet ?? []" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-group" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Группа" sort-key="group_name" filter-type="checkboxes" filter-param="filter_group" :options="$groupOptions" :current-filter="$filterGroup ?? []" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-condition" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Сост." sort-key="condition_name" filter-type="checkboxes" filter-param="filter_condition" :options="$conditionOptions" :current-filter="$filterCondition ?? []" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-writeoff" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    Списание
                                </th>
                                <th data-col="col-ru-number" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="№РУ" sort-key="ru_number" filter-type="text" filter-param="filter_ru" :current-filter-text="$filterRu ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-ru-date" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Дата РУ" sort-key="ru_date" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th data-col="col-grsi" class="px-1.5 py-1.5 text-left text-[10px] font-semibold text-slate-700 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="ГРСИ" sort-key="grsi" filter-type="text" filter-param="filter_grsi" :current-filter-text="$filterGrsi ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($equipment as $item)
                                <tr class="hover:bg-teal-50/50 transition text-[11px] {{ $item->isUtilized() ? 'line-through opacity-60' : '' }}">
                                    @if(auth()->user()?->canManageEquipment())
                                        <td data-col="col-actions" class="px-2 py-1.5 space-y-1">
                                            <div>
                                                <a href="{{ route('equipment.edit', $item) }}" class="text-teal-600 hover:text-teal-800 font-semibold text-base" title="Изменить">Изменить</a>
                                            </div>
                                            <div class="flex flex-wrap items-center gap-2">
                                                <form method="post" action="{{ route('equipment.destroy', $item) }}" class="inline" onsubmit="return confirm('Убрать оборудование из списка? Запись сохранится в архиве, администратор сможет восстановить.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-amber-700 hover:text-amber-900 font-medium text-sm" title="Удалить из списка">Удалить из списка</button>
                                                </form>
                                                @if(auth()->user()?->isAdmin())
                                                    @if($item->writeoff_status === 'requested')
                                                        <form method="post" action="{{ route('equipment.requests.approveWriteoff', $item) }}" class="inline" onsubmit="return confirm('Подтвердить списание этого оборудования?');">
                                                            @csrf
                                                            <button type="submit" class="text-sm font-medium text-amber-700 hover:text-amber-900" title="Подтвердить списание">Подтвердить списание</button>
                                                        </form>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                    @endif
                                    <td data-col="col-number" class="px-2 py-1.5 text-slate-700 whitespace-nowrap">{{ $item->number ?? $item->id }}</td>
                                    <td data-col="col-type" class="px-2 py-1.5 text-slate-700">{{ $item->equipmentType?->name ?? '—' }}</td>
                                    <td data-col="col-name" class="px-2 py-1.5 font-semibold text-slate-800">
                                        @if(auth()->user()?->canManageEquipment())
                                            <a href="{{ route('equipment.show', $item) }}" class="text-teal-600 hover:text-teal-800 hover:underline">{{ $item->name ?? '—' }}</a>
                                        @else
                                            {{ $item->name ?? '—' }}
                                        @endif
                                    </td>
                                    <td data-col="col-serial" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">{{ $item->serial_number ?? '—' }}</td>
                                    <td data-col="col-production-date" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">{{ $item->production_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td data-col="col-year" class="px-2 py-1.5 text-slate-600">{{ $item->year_of_manufacture ?? '—' }}</td>
                                    <td data-col="col-accepted-date" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">
                                        @if($canAssignInventoryNumber)
                                            <form method="post" action="{{ route('equipment.accepted-to-accounting-date.update', $item) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                                <input type="date" name="date_accepted_to_accounting"
                                                       value="{{ old('equipment_id') == $item->id ? old('date_accepted_to_accounting', $item->date_accepted_to_accounting?->format('Y-m-d')) : $item->date_accepted_to_accounting?->format('Y-m-d') }}"
                                                       class="w-36 rounded-md border-slate-300 text-[11px] py-1 px-2"
                                                       required>
                                                <button type="submit" class="px-2 py-1 rounded bg-teal-600 text-white text-[10px] font-semibold hover:bg-teal-700">
                                                    Сохранить
                                                </button>
                                            </form>
                                        @else
                                            {{ $item->date_accepted_to_accounting?->format('d.m.Y') ?? '—' }}
                                        @endif
                                    </td>
                                    <td data-col="col-inventory" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">
                                        @if($canAssignInventoryNumber)
                                            <form method="post" action="{{ route('equipment.inventory-number.update', $item) }}" class="flex items-center gap-2">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="equipment_id" value="{{ $item->id }}">
                                                <input type="text" name="inventory_number" value="{{ old('equipment_id') == $item->id ? old('inventory_number', $item->inventory_number) : $item->inventory_number }}"
                                                       class="w-32 rounded-md border-slate-300 text-[11px] py-1 px-2"
                                                       placeholder="Инв. №" maxlength="100" required>
                                                <button type="submit" class="px-2 py-1 rounded bg-teal-600 text-white text-[10px] font-semibold hover:bg-teal-700">
                                                    Сохранить
                                                </button>
                                            </form>
                                        @else
                                            {{ $item->inventory_number ?? '—' }}
                                        @endif
                                    </td>
                                    <td data-col="col-department" class="px-2 py-1.5 text-slate-600">{{ $item->department?->name ?? '—' }}</td>
                                    <td data-col="col-cabinet" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">{{ $item->cabinet?->number ?? '—' }}</td>
                                    <td data-col="col-group" class="px-2 py-1.5 text-slate-600">{{ $item->group?->name ?? '—' }}</td>
                                    <td data-col="col-condition" class="px-2 py-1.5 text-slate-600">{{ $item->equipmentCondition?->name ?? '—' }}</td>
                                    <td data-col="col-writeoff" class="px-2 py-1.5">
                                        @if($item->writeoff_status === 'approved')
                                            <div class="flex flex-col gap-1 items-start">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-red-50 text-red-700 border border-red-200 text-[10px]">
                                                    Списано
                                                </span>
                                                @if($item->isUtilized())
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-slate-100 text-slate-600 border border-slate-200 text-[10px]">
                                                        Утилизировано
                                                    </span>
                                                @elseif($canManageUtilization)
                                                    <form method="post" action="{{ route('equipment.utilize', $item) }}" enctype="multipart/form-data" class="flex flex-col gap-1 items-start max-w-[200px]" onsubmit="return confirm('Утилизировать с прикреплённым актом?');">
                                                        @csrf
                                                        <input type="file" name="utilization_act" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required class="text-[9px] max-w-full file:mr-1 file:py-0.5 file:px-1 file:rounded file:border-0 file:bg-violet-100 file:text-violet-800">
                                                        <button type="submit" class="text-[10px] font-semibold text-violet-700 hover:text-violet-900 underline decoration-dotted text-left">
                                                            Утилизировать
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        @elseif($item->writeoff_status === 'requested')
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full font-semibold bg-amber-50 text-amber-700 border border-amber-200 text-[10px]">
                                                Запрошено
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td data-col="col-ru-number" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">{{ $item->ru_number ?? '—' }}</td>
                                    <td data-col="col-ru-date" class="px-2 py-1.5 text-slate-600 whitespace-nowrap">{{ $item->ru_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td data-col="col-grsi" class="px-2 py-1.5 text-slate-600">{{ $item->grsi ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()?->canManageEquipment() ? 17 : 16 }}" class="px-4 py-12 text-center text-base text-slate-500">
                                        @if($search ?? '')
                                            Ничего не найдено. Измените поиск или сбросьте фильтр.
                                        @else
                                            Оборудование пока не добавлено.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($equipment->hasPages())
                    <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                        {{ $equipment->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggles = document.querySelectorAll('[data-col-toggle]');
            toggles.forEach(function (toggle) {
                toggle.addEventListener('change', function () {
                    const colKey = this.getAttribute('data-col-toggle');
                    const cells = document.querySelectorAll('[data-col=\"' + colKey + '\"]');
                    cells.forEach(function (cell) {
                        cell.style.display = toggle.checked ? '' : 'none';
                    });
                });
            });
        });
    </script>
@endpush
</x-app-layout>
