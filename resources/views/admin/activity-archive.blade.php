<x-app-layout>
    @php
        $filtersActive = $filteredCount !== null;
    @endphp
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Архив и журнал действий
            </h2>
            <a href="{{ route('dashboard') }}" class="text-teal-600 hover:text-teal-800 text-sm font-medium">← На главную</a>
        </div>
    </x-slot>

    <div class="py-8 space-y-10 max-w-6xl mx-auto sm:px-6 lg:px-8">
        @if (session('status'))
            <div class="rounded-lg bg-teal-50 border border-teal-200 text-teal-800 px-4 py-3 text-sm">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        @if ($inactiveUsers->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-amber-400">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Уволенные сотрудники (доступ отключён)</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Учётные записи сохранены в базе. Можно восстановить доступ кнопкой ниже или из раздела «Пользователи».</p>
                <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach ($inactiveUsers as $u)
                        <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $u->name }}</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400 block">{{ $u->email }}</span>
                                <span class="text-xs text-gray-500">{{ $u->role_label ?? '—' }}</span>
                            </div>
                            <div class="flex flex-wrap gap-2 items-center">
                                <a href="{{ route('users.edit', $u) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Редактировать</a>
                                <form method="post" action="{{ route('users.restore', $u) }}" class="inline"
                                      onsubmit="return confirm('Восстановить доступ этого сотрудника?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                                        Восстановить доступ
                                    </button>
                                </form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($trashedEquipment->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Удалённые из списка оборудования (можно восстановить)</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Записи скрыты из реестра; файлы и карточка сохранены. Восстановление вернёт позицию в список.</p>
                <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                    @foreach ($trashedEquipment as $item)
                        <li class="py-3 flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="font-medium text-gray-900 dark:text-gray-100">№{{ $item->number }} — {{ $item->name }}</span>
                                <span class="text-xs text-gray-500 block mt-0.5">Удалено: {{ $item->deleted_at?->format('d.m.Y H:i') ?? '—' }}</span>
                            </div>
                            <form method="post" action="{{ route('admin.activity-archive.restore', $item->id) }}" class="inline"
                                  onsubmit="return confirm('Восстановить это оборудование в списке?');">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-lg hover:bg-teal-700">
                                    Восстановить
                                </button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($trashedEquipmentTypes->isNotEmpty() || $trashedDepartments->isNotEmpty() || $trashedCabinets->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 border-l-4 border-violet-400">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Удалённые справочники</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Виды оборудования, отделы и кабинеты скрыты из списков настроек, но остаются в базе. Восстановление вернёт их в выбор при редактировании оборудования и в заявках.</p>

                @if ($trashedEquipmentTypes->isNotEmpty())
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-4 mb-2">Виды оборудования</h4>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-600 mb-4">
                        @foreach ($trashedEquipmentTypes as $item)
                            <li class="py-2 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</span>
                                    <span class="text-xs text-gray-500 block">Скрыто: {{ $item->deleted_at?->format('d.m.Y H:i') ?? '—' }}</span>
                                </div>
                                <form method="post" action="{{ route('admin.activity-archive.restore-equipment-type', $item->id) }}" class="inline"
                                      onsubmit="return confirm('Восстановить этот вид в справочнике?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700">Восстановить</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($trashedDepartments->isNotEmpty())
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-4 mb-2">Отделы</h4>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-600 mb-4">
                        @foreach ($trashedDepartments as $item)
                            <li class="py-2 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $item->name }}</span>
                                    <span class="text-xs text-gray-500 block">Скрыто: {{ $item->deleted_at?->format('d.m.Y H:i') ?? '—' }}</span>
                                </div>
                                <form method="post" action="{{ route('admin.activity-archive.restore-department', $item->id) }}" class="inline"
                                      onsubmit="return confirm('Восстановить этот отдел в справочнике?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700">Восстановить</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if ($trashedCabinets->isNotEmpty())
                    <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mt-4 mb-2">Кабинеты / помещения</h4>
                    <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                        @foreach ($trashedCabinets as $item)
                            <li class="py-2 flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <span class="font-medium text-gray-900 dark:text-gray-100">№{{ $item->number }}</span>
                                    <span class="text-xs text-gray-500 block">Скрыто: {{ $item->deleted_at?->format('d.m.Y H:i') ?? '—' }}</span>
                                </div>
                                <form method="post" action="{{ route('admin.activity-archive.restore-cabinet', $item->id) }}" class="inline"
                                      onsubmit="return confirm('Восстановить этот кабинет в справочнике?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-3 py-1.5 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700">Восстановить</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Единый журнал</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Фильтры помогают найти записи. Удаление из списков (оборудование, справочники) и увольнения можно отменить блоками выше. В журнале — только удаление строк истории.</p>
            </div>

            <form method="get" action="{{ route('admin.activity-archive') }}" class="rounded-lg border border-gray-200 dark:border-gray-600 p-4 bg-gray-50/80 dark:bg-gray-900/40">
                <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 mb-3">Фильтры</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label for="filter_q" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Поиск по тексту (объект, подробности)</label>
                        <input type="search" id="filter_q" name="q" value="{{ request('q') }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm" placeholder="Например: номер, ФИО, комментарий…">
                    </div>
                    <div>
                        <label for="filter_entity_type" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Тип объекта</label>
                        <select id="filter_entity_type" name="entity_type" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                            <option value="">Все типы</option>
                            @foreach ($entityLabels as $class => $label)
                                <option value="{{ $class }}" @selected(request('entity_type') === $class)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_action" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Действие</label>
                        <select id="filter_action" name="action" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                            <option value="">Все действия</option>
                            @foreach ($actionLabels as $code => $label)
                                <option value="{{ $code }}" @selected(request('action') === $code)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_user_id" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Сотрудник</label>
                        <select id="filter_user_id" name="user_id" class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                            <option value="">Все</option>
                            @foreach ($usersForFilter as $fu)
                                <option value="{{ $fu->id }}" @selected((string) request('user_id') === (string) $fu->id)>{{ $fu->name }} — {{ $fu->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="filter_date_from" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Дата с</label>
                        <input type="date" id="filter_date_from" name="date_from" value="{{ request('date_from') }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                    </div>
                    <div>
                        <label for="filter_date_to" class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Дата по</label>
                        <input type="date" id="filter_date_to" name="date_to" value="{{ request('date_to') }}"
                               class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-sm">
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="px-4 py-2 bg-teal-600 text-white text-sm font-medium rounded-md hover:bg-teal-700">Применить</button>
                    <a href="{{ route('admin.activity-archive') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm rounded-md text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Сбросить</a>
                </div>
                @if ($filtersActive)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">По фильтру найдено записей: <strong>{{ $filteredCount }}</strong></p>
                @endif
            </form>

            <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-600 text-left text-gray-500 dark:text-gray-400">
                                <th class="py-2 pr-4">Когда</th>
                                <th class="py-2 pr-4">Сотрудник</th>
                                <th class="py-2 pr-4">Тип</th>
                                <th class="py-2 pr-4">Объект</th>
                                <th class="py-2 pr-4">Действие</th>
                                <th class="py-2 pr-4 w-36">Версия</th>
                                <th class="py-2">Подробности</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            @forelse ($entries as $row)
                                @php
                                    $objectTitle = $row->title ?: $row->new_value ?: '—';
                                    $typeLabel = $entityLabels[$row->entity_type] ?? class_basename($row->entity_type);
                                    $canRestoreVersion = $row->entity_type === \App\Models\Equipment::class
                                        && $row->entity_id
                                        && $row->action === 'updated'
                                        && ! empty($row->snapshot)
                                        && \App\Models\Equipment::query()->whereKey($row->entity_id)->whereNull('deleted_at')->exists();
                                @endphp
                                <tr class="align-top">
                                    <td class="py-3 pr-4 whitespace-nowrap text-gray-700 dark:text-gray-300">
                                        {{ $row->occurred_at?->format('d.m.Y H:i') ?? '—' }}
                                    </td>
                                    <td class="py-3 pr-4 text-gray-800 dark:text-gray-200">
                                        {{ $row->user?->name ?? '—' }}
                                        @if($row->user)
                                            <span class="block text-xs text-gray-500">{{ $row->user->email }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                                        {{ $typeLabel }}
                                    </td>
                                    <td class="py-3 pr-4">
                                        @if ($row->entity_type === \App\Models\Equipment::class && $row->entity_id)
                                            @php $eqRow = \App\Models\Equipment::withTrashed()->find($row->entity_id); @endphp
                                            @if ($eqRow)
                                                @if ($eqRow->trashed())
                                                    <span class="text-gray-600 dark:text-gray-400">{{ $objectTitle }} <span class="text-xs">(в архиве)</span></span>
                                                @else
                                                    <a href="{{ route('equipment.show', $eqRow) }}" class="text-teal-600 hover:underline font-medium">{{ $objectTitle }}</a>
                                                @endif
                                            @else
                                                <span class="text-gray-500">{{ $objectTitle }}</span>
                                            @endif
                                        @elseif ($row->entity_type === \App\Models\User::class && $row->entity_id)
                                            @php $uRow = \App\Models\User::find($row->entity_id); @endphp
                                            @if ($uRow)
                                                <a href="{{ route('users.edit', $uRow) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">{{ $objectTitle }}</a>
                                            @else
                                                <span class="text-gray-500">{{ $objectTitle }}</span>
                                            @endif
                                        @else
                                            <span class="text-gray-800 dark:text-gray-200">{{ $objectTitle }}</span>
                                        @endif
                                    </td>
                                    <td class="py-3 pr-4">
                                        @php
                                            $labels = [
                                                'writeoff_approved' => 'Списание подтверждено',
                                                'utilized' => 'Утилизировано',
                                                'move_approved' => 'Перемещение подтверждено',
                                                'deleted' => 'Удалено',
                                                'restored' => 'Восстановлено',
                                                'deactivated' => 'Уволен (доступ отключён)',
                                                'created' => 'Создано',
                                                'updated' => 'Изменено',
                                                'rejected' => 'Отклонено',
                                                'revision_restored' => 'Восстановлена версия',
                                            ];
                                            $label = $labels[$row->action] ?? $row->action;
                                        @endphp
                                        <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold
                                            @if($row->action === 'deleted' || $row->action === 'deactivated') bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200
                                            @elseif($row->action === 'restored' || $row->action === 'revision_restored') bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-200
                                            @elseif($row->action === 'created') bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200
                                            @elseif($row->action === 'rejected') bg-orange-100 text-orange-900 dark:bg-orange-900/40 dark:text-orange-100
                                            @else bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 @endif">
                                            {{ $label }}
                                        </span>
                                    </td>
                                    <td class="py-3 pr-4 align-top">
                                        @if ($canRestoreVersion)
                                            <form method="post" action="{{ route('admin.activity-archive.restore-revision', $row) }}" class="inline"
                                                  onsubmit="return confirm('Вернуть карточку, фото и документы к состоянию до этого изменения? Текущие данные будут заменены.');">
                                                @csrf
                                                @foreach ($filterKeys as $key)
                                                    @if (request()->filled($key))
                                                        <input type="hidden" name="{{ $key }}" value="{{ request($key) }}">
                                                    @endif
                                                @endforeach
                                                <button type="submit" class="text-left text-xs font-semibold text-violet-700 dark:text-violet-300 hover:underline">
                                                    Восстановить эту версию
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-600 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-gray-600 dark:text-gray-400 max-w-md break-words">
                                        @if ($row->field_name)
                                            <span class="text-xs font-mono">{{ $row->field_name }}</span>
                                        @endif
                                        @if ($row->details)
                                            <p class="text-xs mt-1">{{ \Illuminate\Support\Str::limit($row->details, 320) }}</p>
                                        @endif
                                        @if ($row->snapshot)
                                            <p class="text-xs text-amber-700 dark:text-amber-300 mt-1">Есть снимок данных (аудит).</p>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-500">Записей не найдено. Измените фильтры или сбросьте их.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            <div class="mt-4">{{ $entries->links() }}</div>
        </div>
    </div>
</x-app-layout>
