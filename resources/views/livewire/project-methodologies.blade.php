<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <flux:heading level="2">{{ __('Testing Methodology') }}</flux:heading>
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Tools and methods used to test this project') }}</flux:text>
        </div>
        <flux:modal.trigger name="add-methodology-modal">
            <flux:button variant="primary" icon="plus" size="sm">
                {{ __('Add Method') }}
            </flux:button>
        </flux:modal.trigger>
    </div>

    @if ($projectMethodologies->isEmpty())
        <div class="py-4">
            <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No testing methods added yet.') }}</flux:text>
        </div>
    @else
        @php
            $grouped = $projectMethodologies->groupBy(fn($m) => $m->category);
        @endphp

        <div class="space-y-4">
            @foreach ($grouped as $category => $methods)
                <div>
                    <flux:text class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                        {{ match($category) {
                            'screen_reader' => __('Screen Readers'),
                            'browser' => __('Browsers'),
                            'browser_extension' => __('Browser Extensions'),
                            'device' => __('Devices'),
                            'testing_tool' => __('Testing Tools'),
                            default => __(ucfirst(str_replace('_', ' ', $category))),
                        } }}
                    </flux:text>
                    <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($methods as $method)
                            <div class="flex items-start gap-3 py-3 first:pt-0 last:pb-0 group"
                                 x-data="{ editing: false, notes: @js($method->pivot->notes ?? '') }">
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-sm text-zinc-900 dark:text-white">{{ $method->name }}</div>
                                    <div x-show="!editing">
                                        @if ($method->pivot->notes)
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $method->pivot->notes }}</div>
                                        @else
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 italic">{{ __('No version/notes specified') }}</div>
                                        @endif
                                    </div>
                                    <div x-show="editing" class="mt-1">
                                        <flux:input
                                            x-model="notes"
                                            placeholder="{{ __('e.g. version 2024, Windows 11...') }}"
                                        />
                                        <div class="flex gap-2 mt-1">
                                            <flux:button
                                                type="button"
                                                size="xs"
                                                variant="subtle"
                                                @click="$wire.updateNotes('{{ $method->id }}', notes); editing = false"
                                            >{{ __('Save') }}</flux:button>
                                            <flux:button type="button" size="xs" variant="ghost" @click="editing = false">
                                                {{ __('Cancel') }}
                                            </flux:button>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <flux:button
                                        x-on:click="editing = true"
                                        icon="pencil"
                                        size="xs"
                                        variant="subtle"
                                    />
                                    <flux:modal.trigger name="remove-project-methodology-{{ $method->id }}">
                                        <flux:button
                                            type="button"
                                            icon="trash"
                                            size="xs"
                                            variant="subtle"
                                            aria-label="{{ __('Remove :name from this project?', ['name' => $method->name]) }}"
                                        />
                                    </flux:modal.trigger>
                                </div>
                            </div>
                            <flux:modal name="remove-project-methodology-{{ $method->id }}" class="md:w-[28rem]">
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
                    </div>
                </div>
            @endforeach
        </div>
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
            <flux:button variant="ghost" wire:click="$dispatch('close-modal', { name: 'add-methodology-modal' })">{{ __('Cancel') }}</flux:button>
            <flux:button wire:click="addMethodology" variant="primary">{{ __('Add Method') }}</flux:button>
        </div>
    </div>
</flux:modal>
</div>
