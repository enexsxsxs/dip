<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name')) — Система учета медицинского оборудования</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=source-sans-3:400,500,600,700&display=swap" rel="stylesheet" />
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            html { font-size: 17px; }
            body { font-family: 'Source Sans 3', ui-sans-serif, system-ui, sans-serif; }
            /* Крупные кликабельные области для удобства пользователей 30–70 лет */
            .btn-large { min-height: 48px; padding: 0.75rem 1.25rem; font-size: 1rem; font-weight: 600; }
            .link-large { font-size: 1rem; padding: 0.35em 0; }
        </style>
    </head>
    <body class="antialiased min-h-screen bg-[#a8bcc9]">
        @php
            // Страницы, где контент должен быть во всю ширину
            $isWideLayout = request()->routeIs('dashboard') || request()->routeIs('equipment.index');
            $containerClasses = $isWideLayout
                ? 'w-full'
                : 'max-w-7xl mx-auto';
        @endphp

        @include('layouts.navigation')

        @isset($header)
            <header class="bg-white/90 backdrop-blur border-b border-teal-100 shadow-sm">
                <div class="{{ $containerClasses }} py-5 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <main class="{{ $containerClasses }} py-8 px-4 sm:px-6 lg:px-8 min-h-0 overflow-visible">
            {{ $slot }}
        </main>

        {{-- Уведомления в правом нижнем углу: добавление/редактирование — бирюзовый, удаление — другой цвет --}}
        @if(session('success') || session('status') || session('deleted') || session('error'))
            <div class="fixed bottom-6 right-6 z-50 max-w-sm" x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                @if(session('success'))
                    <div class="rounded-xl px-5 py-4 shadow-lg border-2 border-teal-400 bg-teal-500 text-white font-medium text-base flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-teal-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @elseif(session('status'))
                    <div class="rounded-xl px-5 py-4 shadow-lg border-2 border-teal-400 bg-teal-500 text-white font-medium text-base flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-teal-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>{{ session('status') }}</span>
                    </div>
                @elseif(session('deleted'))
                    <div class="rounded-xl px-5 py-4 shadow-lg border-2 border-amber-400 bg-amber-500 text-white font-medium text-base flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-amber-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>{{ session('deleted') }}</span>
                    </div>
                @elseif(session('error'))
                    <div class="rounded-xl px-5 py-4 shadow-lg border-2 border-red-400 bg-red-500 text-white font-medium text-base flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-red-100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
            </div>
        @endif

        @stack('scripts')
    </body>
</html>
