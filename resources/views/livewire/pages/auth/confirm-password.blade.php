<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $password = '';

    /**
     * Confirm the current user's password.
     */
    public function confirmPassword(): void
    {
        $this->validate([
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate([
            'email' => Auth::user()->email,
            'password' => $this->password,
        ])) {
            throw ValidationException::withMessages([
                'password' => __('auth.password'),
            ]);
        }

        session(['auth.password_confirmed_at' => time()]);

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading class="text-center mb-8" size="xl">{{ __('Confirm password') }}</flux:heading>

    <flux:text class="text-center mb-6">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </flux:text>

    <form wire:submit="confirmPassword" class="space-y-6">
        <flux:input
            wire:model="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="current-password"
            :error="$errors->has('password')"
        />
        @error('password')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Confirm') }}
        </flux:button>
    </form>
</div>
