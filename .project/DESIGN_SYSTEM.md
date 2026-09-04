# Design System Guidelines - Kompassen

Denna dokumentation definierar de visuella riktlinjer för konsistent användarupplevelse.

## 1. Färgschema

### Base Colors (Flux Zinc)
- **Primary Surface**: `bg-white` (light), `dark:bg-zinc-900` (dark)
- **Secondary Surface**: `bg-zinc-50` (light), `dark:bg-zinc-800` (dark)
- **Tertiary Surface**: `bg-zinc-100` (light), `dark:bg-zinc-700` (dark)
- **Input/Form**: `bg-white dark:bg-zinc-950`
- **Borders**: `border-zinc-200` (light), `dark:border-zinc-700` (dark)
- **Text**: `text-zinc-900` (light), `dark:text-zinc-100` (dark)
- **Text Secondary**: `text-zinc-600` (light), `dark:text-zinc-400` (dark)

### Status Colors
- **Success**: Use Flux success variant (green)
- **Warning**: Use Flux warning variant (amber)
- **Danger**: Use Flux danger variant (red)
- **Info**: Use Flux info variant (blue)

### Never Use
❌ `gray-*` colors (Breeze legacy)
❌ `slate-*`, `stone-*` colors
❌ Mixed color scales in same component

## 2. Component System

### Always Use Flux Components
Use Flux UI components as primary choice:
- Buttons → `<flux:button>`
- Forms → `<flux:field>`, `<flux:input>`, `<flux:select>`, `<flux:textarea>`, `<flux:checkbox>`
- Cards → `<flux:card>`
- Headings → `<flux:heading>`
- Text → `<flux:text>`
- Tables → `<flux:table>`
- Dropdowns → `<flux:dropdown>`
- Badges → `<flux:badge>`
- Modals → `<flux:modal>`

### When to Use Tailwind
Use Tailwind **only** for:
- Layout & spacing (flex, grid, gap, padding, margin)
- Responsive utilities (sm:, md:, lg:)
- Positioning (absolute, relative, z-index)
- Supporting surfaces around Flux components

### Never
❌ Raw HTML `<input>` elements
❌ Custom button styling with `<button class="...">`
❌ Manual form field markup

## 3. Light/Dark Mode

### Pattern
Every component uses this pattern:
```blade
<div class="bg-white dark:bg-zinc-900 text-zinc-900 dark:text-zinc-100">
```

### Dark Mode Pairs
| Light | Dark |
|-------|------|
| `bg-white` | `dark:bg-zinc-900` |
| `bg-zinc-50` | `dark:bg-zinc-800` |
| `bg-zinc-100` | `dark:bg-zinc-700` |
| `text-zinc-900` | `dark:text-zinc-100` |
| `text-zinc-600` | `dark:text-zinc-400` |
| `border-zinc-200` | `dark:border-zinc-700` |

### Never Mix
❌ `bg-white dark:bg-gray-800` (mixing scales)
❌ Forgetting dark: variant

## 4. Page Structure

All pages use this consistent structure:

```blade
<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Page heading -->
            <flux:heading level="1" class="mb-2">{{ $title }}</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                {{ $description }}
            </flux:text>

            <!-- Main content -->
            <div class="space-y-6">
                <!-- Your content here -->
            </div>
        </div>
    </div>
</x-app-layout>
```

### Key Principles
1. **Heading + Subheading** at top with mb-8 spacing
2. **Main container** = `max-w-4xl` for content pages
3. **Padding** = `px-4 py-8` for responsive spacing
4. **Section spacing** = `space-y-6` for vertical rhythm
5. **Min height** = `min-h-screen` to avoid footer issues

## 5. Form Styling

### Always Use Flux
```blade
<flux:field>
    <flux:label>Label Text</flux:label>
    <flux:input type="text" name="field" />
    <flux:error name="field" />
</flux:field>
```

### Never
❌ Inline `<input class="...">` without flux:field wrapper
❌ Custom validation styling
❌ Manual placeholder text without labels

## 6. Cards & Containers

### Card Pattern
```blade
<flux:card class="p-4">
    <div class="space-y-3">
        <!-- Content -->
    </div>
</flux:card>
```

### Secondary Surfaces
For secondary backgrounds:
```blade
<div class="bg-zinc-50 dark:bg-zinc-800 p-4 rounded-lg">
    <!-- Content -->
</div>
```

## 7. Spacing System

### Consistent Gaps
- **Between sections**: `space-y-6`
- **Between form fields**: `space-y-3`
- **Between cards**: `gap-4`
- **Padding**: `p-4` or `px-4 py-8`
- **Margins**: Use Tailwind (mt-, mb-, etc.)

## 8. Typography

### Hierarchy
```blade
<!-- Page title -->
<flux:heading level="1">Main Title</flux:heading>

<!-- Section heading -->
<flux:heading level="2">Section</flux:heading>

<!-- Subheading/description -->
<flux:text class="text-zinc-600 dark:text-zinc-400">
    Description text here
</flux:text>

<!-- Body text -->
<p class="text-zinc-900 dark:text-zinc-100">
    Body paragraph
</p>
```

## 9. Common Patterns

### Action Bar (top of lists)
```blade
<div class="flex gap-2 mb-6">
    <flux:button>Primary Action</flux:button>
    <flux:button variant="ghost">Secondary</flux:button>
</div>
```

### Status Badge
```blade
<flux:badge variant="primary">Active</flux:badge>
```

### Empty State
```blade
<div class="text-center py-12">
    <flux:text class="text-zinc-600 dark:text-zinc-400">
        No items found
    </flux:text>
</div>
```

## 10. Responsive Design

### Mobile First
```blade
<!-- Stack on mobile, row on medium+ -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
```

### Common Breakpoints
- `sm:` — 640px (rarely needed)
- `md:` — 768px (common for 2-column)
- `lg:` — 1024px (common for 3-column)
- `xl:` — 1280px (rarely needed)

## Implementation Checklist

- [ ] Use `<x-app-layout>` for all authenticated pages
- [ ] Use Flux components for ALL form fields
- [ ] Use `zinc` color scale exclusively
- [ ] Pair dark mode variants for all backgrounds
- [ ] Consistent `min-h-screen bg-white dark:bg-zinc-900` on content divs
- [ ] Headings at top with description text
- [ ] `max-w-4xl` container for content pages
- [ ] `space-y-6` for section spacing
- [ ] No custom HTML inputs — always `flux:input`, `flux:select`, etc.
- [ ] Test dark mode toggle at `/dashboard` after changes

## Auditing Views

To check a view for consistency:
1. Search for `bg-gray` or `gray-` — change to `zinc-`
2. Check all `<input>` tags — wrap in `flux:input`
3. Check all `<select>` tags — use `flux:select`
4. Verify dark mode pairs exist for every background
5. Check page structure matches template above
6. Test in dark mode (toggle at navbar)

## References

- **Flux Docs**: `.examples/` folder for layout patterns
- **Tailwind Docs**: tailwindcss.com for utilities
- **App Layout**: `resources/views/components/layouts/app.blade.php`
- **Example Pages**: `resources/views/.examples/`
