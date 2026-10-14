<?php
/**
 * SEO / TDK 路由设置的 REST 接口：只改提交的路由和字段，与后台表单同一套归一化
 * （空值或等于内置默认 → 存空），未知路由和字段报 400，权限为 SPRINGAPEX_ADMIN_CAP。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('SPRINGAPEX_ADMIN_CAP', 'edit_theme_options');

$GLOBALS['springapex_test_hooks'] = [];
$GLOBALS['springapex_test_routes'] = [];
$GLOBALS['springapex_test_options'] = [];
$GLOBALS['springapex_test_updates'] = 0;
$GLOBALS['springapex_test_can'] = true;

function add_action(string $hook, callable|string $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['springapex_test_hooks'][$hook][] = $callback;
}
function add_filter(string $hook, callable|string $callback, int $priority = 10, int $args = 1): void
{
}
function current_user_can(string $capability): bool
{
    return $capability === 'edit_theme_options' && $GLOBALS['springapex_test_can'];
}
function register_rest_route(string $namespace, string $route, array $args): bool
{
    $GLOBALS['springapex_test_routes'][$namespace . $route] = $args;
    return true;
}
function get_option(string $name, mixed $default = false): mixed
{
    return $GLOBALS['springapex_test_options'][$name] ?? $default;
}
function wp_cache_delete(string $key, string $group = ''): bool
{
    return true;
}
// inc/locks.php 的 compare-and-swap，用内存里的 option 模拟。
// springapex_test_before_write 可在写入前插入一次「别人的并发保存」；
// springapex_test_db_fail 让每次写入都失败。
$GLOBALS['springapex_test_before_write'] = null;
$GLOBALS['springapex_test_db_fail'] = false;
function springapex_option_row_exists(string $name): bool
{
    return array_key_exists($name, $GLOBALS['springapex_test_options']);
}
function springapex_test_concurrent_write(): void
{
    $hook = $GLOBALS['springapex_test_before_write'];
    $GLOBALS['springapex_test_before_write'] = null;
    if (is_callable($hook)) {
        $hook();
    }
}
function springapex_add_option_if_absent(string $name, mixed $value, bool $autoload): bool
{
    springapex_test_concurrent_write();
    if ($GLOBALS['springapex_test_db_fail'] || springapex_option_row_exists($name)) {
        return false;
    }
    $GLOBALS['springapex_test_options'][$name] = $value;
    $GLOBALS['springapex_test_updates']++;
    return true;
}
function springapex_update_option_if_unchanged(string $name, mixed $expected, mixed $value, ?bool $autoload = null): bool
{
    springapex_test_concurrent_write();
    if ($GLOBALS['springapex_test_db_fail'] || ($GLOBALS['springapex_test_options'][$name] ?? null) !== $expected) {
        return false;
    }
    $GLOBALS['springapex_test_options'][$name] = $value;
    $GLOBALS['springapex_test_updates']++;
    return true;
}
function sanitize_text_field(string $value): string
{
    return trim(strip_tags($value));
}
function sanitize_textarea_field(string $value): string
{
    return trim(strip_tags($value));
}

final class WP_REST_Server
{
    public const READABLE = 'GET';
    public const EDITABLE = 'POST, PUT, PATCH';
}
final class WP_REST_Response
{
    public function __construct(public mixed $data = null)
    {
    }
}
final class WP_Error
{
    public function __construct(public string $code = '', public string $message = '', public array $data = [])
    {
    }
}
final class WP_REST_Request
{
    public function __construct(private array $params)
    {
    }
    public function get_param(string $key): mixed
    {
        return $this->params[$key] ?? null;
    }
}

require dirname(__DIR__) . '/inc/seo.php';
require dirname(__DIR__) . '/inc/rest-seo.php';

function check(bool $ok, string $message): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

foreach ($GLOBALS['springapex_test_hooks']['rest_api_init'] ?? [] as $callback) {
    $callback();
}
$route = $GLOBALS['springapex_test_routes']['springapex/v1/seo'] ?? null;
check(is_array($route) && count($route) === 2, 'GET and POST registered');
check($route[0]['permission_callback']() === true, 'admin may read');
$GLOBALS['springapex_test_can'] = false;
check($route[1]['permission_callback']() === false, 'non-admin may not write');
$GLOBALS['springapex_test_can'] = true;

$defaults = springapex_seo_route_definitions();
$GLOBALS['springapex_test_options']['springapex_seo_settings'] = ['routes' => [
    'home' => ['title' => 'Custom Spring Manufacturer in China Since 2001 | NorenSpring', 'description' => '', 'keywords' => 'springs'],
]];

$get = springapex_rest_seo_get()->data['routes'];
check($get['home']['title'] === 'Custom Spring Manufacturer in China Since 2001 | NorenSpring' && $get['home']['custom_title'], 'saved title is reported as custom');
check($get['terms']['description'] === $defaults['terms']['description'] && !$get['terms']['custom_description'], 'unsaved route falls back to default');

$new = 'Read the terms that govern use of the NorenSpring website, including content, catalog downloads, drawings and quote inquiries for custom springs.';
$res = springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['description' => $new]]]));
check($res instanceof WP_REST_Response && $res->data['saved'] === true, 'update saved');
check(array_keys($res->data['routes']) === ['terms'], 'response lists only submitted routes');
check($res->data['routes']['terms']['description'] === $new, 'terms description updated');
$stored = $GLOBALS['springapex_test_options']['springapex_seo_settings']['routes'];
check($stored['terms'] === ['title' => '', 'description' => $new, 'keywords' => ''], 'only the custom description is stored: ' . json_encode($stored['terms']));
check($stored['home']['title'] === 'Custom Spring Manufacturer in China Since 2001 | NorenSpring' && $stored['home']['keywords'] === 'springs', 'other routes untouched');
check($stored['privacy'] === ['title' => '', 'description' => '', 'keywords' => ''], 'default routes stay empty');

$updates = $GLOBALS['springapex_test_updates'];
springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['description' => $new]]]));
check($GLOBALS['springapex_test_updates'] === $updates, 'unchanged values do not rewrite the option');

springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['description' => '']]]));
check($GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['terms']['description'] === '', 'empty string resets to default');
springapex_rest_seo_update(new WP_REST_Request(['routes' => ['home' => ['title' => $defaults['home']['title']]]]));
check($GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['home']['title'] === '', 'value equal to default stores empty');

// 已存的 '0' 不能在改别的路由时被换成默认值。
$GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['sitemap'] = ['title' => '0', 'description' => '0', 'keywords' => ''];
springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['keywords' => 'terms']]]));
$sitemap = $GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['sitemap'];
check($sitemap['title'] === '0' && $sitemap['description'] === '0', 'zero-valued fields survive: ' . json_encode($sitemap));

// 并发：读完快照后、写入前，别人改了 privacy 的描述；本次改 terms 标题。
// 第一次 CAS 失败，重读后两边的改动都要留下。
$GLOBALS['springapex_test_before_write'] = static function (): void {
    $GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['privacy']['description'] = 'Concurrent privacy description.';
};
$res = springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['title' => 'Terms of Use | NorenSpring Springs']]]));
check($res instanceof WP_REST_Response, 'concurrent update eventually saves');
$stored = $GLOBALS['springapex_test_options']['springapex_seo_settings']['routes'];
check($stored['privacy']['description'] === 'Concurrent privacy description.', 'concurrent change is not overwritten');
check($stored['terms']['title'] === 'Terms of Use | NorenSpring Springs', 'own change saved after retry');

// 数据库写入一直失败：不能回 saved: true。
$GLOBALS['springapex_test_db_fail'] = true;
$err = springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['keywords' => 'springs']]]));
check($err instanceof WP_Error && ($err->data['status'] ?? 0) === 409, 'failed write reports an error');
// 值没变时不需要写，写入失败也不影响。
$res = springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['title' => 'Terms of Use | NorenSpring Springs']]]));
check($res instanceof WP_REST_Response, 'unchanged value succeeds without a write');
$GLOBALS['springapex_test_db_fail'] = false;

// option 行还不存在时用 INSERT IGNORE 建行。
unset($GLOBALS['springapex_test_options']['springapex_seo_settings']);
$res = springapex_rest_seo_update(new WP_REST_Request(['routes' => ['terms' => ['description' => $new]]]));
check($res instanceof WP_REST_Response && $GLOBALS['springapex_test_options']['springapex_seo_settings']['routes']['terms']['description'] === $new, 'creates the option when missing');

foreach ([
    ['routes' => []],
    ['routes' => ['nope' => ['title' => 'x']]],
    ['routes' => ['terms' => 'x']],
    ['routes' => ['terms' => ['label' => 'x']]],
    ['routes' => ['terms' => ['title' => 5]]],
] as $bad) {
    $err = springapex_rest_seo_update(new WP_REST_Request($bad));
    check($err instanceof WP_Error && ($err->data['status'] ?? 0) === 400, 'rejects ' . json_encode($bad));
}

echo "rest-seo: OK\n";
