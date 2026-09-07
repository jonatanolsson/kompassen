<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Volt\Component;

new class extends Component
{
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}; ?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div>
        <flux:heading level="2" size="sm">{{ __('Update Password') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Use a long, random password to keep your account secure.') }}</flux:text>
    </div>

    <div class="sm:col-span-2">
        <form wire:submit="updatePassword" class="space-y-6">
            <flux:field>
                <flux:label>{{ __('Current Password') }}</flux:label>
                <flux:input wire:model="current_password" type="password" autocomplete="current-password" />
                <flux:error name="current_password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('New Password') }}</flux:label>
                <flux:input wire:model="password" type="password" autocomplete="new-password" />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Confirm Password') }}</flux:label>
                <flux:input wire:model="password_confirmation" type="password" autocomplete="new-password" />
                <flux:error name="password_confirmation" />
            </flux:field>

            <div class="flex items-center gap-3 pt-2">
                <flux:button type="submit" variant="primary">{{ __('Save') }}</flux:button>
                <span x-data="{ show: false }" x-show="show" x-on:password-updated.window="show = true; setTimeout(() => show = false, 2500)" class="text-sm text-green-600 dark:text-green-400">
                    Saved.
                </span>
            </div>
        </form>
    </div>
</div>
