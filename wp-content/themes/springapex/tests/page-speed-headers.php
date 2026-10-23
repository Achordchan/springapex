<?php
/**
 * 测速相关的两处输出：CloudFront 缓存头只给安全的回应加；GTM 默认延迟加载，
 * 后台关掉后恢复官方立即加载的代码。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$GLOBALS['springapex_test_options'] = [];
$GLOBALS['springapex_test_actions'] = [];

function add_action(string $hook, callable|string $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['springapex_test_actions'][$hook][] = $callback;
}
function get_option(string $name, mixed $default = false): mixed
{
    return $GLOBALS['springapex_test_options'][$name] ?? $default;
}
function wp_get_environment_type(): string
{
    return 'production';
}
function esc_js(string $text): string
{
    return $text;
}
function esc_url(string $url): string
{
    return $url;
}
function add_query_arg(string $key, string $value, string $url): string
{
    return $url . '?' . $key . '=' . $value;
}

require dirname(__DIR__) . '/inc/analytics.php';
require dirname(__DIR__) . '/inc/edge-cache.php';

$failures = [];
$check = static function (bool $ok, string $message) use (&$failures): void {
    if (!$ok) {
        $failures[] = $message;
    }
};

// ---- 缓存头：请求层面 ----
$base = ['method' => 'GET', 'logged_in' => false, 'preview' => false, 'password_required' => false, 'cookies' => []];
$check(springapex_edge_cache_request_allowed($base), 'anonymous GET should be cacheable');
$check(springapex_edge_cache_request_allowed(['method' => 'HEAD'] + $base), 'HEAD should be cacheable');
$check(!springapex_edge_cache_request_allowed(['method' => 'POST'] + $base), 'POST must not be cached');
$check(!springapex_edge_cache_request_allowed(['logged_in' => true] + $base), 'logged-in pages must not be cached');
$check(!springapex_edge_cache_request_allowed(['preview' => true] + $base), 'previews must not be cached');
$check(!springapex_edge_cache_request_allowed(['password_required' => true] + $base), 'password forms must not be cached');
$check(!springapex_edge_cache_request_allowed(['cookies' => ['wp-postpass_abc' => '1']] + $base), 'unlocked protected posts must not be cached');
$check(!springapex_edge_cache_request_allowed(['cookies' => ['comment_author_abc' => 'x']] + $base), 'commenter pages must not be cached');
$check(springapex_edge_cache_request_allowed(['cookies' => ['_ga' => 'GA1.1']] + $base), 'analytics cookies must not disable caching');

// ---- 缓存头：回应层面 ----
$expected = 'Cache-Control: public, max-age=0, s-maxage=600';
$check(springapex_edge_cache_header(200, ['Content-Type: text/html']) === $expected, 'plain 200 should get s-maxage');
$check(springapex_edge_cache_header(404, []) === '', '404 must not be cached');
$check(springapex_edge_cache_header(301, []) === '', 'redirects are left to WordPress');
$check(springapex_edge_cache_header(200, ['Set-Cookie: a=b']) === '', 'responses setting cookies must not be cached');
$check(springapex_edge_cache_header(200, ['Cache-Control: no-cache, private']) === '', 'existing Cache-Control must win');

// ---- GTM 输出 ----
$render_head = static function (): string {
    ob_start();
    foreach ($GLOBALS['springapex_test_actions']['wp_head'] ?? [] as $callback) {
        $callback();
    }

    return (string) ob_get_clean();
};

$delayed = $render_head();
$check(str_contains($delayed, "event:'gtm.js'"), 'delayed snippet still queues gtm.js immediately');
$check(str_contains($delayed, "'GTM-W3B3NW5N'," . SPRINGAPEX_GTM_DELAY_MS), 'delayed snippet uses the seed container and fallback delay');
$check(str_contains($delayed, "'pointerdown','keydown','touchstart','scroll','wheel'"), 'delayed snippet listens for first interaction');
$check(str_contains($delayed, "addEventListener('load',a)"), 'fallback timer starts after window load');
$check(substr_count($delayed, 'googletagmanager.com/gtm.js') === 1, 'exactly one GTM loader');

$GLOBALS['springapex_test_options'][SPRINGAPEX_TRACKING_OPTION] = ['gtm_delay' => false];
$immediate = $render_head();
$check(str_contains($immediate, 'j.async=true;j.src=') && !str_contains($immediate, 'pointerdown'), 'disabling delay restores the official snippet');

$GLOBALS['springapex_test_options'][SPRINGAPEX_TRACKING_OPTION] = ['gtm_id' => ''];
$check($render_head() === '', 'empty container id outputs nothing');

if ($failures !== []) {
    fwrite(STDERR, implode("\n", $failures) . "\n");
    exit(1);
}

echo "page-speed-headers: ok\n";
