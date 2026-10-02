---
name: livewire-development
description: "Use for any task or question involving Livewire. Activate if user mentions Livewire, wire: directives, or Livewire-specific concepts like wire:model, wire:click, invoke this skill. Covers building new components, debugging reactivity issues, real-time form validation, loading states, migrating from Livewire 2 to 3, converting component formats (SFC/MFC/class-based), and performance optimization. Do not use for non-Livewire reactive UI (React, Vue, Alpine-only, Inertia.js) or standard Laravel forms without Livewire."
license: MIT
metadata:
  author: laravel
---

# Livewire Development

## Documentation

Use `search-docs` for detailed Livewire 3 patterns and documentation.

## Basic Usage

### Creating Components

Use the `php artisan make:livewire [Posts\CreatePost]` Artisan command to create new components.

### Fundamental Concepts

- State should live on the server, with the UI reflecting it.
- All Livewire requests hit the Laravel backend; they're like regular HTTP requests. Always validate form data and run authorization checks in Livewire actions.

## Livewire 3 Specifics

### Key Changes From Livewire 2

These things changed in Livewire 3, but may not have been updated in this application. Verify this application's setup to ensure you follow existing conventions.
- Use `wire:model.live` for real-time updates, `wire:model` is now deferred by default.
- Components now use the `App\Livewire` namespace (not `App\Http\Livewire`).
- Use `$this->dispatch()` to dispatch events (not `emit` or `dispatchBrowserEvent`).
- Layout paths are application-specific. Follow the established layout for the project you are working in.

### New Directives

- `wire:show`, `wire:transition`, `wire:cloak`, `wire:offline`, `wire:target` are available for use.

### Alpine Integration

- Alpine is now included with Livewire; don't manually include Alpine.js.
- Plugins included with Alpine: persist, intersect, collapse, and focus.

## Best Practices

### Component Structure

- Livewire components require a single root element.
- Use `wire:loading` and `wire:dirty` for delightful loading states.

### Using Keys in Loops

<!-- Wire Key in Loops -->
```blade
@foreach ($items as $item)
    <div wire:key="item-{{ $item->id }}">
        {{ $item->name }}
    </div>
@endforeach
```

### Lifecycle Hooks

Prefer lifecycle hooks like `mount()`, `updatedFoo()` for initialization and reactive side effects:

<!-- Lifecycle Hook Examples -->
```php
public function mount(User $user) { $this->user = $user; }
public function updatedSearch() { $this->resetPage(); }
```

## JavaScript Hooks

You can listen for `livewire:init` to hook into Livewire initialization:

<!-- Livewire Init Hook Example -->
```js
document.addEventListener('livewire:init', function () {
    Livewire.hook('request', ({ fail }) => {
        if (fail && fail.status === 419) {
            alert('Your session expired');
        }
    });

    Livewire.hook('message.failed', (message, component) => {
        console.error(message);
    });
});
```

## Testing

<!-- Example Livewire Component Test -->
```php
Livewire::test(Counter::class)
    ->assertSet('count', 0)
    ->call('increment')
    ->assertSet('count', 1)
    ->assertSee(1)
    ->assertStatus(200);
```

<!-- Testing Livewire Component Exists on Page -->
```php
$this->get('/posts/create')
    ->assertSeeLivewire(CreatePost::class);
```

## Common Pitfalls

- Forgetting `wire:key` in loops causes unexpected behavior when items change
- Using `wire:model` expecting real-time updates (use `wire:model.live` instead in v3)
- Not validating/authorizing in Livewire actions (treat them like HTTP requests)
- Including Alpine.js separately when it's already bundled with Livewire 3

## Kompassen-specific rules

These rules take precedence over generic examples above when working in
Kompassen.

### Component format and layout

- Default to class-based components in `app/Livewire` with views under `resources/views/livewire`.
- Full-page class-based components use `#[Layout('layouts.app')]`. The active
  Flux application shell is `resources/views/layouts/app.blade.php`;
  `components.layouts.app` is a legacy Breeze layout.
- Use Volt only where an existing feature or an explicit requirement calls for
  a single-file component. Follow sibling components rather than converting
  formats incidentally.

### State, queries, and actions

- Treat every public property as client-controlled input. Use `#[Locked]` only for immutable values; it does not replace authorization.
- Use `wire:model` for ordinary form state. Use `wire:model.live` only when immediate feedback is required, such as search and filters.
- Submit forms with `wire:submit`. Validate inside the action, authorize
  before reading or mutating project data, and scope every project query to
  the current project.
- Keep rich-text editor state as a string and validate it in the action that saves it. Never persist showcase or preview state.
- Keep queries out of Blade. Use computed properties for derived data, and use `#[Url]` only for filters or sorting that should be shareable or survive reloads.
- For repeatable rows and nested Livewire components, provide stable `wire:key` or `:key` values.

### Events, feedback, and testing

- Use Livewire 3 events with `$this->dispatch()`. Close a modal and reset successful form state only after the action succeeds; keep validation errors visible.
- Use Flux for controls and interactive UI. Follow the component API and
  visual rules in
  [`docs/design-system.md`](../../../docs/design-system.md).
- Add focused Pest coverage for authorization, validation, project scoping,
  state changes, and dispatched events. Use browser tests when correctness
  depends on JavaScript interaction, keyboard behavior, or responsive layout.

### Resolve uncertainty before coding

- Check existing Kompassen usage first, then verify unfamiliar Livewire or Flux APIs against documentation for the installed version. Do not infer component props or behavior from names alone.
- External examples are references, not authority. Do not copy omitted authorization, persistence, or error handling.
- The [Podium Livewire views](https://github.com/livewire/podium/tree/main/resources/views/livewire)
  demonstrate URL-backed filters, computed lists, validation, reset-after-save,
  and dispatched events. Adapt them to Kompassen's component format and
  project authorization.
- Ask the user when behavior, persistence, project scope, permissions, or
  destructive-action handling is ambiguous. Do not silently choose a product
  behavior.