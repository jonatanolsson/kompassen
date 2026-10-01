<x-app-layout>
    <x-slot:title>{{ __('Knowledge editors') }}</x-slot:title>
    <div class="product-page space-y-6">
        <div class="product-page-header">
            <div>
                <flux:heading level="1">{{ __('Knowledge editors') }}</flux:heading>
                <flux:text>{{ __('Appoint team members who may edit team examples and testing methods. WCAG criteria, resources, and shared defaults remain read-only.') }}</flux:text>
            </div>
            <flux:button href="{{ route('testing-methodologies') }}" variant="ghost" icon="arrow-left">{{ __('Back') }}</flux:button>
        </div>
        <div class="product-section space-y-3">
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
