<x-app-layout>
    <x-slot:title>{{ __('Members') }} - {{ $project->name }}</x-slot:title>

    <flux:header>
        <flux:heading>{{ __('Members') }}</flux:heading>
        <flux:spacer />
        <flux:button href="{{ route('accessibility-projects.show', $project) }}" variant="ghost" icon="arrow-left">
            {{ __('Back') }}
        </flux:button>
    </flux:header>

    <div class="max-w-4xl space-y-6">
        <div>
            <flux:heading level="2">{{ $project->name }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('People with access to this project') }}
            </flux:text>
        </div>

        @php($currentMember = $project->members->firstWhere('user_id', auth()->id()))

        @if ($currentMember?->role === 'owner')
            <flux:card>
                <div class="space-y-6">
                    <div>
                        <flux:heading level="3">{{ __('Add Member') }}</flux:heading>
                        <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                            {{ __('Add people from your team to this project.') }}
                        </flux:text>
                    </div>

                    <flux:separator />

                    <form action="{{ route('project-members.store', $project) }}" method="POST" class="space-y-4">
                        @csrf
                        <flux:field>
                            <flux:label>{{ __('Email address') }}</flux:label>
                            <flux:input type="email" name="email" value="{{ old('email') }}" required placeholder="{{ __('name@example.com') }}" />
                            <flux:description>{{ __('Only users from your team can be added.') }}</flux:description>
                            <flux:error name="email" />
                        </flux:field>

                        <flux:field>
                            <flux:label>{{ __('Role') }}</flux:label>
                            <flux:select name="role">
                                <option value="editor" @selected(old('role', 'editor') === 'editor')>{{ __('Editor') }}</option>
                                <option value="viewer" @selected(old('role') === 'viewer')>{{ __('Viewer') }}</option>
                                <option value="owner" @selected(old('role') === 'owner')>{{ __('Owner') }}</option>
                            </flux:select>
                            <flux:error name="role" />
                        </flux:field>

                        <div class="flex justify-end">
                            <flux:button type="submit" variant="primary" icon="user-plus">{{ __('Add Member') }}</flux:button>
                        </div>
                    </form>
                </div>
            </flux:card>
        @endif

        <flux:card>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @foreach ($project->members as $member)
                    <div class="flex flex-col gap-3 py-4 first:pt-0 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$member->user->name" size="sm" />
                            <div>
                                <flux:text class="font-medium">{{ $member->user->name }}</flux:text>
                                <flux:text class="text-sm text-zinc-500">{{ $member->user->email }}</flux:text>
                            </div>
                        </div>

                        @if ($currentMember?->role === 'owner')
                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('project-members.update', [$project, $member]) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <flux:select name="role" size="sm" aria-label="{{ __('Role for :name', ['name' => $member->user->name]) }}">
                                        <option value="owner" @selected($member->role === 'owner')>{{ __('Owner') }}</option>
                                        <option value="editor" @selected($member->role === 'editor')>{{ __('Editor') }}</option>
                                        <option value="viewer" @selected($member->role === 'viewer')>{{ __('Viewer') }}</option>
                                    </flux:select>
                                    <flux:button type="submit" variant="ghost" size="sm" icon="check" aria-label="{{ __('Save role') }}" />
                                </form>
                                <form action="{{ route('project-members.destroy', [$project, $member]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <flux:button type="submit" variant="ghost" size="sm" icon="trash" class="text-red-600" aria-label="{{ __('Remove member') }}" onclick="return confirm('{{ __('Remove :name from this project?', ['name' => $member->user->name]) }}')" />
                                </form>
                            </div>
                        @else
                            <flux:badge :color="$member->role === 'owner' ? 'amber' : 'zinc'">
                                {{ __(Str::title($member->role)) }}
                            </flux:badge>
                        @endif
                    </div>
                @endforeach
            </div>
        </flux:card>
    </div>
</x-app-layout>
