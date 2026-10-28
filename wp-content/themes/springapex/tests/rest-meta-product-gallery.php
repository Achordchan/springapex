<?php
/**
 * 产品图集和参数表开放给 REST：注册在 spring_product 上、要编辑权限、
 * 用后台同一套清洗；REST 改图集后第一张同步为特色图。
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
$GLOBALS['springapex_test_post_meta'] = [];
$GLOBALS['springapex_test_thumbnail'] = [];

class WP_Post
{
    public function __construct(public int $ID)
    {
    }
}
class WP_REST_Request
{
    public function __construct(private array $params)
    {
    }
    public function get_param(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }
}

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
function get_post_type(int $id): string
{
    return in_array($id, [1566, 1569], true) ? 'attachment' : '';
}
function get_post_meta(int $post_id, string $key, bool $single): mixed
{
    return $GLOBALS['springapex_test_post_meta'][$post_id][$key] ?? '';
}
function set_post_thumbnail(int $post_id, int $attachment_id): void
{
    $GLOBALS['springapex_test_thumbnail'][$post_id] = $attachment_id;
}
function delete_post_thumbnail(int $post_id): void
{
    $GLOBALS['springapex_test_thumbnail'][$post_id] = 0;
}
function springapex_solution_row_sets(): array
{
    return [];
}
function springapex_solution_row_meta_keys(): array
{
    return [];
}
function springapex_product_row_sets(): array
{
    return [
        'springapex_gallery' => [
            ['key' => 'image', 'label' => '图片', 'type' => 'image'],
        ],
        'springapex_specs' => [
            ['key' => 'label', 'label' => '项目', 'type' => 'text'],
            ['key' => 'value', 'label' => '数值或范围', 'type' => 'text'],
        ],
    ];
}
function springapex_product_row_meta_keys(): array
{
    return [
        'springapex_gallery' => '_springapex_gallery',
        'springapex_specs' => '_springapex_specs',
    ];
}

function assert_same(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, $message . "\nExpected: " . var_export($expected, true) . "\nActual: " . var_export($actual, true) . "\n");
        exit(1);
    }
}

require __DIR__ . '/../inc/admin/row-editor.php';
require __DIR__ . '/../inc/rest-meta.php';

foreach ($GLOBALS['springapex_test_hooks']['init'] ?? [] as $callback) {
    $callback();
}

$gallery = $GLOBALS['springapex_test_meta']['spring_product']['_springapex_gallery'] ?? null;
assert_same(true, is_array($gallery), 'spring_product registers _springapex_gallery.');
assert_same('array', $gallery['type'], 'The gallery is an array.');
assert_same(
    ['image', 'image_id'],
    array_keys($gallery['show_in_rest']['schema']['items']['properties']),
    'Gallery rows expose the file name and the attachment ID.'
);
assert_same(
    [['image' => '', 'image_id' => 1566], ['image' => '', 'image_id' => 1569]],
    ($gallery['sanitize_callback'])([['image_id' => 1566], ['image_id' => 999], ['image_id' => 1569]]),
    'Rows are cleaned like the admin panel: an ID that is not an attachment is dropped.'
);
$GLOBALS['springapex_test_can_edit'] = [16];
assert_same(true, ($gallery['auth_callback'])(false, '_springapex_gallery', 16), 'Editors may write the gallery.');
assert_same(false, ($gallery['auth_callback'])(false, '_springapex_gallery', 17), 'Others may not.');

$specs = $GLOBALS['springapex_test_meta']['spring_product']['_springapex_specs'] ?? null;
assert_same(true, is_array($specs), 'spring_product registers _springapex_specs.');

$hook = $GLOBALS['springapex_test_hooks']['rest_after_insert_spring_product'][0] ?? null;
assert_same(true, is_callable($hook), 'A REST insert hook keeps the featured image in sync.');

$GLOBALS['springapex_test_post_meta'][16]['_springapex_gallery'] = [['image' => '', 'image_id' => 1569]];
$hook(new WP_Post(16), new WP_REST_Request(['meta' => ['_springapex_gallery' => []]]));
assert_same(1569, $GLOBALS['springapex_test_thumbnail'][16] ?? null, 'The first gallery image becomes the featured image.');

$GLOBALS['springapex_test_thumbnail'] = [];
$hook(new WP_Post(16), new WP_REST_Request(['meta' => ['_springapex_seo_title' => 'x']]));
assert_same([], $GLOBALS['springapex_test_thumbnail'], 'Requests without a gallery leave the featured image alone.');

$GLOBALS['springapex_test_post_meta'][16]['_springapex_gallery'] = [];
$hook(new WP_Post(16), new WP_REST_Request(['meta' => ['_springapex_gallery' => []]]));
assert_same(0, $GLOBALS['springapex_test_thumbnail'][16] ?? null, 'An empty gallery clears the featured image, as the admin save does.');

echo "rest-meta product gallery: ok\n";
