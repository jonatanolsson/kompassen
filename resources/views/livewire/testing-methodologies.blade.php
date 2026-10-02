<div class="product-page space-y-8">
    <div class="product-page-header">
        <div>
            <flux:heading level="1">{{ __('Testing Methodologies') }}</flux:heading>
            <flux:text class="mt-1 text-zinc-600 dark:text-zinc-400">
                {{ __('Shared methods are read-only. Team editors can manage methods for their team.') }}
            </flux:text>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            @if (auth()->user()->isTeamOwner())
                <flux:button href="{{ route('team.knowledge-editors.index') }}" variant="outline">
                    {{ __('Knowledge editors') }}
                </flux:button>
            @endif
            @can('create', \App\Models\TestingMethodology::class)
                <flux:modal.trigger name="testing-methodology-form-modal">
                    <flux:button type="button" variant="primary" icon="plus" wire:click="openCreateForm">
                        {{ __('Add Testing Methodology') }}
                    </flux:button>
                </flux:modal.trigger>
            @endcan
        </div>
    </div>

    @forelse ($this->methodologies as $category => $methodologies)
        <section class="product-section space-y-3">
            <flux:heading level="2" size="lg">
                {{ $this->categoryOptions()[$category] ?? Str::headline($category) }}
            </flux:heading>

            <div class="hidden overflow-x-auto lg:block">
                <flux:table class="w-full table-auto!">
                    <flux:table.columns>
                        <flux:table.column>{{ __('Name') }}</flux:table.column>
                        <flux:table.column>{{ __('Description') }}</flux:table.column>
                        <flux:table.column class="whitespace-nowrap">{{ __('Type') }}</flux:table.column>
                        <flux:table.column class="whitespace-nowrap">{{ __('Projects') }}</flux:table.column>
                        <flux:table.column class="whitespace-nowrap text-right">{{ __('Actions') }}</flux:table.column>
                    </flux:table.columns>

                    @foreach ($methodologies as $methodology)
                        <flux:table.row wire:key="methodology-{{ $methodology->id }}">
                            <flux:table.cell class="whitespace-normal break-words font-medium">
                                {{ $methodology->name }}
                            </flux:table.cell>
                            <flux:table.cell class="max-w-2xl whitespace-normal break-words text-sm text-zinc-600 dark:text-zinc-400">
                                {{ $methodology->description ?: __('No description') }}
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">
                                <flux:badge size="sm" color="{{ $methodology->is_custom ? 'blue' : 'zinc' }}">
                                    {{ $methodology->is_custom ? __('Custom') : __('Default') }}
                                </flux:badge>
                            </flux:table.cell>
                            <flux:table.cell class="whitespace-nowrap">{{ $methodology->projects_count }}</flux:table.cell>
                            <flux:table.cell class="text-right">
                                <div class="inline-flex items-center justify-end gap-1">
                                    @can('update', $methodology)
                                        <flux:modal.trigger name="testing-methodology-form-modal">
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="subtle"
                                                icon="pencil"
                                                aria-label="{{ __('Edit :name', ['name' => $methodology->name]) }}"
                                                wire:click="edit('{{ $methodology->id }}')"
                                            />
                                        </flux:modal.trigger>
                                    @endcan
                                    @can('delete', $methodology)
                                        @if ($methodology->projects_count > 0)
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="subtle"
                                                icon="trash"
                                                aria-label="{{ __('Delete') }}"
                                                title="{{ __('Cannot delete a methodology used by projects.') }}"
                                                disabled
                                            />
                                        @else
                                            <flux:modal.trigger name="delete-testing-methodology-{{ $methodology->id }}">
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="subtle"
                                                    icon="trash"
                                                    aria-label="{{ __('Delete') }}"
                                                />
                                            </flux:modal.trigger>
                                        @endif
                                    @endcan
                                    @cannot('update', $methodology)
                                        @cannot('delete', $methodology)
                                            <flux:badge size="sm" variant="outline">{{ __('Read-only') }}</flux:badge>
                                        @endcannot
                                    @endcannot
                                </div>
                            </flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table>
            </div>

            <div class="space-y-3 lg:hidden">
                @foreach ($methodologies as $methodology)
                    <article class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700" wire:key="methodology-card-{{ $methodology->id }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <flux:heading level="3" size="sm" class="break-words">{{ $methodology->name }}</flux:heading>
                                <flux:text class="mt-1 whitespace-normal break-words text-sm text-zinc-600 dark:text-zinc-400">
                                    {{ $methodology->description ?: __('No description') }}
                                </flux:text>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <flux:badge size="sm" color="{{ $methodology->is_custom ? 'blue' : 'zinc' }}">
                                        {{ $methodology->is_custom ? __('Custom') : __('Default') }}
                                    </flux:badge>
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ __('Projects') }}: {{ $methodology->projects_count }}
                                    </flux:text>
                                </div>
                            </div>
                            <div class="flex shrink-0 flex-wrap items-center gap-1">
                                @can('update', $methodology)
                                    <flux:modal.trigger name="testing-methodology-form-modal">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="subtle"
                                            icon="pencil"
                                            aria-label="{{ __('Edit :name', ['name' => $methodology->name]) }}"
                                            wire:click="edit('{{ $methodology->id }}')"
                                        />
                                    </flux:modal.trigger>
                                @endcan
                                @can('delete', $methodology)
                                    @if ($methodology->projects_count > 0)
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="subtle"
                                            icon="trash"
                                            aria-label="{{ __('Delete') }}"
                                            title="{{ __('Cannot delete a methodology used by projects.') }}"
                                            disabled
                                        />
                                    @else
                                        <flux:modal.trigger name="delete-testing-methodology-{{ $methodology->id }}">
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="subtle"
                                                icon="trash"
                                                aria-label="{{ __('Delete') }}"
                                            />
                                        </flux:modal.trigger>
                                    @endif
                                @endcan
                                @cannot('update', $methodology)
                                    @cannot('delete', $methodology)
                                        <flux:badge size="sm" variant="outline">{{ __('Read-only') }}</flux:badge>
                                    @endcannot
                                @endcannot
                            </div>
                        </div>
                        @if ($methodology->projects_count > 0)
                            <flux:text class="mt-3 text-xs text-zinc-500 dark:text-zinc-400">
                                {{ __('Cannot delete a methodology used by projects.') }}
                            </flux:text>
                        @endif
                    </article>
                @endforeach
            </div>

            @foreach ($methodologies as $methodology)
                @can('delete', $methodology)
                    <flux:modal
                        name="delete-testing-methodology-{{ $methodology->id }}"
                        class="md:w-[28rem]"
                        wire:key="delete-testing-methodology-modal-{{ $methodology->id }}"
                    >
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
