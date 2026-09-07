#!/usr/bin/env python3
import json, re, os, sys, subprocess
TAGFILE='ALL_TEXT_NODES_TAGGED.md'
LANGFILE='resources/lang/sv.json'
if not os.path.exists(TAGFILE):
    print('Missing', TAGFILE); sys.exit(1)
if not os.path.exists(LANGFILE):
    print('Missing', LANGFILE); sys.exit(1)
with open(LANGFILE, encoding='utf-8') as f:
    lang = json.load(f)
# mapping English -> Swedish for common UI labels
mapping = {
    'Save': 'Spara',
    'Cancel': 'Avbryt',
    'Create': 'Skapa',
    'Update': 'Uppdatera',
    'Edit': 'Redigera',
    'Delete': 'Radera',
    'Log in': 'Logga in',
    'Register': 'Registrera',
    'Dashboard': 'Instrumentpanel',
    'Profile': 'Profil',
    'Search': 'Sök',
    'Home': 'Hem',
    'Back': 'Tillbaka',
    'Add': 'Lägg till',
    'Remove': 'Ta bort',
    'Upload': 'Ladda upp',
    'Close': 'Stäng',
    'Done': 'Klart',
    'Save profile': 'Spara profil',
    'Save preferences': 'Spara inställningar',
    'Preferences': 'Inställningar',
    'Settings': 'Inställningar',
    'Help': 'Hjälp',
    'Forgot password?': 'Glömt lösenord?',
    'Sign up for free': 'Registrera gratis',
    'View invoice': 'Visa faktura',
    'Refund': 'Återbetalning',
    'Archive': 'Arkiv',
    'All': 'Alla',
    'New task': 'Ny uppgift',
    'Tasks': 'Uppgifter',
    'Files': 'Filer',
    'Inbox': 'Inkorg',
    'Orders': 'Beställningar',
    'Catalog': 'Katalog',
    'Configuration': 'Konfiguration',
    'Marketing site': 'Marknadsplats',
    'Android app': 'Android-app',
    'Brand guidelines': 'Varumärkesriktlinjer',
    'Email notifications': 'E-postaviseringar',
    'Choose which emails you\'d like to get from us.': 'Välj vilka e-postmeddelanden du vill få från oss.',
    'Save preferences': 'Spara inställningar',
    'Filter by:': 'Filtrera efter:',
    'More filters...': 'Fler filter...',
    'Last 7 days': 'Senaste 7 dagarna',
    'Last 14 days': 'Senaste 14 dagarna',
    'Last 30 days': 'Senaste 30 dagarna',
    'Last 60 days': 'Senaste 60 dagarna',
    'Last 90 days': 'Senaste 90 dagarna',
    'Password': 'Lösenord',
    'Forgot password?': 'Glömt lösenord?',
    '<button>Click me</button>': '<button>Klicka här</button>',
    'Click me': 'Klicka här',
    'Title': 'Titel',
    'Description': 'Beskrivning',
    'Reference URL': 'Referens-URL',
    'Link Label': 'Länktext',
    'Code Snippet': 'Kodavsnitt',
}
# collect keys from TAGFILE excluding seeders
keys_to_update = set()
with open(TAGFILE, encoding='utf-8') as f:
    for line in f:
        m = re.match(r'^(?P<file>[^:]+):(?P<line>\d+): (?P<text>.*) -> (?P<tag>\w+)$', line.strip())
        if not m:
            continue
        file = m.group('file')
        text = m.group('text')
        tag = m.group('tag')
        # skip seeders
        if file.startswith('database/seeders'):
            continue
        # include only short UI-like strings
        if len(text) > 120:
            continue
        # include if tag is translate or stub and mapping exists
        if text in mapping:
            keys_to_update.add(text)
# apply updates
changed = {}
for k in keys_to_update:
    sv = mapping[k]
    if k in lang and lang[k] != sv:
        changed[k] = (lang[k], sv)
        lang[k] = sv
    elif k not in lang:
        changed[k] = (None, sv)
        lang[k] = sv
if changed:
    # write back
    with open(LANGFILE, 'w', encoding='utf-8') as f:
        json.dump(lang, f, ensure_ascii=False, indent=4)
    subprocess.run(['git','add',LANGFILE])
    msg = 'i18n: update sv.json values for common UI text nodes'
    subprocess.run(['git','commit','-m',msg,'-m','Auto-updated sv.json for mapped UI strings.'], check=False)
print('Updated keys:', changed)
