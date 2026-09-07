<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?> {{ __('') }} <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"> {{ __('') }} <!-- Logo --> {{ __('') }} <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" /> {{ __('') }} <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link> {{ __('') }} <!-- Settings Dropdown --> {{ __('') }} <x-slot name="trigger"> {{ __('') }} </div> {{ __('') }} <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /> {{ __('') }} </button> {{ __('') }} <x-dropdown-link :href="route('profile')" wire:navigate>
                            {{ __('Profile') }}
                        </x-dropdown-link> {{ __('') }} <x-dropdown-link>
                                {{ __('Log Out') }}
                            </x-dropdown-link> {{ __('') }} </x-dropdown> {{ __('') }} <div class="-me-2 flex items-center sm:hidden"> {{ __('') }} <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /> {{ __('') }} </button> {{ __('') }} </div> {{ __('') }} <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                {{ __('Dashboard') }}
            </x-responsive-nav-link> {{ __('') }} <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600"> {{ __('') }} </div>
                <div class="font-medium text-sm text-gray-500">{{ auth()->user()-> {{ __('') }} <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile')" wire:navigate>
                    {{ __('Profile') }}
                </x-responsive-nav-link> {{ __('') }} <x-responsive-nav-link>
                        {{ __('Log Out') }}
                    </x-responsive-nav-link> {{ __('') }} </div>
    </div>
</nav>
