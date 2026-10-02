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
    <body class="font-sans antialiased text-zinc-900 dark:text-zinc-100">
        <div class="flex min-h-screen bg-zinc-100 dark:bg-zinc-950">
            <!-- Sidebar -->
            <flux:sidebar collapsible="mobile" class="border-r border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-900">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                @php $brandLogo = \App\Models\AppSetting::get('brand_logo'); @endphp

                <flux:sidebar.brand href="{{ route('dashboard') }}">
                    @if ($brandLogo)
                        <img src="{{ Storage::url($brandLogo) }}" alt="{{ config('app.name') }}" class="max-h-8 max-w-full object-contain" />
                    @else
                        <span class="flex items-center gap-2 text-lg font-semibold tracking-tight text-zinc-900 dark:text-white">
                            <span class="flex size-7 items-center justify-center rounded-lg bg-blue-600 text-sm font-bold text-white">K</span>
                            {{ config('app.name') }}
                        </span>
                    @endif
                </flux:sidebar.brand>

                <flux:separator />

                <flux:sidebar.nav>
                    <flux:sidebar.item href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')" icon="home" wire:navigate>
                        {{ __('Dashboard') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('wcag.knowledge-base') }}" :active="request()->routeIs('wcag.knowledge-base')" icon="book-open" wire:navigate>
                        {{ __('Knowledge Base') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('testing-methodologies') }}" :active="request()->routeIs('testing-methodologies')" icon="beaker" wire:navigate>
                        {{ __('Testing Methodology') }}
                    </flux:sidebar.item>
                    @if (app()->environment(['local', 'testing']))
                        <flux:sidebar.item href="{{ route('design-system') }}" :active="request()->routeIs('design-system')" wire:navigate>
                            {{ __('Design System') }}
                        </flux:sidebar.item>
                    @endif
                    <flux:sidebar.item href="{{ route('accessibility-projects.index') }}" :active="request()->routeIs('accessibility-projects.*')" icon="document-text" wire:navigate>
                        {{ __('Accessibility') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('profile') }}" :active="request()->routeIs('profile')" icon="user" wire:navigate>
                        {{ __('Profile') }}
                    </flux:sidebar.item>
                    <flux:sidebar.item href="{{ route('settings') }}" :active="request()->routeIs('settings')" icon="cog-6-tooth" wire:navigate>
                        {{ __('Settings') }}
                    </flux:sidebar.item>
                </flux:sidebar.nav>

                <flux:sidebar.spacer />

                <flux:sidebar.nav>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:sidebar.item as="button" type="submit" icon="arrow-right-start-on-rectangle">
                            {{ __('Logout') }}
                        </flux:sidebar.item>
                    </form>
                </flux:sidebar.nav>
            </flux:sidebar>

            <!-- Main Content -->
            <div class="flex min-w-0 flex-1 flex-col">
                <!-- Header -->
                <flux:header sticky container class="border-b border-zinc-200 bg-white/95 backdrop-blur dark:border-zinc-800 dark:bg-zinc-900/95">
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

                    <flux:heading level="2" size="lg" class="flex-1 truncate">
                        {{ isset($title) ? $title : config('app.name') }}
                    </flux:heading>

                    @auth
                        <flux:dropdown align="end">
                            <flux:button variant="subtle" icon="user" size="sm" />

                            <flux:menu>
                                <flux:menu.group heading="{{ auth()->user()->name }}">
                                    <flux:menu.item href="{{ route('profile') }}" icon="user-circle" wire:navigate>
                                        {{ __('Profile') }}
                                    </flux:menu.item>
                                </flux:menu.group>

                                <flux:menu.separator />

                                <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" as="form" action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full text-left">{{ __('Logout') }}</button>
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    @endauth
                </flux:header>

                <!-- Page Content -->
                <flux:main class="flex-1 bg-zinc-100/70 dark:bg-zinc-950/70">
                    @if (session('success'))
                        <flux:callout class="mb-6" color="green" icon="check-circle">
                            <flux:callout.text>{{ session('success') }}</flux:callout.text>
                        </flux:callout>
                    @endif

                    @if (session('error'))
                        <flux:callout class="mb-6" color="red" icon="exclamation-triangle">
                            <flux:callout.text>{{ session('error') }}</flux:callout.text>
                        </flux:callout>
                    @endif

                    @if ($errors->any())
                        <flux:callout class="mb-6" color="red" icon="exclamation-triangle">
                            <flux:callout.text>
                                <ul class="list-disc space-y-1 ps-5">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </flux:callout.text>
                        </flux:callout>
                    @endif

                    {{ $slot }}
                </flux:main>
            </div>
        </div>
            @livewireScripts
    <flux:toast />
    @fluxScripts
    </body>
</html>
