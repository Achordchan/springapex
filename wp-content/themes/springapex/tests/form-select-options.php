<?php
/**
 * Select 字段选项的保存链路。后台「表单设置」把选项以 textarea（一行一项）提交，
 * 而映射字段的「类型」下拉是 disabled 的、不会随表单提交 —— 解析必须按字段最终
 * 生效的类型来做，不能看提交上来的 type。
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

function esc_attr(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function esc_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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

/** @param array<string, mixed> $row */
function springapex_test_normalize(array $row, string $form = 'contact'): ?array
{
    return springapex_normalize_form_field($row, springapex_test_defaults_by_id($form), springapex_form_field_types());
}

$material_defaults = array_keys(springapex_test_defaults_by_id('contact')['material']['options']);
springapex_test_assert($material_defaults !== [], 'The default schema no longer ships Material options.');

// A mapped field's type control is disabled in wp-admin, so no type is posted.
// Parsing by the posted type wiped these options on every save.
$edited = springapex_test_normalize([
    'id' => 'material',
    'label' => 'Material',
    'placeholder' => 'Select material',
    'width' => 'half',
    'options' => "Music Wire\r\nStainless Steel\r\nInconel",
]);
springapex_test_assert($edited !== null, 'A mapped select field was rejected.');
springapex_test_assert($edited['type'] === 'select', 'A mapped field lost its fixed type.');
springapex_test_assert(
    array_keys($edited['options']) === ['Music Wire', 'Stainless Steel', 'Inconel'],
    'Options posted for a mapped select field were not saved.'
);

// Saving the screen without touching the field must keep what is there.
$untouched = springapex_test_normalize([
    'id' => 'material',
    'label' => 'Material',
    'width' => 'half',
    'options' => implode("\n", $material_defaults),
]);
springapex_test_assert(array_keys($untouched['options']) === $material_defaults, 'An untouched mapped select field lost its options.');

// Rows already wiped by the old behaviour heal back to the shipped options: the
// type is fixed to select, so a dropdown with nothing in it is never intended.
$wiped = springapex_test_normalize(['id' => 'material', 'label' => 'Material', 'width' => 'half', 'options' => '']);
springapex_test_assert(array_keys($wiped['options']) === $material_defaults, 'A wiped mapped select field was not healed.');

$wiped_array = springapex_test_normalize(['id' => 'material', 'label' => 'Material', 'width' => 'half', 'options' => []]);
springapex_test_assert(array_keys($wiped_array['options']) === $material_defaults, 'A wiped mapped select field was not healed.');

// An operator-created select posts its type and is parsed the same way.
$custom = springapex_test_normalize([
    'id' => 'finish',
    'label' => 'Finish',
    'type' => 'select',
    'width' => 'full',
    'options' => "Zinc\nBlack oxide\n\n  Passivated  ",
]);
springapex_test_assert(
    array_keys($custom['options']) === ['Zinc', 'Black oxide', 'Passivated'],
    'Options for an operator-created select were not parsed (blank and padded lines included).'
);

// Nothing to heal for a custom field: it has no shipped default to fall back to.
$custom_empty = springapex_test_normalize(['id' => 'finish', 'label' => 'Finish', 'type' => 'select', 'options' => '']);
springapex_test_assert($custom_empty['options'] === [], 'A custom select invented options out of nowhere.');

// Non-select fields never carry options, whatever was posted.
$text = springapex_test_normalize(['id' => 'free_length', 'label' => 'Free length', 'options' => "should\nbe\nignored"]);
springapex_test_assert($text['type'] === 'text' && $text['options'] === [], 'A non-select field kept options.');

// Labels stay editable per option when they are supplied as value => label.
$labelled = springapex_test_normalize([
    'id' => 'finish',
    'label' => 'Finish',
    'type' => 'select',
    'options' => ['zinc' => 'Zinc plated', 'raw' => ''],
]);
springapex_test_assert($labelled['options'] === ['zinc' => 'Zinc plated', 'raw' => 'raw'], 'Option labels were not preserved.');

echo "form-select-options: mapped field edits, untouched saves, wiped rows, custom selects and non-select fields ok\n";
