<?php
/**
 * 站点地图列表页：注册 archives 提供者，只输出三个列表页，lastmod 取最近修改的文章。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$GLOBALS['springapex_test_hooks'] = [];
$GLOBALS['springapex_test_providers'] = [];
$GLOBALS['springapex_test_latest'] = [
    'spring_product' => [15],
    'spring_solution' => [],
    'spring_case' => [301],
];

abstract class WP_Sitemaps_Provider
{
    protected $name = '';
    protected $object_type = '';
    abstract public function get_url_list($page_num, $object_subtype = '');
    abstract public function get_max_num_pages($object_subtype = '');
}

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['springapex_test_hooks'][$hook][] = $callback;
}
function wp_register_sitemap_provider(string $name, WP_Sitemaps_Provider $provider): bool
{
    $GLOBALS['springapex_test_providers'][$name] = $provider;
    return true;
}
function get_post_type_archive_link(string $post_type): string|false
{
    return [
        'spring_product' => 'https://www.norenspring.com/products/',
        'spring_solution' => 'https://www.norenspring.com/solutions/',
        'spring_case' => 'https://www.norenspring.com/case-studies/',
    ][$post_type] ?? false;
}
function get_posts(array $args): array
{
    if (($args['post_status'] ?? '') !== 'publish' || ($args['orderby'] ?? '') !== 'modified') {
        throw new RuntimeException('unexpected query');
    }
    return $GLOBALS['springapex_test_latest'][$args['post_type']] ?? [];
}
function get_post_modified_time(string $format, bool $gmt, int $post_id): string
{
    return $post_id === 15 ? '2026-10-01T08:00:00+00:00' : '2026-09-20T08:00:00+00:00';
}

require dirname(__DIR__) . '/inc/sitemap-archives.php';

function check(bool $ok, string $message): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

foreach ($GLOBALS['springapex_test_hooks']['wp_sitemaps_init'] ?? [] as $callback) {
    $callback();
}

$provider = $GLOBALS['springapex_test_providers']['archives'] ?? null;
check($provider instanceof WP_Sitemaps_Provider, 'archives provider registered');
check($provider->get_max_num_pages() === 1, 'single sitemap page');
check($provider->get_url_list(2) === [], 'no entries beyond page 1');

$entries = $provider->get_url_list(1);
check($entries === [
    ['loc' => 'https://www.norenspring.com/products/', 'lastmod' => '2026-10-01T08:00:00+00:00'],
    ['loc' => 'https://www.norenspring.com/solutions/'],
    ['loc' => 'https://www.norenspring.com/case-studies/', 'lastmod' => '2026-09-20T08:00:00+00:00'],
], 'archive entries with lastmod: ' . json_encode($entries));

echo "sitemap-archives: OK\n";
