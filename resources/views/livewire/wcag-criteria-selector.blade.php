<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Computed;
use App\Models\WcagSuccessCriterion;

new class extends Component {
    public bool $open = false;

    public array $selectedCriteria = [];

    public ?string $selectedDetailId = null;

    public string $searchQuery = '';

    public function mount(?array $initialSelectedCriteria = null): void
    {
        if ($initialSelectedCriteria) {
            $this->selectedCriteria = $initialSelectedCriteria;
        }
    }

    public function toggleCriterion(string $id): void
    {
        if (in_array($id, $this->selectedCriteria)) {
            $this->selectedCriteria = array_diff($this->selectedCriteria, [$id]);
        } else {
            $this->selectedCriteria[] = $id;
        }
    }

    public function getSelectedCriteria()
    {
        return WcagSuccessCriterion::whereIn('id', $this->selectedCriteria)->get();
    }

    public function getDetailCriterion()
    {
        if (! $this->selectedDetailId) {
            return null;
        }

        return WcagSuccessCriterion::with('relatedResources')->find($this->selectedDetailId);
    }

    #[Computed]
    public function wcagCriteria()
    {
        if (empty($this->searchQuery)) {
            return WcagSuccessCriterion::orderBy('number')->get();
        }

        $search = '%'.trim($this->searchQuery).'%';

        return WcagSuccessCriterion::where('number', 'like', $search)
            ->orWhere('name_sv', 'like', $search)
            ->orWhere('name_en', 'like', $search)
            ->orWhere('description_sv', 'like', $search)
            ->orWhere('description_en', 'like', $search)
            ->orderBy('number')
            ->get();
    }
}; ?>

<div>
    <div class="space-y-4">
        <!-- Hidden input to store selected criteria for form submission -->
        @foreach ($selectedCriteria as $id)
            <input type="hidden" name="wcag_criteria[]" value="{{ $id }}" />
        @endforeach

        <!-- Button to open modal -->
        <flux:button 
            wire:click="$set('open', true)" 
            variant="ghost"
        >
            {{ __('Select WCAG Criteria') }}
        </flux:button>

        @if ($selectedCriteria)
            <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg">
                <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                    {{ count($selectedCriteria) }} {{ __('criteria selected') }}
                </flux:text>
                <div class="space-y-1">
                    @foreach ($this->getSelectedCriteria() as $criterion)
                        <div class="flex items-center justify-between bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                            <flux:text class="text-sm">
                                {{ $criterion->number }} — {{ $criterion->name_sv ?? $criterion->name_en }}
                            </flux:text>
                            <button 
                                type="button"
                                wire:click="toggleCriterion('{{ $criterion->id }}')"
                                class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                            >
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Modal -->
        <div 
            x-data="{ open: @js($open) }" 
            x-show="open"
            x-on:open.window="open = true"
            x-on:close.window="open = false"
            class="fixed inset-0 z-50 overflow-y-auto"
            wire:model="open"
        >
        <!-- Backdrop -->
        <div 
            x-show="open"
            x-transition
            class="fixed inset-0 bg-black/50"
            @click="open = false"
        ></div>

        <!-- Modal Content -->
        <div 
            x-show="open"
            x-transition
            class="relative min-h-screen flex items-center justify-center p-4"
        >
            <div class="relative bg-white dark:bg-zinc-900 rounded-lg shadow-lg max-w-5xl w-full max-h-[90vh] overflow-y-auto border border-zinc-200 dark:border-zinc-700">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-6 flex items-center justify-between">
                    <flux:heading level="2">{{ __('Select WCAG Success Criteria') }}</flux:heading>
                    <button 
                        type="button"
                        @click="open = false"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Search Bar -->
                <div class="sticky top-16 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4">
                    <flux:input 
                        wire:model.live="searchQuery"
                        type="text"
                        placeholder="{{ __('Search criteria') }}..."
                        class="w-full"
                    />
                    @if (!empty($searchQuery))
                        <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            {{ count($this->wcagCriteria) }} {{ __('criteria found') }}
                        </flux:text>
                    @endif
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Criteria List (Left) -->
                        <div class="lg:col-span-1">
                            <flux:text class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-4">
                                {{ __('Criteria') }}
                            </flux:text>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @forelse ($this->wcagCriteria->groupBy(fn ($c) => explode('.', $c->number)[0]) as $principle => $group)
                                    <div class="mb-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                                            {{ __('Principle') }} {{ $principle }}
                                        </p>
                                        <div class="space-y-1 ml-2">
                                            @foreach ($group as $criterion)
                                                <button 
                                                    type="button"
                                                    wire:click="$set('selectedDetailId', '{{ $criterion->id }}')"
                                                    class="w-full text-left p-2 rounded-lg transition text-xs {{ $selectedDetailId === $criterion->id ? 'bg-blue-100 dark:bg-blue-900 border-l-2 border-blue-500' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                                                >
                                                    <span class="font-semibold">{{ $criterion->number }}</span>
                                                    <span class="text-zinc-600 dark:text-zinc-400">({{ $criterion->level }})</span>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @empty
                                    <flux:text class="text-xs text-zinc-500 dark:text-zinc-400 text-center py-4">
                                        {{ __('No criteria found') }}
                                    </flux:text>
                                @endforelse
                            </div>
                        </div>

                        <!-- Detail Panel (Right) -->
                        <div class="lg:col-span-2">
                            @if ($this->getDetailCriterion())
                                @php 
                                    $detail = $this->getDetailCriterion();
                                    $failures = $detail->relatedResources->where('type', 'wai_failure');
                                    $links = $detail->relatedResources->where('type', 'resource_link');
                                @endphp
                                <div class="space-y-4">
                                    <!-- Criterion Detail -->
                                    <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg border border-zinc-200 dark:border-zinc-700">
                                        <div class="flex items-start justify-between mb-3">
                                            <div>
                                                <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Criterion') }}</flux:text>
                                                <flux:heading level="3">{{ $detail->number }}</flux:heading>
                                            </div>
                                            <flux:badge variant="primary">WCAG {{ $detail->level }}</flux:badge>
                                        </div>
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-2">
                                            {{ $detail->name_sv ?? $detail->name_en }}
                                        </p>
                                        <p class="text-sm text-zinc-700 dark:text-zinc-300 leading-relaxed">
                                            {{ $detail->description_sv ?? $detail->description_en }}
                                        </p>
                                        <div class="mt-4">
                                            <button 
                                                type="button"
                                                wire:click="toggleCriterion('{{ $detail->id }}')"
                                                class="w-full {{ in_array($detail->id, $selectedCriteria) ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-100 hover:bg-red-200 dark:hover:bg-red-800' : 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-800' }} px-4 py-2 rounded-lg font-semibold text-sm transition"
                                            >
                                                {{ in_array($detail->id, $selectedCriteria) ? __('Remove from Selection') : __('Add to Selection') }}
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Failures -->
                                    @if ($failures->count() > 0)
                                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                            <flux:text class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3">
                                                {{ __('Failures of Success Criterion') }}
                                            </flux:text>
                                            <div class="space-y-2">
                                                @foreach ($failures as $failure)
                                                    <div class="bg-red-50 dark:bg-red-900/20 p-3 rounded-lg border border-red-200 dark:border-red-800">
                                                        @if ($failure->code)
                                                            <flux:badge class="mb-2" variant="danger">{{ $failure->code }}</flux:badge>
                                                        @endif
                                                        <p class="text-xs font-semibold text-red-900 dark:text-red-100">
                                                            {{ $failure->title_sv ?? $failure->title_en }}
                                                        </p>
                                                        @if ($failure->description_sv || $failure->description_en)
                                                            <p class="text-xs text-red-800 dark:text-red-200 mt-1">
                                                                {{ $failure->description_sv ?? $failure->description_en }}
                                                            </p>
                                                        @endif
                                                        @if ($failure->url)
                                                            <a href="{{ $failure->url }}" target="_blank" rel="noopener noreferrer" class="text-xs text-blue-600 dark:text-blue-400 hover:underline mt-2 inline-block">
                                                                {{ __('View on W3C') }} →
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Related Resources -->
                                    @if ($links->count() > 0)
                                        <div class="border-t border-zinc-200 dark:border-zinc-700 pt-4">
                                            <flux:text class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mb-3">
                                                {{ __('Related Resources') }}
                                            </flux:text>
                                            <div class="space-y-2">
                                                @foreach ($links as $link)
                                                    <div class="bg-blue-50 dark:bg-blue-900/20 p-3 rounded-lg border border-blue-200 dark:border-blue-800">
                                                        <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-blue-700 dark:text-blue-300 hover:underline">
                                                            {{ $link->title_sv ?? $link->title_en }} →
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-lg text-center border border-zinc-200 dark:border-zinc-700">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        {{ __('Select a criterion to view details') }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 p-6 flex justify-end gap-3">
                    <button 
                        type="button"
                        @click="open = false"
                        class="px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition"
                    >
                        {{ __('Done') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


<div>
    <!-- Hidden input to store selected criteria for form submission -->
    @foreach ($selectedCriteria as $id)
        <input type="hidden" name="wcag_criteria[]" value="{{ $id }}" />
    @endforeach

    <!-- Button to open modal -->
    <flux:button 
        wire:click="$set('open', true)" 
        variant="ghost"
        class="mb-4"
    >
        {{ __('Select WCAG Criteria') }}
    </flux:button>

    @if ($selectedCriteria)
        <div class="bg-zinc-50 dark:bg-zinc-800 p-3 rounded-lg mb-4">
            <flux:text class="text-sm text-zinc-600 dark:text-zinc-400 mb-2">
                Selected {{ count($selectedCriteria) }} criteria
            </flux:text>
            <div class="space-y-1">
                @foreach ($this->getSelectedCriteria() as $criterion)
                    <div class="flex items-center justify-between bg-white dark:bg-zinc-900 p-2 rounded border border-zinc-200 dark:border-zinc-700">
                        <flux:text class="text-sm">
                            {{ $criterion->number }} — {{ $criterion->name_en }}
                        </flux:text>
                        <button 
                            type="button"
                            wire:click="toggleCriterion('{{ $criterion->id }}')"
                            class="text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200"
                        >
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Modal -->
    <div 
        x-data="{ open: @js($open) }" 
        x-show="open"
        x-on:open.window="open = true"
        x-on:close.window="open = false"
        class="fixed inset-0 z-50 overflow-y-auto"
        wire:model="open"
    >
        <!-- Backdrop -->
        <div 
            x-show="open"
            x-transition
            class="fixed inset-0 bg-black/50"
            @click="open = false"
        ></div>

        <!-- Modal Content -->
        <div 
            x-show="open"
            x-transition
            class="relative min-h-screen flex items-center justify-center p-4"
        >
            <div class="relative bg-white dark:bg-zinc-900 rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-zinc-200 dark:border-zinc-700">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-6 flex items-center justify-between">
                    <flux:heading level="2">{{ __('Select WCAG Success Criteria') }}</flux:heading>
                    <button 
                        type="button"
                        @click="open = false"
                        class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Criteria List -->
                        <div>
                            <flux:text class="text-sm font-semibold text-zinc-600 dark:text-zinc-400 mb-4">
                                {{ __('Criteria') }}
                            </flux:text>
                            <div class="space-y-2 max-h-96 overflow-y-auto">
                                @foreach ($this->wcagCriteria->groupBy(fn ($c) => explode('.', $c->number)[0]) as $principle => $group)
                                    <div class="mb-4">
                                        <p class="text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400 mb-2">
                                        {{ __('Principle') }} {{ $principle }}
                                        </p>
                                        <div class="space-y-1 ml-2">
                                            @foreach ($group as $criterion)
                                                <button 
                                                    type="button"
                                                    wire:click="toggleCriterion('{{ $criterion->id }}')"
                                                    wire:click.self="$set('selectedDetailId', '{{ $criterion->id }}')"
                                                    class="w-full text-left p-2 rounded-lg transition {{ in_array($criterion->id, $selectedCriteria) ? 'bg-blue-100 dark:bg-blue-900 border-l-2 border-blue-500' : 'hover:bg-zinc-100 dark:hover:bg-zinc-800' }}"
                                                >
                                                    <flux:text class="text-sm">
                                                        <span class="font-semibold">{{ $criterion->number }}</span>
                                                    <span class="text-zinc-600 dark:text-zinc-400">({{ __('Level') }} {{ $criterion->level }})</span>
                                                    </flux:text>
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Detail Panel -->
                        <div>
                            @if ($this->getDetailCriterion())
                                @php $detail = $this->getDetailCriterion(); @endphp
                                <div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg">
                                    <div class="space-y-4">
                                        <div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Criterion</flux:text>
                                            <flux:heading level="3">{{ $detail->number }}</flux:heading>
                                        </div>

                                        <div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Level</flux:text>
                                            <div class="mt-1">
                                                <flux:badge variant="primary">WCAG {{ $detail->level }}</flux:badge>
                                            </div>
                                        </div>

                                        <div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Title</flux:text>
                                            <p class="text-sm font-semibold text-zinc-900 dark:text-zinc-100 mt-1">
                                                {{ $detail->name_en }}
                                            </p>
                                        </div>

                                        <div>
                                            <flux:text class="text-xs text-zinc-500 dark:text-zinc-400">Description</flux:text>
                                            <p class="text-sm text-zinc-700 dark:text-zinc-300 mt-1 leading-relaxed">
                                                {{ $detail->description_en }}
                                            </p>
                                        </div>

                                        <div class="pt-4">
                                            <button 
                                                type="button"
                                                wire:click="toggleCriterion('{{ $detail->id }}')"
                                                class="w-full {{ in_array($detail->id, $selectedCriteria) ? 'bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-100 hover:bg-red-200 dark:hover:bg-red-800' : 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-100 hover:bg-blue-200 dark:hover:bg-blue-800' }} px-4 py-2 rounded-lg font-semibold text-sm transition"
                                            >
                                                {{ in_array($detail->id, $selectedCriteria) ? __('Remove from Selection') : __('Add to Selection') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="bg-zinc-50 dark:bg-zinc-800 p-6 rounded-lg text-center">
                                    <flux:text class="text-zinc-600 dark:text-zinc-400">
                                        {{ __('Select a criterion to view details') }}
                                    </flux:text>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 p-6 flex justify-end gap-3">
                    <button 
                        type="button"
                        @click="open = false"
                        class="px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition"
                    >
                    {{ __('Done') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
