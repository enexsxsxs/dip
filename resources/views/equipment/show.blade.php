@php
    $images = $equipment->images;
    $docRegistration = $equipment->documents->first(fn($d) => in_array($d->type ?? '', ['registration_certificate', 'ru_scan']) || stripos($d->name ?? '', 'удостоверен') !== false || stripos($d->name ?? '', 'скан') !== false);
    $docInstruction = $equipment->documents->first(fn($d) => ($d->type ?? '') === 'instruction' || stripos($d->name ?? '', 'инструкц') !== false);
    $docCommissioningAct = $equipment->documents->first(fn($d) => ($d->type ?? '') === 'commissioning_act' || stripos($d->name ?? '', 'акт ввода') !== false);
    $docUtilizationAct = $equipment->documents->first(fn($d) => ($d->type ?? '') === 'utilization_act');
    $shownDocIds = array_filter([$docRegistration?->id, $docInstruction?->id, $docCommissioningAct?->id, $docUtilizationAct?->id]);
    $otherDocs = $equipment->documents->whereNotIn('id', $shownDocIds);
    $docAccept = '.pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
@endphp
<x-app-layout>
    <div class="max-w-5xl mx-auto">
        @if (session('warning'))
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3 text-sm" role="alert">
                {{ session('warning') }}
            </div>
        @endif
        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-800 px-4 py-3 text-sm" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->has('utilize'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm" role="alert">
                {{ $errors->first('utilize') }}
            </div>
        @endif
        @if ($errors->has('utilization_act'))
            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 text-red-800 px-4 py-3 text-sm" role="alert">
                {{ $errors->first('utilization_act') }}
            </div>
        @endif
        <div class="bg-white rounded-2xl shadow-xl border border-teal-100 overflow-hidden">
            {{-- Заголовок: название + кнопка закрыть --}}
            <div class="flex items-center justify-between px-6 py-5 border-b-2 border-slate-200 bg-slate-50">
                <h1 class="text-2xl font-bold text-slate-800 truncate pr-4">{{ $equipment->name }}</h1>
                <a href="{{ route('equipment.index') }}" class="shrink-0 flex items-center justify-center min-h-[48px] min-w-[48px] rounded-xl text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition text-base font-medium" title="Закрыть">Закрыть</a>
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
                            <ul class="mt-3 text-sm text-slate-500 space-y-1" title="Имена файлов в хранилище">
                                @foreach($images as $img)
                                    <li class="truncate">{{ basename($img->image) }}</li>
                                @endforeach
                            </ul>
                        @else
                            <div class="bg-slate-100 rounded-xl min-h-[200px] flex items-center justify-center text-slate-400 text-sm">
                                Нет фотографий
                            </div>
                        @endif
                    </div>

                    {{-- Документы (PDF, Word, Excel) --}}
                    <div>
                        <h3 class="text-lg font-bold text-slate-700 mb-4">Документы</h3>
                        <p class="text-base text-slate-500 mb-3">Форматы: PDF, Word (.doc, .docx), Excel (.xls, .xlsx). Можно загружать и скачивать.</p>
                        <div class="space-y-4">
                            <div class="rounded-xl border-2 border-slate-200 p-4 bg-slate-50">
                                <p class="text-base font-semibold text-slate-600 mb-2">1. Регистрационное удостоверение</p>
                                @if($docRegistration)
                                    <a href="{{ asset('storage/' . $docRegistration->document) }}" target="_blank" rel="noopener" download class="text-base font-medium text-teal-600 hover:underline">Скачать {{ $docRegistration->name }}</a>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="registration_certificate">
                                            <input type="file" name="document" accept="{{ $docAccept }}" class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Заменить</button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-sm text-slate-400">Нет загруженных документов</p>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="registration_certificate">
                                            <input type="file" name="document" accept="{{ $docAccept }}" required class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Загрузить</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            <div class="rounded-xl border-2 border-slate-200 p-4 bg-slate-50">
                                <p class="text-base font-semibold text-slate-600 mb-2">2. Инструкция на русском языке</p>
                                @if($docInstruction)
                                    <a href="{{ asset('storage/' . $docInstruction->document) }}" target="_blank" rel="noopener" download class="text-base font-medium text-teal-600 hover:underline">Скачать {{ $docInstruction->name }}</a>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="instruction">
                                            <input type="file" name="document" accept="{{ $docAccept }}" class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Заменить</button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-base text-slate-400">Нет загруженных документов</p>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="instruction">
                                            <input type="file" name="document" accept="{{ $docAccept }}" required class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Загрузить</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            <div class="rounded-xl border-2 border-slate-200 p-4 bg-slate-50">
                                <p class="text-base font-semibold text-slate-600 mb-2">3. Акт ввода в эксплуатацию</p>
                                @if($docCommissioningAct)
                                    <a href="{{ asset('storage/' . $docCommissioningAct->document) }}" target="_blank" rel="noopener" download class="text-base font-medium text-teal-600 hover:underline">Скачать {{ $docCommissioningAct->name }}</a>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="commissioning_act">
                                            <input type="file" name="document" accept="{{ $docAccept }}" class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Заменить</button>
                                        </form>
                                    @endif
                                @else
                                    <p class="text-base text-slate-400">Нет загруженных документов</p>
                                    @if(auth()->user()?->canManageEquipment())
                                        <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-3">
                                            @csrf
                                            <input type="hidden" name="type" value="commissioning_act">
                                            <input type="file" name="document" accept="{{ $docAccept }}" required class="text-base file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700">
                                            <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Загрузить</button>
                                        </form>
                                    @endif
                                @endif
                            </div>
                            @if($equipment->isUtilized())
                                <div class="rounded-xl border-2 border-violet-200 p-4 bg-violet-50/40">
                                    <p class="text-base font-semibold text-slate-700 mb-2">4. Акт утилизации</p>
                                    @if($docUtilizationAct)
                                        <div class="flex flex-wrap items-center gap-3">
                                            <a href="{{ asset('storage/' . $docUtilizationAct->document) }}" target="_blank" rel="noopener" class="text-base font-medium text-violet-700 hover:underline">Открыть в новой вкладке</a>
                                            <span class="text-slate-300">|</span>
                                            <a href="{{ asset('storage/' . $docUtilizationAct->document) }}" download class="text-base font-medium text-teal-600 hover:underline">Скачать файл</a>
                                        </div>
                                        <p class="text-xs text-slate-500 mt-2">Загружен: {{ $docUtilizationAct->uploaded_at?->format('d.m.Y H:i') ?? '—' }}</p>
                                        @if(auth()->user()?->canManageUtilization())
                                            <form method="post" action="{{ route('equipment.utilization-act.store', $equipment) }}" enctype="multipart/form-data" class="mt-4 flex flex-wrap items-end gap-3">
                                                @csrf
                                                <div class="flex-1 min-w-[200px]">
                                                    <label for="utilization_act_replace" class="block text-sm font-semibold text-slate-600 mb-1">Заменить акт</label>
                                                    <input id="utilization_act_replace" type="file" name="utilization_act" accept="{{ $docAccept }}" required class="text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-800">
                                                </div>
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700">Заменить файл</button>
                                            </form>
                                        @endif
                                    @else
                                        <p class="text-sm text-amber-800 mb-3">Акт утилизации не найден в файлах (возможно, данные устарели). Загрузите файл заново.</p>
                                        @if(auth()->user()?->canManageUtilization())
                                            <form method="post" action="{{ route('equipment.utilization-act.store', $equipment) }}" enctype="multipart/form-data" class="flex flex-wrap items-end gap-3">
                                                @csrf
                                                <div class="flex-1 min-w-[200px]">
                                                    <label for="utilization_act_restore" class="block text-sm font-semibold text-slate-600 mb-1">Загрузить акт</label>
                                                    <input id="utilization_act_restore" type="file" name="utilization_act" accept="{{ $docAccept }}" required class="text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-800">
                                                </div>
                                                <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-sm font-semibold text-white bg-violet-600 hover:bg-violet-700">Сохранить</button>
                                            </form>
                                        @endif
                                    @endif
                                </div>
                            @endif
                            @if($otherDocs->isNotEmpty())
                                <div class="rounded-xl border-2 border-slate-200 p-4 bg-slate-50">
                                    <p class="text-base font-semibold text-slate-700 mb-2">Прочие документы</p>
                                    <p class="text-sm text-slate-500">Здесь отображаются дополнительные документы (включая подписанные акты списания/перемещения).</p>
                                </div>
                                @foreach($otherDocs as $doc)
                                    <div class="rounded-xl border-2 border-slate-200 p-4 bg-slate-50">
                                        <a href="{{ asset('storage/' . $doc->document) }}" target="_blank" rel="noopener" download class="text-base font-medium text-teal-600 hover:underline">Скачать {{ $doc->name }}</a>
                                    </div>
                                @endforeach
                            @endif
                            @if(auth()->user()?->canManageEquipment())
                                <div class="rounded-xl border-2 border-teal-200 p-4 bg-teal-50/40">
                                    <p class="text-base font-semibold text-slate-700 mb-2">Подписанный акт после печати</p>
                                    <p class="text-sm text-slate-600 mb-3">После печати и подписания акта загрузите скан/файл сюда — он сохранится в «Прочих документах» карточки оборудования.</p>
                                    <form method="post" action="{{ route('equipment.documents.store', $equipment) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        <input type="hidden" name="type" value="signed_report_act">
                                        <div>
                                            <label for="signed_report_act_name" class="block text-sm font-semibold text-slate-600 mb-1">Название документа</label>
                                            <input id="signed_report_act_name" type="text" name="document_name"
                                                   class="w-full rounded-xl border border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[44px] px-3"
                                                   required
                                                   placeholder="Например: Подписанный акт списания от 17.04.2026">
                                        </div>
                                        <div>
                                            <input id="signed_report_act_file" type="file" name="document" accept="{{ $docAccept }}" required
                                                   class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-100 file:text-teal-800">
                                        </div>
                                        <button type="submit" class="min-h-[44px] px-4 py-2 rounded-xl text-base font-semibold text-teal-700 bg-teal-100 hover:bg-teal-200">Загрузить в прочие документы</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Правая колонка: характеристики + заявки --}}
                <div class="lg:w-1/3 p-6 bg-slate-50/50">
                    <h3 class="text-lg font-bold text-slate-700 mb-4">Характеристики</h3>
                    <dl class="space-y-3 text-base">
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
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Статус списания</dt>
                            <dd class="text-slate-800 text-right">
                                @if($equipment->writeoff_status === 'approved')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-red-50 text-red-700 border-2 border-red-200">
                                        Списано
                                    </span>
                                @elseif($equipment->writeoff_status === 'requested')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-amber-50 text-amber-700 border-2 border-amber-200">
                                        Запрошено списание
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400">—</span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt class="text-slate-500 shrink-0">Утилизация</dt>
                            <dd class="text-slate-800 text-right">
                                @if($equipment->isUtilized())
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-semibold bg-slate-100 text-slate-700 border-2 border-slate-200">
                                        Утилизировано
                                    </span>
                                @else
                                    <span class="text-sm text-slate-400">Не утилизировано</span>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if(auth()->user()?->canManageUtilization() && $equipment->isWrittenOff() && ! $equipment->isUtilized())
                        <div class="mt-6 pt-4 border-t-2 border-slate-200">
                            <h4 class="text-base font-bold text-slate-700 mb-2">Утилизация</h4>
                            <p class="text-sm text-slate-600 mb-3">Оборудование списано. Прикрепите акт утилизации (PDF, Word или Excel) и подтвердите — в списке строка станет перечёркнутой, акт будет доступен для просмотра и скачивания в блоке «Документы».</p>
                            <form method="POST" action="{{ route('equipment.utilize', $equipment) }}" enctype="multipart/form-data" class="space-y-4" onsubmit="return confirm('Утилизировать оборудование с прикреплённым актом?');">
                                @csrf
                                <div>
                                    <label for="utilization_act_card" class="block text-sm font-semibold text-slate-600 mb-1">Акт утилизации <span class="text-red-500">*</span></label>
                                    <input id="utilization_act_card" type="file" name="utilization_act" accept="{{ $docAccept }}" required class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-violet-100 file:text-violet-800">
                                </div>
                                <button type="submit" class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-violet-600 hover:bg-violet-700 transition">
                                    Утилизировать и сохранить акт
                                </button>
                            </form>
                        </div>
                    @endif

                    @if(auth()->user()?->canManageEquipment())
                        <div class="mt-6 pt-4 border-t-2 border-slate-200 space-y-4">
                            <a href="{{ route('equipment.edit', $equipment) }}" class="inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition shadow-md">Редактировать</a>
                        </div>
                    @endif

                    @php
                        $canSendRequests = auth()->user()?->isSeniorNurse();
                        $departmentsList = ($departments ?? collect())->filter(fn($d) => $d->id !== $equipment->department_id);
                    @endphp

                    @if($canSendRequests)
                        <div class="mt-6 pt-4 border-t-2 border-slate-200 space-y-6">
                            {{-- Заявка на списание --}}
                            @if(!$equipment->isWrittenOff())
                                <div>
                                    <h4 class="text-base font-bold text-slate-700 mb-3">Заявка на списание</h4>
                                    <form method="POST" action="{{ route('equipment.requests.writeoff', $equipment) }}" enctype="multipart/form-data" class="space-y-3">
                                        @csrf
                                        <label for="writeoff_comment" class="block text-base font-semibold text-slate-600 mb-1">
                                            Причина списания <span class="text-red-500">*</span>
                                        </label>
                                        <textarea id="writeoff_comment" name="comment" rows="3" required class="w-full rounded-xl border-2 border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base py-2 px-3" placeholder="Опишите, почему оборудование нужно списать (неисправность, устарело и т.п.)">{{ old('comment') }}</textarea>
                                        <div>
                                            <label for="writeoff_photo" class="block text-base font-semibold text-slate-600 mb-1">
                                                Фото причины (необязательно)
                                            </label>
                                            <input id="writeoff_photo" type="file" name="photo" accept="image/jpeg,image/png,image/gif,image/webp"
                                                   class="block w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                                            <p class="mt-1 text-xs text-slate-500">Допустимые форматы: JPEG, PNG, GIF, WebP. Максимум 5 МБ.</p>
                                        </div>
                                        <button type="submit" class="mt-2 inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-red-600 hover:bg-red-700 transition">
                                            Отправить заявку на списание
                                        </button>
                                    </form>
                                </div>
                            @endif

                            {{-- Заявка на перемещение --}}
                            <div>
                                <h4 class="text-base font-bold text-slate-700 mb-3">Заявка на перемещение</h4>
                                <form method="POST" action="{{ route('equipment.requests.move', $equipment) }}" class="space-y-3">
                                    @csrf
                                    <div>
                                        <label for="to_department_id" class="block text-base font-semibold text-slate-600 mb-1">Новое отделение</label>
                                        <select id="to_department_id" name="to_department_id" required class="w-full rounded-xl border-2 border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base py-2.5 px-3">
                                            <option value="">Выберите отделение</option>
                                            @foreach($departmentsList as $dept)
                                                <option value="{{ $dept->id }}" @selected(old('to_department_id') == $dept->id)>
                                                    {{ $dept->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="move_comment" class="block text-base font-semibold text-slate-600 mb-1">Комментарий (опционально)</label>
                                        <textarea id="move_comment" name="comment" rows="2" class="w-full rounded-xl border-2 border-slate-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base py-2 px-3" placeholder="Причина или детали перемещения...">{{ old('comment') }}</textarea>
                                    </div>
                                    <button type="submit" class="mt-2 inline-flex items-center min-h-[48px] px-5 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition">
                                        Отправить заявку на перемещение
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
