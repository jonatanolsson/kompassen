# Copilot Instructions for Kompassen

## Purpose

Keep changes aligned with the existing Laravel + Livewire + Volt + Flux stack. Prefer small, focused edits that preserve scoping, authorization, and the current UI system.

## Stack

- PHP 8.5
- Laravel 13
- Livewire 3
- Volt 1
- Flux UI + Flux Pro
- Tailwind CSS v3
- Pest 4
- MariaDB/MySQL in app runtime, SQLite in tests
- Laravel Sail for local commands

## Command rules

Always run build, lint, test, and Artisan commands through **Laravel Sail**.

Common commands:

```bash
# Run tests
bash vendor/bin/sail artisan test --compact
bash vendor/bin/sail artisan test --compact tests/Feature/ExpenseFormTest.php

# Format PHP changes
bash vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent

# Run migrations
bash vendor/bin/sail artisan migrate

# Frontend build
bash vendor/bin/sail npm run build
```

If a frontend change is not visible, the user may need to run `bash vendor/bin/sail npm run dev` or restart Sail.

## Architecture overview

### Accessibility audit domain

Kompassen is a WCAG accessibility audit tool. Core models:
- **AccessibilityProject** — top-level audit scope, tied to a Team
- **AccessibilityPage** — pages/services within a project
- **AccessibilityIssue** — individual WCAG violations
- **AccessibilityIssueAttachment** — screenshots/evidence for issues
- **WcagSuccessCriterion** — WCAG 2.0/2.1 criteria mappings
- **TestingMethodology** — audit methodologies per project

All models use ULID primary keys (`$keyType = 'string'`, `$incrementing = false`).

### Controllers and UI composition

The app uses thin Laravel controllers that usually render wrapper Blade views. Those views then mount Livewire or Volt components.

- HTTP routes live in `routes/web.php`
- Auth pages are defined as Volt routes in `routes/auth.php`
- API CRUD endpoints live in `routes/api.php` and use `auth:sanctum`

### Livewire and Volt split

There are two main UI patterns in this repo:

1. **Single-file Volt components in Blade views** under `resources/views/livewire/...`
2. **Class-based Livewire components** under `app/Livewire/...` (Dashboard, WcagKnowledgeBase, Edit/Create screens, etc.)

Before changing a screen, inspect sibling files and keep the same component style.

### CRITICAL: Livewire full-page component layout system

Livewire components can be routed directly (full-page). These REQUIRE the `#[Layout('layouts.app')]` attribute:

```php
#[Layout('layouts.app')]
class EditAccessibilityIssue extends Component { ... }
```

**Key rule:** The `#[Layout]` attribute automatically wraps the component view with the specified layout file. These views should render ONLY component content—do NOT wrap them with Blade component wrappers like `<x-app-layout>`.

**Wrong:** Component view contains `<x-app-layout>{{ $this->component }}</x-app-layout>` with `#[Layout]` attribute
- Results in nested layouts, missing dark mode, broken navigation

**Right:** Component view contains only the component content, wrapped by layout via `#[Layout]` attribute
- Layout's `{{ $slot }}` receives component view

Blade component wrappers (like `<x-app-layout>`) are only for wrapping non-Livewire views.

## Domain rules

### Project access is critical

Never write queries that can leak data across projects.

### Authorization

Policies are part of the normal flow. Issue and project policies rely on project membership/team access. Always authorize in controllers and component actions:

```php
$this->authorize('update', $project);
```

### File uploads in Livewire components

Use `WithFileUploads` trait for handling file uploads:

```php
use Livewire\WithFileUploads;

class EditAccessibilityIssue extends Component {
    use AuthorizesRequests;
    use WithFileUploads;

    #[Validate('nullable|array')]
    public array $attachments = [];
}
```

In Blade: `<flux:input type="file" wire:model="attachments" multiple accept="image/*" />`

**Storage convention:** Store files with meaningful subdirectories (e.g., `issue-attachments`), save the path in database as `filename` column.

### Livewire property auto-exposure caveat

Livewire automatically exposes all public properties to views. If a property name collides with a view variable passed from `render()`, the property overwrites the variable. 

**Example:** Component has `public array $attachments = []` (for `WithFileUploads`), and `render()` passes `attachments => $databaseRows`. The public property shadows the passed variable.

**Solution:** Rename view variables to avoid collision (e.g., `databaseAttachments`):

```php
return view('livewire.edit-issue', [
    'databaseAttachments' => $issueAttachments,  // not 'attachments'
]);
```

### Route model binding

Register custom model bindings in `AppServiceProvider::boot()`:

```php
$this->app['router']->model('attachment', AccessibilityIssueAttachment::class);
```

Models with string primary keys (`$keyType = 'string'`, `$incrementing = false`) need explicit bindings.

## UI conventions

### Dark mode and Flux interactivity

Flux components require specific scripts in the layout to function correctly:

- `@fluxAppearance` — enables dark mode support
- `@livewireScripts` and `@fluxScripts` — required for Flux dropdown/modal/interactive components

These MUST be in the layout file (e.g., `layouts/app.blade.php`) or in `<x-app-layout>` component:

```blade
<head>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @fluxAppearance
</head>

<body>
    ...
    @livewireScripts
    @fluxScripts
</body>
```

Without these, dropdowns won't open, modals won't work, dark mode won't toggle.

### Flux is the default UI system

**Always start with Flux components when building or changing UI.**

Do not jump straight to raw Tailwind utility styling if Flux already has a suitable component or pattern. Tailwind should be used to compose layout, spacing, and supporting presentation **around** Flux components, not as a replacement for them.

If Tailwind is needed, keep it visually aligned with Flux:

- use Flux-friendly surface and neutral scales, especially `zinc`
- follow the existing Flux-like contrast and dark-mode patterns in nearby views
- avoid introducing color choices, shadows, or control styling that fight the Flux system

### Think in Flux structure, not just components

Use the `.examples/` directory as the mental model for how screens should be composed.

Prefer Flux layout primitives before inventing structure with generic wrappers:

- `flux:sidebar`, `flux:header`, `flux:main`
- `flux:sidebar.header`, `flux:sidebar.brand`, `flux:sidebar.nav`
- `flux:sidebar.spacer`, `flux:sidebar.profile`
- `flux:sidebar.toggle`, `collapsible="mobile"` for responsive navigation
- `flux:separator` to break large pages into clear sections

When a page needs navigation, filtering, sections, or a content shell, first ask: **is there a Flux layout pattern in `.examples/` that already matches this?**

### Composition patterns from Flux examples

#### App layout and navigation

- For authenticated product pages, think in **sidebar + header + main** rather than stacked containers
- Put brand and primary navigation at the top of the sidebar
- Put secondary actions, profile, and logout at the bottom using spacer/profile patterns
- Treat mobile as a first-class layout: use sidebar toggles and collapsible mobile navigation instead of just shrinking the desktop sidebar

#### Settings and form pages

- Use a strong page heading first, then separate major sections with subtle separators
- On larger screens, prefer a two-part section structure:
  - left column for `heading` + `subheading`
  - right column for inputs, switches, radios, and actions
- Keep primary save actions aligned consistently at the end of the section or form
- Use Flux field descriptions and supporting text to explain intent instead of adding ad hoc helper markup everywhere

#### Dashboards and list pages

- Compose these pages as **toolbar/filter row -> stats/cards -> table/list**
- Keep filters and view controls in a compact top row
- Use Flux cards for summary metrics and Flux tables for record listings
- Prefer dropdowns, menus, badges, and segmented controls for dense actions instead of many competing buttons

#### Auth and onboarding pages

- Keep the main task in a focused form column with a clear primary CTA
- Put secondary marketing, illustration, or trust content in a separate supporting panel on larger screens
- Preserve strong vertical rhythm with heading, separator, fields, and CTA in a clear sequence

### Repo-specific interpretation of Flux structure

- Use Flux patterns to support the app's **project-scoped** navigation and flows
- For authenticated pages, prefer shared layouts and navigation shells over duplicating page-level navigation in every view
- Preserve the repo's current `zinc`-based tonal hierarchy when translating patterns from `.examples/`
- When adapting an example, copy the **structure and hierarchy**, not its branding or demo-specific content

### Use these patterns

- `flux:button`, `flux:input`, `flux:select`, `flux:textarea`, `flux:card`, `flux:table`, `flux:dropdown`, `flux:badge`
- `icon=""` on `<flux:button>` when the button has both icon and text
- Flux components first, then Tailwind only where Flux needs layout or surface support
- sidebar/header/main structure from Flux examples for full-page authenticated layouts
- separators and heading/subheading pairs to break large forms and settings screens into sections
- toolbar -> stats -> table composition for dashboards and list-heavy pages
- `zinc`-based neutral surfaces for cards, tables, and section backgrounds
- responsive layouts using Tailwind utilities already used in neighboring views

### Avoid these patterns

- Raw HTML form controls when a Flux equivalent exists
- Starting from Tailwind alone before checking whether Flux already solves the UI need
- Rebuilding a layout that Flux already models in `.examples/` using only generic `div` wrappers
- Treating mobile as a compressed desktop layout instead of using Flux navigation/toggle patterns
- Heavy `bg-white`, `gray-*`, and shadow-heavy panels that drift away from the current Flux-like surface scale
- Tailwind colors or visual treatments that do not match Flux surfaces, contrast, and component behavior
- `placeholder` on `flux:select` in forms; use labels, default options, or explicit `<option value="">...`

## Editing guidance

### Localization

All UI strings must be in `resources/lang/sv.json` (Swedish is primary). Use translation function in templates:

```blade
{{ __('Label text') }}
{{ __('Action completed.') }}
```

Also applies to validation messages, button labels, section headings, etc. Avoids hardcoded English text.

### General patterns

- Follow existing naming, route names, and folder structure
- Prefer named routes with `route()`
- Reuse existing helpers, actions, and relationships before adding new abstractions
- Keep controllers thin and put reusable domain behavior in actions/services where the repo already does that
- Preserve current UX conventions: readable headings, non-wrapping primary actions when possible, and consistent spacing

### Livewire component patterns

**Deletion in components:** Use `wire:click` with `@confirm` directive instead of form submissions:

```blade
<flux:button 
    wire:click="deleteAttachment('{{ $id }}')"
    @confirm
    variant="danger"
>
    Delete
</flux:button>
```

```php
public function deleteAttachment(string $id): void {
    $attachment = ...; // fetch and authorize
    Storage::disk('public')->delete($attachment->filename);
    $attachment->delete();
}
```

**File uploads:** Always show temporary uploads (preview with `temporaryUrl()`) separately from database records. This prevents Livewire array/object type confusion:

```blade
<!-- Database attachments as arrays -->
@foreach ($databaseAttachments as $attachment)
    <img src="{{ asset('storage/' . $attachment['filename']) }}" />
@endforeach

<!-- Temporary uploads as objects -->
@foreach ($this->attachments as $file)
    <img src="{{ $file->temporaryUrl() }}" />
@endforeach
```

## Testing guidance

- Use Pest for tests
- Prefer focused feature tests for behavior changes
- Run the minimum test scope that proves the change
- If you change PHP files, run Pint through Sail before finishing

Useful examples:

```bash
bash vendor/bin/sail artisan test --compact tests/Feature/UxViewRenderingTest.php
bash vendor/bin/sail artisan test --compact --filter=expense_form_redirects_after_save
```
