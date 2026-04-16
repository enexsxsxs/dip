<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Макет: {{ $layout->title }}</h2>
    </x-slot>

    <div class="layout-form-block max-w-4xl mx-auto bg-white rounded-2xl border-2 border-slate-300 p-8 sm:p-10">
        @if($layout->trashed())
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-4 mb-6 text-base text-amber-950">
                Этот макет <strong>скрыт из списка</strong>. Вы можете изменить его здесь: после сохранения он снова появится в списке макетов и в выборе при создании заявки. Либо откройте <a href="{{ route('admin.activity-archive') }}" class="font-semibold text-teal-800 underline">Архив и журнал</a> и восстановите макет без правок.
            </div>
        @endif
        <form method="post" action="{{ route('report-layouts.update', $layout) }}"
              x-data="reportLayoutForm(@js($initialSchema), @js($headerSourceLayouts ?? []), @js($footerPickUsers ?? []))"
              @submit="prepareSubmit()"
              class="space-y-8">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-slate-600 mb-2">Название макета</label>
                <input type="text" name="title" id="title" value="{{ old('title', $layout->title) }}" required
                       class="w-full rounded-xl border border-slate-200 bg-white shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none text-base min-h-[48px] px-4 py-2.5 transition-colors">
                @error('title')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-center gap-3">
                <input type="hidden" name="has_header" value="0">
                <input type="checkbox" name="has_header" id="has_header" value="1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30 w-5 h-5" @checked(old('has_header', $layout->has_header))>
                <label for="has_header" class="text-base text-slate-700 leading-relaxed">Нужна шапка заявления (до трёх блоков в форме)</label>
            </div>

            @include('report-layouts.partials.schema-builder')

            @error('schema')<p class="text-rose-600 text-sm">{{ $message }}</p>@enderror

            <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="min-h-[48px] px-8 rounded-xl bg-teal-700 text-white font-medium hover:bg-teal-800 transition-colors shadow-sm">Сохранить</button>
                <a href="{{ route('report-layouts.index') }}" class="inline-flex items-center min-h-[48px] px-8 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors">Назад</a>
            </div>
        </form>
    </div>
</x-app-layout>
