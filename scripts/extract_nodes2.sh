#!/usr/bin/env bash
set -e
OUT=ALL_TEXT_NODES.md
echo "# All text nodes from Blade/Volt views" > $OUT
echo "Generated: $(date -u)" >> $OUT
echo "" >> $OUT
echo "## Views (resources/views)" >> $OUT
find resources/views -type f -name '*.blade.php' | while read f; do
  perl -nle 'if(/>([^<]{1,400})</){ $s=$1; $t=$s; $t=~s/\s+/ /g; $t=~s/^\s+|\s+$//g; next if $t eq ""; next if $t =~ /{{|}}|__\(|@\w|wire:|href=|src=|http:|https:|^</; next if $t =~ /^\s*$/; print "$ARGV:".($.).": $t" }' "$f"
done | sort -u >> $OUT

echo "" >> $OUT
echo "## Examples & local packages (.examples, local_packages)" >> $OUT
for dir in .examples local_packages; do
  if [ -d "$dir" ]; then
    find "$dir" -type f \( -name '*.php' -o -name '*.blade.php' -o -name '*.html' \) | while read f; do
      perl -nle 'if(/>([^<]{1,400})</){ $s=$1; $t=$s; $t=~s/\s+/ /g; $t=~s/^\s+|\s+$//g; next if $t eq ""; next if $t =~ /{{|}}|__\(|@\w|wire:|href=|src=|http:|https:|^</; next if $t =~ /^\s*$/; print "$ARGV:".($.).": $t" }' "$f"
    done | sort -u >> $OUT
  fi
done

git add $OUT || true
if git diff --staged --name-only | grep -q "$OUT"; then
  git commit -m "i18n: extract all text nodes from Blade/Volt views (regenerated)" -m "Auto-generated ALL_TEXT_NODES.md listing visible text nodes for translation review." || true
fi

echo "Wrote $OUT"
sed -n '1,200p' $OUT
