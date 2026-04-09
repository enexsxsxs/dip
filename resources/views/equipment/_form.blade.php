@props(['equipment' => null])
@php
    $isEdit = $equipment !== null;
    $existingImages = $equipment?->images ?? collect();
    $maxPhotos = 5;
    $canAddMore = $isEdit ? ($maxPhotos - $existingImages->count()) : $maxPhotos;
@endphp

<div class="bg-white rounded-2xl shadow-xl border border-teal-100 overflow-hidden">
    {{-- Фотографии --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-4 bg-teal-50 border-l-4 border-teal-500">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Фотографии</h3>
            <p class="text-base text-slate-600 mt-1">От 1 до 5 фото (JPEG, PNG, GIF, WebP, до 5 МБ).</p>
        </div>
        <div class="p-6">
        @if($isEdit && $existingImages->isNotEmpty())
            <div class="flex flex-wrap gap-3 mb-4">
                @foreach($existingImages as $img)
                    <div class="relative">
                        <img src="{{ asset('storage/' . $img->image) }}" alt="Фото" class="w-20 h-20 object-cover rounded-lg border border-slate-200">
                        <label class="absolute bottom-0 left-0 right-0 flex items-center justify-center gap-1 text-xs text-red-600 bg-white/95 py-1 rounded-b-lg cursor-pointer border border-t border-slate-200">
                            <input type="checkbox" name="delete_images[]" value="{{ $img->id }}" class="rounded border-slate-300 text-red-600 w-3 h-3">
                            Удалить
                        </label>
                    </div>
                @endforeach
            </div>
        @endif
        @if($canAddMore > 0 || !$isEdit)
            <input type="file" id="images" name="images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple
                   @if(!$isEdit) required @endif
                   class="block w-full text-base text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
            <x-input-error :messages="$errors->get('images')" class="mt-1" />
        @endif
        </div>
    </section>

    {{-- Документы: обязательны при создании, опциональны при редактировании --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-4 bg-slate-100 border-l-4 border-slate-400">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Документы</h3>
            <p class="text-base text-slate-600 mt-1">
                Регистрационное удостоверение, инструкция на русском языке и акт ввода в эксплуатацию
                @if($isEdit)
                    — можно обновить при необходимости.
                @else
                    — обязательны при добавлении нового оборудования.
                @endif
                Форматы: PDF, Word (.doc, .docx), Excel (.xls, .xlsx).
            </p>
        </div>
        <div class="p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label for="document_registration_certificate" class="block text-base font-semibold text-slate-600 mb-2">1. Регистрационное удостоверение @if(!$isEdit)<span class="text-red-500">*</span>@endif</label>
                @if($isEdit && ($equipment->documents ?? collect())->whereIn('type', ['registration_certificate', 'ru_scan'])->isNotEmpty())
                    <p class="text-base text-slate-500 mb-2">Текущий: {{ ($equipment->documents ?? collect())->first(fn($d) => in_array($d->type ?? '', ['registration_certificate', 'ru_scan']))->name ?? '—' }}</p>
                @endif
                <input type="file" id="document_registration_certificate" name="document_registration_certificate" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                       @if(!$isEdit) required @endif
                       class="block w-full text-base text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <x-input-error :messages="$errors->get('document_registration_certificate')" class="mt-1" />
            </div>
            <div>
                <label for="document_instruction" class="block text-base font-semibold text-slate-600 mb-2">2. Инструкция на русском языке @if(!$isEdit)<span class="text-red-500">*</span>@endif</label>
                @if($isEdit && ($equipment->documents ?? collect())->where('type', 'instruction')->isNotEmpty())
                    <p class="text-base text-slate-500 mb-2">Текущий: {{ ($equipment->documents ?? collect())->first(fn($d) => ($d->type ?? '') === 'instruction')->name ?? '—' }}</p>
                @endif
                <input type="file" id="document_instruction" name="document_instruction" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                       @if(!$isEdit) required @endif
                       class="block w-full text-base text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <x-input-error :messages="$errors->get('document_instruction')" class="mt-1" />
            </div>
            <div>
                <label for="document_commissioning_act" class="block text-base font-semibold text-slate-600 mb-2">3. Акт ввода в эксплуатацию @if(!$isEdit)<span class="text-red-500">*</span>@endif</label>
                @if($isEdit && ($equipment->documents ?? collect())->where('type', 'commissioning_act')->isNotEmpty())
                    <p class="text-base text-slate-500 mb-2">Текущий: {{ ($equipment->documents ?? collect())->first(fn($d) => ($d->type ?? '') === 'commissioning_act')->name ?? '—' }}</p>
                @endif
                <input type="file" id="document_commissioning_act" name="document_commissioning_act" accept=".pdf,.doc,.docx,.xls,.xlsx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                       @if(!$isEdit) required @endif
                       class="block w-full text-base text-slate-600 file:mr-3 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:bg-teal-50 file:text-teal-700 hover:file:bg-teal-100">
                <x-input-error :messages="$errors->get('document_commissioning_act')" class="mt-1" />
            </div>
        </div>
        </div>
    </section>

    {{-- Наименование и статус (как на слайде) --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-3 bg-teal-50 border-l-4 border-teal-500">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Основные данные</h3>
            <p class="text-base text-slate-600 mt-1">Наименование, статус и вид оборудования.</p>
        </div>
        <div class="p-6 bg-slate-50/30">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
            <div class="md:col-span-2">
                <label for="name" class="block text-base font-semibold text-slate-600 mb-2">Наименование <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" required maxlength="255"
                       value="{{ old('name', $equipment?->name) }}"
                       placeholder="Система газовой анестезии EZ-ANESTHESIA"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3 placeholder:text-slate-400">
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>
            <div>
                <label for="equipment_condition_id" class="block text-base font-semibold text-slate-600 mb-2">Статус оборудования</label>
                <select id="equipment_condition_id" name="equipment_condition_id"
                        class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3 bg-white border-green-200 focus:border-green-500">
                    <option value="">&lt; Не выбрано &gt;</option>
                    @foreach($conditions as $c)
                        <option value="{{ $c->id }}" @selected(old('equipment_condition_id', $equipment?->equipment_condition_id) == $c->id)>{{ $c->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('equipment_condition_id')" class="mt-1" />
            </div>
        </div>
        <div class="mt-4">
            <label for="equipment_type_id" class="block text-base font-semibold text-slate-600 mb-2">Вид оборудования (категория)</label>
            <select id="equipment_type_id" name="equipment_type_id"
                    class="w-full max-w-md rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                <option value="">&lt; Не выбрано &gt;</option>
                @foreach($equipmentTypes as $t)
                    <option value="{{ $t->id }}" @selected(old('equipment_type_id', $equipment?->equipment_type_id) == $t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        </div>
    </section>

    {{-- Ряд выпадающих списков: Отдел, Кабинет, Группа --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-3 bg-slate-100 border-l-4 border-slate-400">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Отдел, помещение, группа</h3>
            <p class="text-base text-slate-600 mt-1">Расположение оборудования.</p>
        </div>
        <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="department_id" class="block text-base font-semibold text-slate-600 mb-2">Отдел</label>
                <select id="department_id" name="department_id" class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    <option value="">&lt; Не выбрано &gt;</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" @selected(old('department_id', $equipment?->department_id) == $d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="cabinet_id" class="block text-base font-semibold text-slate-600 mb-2">Помещение / Кабинет</label>
                <select id="cabinet_id" name="cabinet_id" class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    <option value="">&lt; Не выбрано &gt;</option>
                    @foreach($cabinets as $c)
                        <option value="{{ $c->id }}" @selected(old('cabinet_id', $equipment?->cabinet_id) == $c->id)>{{ $c->number }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="group_id" class="block text-base font-semibold text-slate-600 mb-2">Группа</label>
                <select id="group_id" name="group_id" class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    <option value="">&lt; Не выбрано &gt;</option>
                    @foreach($groups as $g)
                        <option value="{{ $g->id }}" @selected(old('group_id', $equipment?->group_id) == $g->id)>{{ $g->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        </div>
    </section>

    {{-- Норматив / учёт: №, Инв. №, Серийный №, даты --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-3 bg-teal-50 border-l-4 border-teal-500">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Учёт и идентификация</h3>
            <p class="text-base text-slate-600 mt-1">№, инв. №, серийный №, даты выпуска и ввода в эксплуатацию.</p>
        </div>
        <div class="p-6 bg-slate-50/30">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3">
            <div>
                <label for="number" class="block text-base font-semibold text-slate-600 mb-1">№ <span class="text-red-500">*</span></label>
                <input type="number" id="number" name="number" min="1" required
                       value="{{ old('number', $equipment?->number) }}"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                <x-input-error :messages="$errors->get('number')" class="mt-0.5" />
            </div>
            <div>
                <label for="inventory_number" class="block text-base font-semibold text-slate-600 mb-1">Инв. №</label>
                @if(auth()->user()?->canAssignInventoryNumber())
                    <input type="text" id="inventory_number" name="inventory_number" maxlength="100"
                           value="{{ old('inventory_number', $equipment?->inventory_number) }}"
                           class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                @else
                    <p class="w-full rounded-xl border-2 border-slate-200 bg-slate-100 text-slate-500 text-base py-2.5 px-3">
                        {{ old('inventory_number', $equipment?->inventory_number) ?: 'Присваивается бухгалтером в списке оборудования' }}
                    </p>
                @endif
            </div>
            <div>
                <label for="serial_number" class="block text-base font-semibold text-slate-600 mb-1">Серийный №</label>
                <input type="text" id="serial_number" name="serial_number" maxlength="100"
                       value="{{ old('serial_number', $equipment?->serial_number) }}"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
            </div>
            <div>
                <label for="production_date" class="block text-base font-semibold text-slate-600 mb-1">Дата выпуска</label>
                <input type="date" id="production_date" name="production_date"
                       value="{{ old('production_date', $equipment?->production_date?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
            </div>
            <div>
                <label for="date_accepted_to_accounting" class="block text-base font-semibold text-slate-600 mb-1">Дата ввода в эксплуатацию</label>
                <input type="date" id="date_accepted_to_accounting" name="date_accepted_to_accounting"
                       value="{{ old('date_accepted_to_accounting', $equipment?->date_accepted_to_accounting?->format('Y-m-d')) }}"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
            </div>
            <div>
                <label for="year_of_manufacture" class="block text-base font-semibold text-slate-600 mb-1">Год выпуска</label>
                <input type="text" id="year_of_manufacture" name="year_of_manufacture" maxlength="55"
                       value="{{ old('year_of_manufacture', $equipment?->year_of_manufacture) }}"
                       class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
            </div>
        </div>
        </div>
    </section>

    {{-- Две колонки: рег. удостоверение, поверка, поставщик, сервис --}}
    <section class="border-b-2 border-slate-200">
        <div class="px-6 py-3 bg-slate-100 border-l-4 border-slate-400">
            <h3 class="text-base font-bold text-slate-800 uppercase tracking-wider">Регистрация, поверка, поставщик</h3>
            <p class="text-base text-slate-600 mt-1">Рег. удостоверение, РУ, поверка, поставщик и сервисная организация.</p>
        </div>
        <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
                <h4 class="text-base font-semibold text-slate-600 mb-3">Регистрация и поверка</h4>
                <div class="space-y-3">
                    <div>
                        <label for="registration_certificate" class="block text-base font-semibold text-slate-600 mb-1">Рег. удостоверение</label>
                        <input type="text" id="registration_certificate" name="registration_certificate" maxlength="100"
                               value="{{ old('registration_certificate', $equipment?->registration_certificate) }}"
                               class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="ru_number" class="block text-base font-semibold text-slate-600 mb-1">Номер в Гос. реестре / №РУ</label>
                            <input type="text" id="ru_number" name="ru_number" maxlength="100"
                                   value="{{ old('ru_number', $equipment?->ru_number) }}"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                        <div>
                            <label for="ru_date" class="block text-base font-semibold text-slate-600 mb-1">Дата РУ</label>
                            <input type="date" id="ru_date" name="ru_date"
                                   value="{{ old('ru_date', $equipment?->ru_date?->format('Y-m-d')) }}"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="valid_until" class="block text-base font-semibold text-slate-600 mb-1">Срок действия РУ</label>
                            <input type="text" id="valid_until" name="valid_until" maxlength="20"
                                   value="{{ old('valid_until', $equipment?->valid_until) }}"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                        <div>
                            <label for="valid_to" class="block text-base font-semibold text-slate-600 mb-1">Годен до</label>
                            <input type="text" id="valid_to" name="valid_to" maxlength="20"
                                   value="{{ old('valid_to', $equipment?->valid_to) }}"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                    </div>
                    <div>
                        <label for="grsi" class="block text-base font-semibold text-slate-600 mb-1">ГРСИ / Номер в Гос. реестре СИ</label>
                        <input type="text" id="grsi" name="grsi" maxlength="255"
                               value="{{ old('grsi', $equipment?->grsi) }}"
                               class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label for="verification_period" class="block text-base font-semibold text-slate-600 mb-1">Межповерочный интервал, лет</label>
                            <input type="text" id="verification_period" name="verification_period" maxlength="55"
                                   value="{{ old('verification_period', $equipment?->verification_period) }}"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                        <div>
                            <label for="last_verification_date" class="block text-base font-semibold text-slate-600 mb-1">Дата последней поверки</label>
                            <input type="text" id="last_verification_date" name="last_verification_date" maxlength="20"
                                   value="{{ old('last_verification_date', $equipment?->last_verification_date) }}"
                                   placeholder="2014-09-01"
                                   class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                        </div>
                    </div>
                    <div>
                        <label for="date_of_registration" class="block text-base font-semibold text-slate-600 mb-1">Дата регистрации</label>
                        <input type="text" id="date_of_registration" name="date_of_registration" maxlength="20"
                               value="{{ old('date_of_registration', $equipment?->date_of_registration) }}"
                               class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                    </div>
                </div>
            </div>
            <div>
                <h4 class="text-base font-semibold text-slate-600 mb-3">Поставщик и обслуживание</h4>
                <div class="space-y-3">
                    <div>
                        <label for="supplier_id" class="block text-base font-semibold text-slate-600 mb-1">Поставщик</label>
                        <select id="supplier_id" name="supplier_id" class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                            <option value="">&lt; Не выбрано &gt;</option>
                            @foreach($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id', $equipment?->supplier_id) == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="service_organization_id" class="block text-base font-semibold text-slate-600 mb-1">Сервисная организация</label>
                        <select id="service_organization_id" name="service_organization_id" class="w-full rounded-xl border-2 border-slate-300 focus:border-teal-500 focus:ring-teal-500 shadow-sm text-base py-2.5 px-3">
                            <option value="">&lt; Не выбрано &gt;</option>
                            @foreach($serviceOrganizations as $s)
                                <option value="{{ $s->id }}" @selected(old('service_organization_id', $equipment?->service_organization_id) == $s->id)>{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {{-- Кнопки --}}
    <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-wrap gap-3">
        <button type="submit" class="inline-flex items-center min-h-[48px] px-6 py-3 rounded-xl text-base font-semibold text-white bg-teal-600 hover:bg-teal-700 transition shadow-md">
            {{ $isEdit ? 'Сохранить' : 'Добавить оборудование' }}
        </button>
        <a href="{{ route('equipment.index') }}" class="inline-flex items-center min-h-[48px] px-6 py-3 rounded-xl text-base font-semibold text-slate-600 bg-white border-2 border-slate-300 hover:bg-slate-50 transition">
            Отмена
        </a>
    </div>
</div>
