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
    </head>
    <body class="font-sans text-zinc-900 dark:text-zinc-100 antialiased">
        <div class="flex min-h-screen">
            <div class="flex-1 flex justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
                <div class="w-full max-w-md space-y-8">
                    <div class="flex justify-center">
                        <a href="/" class="group flex items-center gap-3 opacity-50 hover:opacity-100 transition">
                            <div>
                                <svg class="h-4 text-zinc-800 dark:text-white" viewBox="0 0 18 13" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <g>
                                        <line x1="1" y1="5" x2="1" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                        <line x1="5" y1="1" x2="5" y2="8" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                        <line x1="9" y1="5" x2="9" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                        <line x1="13" y1="1" x2="13" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                        <line x1="17" y1="5" x2="17" y2="10" stroke="currentColor" stroke-width="2" stroke-linecap="round"></line>
                                    </g>
                                </svg>
                            </div>
                            <span class="text-lg font-semibold text-zinc-800 dark:text-white">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    {{ $slot }}
                </div>
            </div>

            <!-- Illustration Panel -->
            <div class="hidden lg:flex lg:flex-1 lg:p-4">
                <div class="relative w-full rounded-lg bg-gradient-to-br from-zinc-900 to-zinc-950 flex flex-col items-start justify-end p-16" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 1440 320%22%3E%3Cpath fill=%22%23ffffff%22 fill-opacity=%220.1%22 d=%22M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,144C960,149,1056,139,1152,133.3C1248,128,1344,128,1392,128L1440,128L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z%22%3E%3C/path%3E%3C/svg%3E')">
                    <div class="flex gap-2 mb-4">
                        <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                    </div>

                    <div class="mb-6 italic font-base text-2xl xl:text-3xl text-white">
                        {{ __('Professional WCAG accessibility auditing and reporting tools.') }}
                    </div>

                    <div class="flex gap-4">
                        <flux:avatar src="https://ui-avatars.com/api/?name=Kompassen&background=667eea&color=fff" size="lg" />
                        <div class="flex flex-col justify-center font-medium">
                            <div class="text-lg text-white">{{ config('app.name') }}</div>
                            <div class="text-zinc-300">{{ __('Accessibility Made Easy') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
