<?php
/**
 * 映射字段可加到任意表单。产品表单默认没有「姓名」，所以产品询盘的标题与
 * 通知邮件一律记为「匿名」；运营者把别的字段（如数量）改名叫 name 也没用，
 * 因为字段的用途绑定在稳定 id 上，不看名称。修复后：运营者能用「添加映射
 * 字段」把 name 加到产品表单，规范化（保存/加载的同一入口）必须接受它。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

/** @var array<string, mixed> */
$springapex_test_options = [];

function get_option(string $option, mixed $default_value = false): mixed
{
    global $springapex_test_options;
    return $springapex_test_options[$option] ?? $default_value;
}

function update_option(string $option, mixed $value, bool $autoload = false): bool
{
    global $springapex_test_options;
    $springapex_test_options[$option] = $value;
    return true;
}

require __DIR__ . '/../inc/form-schema.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

/** @return array<string, array<string, mixed>> */
function springapex_test_defaults_by_id(string $form): array
{
    $by_id = [];
    foreach (springapex_form_schema_defaults()[$form]['fields'] as $field) {
        $by_id[$field['id']] = $field;
    }
    return $by_id;
}

// 产品表单默认没有姓名字段：这正是产品询盘一律「匿名」的根因。
$product_defaults = springapex_test_defaults_by_id('product');
springapex_test_assert(!isset($product_defaults['name']), 'Product defaults unexpectedly ship a name field.');

// 规范定义覆盖所有系统字段（含锁定的邮箱/留言），姓名的类型是 text。
$definitions = springapex_form_system_field_definitions();
foreach (array_keys(springapex_form_system_fields()) as $system_id) {
    springapex_test_assert(isset($definitions[$system_id]), "System field '{$system_id}' has no canonical definition.");
}
springapex_test_assert($definitions['name']['type'] === 'text', 'Canonical name field is no longer a text input.');

// 可添加的映射字段：去掉锁定字段（邮箱/留言恒在），保留姓名等。
$addable = springapex_form_addable_mapped_fields();
springapex_test_assert(!isset($addable['email']) && !isset($addable['message']), 'Locked fields must not be offered as addable.');
foreach (['name', 'phone', 'company', 'country', 'quantity', 'material', 'operating_environment'] as $expected) {
    springapex_test_assert(isset($addable[$expected]), "Mapped field '{$expected}' is not addable.");
}

// 核心修复：在产品表单上规范化一个 name 行，过去返回 null（系统字段不在本
// 表单默认里就拒绝），现在必须接受，id 保持 name、类型取规范定义的 text、
// 运营者填的名称与必填照旧尊重。
$normalized = springapex_normalize_form_field(
    ['id' => 'name', 'label' => '联系人', 'type' => 'text', 'required' => '1'],
    $product_defaults,
    springapex_form_field_types()
);
springapex_test_assert($normalized !== null, 'A name field was rejected on the product form.');
springapex_test_assert($normalized['id'] === 'name', 'The added mapped field lost its stable id.');
springapex_test_assert($normalized['type'] === 'text', 'The added name field lost its canonical type.');
springapex_test_assert($normalized['label'] === '联系人', 'The operator label for the added name field was dropped.');
springapex_test_assert($normalized['required'] === true, 'The required flag for the added name field was dropped.');

// 一个既非系统字段、又不在默认里的 id 仍按自定义字段处理（不是映射字段）。
$custom = springapex_normalize_form_field(
    ['id' => 'nickname', 'label' => 'Nickname', 'type' => 'text'],
    $product_defaults,
    springapex_form_field_types()
);
springapex_test_assert($custom !== null && $custom['id'] === 'nickname', 'A plain custom field was mishandled.');

// 端到端：保存/加载的同一入口 springapex_form_schema() 必须让加进产品表单的
// name 存活，前台才会渲染它、contact.php 才能把它写进询盘标题。
$springapex_test_options['springapex_form_schema_version'] = SPRINGAPEX_FORM_SCHEMA_VERSION;
$springapex_test_options['springapex_form_schema'] = [
    'product' => [
        'turnstile' => true,
        'fields' => [
            ['id' => 'email', 'label' => 'Work Email', 'type' => 'email', 'required' => true],
            ['id' => 'name', 'label' => 'Contact name', 'type' => 'text', 'required' => false],
            ['id' => 'quantity', 'label' => 'Quantity', 'type' => 'text', 'required' => false],
        ],
    ],
];
$loaded = springapex_form_schema();
$loaded_ids = array_map(static fn (array $f): string => (string) $f['id'], $loaded['product']['fields']);
springapex_test_assert(in_array('name', $loaded_ids, true), 'The added name field did not survive a schema load.');
springapex_test_assert(in_array('message', $loaded_ids, true), 'The locked message field was not re-added on load.');

echo "form-mapped-fields: canonical definitions, addable set, name-on-product normalize and schema round-trip ok\n";
