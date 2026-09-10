<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Kompassen') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="flex min-h-screen">
            <!-- Left Panel - Content -->
            <div class="flex-1 flex justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
                <div class="w-full max-w-md space-y-8">
                    <div class="flex justify-center opacity-50">
                        <a href="/" class="group flex items-center gap-3">
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
                            <span class="text-xl font-semibold text-zinc-800 dark:text-white">{{ config('app.name') }}</span>
                        </a>
                    </div>

                    <flux:heading class="text-center" size="xl">{{ __('Welcome') }}</flux:heading>

                    <div class="space-y-4">
                        <flux:field>
                            <flux:label for="email">{{ __('Email') }}</flux:label>
                            <flux:input id="email" type="email" placeholder="{{ __('you@example.com') }}" />
                        </flux:field>

                        <flux:field>
                            <flux:label for="password">{{ __('Password') }}</flux:label>
                            <flux:input id="password" type="password" placeholder="{{ __('Your password') }}" />
                        </flux:field>

                        <flux:checkbox label="{{ __('Remember me for 30 days') }}" />

                        @if (Route::has('login'))
                            <flux:button variant="primary" class="w-full" href="{{ route('login') }}" wire:navigate>
                                {{ __('Log in') }}
                            </flux:button>
                        @endif
                    </div>

                    @if (Route::has('register'))
                        <flux:subheading class="text-center">
                            {{ __('First time around here?') }}
                            <flux:link href="{{ route('register') }}" variant="subtle" class="text-sm" wire:navigate>
                                {{ __('Sign up for free') }}
                            </flux:link>
                        </flux:subheading>
                    @endif
                </div>
            </div>

            <!-- Right Panel - Illustration -->
            <div class="hidden lg:flex lg:flex-1 lg:p-4">
                <div class="relative w-full rounded-lg bg-gradient-to-br from-zinc-900 to-zinc-950 flex flex-col items-start justify-end p-16 overflow-hidden">
                    <!-- Animated background -->
                    <div class="absolute inset-0 opacity-20">
                        <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
                        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse"></div>
                    </div>

                    <div class="relative z-10">
                        <div class="flex gap-2 mb-4">
                            <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                            <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                            <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                            <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                            <flux:icon icon="star" variant="solid" class="text-yellow-400" />
                        </div>

                        <div class="mb-6 italic font-base text-2xl xl:text-3xl text-white leading-relaxed">
                            {{ __('Comprehensive WCAG accessibility audits and professional reporting for your web applications.') }}
                        </div>

                        <div class="flex gap-4">
                            <flux:avatar src="https://ui-avatars.com/api/?name=Kompassen&background=667eea&color=fff" size="lg" />
                            <div class="flex flex-col justify-center font-medium">
                                <div class="text-lg text-white">{{ config('app.name') }}</div>
                                <div class="text-zinc-300">{{ __('Web Accessibility Auditing') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
