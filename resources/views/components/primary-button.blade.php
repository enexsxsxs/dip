<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-5 py-2.5 bg-teal-600 hover:bg-teal-700 border border-transparent rounded-xl font-semibold text-sm text-white uppercase tracking-wide focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 active:bg-teal-800 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
