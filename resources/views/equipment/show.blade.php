@php
    $images = $equipment->images;
    $docInstruction = $equipment->documents->first(fn($d) => ($d->type ?? '') === 'instruction' || stripos($d->name ?? '', 'инструкц') !== false);
    $docRuScan = $equipment->documents->first(fn($d) => ($d->type ?? '') === 'ru_scan' || stripos($d->name ?? '', 'скан') !== false || stripos($d->name ?? '', 'удостоверен') !== false);
    $shownDocIds = array_filter([$docInstruction?->id, $docRuScan?->id]);
    $otherDocs = $equipment->documents->whereNotIn('id', $shownDocIds);
@endphp
<x-app-layout>
    <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-teal-100 overflow-hidden">
            {{-- Заголовок: название + кнопка закрыть --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-200 bg-slate-50">
                <h1 class="text-xl font-semibold text-slate-800 truncate pr-4">{{ $equipment->name }}</h1>
                <a href="{{ route('equipment.index') }}" class="shrink-0 flex items-center justify-center w-10 h-10 rounded-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition" title="Закрыть">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </a>
            </div>

            <div class="flex flex-col lg:flex-row">
                {{-- Левая колонка: галерея + документы --}}
                <div class="lg:w-2/3 p-6 border-b lg:border-b-0 lg:border-r border-slate-200">
                    {{-- Галерея фото --}}
                    <div class="mb-6" x-data="{ current: 0, urls: {{ json_encode($images->map(fn($i) => asset('storage/' . $i->image))->values()->all()) }} }">
                        @if($images->isNotEmpty())
                            <div class="relative bg-slate-100 rounded-xl overflow-hidden flex items-center justify-center min-h-[280px]">
                                <img :src="urls[current]" alt="Фото оборудования" class="w-full h-full max-h-[280px] object-contain p-4">
                                @if($images->count() > 1)
                                    <button type="button" @click="current = current === 0 ? {{ $images->count() - 1 }} : current - 1"
                                            class="absolute left-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-teal-50 hover:text-teal-700 transition z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                    </button>
                                    <button type="button" @click="current = current === {{ $images->count() - 1 }} ? 0 : current + 1"
                                            class="absolute right-2 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-white/90 shadow border border-slate-200 flex items-center justify-center text-slate-600 hover:bg-teal-50 hover:text-teal-700 transition z-10">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                    <div class="absolute bottom-2 left-1/2 -translate-x-1/2 flex gap-1.5 z-10">
                                        @foreach($images as $i => $img)
                                            <button type="button" class="w-2.5 h-2.5 rounded-full transition" :class="current === {{ $i }} ? 'bg-teal-600 scale-110' : 'bg-white/80 hover:bg-white'" @click="current = {{ $i }}" aria-label="Фото {{ $i + 1 }}"></button>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="bg-slate-100 rounded-xl min-h-[200px] flex items-center justify-center text-slate-400 text-sm">
                                Нет фотографий
                            </div>
                        @endif
                    </div>

                    {{-- Документы PDF --}}
                    <div>
                        <h3 class="text-sm font-semibold text-slate-700 mb-3">Документы PDF</h3>
                        <div class="space-y-3">
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
                                <p class="text-xs font-medium text-slate-500 mb-1">1. Инструкция</p>
                                @if($docInstruction)
                                    <a href="{{ asset('storage/' . $docInstruction->document) }}" target="_blank" rel="noopener" download class="text-sm text-teal-600 hover:underline">Скачать {{ $docInstruction->name }}</a>
                                    @if(auth()->user()?->isAdmin())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="type" value="instruction">
                                            <input type="file" name="document" accept=".pdf,application/pdf" class="text-sm text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700 file:text-xs">
                                            <button type="submit" class="text-xs font-medium text-teal-600 hover:text-teal-800">Заменить</button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-sm text-slate-400">Нет загруженных документов</p>
                                    @if(auth()->user()?->isAdmin())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="type" value="instruction">
                                            <input type="file" name="document" accept=".pdf,application/pdf" required class="text-sm text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700 file:text-xs">
                                            <button type="submit" class="text-xs font-medium text-teal-600 hover:text-teal-800">Загрузить</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
                                <p class="text-xs font-medium text-slate-500 mb-1">2. Скан РУ</p>
                                @if($docRuScan)
                                    <a href="{{ asset('storage/' . $docRuScan->document) }}" target="_blank" rel="noopener" download class="text-sm text-teal-600 hover:underline">Скачать {{ $docRuScan->name }}</a>
                                    @if(auth()->user()?->isAdmin())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="type" value="ru_scan">
                                            <input type="file" name="document" accept=".pdf,application/pdf" class="text-sm text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700 file:text-xs">
                                            <button type="submit" class="text-xs font-medium text-teal-600 hover:text-teal-800">Заменить</button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-sm text-slate-400">Нет загруженных документов</p>
                                    @if(auth()->user()?->isAdmin())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-2 flex flex-wrap items-center gap-2">
                                            @csrf
                                            <input type="hidden" name="type" value="ru_scan">
                                            <input type="file" name="document" accept=".pdf,application/pdf" required class="text-sm text-slate-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-teal-50 file:text-teal-700 file:text-xs">
                                            <button type="submit" class="text-xs font-medium text-teal-600 hover:text-teal-800">Загрузить</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            @if($otherDocs->isNotEmpty())
                                @foreach($otherDocs as $doc)
                                    <div class="rounded-xl border border-slate-200 p-3 bg-slate-50">
                                        <a href="{{ asset('storage/' . $doc->document) }}" target="_blank" rel="noopener" class="text-sm text-teal-600 hover:underline">{{ $doc->name }}</a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Правая колонка: характеристики --}}
                <div class="lg:w-1/3 p-6 bg-slate-50/50">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Характеристики</h3>
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Наименование</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->name ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Инв. №</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->inventory_number ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Серийный №</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->serial_number ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Регистрационное удостоверение</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->registration_certificate ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Дата РУ</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->ru_date?->format('Y-m-d') ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Срок действия РУ</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->valid_until ?: $equipment->valid_to ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Год выпуска</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->year_of_manufacture ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Годен до</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->valid_to ?: $equipment->valid_until ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Отделение</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->department?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Кабинет</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->cabinet?->number ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Период поверки</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->verification_period ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Дата последней поверки</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->last_verification_date ?: '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Обслуживающая организация</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->serviceOrganization?->name ?? '—' }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Поставщик</dt>
                            <dd class="text-slate-800 text-right">{{ $equipment->supplier?->name ?? '—' }}</dd>
                        </div>
                    </dl>
                    @if(auth()->user()?->isAdmin())
                        <div class="mt-6 pt-4 border-t border-slate-200">
                            <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center px-4 py-2 rounded-xl text-sm font-medium text-white bg-teal-600 hover:bg-teal-700 transition">Редактировать</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
