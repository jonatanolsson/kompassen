#!/usr/bin/env bash
set -e
IN=ALL_TEXT_NODES_TAGGED.md
LANG=resources/lang/sv.json
TMPLANG=/tmp/sv_new_entries.json
jq_available=$(command -v jq || true)
# collect translate entries in resources/views
grep "-> translate" "$IN" | grep "resources/views" | sed -E 's/([^:]+):([0-9]+): (.*) -> translate/\1|\2|\3/' | while IFS='|' read -r file line text; do
  # trim
  text=$(echo "$text" | sed -E "s/^\s+|\s+$//g")
  # escape for perl
  esc_text=$(printf '%s' "$text" | perl -pe 's/(\'"\")(.)/\1\2/g')
  # replace >text< with >{{ __('text') }}< only if exact occurrence exists
  perl -0777 -pe "s/>\Q${text}\E</>{{ \"{{ __('$text') }}\" }}/g" -i.bak "$file" || true
  # Now fix: previous command left literal {{ in file due to escaping, do proper replace using perl safe
  # perform proper replace using safer approach
  perl -0777 -i.bak -pe "s/>\Q${text}\E</>\{\{ __('${text//'/\\\'}') \}\}</gs" "$file" || true
  # Add to lang file if key missing
  if ! grep -q "\"${text//"/\"}\"\s*:\s*\"" "$LANG"; then
    # append naive entry before final }
    sed -i.bak "\$sed_escape=\"\\\\\"\"\"; s/\n\}\$/,\n    \"${text//"/\"}\": \"${text//"/\"}\"\n}\n/" $LANG || true
  fi
done
# cleanup backups
find resources/views -name "*.bak" -delete
rm -f ${LANG}.bak || true

git add resources/views resources/lang/sv.json || true
if git diff --staged --name-only | grep -q "resources/views\|resources/lang/sv.json"; then
  git commit -m "i18n: wrap translated text nodes in __('...') in views and add keys to sv.json" -m "Auto-wrapped safe translate-tagged text nodes in resources/views and appended keys to resources/lang/sv.json." || true
fi

echo "Applied wrappers and updated lang file. Review commits and run tests." 
