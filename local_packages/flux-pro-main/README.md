# Flux Pro v2.11.1

The pro version of Flux, the official UI component library for Livewire.

## Installation

### Option 1: One-Line Install (Recommended)

Run this command from your Laravel project root:

```bash
curl -s https://raw.githubusercontent.com/novislab/flux-pro/main/install.sh | bash
```

This will automatically:
- Add the repository to your `composer.json`
- Install the package via Composer
- Configure your CSS file with Flux Pro imports

### Option 2: Install via Composer (Manual)

Add the GitHub repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/novislab/flux-pro"
        }
    ]
}
```

Then require the package:

```bash
composer require livewire/flux-pro
```

Or install directly from the repository:

```bash
composer require livewire/flux-pro:dev-main
```

### Configure Tailwind CSS

Add the Flux Pro styles and component paths to your `app.css`:

```css
@import "tailwindcss";
@import "../../vendor/livewire/flux/dist/flux.css";

@source '../views';
@source '../../vendor/livewire/flux/stubs/**/*.blade.php';
@source '../../vendor/livewire/flux-pro/stubs/**/*.blade.php';
```

### Publish Assets (Optional)

If you need to customize the components, you can publish the views:

```bash
php artisan vendor:publish --tag=flux-pro-views
```

## Usage

Once installed, you can use Flux Pro components in your Blade templates:

```blade
<flux:editor wire:model="content" />

<flux:command placeholder="Search commands...">
    <flux:command.item>Option 1</flux:command.item>
    <flux:command.item>Option 2</flux:command.item>
</flux:command>

<flux:chart>
    <flux:chart.line field="revenue" />
    <flux:chart.axis />
    <flux:chart.legend />
</flux:chart>

<flux:kanban>
    <flux:kanban.column>
        <flux:kanban.column.header>To Do</flux:kanban.column.header>
        <flux:kanban.column.cards>
            <flux:kanban.card>Task 1</flux:kanban.card>
        </flux:kanban.column.cards>
    </flux:kanban.column>
</flux:kanban>

<flux:date-picker wire:model="date" />

<flux:time-picker wire:model="time" />

<flux:autocomplete wire:model="selection">
    <flux:autocomplete.item value="1">Item 1</flux:autocomplete.item>
    <flux:autocomplete.item value="2">Item 2</flux:autocomplete.item>
</flux:autocomplete>

<flux:file-upload wire:model="files" />

<flux:pillbox wire:model="tags">
    <flux:pillbox.option value="tag1">Tag 1</flux:pillbox.option>
    <flux:pillbox.option value="tag2">Tag 2</flux:pillbox.option>
</flux:pillbox>

<flux:slider wire:model="value" min="0" max="100" />

<flux:select searchable>
    <flux:select.option value="1">Option 1</flux:select.option>
    <flux:select.option value="2">Option 2</flux:select.option>
</flux:select>
```

## Available Components

Flux Pro v2.11.1 includes the following premium components:

- **Editor** - Rich text editor with customizable toolbar
- **Command** - Command palette interface for quick actions
- **Chart** - Advanced charting with area, line, axis, legend, tooltip, and summary
- **Kanban** - Drag-and-drop kanban board with columns and cards (NEW)
- **Calendar** - Interactive calendar component
- **Date Picker** - Date selection with calendar popup
- **Time Picker** - Time selection interface
- **Autocomplete** - Autocomplete input with async support
- **File Upload** - File upload with drag & drop and dropzone
- **Select** - Enhanced searchable select with combobox, listbox, and custom variants
- **Pillbox** - Multi-select pill/tag selection interface with combobox variant
- **Slider** - Range slider input (NEW)
- **Composer** - Rich composition interface (NEW)
- **Tab** - Tab navigation with panels
- **Popover** - Popover overlay component
- **Accordion** - Collapsible accordion sections
- **Toast** - Toast notification system
- **Context** - Context menu component

## What's New in v2.11.1

- Added **Kanban** component for drag-and-drop task boards
- Added **Slider** component for range input
- Added **Composer** component
- Enhanced **Select** with indicator variants (check, checkbox, radio)
- Enhanced **Pillbox** with combobox variant and option create/empty states
- Updated dependencies: Livewire ^3.7.4|^4.0, Flux 2.11.1

## Requirements

- PHP ^8.1
- Laravel ^10.0|^11.0|^12.0
- Livewire ^3.7.4|^4.0
- Flux (base package) 2.11.1

## License

Proprietary - See LICENSE.md for details.
