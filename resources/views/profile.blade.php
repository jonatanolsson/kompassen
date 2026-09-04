<x-app-layout>
    <div class="max-w-2xl mx-auto px-4 py-10">
        <flux:heading level="1" class="mb-1">{{ __('Profile') }}</flux:heading>
        <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('Manage your account information and security settings.') }}</flux:text>

        <flux:separator class="my-8" />

        <livewire:profile.update-profile-information-form />

        <flux:separator class="my-8" />

        <livewire:profile.update-password-form />

        <flux:separator class="my-8" />

        <livewire:profile.delete-user-form />
    </div>
</x-app-layout>
