<?php
/**
 * 表单设置的 REST 接口：只改提交的表单和键，字段与后台保存同一套规范化，
 * 未知表单/键、无效或重复字段报 400，并发写入走 compare-and-swap，权限为 manage_options。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$GLOBALS['springapex_test_hooks'] = [];
$GLOBALS['springapex_test_routes'] = [];
$GLOBALS['springapex_test_options'] = [];
$GLOBALS['springapex_test_updates'] = 0;
$GLOBALS['springapex_test_can'] = true;
$GLOBALS['springapex_test_before_write'] = null;
$GLOBALS['springapex_test_db_fail'] = false;

function add_action(string $hook, callable|string $callback, int $priority = 10, int $args = 1): void
{
    $GLOBALS['springapex_test_hooks'][$hook][] = $callback;
}
function current_user_can(string $capability): bool
{
    return $capability === 'manage_options' && $GLOBALS['springapex_test_can'];
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
function update_option(string $name, mixed $value, bool $autoload = false): bool
{
    $GLOBALS['springapex_test_options'][$name] = $value;
    return true;
}
function delete_option(string $name): bool
{
    unset($GLOBALS['springapex_test_options'][$name]);
    return true;
}
function wp_cache_delete(string $key, string $group = ''): bool
{
    return true;
}
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

require dirname(__DIR__) . '/inc/form-schema.php';
require dirname(__DIR__) . '/inc/rest-form-schema.php';

function check(bool $ok, string $message): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

/** @return array<string, mixed> */
function field_by_id(array $fields, string $id): array
{
    foreach ($fields as $field) {
        if ($field['id'] === $id) {
            return $field;
        }
    }
    return [];
}

function post(array $forms): WP_REST_Response|WP_Error
{
    return springapex_rest_form_schema_update(new WP_REST_Request(['forms' => $forms]));
}

foreach ($GLOBALS['springapex_test_hooks']['rest_api_init'] ?? [] as $callback) {
    $callback();
}
$route = $GLOBALS['springapex_test_routes']['springapex/v1/form-schema'] ?? null;
check(is_array($route) && count($route) === 2, 'GET and POST registered');
check($route[0]['permission_callback']() === true, 'admin may read');
$GLOBALS['springapex_test_can'] = false;
check($route[1]['permission_callback']() === false, 'non-admin may not write');
$GLOBALS['springapex_test_can'] = true;

// 线上现状：产品表单材质下拉里有一个拼错的选项，联系表单是运营自定义过的。
$defaults = springapex_form_schema_defaults();
$product = $defaults['product'];
foreach ($product['fields'] as &$field) {
    if ($field['id'] === 'material') {
        $field['options']['Titanium Allo Spring Wire'] = 'Titanium Allo Spring Wire';
    }
}
unset($field);
$contact = $defaults['contact'];
$contact['turnstile'] = false;
$contact['fields'] = array_values(array_filter($contact['fields'], static fn(array $f): bool => $f['id'] !== 'company'));
$GLOBALS['springapex_test_options']['springapex_form_schema'] = ['quick' => $defaults['quick'], 'contact' => $contact, 'product' => $product];
$GLOBALS['springapex_test_options']['springapex_form_schema_version'] = SPRINGAPEX_FORM_SCHEMA_VERSION;

$get = springapex_rest_form_schema_get()->data;
check(isset($get['forms']['product'], $get['forms']['contact'], $get['forms']['quick']), 'GET returns all three forms');
check(isset(field_by_id($get['forms']['product']['fields'], 'material')['options']['Titanium Allo Spring Wire']), 'GET shows stored options');
check(in_array('select', $get['field_types'], true) && $get['locked_fields'] === ['email', 'message'], 'GET lists types and locked fields');

// 改拼写：GET 产品表单，替换一个选项，整份字段列表发回。
$fields = $get['forms']['product']['fields'];
foreach ($fields as &$field) {
    if ($field['id'] === 'material') {
        unset($field['options']['Titanium Allo Spring Wire']);
        $field['options']['Titanium Alloy Spring Wire'] = 'Titanium Alloy Spring Wire';
    }
}
unset($field);
$res = post(['product' => ['fields' => $fields]]);
check($res instanceof WP_REST_Response && $res->data['saved'] === true, 'update saved');
check(array_keys($res->data['forms']) === ['product'], 'response lists only submitted forms');
$stored = $GLOBALS['springapex_test_options']['springapex_form_schema'];
$options = field_by_id($stored['product']['fields'], 'material')['options'];
check(isset($options['Titanium Alloy Spring Wire']) && !isset($options['Titanium Allo Spring Wire']), 'typo fixed: ' . json_encode($options));
check(array_column($stored['product']['fields'], 'id') === array_column($product['fields'], 'id'), 'product field order kept');
check($stored['product']['turnstile'] === true, 'unsent turnstile flag keeps its current value');
check($stored['contact']['turnstile'] === false && field_by_id($stored['contact']['fields'], 'company') === [], 'other forms untouched');
check($stored['quick']['enabled'] === true, 'quick form stays enabled');

$updates = $GLOBALS['springapex_test_updates'];
post(['product' => ['fields' => $fields]]);
check($GLOBALS['springapex_test_updates'] === $updates, 'unchanged values do not rewrite the option');

// 开关单独改，不碰字段。
$res = post(['quick' => ['enabled' => false]]);
$stored = $GLOBALS['springapex_test_options']['springapex_form_schema'];
check($res instanceof WP_REST_Response && $stored['quick']['enabled'] === false && count($stored['quick']['fields']) === 3, 'flag-only update');

// 选项可用列表形式；锁定字段缺失时补回。
$res = post(['product' => ['fields' => [
    ['id' => 'material', 'label' => 'Material', 'options' => ['Music Wire', 'Inconel']],
]]]);
$stored = $GLOBALS['springapex_test_options']['springapex_form_schema'];
check(field_by_id($stored['product']['fields'], 'material')['options'] === ['Music Wire' => 'Music Wire', 'Inconel' => 'Inconel'], 'list options expand to value => label');
check(array_column($stored['product']['fields'], 'id') === ['material', 'email', 'message'], 'locked fields restored: ' . json_encode(array_column($stored['product']['fields'], 'id')));

// 并发：读完快照后、写入前，后台改了联系表单；本次改产品表单。两边都要留下。
$GLOBALS['springapex_test_before_write'] = static function (): void {
    $GLOBALS['springapex_test_options']['springapex_form_schema']['contact']['turnstile'] = true;
};
$res = post(['product' => ['turnstile' => false]]);
$stored = $GLOBALS['springapex_test_options']['springapex_form_schema'];
check($res instanceof WP_REST_Response, 'concurrent update eventually saves');
check($stored['contact']['turnstile'] === true, 'concurrent change is not overwritten');
check($stored['product']['turnstile'] === false, 'own change saved after retry');

// 数据库写入一直失败：不能回 saved: true。
$GLOBALS['springapex_test_db_fail'] = true;
$err = post(['product' => ['turnstile' => true]]);
check($err instanceof WP_Error && ($err->data['status'] ?? 0) === 409, 'failed write reports an error');
$GLOBALS['springapex_test_db_fail'] = false;

// option 还不存在（从未保存过）：以默认配置为底建行，并盖结构版本。
$GLOBALS['springapex_test_options'] = [];
$res = post(['contact' => ['turnstile' => false]]);
$stored = $GLOBALS['springapex_test_options']['springapex_form_schema'] ?? [];
check($res instanceof WP_REST_Response && $stored['contact']['turnstile'] === false, 'creates the option when missing');
check(count($stored['contact']['fields']) === count($defaults['contact']['fields']), 'default fields kept on first save');
check(($GLOBALS['springapex_test_options']['springapex_form_schema_version'] ?? '') === SPRINGAPEX_FORM_SCHEMA_VERSION, 'version stamped');

foreach ([
    'empty' => [],
    'unknown form' => ['nope' => ['turnstile' => true]],
    'form not object' => ['product' => 'x'],
    'empty form' => ['product' => []],
    'unknown key' => ['product' => ['label' => 'x']],
    'enabled on form without it' => ['product' => ['enabled' => true]],
    'non-bool flag' => ['product' => ['turnstile' => 'yes']],
    'fields not list' => ['product' => ['fields' => ['a' => ['id' => 'email']]]],
    'field not object' => ['product' => ['fields' => ['email']]],
    'invalid id' => ['product' => ['fields' => [['id' => '!!!']]]],
    'duplicate id' => ['product' => ['fields' => [['id' => 'email'], ['id' => 'Email']]]],
] as $case => $forms) {
    $before = $GLOBALS['springapex_test_options'];
    $err = post($forms);
    check($err instanceof WP_Error && ($err->data['status'] ?? 0) === 400, "rejects {$case}");
    check($GLOBALS['springapex_test_options'] === $before, "{$case} writes nothing");
}

echo "rest-form-schema: ok\n";
