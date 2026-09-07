<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component
{
    public string $password = '';

    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => ['required', 'string', 'current_password'],
        ]);

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
    <div>
        <flux:heading level="2" size="sm" class="text-red-600 dark:text-red-400">{{ __('Delete Account') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Once deleted, all your data will be permanently removed.') }}</flux:text>
    </div>

    <div class="sm:col-span-2">
        <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger">{{ __('Delete Account') }}</flux:button>
        </flux:modal.trigger>

        <flux:modal name="confirm-user-deletion" class="max-w-md">
            <form wire:submit="deleteUser" class="space-y-5">
                <div>
                    <flux:heading level="2" size="lg">{{ __('Delete Account?') }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                        {{ __('This is permanent. All your data will be deleted and cannot be recovered. Please enter your password to confirm.') }}
                    </flux:text>
                </div>

                <flux:field>
                    <flux:label class="sr-only">{{ __('Password') }}</flux:label>
                    <flux:input wire:model="password" type="password" placeholder="{{ __('Your password') }}" autofocus />
                    <flux:error name="password" />
                </flux:field>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">{{ __('Delete Account') }}</flux:button>
                </div>
            </form>
        </flux:modal>
    </div>
</div>
