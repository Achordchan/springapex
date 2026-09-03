<?php
/** Brand contact/social single-source migration, with WordPress options and theme mods stubbed. */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

/** @var array<string, mixed> */
$springapex_test_options = [];
/** @var array<string, mixed> */
$springapex_test_theme_mods = [];
/** Set to true to make every content-overrides write fail, as a dying request would. */
$springapex_test_block_overrides_write = false;

function get_option(string $option, mixed $default_value = false): mixed
{
    global $springapex_test_options;
    return $springapex_test_options[$option] ?? $default_value;
}

function update_option(string $option, mixed $value, bool $autoload = false): bool
{
    global $springapex_test_options, $springapex_test_block_overrides_write;
    if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
        return false;
    }
    if (($springapex_test_options[$option] ?? null) === $value) {
        return false;
    }
    $springapex_test_options[$option] = $value;
    return true;
}

function delete_option(string $option): bool
{
    global $springapex_test_options;
    unset($springapex_test_options[$option]);
    return true;
}

function get_theme_mod(string $name, mixed $default_value = false): mixed
{
    global $springapex_test_theme_mods;
    return $springapex_test_theme_mods[$name] ?? $default_value;
}

function set_theme_mod(string $name, mixed $value): void
{
    global $springapex_test_theme_mods;
    $springapex_test_theme_mods[$name] = $value;
}

function remove_theme_mod(string $name): void
{
    global $springapex_test_theme_mods;
    unset($springapex_test_theme_mods[$name]);
}

function add_action(string $hook, callable|string $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function add_filter(string $hook, callable|string $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function do_action(string $hook, mixed ...$args): void
{
}

function sanitize_text_field(string $value): string
{
    return trim(strip_tags($value));
}

function sanitize_textarea_field(string $value): string
{
    return trim(strip_tags($value));
}

function sanitize_email(string $value): string
{
    return (string) filter_var(trim($value), FILTER_SANITIZE_EMAIL);
}

function is_email(string $value): bool
{
    return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
}

/** @param array<int, string> $protocols */
function esc_url_raw(string $url, array $protocols = []): string
{
    $url = trim($url);
    return preg_match('#^https?://#i', $url) === 1 ? $url : '';
}

require __DIR__ . '/../inc/content-overrides.php';

function springapex_test_brand(string $key): mixed
{
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    return $overrides['brand'][$key] ?? null;
}

/** Legacy customizer values left over on a production site. */
function springapex_test_seed_legacy(): void
{
    set_theme_mod('springapex_x', 'https://x.com/legacy_handle');
    set_theme_mod('springapex_facebook', 'https://www.facebook.com/legacy-page/');
    set_theme_mod('springapex_phone', '+86 000 0000 0000');
    set_theme_mod('springapex_inquiry_email', 'sales@example.com');
    delete_option('springapex_brand_contact_source_version');
}

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// The content overrides a site already has, including one key the migration also carries.
$springapex_test_options['springapex_content_overrides'] = [
    'brand' => ['facebook' => 'https://www.facebook.com/from-admin/', 'x' => '', 'hours' => 'Mon – Fri'],
];

// A write that never lands must not cost the source values: deleting them first
// would drop the operator's contact details for good, and the next request would
// find nothing to move and mark the migration done.
springapex_test_seed_legacy();
$springapex_test_block_overrides_write = true;
springapex_migrate_brand_contact_source();
$springapex_test_block_overrides_write = false;
springapex_test_assert(get_theme_mod('springapex_x', '') === 'https://x.com/legacy_handle', 'A failed write deleted the source theme mod.');
springapex_test_assert(get_theme_mod('springapex_phone', '') === '+86 000 0000 0000', 'A failed write deleted the source theme mod.');
springapex_test_assert(springapex_test_brand('x') === '', 'Overrides changed even though the write failed.');
springapex_test_assert(get_option('springapex_brand_contact_source_version', null) === null, 'A failed migration was marked complete.');

// The same run, now able to write: theme mods win over the overrides table the way
// they did before this migration existed, so visitors keep seeing the same values.
springapex_migrate_brand_contact_source();
springapex_test_assert(springapex_test_brand('x') === 'https://x.com/legacy_handle', 'Legacy X URL was not migrated.');
springapex_test_assert(springapex_test_brand('facebook') === 'https://www.facebook.com/legacy-page/', 'Legacy Facebook URL did not win over the overrides table.');
springapex_test_assert(springapex_test_brand('phone') === '+86 000 0000 0000', 'Legacy phone was not migrated.');
springapex_test_assert(springapex_test_brand('hours') === 'Mon – Fri', 'Migration clobbered an unrelated overrides key.');
springapex_test_assert(get_theme_mod('springapex_x', '') === '', 'Migrated theme mod was left behind.');
springapex_test_assert(get_theme_mod('springapex_facebook', '') === '', 'Migrated theme mod was left behind.');
springapex_test_assert(get_theme_mod('springapex_inquiry_email', '') === 'sales@example.com', 'The form settings theme mod was touched.');
springapex_test_assert(get_option('springapex_brand_contact_source_version', null) === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION, 'A completed migration was not recorded.');

// Re-entrant: a request that died between writing and clearing leaves the version
// unset. Running again must finish the job without rewriting identical values.
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_x', 'https://x.com/legacy_handle');
$springapex_test_options_before = $springapex_test_options['springapex_content_overrides'];
springapex_migrate_brand_contact_source();
springapex_test_assert($springapex_test_options['springapex_content_overrides'] === $springapex_test_options_before, 'A re-entrant run rewrote identical overrides.');
springapex_test_assert(get_theme_mod('springapex_x', '') === '', 'A re-entrant run left the theme mod behind.');
springapex_test_assert(get_option('springapex_brand_contact_source_version', null) === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION, 'A re-entrant run did not record completion.');

// Concurrent requests: the overrides table is read right before the write, so an
// edit that landed in between (another migrating request, or an operator saving
// the admin screen) is not overwritten with a stale snapshot.
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_tiktok', 'https://www.tiktok.com/@legacy');
$springapex_test_options['springapex_content_overrides']['brand']['hours'] = 'Edited while migrating';
springapex_migrate_brand_contact_source();
springapex_test_assert(springapex_test_brand('hours') === 'Edited while migrating', 'A stale snapshot overwrote a concurrent edit.');
springapex_test_assert(springapex_test_brand('tiktok') === 'https://www.tiktok.com/@legacy', 'Legacy TikTok URL was not migrated.');

// Junk left by an old customizer must not reach the front end as a live link.
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_instagram', 'javascript:alert(1)');
set_theme_mod('springapex_email', 'not-an-email');
springapex_migrate_brand_contact_source();
springapex_test_assert(springapex_test_brand('instagram') === null, 'A non-http URL was migrated.');
springapex_test_assert(springapex_test_brand('email') === null, 'An invalid email was migrated.');

echo "brand-contact-migration: failed write, completion, re-entry, concurrent edit and junk values ok\n";
