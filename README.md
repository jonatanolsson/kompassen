# Kompassen

Kompassen är en webbaserad plattform för planering, genomförande och
rapportering av WCAG-tillgänglighetsgranskningar. Ett projekt samlar sidor,
tjänster, granskningsproblem, WCAG-kriterier, testmetodik och rapporter på ett
ställe.

Applikationen är byggd för svenska arbetsflöden och använder projektbaserad
åtkomst med roller för projektmedlemmar. Rapporter kan förhandsgranskas,
laddas ner som PDF och delas via tidsbegränsade länkar.

## Huvudfunktioner

- **Dashboard** – översikt över projekt, granskningsstatus och snabbåtgärder.
- **Projekt** – skapa, redigera och följa upp tillgänglighetsprojekt med
  målversion och målnivå för WCAG.
- **Sidor och tjänster** – registrera både publika och inloggade resurser,
  exempelvis Mina sidor, med URL, beskrivning, åtkomstkontext och
  granskningsomfattning.
- **Problem** – dokumentera tillgänglighetsproblem med allvarlighetsgrad,
  svårighetsgrad, status, tilldelad medlem, WCAG-kriterium, rich text och
  bilagor/skärmbilder.
- **Problemhantering** – tilldela, lösa, exportera och följa upp problem.
- **WCAG-kunskapsbank** – sökbara framgångskriterier, exempel och relaterade
  resurser från bland annat W3C och DIGG.
- **Testmetodik** – administrera återanvändbara metoder och koppla dem till
  projekt.
- **Rapporter** – skapa rapporter från projektets data, visa förhandsvisning
  och ladda ner PDF.
- **Delning** – skapa delningslänkar till kundanpassad rapportförhandsvisning
  utan inloggning.
- **Projektmedlemmar** – hantera medlemmar och roller i en separat vy.
- **Rich text och säker rendering** – användarinnehåll saneras före visning.
- **Svenska översättningar** – UI-strängar går via Laravels
  översättningsfunktion.

## Teknikstack

- PHP 8.3+
- Laravel 13
- Livewire 3 och Volt 1
- Flux UI 2 / Flux Pro
- Tailwind CSS 4
- Vite
- Pest 4 och Laravel Pint
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
