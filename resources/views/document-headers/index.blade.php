<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Макеты шапок документов</h2>
            <a href="{{ route('document-headers.create') }}"
               class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">
                Новый макет шапки
            </a>
        </div>
    </x-slot>

    @if(session('success'))
        <div class="max-w-4xl mx-auto mb-4 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 text-teal-900">{{ session('success') }}</div>
    @endif
    @if(session('deleted'))
        <div class="max-w-4xl mx-auto mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-950">{{ session('deleted') }}</div>
    @endif
    @if(session('error'))
        <div class="max-w-4xl mx-auto mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-rose-900">{{ session('error') }}</div>
    @endif

    <div class="bg-white/95 rounded-2xl shadow-sm border border-teal-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-base">
                <thead class="bg-teal-50 text-teal-900 text-left text-sm uppercase tracking-wide">
                    <tr>
                        <th class="px-4 py-3">Название</th>
                        <th class="px-4 py-3 text-right">Действия</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-teal-100">
                    @forelse($headers as $header)
                        <tr class="hover:bg-teal-50/50">
                            <td class="px-4 py-3 font-medium text-slate-800">{{ $header->title }}</td>
                            <td class="px-4 py-3 text-right space-x-3">
                                <a href="{{ route('document-headers.edit', $header) }}" class="text-teal-700 font-semibold hover:underline">Изменить</a>
                                <form action="{{ route('document-headers.destroy', $header) }}" method="post" class="inline" onsubmit="return confirm('Скрыть макет шапки из списка?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 font-semibold hover:underline">Скрыть</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-slate-500">Макетов шапок пока нет. Создайте первый — затем выберите его в макете заявки.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-teal-100">{{ $headers->links() }}</div>
    </div>
</x-app-layout>
