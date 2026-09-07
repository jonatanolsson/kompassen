#!/usr/bin/env bash
set -e
OUT=I18N_TAGGED_REPORT.md
echo "# I18N Tagged Report" > $OUT
echo "Generated: $(date -u)" >> $OUT
echo "" >> $OUT
echo "## UI candidates (views) - suggested: translate" >> $OUT
# Extract visible text nodes from views
find resources/views -type f -name '*.blade.php' | while read f; do
  perl -nle 'if(/>([^<]{3,200})</){ $s=$1; $t=$s; $t=~s/\s+/ /g; $t=~s/^\s+|\s+$//g; next if $t=~ /__\(|\{\{|\@|http|href|src|\$|\{|\}|\(|\)|\[|\]|:|%|\\d/; next unless $t=~/[A-Za-zÅÄÖåäö]/; next if length($t)<3 || length($t)>200; print "$ARGV:".($.).": $t" }' "$f"
done | sort -u >> $OUT

echo "" >> $OUT
echo "## Content candidates (seeders, fixtures) - suggested: content (do not translate)" >> $OUT
# List seeders lines containing common english keys
grep -RIn --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=storage -n "title_en\|name_en\|description_en\|url\s*=>\|\'example\'\|example" database/seeders >> $OUT || true

echo "" >> $OUT
echo "## Stub/Examples (local_packages, .examples) - suggested: stub review" >> $OUT
grep -RIn --exclude-dir=vendor --exclude-dir=node_modules --exclude-dir=storage -n "Save profile\|Search...\|Dashboard\|Example\|placeholder=\"Search...\"" .examples local_packages >> $OUT || true
