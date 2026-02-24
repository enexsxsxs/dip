<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('dashboard') }}" class="text-slate-600 hover:text-teal-700 font-medium">← Справочники</a>
            <h2 class="font-semibold text-xl text-slate-800 leading-tight">Отделы</h2>
        </div>
    </x-slot>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
            <div class="p-6 border-b border-slate-200">
                <form method="post" action="{{ route('departments.store') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <label for="name" class="block text-sm font-medium text-slate-700 mb-1">Название отдела</label>
                        <input type="text" id="name" name="name" required maxlength="155" value="{{ old('name') }}"
                               class="w-full rounded-xl border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-sm"
                               placeholder="Например: Терапевтическое">
                        <x-input-error :messages="$errors->get('name')" class="mt-1" />
                    </div>
                    <button type="submit" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 transition">Добавить</button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600 uppercase">Название</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600 uppercase w-28">Действия</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse($departments as $item)
                            <tr class="hover:bg-teal-50/50">
                                <td class="px-4 py-3 text-sm text-slate-800">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-right">
                                    @if($item->equipment_count > 0)
                                        <span class="text-xs text-slate-500" title="Нельзя удалить: к этому отделу привязано {{ $item->equipment_count }} ед. оборудования">Привязано оборудование ({{ $item->equipment_count }})</span>
                                    @else
                                        <form method="post" action="{{ route('departments.destroy', $item) }}" class="inline" onsubmit="return confirm('Удалить этот отдел?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">Удалить</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-slate-500 text-sm">Пока нет записей. Добавьте первый отдел.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
