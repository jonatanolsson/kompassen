#!/usr/bin/env bash
set -e
OUT=ALL_TEXT_NODES.md
TAG_OUT=ALL_TEXT_NODES_TAGGED.md
echo "# All text nodes from Blade/Volt views" > $OUT
echo "Generated: $(date -u)" >> $OUT
echo "" >> $OUT
echo "## Views (resources/views)" >> $OUT
# Extract with perl printing filename
find resources/views -type f -name '*.blade.php' | while read f; do
  perl -nle 'if(/>([^<]{1,400})</){ $s=$1; $t=$s; $t=~s/\s+/ /g; $t=~s/^\s+|\s+$//g; next if $t eq ""; next if $t =~ /{{|}}|__\(|@\w|wire:|href=|src=|http:|https:|^</; next if $t =~ /^\s*$/; print "$ARGV:$.: $t" }' "$f"
done | sort -u >> $OUT

# Examples & local packages
for dir in .examples local_packages; do
  if [ -d "$dir" ]; then
    find "$dir" -type f \( -name '*.php' -o -name '*.blade.php' -o -name '*.html' \) | while read f; do
      perl -nle 'if(/>([^<]{1,400})</){ $s=$1; $t=$s; $t=~s/\s+/ /g; $t=~s/^\s+|\s+$//g; next if $t eq ""; next if $t =~ /{{|}}|__\(|@\w|wire:|href=|src=|http:|https:|^</; next if $t =~ /^\s*$/; print "$ARGV:$.: $t" }' "$f"
    done | sort -u >> $OUT
  fi
done

# Now tag using perl
perl -e '
  my $in = shift; my $out = shift; open my $IN, "<", $in or die $!; open my $OUT, ">", $out or die $!; print $OUT "# Tagged text nodes\n"; print $OUT "Generated: " . scalar(gmtime()) . " UTC\n\n"; print $OUT "## Tagged entries (format: file:line: text -> tag)\n\n"; 
  my @keywords = qw(Save Cancel Create Update Edit Delete "Log in" Register Dashboard Profile Search Home Back Add Remove Upload Close Done Preview Name Description Save_Settings Generate_Report); 
  my $kw_re = join('|', map { quotemeta($_) } @keywords);
  while(<$IN>){ chomp; if(/^(.+):(\d+):\s+(.*)$/){ my ($file,$ln,$text)=($1,$2,$3); my $tag='content'; if($file =~ /\.examples|local_packages/){ $tag='stub'; } if($text =~ /{{|{!!|\$|@|https?:\/\//){ $tag='content'; } if($tag ne 'stub' && $text =~ /$kw_re/i){ $tag='translate'; } if($tag eq 'content'){ if(length($text) <= 40 && $text =~ /^[A-Za-z0-9 \-\'\:\.\,\(\)]+$/){ if($text =~ /$kw_re/i){ $tag='translate'; } } } print $OUT "$file:$ln: $text -> $tag\n"; } else { print $OUT "$_\n"; } } close $IN; close $OUT; ' $OUT $TAG_OUT

git add $OUT $TAG_OUT || true
if git diff --staged --name-only | grep -q "$OUT\|$TAG_OUT"; then
  git commit -m "i18n: extract and tag all text nodes from views" -m "Generated ALL_TEXT_NODES.md and ALL_TEXT_NODES_TAGGED.md for translation review." || true
fi

echo "Generated and committed $OUT and $TAG_OUT"

sed -n '1,200p' $TAG_OUT
