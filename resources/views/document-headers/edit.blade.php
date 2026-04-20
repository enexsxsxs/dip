<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Макет шапки: {{ $header->title }}</h2>
    </x-slot>

    <div class="layout-form-block max-w-4xl mx-auto bg-white rounded-2xl border-2 border-slate-300 p-8 sm:p-10">
        @if($header->trashed())
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-4 mb-6 text-base text-amber-950">
                Этот макет шапки <strong>скрыт из списка</strong>. После сохранения он снова появится в списке.
            </div>
        @endif

        <form method="post" action="{{ route('document-headers.update', $header) }}"
              x-data="documentHeaderForm(@js($initialSchema), @js($headerRoleSigners ?? []))"
              @submit="prepareSubmit()"
              class="space-y-8">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="block text-sm font-medium text-slate-600 mb-2">Название макета шапки</label>
                <input type="text" name="title" id="title" value="{{ old('title', $header->title) }}" required
                       class="w-full rounded-xl border border-slate-200 bg-white shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none text-base min-h-[48px] px-4 py-2.5 transition-colors">
                @error('title')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            @include('document-headers.partials.header-editor')

            @error('schema')<p class="text-rose-600 text-sm">{{ $message }}</p>@enderror

            <div class="flex flex-wrap gap-3 pt-4 border-t border-slate-100">
                <button type="submit" class="min-h-[48px] px-8 rounded-xl bg-teal-700 text-white font-medium hover:bg-teal-800 transition-colors shadow-sm">Сохранить</button>
                <a href="{{ route('document-headers.index') }}" class="inline-flex items-center min-h-[48px] px-8 rounded-xl border border-slate-300 text-slate-700 font-medium hover:bg-slate-50 transition-colors">Назад</a>
            </div>
        </form>
    </div>
</x-app-layout>
