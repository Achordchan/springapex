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

is_numeric_version() {
    case $1 in
        ''|*[!0-9.]*|.*|*.|*..*) return 1 ;;
        *.*) return 0 ;;
        *) return 1 ;;
    esac
}

if ! is_numeric_version "$old_version" || ! is_numeric_version "$new_version"; then
    echo 'Theme assets changed with an invalid SPRINGAPEX_VERSION.' >&2
    exit 1
fi

if ! awk -v old="$old_version" -v new="$new_version" '
    BEGIN {
        old_count = split(old, old_parts, ".")
        new_count = split(new, new_parts, ".")
        count = old_count > new_count ? old_count : new_count

        for (part_index = 1; part_index <= count; part_index++) {
            old_part = part_index <= old_count ? old_parts[part_index] : "0"
            new_part = part_index <= new_count ? new_parts[part_index] : "0"
            sub(/^0+/, "", old_part)
            sub(/^0+/, "", new_part)
            if (old_part == "") old_part = "0"
            if (new_part == "") new_part = "0"

            if (length(new_part) != length(old_part)) {
                exit length(new_part) > length(old_part) ? 0 : 1
            }
            if (("x" new_part) != ("x" old_part)) {
                exit ("x" new_part) > ("x" old_part) ? 0 : 1
            }
        }

        exit 1
    }
'; then
    echo 'Theme assets changed without advancing SPRINGAPEX_VERSION.' >&2
    exit 1
fi

echo "asset-version: $old_version -> $new_version"
