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
}; ?> {{ __('') }} <flux:heading level="2" size="sm" class="text-red-600 dark:text-red-400">{{ __('Delete Account') }}</flux:heading>
        <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-1">{{ __('Once deleted, all your data will be permanently removed.') }}</flux:text> {{ __('') }} <flux:modal.trigger name="confirm-user-deletion">
            <flux:button variant="danger">{{ __('Delete Account') }}</flux:button> {{ __('') }} <form wire:submit="deleteUser" class="space-y-5">
                <div>
                    <flux:heading level="2" size="lg">{{ __('Delete Account?') }}</flux:heading>
                    <flux:text class="text-zinc-600 dark:text-zinc-400 mt-1">
                        {{ __('This is permanent. All your data will be deleted and cannot be recovered. Please enter your password to confirm.') }}
                    </flux:text> {{ __('') }} <flux:label class="sr-only">{{ __('Password') }}</flux:label>
                    <flux:input wire:model="password" type="password" placeholder="{{ __('Your password') }}" autofocus /> {{ __('') }} <div class="flex justify-end gap-3">
                    <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                    </flux:modal.close>
                    <flux:button type="submit" variant="danger">{{ __('Delete Account') }}</flux:button> {{ __('') }} </flux:modal>
    </div>
</div>
