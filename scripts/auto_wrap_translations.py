#!/usr/bin/env python3
import re, json, sys, os
IN='ALL_TEXT_NODES_TAGGED.md'
LANG='resources/lang/sv.json'
if not os.path.exists(IN):
    print('Missing', IN); sys.exit(1)
# parse entries
entries = {}
with open(IN, encoding='utf-8') as f:
    for line in f:
        line=line.rstrip('\n')
        m = re.match(r'^(resources/views/[^:]+):(\d+):\s+(.*) -> translate$', line)
        if m:
            path = m.group(1)
            ln = int(m.group(2))
            text = m.group(3).strip()
            entries.setdefault(path, []).append((ln, text))
if not entries:
    print('No translate-tagged entries found in', IN)
    sys.exit(0)
# load lang json
if os.path.exists(LANG):
    with open(LANG, encoding='utf-8') as f:
        try:
            lang = json.load(f)
        except Exception as e:
            print('Error loading', LANG, e); lang={}
else:
    lang = {}
changed_files = []
for path, items in entries.items():
    if not os.path.exists(path):
        print('File missing', path); continue
    # read file
    with open(path, encoding='utf-8') as f:
        lines = f.readlines()
    # apply replacements
    modified = False
    for ln, text in items:
        idx = ln-1
        if idx < 0 or idx >= len(lines):
            # skip
            continue
        line = lines[idx]
        # skip if already contains __(
        if "__('" in line or '__("' in line:
            continue
        # build regex to find >...< containing the exact text (allow surrounding whitespace)
        esc = re.escape(text)
        pattern = re.compile(r'(>\s*)' + esc + r'(\s*<)')
        if pattern.search(line):
            new = pattern.sub(r"\1{{ __('
