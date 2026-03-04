@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center min-h-[48px] px-4 py-3 rounded-xl text-base font-semibold text-teal-700 bg-teal-50 border-2 border-teal-200'
    : 'inline-flex items-center min-h-[48px] px-4 py-3 rounded-xl text-base font-medium text-slate-700 hover:text-teal-700 hover:bg-teal-50 border-2 border-transparent hover:border-teal-100 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
