<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Макеты заявок (PDF)</h2>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('document-headers.index') }}"
                   class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl border border-teal-600 text-teal-800 font-semibold hover:bg-teal-50 transition">
                    Макеты шапок
                </a>
                <a href="{{ route('report-layouts.create') }}"
                   class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">
                    Новый макет
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white/95 rounded-2xl shadow-sm border border-teal-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-teal-50 text-teal-900 text-left text-sm uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3">Шапка</th>
                        <th class="px-4 py-3 hidden sm:table-cell">Макет шапки</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-teal-100">
                    @forelse($layouts as $layout)
                        <tr class="hover:bg-teal-50/50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $layout->title }}</td>
                            <td class="px-4 py-3">
                                @if($layout->has_header)
                                    <span class="text-teal-700 font-medium">Да</span>
                                @else
                                    <span class="text-slate-400">Нет</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-slate-700">
                                @if($layout->has_header && $layout->documentHeader)
                                    <span class="font-medium text-slate-800">{{ $layout->documentHeader->title }}</span>
                                @elseif($layout->has_header)
                                    <span class="text-slate-500 text-sm">встроенная (старый формат)</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('report-layouts.edit', $layout) }}" class="text-teal-700 font-semibold hover:underline">Изменить</a>
                                <form action="{{ route('report-layouts.destroy', $layout) }}" method="post" class="inline" onsubmit="return confirm('Скрыть макет из списка? Запись останется в базе; восстановление и правки — в разделе «Архив и журнал».');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 font-semibold hover:underline">Скрыть</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500">Макетов пока нет. Создайте первый или выполните сидер демо-данных.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-teal-100">{{ $layouts->links() }}</div>
    </div>
</x-app-layout>
