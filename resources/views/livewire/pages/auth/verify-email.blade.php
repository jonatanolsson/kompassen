<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    /**
     * Send an email verification notification to the user.
     */
    public function sendVerification(): void
    {
        if (Auth::user()->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

            return;
        }

        Auth::user()->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <flux:heading class="text-center mb-8" size="xl">{{ __('Verify email') }}</flux:heading>

    <flux:text class="text-center mb-6">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </flux:text>

    @if (session('status') == 'verification-link-sent')
        <flux:callout class="mb-6" icon="check-circle" color="success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </flux:callout>
    @endif

    <div class="flex gap-3">
        <flux:button variant="primary" wire:click="sendVerification" class="flex-1">
            {{ __('Resend Verification Email') }}
        </flux:button>

        <flux:button variant="ghost" wire:click="logout" class="flex-1">
            {{ __('Log Out') }}
        </flux:button>
    </div>
</div>
