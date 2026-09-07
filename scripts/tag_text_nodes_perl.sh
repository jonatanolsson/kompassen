#!/usr/bin/env perl
use strict; use warnings; 
my $in = 'ALL_TEXT_NODES.md';
my $out = 'ALL_TEXT_NODES_TAGGED.md';
open my $IN, '<', $in or die "Can't open $in: $!";
open my $OUT, '>', $out or die "Can't open $out: $!";
print $OUT "# Tagged text nodes\n";
print $OUT "Generated: " . scalar(gmtime()) . " UTC\n\n";
print $OUT "## Tagged entries (format: file:line: text -> tag)\n\n";
my @keywords = qw(Save Cancel Create Update Edit Delete "Log in" Register Dashboard Profile Search Home Back Add Remove Upload Close Done Preview Name Description Save\ Settings Generate Report);
my $kw_re = join('|', map { quotemeta($_) } @keywords);
while(<$IN>){
  chomp;
  if(/^(.+):(\d+):\s+(.*)$/){
    my ($file,$ln,$text) = ($1,$2,$3);
    my $tag = 'content';
    if($file =~ /\.examples|local_packages/){ $tag = 'stub'; }
    if($text =~ /\{\{|\{!!|\$|\@|https?:\/\//){ $tag = 'content'; }
    if($tag ne 'stub' && $text =~ /$kw_re/i){ $tag = 'translate'; }
    if($tag eq 'content'){
      # small heuristic: short single/phrase that is alphanumeric -> translate
      if(length($text) <= 40 && $text =~ /^[A-Za-z0-9 \-\'\:]+$/){
        if($text =~ /$kw_re/i){ $tag = 'translate'; }
      }
    }
    print $OUT "$file:$ln: $text -> $tag\n";
  } else {
    print $OUT "$_\n";
  }
}
close $IN; close $OUT;
print `wc -l $out`;
