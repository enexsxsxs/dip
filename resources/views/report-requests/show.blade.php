<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-2xl text-slate-800 leading-tight">Заявка №{{ $record->registry_number ?? $record->id }}</h2>
            <a href="{{ route('report-requests.pdf', $record) }}"
               class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl bg-teal-600 text-white font-semibold hover:bg-teal-700 transition">
                Скачать PDF
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl space-y-6">
        @if($record->trashed())
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 p-4 text-base text-amber-950">
                Заявка <strong>скрыта из списка</strong>. <a href="{{ route('report-requests.edit', $record) }}" class="font-semibold text-teal-800 underline">Изменить</a> или восстановите в <a href="{{ route('admin.activity-archive') }}" class="font-semibold text-teal-800 underline">Архив и журнал</a>.
            </div>
        @endif
        <div class="bg-white/95 rounded-2xl shadow-sm border border-teal-100 p-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-base">
                <div>
                    <dt class="text-slate-500 text-sm font-semibold">Макет</dt>
                    <dd class="text-slate-900 font-medium">{{ $record->layout?->title ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-sm font-semibold">Автор</dt>
                    <dd class="text-slate-900">{{ $record->author?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-sm font-semibold">Создана</dt>
                    <dd class="text-slate-900">{{ $record->created_at?->format('d.m.Y H:i') }}</dd>
                </div>
            </dl>
        </div>

        <div class="flex flex-wrap gap-3">
            <a href="{{ route('report-requests.edit', $record) }}"
               class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl border-2 border-teal-300 text-teal-800 font-semibold hover:bg-teal-50 transition">
                Изменить заявку
            </a>
            @if(!$record->trashed())
                <form action="{{ route('report-requests.destroy', $record) }}" method="post" class="inline"
                      onsubmit="return confirm('Скрыть заявку из списка? Восстановить можно в «Архив и журнал».');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center min-h-[48px] px-4 py-2 rounded-xl border-2 border-rose-200 text-rose-700 font-semibold hover:bg-rose-50 transition">
                        Скрыть из списка
                    </button>
                </form>
            @endif
        </div>

        <div class="bg-white/95 rounded-2xl shadow-sm border border-teal-100 p-6 sm:p-8">
            <h3 class="text-lg font-semibold text-slate-800 mb-6">Данные заявки</h3>

            @if($recipientUser)
                <div class="mb-8 pb-6 border-b border-teal-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Получатель (для шапки документа)</p>
                    <p class="text-base font-medium text-slate-900">{{ $recipientUser->name }}</p>
                </div>
            @endif

            @if(count($headerOverrides) > 0)
                <div class="mb-8 pb-6 border-b border-teal-100">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 mb-3">Строки шапки (значения из заявки)</p>
                    <dl class="space-y-4">
                        @foreach($headerOverrides as $hid => $hval)
                            <div>
                                <dt class="text-sm font-semibold text-slate-600 mb-1">{{ $headerLineLabels[$hid] ?? $hid }}</dt>
                                <dd class="text-base border-l-4 border-slate-300 pl-4 py-1">{{ $hval === '' ? '—' : $hval }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <dl class="space-y-8">
                @foreach($layoutFields as $field)
                    @php
                        $fieldId = isset($field['id']) ? (string) $field['id'] : '';
                        $fieldLabel = isset($field['name']) ? trim((string) $field['name']) : '';
                        if ($fieldLabel === '') {
                            $fieldLabel = $fieldId !== '' ? $fieldId : 'Поле';
                        }
                    @endphp
                    @if($fieldId === '')
                        @continue
                    @endif
                    <div>
                        <dt class="text-sm font-semibold text-slate-600 mb-2">{{ $fieldLabel }}</dt>
                        <dd class="text-base border-l-4 border-teal-200 pl-4 py-1 min-h-[1.5rem]">
                            @include('report-requests.partials.data-display', ['value' => $data[$fieldId] ?? null])
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if(count($extraData) > 0)
                <div class="mt-10 pt-8 border-t border-slate-200">
                    <h4 class="text-base font-semibold text-slate-800 mb-4">Дополнительные данные</h4>
                    <dl class="space-y-6">
                        @foreach($extraData as $key => $val)
                            <div>
                                <dt class="text-sm font-semibold text-slate-600 mb-2">{{ $key }}</dt>
                                <dd class="text-base border-l-4 border-slate-200 pl-4 py-1">
                                    @include('report-requests.partials.data-display', ['value' => $val])
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>

        <a href="{{ route('report-requests.index') }}" class="inline-flex items-center text-teal-700 font-semibold hover:underline">← К списку заявок</a>
    </div>
</x-app-layout>
