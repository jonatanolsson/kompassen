<div class="product-page space-y-8">
    <div class="product-page-header">
        <div>
            <flux:heading level="1">{{ __('Testing Methodologies') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('Shared methods are read-only. Team editors can manage methods for their team.') }}
            </flux:text>
        </div>
        @if (auth()->user()->isTeamOwner())
            <flux:button href="{{ route('team.knowledge-editors.index') }}" variant="outline">{{ __('Knowledge editors') }}</flux:button>
        @endif
        @can('create', \App\Models\TestingMethodology::class)
        <flux:modal.trigger name="testing-methodology-form-modal">
            <flux:button variant="primary" icon="plus" wire:click="openCreateForm">
                {{ __('Add Testing Methodology') }}
            </flux:button>
        </flux:modal.trigger>
        @endcan
    </div>

    @forelse ($this->methodologies as $category => $methodologies)
        <section class="product-section space-y-3">
            <flux:heading level="2" size="lg">
                {{ $this->categoryOptions()[$category] ?? Str::headline($category) }}
            </flux:heading>

            <div class="overflow-x-auto">
            <flux:table class="w-full min-w-[680px]">
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
                            @can('update', $methodology)
                            <flux:modal.trigger name="testing-methodology-form-modal">
                                <flux:button size="sm" variant="subtle" icon="pencil" wire:click="edit('{{ $methodology->id }}')">
                                    {{ __('Edit') }}
                                </flux:button>
                            </flux:modal.trigger>
                            @endcan
                            @can('delete', $methodology)
                            <flux:modal.trigger name="delete-testing-methodology-{{ $methodology->id }}">
                                <flux:button type="button" size="sm" variant="subtle" icon="trash">
                                    {{ __('Delete') }}
                                </flux:button>
                            </flux:modal.trigger>
                            @endcan
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table>
            </div>

            @foreach ($methodologies as $methodology)
                @can('delete', $methodology)
                <flux:modal name="delete-testing-methodology-{{ $methodology->id }}" class="md:w-[28rem]">
                    <div class="space-y-6">
                        <div>
                            <flux:heading level="2">{{ __('Delete this testing methodology?') }}</flux:heading>
                            <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This action cannot be undone.') }}</flux:text>
                        </div>
                        <div class="flex justify-end gap-2">
                            <flux:modal.close>
                                <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                            </flux:modal.close>
                            <flux:button type="button" variant="danger" wire:click="delete('{{ $methodology->id }}')">
                                {{ __('Delete') }}
                            </flux:button>
                        </div>
                    </div>
                </flux:modal>
                @endcan
            @endforeach
        </section>
    @empty
        <flux:callout icon="beaker" color="zinc">
            <flux:callout.text>{{ __('No testing methodologies available.') }}</flux:callout.text>
        </flux:callout>
    @endforelse

    @can('create', \App\Models\TestingMethodology::class)
    <flux:modal name="testing-methodology-form-modal" class="w-full max-w-2xl">
        <form wire:submit="save" class="space-y-6">
            <div>
                <flux:heading size="lg">
                    {{ $editingMethodologyId ? __('Edit Testing Methodology') : __('Add Testing Methodology') }}
                </flux:heading>
                <flux:text size="sm" class="mt-1 text-zinc-600 dark:text-zinc-400">
                    {{ __('This method will be available to projects in your team.') }}
                </flux:text>
            </div>

            <flux:separator />

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <flux:field>
                    <flux:label>{{ __('Name') }}</flux:label>
                    <flux:input wire:model="name" autofocus />
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

            <flux:separator />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost" wire:click="cancel">
                        {{ __('Cancel') }}
                    </flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary">
                    {{ $editingMethodologyId ? __('Update Methodology') : __('Save Methodology') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
    @endcan
</div>
