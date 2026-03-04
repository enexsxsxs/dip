<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center min-h-[48px] px-6 py-3 bg-teal-600 hover:bg-teal-700 border border-transparent rounded-xl font-semibold text-base text-white focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 active:bg-teal-800 transition ease-in-out duration-150 shadow-sm']) }}>
    {{ $slot }}
</button>
