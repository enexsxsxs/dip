<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Заявки по макетам</h2>
            <a href="{{ route('report-requests.create') }}"
               class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">
                Новая заявка
            </a>
        </div>
    </x-slot>

    <div class="bg-white/95 rounded-2xl shadow-sm border border-teal-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-teal-50 text-teal-900 text-left text-sm uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3">№</th>
                        <th class="px-4 py-3">Макет</th>
                        <th class="px-4 py-3">Автор</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-teal-100">
                    @forelse($records as $record)
                        <tr class="hover:bg-teal-50/50">
                            <td class="px-4 py-3 text-slate-600">{{ $record->registry_number ?? $record->id }}</td>
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $record->layout?->title ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $record->author?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('report-requests.show', $record) }}" class="text-teal-700 font-semibold hover:underline">Открыть</a>
                                <a href="{{ route('report-requests.edit', $record) }}" class="text-indigo-700 font-semibold hover:underline">Изменить</a>
                                <a href="{{ route('report-requests.pdf', $record) }}" class="text-slate-700 font-semibold hover:underline">PDF</a>
                                <form action="{{ route('report-requests.destroy', $record) }}" method="post" class="inline"
                                      onsubmit="return confirm('Скрыть заявку из списка? Запись останется в базе; восстановление и правки — в «Архив и журнал».');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 font-semibold hover:underline">Скрыть</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Заявок нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-teal-100">{{ $records->links() }}</div>
    </div>
</x-app-layout>
