# Copilot Instructions for Kompassen

Kompassen is a Laravel 13 WCAG accessibility-audit application. Keep changes small and preserve project scoping, authorization, localization, and the existing Flux UI.

## Stack

- PHP 8.3+; Laravel 13
- Livewire 3, Volt 1, Flux UI 2 / Flux Pro
- Pest 4, Laravel Pint, Laravel Sail
- Tailwind CSS 4, Vite
- MariaDB/MySQL in the app; SQLite in tests

## Commands

Run Artisan, PHP, tests, Pint, and npm through Sail:

```bash
# Full test suite
bash vendor/bin/sail artisan test --compact

# One file or test
bash vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php
bash vendor/bin/sail artisan test --compact --filter=test_name

# Format changed PHP files
bash vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent

# Build frontend assets
bash vendor/bin/sail npm run build

# Watch frontend assets
bash vendor/bin/sail npm run dev
```

Use existing Artisan commands with `--no-interaction`. Run the focused test, Pint, and build relevant to the change.

## Architecture

- Web routes: `routes/web.php`; auth routes: `routes/auth.php`; API routes: `routes/api.php` with `auth:sanctum`.
- Controllers are thin. Reusable domain logic belongs in existing actions/services.
- Class-based Livewire components live in `app/Livewire`; Volt components are single-file views under `resources/views/livewire`.
- Full-page Livewire components use `#[Layout('layouts.app')]`. Their views contain only page content; never nest `<x-app-layout>` inside them.
- Core domain: `AccessibilityProject` contains `AccessibilityPage` and `AccessibilityIssue`; issues can have `AccessibilityIssueAttachment` records and WCAG criteria. `TestingMethodology` belongs to projects.
- Models use ULID string keys (except models that explicitly use UUIDs).

## Non-negotiable conventions

### Scope and authorization

- Every project-related query and mutation must be scoped to the current project.
- Authorize in every controller and Livewire action before reading or mutating project data, normally with `$this->authorize(...)`.
- Use named routes and `route()` for links and redirects.

### Livewire

- Validate and authorize inside component actions, not only in Blade.
- Livewire exposes public properties to the view. Do not pass a render variable with the same name as a public property; e.g. use `$databaseAttachments` when `$attachments` holds `TemporaryUploadedFile` objects.
- For uploads, use `WithFileUploads`, bind the input with `wire:model`, preview temporary files with `temporaryUrl()`, and render them separately from database attachments.
- Issue files are stored on the `public` disk under `accessibility-issues/{issue}`; `filename` stores the generated basename and `path` stores the disk-relative path.
- Destructive Livewire actions must verify ownership/project scope and remove both the storage file and database record.

### Layout and Flux

- Use Flux components before raw HTML controls. Use Tailwind mainly for layout and responsive composition.
- Keep Flux styling aligned with existing `zinc` surfaces and dark-mode variants; follow patterns in `.examples/`.
- Authenticated layouts must include `@fluxAppearance`, `@livewireScripts`, and `@fluxScripts`; without them dark mode, dropdowns, and modals fail.
- Use `flux:editor wire:model="description"` for rich-text issue descriptions. Keep editor state as a string and validate it in the Livewire action.

### Localization

- UI strings belong in `resources/lang/sv.json`.
- Use `__()` in Blade and PHP for labels, actions, headings, messages, and validation-related text.

### PHP and tests

- Follow existing PHP style: explicit parameter and return types, curly braces, PHP 8 constructor promotion, and Pint formatting.
- Use Pest feature tests for behavior changes. Prefer the smallest focused test scope.
- Do not change dependencies or create new top-level architecture without approval.
