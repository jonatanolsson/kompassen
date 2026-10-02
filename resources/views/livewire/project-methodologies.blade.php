<div id="testing-methodology" class="product-section mb-8">
    <div class="flex items-center justify-between mb-4">
        <flux:heading level="2">{{ __('Testing Methodology') }}</flux:heading>
        <flux:modal.trigger name="add-methodology-modal">
            <flux:button type="button" variant="primary" icon="plus" size="sm">
                {{ __('Add Method') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    @if ($projectMethodologies->isEmpty())
        <div class="py-4">
            <flux:text class="text-zinc-600 dark:text-zinc-400">{{ __('No testing methods added yet.') }}</flux:text>
        </div>
    @else
        <div class="max-w-full overflow-x-auto">
            <flux:table class="w-full table-auto! text-sm">
                <flux:table.columns>
                    <flux:table.column class="py-2">{{ __('Method / Tool') }}</flux:table.column>
                    <flux:table.column class="py-2">{{ __('Version / Device / Notes') }}</flux:table.column>
                    <flux:table.column class="whitespace-nowrap py-2 text-right">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>

                @foreach ($projectMethodologies as $method)
                    @php
                        $categoryLabel = match($method->category) {
                            'screen_reader' => __('Screen Readers'),
                            'browser' => __('Browsers'),
                            'browser_extension' => __('Browser Extensions'),
                            'device' => __('Devices'),
                            'testing_tool' => __('Testing Tools'),
                            default => __(ucfirst(str_replace('_', ' ', $method->category))),
                        };
                    @endphp
                    <flux:table.row
                        wire:key="project-methodology-{{ $method->id }}"
                        x-data="{ editing: false, notes: @js($method->pivot->notes ?? '') }"
                    >
                        <flux:table.cell class="whitespace-normal break-words py-2 font-medium">
                            <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                <span>{{ $method->name }}</span>
                                <span class="text-xs font-normal text-zinc-500 dark:text-zinc-400">{{ $categoryLabel }}</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="max-w-[16rem] whitespace-nowrap py-2 text-sm text-zinc-600 dark:text-zinc-400">
                            <div x-show="!editing">
                                @if ($method->pivot->notes)
                                    <span class="block max-w-[16rem] truncate">{{ $method->pivot->notes }}</span>
                                @else
                                    <span class="text-xs italic text-zinc-400 dark:text-zinc-500">{{ __('No version/notes specified') }}</span>
                                @endif
                            </div>
                            <div x-show="editing" class="max-w-xs space-y-2">
                                <flux:input
                                    x-model="notes"
                                    placeholder="{{ __('e.g. version 2024, Windows 11...') }}"
                                />
                                <flux:error name="notes" />
                                <div class="flex gap-2">
                                    <flux:button
                                        type="button"
                                        size="xs"
                                        variant="subtle"
                                        @click="$wire.updateNotes('{{ $method->id }}', notes).then((updated) => { if (updated) editing = false })"
                                    >{{ __('Save') }}</flux:button>
                                    <flux:button type="button" size="xs" variant="ghost" @click="$wire.cancelNotesEdit(); editing = false">
                                        {{ __('Cancel') }}
                                    </flux:button>
                                </div>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell class="whitespace-nowrap py-2 text-right">
                            <div class="inline-flex items-center justify-end gap-1">
                                <flux:button
                                    href="{{ route('project-methodologies.show', [$project, $method]) }}"
                                    size="sm"
                                    variant="ghost"
                                >
                                    {{ __('View details') }}
                                </flux:button>
                                <flux:button
                                    type="button"
                                    x-on:click="editing = true"
                                    icon="pencil"
                                    size="sm"
                                    variant="subtle"
                                    aria-label="{{ __('Edit notes for :name', ['name' => $method->name]) }}"
                                />
                                <flux:modal.trigger name="remove-project-methodology-{{ $method->id }}">
                                    <flux:button
                                        type="button"
                                        icon="trash"
                                        size="sm"
                                        variant="subtle"
                                        aria-label="{{ __('Remove :name from this project?', ['name' => $method->name]) }}"
                                    />
                                </flux:modal.trigger>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                @endforeach
            </flux:table>
        </div>

        @foreach ($projectMethodologies as $method)
            <flux:modal name="remove-project-methodology-{{ $method->id }}" class="md:w-[28rem]" wire:key="remove-methodology-modal-{{ $method->id }}">
                <div class="space-y-6">
                    <div>
                        <flux:heading level="2">{{ __('Remove :name from this project?', ['name' => $method->name]) }}</flux:heading>
                        <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">{{ __('This only removes the method from this project.') }}</flux:text>
                    </div>
                    <div class="flex justify-end gap-2">
                        <flux:modal.close>
                            <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                        </flux:modal.close>
                        <flux:button type="button" variant="danger" wire:click="removeMethodology('{{ $method->id }}')">
                            {{ __('Remove') }}
                        </flux:button>
                    </div>
                </div>
            </flux:modal>
        @endforeach
    @endif

    <!-- Add Methodology Modal -->
    <flux:modal name="add-methodology-modal" class="md:w-96">
    <div class="space-y-6">
        <flux:heading level="2">{{ __('Add Testing Method') }}</flux:heading>

        <flux:separator />

        <div class="space-y-4">
            <flux:field>
                <flux:label>{{ __('Method / Tool') }}</flux:label>
                <flux:select wire:model="selectedMethodologyId">
                    <option value="">{{ __('Select a method...') }}</option>
                    @foreach ($allMethodologies as $category => $methods)
                        <optgroup label="{{ match($category) {
                            'screen_reader' => __('Screen Readers'),
                            'browser' => __('Browsers'),
                            'browser_extension' => __('Browser Extensions'),
                            'device' => __('Devices'),
                            'testing_tool' => __('Testing Tools'),
                            default => __(ucfirst(str_replace('_', ' ', $category))),
                        } }}">
                            @foreach ($methods as $method)
                                @if (!in_array($method->id, $projectMethodologyIds))
                                    <option value="{{ $method->id }}">{{ $method->name }}</option>
                                @endif
                            @endforeach
                        </optgroup>
                    @endforeach
                </flux:select>
                <flux:error name="selectedMethodologyId" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Version / Device / Notes') }} <flux:badge size="sm" variant="outline">{{ __('Optional') }}</flux:badge></flux:label>
                <flux:textarea
                    wire:model="notes"
                    placeholder="{{ __('e.g. JAWS 2024, Windows 11 22H2 — or leave blank') }}"
                    rows="3"
                />
                <flux:error name="notes" />
            </flux:field>
        </div>

        <flux:separator />

        <div class="flex gap-2 justify-end">
            <flux:modal.close>
                <flux:button type="button" variant="ghost" wire:click="cancelAddMethodology">{{ __('Cancel') }}</flux:button>
            </flux:modal.close>
            <flux:button type="button" wire:click="addMethodology" variant="primary">{{ __('Add Method') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
