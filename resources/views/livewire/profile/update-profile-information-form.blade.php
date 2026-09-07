<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component
{
    public string $name = '';
    public string $email = '';

    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
        ]);

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    public function sendVerification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?> {{ __('') }} <flux:heading level="2" size="sm">{{ __('Profile Information') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Update your name and email address.') }}</flux:text> {{ __('') }} <form wire:submit="updateProfileInformation" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label> {{ __('') }} </flux:field>

            <flux:field>
                <flux:label>{{ __('Email') }}</flux:label> {{ __('') }} <flux:callout color="amber" icon="exclamation-triangle" class="mt-2">
                        <flux:callout.text>
                        {{ __('Your email address is unverified.') }}
                            <flux:button variant="ghost" size="sm" wire:click.prevent="sendVerification" class="underline p-0">
                            {{ __('Re-send verification email') }}
                            </flux:button> {{ __('') }} <flux:callout color="green" icon="check-circle" class="mt-2">
                            <flux:callout.text>{{ __('A new verification link has been sent to your email address.') }}</flux:callout.text>
                        </flux:callout> {{ __('') }} <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <flux:text size="sm" x-data x-show="false" wire:loading.class.remove="hidden" wire:loading wire:target="updateProfileInformation" class="text-zinc-500">{{ __('Saving…') }}</flux:text>
                <span x-data="{ show: false }" x-show="show" x-on:profile-updated.window="show = true; setTimeout(() => show = false, 2500)" class="text-sm text-green-600 dark:text-green-400">
                    {{ __('Saved.') }}
                </span> {{ __('') }} </div>
</div>
