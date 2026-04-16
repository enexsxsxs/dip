@php
    $allowedRichTags = '<p><br><br/><strong><b><i><em><u><ul><ol><li><span><div><hr><table><thead><tbody><tr><th><td><h1><h2><h3><h4>';
@endphp
@if($value === null || $value === '')
    <span class="text-slate-400 italic">не заполнено</span>
@elseif(is_array($value))
    @if(count($value) === 0)
        <span class="text-slate-400 italic">—</span>
    @elseif(array_is_list($value) && collect($value)->every(fn ($x) => is_string($x) || is_numeric($x)))
        <ul class="list-disc pl-5 space-y-1 text-slate-900">
            @foreach($value as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @elseif(array_is_list($value) && collect($value)->every(fn ($row) => is_array($row)))
        @php
            $headers = [];
            foreach ($value as $row) {
                if (is_array($row)) {
                    foreach (array_keys($row) as $h) {
                        $headers[$h] = true;
                    }
                }
            }
            $headers = array_keys($headers);
        @endphp
        @if(count($headers) > 0)
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="min-w-full text-sm text-slate-800">
                    <thead class="bg-slate-100 text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            @foreach($headers as $h)
                                <th class="px-3 py-2 border-b border-slate-200">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($value as $row)
                            @if(is_array($row))
                                <tr class="align-top">
                                    @foreach($headers as $h)
                                        <td class="px-3 py-2 border-slate-100">
                                            @if(array_key_exists($h, $row))
                                                @include('report-requests.partials.data-display', ['value' => $row[$h]])
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <pre class="text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl p-4 overflow-x-auto">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
        @endif
    @else
        <pre class="text-xs font-mono bg-slate-50 border border-slate-200 rounded-xl p-4 overflow-x-auto">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
    @endif
@elseif(is_bool($value))
    <span class="text-slate-900 font-medium">{{ $value ? 'Да' : 'Нет' }}</span>
@elseif(is_string($value) && preg_match('/<[a-z][\s\S]*>/iu', $value))
    <div class="report-request-rich text-base text-slate-900 leading-relaxed">
        {!! strip_tags($value, $allowedRichTags) !!}
    </div>
@else
    <div class="whitespace-pre-wrap text-slate-900">{{ is_scalar($value) ? (string) $value : json_encode($value, JSON_UNESCAPED_UNICODE) }}</div>
@endif
