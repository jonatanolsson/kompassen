<x-app-layout>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="1">{{ __('Dashboard') }}</flux:heading>
            <flux:subheading class="mt-2">{{ __('Welcome back, :name!', ['name' => auth()->user()->name]) }}</flux:subheading>
        </div>
    </div>

    <flux:separator />

    <flux:card class="p-8 text-center">
        <div class="space-y-4">
            <flux:icon icon="home" variant="solid" class="h-16 w-16 mx-auto text-zinc-400 dark:text-zinc-600" />
            <flux:heading level="2">{{ __('Hello World!') }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400">
                {{ __('Your dashboard is ready. Start building your application here.') }}
            </flux:text>
        </div>
    </flux:card>
</div>
</x-app-layout>
