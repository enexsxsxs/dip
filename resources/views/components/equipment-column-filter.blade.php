@props([
    'columnLabel',
    'sortKey',
    'filterType' => 'text',
    'filterParam' => '',
    'options' => [],
    'currentFilter' => [],
    'currentFilterText' => '',
    'baseQuery' => [],
    'numericSort' => false,
])
@php
    $baseQuery = $baseQuery ?? [];
    $numericSort = filter_var($numericSort ?? false, FILTER_VALIDATE_BOOLEAN);
@endphp
<div class="relative inline-flex items-center gap-1" x-data="{ open: false }" @click.outside="open = false">
    <span>{{ $columnLabel }}</span>
    <button type="button"
            @click="open = ! open"
            class="p-0.5 rounded text-slate-400 hover:text-teal-600 hover:bg-teal-100"
            title="Фильтр и сортировка">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
        </svg>
    </button>
    <div x-show="open"
         x-transition
         class="absolute z-30 left-0 top-full mt-1 w-72 bg-white rounded-xl shadow-xl border border-slate-200 py-2"
         style="display: none;">
        <div class="px-3 py-1.5 border-b border-slate-100 space-y-0.5">
            @if($numericSort)
                <a href="{{ route('equipment.index', array_merge($baseQuery, ['sort_by' => $sortKey, 'sort_dir' => 'asc'])) }}"
                   class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                    <span class="text-teal-600">↑</span> По возрастанию
                </a>
                <a href="{{ route('equipment.index', array_merge($baseQuery, ['sort_by' => $sortKey, 'sort_dir' => 'desc'])) }}"
                   class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                    <span class="text-teal-600">↓</span> По убыванию
                </a>
            @else
                <a href="{{ route('equipment.index', array_merge($baseQuery, ['sort_by' => $sortKey, 'sort_dir' => 'asc'])) }}"
                   class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                    <span class="text-teal-600">A→Я</span> Сортировка от А до Я
                </a>
                <a href="{{ route('equipment.index', array_merge($baseQuery, ['sort_by' => $sortKey, 'sort_dir' => 'desc'])) }}"
                   class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-slate-700 hover:bg-slate-50 rounded-lg">
                    <span class="text-teal-600">Я→А</span> Сортировка от Я до А
                </a>
            @endif
            @if($filterParam && (is_array($currentFilter) ? count($currentFilter) > 0 : (string)$currentFilterText !== ''))
                @php $queryWithoutThis = collect($baseQuery)->filter(fn($v) => $v !== null && $v !== '')->except($filterParam)->all(); @endphp
                <a href="{{ route('equipment.index', $queryWithoutThis) }}"
                   class="flex items-center gap-2 w-full px-2 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg">
                    Снять фильтр с «{{ $columnLabel }}»
                </a>
            @endif
        </div>
        <div class="px-3 py-2">
            @if(!$filterParam)
                <p class="text-xs text-slate-500">Доступна только сортировка.</p>
            @elseif($filterType === 'checkboxes' && count($options) > 0)
                <form method="get" action="{{ route('equipment.index') }}" x-data="{ searchFilter: '' }">
                    @foreach($baseQuery as $k => $v)
                        @if(is_array($v))
                            @foreach($v as $vv)
                                <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                            @endforeach
                        @elseif($k !== $filterParam && $v !== null && $v !== '')
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach
                    <input type="search"
                           x-model="searchFilter"
                           placeholder="Поиск..."
                           class="w-full mb-2 text-sm border-slate-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                    <div class="max-h-48 overflow-y-auto space-y-1 mb-3" x-data="{ selectAll: true }">
                        @foreach($options as $opt)
                            @php
                                $optId = is_array($opt) ? ($opt['id'] ?? '') : $opt->id;
                                $optLabel = is_array($opt) ? ($opt['label'] ?? $opt['name'] ?? $opt['number'] ?? '') : ($opt->name ?? $opt->number ?? '');
                            @endphp
                            <label class="flex items-center gap-2 px-2 py-1 text-sm hover:bg-slate-50 rounded cursor-pointer"
                                   x-show="!searchFilter || ($el.dataset.label || '').toLowerCase().includes(searchFilter.toLowerCase())"
                                   data-label="{{ e($optLabel) }}">
                                <input type="checkbox"
                                       name="{{ $filterParam }}[]"
                                       value="{{ $optId }}"
                                       {{ in_array((string)$optId, array_map('strval', is_array($currentFilter) ? $currentFilter : [])) ? 'checked' : '' }}
                                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                <span>{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg">OK</button>
                        <button type="button" @click="open = false" class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Отмена</button>
                    </div>
                </form>
            @else
                <form method="get" action="{{ route('equipment.index') }}">
                    @foreach($baseQuery as $k => $v)
                        @if($k !== $filterParam && $v !== null && $v !== '')
                            @if(is_array($v))
                                @foreach($v as $vv)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $vv }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endif
                    @endforeach
                    <p class="text-xs text-slate-500 mb-2">Текстовый фильтр</p>
                    <input type="text"
                           name="{{ $filterParam }}"
                           value="{{ is_array($currentFilter) ? '' : (string)$currentFilterText }}"
                           placeholder="Введите значение..."
                           class="w-full mb-3 text-sm border-slate-300 rounded-lg focus:border-teal-500 focus:ring-teal-500">
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 px-3 py-1.5 text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 rounded-lg">OK</button>
                        <button type="button" @click="open = false" class="px-3 py-1.5 text-sm text-slate-600 hover:bg-slate-100 rounded-lg">Отмена</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
