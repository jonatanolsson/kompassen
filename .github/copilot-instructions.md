# Copilot Instructions for Kompassen

## Purpose

Keep changes aligned with the existing Laravel + Livewire + Volt + Flux stack. Prefer small, focused edits that preserve household scoping, authorization, and the current UI system.

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

### Controllers and UI composition

The app uses thin Laravel controllers that usually render wrapper Blade views. Those views then mount Livewire or Volt components.

- HTTP routes live in `routes/web.php`
- Auth pages are defined as Volt routes in `routes/auth.php`
- API CRUD endpoints live in `routes/api.php` and use `auth:sanctum`

### Livewire and Volt split

There are two main UI patterns in this repo:

1. **Single-file Volt components in Blade views** under `resources/views/livewire/...`
2. **Class-based Livewire components** under `app/Livewire/...` for some dashboard and household-management features

Before changing a screen, inspect sibling files and keep the same component style.

## Domain rules

### Project access is critical

Never write queries that can leak data across projects.

### Authorization

Policies are part of the normal flow. Income and expense policies currently rely on project membership checks. Keep authorization explicit in controllers and component actions.


### Period handling

Periods are tied to household + year + month.

- Registration creates a default household, owner membership, and current period
- `App\Actions\EnsurePeriodExists` creates a period automatically from a chosen date when needed

Do not reintroduce a manual period selector where the codebase already derives period from date input.

## UI conventions

Flux is the default UI system. **Always start with Flux components when building or changing UI.**

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

- Follow existing naming, route names, and folder structure
- Prefer named routes with `route()`
- Reuse existing helpers, actions, and relationships before adding new abstractions
- Keep controllers thin and put reusable domain behavior in actions/services where the repo already does that
- Preserve current UX conventions: readable headings, non-wrapping primary actions when possible, and consistent spacing

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
