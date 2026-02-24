@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full ps-4 pe-4 py-2 border-l-4 border-teal-500 text-start text-base font-medium text-teal-700 bg-teal-50'
    : 'block w-full ps-4 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-slate-600 hover:bg-teal-50 hover:text-teal-700 hover:border-teal-200 transition';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
