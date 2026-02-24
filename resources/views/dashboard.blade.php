<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-slate-800 leading-tight">
            Главная — Система учета медицинского оборудования
        </h2>
    </x-slot>

    <div class="space-y-8">
        {{-- Три блока по прототипу: статистика и кнопка перехода к списку --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Карточка: всего оборудования --}}
            <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-teal-500 to-cyan-500"></div>
                <div class="p-6">
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Всего оборудования</p>
                    <p class="mt-2 text-4xl font-bold text-teal-700">{{ $equipmentCount }}</p>
                    <p class="mt-1 text-sm text-slate-500">единиц на учёте</p>
                </div>
            </div>

            {{-- Карточка: всего отделений --}}
            <div class="bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden">
                <div class="h-1.5 bg-gradient-to-r from-cyan-500 to-teal-400"></div>
                <div class="p-6">
                    <p class="text-sm font-medium text-slate-500 uppercase tracking-wide">Всего отделений</p>
                    <p class="mt-2 text-4xl font-bold text-teal-700">{{ $departmentCount }}</p>
                    <p class="mt-1 text-sm text-slate-500">отделений в системе</p>
                </div>
            </div>

            {{-- Карточка-кнопка: список оборудования (кликабельная) --}}
            <a href="{{ route('equipment.index') }}"
               class="group block bg-white rounded-2xl shadow-lg border border-teal-100 overflow-hidden hover:border-teal-200 hover:shadow-xl transition-all duration-200">
                <div class="h-1.5 bg-gradient-to-r from-teal-600 to-cyan-600"></div>
                <div class="p-6 flex flex-col items-center justify-center min-h-[120px] text-center">
                    <span class="flex items-center justify-center w-14 h-14 rounded-xl bg-teal-100 text-teal-600 group-hover:bg-teal-600 group-hover:text-white transition-colors">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </span>
                    <span class="mt-3 text-lg font-semibold text-slate-800 group-hover:text-teal-700">Список оборудования</span>
                    <span class="mt-1 text-sm text-slate-500">Перейти к полному списку</span>
                </div>
            </a>
        </div>
    </div>
</x-app-layout>
