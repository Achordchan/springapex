<?php
/**
 * Persisted content overrides shared by the front end and wp-admin.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_CONTENT_OVERRIDES_OPTION = 'springapex_content_overrides';
const SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT = 204800;

function springapex_content_is_list(array $value): bool
{
    $index = 0;
    foreach ($value as $key => $_item) {
        if ($key !== $index) {
            return false;
        }
        $index++;
    }
    return true;
}

/**
 * Lists replace as a whole; associative arrays merge recursively.
 */
function springapex_content_merge(mixed $base, mixed $override): mixed
{
    if (!is_array($base) || !is_array($override)) {
        return $override;
    }

    if (springapex_content_is_list($override)) {
        return array_values($override);
    }
    if (springapex_content_is_list($base)) {
        return $override;
    }

    $merged = $base;
    foreach ($override as $key => $value) {
        $merged[$key] = array_key_exists($key, $merged)
            ? springapex_content_merge($merged[$key], $value)
            : $value;
    }
    return $merged;
}

function springapex_content_apply_overrides(array $data): array
{
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    // An empty option means "nothing overridden". It must not reach the merge:
    // an empty array counts as a list there, and a list override replaces
    // wholesale — which would blank the entire content tree.
    if (!is_array($overrides) || $overrides === []) {
        return $data;
    }
    return springapex_content_merge($data, $overrides);
}
add_filter('springapex_content', 'springapex_content_apply_overrides');

function springapex_content_store_overrides(array $overrides): void
{
    if ($overrides === []) {
        delete_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION);
        return;
    }

    $autoload = strlen(serialize($overrides)) <= SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT;
    update_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $overrides, $autoload);
}

/**
 * update_option() already invalidates the options cache, so there is nothing
 * to flush here. Deliberately no wp_cache_flush(): with a persistent object
 * cache that would drop every site visitor's cache on each text edit.
 * Page-cache plugins can hook the action below to purge precisely.
 */
function springapex_content_flush_caches(string $screen): void
{
    do_action('springapex_content_cache_flushed', $screen);
}
