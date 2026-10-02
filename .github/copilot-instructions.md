# Copilot Instructions for Kompassen

Kompassen is a Laravel 13 WCAG accessibility-audit application. Keep changes small and preserve project scoping, authorization, localization, and the existing Flux UI.

## Stack

- PHP 8.3+; Laravel 13
- Livewire 3, Volt 1, Flux UI 2 / Flux Pro
- Pest 4, Laravel Pint, Laravel Sail
- Tailwind CSS 4, Vite
- MariaDB/MySQL in production; SQLite in tests
- Playwright for browser testing; mPDF for PDF reports

## MCP Servers

Laravel Boost is configured in `.mcp.json`:

```bash
bash vendor/bin/sail artisan boost:mcp
```

Use Boost tools for database queries, schema inspection, and URL resolution before manual SQL or tinker.

## Commands

Run Artisan, PHP, tests, Pint, and npm through Sail:

```bash
# Full test suite
bash vendor/bin/sail artisan test --compact

# One file or test
bash vendor/bin/sail artisan test --compact tests/Feature/ExampleTest.php
bash vendor/bin/sail artisan test --compact --filter=test_name

# Browser tests (one-time install + run)
bash vendor/bin/sail exec laravel.test npx playwright install chromium
bash vendor/bin/sail artisan test --compact tests/Browser

# Format changed PHP files
bash vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent

# Build frontend assets
bash vendor/bin/sail npm run build

# Watch frontend assets (live reload)
bash vendor/bin/sail npm run dev

# Full dev environment (server, queue, watchers)
composer run dev
```

Use existing Artisan commands with `--no-interaction`. Run the focused test, Pint, and build relevant to the change. SQLite is used for test database; MariaDB for development and production.

## Architecture

- Web routes: `routes/web.php`; auth routes: `routes/auth.php`; API routes: `routes/api.php` with `auth:sanctum`.
- Controllers are thin. Reusable domain logic belongs in existing actions/services.
- Class-based Livewire components live in `app/Livewire`; Volt components are single-file views under `resources/views/livewire`.
- Full-page Livewire components use `#[Layout('layouts.app')]`. Their views contain only page content; never nest `<x-app-layout>` inside them.

### Core Domain Model

The app uses **team-scoped projects** as the access boundary:

- **Teams** own projects, users belong to teams with roles, and all audits/reports are team-scoped.
- **AccessibilityProject** contains `AccessibilityPage` (URLs/services) and `AccessibilityIssue` (findings).
- **Issues** can have multiple `AccessibilityIssueAttachment` records (images, files) and references to WCAG criteria.
- **AccessibilityReport** and `ProjectShareLink` provide report snapshots and time-limited guest previews.
- **TestingMethodology** belongs to teams; projects reference shared (read-only) or team-owned methodologies.
- **WcagSuccessCriterion**, `WcagCriterionExample`, and attachments form a searchable knowledge base.
- Models use ULID string keys (except `User`, `Team`, and `WcagSuccessCriterion` which use UUIDs).

### Team-Based Access & Scoping

- **All project queries must be team-scoped** through `$this->authorize()` and policy checks.
- Nested resources (`pages`, `issues`, `reports`) must verify the project belongs to the current team.
- **Volt components and Livewire actions validate and authorize** before reading or mutating, not just in the controller.
- Knowledge-base items (WCAG criteria, methodologies) are team-aware; users see only their team's custom methodologies and examples.

### Storage & Attachments

- **Issue files** are stored on the `public` disk under `accessibility-issues/{issueId}/`.
- Database columns: `filename` (generated basename), `path` (disk-relative path), `original_name`, `mime_type`, `size`.
- **Livewire uploads** use `WithFileUploads`, bind with `wire:model`, preview with `temporaryUrl()`.
- Separate render loops for temporary (preview) vs. database attachments.
- Destructive actions must remove both the storage file and database record with proper error handling.

## Non-negotiable conventions

### Documentation

- Update `README.md` in the same change whenever adding a new feature or
  making a critical change. Document user-visible behavior, setup changes,
  configuration requirements, and operational considerations when relevant.

### Scope and authorization

- Every project-related query and mutation must be scoped to the current project.
- Authorize in every controller and Livewire action before reading or mutating project data, normally with `$this->authorize(...)`.
- Use named routes and `route()` for links and redirects.

### Livewire

- Validate and authorize inside component actions, not only in Blade.
- Livewire exposes public properties to the view. Do not pass a render variable with the same name as a public property; e.g. use `$databaseAttachments` when `$attachments` holds `TemporaryUploadedFile` objects.
- For uploads, use `WithFileUploads`, bind the input with `wire:model`, preview temporary files with `temporaryUrl()`, and render them separately from database attachments.
- Destructive Livewire actions must verify ownership/project scope and remove both the storage file and database record.

### Volt Components

- Check existing components to determine functional vs. class-based style before writing new ones.
- Single-file Volt components go in `resources/views/livewire/`.
- For complex interactive workflows, prefer class-based Volt; for simple forms/displays, use functional style.
- All actions must validate and authorize within the component, not in the view.

### Report Generation

- **AccessibilityReport** stores snapshots of HTML and associated metadata (title, creator, generation date, issue selections).
- **ProjectShareLink** provides time-limited guest access to a specific report version without login.
- Reports are rendered light-themed and print-optimized; the app's dark theme doesn't affect report preview or PDF.
- Mermaid diagrams in WCAG examples are rendered to SVG at save time; unchanged code reuses the stored SVG, regeneration needed if code changes.
- PDF export normalizes SVG stroke offsets for mPDF compatibility without modifying the stored snapshot.
- Reports can embed a website favicon if provided and accessible; missing favicon or diagram mismatches are handled with actionable error messages.
- Each new report version creates a new record; older versions remain unchanged unless explicitly re-generated.

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
