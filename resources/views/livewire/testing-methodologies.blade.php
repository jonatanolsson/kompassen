<div class="space-y-8">
    <div class="flex items-start justify-between gap-4">
        <div>
            <flux:heading level="1">{{ __('Testing Methodologies') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('Manage the methods available when documenting project testing.') }}
            </flux:text>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="openCreateForm">
            {{ __('Add Testing Methodology') }}
        </flux:button>
    </div>

    @if ($showForm)
        <flux:card>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <flux:heading level="2">
                        {{ $editingMethodologyId ? __('Edit Testing Methodology') : __('Add Testing Methodology') }}
                    </flux:heading>
                    <flux:text class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('This method will be available to all projects.') }}
                    </flux:text>
                </div>
                <flux:button variant="subtle" icon="x-mark" wire:click="cancel" />
            </div>

            <flux:separator class="my-6" />

            <form wire:submit="save" class="space-y-5">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    <flux:field>
                        <flux:label>{{ __('Name') }}</flux:label>
                        <flux:input wire:model="name" />
                        <flux:error name="name" />
                    </flux:field>

                    <flux:field>
                        <flux:label>{{ __('Category') }}</flux:label>
                        <flux:select wire:model="category">
                            @foreach ($this->categoryOptions() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="category" />
                    </flux:field>
                </div>

                <flux:field>
                    <flux:label>{{ __('Description') }}</flux:label>
                    <flux:textarea wire:model="description" rows="3" />
                    <flux:error name="description" />
                </flux:field>

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cancel">
                        {{ __('Cancel') }}
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        {{ $editingMethodologyId ? __('Update Methodology') : __('Save Methodology') }}
                    </flux:button>
                </div>
            </form>
        </flux:card>
    @endif

    @forelse ($this->methodologies as $category => $methodologies)
        <section class="space-y-3">
            <flux:heading level="2" size="lg">
                {{ $this->categoryOptions()[$category] ?? Str::headline($category) }}
            </flux:heading>

            <flux:table>
                <flux:table.columns>
                    <flux:table.cell>{{ __('Name') }}</flux:table.cell>
                    <flux:table.cell>{{ __('Description') }}</flux:table.cell>
                    <flux:table.cell>{{ __('Type') }}</flux:table.cell>
                    <flux:table.cell>{{ __('Projects') }}</flux:table.cell>
                    <flux:table.cell>{{ __('Actions') }}</flux:table.cell>
                </flux:table.columns>

                @foreach ($methodologies as $methodology)
                    <flux:table.row wire:key="methodology-{{ $methodology->id }}">
                        <flux:table.cell class="font-medium">{{ $methodology->name }}</flux:table.cell>
                        <flux:table.cell class="max-w-md text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $methodology->description ?: __('No description') }}
                        </flux:table.cell>
                        <flux:table.cell>
                            <flux:badge size="sm" color="{{ $methodology->is_custom ? 'blue' : 'zinc' }}">
                                {{ $methodology->is_custom ? __('Custom') : __('Default') }}
                            </flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>{{ $methodology->projects_count }}</flux:table.cell>
                        <flux:table.cell class="flex gap-1">
                            <flux:button size="sm" variant="subtle" icon="pencil" wire:click="edit('{{ $methodology->id }}')">
                                {{ __('Edit') }}
                            </flux:button>
                            <flux:button
                                size="sm"
                                variant="subtle"
                                icon="trash"
                                wire:click="delete('{{ $methodology->id }}')"
                                wire:confirm="{{ __('Delete this testing methodology?') }}"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table>
        </section>
    @empty
        <flux:callout icon="beaker" color="zinc">
            <flux:callout.text>{{ __('No testing methodologies available.') }}</flux:callout.text>
        </flux:callout>
    @endforelse
</div>
