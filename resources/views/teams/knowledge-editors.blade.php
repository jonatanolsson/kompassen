<x-app-layout>
    <x-slot:title>{{ __('Knowledge editors') }}</x-slot:title>
    <div class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 sm:py-8 lg:px-8 space-y-6">
        <div class="flex flex-col gap-4 border-b border-zinc-200/80 pb-6 sm:flex-row sm:items-start sm:justify-between dark:border-zinc-800">
            <div>
                <flux:heading level="1">{{ __('Knowledge editors') }}</flux:heading>
                <flux:text>{{ __('Appoint team members who may edit team examples and testing methods. WCAG criteria, resources, and shared defaults remain read-only.') }}</flux:text>
            </div>
            <flux:button href="{{ route('testing-methodologies') }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
        </div>
        <div class="rounded-2xl border border-zinc-200/80 bg-white/80 p-5 shadow-sm shadow-zinc-950/5 sm:p-6 dark:border-zinc-800 dark:bg-zinc-900/70 dark:shadow-black/10 space-y-3">
            @foreach ($users as $user)
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-zinc-200 py-3 dark:border-zinc-700">
                    <div>
                        <flux:heading level="2" size="sm">{{ $user->name }}</flux:heading>
                        <flux:text>{{ $user->email }}</flux:text>
                    </div>
                    @if ($user->is(auth()->user()))
                        <flux:badge>{{ __('Team owner') }}</flux:badge>
                    @else
                        <form action="{{ route('team.knowledge-editors.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_knowledge_editor" value="{{ $user->is_knowledge_editor ? '0' : '1' }}">
                            <flux:button type="submit" variant="{{ $user->is_knowledge_editor ? 'danger' : 'primary' }}">
                                {{ $user->is_knowledge_editor ? __('Remove editor') : __('Appoint editor') }}
                            </flux:button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
