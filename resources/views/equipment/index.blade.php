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
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-2">
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">
                Список оборудования
            </h2>
            <div class="flex items-center gap-2">
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('equipment.create') }}"
                       class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 transition">
                        + Добавить оборудование
                    </a>
                @endif
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200 hover:bg-teal-100 transition">
                    ← На главную
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">
        <div class="bg-white rounded-2xl shadow-lg border border-teal-100 p-4">
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
                <div class="flex-1 min-w-[200px]">
                    <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Поиск</label>
                    <input type="search" id="search" name="search" value="{{ old('search', $search ?? '') }}"
                           placeholder="Название, инв. №, SN, №РУ, ГРСИ..."
                           class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-sm">
                </div>
                <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 transition">Искать</button>
                @if($search ?? '')
                    <a href="{{ route('equipment.index', array_merge(array_diff_key($baseQuery, ['search' => 1]), ['sort_by' => $currentSortBy, 'sort_dir' => $currentSortDir])) }}"
                       class="inline-flex items-center px-4 py-2 text-sm text-slate-500 hover:text-teal-600">Сбросить поиск</a>
                @endif
            </form>
        </div>

        <div class="w-full">
            <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                @if(auth()->user()?->isAdmin())
                                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap w-20">Действия</th>
                                @endif
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="№" sort-key="number" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Вид оборудования" sort-key="equipment_type_name" filter-type="checkboxes" filter-param="filter_etype" :options="$etypeOptions" :current-filter="$filterEtype ?? []" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Название" sort-key="name" filter-type="text" filter-param="filter_name" :current-filter-text="$filterName ?? ''" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Заводской номер [SN]" sort-key="serial_number" filter-type="text" filter-param="filter_serial" :current-filter-text="$filterSerial ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Дата производства" sort-key="production_date" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Год производства" sort-key="year_of_manufacture" filter-type="text" filter-param="filter_year" :current-filter-text="$filterYear ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Дата принятия к учёту" sort-key="date_accepted_to_accounting" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Инв. номер [IN]" sort-key="inventory_number" filter-type="text" filter-param="filter_inventory" :current-filter-text="$filterInventory ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Отделение" sort-key="department_name" filter-type="checkboxes" filter-param="filter_department" :options="$deptOptions" :current-filter="$filterDepartment ?? []" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Кабинет" sort-key="cabinet_number" filter-type="checkboxes" filter-param="filter_cabinet" :options="$cabinetOptions" :current-filter="$filterCabinet ?? []" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Группа" sort-key="group_name" filter-type="checkboxes" filter-param="filter_group" :options="$groupOptions" :current-filter="$filterGroup ?? []" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Состояние" sort-key="condition_name" filter-type="checkboxes" filter-param="filter_condition" :options="$conditionOptions" :current-filter="$filterCondition ?? []" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="№РУ" sort-key="ru_number" filter-type="text" filter-param="filter_ru" :current-filter-text="$filterRu ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="Дата РУ" sort-key="ru_date" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                                <th class="px-3 py-2.5 text-left text-xs font-semibold text-slate-600 uppercase tracking-wider whitespace-nowrap">
                                    <x-equipment-column-filter column-label="ГРСИ" sort-key="grsi" filter-type="text" filter-param="filter_grsi" :current-filter-text="$filterGrsi ?? ''" :numeric-sort="true" :base-query="$baseQuery" />
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse($equipment as $item)
                                <tr class="hover:bg-teal-50/50 transition">
                                    @if(auth()->user()?->isAdmin())
                                        <td class="px-3 py-2 text-sm">
                                            <a href="{{ route('equipment.edit', $item) }}" class="text-teal-600 hover:text-teal-800 font-medium" title="Изменить">Изменить</a>
                                            <form method="post" action="{{ route('equipment.destroy', $item) }}" class="inline ml-1" onsubmit="return confirm('Удалить это оборудование?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm" title="Удалить">Удалить</button>
                                            </form>
                                        </td>
                                    @endif
                                    <td class="px-3 py-2 text-sm text-slate-700">{{ $item->number ?? $item->id }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-700">{{ $item->equipmentType?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm font-medium text-slate-800">
                                        <a href="{{ route('equipment.show', $item) }}" class="text-teal-600 hover:text-teal-800 hover:underline">{{ $item->name ?? '—' }}</a>
                                    </td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->serial_number ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->production_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->year_of_manufacture ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->date_accepted_to_accounting?->format('d.m.Y') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->inventory_number ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->department?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->cabinet?->number ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->group?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->equipmentCondition?->name ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->ru_number ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->ru_date?->format('d.m.Y') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-sm text-slate-600">{{ $item->grsi ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()?->isAdmin() ? 16 : 15 }}" class="px-4 py-12 text-center text-slate-500">
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
</x-app-layout>
