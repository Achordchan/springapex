<?php
/**
 * Brand contact/social single-source migration, with the WordPress options table
 * stubbed closely enough to exercise the compare-and-swap write: values are kept
 * serialized the way wp_options keeps them, and $wpdb->update() only touches the
 * row when option_value still matches what the caller read.
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

/** Raw option rows, option_name => serialized option_value. */
$springapex_test_rows = [];
/** Theme mods, name => value. */
$springapex_test_theme_mods = [];
/** Set to true to make every content-overrides write fail, as a dying request would. */
$springapex_test_block_overrides_write = false;
/** Fired once, right after the migration reads the overrides row. */
$springapex_test_on_overrides_read = null;

function maybe_serialize(mixed $value): string
{
    return is_array($value) || is_object($value) ? serialize($value) : (string) $value;
}

function maybe_unserialize(string $value): mixed
{
    if (preg_match('/^[aOsbid]:/', $value) !== 1) {
        return $value;
    }
    $restored = @unserialize($value);
    return $restored === false && $value !== serialize(false) ? $value : $restored;
}

function get_option(string $option, mixed $default_value = false): mixed
{
    global $springapex_test_rows, $springapex_test_on_overrides_read;

    $value = array_key_exists($option, $springapex_test_rows)
        ? maybe_unserialize($springapex_test_rows[$option])
        : $default_value;

    if ($option === 'springapex_content_overrides' && $springapex_test_on_overrides_read !== null) {
        $concurrent = $springapex_test_on_overrides_read;
        $springapex_test_on_overrides_read = null;   // once, or it recurses
        $concurrent();
    }

    return $value;
}

function update_option(string $option, mixed $value, bool $autoload = false): bool
{
    global $springapex_test_rows, $springapex_test_block_overrides_write;

    if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
        return false;
    }
    $serialized = maybe_serialize($value);
    if (($springapex_test_rows[$option] ?? null) === $serialized) {
        return false;
    }
    $springapex_test_rows[$option] = $serialized;
    return true;
}

function add_option(string $option, mixed $value, string $deprecated = '', bool $autoload = false): bool
{
    global $springapex_test_rows, $springapex_test_block_overrides_write;

    if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
        return false;
    }
    if (array_key_exists($option, $springapex_test_rows)) {
        return false;
    }
    $springapex_test_rows[$option] = maybe_serialize($value);
    return true;
}

function delete_option(string $option): bool
{
    global $springapex_test_rows;
    unset($springapex_test_rows[$option]);
    return true;
}

function wp_cache_delete(string $key, string $group = ''): bool
{
    return true;
}

/** Stands in for $wpdb, with update() honouring the WHERE clause the CAS relies on. */
final class Springapex_Test_WPDB
{
    public string $options = 'wp_options';

    /**
     * @param array<string, string> $data
     * @param array<string, string> $where
     * @param array<int, string> $format
     * @param array<int, string> $where_format
     */
    public function update(string $table, array $data, array $where, array $format = [], array $where_format = []): int|false
    {
        global $springapex_test_rows, $springapex_test_block_overrides_write;

        $option = $where['option_name'] ?? '';
        if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
            return false;
        }
        // The row only moves when nobody rewrote it since the caller read it.
        if (($springapex_test_rows[$option] ?? null) !== ($where['option_value'] ?? null)) {
            return 0;
        }
        $springapex_test_rows[$option] = $data['option_value'];
        return 1;
    }
}

$wpdb = new Springapex_Test_WPDB();

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

require __DIR__ . '/../inc/locks.php';
require __DIR__ . '/../inc/content-overrides.php';

function springapex_test_brand(string $key): mixed
{
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    return $overrides['brand'][$key] ?? null;
}

/** @param array<string, mixed> $brand */
function springapex_test_set_overrides(array $brand): void
{
    global $springapex_test_rows;
    $springapex_test_rows[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] = maybe_serialize(['brand' => $brand]);
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

springapex_test_set_overrides([
    'facebook' => 'https://www.facebook.com/from-admin/',
    'x' => '',
    'hours' => 'Mon – Fri',
]);

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
$springapex_test_rows_before = $springapex_test_rows[SPRINGAPEX_CONTENT_OVERRIDES_OPTION];
springapex_migrate_brand_contact_source();
springapex_test_assert($springapex_test_rows[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] === $springapex_test_rows_before, 'A re-entrant run rewrote identical overrides.');
springapex_test_assert(get_theme_mod('springapex_x', '') === '', 'A re-entrant run left the theme mod behind.');
springapex_test_assert(get_option('springapex_brand_contact_source_version', null) === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION, 'A re-entrant run did not record completion.');

// The interleaving the compare-and-swap exists for: an editor saves the overrides
// screen *after* the migration read the row and *before* it writes. Reading again
// first is not enough — the stale snapshot has to be rejected by the write itself.
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_tiktok', 'https://www.tiktok.com/@legacy');
$springapex_test_on_overrides_read = static function (): void {
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    $overrides['brand']['hours'] = 'Saved by an editor mid-migration';
    update_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $overrides);
};
springapex_migrate_brand_contact_source();
springapex_test_assert($springapex_test_on_overrides_read === null, 'The concurrent save never ran; the interleaving was not exercised.');
springapex_test_assert(springapex_test_brand('hours') === 'Saved by an editor mid-migration', 'A stale snapshot overwrote a concurrent save.');
springapex_test_assert(springapex_test_brand('tiktok') === 'https://www.tiktok.com/@legacy', 'Legacy TikTok URL was not migrated.');
springapex_test_assert(get_option('springapex_brand_contact_source_version', null) === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION, 'The migration did not finish after retrying.');

// Junk left by an old customizer must not reach the front end as a live link.
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_instagram', 'javascript:alert(1)');
set_theme_mod('springapex_email', 'not-an-email');
springapex_migrate_brand_contact_source();
springapex_test_assert(springapex_test_brand('instagram') === null, 'A non-http URL was migrated.');
springapex_test_assert(springapex_test_brand('email') === null, 'An invalid email was migrated.');

echo "brand-contact-migration: failed write, completion, re-entry, concurrent save and junk values ok\n";
