# Kompassen - Project Status & Design System Implementation

## Current Status

**Project**: WCAG Accessibility Audit Tracker (Laravel + Flux UI)
**Last Updated**: 2026-09-04
**Overall Health**: 🟡 In Progress (Design standardization needed)

---

## Known Issues Fixed Today

### 1. ✅ Route Authorization Error
**Problem**: `Call to undefined method Illuminate\Routing\RouteFileRegistrar::authorize()`
- Location: `routes/web.php` - issues create route was using Closure with `$this->authorize()`
- Fix: Moved to `AccessibilityIssueController::create()` method
- Status: RESOLVED

### 2. ✅ Livewire Serialization Error
**Problem**: `Method or action [toJSON] does not exist on component`
- Root Cause: Livewire + WithFileUploads + Volt single-file components have serialization issues
- Fix: Reverted to classical HTML forms with `<flux:input type="file">` and controller handling
- Status: RESOLVED

### 3. 🟡 Visual Inconsistencies
**Problem**: App pendler mellan stilar och dark/light mode
- **Color Scale Mixing**: 
  - Layout = `gray-*` (Breeze legacy)
  - New pages = `zinc-*` (Flux standard)
  - Text colors = mixed `gray-600` vs `text-zinc-600`
- **Dark Mode Pairs Inconsistent**:
  - `dark:bg-gray-800` (old)
  - `dark:bg-zinc-900` (new)
  - `dark:bg-zinc-950` (raw inputs)
- **Form Components**:
  - Some use `flux:input`
  - Others use raw `<input class="...">`
  - Textarea used raw HTML
- Status: PARTIALLY FIXED

---

## Design System Standardization (WIP)

### ✅ Completed
1. Created `.project/DESIGN_SYSTEM.md` with comprehensive guidelines
2. Updated app layout to use `zinc` instead of `gray`
3. Converted raw file inputs to `flux:input`
4. Converted raw textarea to `flux:textarea`
5. Fixed reports view color scale
6. Fixed icon colors in reports index
7. Standardized dark mode pairs in new accessibility views

### 📋 TODO - Remaining Work

#### High Priority (Affects New Pages)
- [ ] Audit all accessibility/* pages for `dark:bg-zinc-950` → `dark:bg-zinc-950` (raw input backgrounds)
- [ ] Ensure all WCAG criteria checkboxes use `flux:checkbox` consistently
- [ ] Verify all pages follow page structure template from DESIGN_SYSTEM.md
- [ ] Test dark mode toggle on each accessibility page

#### Medium Priority (Affects Auth Pages)
- [ ] Update `resources/views/auth/*` to use zinc instead of gray
- [ ] Standardize button colors (currently using indigo for nav, red for danger)
- [ ] Update modal backgrounds to use zinc-900/800 instead of gray-800/900

#### Low Priority (Breeze Legacy)
- [ ] Update `resources/views/components/` Breeze components to use zinc
- [ ] Update dashboard.blade.php layout
- [ ] Update navigation.blade.php colors

---

## Color System Reference

### Current Inconsistency (BEFORE)
```
App Layout:      gray-800, gray-700, gray-900
Accessibility:   zinc-900, zinc-800, zinc-700
Buttons:         indigo-500, red-600
Dark Mode:       dark:bg-gray-800 OR dark:bg-zinc-900
```

### New Standard (AFTER)
```
Everything:      zinc-* (exclusively)
Surfaces:        bg-white / dark:bg-zinc-900
Secondary:       bg-zinc-50 / dark:bg-zinc-800
Tertiary:        bg-zinc-100 / dark:bg-zinc-700
Input:           bg-white / dark:bg-zinc-950
Borders:         border-zinc-200 / dark:border-zinc-700
Text Primary:    text-zinc-900 / dark:text-zinc-100
Text Secondary:  text-zinc-600 / dark:text-zinc-400
Status Colors:   Use Flux variants (red, amber, green, blue)
```

---

## Component Usage Pattern

### ✅ Correct (Always Use Flux)
```blade
<flux:field>
    <flux:label>Title</flux:label>
    <flux:input type="text" name="title" />
    <flux:error name="title" />
</flux:field>

<flux:select name="page_id">
    <option value="">Select...</option>
</flux:select>

<flux:textarea name="description" />

<flux:checkbox name="criteria[]" value="1.1.1" label="1.1.1 Text Alternatives" />

<flux:input type="file" name="attachments[]" />
```

### ❌ Wrong (Never Use Raw HTML)
```blade
<input type="text" name="title" class="...">
<textarea name="description" class="...">
<select name="page_id" class="...">
<input type="checkbox" name="criteria[]">
<input type="file" name="attachments[]">
```

---

## Page Structure Template

All new pages must follow this structure for visual consistency:

```blade
<x-app-layout>
    <div class="min-h-screen bg-white dark:bg-zinc-900">
        <div class="max-w-4xl mx-auto px-4 py-8">
            <!-- Heading -->
            <flux:heading level="1" class="mb-2">Page Title</flux:heading>
            <flux:text class="text-zinc-600 dark:text-zinc-400 mb-8">
                Description or purpose
            </flux:text>

            <!-- Content Sections (space-y-6) -->
            <div class="space-y-6">
                <!-- Form groups (space-y-3) -->
                <!-- Cards -->
                <!-- Lists -->
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## Testing Checklist for Each Page

- [ ] Loads in light mode without visual glitches
- [ ] Loads in dark mode (toggle via navbar dropdown)
- [ ] All form inputs are wrapped in `flux:field`
- [ ] No raw `<input>`, `<select>`, `<textarea>` tags
- [ ] Dark mode backgrounds use consistent zinc scale
- [ ] Text colors pair with backgrounds (`text-zinc-600 dark:text-zinc-400`)
- [ ] Responsive on mobile (use DevTools)
- [ ] No Tailwind classes breaking the design
- [ ] Heading hierarchy is logical (h1 → h2 → text)

---

## Files Modified Today

1. ✅ `.project/DESIGN_SYSTEM.md` - NEW
2. ✅ `routes/web.php` - Fixed authorize() Closure
3. ✅ `resources/views/components/layouts/app.blade.php` - Zinc colors
4. ✅ `resources/views/accessibility/issues/create.blade.php` - Flux components
5. ✅ `resources/views/accessibility/issues/edit.blade.php` - Flux components
6. ✅ `resources/views/accessibility/reports/index.blade.php` - Zinc colors
7. ✅ `resources/views/accessibility/reports/show.blade.php` - Zinc colors

---

## How to Continue

### For Next Changes
1. **Before** coding any new page or component:
   - Read `.project/DESIGN_SYSTEM.md` sections 1-7
   - Use the Page Structure Template above
2. **While** coding:
   - Use `flux:*` components exclusively
   - Use zinc color scale only
   - Pair dark mode variants for every background
3. **Before** committing:
   - Test light mode (normal view)
   - Test dark mode (toggle in navbar)
   - Run `bash vendor/bin/sail artisan test --compact`
   - Check responsive (DevTools)

### Audit Existing Views
To systematically fix remaining views:
```bash
# Find all pages using gray colors
grep -r "gray-" resources/views --include="*.blade.php"

# Find all raw inputs
grep -r "<input" resources/views --include="*.blade.php" | grep -v "flux:input"

# Find all raw textareas
grep -r "<textarea" resources/views --include="*.blade.php"

# Find all raw selects
grep -r "<select" resources/views --include="*.blade.php" | grep -v "flux:select"
```

---

## Key Principles

1. **One Color Scale** = `zinc-*` everywhere
2. **Consistent Dark Mode** = Always pair `bg-X dark:bg-Y` for each color
3. **Component First** = Flux before Tailwind before raw HTML
4. **Structure Over Style** = Use page template before adding custom CSS
5. **Test Both Modes** = Every change verified in light AND dark mode

---

## Design System Owner

Guidelines documented by: Copilot CLI
Maintained in: `.project/DESIGN_SYSTEM.md`
Enforced by: Code review + visual testing

---

**Next Session**: Continue with medium-priority items (auth pages, component standardization)
