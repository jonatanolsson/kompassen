<div class="space-y-4"> {{ __('') }} <flux:heading level="2">{{ __('Testing Methodology') }}</flux:heading>
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">{{ __('Tools and methods used to test this project') }}</flux:text>
        </div>
        <flux:button wire:click="$set('showAddForm', true)" variant="primary" icon="plus" size="sm">
            {{ __('Add Method') }}
        </flux:button>
    </div> {{ __('') }} <flux:heading level="3" class="text-base">{{ __('Add Testing Method') }}</flux:heading>

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
                            </optgroup> {{ __('') }} </flux:field>

                <flux:field>
                <flux:label>{{ __('Version / Device / Notes') }} <flux:badge size="sm" variant="outline">{{ __('Optional') }}</flux:badge></flux:label>
                    <flux:textarea
                        wire:model="notes"
                    placeholder="{{ __('e.g. JAWS 2024, Windows 11 22H2 — or leave blank') }}"
                        rows="2"
                    /> {{ __('') }} <div class="flex gap-2">
                    <flux:button wire:click="addMethodology" variant="primary" size="sm">{{ __('Add') }}</flux:button>
                    <flux:button wire:click="$set('showAddForm', false)" variant="ghost" size="sm">{{ __('Cancel') }}</flux:button> {{ __('') }} </flux:card>
    @endif

    @if ($projectMethodologies-> {{ __('') }} <flux:text class="text-zinc-500 dark:text-zinc-400">{{ __('No testing methods added yet.') }}</flux:text>
        </flux:card>
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
                            default => {{ __('') }} <div class="flex items-start gap-3 bg-zinc-50 dark:bg-zinc-800 rounded-lg px-4 py-3 group"
                                 x-data="{ editing: false, notes: @js($method->pivot->notes ?? '') }"> {{ __('') }} </div>
                                    <div x-show="!editing">
                                        @if ($method->pivot->notes)
                                            <div class="text-sm text-zinc-600 dark:text-zinc-400 mt-0.5">{{ $method->pivot->notes }}</div>
                                        @else
                                        <div class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5 italic">{{ __('No version/notes specified') }}</div> {{ __('') }} <input
                                            type="text"
                                            x-model="notes"
                                            class="w-full text-sm px-2 py-1 border border-zinc-300 dark:border-zinc-600 rounded bg-white dark:bg-zinc-900 text-zinc-900 dark:text-white"
                                            placeholder="{{ __('e.g. version 2024, Windows 11...') }}"
                                        />
                                        <div class="flex gap-2 mt-1">
                                            <button
                                                @click="$wire.updateNotes('{{ $method->id }}', notes); editing = false"
                                                class="text-xs text-blue-600 dark:text-blue-400 hover:underline"
                                            >{{ __('Save') }}</button>
                                            <button @click="editing = false" class="text-xs text-zinc-500 hover:underline">{{ __('Cancel') }}</button> {{ __('') }} </div> {{ __('') }} <flux:button
                                        wire:click="removeMethodology('{{ $method->id }}')"
                                        wire:confirm='{{ __("Remove :name from this project?", ["name" => $method-> {{ __('') }} </div> {{ __('') }} </div>
    @endif
</div>
