<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                Заявки
                @if($pendingCount > 0)
                    <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-base font-semibold bg-amber-100 text-amber-800 border-2 border-amber-200">
                        Ожидают: {{ $pendingCount }}
                    </span>
                @endif
            </h2>
            <a href="{{ route('dashboard') }}"
               class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-teal-700 bg-teal-50 border-2 border-teal-200 hover:bg-teal-100 transition">
                ← На главную
            </a>
        </div>
    </x-slot>

    <div class="space-y-6 pb-8">
        @if(session('success'))
            <div class="rounded-xl bg-teal-50 border-2 border-teal-200 text-teal-800 px-5 py-4 text-base font-medium">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="rounded-xl bg-red-50 border-2 border-red-200 text-red-800 px-5 py-4 text-base font-medium">
                {{ session('error') }}
            </div>
        @endif

        {{-- Фильтры --}}
        <div class="bg-white rounded-2xl shadow-lg border-2 border-teal-100 p-5">
            <form method="get" action="{{ route('equipment-requests.index') }}" class="flex flex-wrap items-end gap-4">
                <div>
                    <label for="type" class="block text-base font-semibold text-slate-600 mb-2">Тип</label>
                    <select id="type" name="type" class="rounded-xl border-2 border-slate-300 shadow-sm text-base py-2.5 px-3">
                        <option value="">Все</option>
                        <option value="writeoff" @selected($filterType === 'writeoff')>Списание</option>
                        <option value="move" @selected($filterType === 'move')>Перемещение</option>
                    </select>
                </div>
                <div>
                    <label for="status" class="block text-base font-semibold text-slate-600 mb-2">Статус</label>
                    <select id="status" name="status" class="rounded-xl border-2 border-slate-300 shadow-sm text-base py-2.5 px-3">
                        <option value="">Все</option>
                        <option value="pending" @selected($filterStatus === 'pending')>Ожидает</option>
                        <option value="approved" @selected($filterStatus === 'approved')>Выполнено</option>
                        <option value="rejected" @selected($filterStatus === 'rejected')>Отклонено</option>
                    </select>
                </div>
                <button type="submit" class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition">Показать</button>
                <a href="{{ route('equipment-requests.index') }}" class="inline-flex items-center min-h-[48px] py-3 text-base font-medium text-slate-600 hover:text-teal-600">Сбросить</a>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-lg border-2 border-teal-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Дата</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Тип</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Оборудование</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Откуда / Куда</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Автор</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase">Статус</th>
                            <th class="px-4 py-3.5 text-left text-sm font-bold text-slate-700 uppercase w-48">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($requests as $req)
                            <tr class="hover:bg-teal-50/50 transition">
                                <td class="px-4 py-3 text-base text-slate-600 whitespace-nowrap">
                                    {{ $req->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-4 py-3 text-base">
                                    @if($req->type === 'writeoff')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-sm font-semibold bg-red-50 text-red-700 border border-red-200">Списание</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded text-sm font-semibold bg-teal-50 text-teal-700 border border-teal-200">Перемещение</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-base font-semibold text-slate-800">
                                    <a href="{{ route('equipment.show', $req->equipment) }}" class="text-teal-600 hover:text-teal-800 hover:underline">
                                        {{ $req->equipment?->name ?? '—' }}
                                    </a>
                                    @if($req->comment)
                                        <p class="text-sm text-slate-500 mt-1">{{ Str::limit($req->comment, 50) }}</p>
                                    @endif
                                    @if($req->photo)
                                        <p class="mt-1">
                                            <a href="{{ asset('storage/' . $req->photo) }}" target="_blank" class="inline-flex items-center text-xs text-teal-600 hover:text-teal-800 hover:underline">
                                                Фото причины списания
                                            </a>
                                        </p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-base text-slate-600">
                                    @if($req->type === 'move')
                                        {{ $req->fromDepartment?->name ?? '—' }} → {{ $req->toDepartment?->name ?? '—' }}
                                    @else
                                        {{ $req->fromDepartment?->name ?? '—' }}
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-base text-slate-600">{{ $req->user?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-base">
                                    @if($req->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-amber-50 text-amber-700 border-2 border-amber-200">Ожидает</span>
                                    @elseif($req->status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-teal-50 text-teal-700 border-2 border-teal-200">Выполнено</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-600">Отклонено</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-base">
                                    @if($req->status === 'pending')
                                        <div class="flex flex-wrap gap-2">
                                        @if($req->type === 'writeoff')
                                            <form method="post" action="{{ route('equipment.requests.approveWriteoff', $req->equipment) }}" class="inline" onsubmit="return confirm('Подтвердить списание?');">
                                                @csrf
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200 border border-teal-300">Подтвердить списание</button>
                                            </form>
                                        @else
                                            <form method="post" action="{{ route('equipment-requests.approveMove', $req) }}" class="inline" onsubmit="return confirm('Выполнить перемещение в отделение «{{ $req->toDepartment?->name }}»?');">
                                                @csrf
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200 border border-teal-300">Выполнить перемещение</button>
                                            </form>
                                        @endif
                                        <form method="post" action="{{ route('equipment-requests.reject', $req) }}" class="inline" onsubmit="return confirm('Отклонить заявку?');">
                                            @csrf
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-red-700 bg-red-50 hover:bg-red-100 border border-red-200">Отклонить</button>
                                        </form>
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-base text-slate-500">
                                    Заявок нет. Заявки на списание и перемещение от старшей медсестры появятся здесь.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->hasPages())
                <div class="px-4 py-3 border-t border-slate-200 bg-slate-50">
                    {{ $requests->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
