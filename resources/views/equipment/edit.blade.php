<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-4">
            <a href="{{ route('equipment.index') }}" class="inline-flex items-center min-h-[48px] py-2 text-base font-semibold text-slate-600 hover:text-teal-700">
                ← Список оборудования
            </a>
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
                Редактирование оборудования
            </h2>
        </div>
    </x-slot>

    <div class="pb-8">
        <form method="POST" action="{{ route('equipment.update', $equipment) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('equipment._form', ['equipment' => $equipment])
        </form>
    </div>
</x-app-layout>
