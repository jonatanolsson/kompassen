<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Kompassen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @fluxAppearance
    </head>
    <body class="font-sans text-zinc-900 antialiased dark:text-zinc-100">
        <div class="flex min-h-screen items-center justify-center bg-zinc-50 px-4 py-12 dark:bg-zinc-950">
            <main class="w-full max-w-md">
                <div class="mb-8 text-center">
                    <a href="/" class="text-xl font-semibold text-zinc-900 transition hover:text-zinc-600 dark:text-white dark:hover:text-zinc-300">
                        {{ config('app.name', 'Kompassen') }}
                    </a>
                </div>

                <flux:card class="p-6 sm:p-8">
                    {{ $slot }}
                </flux:card>
            </main>
        </div>
        @livewireScripts
        @fluxScripts
    </body>
</html>
