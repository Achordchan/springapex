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

/**
 * Replace the retired public brand name without touching stable technical
 * identifiers such as option names, theme paths, domains, email addresses or
 * social handles.
 */
function springapex_replace_public_brand(mixed $value): mixed
{
    if (is_string($value)) {
        return str_replace(
            ['ApexSpring', 'APEXSPRING', 'Apexspring', 'SpringApex'],
            ['NorenSpring', 'NORENSPRING', 'NorenSpring', 'NorenSpring'],
            $value
        );
    }

    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        $value[$key] = springapex_replace_public_brand($item);
    }

    return $value;
}

/**
 * Persist the public-brand migration once so existing wp-admin values and the
 * front end agree immediately after deployment. Exact legacy names only are
 * replaced; legal company names and technical identifiers are left intact.
 */
function springapex_migrate_public_brand_options(): void
{
    if ((string) get_option('springapex_public_brand_version', '') === '1') {
        return;
    }

    $success = true;
    foreach (['blogname', 'blogdescription'] as $option_name) {
        $current = get_option($option_name, '');
        $updated = springapex_replace_public_brand($current);
        if ($updated !== $current) {
            update_option($option_name, $updated, false);
            $success = get_option($option_name, '') === $updated && $success;
        }
    }

    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    if (is_array($overrides)) {
        $updated_overrides = springapex_replace_public_brand($overrides);
        if ($updated_overrides !== $overrides) {
            springapex_content_store_overrides($updated_overrides);
            $success = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []) === $updated_overrides && $success;
        }
    }

    $custom_logo_id = function_exists('get_theme_mod') ? (int) get_theme_mod('custom_logo', 0) : 0;
    if ($custom_logo_id > 0 && function_exists('get_post_field') && function_exists('get_post_meta')) {
        $logo_identity = implode(' ', [
            (string) get_post_field('post_title', $custom_logo_id, 'raw'),
            (string) get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true),
        ]);
        if (springapex_replace_public_brand($logo_identity) !== $logo_identity) {
            remove_theme_mod('custom_logo');
            $success = (int) get_theme_mod('custom_logo', 0) === 0 && $success;
        }
    }

    if ($success) {
        update_option('springapex_public_brand_version', '1', false);
    }
}
add_action('init', 'springapex_migrate_public_brand_options', 1);

// Keep old database-authored post content from leaking the retired brand while
// the versioned seed migration updates untouched records in the background.
add_filter('the_title', 'springapex_replace_public_brand', 20);
add_filter('the_content', 'springapex_replace_public_brand', 20);
add_filter('get_the_excerpt', 'springapex_replace_public_brand', 20);

add_action('init', static function (): void {
    add_filter('option_blogname', 'springapex_replace_public_brand', 20);
    add_filter('option_blogdescription', 'springapex_replace_public_brand', 20);
}, 2);

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
        return springapex_replace_public_brand($data);
    }
    return springapex_replace_public_brand(springapex_content_merge($data, $overrides));
}
add_filter('springapex_content', 'springapex_content_apply_overrides');

function springapex_content_store_overrides(array $overrides): void
{
    $overrides = springapex_replace_public_brand($overrides);

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
