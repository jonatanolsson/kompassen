<div class="product-page space-y-6">
    <x-ui.page-header
        :title="__('Component catalog')"
        :description="__('A live catalog of Kompassen UI patterns and reusable components.')"
    />

    <x-ui.section
        :title="__('Foundations')"
        :description="__('Use these shared colors and surfaces as the default visual language.')"
    >
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-ui.stat-card :label="__('Base color')" value="Zinc">
                {{ __('Neutral text, borders, and surfaces.') }}
            </x-ui.stat-card>
            <x-ui.stat-card :label="__('Accent color')" value="Emerald">
                {{ __('Use accent for primary actions and selected states.') }}
            </x-ui.stat-card>
            <x-ui.stat-card :label="__('Shared surface')" value="product-section">
                {{ __('Consistent border, radius, background, and shadow.') }}
            </x-ui.stat-card>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Application components')"
        :description="__('Use these app-specific components for repeated layouts; use Flux directly for controls.')"
    >
        <div class="grid gap-4 sm:grid-cols-2">
            <x-ui.stat-card :label="__('Page header')" value="x-ui.page-header">
                {{ __('Title, optional description, and actions slot.') }}
            </x-ui.stat-card>
            <x-ui.stat-card :label="__('Section')" value="x-ui.section">
                {{ __('Shared surface with optional heading and actions.') }}
            </x-ui.stat-card>
            <x-ui.stat-card :label="__('Stat card')" value="x-ui.stat-card">
                {{ __('Label, prominent value, and optional supporting text.') }}
            </x-ui.stat-card>
            <x-ui.stat-card :label="__('Empty state')" value="x-ui.empty-state">
                {{ __('Consistent no-data message with optional actions.') }}
            </x-ui.stat-card>
        </div>

        <div class="mt-4">
            <x-ui.empty-state
                :message="__('No testing methods added yet.')"
                :description="__('Add a method to see how empty states can guide the next action.')"
            >
                <x-slot:actions>
                    <flux:button type="button" size="sm" variant="outline">{{ __('Add Method') }}</flux:button>
                </x-slot:actions>
            </x-ui.empty-state>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Typography and headings')"
        :description="__('Use semantic levels for page structure and sizes for visual hierarchy.')"
    >
        <div class="space-y-3">
            <flux:heading level="3" size="xl">{{ __('Heading level 3') }}</flux:heading>
            <flux:heading level="4" size="lg">{{ __('Heading level 4') }}</flux:heading>
            <flux:text>{{ __('Body text example.') }}</flux:text>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Rich text editor')"
        :description="__('Use the Flux editor for authored content and bind its string value with wire:model.')"
    >
        <flux:editor
            wire:model="editorContent"
            :label="__('Editor example')"
            :description="__('Try formatting the text with the accessible toolbar.')"
            :placeholder="__('Write a short accessibility note...')"
            toolbar="heading | bold italic underline | bullet ordered | undo redo"
        />
    </x-ui.section>

    <x-ui.section
        :title="__('Buttons')"
        :description="__('Prefer Flux buttons and use variants according to action importance.')"
    >
        <div class="flex flex-wrap items-center gap-2">
            <flux:button type="button" variant="primary">{{ __('Save') }}</flux:button>
            <flux:button type="button" variant="outline">{{ __('Cancel') }}</flux:button>
            <flux:button type="button" variant="ghost">{{ __('Edit') }}</flux:button>
            <flux:button type="button" variant="subtle">{{ __('Read-only') }}</flux:button>
            <flux:button type="button" variant="danger">{{ __('Delete') }}</flux:button>
            <flux:button type="button" variant="primary" disabled>{{ __('Disabled') }}</flux:button>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Form controls')"
        :description="__('Use Flux fields so labels, controls, and validation messages stay connected.')"
    >
        <div class="grid gap-4 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Name') }}</flux:label>
                <flux:input placeholder="{{ __('Name') }}" />
                <flux:error name="sample-name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Method / Tool') }}</flux:label>
                <flux:select>
                    <option value="">{{ __('Select a method...') }}</option>
                    <option value="screen-reader">{{ __('Screen Readers') }}</option>
                    <option value="browser">{{ __('Browsers') }}</option>
                </flux:select>
                <flux:error name="sample-method" />
            </flux:field>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Status and feedback')"
        :description="__('Color supports status meaning; always keep a readable text label.')"
    >
        <div class="flex flex-wrap items-center gap-2">
            <flux:badge color="zinc">{{ __('Read-only') }}</flux:badge>
            <flux:badge color="green">{{ __('Resolved') }}</flux:badge>
            <flux:badge color="amber">{{ __('In progress') }}</flux:badge>
            <flux:badge color="red">{{ __('Open') }}</flux:badge>
        </div>

        <flux:callout variant="warning" class="mt-4">
            <flux:callout.text>{{ __('This action cannot be undone.') }}</flux:callout.text>
        </flux:callout>
    </x-ui.section>

    <x-ui.section
        :title="__('Progress and loading')"
        :description="__('Give progress a readable label and use skeletons while content loads.')"
    >
        <div class="grid gap-6 md:grid-cols-2">
            <flux:field>
                <flux:label>{{ __('Audit progress') }}</flux:label>
                <flux:progress value="42" color="emerald" />
                <flux:description>{{ __('Example progress: 42 percent complete.') }}</flux:description>
            </flux:field>

            <flux:skeleton.group animate="shimmer" class="space-y-3">
                <flux:skeleton.line class="w-2/3" />
                <flux:skeleton.line />
                <flux:skeleton.line class="w-3/4" />
            </flux:skeleton.group>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Compact data table')"
        :description="__('Use Flux tables for dense data; keep action and metadata columns intrinsic.')"
    >
        <div class="max-w-full overflow-x-auto">
            <flux:table class="w-full table-auto!">
                <flux:table.columns>
                    <flux:table.column>{{ __('Method / Tool') }}</flux:table.column>
                    <flux:table.column>{{ __('Category') }}</flux:table.column>
                    <flux:table.column class="whitespace-nowrap">{{ __('Actions') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.row>
                    <flux:table.cell class="font-medium">NVDA</flux:table.cell>
                    <flux:table.cell>{{ __('Screen Readers') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button type="button" size="sm" variant="subtle" aria-label="{{ __('Edit') }}">
                            {{ __('Edit') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
                <flux:table.row>
                    <flux:table.cell class="font-medium">VoiceOver</flux:table.cell>
                    <flux:table.cell>{{ __('Screen Readers') }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:button type="button" size="sm" variant="subtle" aria-label="{{ __('Edit') }}">
                            {{ __('Edit') }}
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            </flux:table>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Separators')"
        :description="__('Use separators between related content, not as decoration between bordered sections.')"
    >
        <div class="space-y-5">
            <flux:separator :text="__('Or')" />

            <div class="flex items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
                <span>{{ __('Pages & Services') }}</span>
                <flux:separator vertical class="h-4" />
                <span>{{ __('Issues') }}</span>
            </div>
        </div>
    </x-ui.section>

    <x-ui.section
        :title="__('Tabs example')"
        :description="__('Use tabs for related views inside the same page; keep panels matched to tab names.')"
    >
        <flux:tab.group>
            <flux:tabs wire:model="activeExampleTab" scrollable>
                <flux:tab name="overview">{{ __('Overview') }}</flux:tab>
                <flux:tab name="details">{{ __('Details') }}</flux:tab>
                <flux:tab name="activity">{{ __('Activity') }}</flux:tab>
            </flux:tabs>

            <flux:tab.panel name="overview" class="pt-4">
                <flux:text>{{ __('Overview panel example.') }}</flux:text>
            </flux:tab.panel>
            <flux:tab.panel name="details" class="pt-4">
                <flux:text>{{ __('Details panel example.') }}</flux:text>
            </flux:tab.panel>
            <flux:tab.panel name="activity" class="pt-4">
                <flux:text>{{ __('Activity panel example.') }}</flux:text>
            </flux:tab.panel>
        </flux:tab.group>
    </x-ui.section>

    <x-ui.section
        :title="__('Accordion example')"
        :description="__('Use accordions for optional, expandable detail such as FAQs.')"
    >
        <flux:accordion exclusive transition>
            <flux:accordion.item :heading="__('When should I use tabs?')">
                <flux:text>{{ __('Use tabs to switch between related panels without leaving the page.') }}</flux:text>
            </flux:accordion.item>
            <flux:accordion.item :heading="__('When should I use an accordion?')">
                <flux:text>{{ __('Use an accordion to expand optional details while keeping the page compact.') }}</flux:text>
            </flux:accordion.item>
        </flux:accordion>
    </x-ui.section>

    <x-ui.section
        :title="__('Overlays and menus')"
        :description="__('Use Flux for accessible dialogs and action menus.')"
    >
        <div class="flex flex-wrap items-center gap-2">
            <flux:modal.trigger name="design-system-example-modal">
                <flux:button type="button" variant="outline">{{ __('Open modal') }}</flux:button>
            </flux:modal.trigger>

            <flux:dropdown align="start">
                <flux:button type="button" variant="subtle">{{ __('Actions') }}</flux:button>
                <flux:menu>
                    <flux:menu.item icon="pencil">{{ __('Edit') }}</flux:menu.item>
                    <flux:menu.item icon="trash" variant="danger">{{ __('Delete') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>
        </div>
    </x-ui.section>

    <flux:modal name="design-system-example-modal" class="md:w-[28rem]">
        <div class="space-y-6">
            <div>
                <flux:heading level="2">{{ __('Modal example') }}</flux:heading>
                <flux:text class="mt-2 text-zinc-600 dark:text-zinc-400">
                    {{ __('Keep modal titles clear and place destructive actions last.') }}
                </flux:text>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button type="button" variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="button" variant="primary">{{ __('Save') }}</flux:button>
            </div>
        </div>
    </flux:modal>
</div>
