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
/** autoload column per option row. */
$springapex_test_autoload = [];
/** Fired once, right after the theme mods row is read. */
$springapex_test_on_theme_mods_read = null;

const SPRINGAPEX_TEST_THEME_MODS_OPTION = 'theme_mods_springapex';
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
    global $springapex_test_rows, $springapex_test_on_overrides_read, $springapex_test_on_theme_mods_read;

    if ($option === 'stylesheet') {
        return 'springapex';
    }

    $value = array_key_exists($option, $springapex_test_rows)
        ? maybe_unserialize($springapex_test_rows[$option])
        : $default_value;

    if ($option === 'springapex_content_overrides' && $springapex_test_on_overrides_read !== null) {
        $concurrent = $springapex_test_on_overrides_read;
        $springapex_test_on_overrides_read = null;   // once, or it recurses
        $concurrent();
    }

    if ($option === SPRINGAPEX_TEST_THEME_MODS_OPTION && $springapex_test_on_theme_mods_read !== null) {
        $concurrent = $springapex_test_on_theme_mods_read;
        $springapex_test_on_theme_mods_read = null;
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

/**
 * Stands in for $wpdb. prepare() just carries the arguments through, and query()
 * applies the two statements the CAS helpers issue, comparing option_value
 * byte-for-byte the way CAST(... AS BINARY) does in MySQL — the column's own
 * collation is case-insensitive, which is exactly what the SQL has to override.
 */
final class Springapex_Test_WPDB
{
    public string $options = 'wp_options';

    /** @return array{sql: string, args: array<int, mixed>} */
    public function prepare(string $query, mixed ...$args): array
    {
        return ['sql' => $query, 'args' => $args];
    }

    /** @param array{sql: string, args: array<int, mixed>} $prepared */
    public function get_var(array $prepared): ?string
    {
        global $springapex_test_rows;

        // Only the row-existence probe uses get_var().
        return (string) (int) array_key_exists($prepared['args'][0], $springapex_test_rows);
    }

    /** @param array{sql: string, args: array<int, mixed>} $prepared */
    public function query(array $prepared): int|false
    {
        global $springapex_test_rows, $springapex_test_autoload, $springapex_test_block_overrides_write;

        $sql = $prepared['sql'];

        if (str_starts_with($sql, 'INSERT IGNORE')) {
            [$option, $value, $autoload] = $prepared['args'];
            if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
                return false;
            }
            // The unique index on option_name is what makes this atomic: an
            // existing row is left exactly as it is.
            if (array_key_exists($option, $springapex_test_rows)) {
                return 0;
            }
            $springapex_test_rows[$option] = $value;
            $springapex_test_autoload[$option] = $autoload;
            return 1;
        }

        $is_update = str_starts_with($sql, 'UPDATE');
        $sets_autoload = $is_update && str_contains($sql, 'autoload = %s');

        if ($is_update) {
            [$option, $expected] = $sets_autoload
                ? [$prepared['args'][2], $prepared['args'][3]]
                : [$prepared['args'][1], $prepared['args'][2]];
        } else {
            [$option, $expected] = [$prepared['args'][0], $prepared['args'][1]];
        }

        if ($springapex_test_block_overrides_write && $option === 'springapex_content_overrides') {
            return false;
        }
        // The row only moves when nobody rewrote it since the caller read it.
        if (($springapex_test_rows[$option] ?? null) !== $expected) {
            return 0;
        }

        if ($is_update) {
            $springapex_test_rows[$option] = $prepared['args'][0];
            if ($sets_autoload) {
                $springapex_test_autoload[$option] = $prepared['args'][1];
            }
        } else {
            unset($springapex_test_rows[$option], $springapex_test_autoload[$option]);
        }
        return 1;
    }
}

$wpdb = new Springapex_Test_WPDB();

/**
 * Theme mods live in one option row, which is exactly why clearing them one by
 * one is a read-modify-write over everything the "表单设置" screen stores too.
 */
function springapex_test_theme_mods(): array
{
    $mods = get_option(SPRINGAPEX_TEST_THEME_MODS_OPTION, []);
    return is_array($mods) ? $mods : [];
}

function get_theme_mod(string $name, mixed $default_value = false): mixed
{
    return springapex_test_theme_mods()[$name] ?? $default_value;
}

function set_theme_mod(string $name, mixed $value): void
{
    $mods = springapex_test_theme_mods();
    $mods[$name] = $value;
    update_option(SPRINGAPEX_TEST_THEME_MODS_OPTION, $mods);
}

function remove_theme_mod(string $name): void
{
    $mods = springapex_test_theme_mods();
    unset($mods[$name]);
    update_option(SPRINGAPEX_TEST_THEME_MODS_OPTION, $mods);
}

function wp_determine_option_autoload_value(string $option, mixed $value, string $serialized, ?bool $autoload): string
{
    return $autoload ? 'on' : 'off';
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

/** Only the brand path is exercised here; helpers.php reads it through springapex_get(). */
function springapex_get(string $path, mixed $default_value = null): mixed
{
    if ($path !== 'brand') {
        return $default_value;
    }
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    return is_array($overrides) && isset($overrides['brand']) ? $overrides['brand'] : $default_value;
}

require __DIR__ . '/../inc/locks.php';
require __DIR__ . '/../inc/content-overrides.php';
require __DIR__ . '/../inc/helpers.php';

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

// While the migration keeps failing, the theme mods are still what visitors were
// being served, so the front end has to keep falling back to them instead of
// rendering an empty phone number and a footer with no links.
$springapex_test_failing_brand = springapex_brand();
springapex_test_assert(($springapex_test_failing_brand['x'] ?? null) === 'https://x.com/legacy_handle', 'The front end stopped falling back before the migration committed.');
springapex_test_assert(($springapex_test_failing_brand['phone'] ?? null) === '+86 000 0000 0000', 'The front end stopped falling back before the migration committed.');

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

// Once it has committed, the fallback is off for good: an operator clearing a
// social link in wp-admin must not be overruled by a leftover theme mod again.
set_theme_mod('springapex_tiktok', 'https://www.tiktok.com/@stale');
$springapex_test_done_brand = springapex_brand();
springapex_test_assert(($springapex_test_done_brand['tiktok'] ?? null) === null, 'A leftover theme mod still overrules the overrides table after the migration.');
remove_theme_mod('springapex_tiktok');

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

// An admin save is a read-modify-write with a form round trip in the middle, so it
// goes through the same compare-and-swap. A save that read the row before the
// migration committed must not put its stale snapshot back on top afterwards.
springapex_test_set_overrides(['facebook' => 'https://www.facebook.com/from-admin/', 'hours' => 'Mon – Fri']);
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_x', 'https://x.com/legacy_handle');
$springapex_test_on_overrides_read = static function (): void {
    springapex_migrate_brand_contact_source();   // the migration commits mid-save
};
$springapex_test_saved = springapex_content_update_overrides(
    static function (array $overrides): array {
        // What the operator submitted from the "公司信息" screen.
        $overrides['brand'] = springapex_content_merge($overrides['brand'] ?? [], ['hours' => 'Mon – Sat']);
        return $overrides;
    }
);
springapex_test_assert($springapex_test_saved, 'The admin save gave up instead of retrying.');
springapex_test_assert(springapex_test_brand('hours') === 'Mon – Sat', 'The admin save did not land.');
springapex_test_assert(springapex_test_brand('x') === 'https://x.com/legacy_handle', 'A stale admin save wiped out the values the migration had just committed.');

// A migrating request that stalls must not resume and reapply its theme-mod
// snapshot: another request can finish the migration while it waits, and the
// operator can clear one of those links right after. The snapshot is stale from
// that moment on, so the retry has to give up rather than merge it back in.
springapex_test_set_overrides(['x' => '', 'hours' => 'Mon – Fri']);
delete_option('springapex_brand_contact_source_version');
set_theme_mod('springapex_x', 'https://x.com/legacy_handle');
$springapex_test_on_overrides_read = static function (): void {
    // Another request migrates the link in, and an operator then clears it and
    // edits something else — so this row no longer matches the stalled snapshot.
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    $overrides['brand']['x'] = '';
    $overrides['brand']['hours'] = 'Sat only';
    update_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $overrides);
    remove_theme_mod('springapex_x');
    update_option('springapex_brand_contact_source_version', SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION);
};
springapex_migrate_brand_contact_source();
springapex_test_assert($springapex_test_on_overrides_read === null, 'The competing migration never ran; the interleaving was not exercised.');
springapex_test_assert(springapex_test_brand('hours') === 'Sat only', 'A stale migration retry rolled back a concurrent edit.');
springapex_test_assert(springapex_test_brand('x') === '', 'A stale migration retry restored a link the operator had cleared.');

// Creating the row is a race too: another request can insert it between this
// request reading "no row" and writing. The insert must lose that race instead
// of overwriting what the other one just stored.
delete_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION);
$springapex_test_on_overrides_read = static function (): void {
    global $springapex_test_rows;
    $springapex_test_rows[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] = maybe_serialize(['brand' => ['hours' => 'Created by another request']]);
};
$springapex_test_created = springapex_content_update_overrides(
    static function (array $overrides): array {
        $overrides['brand']['phone'] = '+86 100 0000 0000';
        return $overrides;
    }
);
springapex_test_assert($springapex_test_created, 'Creating the overrides row gave up instead of retrying.');
springapex_test_assert(springapex_test_brand('hours') === 'Created by another request', 'The insert overwrote a row another request had just created.');
springapex_test_assert(springapex_test_brand('phone') === '+86 100 0000 0000', 'The retry did not reapply this request own change.');

// Theme mods all live in one option row, and the "表单设置" screen keeps the
// inquiry recipient there too. Clearing the legacy keys is therefore a
// read-modify-write over that screen's settings as well: a save landing between
// the read and the write must survive. (Driven directly rather than through the
// whole migration, so the concurrent save lands in that exact window.)
delete_option(SPRINGAPEX_TEST_THEME_MODS_OPTION);
set_theme_mod('springapex_x', 'https://x.com/legacy_handle');
set_theme_mod('springapex_facebook', 'https://www.facebook.com/legacy-page/');
set_theme_mod('springapex_inquiry_email', 'old@example.com');
$springapex_test_on_theme_mods_read = static function (): void {
    // An operator saves the form settings screen right after the read.
    set_theme_mod('springapex_inquiry_email', 'new@example.com');
};
$springapex_test_cleared = springapex_brand_contact_clear_theme_mods(['springapex_x', 'springapex_facebook']);
springapex_test_assert($springapex_test_cleared, 'Clearing the legacy theme mods gave up instead of retrying.');
springapex_test_assert($springapex_test_on_theme_mods_read === null, 'The concurrent form-settings save never ran; the interleaving was not exercised.');
springapex_test_assert(get_theme_mod('springapex_inquiry_email', '') === 'new@example.com', 'Clearing the legacy theme mods rolled back a concurrent form-settings save.');
springapex_test_assert(get_theme_mod('springapex_x', '') === '', 'The legacy theme mod was not cleared.');
springapex_test_assert(get_theme_mod('springapex_facebook', '') === '', 'The legacy theme mod was not cleared.');

// A row whose value is not an array (hand-edited, or left by something older)
// must be repairable. Treating it as a missing row means every write tries to
// INSERT, the row is already there, and admin saves silently stop working.
$springapex_test_rows[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] = 'not-an-array';
$springapex_test_repaired = springapex_content_update_overrides(
    static function (array $overrides): array {
        $overrides['brand']['hours'] = 'Repaired';
        return $overrides;
    }
);
springapex_test_assert($springapex_test_repaired, 'A malformed overrides row could not be written over.');
springapex_test_assert(springapex_test_brand('hours') === 'Repaired', 'A malformed overrides row was not replaced.');

// autoload has to follow the row's size, the way the old storage helper did:
// a row that grows past the limit must stop loading on every request.
springapex_test_set_overrides(['hours' => 'Mon – Fri']);
$springapex_test_autoload[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] = 'on';
springapex_content_update_overrides(
    static function (array $overrides): array {
        $overrides['bulk'] = str_repeat('x', SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT + 1);
        return $overrides;
    }
);
springapex_test_assert(($springapex_test_autoload[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] ?? null) === 'off', 'A row that grew past the autoload limit is still autoloaded.');
springapex_content_update_overrides(
    static function (array $overrides): array {
        unset($overrides['bulk']);
        return $overrides;
    }
);
springapex_test_assert(($springapex_test_autoload[SPRINGAPEX_CONTENT_OVERRIDES_OPTION] ?? null) === 'on', 'A row that shrank below the limit is not autoloaded again.');

echo "brand-contact-migration: failed write, completion, re-entry, concurrent save, admin save, stale retry, row creation, theme mods, malformed row, autoload and fallback ok\n";
