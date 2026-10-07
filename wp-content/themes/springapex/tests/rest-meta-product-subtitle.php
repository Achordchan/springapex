<?php
/**
 * 产品「标题下的一句话」开放给 REST：可读写、要编辑权限、清洗规则与后台一致、
 * 不设默认值（保留回退到种子文案的行为）。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('SPRINGAPEX_NEWS_DATE_LABEL_META', '_springapex_news_date_label');
define('SPRINGAPEX_NEWS_PRODUCTS_META', '_springapex_news_products');
define('SPRINGAPEX_NEWS_AUTHOR_META', '_springapex_news_author');
define('SPRINGAPEX_NEWS_VIEWS_BASE_META', '_springapex_news_views_base');
define('SPRINGAPEX_NEWS_AUTHOR_ROLE_META', '_springapex_news_author_role');
define('SPRINGAPEX_NEWS_AUTHOR_BIO_META', '_springapex_news_author_bio');

$GLOBALS['springapex_test_hooks'] = [];
$GLOBALS['springapex_test_meta'] = [];
$GLOBALS['springapex_test_can_edit'] = [];

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['springapex_test_hooks'][$hook][] = $callback;
}
function add_post_type_support(string $post_type, string $feature): void
{
}
function register_post_meta(string $post_type, string $meta_key, array $args): bool
{
    $GLOBALS['springapex_test_meta'][$post_type][$meta_key] = $args;
    return true;
}
function current_user_can(string $capability, int $post_id): bool
{
    return in_array($post_id, $GLOBALS['springapex_test_can_edit'], true);
}
function sanitize_text_field(string $value): string
{
    return trim(strip_tags($value));
}
function sanitize_textarea_field(string $value): string
{
    return trim(strip_tags($value));
}
function springapex_solution_row_sets(): array
{
    return [];
}
function springapex_solution_row_meta_keys(): array
{
    return [];
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

require __DIR__ . '/../inc/rest-meta.php';

foreach ($GLOBALS['springapex_test_hooks']['init'] ?? [] as $callback) {
    $callback();
}

$args = $GLOBALS['springapex_test_meta']['spring_product']['_springapex_subtitle'] ?? null;
assert_same(true, is_array($args), 'spring_product registers _springapex_subtitle.');
assert_same(true, $args['show_in_rest'], 'The subtitle is exposed in REST.');
assert_same('string', $args['type'], 'The subtitle is a string.');
assert_same(true, $args['single'], 'The subtitle is a single value.');
assert_same(false, array_key_exists('default', $args), 'No default, so a missing meta still falls back to the seed text.');
assert_same(
    'Springs for vibrating screens.',
    ($args['sanitize_callback'])('  <b>Springs for vibrating screens.</b> '),
    'The subtitle is cleaned like the admin panel.'
);
assert_same('', ($args['sanitize_callback'])(['not', 'text']), 'Non-scalar input becomes empty.');

$GLOBALS['springapex_test_can_edit'] = [232];
assert_same(true, ($args['auth_callback'])(false, '_springapex_subtitle', 232), 'Editors of the product can write it.');
assert_same(false, ($args['auth_callback'])(false, '_springapex_subtitle', 15), 'Users who cannot edit the product cannot write it.');

echo "rest-meta product subtitle: ok\n";
