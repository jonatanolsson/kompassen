# Kompassen designsystem

Designsystemet ska hålla Kompassens gränssnitt konsekvent utan att ersätta
Flux UI. Flux tillhandahåller interaktiva grundkomponenter; `x-ui.*`
standardiserar återkommande Kompassen-layouter.

Komponentkatalogen på `/design-system` visar levande exempel. Den kräver
inloggning och routen registreras endast i `local`- och `testing`-miljö.

## Teknik och källor

Versionernas källor är `composer.json` och `package.json`. Projektet använder
Laravel 13, Livewire 3, Flux UI 2 / Flux Pro, Tailwind CSS 4, Alpine.js och
Vite. Pest används för beteende- och åtkomsttester.

Flux-komponenter ska användas direkt från Blade. Officiella riktlinjer:

- [Flux installation and setup](https://fluxui.dev/docs/installation)
- [Flux theming](https://fluxui.dev/docs/theming)
- [Flux customization](https://fluxui.dev/docs/customization)
- [Flux dark mode](https://fluxui.dev/docs/dark-mode)

Komponentspecifika exempel och API:er:

- [Editor](https://fluxui.dev/components/editor)
- [Heading and text](https://fluxui.dev/components/heading)
- [Progress](https://fluxui.dev/components/progress)
- [Separator](https://fluxui.dev/components/separator)
- [Tabs](https://fluxui.dev/components/tabs)
- [Accordion](https://fluxui.dev/components/accordion)
- [Skeleton](https://fluxui.dev/components/skeleton)

## Komponenter

| Komponent | API | Användning |
|---|---|---|
| `x-ui.page-header` | `title`, valfri `description`, `actions`-slot | Sidrubrik, sammanfattning och sidåtgärder |
| `x-ui.section` | valfri `title`, `description`, `actions`-slot | Produktens gemensamma sektionsyta |
| `x-ui.stat-card` | `label`, `value`, valfri standardslot | Kompakt nyckeltal eller sammanfattning |
| `x-ui.empty-state` | `message`, valfri `description`, `actions`-slot | Tomma listor och nästa rekommenderade steg |

Komponentkatalogen visar även Flux-exempel för rubrikhierarki, rich-text,
status och laddning, avdelare, flikar, utfällbara avsnitt, formulärkontroller,
tabeller, menyer och modaler. Redigerarens innehåll och aktiv flik är lokal
Livewire-demo-state; ingenting sparas.

Exempel:

```blade
<x-ui.page-header
    :title="__('Accessibility projects')"
    :description="__('Manage project audits.')"
>
    <x-slot:actions>
        <flux:button variant="primary">{{ __('Create project') }}</flux:button>
    </x-slot:actions>
</x-ui.page-header>

<x-ui.section :title="__('Issues')" class="mb-8">
    {{-- Section content --}}
</x-ui.section>

<x-ui.stat-card :label="__('Open issues')" :value="$openIssues" />

<x-ui.empty-state :message="__('No issues reported yet.')">
    <x-slot:actions>
        <flux:button variant="outline">{{ __('Report Issue') }}</flux:button>
    </x-slot:actions>
</x-ui.empty-state>
```

Flux är förstahandsval för knappar, fält, badges, tabeller, callouts,
dropdowns, menyer, avdelare och modaler. Skapa inte en `x-ui`-wrapper som
enbart byter namn på eller duplicerar en Flux-komponent.

## Visuella regler

- Använd zinc för neutral text, kanter och ytor samt gemensamma accenttokens
  (`--color-accent`, `--color-accent-content` och
  `--color-accent-foreground`) för primära åtgärder och markerade lägen.
- Använd befintliga layoutklasser: `.product-page`,
  `.product-page-header`, `.product-section`, `.product-section-heading`,
  `.product-stat` och `.user-content`.
- Ge anpassade ytor och texter stöd för mörkt läge. Flux Appearance är redan
  aktiverat i den autentiserade layouten.
- Föredra kompakta tabeller för tät, strukturerad data. Låt beskrivningskolumner
  växa och ge åtgärds-, datum- och antalskolumner innehållsbaserad bredd.
  Använd horisontell rullning endast när det behövs.
- Använd mellanrumsklasser konsekvent (`gap`, befintlig sektionspadding och
  gemensamma layoutklasser). Undvik engångsfärger, godtycklig rundning,
  dubbla kanter och `!important`. Använd `!important` endast för en bekräftad
  konflikt med Flux standardstilar.
- Färg får förstärka status, men status ska alltid ha en läsbar textetikett.
  Kontroller ska ha synlig tangentbordsfokus och tillgängliga namn.

## Komponent- och beteendegränser

1. Använd en befintlig Flux- eller `x-ui`-komponent innan du skriver egen
   markup.
2. Lägg till en gemensam komponent endast för ett återkommande, stabilt
   mönster eller en tydlig produktregel. Dokumentera dess props, slots och
   levande exempel här.
3. Behåll Flux-beteendet. Anpassa med dokumenterade props och
   komponentklasser; redigera inte vendor-filer. Publicerade Flux-komponenter
   kräver ett medvetet underhållsbeslut eftersom kopior behöver uppdateras
   manuellt.
4. Lägg synliga UI-texter i `resources/lang/sv.json` och använd `__()` för att
   visa dem. Flux-redigerarens verktygsfält har dessutom egna översättnings-
   nycklar där.
5. Validera och auktorisera Livewire-åtgärder på servern. Behåll
   projektscopen och stabila `wire:key`-värden för upprepade rader.
6. Använd semantiska rubriker, etiketter, valideringsmeddelanden och namngivna
   routes. Förlita dig inte enbart på färg, hover eller ikoner för att
   förklara en åtgärd.

## Verifiering

Kör fokuserade Pest-tester och formatera ändrad PHP via Sail:

```bash
bash vendor/bin/sail artisan test --compact tests/Feature/DesignSystemTest.php
bash vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent
bash vendor/bin/sail npm run build
```
