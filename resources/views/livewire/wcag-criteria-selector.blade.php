<?php

use App\Models\WcagSuccessCriterion;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $open = false;

    public array $selectedCriteria = [];

    public ?string $selectedDetailId = null;

    public string $searchQuery = '';

    public bool $showFailureModal = false;

    public ?string $failureModalCriterionId = null;

    public string $failureType = '';

    public string $failureComment = '';

    public string $failureCodeSnippet = '';

    private $cachedCriteria = null;

    public function mount(?array $initialSelectedCriteria = null): void
    {
        if ($initialSelectedCriteria) {
            $this->selectedCriteria = $initialSelectedCriteria;
        }
    }

    public function openModal(): void
    {
        $this->open = true;
    }

    public function closeModal(): void
    {
        $this->open = false;
        $this->resetFailureModal();
    }

    public function openFailureModal(string $criterionId): void
    {
        $this->failureModalCriterionId = $criterionId;
        $this->showFailureModal = true;

        // Pre-fill if already selected
        $existing = $this->getSelectedCriterionDetails($criterionId);
        if ($existing) {
            $this->failureType = $existing['failure_type'] ?? '';
            $this->failureComment = $existing['comment'] ?? '';
            $this->failureCodeSnippet = $existing['code_snippet'] ?? '';
        } else {
            $this->resetFailureFields();
        }
    }

    public function closeFailureModal(): void
    {
        $this->showFailureModal = false;
        $this->resetFailureModal();
    }

    public function resetFailureModal(): void
    {
        $this->failureModalCriterionId = null;
        $this->resetFailureFields();
    }

    private function resetFailureFields(): void
    {
        $this->failureType = '';
        $this->failureComment = '';
        $this->failureCodeSnippet = '';
    }

    public function saveFailureDetails(): void
    {
        if (! $this->failureModalCriterionId) {
            return;
        }

        // Add or update criterion with details
        $criterionId = $this->failureModalCriterionId;
        $key = array_search($criterionId, array_column($this->selectedCriteria, 'id'));

        if ($key === false) {
            // New criterion
            $this->selectedCriteria[] = [
                'id' => $criterionId,
                'failure_type' => $this->failureType,
                'comment' => $this->failureComment,
                'code_snippet' => $this->failureCodeSnippet,
            ];
        } else {
            // Update existing
            $this->selectedCriteria[$key] = [
                'id' => $criterionId,
                'failure_type' => $this->failureType,
                'comment' => $this->failureComment,
                'code_snippet' => $this->failureCodeSnippet,
            ];
        }

        $this->closeFailureModal();
    }

    public function selectCriterion(string $id): void
    {
        $this->openFailureModal($id);
    }

    public function removeCriterion(string $id): void
    {
        $this->selectedCriteria = array_filter(
            $this->selectedCriteria,
            fn ($c) => $c['id'] !== $id
        );
        $this->selectedCriteria = array_values($this->selectedCriteria);
    }

    private function getSelectedCriterionDetails(string $id): ?array
    {
        foreach ($this->selectedCriteria as $criterion) {
            if ($criterion['id'] === $id) {
                return $criterion;
            }
        }

        return null;
    }

    public function getSelectedCriteriaIds(): array
    {
        return array_map(fn ($c) => $c['id'], $this->selectedCriteria);
    }

    public function getSelectedCriteria()
    {
        $ids = $this->getSelectedCriteriaIds();

        return WcagSuccessCriterion::whereIn('id', $ids)
            ->orderBy('number')
            ->get();
    }

    public function getDetailCriterion()
    {
        if (! $this->selectedDetailId) {
            return null;
        }

        return WcagSuccessCriterion::with('relatedResources')->find($this->selectedDetailId);
    }

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
    <!-- Hidden input to store selected criteria for form submission -->
    @foreach ($selectedCriteria as $item)
        <input type="hidden" name="wcag_criteria[]" value="{{ $item['id'] }}" />
        <input type="hidden" name="wcag_failure_types[{{ $item['id'] }}]" value="{{ $item['failure_type'] ?? '' }}" />
        <input type="hidden" name="wcag_comments[{{ $item['id'] }}]" value="{{ $item['comment'] ?? '' }}" />
        <input type="hidden" name="wcag_code_snippets[{{ $item['id'] }}]" value="{{ $item['code_snippet'] ?? '' }}" />
    @endforeach

    <!-- Button to open modal -->
    <flux:button 
        wire:click="openModal" 
        variant="ghost"
        icon="plus"
    >
        {{ __('Add WCAG Criteria') }}
    </flux:button>

    <!-- Selected Criteria Display -->
    @if ($selectedCriteria)
        <div class="mt-4 flex flex-wrap gap-2">
            @foreach ($this->getSelectedCriteria() as $criterion)
                <div class="group relative">
                    <button
                        type="button"
                        wire:click="openFailureModal('{{ $criterion->id }}')"
                        class="flex items-center gap-2 px-3 py-1.5 bg-blue-600 text-white rounded-full hover:bg-blue-700 transition cursor-pointer"
                        title="{{ __('Click to edit') }}"
                    >
                        <span>{{ $criterion->number }} — {{ $criterion->name_sv ?? $criterion->name_en }}</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="removeCriterion('{{ $criterion->id }}')"
                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600 opacity-0 group-hover:opacity-100 transition"
                        title="{{ __('Remove') }}"
                    >
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal -->
    @if ($open)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            @click.self="$wire.closeModal()"
        >
            <!-- Backdrop -->
            <div 
                class="absolute inset-0 bg-black/50"
                @click="$wire.closeModal()"
            ></div>

            <!-- Modal Content -->
            <div class="relative bg-white dark:bg-zinc-900 rounded-lg shadow-lg w-full max-w-5xl max-h-[90vh] overflow-y-auto border border-zinc-200 dark:border-zinc-700">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-6 flex items-center justify-between">
                    <flux:heading level="2">{{ __('Select WCAG Criteria') }}</flux:heading>
                    <flux:button variant="ghost" icon="x-mark" size="sm" wire:click="closeModal" class="text-zinc-400" />
                </div>

                <!-- Search Bar -->
                <div class="sticky top-16 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-4">
                    <input 
                        wire:model.debounce-500ms="searchQuery"
                        type="text"
                        placeholder="{{ __('Search criteria...') }}"
                        class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 placeholder-zinc-500 dark:placeholder-zinc-400"
                    />
                    @if (!empty($searchQuery))
                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            {{ count($this->wcagCriteria()) }} {{ __('criteria found') }}
                        </div>
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
                                @forelse ($this->wcagCriteria()->groupBy(fn ($c) => explode('.', $c->number)[0]) as $principle => $group)
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
                                            <flux:button type="button" wire:click="selectCriterion('{{ $detail->id }}')" variant="primary" class="w-full">
                                                {{ __('Add to Selection') }}
                                            </flux:button>
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
                                                    <div class="bg-zinc-100 dark:bg-zinc-800 p-3 rounded-lg border border-zinc-300 dark:border-zinc-700">
                                                        @if ($failure->code)
                                                            <flux:badge class="mb-2" variant="danger">{{ $failure->code }}</flux:badge>
                                                        @endif
                                                        <p class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                                            {{ $failure->title_sv ?? $failure->title_en }}
                                                        </p>
                                                        @if ($failure->description_sv || $failure->description_en)
                                                            <p class="text-xs text-zinc-700 dark:text-zinc-400 mt-1">
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
                                                    <a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="block bg-zinc-100 dark:bg-zinc-800 p-3 rounded-lg border border-zinc-300 dark:border-zinc-700 hover:border-zinc-400 dark:hover:border-zinc-600 transition">
                                                        <p class="text-xs font-semibold text-zinc-900 dark:text-zinc-100">
                                                            {{ $link->title_sv ?? $link->title_en }} →
                                                        </p>
                                                    </a>
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
                        wire:click="closeModal"
                        class="px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition font-semibold"
                    >
                        {{ __('Done') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Failure Details Modal -->
    @if ($showFailureModal && $failureModalCriterionId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-black/50" wire:click="closeFailureModal"></div>

            <!-- Modal Content -->
            <div class="relative bg-white dark:bg-zinc-900 rounded-lg shadow-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <!-- Header -->
                <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-700 p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ __('Add Failure Details') }}
                        </h2>
                        <button 
                            type="button"
                            wire:click="closeFailureModal"
                            class="text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300 focus:outline-none"
                        >
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <div class="p-6 space-y-4">
                    <!-- Failure Type -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Failure Type') }}
                        </label>
                        <input 
                            type="text"
                            wire:model="failureType"
                            placeholder="{{ __('E.g., F3, F13, or custom description') }}"
                            class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        />
                    </div>

                    <!-- Comment -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Comment') }}
                        </label>
                        <textarea 
                            wire:model="failureComment"
                            placeholder="{{ __('Describe why/how this criterion fails') }}"
                            rows="6"
                            class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100"
                        ></textarea>
                    </div>

                    <!-- Code Snippet -->
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">
                            {{ __('Code Snippet') }} <span class="text-xs text-zinc-500">({{ __('optional') }})</span>
                        </label>
                        <textarea 
                            wire:model="failureCodeSnippet"
                            placeholder="{{ __('Example code that fails') }}"
                            rows="8"
                            class="w-full px-3 py-2 border border-zinc-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-800 text-zinc-900 dark:text-zinc-100 font-mono text-xs"
                        ></textarea>
                    </div>
                </div>

                <!-- Footer -->
                <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-700 p-6 flex justify-end gap-3">
                    <button 
                        type="button"
                        wire:click="closeFailureModal"
                        class="px-4 py-2 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded-lg transition font-semibold"
                    >
                        {{ __('Cancel') }}
                    </button>
                    <button 
                        type="button"
                        wire:click="saveFailureDetails"
                        class="px-4 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition font-semibold"
                    >
                        {{ __('Confirm & Add') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

