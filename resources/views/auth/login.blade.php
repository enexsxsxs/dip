<x-guest-layout>
    <h2 class="text-xl font-semibold text-slate-800 mb-6">Вход в систему</h2>

    <x-auth-session-status class="mb-4 px-3 py-2 rounded-lg bg-teal-50 text-teal-800 text-sm border border-teal-100" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1.5 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password" :value="__('Пароль')" />
            <x-password-input id="password" class="block mt-1.5 w-full" name="password" required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center">
            <label for="remember_me" class="inline-flex items-center cursor-pointer">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-teal-600 shadow-sm focus:ring-teal-500 focus:ring-offset-0" name="remember">
                <span class="ms-2 text-sm text-slate-600">Запомнить меня</span>
            </label>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
            @if (Route::has('password.request'))
                <a class="text-sm text-teal-600 hover:text-teal-700 hover:underline focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2 rounded" href="{{ route('password.request') }}">
                    Забыли пароль?
                </a>
            @endif
            <x-primary-button class="w-full sm:w-auto sm:min-w-[140px] justify-center">
                Войти
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
