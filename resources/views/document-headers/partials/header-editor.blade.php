@php
    $cSelSm = 'select-teal-arrow rounded-xl border border-slate-200 bg-white text-slate-800 min-h-[44px] pl-3.5 py-2 text-sm shadow-sm focus:border-teal-600/45 focus:ring-2 focus:ring-teal-500/15 focus:outline-none transition-colors';
    $lblXs = 'block text-xs font-medium text-slate-500 mb-1.5 uppercase tracking-wide';
@endphp

<div class="space-y-6">
    <template x-for="(section, si) in headerSections" :key="si">
        <div class="layout-form-block--nested rounded-xl border border-slate-300 bg-white p-5 sm:p-6 space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="font-medium text-slate-800 text-base" x-text="'Блок шапки ' + (si + 1)"></div>
                <button type="button"
                        x-show="headerSections.length > 1"
                        x-cloak
                        @click="removeHeaderBlock(si)"
                        class="text-sm font-medium text-rose-600 hover:text-rose-800 hover:underline shrink-0">
                    Удалить блок
                </button>
            </div>
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
                    <div class="space-y-2">
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
                        <div x-show="line.editable" x-cloak class="flex flex-wrap gap-2 items-center pl-1 sm:pl-2 border-l-2 border-teal-200/90 ml-0.5 py-1">
                            <select x-model="headerRolePickerByLineKey[headerLineRoleKey(si, li)]" class="{{ $cSelSm }} min-w-[14rem]">
                                <option value="">Роль для ФИО…</option>
                                <template x-for="opt in headerRoleOptions()" :key="opt.key">
                                    <option :value="opt.key" x-text="opt.label"></option>
                                </template>
                            </select>
                            <button type="button" @click="insertRoleTokenIntoHeaderLine(si, li)"
                                    class="min-h-[40px] px-3 rounded-lg border border-teal-300 bg-teal-50 text-teal-800 text-sm font-medium hover:bg-teal-100 transition-colors shrink-0">
                                + ФИО по роли
                            </button>
                        </div>
                    </div>
                </template>
                <button type="button" @click="addHeaderLine(si)" class="text-teal-700 font-medium text-sm hover:text-teal-800 hover:underline">+ Строка</button>
                <p class="text-xs text-slate-500">Отметьте «В заявке», затем выберите роль и при необходимости нажмите «+ ФИО по роли» — в строку подставится токен вида <code>&#123;&#123;role:…&#125;&#125;</code>, в PDF он заменится на ФИО из БД.</p>
            </div>
        </div>
    </template>

    <div x-show="headerSections.length < maxHeaderBlocks" x-cloak class="rounded-xl border border-dashed border-teal-400 bg-teal-50/60 p-4 sm:p-5">
        <button type="button"
                @click="addHeaderBlock()"
                class="w-full sm:w-auto min-h-[44px] px-5 rounded-xl bg-white border-2 border-teal-600 text-teal-800 font-semibold text-sm hover:bg-teal-50 transition-colors shadow-sm">
            + Добавить блок шапки
            <span class="text-slate-500 font-normal" x-text="'(ещё ' + (maxHeaderBlocks - headerSections.length) + ' из ' + maxHeaderBlocks + ')'"></span>
        </button>
        <p class="text-xs text-slate-600 mt-3 leading-relaxed max-w-2xl">
            По умолчанию один блок. При необходимости добавьте второй и третий (например: ведомство, учреждение, название документа).
        </p>
    </div>
</div>

<input type="hidden" name="schema" id="schema" value="">
