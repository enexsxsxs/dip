@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full min-h-[48px] ps-4 pe-4 py-3 border-l-4 border-teal-500 text-start text-base font-semibold text-teal-700 bg-teal-50 flex items-center'
    : 'block w-full min-h-[48px] ps-4 pe-4 py-3 border-l-4 border-transparent text-start text-base font-medium text-slate-700 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 transition flex items-center';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
