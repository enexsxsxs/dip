<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">
            Главная — Система учета медицинского оборудования
        </h2>
    </x-slot>

    <div class="space-y-8">
        {{-- Верхняя большая кнопка --}}
        <div class="max-w-3xl">
            <a href="{{ route('equipment.index') }}"
               class="group block bg-white rounded-2xl shadow-lg border-2 border-teал-200 overflow-hidden hover:border-teal-400 hover:shadow-xl transition-all duration-200 min-h-[140px]">
                <div class="h-2 bg-gradient-to-r from-teal-600 to-cyan-600"></div>
                <div class="p-5 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center justify-center w-14 h-14 rounded-2xl bg-teал-100 text-teal-600 group-hover:bg-teал-600 group-hover:text-white transition-colors">
                            <svg class="w-9 h-9" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </span>
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Основной раздел</p>
                            <p class="mt-1 text-xl font-bold text-slate-800 group-hover:text-teal-700">Список оборудования</p>
                            <p class="mt-1 text-sm text-slate-500">Нажмите, чтобы перейти к таблице оборудования</p>
                        </div>
                    </div>
                    <span class="hidden md:inline-flex items-center text-sm font-semibold text-teal-700 group-hover:text-teal-800">
                        Открыть →
                    </span>
                </div>
            </a>
        </div>

        {{-- Нижний ряд: информационные карточки --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Инфо-карточка: всего оборудования (некликабельная) --}}
            <div class="bg-white/90 rounded-2xl shadow-sm border border-teal-100 overflow-hidden cursor-default">
                <div class="h-1.5 bg-gradient-to-r from-teal-500 to-cyan-500 opacity-80"></div>
                <div class="p-5 flex items-center gap-4">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-teal-50 text-teal-600">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="4" y="4" width="16" height="16" rx="3"></rect>
                            <path d="M9 9h6M9 13h4M9 17h2"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Всего оборудования</p>
                        <p class="mt-1 text-3xl font-bold text-teal-700 leading-none">{{ $equipmentCount }}</p>
                        <p class="mt-1 text-xs text-slate-500">единиц на учёте</p>
                    </div>
                </div>
            </div>

            {{-- Инфо-карточка: всего отделений (некликабельная) --}}
            <div class="bg-white/90 rounded-2xl shadow-sm border border-teal-100 overflow-hidden cursor-default">
                <div class="h-1.5 bg-gradient-to-r from-cyan-500 to-teal-400 opacity-80"></div>
                <div class="p-5 flex items-center gap-4">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-cyan-50 text-cyan-600">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 20h4V8H4zM10 20h4V4h-4zM16 20h4v-6h-4z"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Всего отделений</p>
                        <p class="mt-1 text-3xl font-bold text-teal-700 leading-none">{{ $departmentCount }}</p>
                        <p class="mt-1 text-xs text-slate-500">отделений в системе</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
