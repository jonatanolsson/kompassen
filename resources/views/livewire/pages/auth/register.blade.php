<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        event(new Registered($user = User::create($validated)));

        Auth::login($user);

        $this->redirect(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <flux:heading class="text-center mb-8" size="xl">{{ __('Create account') }}</flux:heading>

    <form wire:submit="register" class="space-y-6">
        <flux:input
            wire:model="name"
            label="{{ __('Name') }}"
            type="text"
            name="name"
            required
            autofocus
            autocomplete="name"
            :error="$errors->has('name')"
        />
        @error('name')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:input
            wire:model="email"
            label="{{ __('Email') }}"
            type="email"
            name="email"
            required
            autocomplete="username"
            :error="$errors->has('email')"
        />
        @error('email')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:input
            wire:model="password"
            label="{{ __('Password') }}"
            type="password"
            name="password"
            required
            autocomplete="new-password"
            :error="$errors->has('password')"
        />
        @error('password')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:input
            wire:model="password_confirmation"
            label="{{ __('Confirm Password') }}"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
            :error="$errors->has('password_confirmation')"
        />
        @error('password_confirmation')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Register') }}
        </flux:button>
    </form>

    <flux:subheading class="text-center mt-8">
        {{ __('Already have an account?') }} <flux:link href="{{ route('login') }}" wire:navigate>{{ __('Sign in') }}</flux:link>
    </flux:subheading>
</div>
