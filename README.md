# Kompassen

Kompassen är en webbaserad plattform för planering, genomförande och
rapportering av WCAG-tillgänglighetsgranskningar. Ett projekt samlar sidor,
tjänster, granskningsproblem, WCAG-kriterier, testmetodik och rapporter på ett
ställe.

Applikationen är byggd för svenska arbetsflöden och använder projektbaserad
åtkomst med roller för projektmedlemmar. Rapporter kan förhandsgranskas,
laddas ner som PDF och delas via tidsbegränsade länkar.

## Huvudfunktioner

- **Dashboard** – visar öppna problem och senaste projektaktivitet med direktväg
  till granskningens nästa steg.
- **Projekt** – skapa, redigera och följa upp tillgänglighetsprojekt med
  målversion och målnivå för WCAG. Projektets valfria huvudwebbplats kan
  ändras utan att sidornas URL:er påverkas; den visas som domän och kan
  användas för rapportens webbplatsikon.
- **Sidor och tjänster** – registrera både publika och inloggade resurser,
  exempelvis Mina sidor, med URL, beskrivning, åtkomstkontext och
  granskningsomfattning. Projektöversikten visar en kompakt sammanfattning;
  varje resurs har en separat detaljvy.
- **Problem** – dokumentera tillgänglighetsproblem med allvarlighetsgrad,
  svårighetsgrad, status, tilldelad medlem, WCAG-kriterium, separat
  användarkonsekvens i rich text och bilagor/skärmbilder. Mermaid-diagram
  renderas med JavaScript vid sparande och lagras som sanerad SVG tillsammans
  med källkoden; en omrendering krävs när källkoden ändras. Formuläret visar
  grunduppgifter först och fördjupar med ytterligare underlag, påverkan samt
  WCAG-kriterier och exempel. Nya problem börjar som öppna; status ändras
  efter sparande via åtgärden för lösning, med anteckning. Problem på sidor
  utanför granskningsomfattningen finns kvar i projektet och märks tydligt
  där, men tas inte med i rapporter.
- **Problemhantering** – tilldela eller ändra tilldelning till en medlem i
  projektet, lösa, exportera och följa upp problem.
- **WCAG-kunskapsbank** – sökbara framgångskriterier, exempel och relaterade
  resurser från bland annat W3C och DIGG. Mermaid-exempel sparar även en
  sanerad, ljus SVG för rapportens webb- och PDF-visning. Äldre exempel med
  Mermaid-kod behöver öppnas och sparas om i en webbläsare med JavaScript
  innan de kan tas med som diagram i en ny rapport; annars anger felet vilket
  exempel som behöver uppdateras. Att välja bort diagram tillåter rapport
  utan exempelbilden. I nya rapporter visas bara diagrammets bild, inte dess
  Mermaid-källa; källan finns kvar för redigering i kunskapsbanken.
- **Testmetodik** – administrera återanvändbara metoder och koppla dem till
  projekt. Gemensamma, förinstallerade metoder är skrivskyddade. Teamägaren
  kan via **Kunskapsredaktörer** utse befintliga användare i samma team till
  redaktörer. Redaktörer får skapa och ändra teamets egna metoder; bara
  gemensamma och teamets metoder får kopplas till projektet. Äldre anpassade
  metoder utan teamtillhörighet bevaras som skrivskyddade gemensamma metoder.
  Projektöversikten visar kopplade metoder kompakt med separata detaljsidor
  för metodbeskrivning och projektspecifika anteckningar.
- **Kunskapsbank** – globala WCAG-kriterier och tillhörande resurser är
  skrivskyddade. Gemensamma befintliga exempel förblir synliga men skrivskyddade;
  redaktörer skapar och redigerar enbart teamets egna exempel. Andra teams
  exempel och metoder visas inte. Nya registreringar skapar eget team och
  registranten blir teamägare; för äldre team utses den först skapade medlemmen
  till ägare vid migrering. Redaktörsutnämning förutsätter att användaren redan
  tillhör teamet; det finns ännu inget självbetjäningsflöde för att bjuda in
  användare till ett team.
- **Rapporter** – skapa en ljus, utskriftsanpassad rapport med prioriterad
  sammanfattning och tekniskt appendix. Avancerade innehållsval sparas som
  projektstandard; varje rapportversion lagrar sina egna val och sin egen
  HTML-ögonblicksbild för identiskt innehåll i webb- och PDF-visning.
  Förhandsvisningen öppnar den sparade HTML:en i en isolerad, ljus ram så
  appens mörka tema inte kan färga rapportens sidor. En uppdatering
  skapar en ny version utan att ändra tidigare rapporter. Äldre rapportmallar
  bevaras tills en ny version uttryckligen skapas. Företagets logotyp i
  appinställningarna visas överst, tillsammans med rapportens skapare och
  datum; projektets kundlogotyp visas vid projektnamnet. Välj en publik
  HTTPS-webbplats från projektets huvudwebbplats eller testade sidor när rapporten skapas
  för att bädda in dess webbplatsikon. Om ingen ikon kan hämtas visas ett
  åtgärdbart fel och du kan välja bort den. PNG, JPEG, GIF och WebP stöds,
  liksom ICO med inbäddad PNG. Inställningen sparas för nästa rapport.
  Befintliga versioner påverkas inte. Valda bilder bäddas in vid
  generering; saknade bilder eller diagram som inte matchar källkoden stoppar
  genereringen med ett åtgärdsförslag. Rapporter behåller namn på skaparen
  även om användarkontot tas bort. Vid PDF-export normaliseras
  diagrammens SVG-streckförskjutning för mPDF utan att den sparade
  rapportversionen ändras.
  Rapportdokumentet använder Verdana i webben och inbäddad DejaVu Sans
  för text och diagram i PDF, med luftigare layout och enkelt språk; tidigare
  versioner nås via
  historikmenyn på rapportens sida. Befintliga sparade versioner behåller sitt
  ursprungliga utseende tills en ny version skapas. Radering kräver bekräftelse.
- **Delning** – skapa tidsbegränsade kundlänkar från en specifik sparad
  rapportversion. Länken visar exakt den versionens isolerade, ljusa HTML-dokument
  utan inloggning, även om projektet eller senare rapportversioner ändras.
  Giltighetstid i framtiden krävs; länkar kan återkallas från rapportversionens
  sida. Projektsidans förhandsvisning visar däremot aktuellt arbete och kräver
  medlemskap. Äldre projektlänkar bevaras i databasen för återkallning men
  returnerar 410 och visar aldrig levande projektdata. Om en delad rapportversion
  raderas blir dess länk otillgänglig (410). Skapa då en ny länk från en sparad
  version.
- **Projektmedlemmar** – hantera medlemmar och roller i en separat vy.
- **Rich text och säker rendering** – användarinnehåll saneras före visning.
- **Svenska översättningar** – UI-strängar går via Laravels
  översättningsfunktion.

Rotadressen (`/`) skickar gäster till inloggningen och inloggade användare
vidare till dashboarden. Det finns därför en gemensam inloggningssida på
`/login`.

## Teknikstack

- PHP 8.3+
- Laravel 13
- Livewire 3 och Volt 1
- Flux UI 2 / Flux Pro
- Tailwind CSS 4
- Vite
- Pest 4, Pest Browser, Playwright och Laravel Pint
- MariaDB/MySQL i drift
- SQLite i tester
- mPDF för PDF-rapporter
- CommonMark och HTML-sanering för användarinnehåll
- Laravel Sail för lokal Docker-miljö

## Förutsättningar

Rekommenderad utvecklingsmiljö:

- Docker Desktop eller Docker Engine
- Git
- Node.js och npm, om frontend ska köras utanför Sail

Projektet använder Laravel Sail. PHP, Composer, MariaDB, Valkey och
Meilisearch körs i containrar när Sail används.

## Installation med Sail

Klona projektet och gå till projektmappen:

```bash
git clone git@github.com:jonatanolsson/kompassen.git
cd kompassen
```

Installera PHP- och JavaScript-beroenden:

```bash
composer install
cp .env.example .env
```

Starta tjänsterna:

```bash
./vendor/bin/sail up -d --build
./vendor/bin/sail artisan key:generate
./vendor/bin/sail npm install
```

Kör migreringar och seeders:

```bash
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan storage:link
```

Bygg frontend-resurser:

```bash
./vendor/bin/sail npm run build
```

Applikationen finns normalt på [http://localhost](http://localhost).

### Snabbinstallation

Composer innehåller ett setup-script som installerar beroenden, skapar `.env`,
genererar appnyckel, kör migreringar och bygger frontend:

```bash
composer run setup
```

Kör därefter seeders och storage-länk om du vill ha utvecklingsdata:

```bash
./vendor/bin/sail artisan db:seed
./vendor/bin/sail artisan storage:link
```

## Utvecklingsinloggning

`UserSeeder` skapar ett verifierat utvecklingskonto:

| Fält | Värde |
|---|---|
| E-post | `test@example.com` |
| Lösenord | `password` |
| Namn | `Test User` |

Kontot är endast avsett för lokal utveckling och test. Ändra eller ta bort
seedern och använd säkra, individuella lösenord innan applikationen körs i
någon delad eller publik miljö.

För att återskapa en lokal databas med seedad data:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

Seedningen skapar också exempelprojektet **Nordiska E-tjänster** med fem
publika och autentiserade sidor, åtta realistiska tillgänglighetsproblem,
kopplade WCAG-kriterier, testmetodik och blandade statusar för att kunna
prova projekt-, problem- och rapportflöden lokalt.

## Vanliga kommandon

Starta lokal utveckling med server, kö, loggar och Vite:

```bash
composer run dev
```

Kör tester:

```bash
./vendor/bin/sail artisan test --compact
```

Kör ett specifikt test:

```bash
./vendor/bin/sail artisan test --compact tests/Feature/AccessibilityReportTest.php
./vendor/bin/sail artisan test --compact --filter="can generate report"
```

Browser-testerna körs i Chromium. Installera browsern en gång i Sail-containern
och kör sedan browser-sviten separat:

```bash
./vendor/bin/sail exec laravel.test npx playwright install chromium
./vendor/bin/sail artisan test --compact tests/Browser
```

Formatera ändrade PHP-filer:

```bash
./vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent
```

Bygg frontend:

```bash
./vendor/bin/sail npm run build
```

Kör Vite i watch-läge:

```bash
./vendor/bin/sail npm run dev
```

Stoppa Sail:

```bash
./vendor/bin/sail down
```

## Konfiguration

Kopiera `.env.example` till `.env` och kontrollera minst:

```dotenv
APP_NAME=Kompassen
APP_URL=http://localhost
APP_LOCALE=sv
APP_FALLBACK_LOCALE=sv

DB_CONNECTION=mariadb
DB_HOST=mariadb
DB_PORT=3306
DB_DATABASE=kompassen
DB_USERNAME=sail
DB_PASSWORD=password
```

När applikationen körs via Sail ska databasvärden normalt matcha
`compose.yaml`. Om databasen körs direkt på värddatorn används vanligtvis
`DB_HOST=127.0.0.1` i stället.

För produktion:

- sätt `APP_ENV=production` och `APP_DEBUG=false`,
- använd en unik `APP_KEY`,
- byt ut alla utvecklingslösenord,
- konfigurera riktig e-postleverantör,
- konfigurera persistent och säker filhantering,
- kör `php artisan config:cache`, `route:cache` och `view:cache` efter
  verifierad konfiguration,
- exponera inte databas, Valkey eller Meilisearch publikt utan skydd.

## Projektstruktur

```text
app/
  Http/Controllers/       Tunna HTTP-kontrollers och projektflöden
  Livewire/                Interaktiva Livewire-komponenter
  Models/                  Domänmodeller och relationer
  View/Components/         Blade-komponenter och layout
database/
  factories/               Testfabriker
  migrations/              Databasschema
  seeders/                 Utvecklings- och referensdata
resources/
  lang/sv.json             Svenska översättningar
  views/                   Blade- och Livewire-vyer
  css/                     Tailwind/CSS
  js/                      Frontend-entrypoint
routes/
  web.php                  Webb- och autentiserade projektflöden
  auth.php                 Autentiseringsroutes
tests/
  Browser/                 Kritiska användarresor i Chromium
  Feature/                 Beteende- och åtkomsttester
  Unit/                    Enhetstester
```

## Åtkomst och säkerhet

Projektdata ska alltid vara kopplad till aktuell användares projektmedlemskap.
Ägare kan administrera projektmedlemmar; övriga roller får endast de
behörigheter som deras projektroll medger. Projekt-, sida-, tjänste-, problem-
och rapportroutes använder scoped binding där underresurser behöver begränsas
till rätt projekt.

Delade rapporter nås via tokenroute utan inloggning. Behandla delningslänkar
som åtkomstuppgifter och återkalla dem när de inte längre ska vara giltiga.

## Designsystem

Gemensamma UI-regler, komponent-API:er och Flux-/Tailwind-mönster finns i
[`docs/design-system.md`](docs/design-system.md). Den interaktiva
komponentkatalogen finns på `/design-system` i local- och testmiljö efter
inloggning; routen registreras inte i produktion.

## Testning och kvalitet

Ändringar ska ha relevant Pest-test och följa Laravel Pint. Minimikontroller
för en ändring:

```bash
./vendor/bin/sail artisan test --compact
./vendor/bin/sail php ./vendor/bin/pint --dirty --format=agent
./vendor/bin/sail npm run build
```

## Licens

Projektets licens fastställs separat. Laravel och övriga beroenden omfattas av
de licenser som anges av respektive projekt.
