#!/bin/sh
set -eu

base=${1:-}
if [ -z "$base" ] || ! git cat-file -e "$base^{commit}" 2>/dev/null; then
    echo 'A valid base commit is required for the asset version check.' >&2
    exit 1
fi

assets='wp-content/themes/springapex/assets'
version_file='wp-content/themes/springapex/functions.php'
if git diff --quiet "$base" HEAD -- "$assets"; then
    echo 'asset-version: no asset changes'
    exit 0
fi

old_version=$(git show "$base:$version_file" 2>/dev/null \
    | sed -n "s/^define('SPRINGAPEX_VERSION', '\([^']*\)');/\1/p")
new_version=$(sed -n "s/^define('SPRINGAPEX_VERSION', '\([^']*\)');/\1/p" "$version_file")
if [ -z "$new_version" ] || [ "$new_version" = "$old_version" ]; then
    echo 'Theme assets changed without advancing SPRINGAPEX_VERSION.' >&2
    exit 1
fi

echo "asset-version: $old_version -> $new_version"
