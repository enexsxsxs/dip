<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Вход — {{ config('app.name', 'Система учета медицинского оборудования') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Source Sans 3', ui-sans-serif, system-ui, sans-serif; }
        </style>
    </head>
    <body class="antialiased min-h-screen bg-[#a8bcc9]">
        {{-- Фон с мягким приглушённым узором --}}
        <div class="fixed inset-0 overflow-hidden pointer-events-none" aria-hidden="true">
            <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-teal-300/20 blur-3xl"></div>
            <div class="absolute top-1/2 -left-32 w-64 h-64 rounded-full bg-slate-400/15 blur-3xl"></div>
            <div class="absolute bottom-0 left-1/2 w-[500px] h-[300px] rounded-full bg-teal-400/15 blur-3xl -translate-x-1/2 translate-y-1/2"></div>
        </div>

        <div class="relative min-h-screen flex flex-col sm:justify-center items-center py-8 sm:py-12 px-4">
            {{-- Логотип и название системы --}}
            <a href="/" class="flex flex-col items-center gap-4 mb-8 sm:mb-10 group">
                <div class="flex items-center justify-center w-20 h-20 rounded-2xl bg-white shadow-lg border border-teal-100 group-hover:border-teal-200 transition-colors">
                    <svg class="w-11 h-11 text-teal-600" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 8v8M8 12h8"/>
                    </svg>
                </div>
                <div class="text-center">
                    <h1 class="text-lg sm:text-xl font-semibold text-slate-800 tracking-tight">
                        Система учета медицинского оборудования
                    </h1>
                    <p class="text-sm text-slate-500 mt-0.5">Авторизация</p>
                </div>
            </a>

            {{-- Карточка формы --}}
            <div class="w-full sm:max-w-md px-6 py-8 sm:px-8 bg-white rounded-2xl shadow-xl border border-slate-100/80 overflow-hidden">
                {{-- Акцентная полоска сверху --}}
                <div class="h-1 -mx-6 sm:-mx-8 -mt-8 mb-6 rounded-t-2xl bg-gradient-to-r from-teal-500 to-cyan-500"></div>
                {{ $slot }}
            </div>

            <p class="mt-6 text-xs text-slate-500 text-center">
                Доступ только для авторизованных пользователей
            </p>
        </div>
    </body>
</html>
