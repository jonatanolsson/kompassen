<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}"> {{ __('') }} <meta name="viewport" content="width=device-width, initial-scale=1"> {{ __('') }} </title> {{ __('') }} <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts --> {{ __('') }} <div class="flex min-h-screen"> {{ __('') }} <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

                @php $brandLogo = \App\Models\AppSetting::get('brand_logo'); @endphp

                <flux:sidebar.brand href="{{ route('dashboard') }}">
                    @if ($brandLogo)
                        <img src="{{ Storage::url($brandLogo) }}" alt="{{ config('app.name') }}" class="max-h-8 max-w-full object-contain" />
                    @else
                        <span class="text-lg font-semibold text-zinc-800 dark:text-white">{{ config('app.name') }}</span> {{ __('') }} <flux:sidebar.nav>
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
                    </flux:sidebar.item> {{ __('') }} <flux:sidebar.nav>
                    <flux:sidebar.item as="button" wire:click="logout" icon="arrow-right-start-on-rectangle">
                        {{ __('Logout') }}
                    </flux:sidebar.item> {{ __('') }} <!-- Main Content --> {{ __('') }} <flux:header sticky container class="bg-white dark:bg-zinc-800 border-b border-zinc-200 dark:border-zinc-600"> {{ __('') }} </flux:heading> {{ __('') }} <flux:menu>
                                <flux:menu.group heading="{{ auth()->user()->name }}">
                                    <flux:menu.item href="{{ route('profile') }}" icon="user-circle" wire:navigate>
                                        {{ __('Profile') }}
                                    </flux:menu.item> {{ __('') }} <flux:menu.item icon="arrow-right-start-on-rectangle" variant="danger" as="button" wire:click="logout">
                                    {{ __('Logout') }}
                                </flux:menu.item> {{ __('') }} </flux:header> {{ __('') }} </flux:main> {{ __('') }} </body>
</html>
