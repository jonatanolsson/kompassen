#!/usr/bin/env python3
import json, os, re, sys, subprocess
TAGFILE='ALL_TEXT_NODES_TAGGED.md'
LANGFILE='resources/lang/sv.json'
if not os.path.exists(TAGFILE) or not os.path.exists(LANGFILE):
    print('Missing required files'); sys.exit(1)
with open(LANGFILE, encoding='utf-8') as f:
    lang = json.load(f)
# extended mapping
mapping = {
    'Questions':'Frågor','Recent':'Senaste','Caleb Porzio':'Caleb Porzio','Creator of Livewire':'Skapare av Livewire',
    'flux':'flux','Welcome back':'Välkommen tillbaka','Password':'Lösenord','Forgot password?':'Glömt lösenord?',
    'Log in':'Logga in','Sign up for free':'Registrera gratis','Recent':'Senaste','Invite':'Bjud in',
    'Leaderboard':'Topplista','View invoice':'Visa faktura','Refund':'Återbetalning','All':'Alla',
    'Archive':'Arkiv','Unapproved':'Ej godkänd','11 tasks':'11 uppgifter','Approved':'Godkänd',
    'Announcements':'Meddelanden','Projects':'Projekt','Most popular':'Mest populär','Newest':'Nyast','Oldest':'Äldst',
    'Email notifications':'E-postaviseringar','Choose which emails you\'d like to get from us.':'Välj vilka e-postmeddelanden du vill få från oss.',
    'New question':'Ny fråga','New task':'Ny uppgift','Tasks':'Uppgifter','Files':'Filer','John Doe':'John Doe',
    'Moderator':'Moderator','2 days ago':'2 dagar sedan','Where can I find the best tutorials for Laravel?':'Var hittar jag de bästa handledningarna för Laravel?',
    'Approve':'Godkänn','Delete':'Radera','Welcome back':'Välkommen tillbaka','Live':'Live','Marketing site':'Marknadsplats','Users':'Användare',
    '92':'92','Android app':'Android-app','Events':'Evenemang','Edit':'Redigera','Delete':'Radera','Brand guidelines':'Varumärkesriktlinjer',
    'Products':'Produkter','Acme Inc.':'Acme Inc.','Sign up for free':'Registrera gratis','iOS App V2':'iOS-app V2',
    'Filters':'Filter','ID':'ID','Date':'Datum','Status':'Status','Customer':'Kund','Purchase':'Köp','Revenue':'Intäkter','Board':'Panel',
    'List':'Lista','Timeline':'Tidslinje','#':'#','&ZeroWidthSpace;':'','Filter by:':'Filtrera efter:','Amount':'Belopp','More filters...':'Fler filter...',
    'Logout':'Logga ut','Settings':'Inställningar','Help':'Hjälp','Profile':'Profil','This is how others will see you on the site.':'Så här kommer andra att se dig på sajten.',
    'Log in':'Logga in','Sign up for free':'Registrera gratis','Save profile':'Spara profil','Save preferences':'Spara inställningar','Preferences':'Inställningar'
}
# collect keys from tagfile (exclude seeders)
texts = set()
pat = re.compile(r'^(?P<file>[^:]+):(?P<line>\d+): (?P<text>.*) -> (?P<tag>\w+)$')
with open(TAGFILE, encoding='utf-8') as f:
    for line in f:
        m = pat.match(line.strip())
        if not m:
            continue
        file = m.group('file')
        text = m.group('text')
        tag = m.group('tag')
        if file.startswith('database/seeders'):
            continue
        # include translate or stub
        if tag in ('translate','stub'):
            # avoid long content
            if len(text) <= 120:
                texts.add(text)
# update lang entries where value equals key or missing, using mapping when available; otherwise leave as-is
updated = {}
for t in sorted(texts):
    if t in mapping:
        sv = mapping[t]
    else:
        # skip names and sentences we don't know: heuristic: if contains '?', ':' or is sentence-like > 40 chars skip
        if len(t) > 40 or re.search(r'[\?\:]|\.|,', t):
            continue
        # fallback: simple lowercasing and capitalize
        sv = t
    # apply
    if t not in lang or lang[t] != sv:
        updated[t] = (lang.get(t), sv)
        lang[t] = sv
# write back
if updated:
    with open(LANGFILE, 'w', encoding='utf-8') as f:
        json.dump(lang, f, ensure_ascii=False, indent=4)
    subprocess.run(['git','add',LANGFILE])
    subprocess.run(['git','commit','-m','i18n: update sv.json translations for found text nodes','-m','Auto-updated Swedish translations for discovered UI/stub text nodes (seeders excluded).'], check=False)
print('Updated count:', len(updated))
for k,v in updated.items():
    print(k, '->', v[1])
