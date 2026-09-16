<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    public function mount(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $this->form->email = 'test@example.com';
            $this->form->password = 'password';
        }
    }

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <div class="text-center">
        <flux:heading size="xl">{{ __('Welcome back') }}</flux:heading>
        <flux:subheading class="mt-2">{{ __('Log in to continue to Kompassen.') }}</flux:subheading>
    </div>

    @if (session('status'))
        <flux:callout class="mb-6" icon="check-circle" color="success">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="login" class="mt-8 space-y-6">
        <flux:input
            wire:model="form.email"
            label="{{ __('Email') }}"
            type="email"
            name="email"
            required
            autofocus
            autocomplete="username"
            :error="$errors->has('form.email')"
        />
        @error('form.email')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:field>
            <div class="mb-3 flex justify-between">
                <flux:label>{{ __('Password') }}</flux:label>
                @if (Route::has('password.request'))
                    <flux:link href="{{ route('password.request') }}" variant="subtle" wire:navigate class="text-sm">
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>
            <flux:input
                wire:model="form.password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                :error="$errors->has('form.password')"
            />
            @error('form.password')
                <flux:error>{{ $message }}</flux:error>
            @enderror
        </flux:field>

        <flux:checkbox wire:model="form.remember" label="{{ __('Remember me') }}" />

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Log in') }}
        </flux:button>
    </form>

    <flux:subheading class="text-center mt-8">
        {{ __('Need an account?') }} <flux:link href="{{ route('register') }}" wire:navigate>{{ __('Sign up') }}</flux:link>
    </flux:subheading>
</div>
