<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $this->only('email')
        );

        if ($status != Password::RESET_LINK_SENT) {
            $this->addError('email', __($status));

            return;
        }

        $this->reset('email');

        session()->flash('status', __($status));
    }
}; ?>

<div>
    <flux:heading class="text-center mb-8" size="xl">{{ __('Reset password') }}</flux:heading>

    <flux:text class="text-center mb-6">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </flux:text>

    @if (session('status'))
        <flux:callout class="mb-6" icon="check-circle" color="success">
            {{ session('status') }}
        </flux:callout>
    @endif

    <form wire:submit="sendPasswordResetLink" class="space-y-6">
        <flux:input
            wire:model="email"
            label="{{ __('Email') }}"
            type="email"
            name="email"
            required
            autofocus
            :error="$errors->has('email')"
        />
        @error('email')
            <flux:error>{{ $message }}</flux:error>
        @enderror

        <flux:button variant="primary" type="submit" class="w-full">
            {{ __('Email Password Reset Link') }}
        </flux:button> {{ __('') }} <flux:link href="{{ route('login') }}" wire:navigate>{{ __('Back to login') }}</flux:link>
    </flux:subheading>
</div>
