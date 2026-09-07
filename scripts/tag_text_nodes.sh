#!/usr/bin/env bash
set -e
IN=ALL_TEXT_NODES.md
OUT=ALL_TEXT_NODES_TAGGED.md
keywords='\b(Save|Cancel|Create|Update|Edit|Delete|Log in|Register|Dashboard|Profile|Search|Home|Back|Add|Remove|Upload|Cancel|Close|Done|Save Settings|Generate Report|Generate|Back|Preview|Name|Description)\b'
rm -f $OUT
echo "# Tagged text nodes" > $OUT
echo "Generated: $(date -u)" >> $OUT
echo "" >> $OUT
if [ ! -f "$IN" ]; then echo "Missing $IN"; exit 0; fi
awk '/^##/ {print; next} /^[#]/ {print; next} NF==0 {print; next} {print}' $IN > /tmp/allnodes.tmp
# Process lines that look like file:line: text
while IFS= read -r line; do
  if [[ "$line" =~ ^(.+):([0-9]+):\ (.*)$ ]]; then
    file="${BASH_REMATCH[1]}"
    lineno="${BASH_REMATCH[2]}"
    text="${BASH_REMATCH[3]}"
    tag="content"
    # stub if file path contains .examples or local_packages
    if [[ "$file" =~ \.examples|local_packages ]]; then
      tag="stub"
    fi
    # content if blade variable or blade tags
    if [[ "$text" =~ \{\{|\{!!|\$|\@|http|https|<\/?[a-zA-Z] ]]; then
      tag="content"
    fi
    # translate if matches keyword and not stub
    if [[ "$tag" != "stub" && "$text" =~ $keywords ]]; then
      tag="translate"
    fi
    # short uppercase words like Dashboard, Save etc -> translate
    if [[ "$tag" == "content" && "$text" =~ ^[A-Za-z0-9 \-]{1,40}$ && ("$text" =~ $keywords) ]]; then
      tag="translate"
    fi
    echo "$file:$lineno: $text -> $tag" >> $OUT
  else
    echo "$line" >> $OUT
  fi
done < /tmp/allnodes.tmp
# counts
echo "" >> $OUT
echo "## Counts" >> $OUT
grep -c "-> translate" $OUT || true >> $OUT
grep -c "-> content" $OUT || true >> $OUT
grep -c "-> stub" $OUT || true >> $OUT

git add $OUT || true
if git diff --staged --name-only | grep -q "$OUT"; then
  git commit -m "i18n: tag text nodes (translate/content/stub)" -m "Auto-tagged ALL_TEXT_NODES.md entries for review before translation." || true
fi
sed -n '1,200p' $OUT
