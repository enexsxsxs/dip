@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-teal-700 bg-teal-50 border border-teal-200'
    : 'inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-teal-700 hover:bg-teal-50 border border-transparent hover:border-teal-100 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
