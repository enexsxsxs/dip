{{-- Ожидается родитель: reportLayoutForm(@js($initialSchema), @js($headerSourceLayouts ?? []), @js($footerPickUsers ?? [])) --}}

@php
    $cIn = 'w-full rounded-xl border border-slate-200 bg-white text-slate-800 placeholder:text-slate-400 min-h-[48px] px-4 py-2.5 text-base shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none transition-colors duration-150';
    $cSel = 'select-teal-arrow w-full rounded-xl border border-slate-200 bg-white text-slate-800 min-h-[48px] pl-4 py-2 text-base shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none transition-colors duration-150';
    $cSelSm = 'select-teal-arrow rounded-xl border border-slate-200 bg-white text-slate-800 min-h-[44px] pl-3.5 py-2 text-sm shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none transition-colors';
    $lbl = 'block text-sm font-medium text-slate-600 mb-2 leading-snug';
    $lblXs = 'block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide';
@endphp

<div class="space-y-10">
    {{-- Заголовок / подзаголовок документа --}}
    <section class="layout-form-block rounded-2xl border-2 border-slate-300 border-l-[6px] border-l-teal-600 bg-white p-6 sm:p-8">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-600 mb-5">Оформление документа</h4>
        <div class="grid gap-6 sm:grid-cols-2">
            <div>
                <label for="doc_title" class="{{ $lbl }}">Заголовок в документе</label>
                <input type="text" id="doc_title" x-model="docTitle" placeholder="Например: АКТ"
                       class="{{ $cIn }}">
            </div>
            <div>
                <label for="doc_title_font" class="{{ $lbl }}">Размер заголовка (pt)</label>
                <select id="doc_title_font" x-model.number="docTitleFontPt" class="{{ $cSel }}">
                    @foreach([12,14,16,18,20,22,24] as $pt)
                        <option value="{{ $pt }}">{{ $pt }} pt</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="doc_subtitle" class="{{ $lbl }}">Подзаголовок</label>
                <input type="text" id="doc_subtitle" x-model="docSubtitle" placeholder="Строки под заголовком"
                       class="{{ $cIn }}">
            </div>
            <div>
                <label for="doc_subtitle_font" class="{{ $lbl }}">Размер подзаголовка (pt)</label>
                <select id="doc_subtitle_font" x-model.number="docSubtitleFontPt" class="{{ $cSel }}">
                    @foreach([10,11,12,13,14,16] as $pt)
                        <option value="{{ $pt }}">{{ $pt }} pt</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="pdf_footer_style" class="{{ $lbl }}">Подвал в PDF</label>
                <select id="pdf_footer_style" x-model="pdfFooterStyle" class="{{ $cSel }}">
                    <option value="legacy">Один подписант (автор заявки)</option>
                    <option value="rapport_two">Рапорт: два подписанта (заведующая = «Пользователь», старшая медсестра)</option>
                    <option value="rapport_three">Рапорт: три подписанта (+ «Инженер» = администратор)</option>
                </select>
                <p class="text-xs text-slate-500 mt-2 leading-relaxed max-w-3xl">
                    <strong>Заведующая отделением</strong> и <strong>Инженер</strong> (в режиме из трёх строк) выбираются ниже из списков.
                    <strong>Старшая медсестра</strong> в PDF всегда подставляется автоматически: автор последней <strong>неподтверждённой</strong> заявки на списание или перемещение; если такой нет — автор PDF-заявки при роли «Старшая медсестра», иначе первая активная старшая медсестра в системе.
                </p>
                <div x-show="pdfFooterStyle === 'rapport_two' || pdfFooterStyle === 'rapport_three'" x-cloak class="mt-4 space-y-3 max-w-xl">
                    <div>
                        <label for="pdf_footer_head_user" class="{{ $lbl }}">Заведующая отделением (роль «Пользователь»)</label>
                        <select id="pdf_footer_head_user" x-model="pdfFooterHeadUserId" class="{{ $cSel }}">
                            <option value="">По умолчанию — первый по фамилии</option>
                            <template x-for="u in footerPickUsersHead" :key="u.id">
                                <option :value="String(u.id)" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
                <div x-show="pdfFooterStyle === 'rapport_three'" x-cloak class="mt-3 space-y-3 max-w-xl">
                    <div>
                        <label for="pdf_footer_engineer_user" class="{{ $lbl }}">Инженер (роль «Администратор»)</label>
                        <select id="pdf_footer_engineer_user" x-model="pdfFooterEngineerUserId" class="{{ $cSel }}">
                            <option value="">По умолчанию — первый по фамилии</option>
                            <template x-for="u in footerPickUsersEngineer" :key="u.id">
                                <option :value="String(u.id)" x-text="u.name"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Текст заявки: базовые настройки --}}
    <section class="layout-form-block layout-form-block--tint rounded-2xl border-2 border-slate-300 border-l-[6px] border-l-cyan-700 p-6 sm:p-8">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-600 mb-5">Текст заявки в документе</h4>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4 items-end lg:grid-flow-dense">
            <div class="min-w-0 sm:min-w-[15rem] lg:min-w-[16rem]">
                <label class="{{ $lbl }}">Базовый шрифт</label>
                <select x-model="bodyDefaultFontFamily" @change="applyEditorBaseStyle()" class="{{ $cSel }} min-h-[44px] w-full">
                    <option value="DejaVu Serif">Times New Roman</option>
                    <option value="DejaVu Sans">Arial</option>
                </select>
            </div>
            <div>
                <label class="{{ $lbl }}">Размер текста (pt)</label>
                <select x-model.number="bodyDefaultFontSizePt" @change="applyEditorBaseStyle()" class="{{ $cSel }} min-h-[44px]">
                    @foreach([9,10,11,12,13,14] as $pt)
                        <option value="{{ $pt }}">{{ $pt }} pt</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="{{ $lbl }}">Межстрочный интервал</label>
                <select x-model.number="bodyLineHeight" @change="applyEditorBaseStyle()" class="{{ $cSel }} min-h-[44px]">
                    <option value="1.15">1,15</option>
                    <option value="1.35">1,35</option>
                    <option value="1.5">1,5</option>
                    <option value="1.65">1,65</option>
                </select>
            </div>
            <div>
                <button type="button" @click="applyEditorBaseStyle()"
                        class="w-full min-h-[44px] rounded-xl border border-slate-300 bg-white text-slate-700 font-medium text-sm px-4 hover:bg-slate-50 hover:border-slate-400 transition-colors">
                    Обновить стиль поля ввода
                </button>
            </div>
        </div>
    </section>

    {{-- Шапка --}}
    <section class="layout-form-block layout-form-block--tint rounded-2xl border-2 border-slate-300 border-l-[6px] border-l-slate-600 p-6 sm:p-8 space-y-6">
        <h3 class="text-lg font-semibold text-slate-800 tracking-tight">Шапка документа</h3>

        <div x-show="headerSourceLayouts.length > 0" x-cloak class="rounded-xl border border-dashed border-teal-400 bg-teal-50/70 p-4 sm:p-5 space-y-3">
            <p class="text-sm font-semibold text-slate-800">Шапка из другого макета</p>
            <p class="text-xs text-slate-600 leading-relaxed max-w-3xl">Подставляются только три блока шапки (текст, выравнивание, шрифт). Остальные настройки макета не меняются. Для строк с «В заявке» после подстановки создаются новые внутренние ключи.</p>
            <div class="flex flex-wrap gap-3 items-end">
                <div class="min-w-[12rem] flex-1 max-w-lg">
                    <label class="{{ $lblXs }}">Выберите сохранённый макет</label>
                    <select x-model="selectedHeaderLayoutId"
                            class="{{ $cSelSm }} w-full min-w-0 text-base min-h-[44px]">
                        <option value="">— Макет —</option>
                        <template x-for="opt in headerSourceLayouts" :key="opt.id">
                            <option :value="String(opt.id)" x-text="opt.title"></option>
                        </template>
                    </select>
                </div>
                <button type="button"
                        @click="importHeaderFromSelectedLayout()"
                        :disabled="!selectedHeaderLayoutId || headerImportBusy"
                        class="min-h-[44px] px-5 rounded-xl bg-teal-700 text-white font-medium hover:bg-teal-800 disabled:opacity-45 disabled:cursor-not-allowed transition-colors shadow-sm shrink-0">
                    <span x-show="!headerImportBusy">Подставить шапку</span>
                    <span x-show="headerImportBusy" x-cloak>Загрузка…</span>
                </button>
            </div>
            <p x-show="headerImportError" x-cloak class="text-sm text-rose-600" x-text="headerImportError"></p>
        </div>

        <template x-for="(section, si) in headerSections" :key="si">
            <div class="layout-form-block--nested rounded-xl border border-slate-300 bg-white p-5 sm:p-6 space-y-4">
                <div class="font-medium text-slate-800 text-base" x-text="'Блок шапки ' + (si + 1)"></div>
                <div class="flex flex-wrap gap-5 items-end">
                    <div>
                        <label class="{{ $lblXs }}">Выравнивание</label>
                        <select x-model="section.align"
                                class="{{ $cSelSm }} min-w-[10rem]">
                            <option value="center">По центру</option>
                            <option value="left">Слева</option>
                            <option value="right">Справа</option>
                        </select>
                    </div>
                    <label class="flex items-center gap-2.5 cursor-pointer pb-1">
                        <input type="checkbox" x-model="section.bold" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30 w-5 h-5">
                        <span class="text-sm text-slate-700">Жирный</span>
                    </label>
                    <div>
                        <label class="{{ $lblXs }}">Шрифт блока</label>
                        <select x-model="section.font_family"
                                class="{{ $cSelSm }} min-w-[13rem] max-w-full">
                            <option value="DejaVu Serif">Times New Roman</option>
                            <option value="DejaVu Sans">Arial</option>
                        </select>
                    </div>
                    <div>
                        <label class="{{ $lblXs }}">Размер (pt)</label>
                        <select x-model.number="section.font_size_pt"
                                class="{{ $cSelSm }} w-[5.85rem]">
                            @foreach([9,10,11,12,13,14,16,18,20] as $pt)
                                <option value="{{ $pt }}">{{ $pt }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <span class="text-sm font-medium text-slate-600">Строки блока</span>
                    <template x-for="(line, li) in section.lines" :key="line.line_id || ('h_' + si + '_' + li)">
                        <div class="flex flex-wrap gap-2 items-center">
                            <input type="text" x-model="line.text"
                                   class="flex-1 min-w-[12rem] rounded-xl border border-slate-200 bg-white shadow-sm text-base min-h-[44px] px-4 focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none transition-colors"
                                   placeholder="Текст строки">
                            <label class="inline-flex items-center gap-2 cursor-pointer shrink-0 text-sm text-slate-700 min-h-[44px] px-2 rounded-lg border border-transparent hover:bg-slate-50">
                                <input type="checkbox" x-model="line.editable" @change="onHeaderLineEditableToggle(si, li)"
                                       class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30 w-5 h-5">
                                <span>В заявке <span class="text-slate-500 font-normal">(ФИО и т.п.)</span></span>
                            </label>
                            <button type="button" @click="removeHeaderLine(si, li)" class="text-slate-400 hover:text-rose-600 px-2.5 py-2 font-medium shrink-0 rounded-lg hover:bg-rose-50 transition-colors" title="Удалить строку">✕</button>
                        </div>
                    </template>
                    <button type="button" @click="addHeaderLine(si)" class="text-teal-700 font-medium text-sm hover:text-teal-800 hover:underline">+ Строка</button>
                </div>
            </div>
        </template>
    </section>

    {{-- Вкладки --}}
    <div class="flex flex-wrap gap-2 pb-1 border-b border-slate-200">
        <button type="button" @click="tab = 'fields'"
                :class="tab === 'fields' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100/80 text-slate-600 border border-transparent hover:bg-slate-200/80'"
                class="min-h-[44px] px-6 rounded-xl font-medium text-sm transition-colors">
            Поля заявки
        </button>
        <button type="button" @click="tab = 'text'; $nextTick(() => syncFieldWidgetLabels())"
                :class="tab === 'text' ? 'bg-slate-800 text-white shadow-sm' : 'bg-slate-100/80 text-slate-600 border border-transparent hover:bg-slate-200/80'"
                class="min-h-[44px] px-6 rounded-xl font-medium text-sm transition-colors">
            Текст заявки
        </button>
    </div>

    {{-- Вкладка: поля --}}
    <div x-show="tab === 'fields'" x-cloak class="space-y-6">
        <p x-show="fields.length === 0" class="text-sm text-slate-600 leading-relaxed rounded-xl border border-dashed border-slate-300 bg-slate-50/80 p-4">
            Полей нет — так можно оставить макет (только текст заявки и системные вставки, например список оборудования). Чтобы добавить вводимые поля, нажмите «Добавить поле».
        </p>
        <div class="flex justify-end">
            <button type="button" @click="addField()"
                    class="inline-flex items-center min-h-[44px] px-5 rounded-xl bg-teal-700 text-white font-medium hover:bg-teal-800 transition-colors shadow-sm">
                + Добавить поле
            </button>
        </div>

        <template x-for="(field, index) in fields" :key="field.id">
            <div class="layout-form-block--nested rounded-2xl border border-slate-300 bg-white p-6 sm:p-7 space-y-5">
                <div class="flex justify-between items-start gap-3">
                    <span class="text-sm font-semibold text-slate-700" x-text="'Поле ' + (index + 1)"></span>
                    <button type="button" @click="removeField(index)" class="text-slate-400 hover:text-rose-600 p-2 hover:bg-rose-50 rounded-xl shrink-0 transition-colors" title="Удалить">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="{{ $lbl }}">Название</label>
                        <input type="text" x-model="field.name" placeholder="Например: ФИО"
                               class="{{ $cIn }}">
                    </div>
                    <div>
                        <label class="{{ $lbl }}">Тип</label>
                        <select x-model="field.type" class="{{ $cSel }}">
                            <option value="text">Текст</option>
                            <option value="number">Число</option>
                            <option value="select">Множественный выбор</option>
                        </select>
                    </div>
                </div>
                <div x-show="field.type === 'select'" x-cloak class="space-y-4 pl-1 sm:pl-3 border-l-2 border-slate-200">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="field.allow_other" class="rounded border-slate-300 text-teal-600 focus:ring-teal-500/30 w-5 h-5">
                        <span class="text-sm text-slate-700">Добавить в конце вариант «Другое»</span>
                    </label>
                    <div>
                        <span class="block text-sm font-medium text-slate-600 mb-2">Варианты</span>
                        <template x-for="(opt, oi) in field.options" :key="oi">
                            <div class="flex gap-2 mb-2">
                                <input type="text" x-model="field.options[oi]" placeholder="Вариант"
                                       class="flex-1 rounded-xl border border-slate-200 shadow-sm text-base min-h-[44px] px-4 focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none">
                                <button type="button" @click="removeOption(index, oi)" class="text-slate-400 hover:text-rose-600 px-3 font-medium rounded-lg hover:bg-rose-50">✕</button>
                            </div>
                        </template>
                        <button type="button" @click="addOption(index)" class="text-teal-700 font-medium text-sm hover:underline">+ вариант</button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Вкладка: текст --}}
    <div x-show="tab === 'text'" x-cloak class="space-y-6">
        <p class="text-sm text-slate-600 leading-relaxed max-w-3xl">Выделите фрагмент и задайте шрифт и размер. В итоговом документе сохранятся начертания Times New Roman или Arial.</p>
        <div class="layout-form-block--nested flex flex-wrap gap-4 items-end p-4 rounded-xl border border-slate-300 bg-white">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Шрифт для выделения</label>
                <select x-model="selectionFontFamily" class="{{ $cSelSm }} min-w-[12rem]">
                    <option value="DejaVu Serif">Times New Roman</option>
                    <option value="DejaVu Sans">Arial</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Размер (pt)</label>
                <select x-model.number="selectionFontSizePt" class="{{ $cSelSm }} w-[5.75rem]">
                    @foreach([9,10,11,12,13,14,16,18] as $pt)
                        <option value="{{ $pt }}">{{ $pt }}</option>
                    @endforeach
                </select>
            </div>
            <button type="button" @click="applySelectionFont()"
                    class="min-h-[40px] px-4 rounded-xl bg-teal-700 text-white font-medium text-sm hover:bg-teal-800 transition-colors shadow-sm">
                Применить к выделению
            </button>
        </div>
        <div class="layout-form-block--nested flex flex-wrap gap-1.5 p-3 rounded-xl border border-slate-300 bg-slate-100/60">
            <button type="button" @click="exec('bold')" class="min-w-[40px] min-h-[40px] rounded-lg font-bold border border-slate-200 bg-white text-slate-700 hover:bg-white hover:border-slate-300 shadow-sm" title="Жирный">B</button>
            <button type="button" @click="exec('italic')" class="min-w-[40px] min-h-[40px] rounded-lg italic border border-slate-200 bg-white text-slate-700 hover:bg-white hover:border-slate-300 shadow-sm" title="Курсив">I</button>
            <button type="button" @click="exec('underline')" class="min-w-[40px] min-h-[40px] rounded-lg underline border border-slate-200 bg-white text-slate-700 hover:bg-white hover:border-slate-300 shadow-sm" title="Подчёркнутый">U</button>
            <button type="button" @click="exec('strikeThrough')" class="min-w-[40px] min-h-[40px] rounded-lg line-through border border-slate-200 bg-white text-slate-700 hover:bg-white hover:border-slate-300 shadow-sm" title="Зачёркнутый">S</button>
            <span class="w-px h-8 bg-slate-200 mx-1 self-center"></span>
            <button type="button" @click="exec('insertUnorderedList')" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm" title="Маркеры">• Список</button>
            <button type="button" @click="exec('insertOrderedList')" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm" title="Нумерация">1. Список</button>
            <span class="w-px h-8 bg-slate-200 mx-1 self-center"></span>
            <button type="button" @click="exec('justifyLeft')" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm">Слева</button>
            <button type="button" @click="exec('justifyCenter')" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm">По центру</button>
            <button type="button" @click="exec('justifyRight')" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm">Справа</button>
            <span class="w-px h-8 bg-slate-200 mx-1 self-center"></span>
            <button type="button" @click="insertHorizontalRule()" class="min-h-[40px] px-3 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm hover:border-slate-300 shadow-sm" title="Линия">— Линия</button>
        </div>

        <div x-ref="editor"
             contenteditable="true"
             class="layout-form-block--nested min-h-[320px] w-full rounded-2xl border-2 border-slate-300 bg-white p-6 text-base text-slate-800 leading-relaxed focus:outline-none focus:ring-2 focus:ring-teal-500/25 focus:border-teal-600/40 shadow-inner shadow-slate-200/50"
             style="font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.35;"
             spellcheck="true"></div>

        <div class="layout-form-block--nested rounded-xl border border-slate-300 bg-slate-50/90 p-5">
            <p class="text-sm font-medium text-slate-700 mb-1">Вставить поле</p>
            <p class="text-sm text-slate-500 mb-4 leading-relaxed">Сначала задайте названия на вкладке «Поля заявки». Кнопки ниже вставляют поля и спец-токены (списки оборудования из БД по заявкам).</p>
            <div class="flex flex-wrap gap-2.5">
                <button type="button" @click="insertSystemToken('sys.writeoff_equipment_list')"
                        title="В PDF — нумерованный список из БД: только оборудование со статусом «заявка на списание», без подтверждения администратора"
                        class="min-h-[42px] px-4 rounded-xl border-2 border-fuchsia-500 bg-fuchsia-100 text-fuchsia-950 hover:bg-fuchsia-200 font-semibold text-sm shadow-sm transition-colors">
                    Список оборудования на списание (из БД)
                </button>
                <button type="button" @click="insertSystemToken('sys.move_equipment_list')"
                        title="В PDF — нумерованный список: оборудование с заявкой на перемещение, пока администратор не подтвердил"
                        class="min-h-[42px] px-4 rounded-xl border-2 border-indigo-500 bg-indigo-100 text-indigo-950 hover:bg-indigo-200 font-semibold text-sm shadow-sm transition-colors">
                    Список оборудования на перемещение (из БД)
                </button>
                <template x-for="(f, fi) in fieldsForInsert()" :key="f.id">
                    <button type="button" @click="insertFieldToken(f.id)"
                            class="min-h-[42px] px-4 rounded-xl border-2 font-semibold text-sm shadow-sm transition-colors"
                            :class="{
                                'bg-teal-100 border-teal-500 text-teal-950 hover:bg-teal-200': fi % 6 === 0,
                                'bg-sky-100 border-sky-500 text-sky-950 hover:bg-sky-200': fi % 6 === 1,
                                'bg-violet-100 border-violet-500 text-violet-950 hover:bg-violet-200': fi % 6 === 2,
                                'bg-amber-100 border-amber-500 text-amber-950 hover:bg-amber-200': fi % 6 === 3,
                                'bg-emerald-100 border-emerald-500 text-emerald-950 hover:bg-emerald-200': fi % 6 === 4,
                                'bg-rose-100 border-rose-500 text-rose-950 hover:bg-rose-200': fi % 6 === 5,
                            }"
                            x-text="f.name"></button>
                </template>
            </div>
            <p class="text-xs text-slate-400 mt-4" x-show="fieldsForInsert().length === 0">Нет полей с заполненным названием.</p>
        </div>
    </div>

    <input type="hidden" name="schema" id="schema" value="">
</div>
