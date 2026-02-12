<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">
                ← Пользователи
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Добавление пользователя
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('users.store') }}">
                        @csrf

                        <!-- Фамилия -->
                        <div>
                            <x-input-label for="surname" value="Фамилия" />
                            <x-text-input id="surname" class="block mt-1 w-full" type="text" name="surname"
                                          :value="old('surname')" required autofocus maxlength="255" />
                            <x-input-error :messages="$errors->get('surname')" class="mt-2" />
                        </div>

                        <!-- Имя -->
                        <div class="mt-4">
                            <x-input-label for="name" value="Имя" />
                            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name"
                                          :value="old('name')" required maxlength="255" />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <!-- Отчество -->
                        <div class="mt-4">
                            <x-input-label for="patronymic" value="Отчество" />
                            <x-text-input id="patronymic" class="block mt-1 w-full" type="text" name="patronymic"
                                          :value="old('patronymic')" required maxlength="255" />
                            <x-input-error :messages="$errors->get('patronymic')" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div class="mt-4">
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                                          :value="old('email')" required maxlength="255" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <!-- Роль -->
                        <div class="mt-4">
                            <x-input-label for="role" value="Роль" />
                            <select id="role" name="role" required
                                    class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="">Выберите роль</option>
                                @foreach(\App\Models\User::ROLES as $roleKey)
                                    <option value="{{ $roleKey }}" @selected(old('role') === $roleKey)>
                                        {{ \App\Models\User::ROLE_LABELS[$roleKey] }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <!-- Пароль -->
                        <div class="mt-4">
                            <x-input-label for="password" value="Пароль" />
                            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password"
                                          required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <!-- Подтверждение пароля -->
                        <div class="mt-4">
                            <x-input-label for="password_confirmation" value="Подтверждение пароля" />
                            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                                          name="password_confirmation" required autocomplete="new-password" />
                            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-end mt-6 gap-4">
                            <a href="{{ route('users.index') }}" class="text-gray-600 dark:text-gray-400 hover:underline">
                                Отмена
                            </a>
                            <x-primary-button>
                                Добавить пользователя
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
