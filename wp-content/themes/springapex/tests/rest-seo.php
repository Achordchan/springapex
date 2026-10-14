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
function update_option(string $name, mixed $value): bool
{
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
