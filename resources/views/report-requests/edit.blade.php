<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Заявка №{{ $record->registry_number ?? $record->id }} — редактирование</h2>
    </x-slot>

    <div class="max-w-4xl bg-white/95 rounded-2xl shadow-sm border border-teal-100 p-6 sm:p-8"
         x-data="reportRequestForm(@js($layoutsPayload), @js($initialFormData))"
         x-init="init()">
        @if($record->trashed())
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-4 mb-6 text-base text-amber-950">
                Эта заявка <strong>скрыта из списка</strong>. После сохранения она снова появится в списке. Либо откройте <a href="{{ route('admin.activity-archive') }}" class="font-semibold text-teal-800 underline">Архив и журнал</a> и восстановите заявку без правок.
            </div>
        @endif
        <form method="post" action="{{ route('report-requests.update', $record) }}" class="space-y-6" @submit="syncDataJson()">
            @csrf
            @method('PUT')
            <div>
                <label for="request_layout_id" class="block text-sm font-semibold text-slate-700 mb-1">Макет</label>
                <select name="request_layout_id" id="request_layout_id" required
                        @change="onLayoutChange()"
                        class="select-teal-arrow w-full rounded-xl border-teal-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[48px] pl-3">
                    @foreach($layouts as $l)
                        <option value="{{ $l->id }}" @selected(old('request_layout_id', $record->request_layout_id) == $l->id)>{{ $l->title }}@if($l->trashed()) (скрытый макет)@endif</option>
                    @endforeach
                </select>
                @error('request_layout_id')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="recipient_user_id" class="block text-sm font-semibold text-slate-700 mb-1">Получатель (для шапки), опционально</label>
                <select name="recipient_user_id" id="recipient_user_id"
                        class="select-teal-arrow w-full rounded-xl border-teal-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[48px] pl-3">
                    <option value="">— как автор —</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" @selected(old('recipient_user_id', $record->data['recipient_user_id'] ?? '') == $u->id)>{{ $u->name }} (id {{ $u->id }})</option>
                    @endforeach
                </select>
            </div>

            <div x-show="editableHeaderLines.length > 0" x-cloak
                 class="space-y-4 rounded-2xl border-2 border-slate-200 bg-slate-50/80 p-5">
                <h3 class="text-lg font-bold text-slate-800">Строки шапки (по макету)</h3>
                <p class="text-sm text-slate-600">Эти поля отмечены в макете как «В заявке» — обычно ФИО или другой текст, который меняется от документа к документу. Значение из макета подставлено по умолчанию; при необходимости исправьте.</p>
                <template x-for="row in editableHeaderLines" :key="row.line_id">
                    <div class="space-y-1">
                        <label class="block text-sm font-semibold text-slate-800" :for="'hdr_' + row.line_id" x-text="row.label"></label>
                        <p class="text-xs text-slate-500" x-show="row.default_text" x-text="'Текст в макете: ' + row.default_text"></p>
                        <input type="text" :id="'hdr_' + row.line_id"
                               x-model="headerOverrides[row.line_id]"
                               @input="syncDataJson()"
                               class="w-full rounded-xl border border-slate-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[48px] px-3">
                    </div>
                </template>
            </div>

            <div class="space-y-4 rounded-2xl border-2 border-teal-100 bg-teal-50/30 p-5">
                <h3 class="text-lg font-bold text-teal-900">Данные для полей макета</h3>
                <p class="text-sm text-slate-600">Соответствует вкладке «Поля заявки» в макете. Эти значения подставятся в PDF вместо <code class="text-xs">@{{field:…}}</code>.</p>
                <p class="text-sm text-slate-500">Текстовые поля — с панелью форматирования (как в макете). Базовый шрифт и размер совпадают с настройками «Текста заявки» в выбранном макете; в PDF — Times New Roman или Arial.</p>

                <div x-show="hasRichTextFields" class="flex flex-wrap gap-2 items-end p-2 rounded-xl border border-teal-200 bg-white mb-2">
                    <div>
                        <label class="block text-xs text-slate-500 mb-0.5">К выделению в активном поле: шрифт</label>
                        <select x-model="selectionFontFamily" class="select-teal-arrow rounded-lg border-teal-200 text-sm min-h-[40px] pl-2 min-w-[11rem]">
                            <option value="DejaVu Serif">Times New Roman</option>
                            <option value="DejaVu Sans">Arial</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-slate-500 mb-0.5">Размер (pt)</label>
                        <select x-model.number="selectionFontSizePt" class="select-teal-arrow rounded-lg border-teal-200 text-sm min-h-[40px] pl-2 w-[5.5rem]">
                            @foreach([9,10,11,12,13,14,16,18] as $pt)
                                <option value="{{ $pt }}">{{ $pt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <p class="text-xs text-slate-500 max-w-xs pb-1">Кликните в нужное текстовое поле, выделите фрагмент и нажмите кнопку у этого поля «К выделению».</p>
                </div>

                <template x-for="field in activeFields" :key="layoutFieldKey(field.id)">
                    <div class="space-y-1 pb-4 border-b border-teal-100 last:border-0">
                        <label class="block text-sm font-semibold text-slate-800" :for="'fld_' + field.id" x-text="field.name || field.id"></label>
                        <select x-show="field.type === 'select'"
                                :id="'fld_' + field.id"
                                x-model="values[field.id]"
                                @change="syncDataJson()"
                                class="select-teal-arrow w-full rounded-xl border-teal-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[48px] pl-3">
                            <option value="">— выберите —</option>
                            <template x-for="(opt, oi) in (field.options || [])" :key="oi">
                                <option :value="opt" x-text="opt"></option>
                            </template>
                        </select>
                        <input x-show="field.type === 'number'" type="text" inputmode="decimal" :id="'fld_' + field.id"
                               x-model="values[field.id]"
                               @input="syncDataJson()"
                               class="w-full rounded-xl border-teal-200 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-base min-h-[48px] px-3"
                               placeholder="Число">
                        <div x-show="field.type !== 'select' && field.type !== 'number'" class="space-y-2">
                            <div class="flex flex-wrap gap-1 p-2 rounded-xl border border-teal-200 bg-slate-50">
                                <button type="button" @click="rteApplySelectionFont(field.id)"
                                        class="min-h-[40px] px-3 rounded-lg bg-teal-600 text-white font-semibold text-sm hover:bg-teal-700 mr-1">
                                    К выделению
                                </button>
                                <button type="button" @click="rteExec(field.id, 'bold')" class="min-w-[40px] min-h-[40px] rounded-lg font-bold border border-slate-200 bg-white hover:bg-teal-50" title="Жирный">B</button>
                                <button type="button" @click="rteExec(field.id, 'justifyLeft')" class="min-h-[40px] px-2 rounded-lg border border-slate-200 bg-white hover:bg-teal-50 text-sm">Слева</button>
                                <button type="button" @click="rteExec(field.id, 'justifyCenter')" class="min-h-[40px] px-2 rounded-lg border border-slate-200 bg-white hover:bg-teal-50 text-sm">Центр</button>
                                <button type="button" @click="rteExec(field.id, 'justifyRight')" class="min-h-[40px] px-2 rounded-lg border border-slate-200 bg-white hover:bg-teal-50 text-sm">Справа</button>
                                <span class="w-px h-8 bg-slate-300 mx-1 self-center"></span>
                                <button type="button" @click="rteInsertHr(field.id)" class="min-h-[40px] px-2 rounded-lg border border-slate-200 bg-white hover:bg-teal-50 text-sm">Линия</button>
                                <span class="w-px h-8 bg-slate-300 mx-1 self-center"></span>
                                <button type="button" @click="rteRefreshBaseStyle(field.id)" class="min-h-[40px] px-2 rounded-lg border border-slate-200 bg-white hover:bg-teal-50 text-sm" title="Применить базовый шрифт макета">Базовый стиль</button>
                            </div>
                            <div :id="'rte_' + field.id"
                                 contenteditable="true"
                                 tabindex="0"
                                 class="min-h-[140px] w-full rounded-xl border-2 border-teal-200 bg-white p-3 text-base focus:outline-none focus:ring-2 focus:ring-teal-400"
                                 x-init="richInit($el, field.id)"
                                 @input="richSync(field.id, $el)"
                                 @blur="richSync(field.id, $el)"></div>
                        </div>
                    </div>
                </template>

                <p class="text-slate-500 text-sm" x-show="activeFields.length === 0">У этого макета нет полей — достаточно текста заявки (и системных вставок в PDF, если они есть в макете).</p>
            </div>

            <input type="hidden" name="data" id="request_data_hidden" x-bind:value="dataJson">

            @error('data')<p class="text-rose-600 text-sm mt-1">{{ $message }}</p>@enderror

            <div class="flex flex-wrap gap-4">
                <button type="submit" class="min-h-[48px] px-6 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">Сохранить</button>
                <a href="{{ route('report-requests.show', $record) }}" class="inline-flex items-center min-h-[48px] px-6 rounded-xl border-2 border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">К просмотру</a>
                <a href="{{ route('report-requests.index') }}" class="inline-flex items-center min-h-[48px] px-6 rounded-xl border-2 border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">К списку</a>
            </div>
        </form>
    </div>
</x-app-layout>
