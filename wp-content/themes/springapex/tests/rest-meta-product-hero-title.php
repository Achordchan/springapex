<?php
/**
 * 产品「详情页 H1」开放给 REST：可读写、要编辑权限、单行清洗、不设默认值；
 * 模板在它为空时回退到产品名，面包屑始终用产品名。
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

$args = $GLOBALS['springapex_test_meta']['spring_product']['_springapex_hero_title'] ?? null;
assert_same(true, is_array($args), 'spring_product registers _springapex_hero_title.');
assert_same(true, $args['show_in_rest'], 'The hero title is exposed in REST.');
assert_same('string', $args['type'], 'The hero title is a string.');
assert_same(true, $args['single'], 'The hero title is a single value.');
assert_same(false, array_key_exists('default', $args), 'No default, so an unset hero title falls back to the product title.');
assert_same(
    'Custom Compression Spring Manufacturer',
    ($args['sanitize_callback'])('  <b>Custom Compression Spring Manufacturer</b> '),
    'The hero title is cleaned to plain text.'
);
assert_same('', ($args['sanitize_callback'])(['not', 'text']), 'Non-scalar input becomes empty.');

$GLOBALS['springapex_test_can_edit'] = [15];
assert_same(true, ($args['auth_callback'])(false, '_springapex_hero_title', 15), 'Editors of the product can write it.');
assert_same(false, ($args['auth_callback'])(false, '_springapex_hero_title', 232), 'Users who cannot edit the product cannot write it.');

// 模板：H1 用详情页 H1，面包屑仍用产品名；为空时 H1 回退到产品名。
$template = (string) file_get_contents(__DIR__ . '/../templates/product-compression.php');
assert_same(1, preg_match('/\$product_h1 = trim\(\(string\) \(\$product\[\'hero_title\'\] \?\? \'\'\)\);/', $template), 'The template reads hero_title for the H1.');
assert_same(1, preg_match('/<h1 id="compression-title"><\?php echo esc_html\(\$product_h1\); \?><\/h1>/', $template), 'The H1 prints the resolved hero title.');
assert_same(1, preg_match('/<span aria-current="page"><\?php echo esc_html\(\(string\) \(\$product\[\'title\'\]/', $template), 'The breadcrumb still uses the product title.');

echo "rest-meta product hero title: ok\n";
