#!/usr/bin/env python3
import re, json, os, sys, subprocess
IN='ALL_TEXT_NODES_TAGGED.md'
LANG='resources/lang/sv.json'
if not os.path.exists(IN):
    print('Missing', IN); sys.exit(1)
# load lang
lang = {}
if os.path.exists(LANG):
    with open(LANG, encoding='utf-8') as f:
        try:
            lang = json.load(f)
        except Exception as e:
            print('Failed to load lang file:', e)
            lang = {}
changed_files = set()
with open(IN, encoding='utf-8') as fh:
    for line in fh:
        line=line.rstrip('\n')
        m = re.match(r'^(?P<file>[^:]+):(?P<line>\d+): (?P<text>.*) -> translate$', line)
        if not m:
            continue
        filepath = m.group('file')
        lineno = int(m.group('line'))
        text = m.group('text').strip()
        # skip seeders explicitly
        if 'database/seeders' in filepath or filepath.startswith('database/seeders'):
            print('Skipping seeder entry', filepath, lineno, text)
            continue
        # safety: skip if text contains blade expressions or variables or urls
        if any(s in text for s in ['{{', '{!!', '$', '@', 'http://', 'https://', '<button', '<']):
            print('Skipping unsafe text (blade/variable/url/html):', filepath, lineno, text)
            continue
        # open file
        if not os.path.exists(filepath):
            print('File missing, skip:', filepath)
            continue
        with open(filepath, encoding='utf-8') as f:
            lines = f.readlines()
        idx = lineno - 1
        if idx < 0 or idx >= len(lines):
            print('Line number out of range for', filepath, lineno)
            continue
        orig_line = lines[idx]
        if "__('" in orig_line or '__("' in orig_line:
            print('Already contains translation on line, skip:', filepath, lineno)
            continue
        # find pattern >...< where inner trimmed equals text
        pattern = re.compile(r'(>)([^<]{0,400})(<)')
        def repl(m):
            inner = m.group(2)
            if inner.strip() == text:
                key = text.replace("'", "\\'")
                return m.group(1) + "{{ __('" + key + "') }}" + m.group(3)
            return m.group(0)
        new_line, n = pattern.subn(repl, orig_line, count=1)
        if n>0 and new_line != orig_line:
            lines[idx] = new_line
            with open(filepath, 'w', encoding='utf-8') as f:
                f.writelines(lines)
            changed_files.add(filepath)
            if text not in lang:
                lang[text] = text
            print('Replaced in', filepath, 'line', lineno, 'text:', text)
        else:
            print('No match for exact text on line, skipping:', filepath, lineno, text)
# write lang file if changed
if changed_files:
    with open(LANG, 'w', encoding='utf-8') as f:
        json.dump(lang, f, ensure_ascii=False, indent=4)
# git add and commit
if changed_files:
    subprocess.run(['git','add'] + list(changed_files) + [LANG])
    subprocess.run(['git','commit','-m','i18n: wrap translate-tagged text nodes in views (exclude seeders)','-m','Auto-wrapped translate-tagged text nodes in views and updated sv.json; seeders were excluded.'], check=False)
print('Done. changed files:', list(changed_files))
