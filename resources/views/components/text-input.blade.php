@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-teal-500 focus:ring-teal-500 rounded-xl shadow-sm placeholder:text-slate-400 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:placeholder:text-slate-500 dark:focus:border-teal-400 dark:focus:ring-teal-400']) }}>
