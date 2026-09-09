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
    <body class="font-sans antialiased">
        <div class="flex min-h-screen">
            <!-- Sidebar -->
            <flux:sidebar collapsible="mobile" class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
                <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                @php $brandLogo = \App\Models\AppSetting::get('brand_logo'); @endphp

                <flux:sidebar.brand href="{{ route('dashboard') }}">
                    @if ($brandLogo)
                        <img src="{{ Storage::url($brandLogo) }}" alt="{{ config('app.name') }}" class="max-h-8 max-w-full object-contain" />
                    @else
                        <span class="text-lg font-semibold text-zinc-800 dark:text-white">{{ config('app.name') }}</span>
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
                        <flux:sidebar.item type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Logout') }}
                        </flux:sidebar.item>
                    </form>
                </flux:sidebar.nav>
            </flux:sidebar>

            <!-- Main Content -->
            <div class="flex flex-col flex-1">
                <!-- Header -->
                <flux:header sticky container class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-600">
                    <flux:sidebar.toggle class="lg:hidden" icon="bars-2" />

                    <flux:heading level="1" size="lg" class="flex-1 truncate">
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

                                <form method="POST" action="{{ route('logout') }}" class="w-full">
                                    @csrf
                                    <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" type="submit" class="w-full">
                                        {{ __('Logout') }}
                                    </flux:menu.item>
                                </form>
                            </flux:menu>
                        </flux:dropdown>
                    @endauth
                </flux:header>

                <!-- Page Content -->
                <flux:main container class="flex-1">
                    {{ $slot }}
                </flux:main>
            </div>
        </div>
    </body>
</html>
