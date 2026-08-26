<?php
/**
 * 表单字段 schema：三个询盘表单（quick / contact / product）的字段结构
 * 以数据形式存储，「表单设置」页编辑结构，前台与服务端按同一份
 * schema 渲染和校验。
 *
 * 字段对象结构：
 *   id        string  稳定标识（提交名 name="springapex_field_{id}"）
 *   label     string  前台显示名称（运营者可自由修改）
 *   type      text|email|tel|number|url|textarea|select|checkbox
 *   required  bool
 *   placeholder string
 *   options   array   select 类型的选项（value => label）
 *   width     full|half  布局提示（half 两个并排）
 *
 * 三个系统字段有固定语义，不可删除/改类型：
 *   name（姓名）、email（邮箱）、message（留言正文）——映射到询盘核心数据。
 * quick 表单另有 enabled（整体启停）；三表单各有 turnstile 开关。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** 字段类型元数据：标签、输入类型、校验器名。 */
function springapex_form_field_types(): array
{
    return [
        'text' => ['label' => '文本', 'input' => 'text', 'validate' => 'text'],
        'email' => ['label' => '邮箱', 'input' => 'email', 'validate' => 'email'],
        'tel' => ['label' => '电话', 'input' => 'tel', 'validate' => 'text'],
        'number' => ['label' => '数字', 'input' => 'number', 'validate' => 'number'],
        'url' => ['label' => '网址', 'input' => 'url', 'validate' => 'url'],
        'textarea' => ['label' => '多行文本', 'input' => 'textarea', 'validate' => 'textarea'],
        'select' => ['label' => '下拉选择', 'input' => 'select', 'validate' => 'text'],
        'checkbox' => ['label' => '勾选确认', 'input' => 'checkbox', 'validate' => 'checkbox'],
    ];
}

/**
 * 不可删除、缺失时强制补回的字段（询盘成立的基础），与映射表分离。
 * 邮箱与留言是询盘回复与内容的底线；姓名允许运营者按需删除/设为非必填
 * （提交留空时询盘标题回退为「匿名」，见 inc/contact.php）。
 */
function springapex_form_locked_fields(): array
{
    return ['email', 'message'];
}

/**
 * 系统字段：id 有固定语义（映射到询盘专用 meta/列），类型锁定。
 * 注意这只是「映射」集合——可删与否看 springapex_form_locked_fields()。
 */
function springapex_form_system_fields(): array
{
    return [
        'name' => '姓名（映射询盘联系人）',
        'email' => '邮箱（映射询盘邮箱）',
        'message' => '留言正文（映射询盘留言）',
        'phone' => '电话（映射询盘电话）',
        'company' => '公司（映射询盘公司）',
        'country' => '国家/地区（映射询盘国家）',
        'wire_diameter' => '线径（技术参数，产品页按产品类型换标签）',
        'outside_diameter' => '外径（技术参数，产品页按产品类型换标签）',
        'free_length' => '自由长度（技术参数，产品页按产品类型换标签）',
        'quantity' => '数量',
        'material' => '材料',
        'operating_environment' => '工作环境',
    ];
}

/** 三表单默认 schema：与历史表单一一对应，未配置时行为不变。 */
function springapex_form_schema_defaults(): array
{
    $common = static fn (array $overrides): array => array_merge([
        'id' => '',
        'label' => '',
        'type' => 'text',
        'required' => false,
        'placeholder' => '',
        'options' => [],
        'width' => 'full',
    ], $overrides);

    return [
        'quick' => [
            'enabled' => true,
            'turnstile' => true,
            'fields' => [
                $common(['id' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Your name']),
                $common(['id' => 'email', 'label' => 'Work email', 'type' => 'email', 'required' => true, 'placeholder' => 'name@company.com']),
                $common(['id' => 'message', 'label' => 'What spring or application do you need?', 'type' => 'textarea', 'required' => true, 'placeholder' => '']),
            ],
        ],
        'contact' => [
            // 顺序与线上现状一致：姓名 → 电话 → 公司 → 邮箱 → 国家 →
            // 技术参数（原「Add project details」折叠区，现按 schema 渲染）。
            'turnstile' => true,
            'fields' => [
                $common(['id' => 'name', 'label' => 'Name', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter your name']),
                $common(['id' => 'phone', 'label' => 'Phone or WhatsApp', 'type' => 'tel', 'required' => true, 'placeholder' => 'Enter phone or WhatsApp number']),
                $common(['id' => 'company', 'label' => 'Company', 'type' => 'text', 'required' => false, 'placeholder' => 'Enter your company name']),
                $common(['id' => 'email', 'label' => 'Work email', 'type' => 'email', 'required' => true, 'placeholder' => 'Enter your work email']),
                $common(['id' => 'country', 'label' => 'Country', 'type' => 'text', 'required' => true, 'placeholder' => 'Enter your country']),
                $common(['id' => 'wire_diameter', 'label' => 'Wire diameter', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 1.2 mm', 'width' => 'half']),
                $common(['id' => 'outside_diameter', 'label' => 'Outside diameter', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 12 mm', 'width' => 'half']),
                $common(['id' => 'free_length', 'label' => 'Free length', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 45 mm']),
                $common(['id' => 'quantity', 'label' => 'Quantity', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 10,000 pcs', 'width' => 'half']),
                $common(['id' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => false, 'placeholder' => 'Select material', 'width' => 'half', 'options' => [
                    'Music Wire' => 'Music Wire',
                    'Stainless Steel' => 'Stainless Steel',
                    'Carbon Steel' => 'Carbon Steel',
                    'Alloy or special material' => 'Alloy or special material',
                    'Need engineering recommendation' => 'Need engineering recommendation',
                ]]),
                $common(['id' => 'operating_environment', 'label' => 'Operating environment', 'type' => 'text', 'required' => false, 'placeholder' => 'Temperature, moisture, chemicals, indoor or outdoor use']),
                $common(['id' => 'message', 'label' => 'Additional project information', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Required load, working travel, material, cycle life, tolerances, or any other details.']),
            ],
        ],
        'product' => [
            'turnstile' => true,
            'fields' => [
                $common(['id' => 'email', 'label' => 'Work Email', 'type' => 'email', 'required' => true, 'placeholder' => 'name@company.com']),
                $common(['id' => 'wire_diameter', 'label' => 'Wire diameter', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 1.2 mm', 'width' => 'half']),
                $common(['id' => 'outside_diameter', 'label' => 'Outside diameter', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 12 mm', 'width' => 'half']),
                $common(['id' => 'free_length', 'label' => 'Free length', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 45 mm']),
                $common(['id' => 'quantity', 'label' => 'Quantity', 'type' => 'text', 'required' => false, 'placeholder' => 'e.g. 5,000 pcs', 'width' => 'half']),
                $common(['id' => 'material', 'label' => 'Material', 'type' => 'select', 'required' => false, 'placeholder' => 'Select material', 'width' => 'half', 'options' => [
                    'Music Wire' => 'Music Wire',
                    'Stainless Steel' => 'Stainless Steel',
                    'Carbon Steel' => 'Carbon Steel',
                    'Alloy or special material' => 'Alloy or special material',
                    'Need engineering recommendation' => 'Need engineering recommendation',
                ]]),
                $common(['id' => 'message', 'label' => 'Other requirements', 'type' => 'textarea', 'required' => false, 'placeholder' => 'Coating, load, end type, environment, tolerance, testing, or any additional notes.']),
            ],
        ],
    ];
}

/**
 * 完整 schema：默认值与 option 深度合并，逐字段规范化。
 * option 里多出的字段（用户新增）按声明类型归位；结构非法的字段丢弃。
 *
 * @return array<string, array<string, mixed>>
 */
// schema 结构版本：技术参数字段并入 schema 的那次演进。旧版本保存过的
// 站点靠它触发一次性迁移（见 springapex_form_schema()），迁移后不再补。
define('SPRINGAPEX_FORM_SCHEMA_VERSION', '2026-08-20-techspecs');

function springapex_form_schema(): array
{
    $stored = get_option('springapex_form_schema', []);
    if (!is_array($stored)) {
        return springapex_form_schema_defaults();
    }
    $schema_version = (string) get_option('springapex_form_schema_version', '');
    $migrated = false;
    // 全新安装：option 从未落库，走「未保存」分支不会写版本标记——若不在
    // 此处盖章，运营者首次保存（可能删除了默认字段）后会被迁移逻辑复活。
    if ($stored === [] && $schema_version !== SPRINGAPEX_FORM_SCHEMA_VERSION) {
        update_option('springapex_form_schema_version', SPRINGAPEX_FORM_SCHEMA_VERSION, false);
    }

    $types = springapex_form_field_types();
    $defaults = springapex_form_schema_defaults();
    $schema = [];
    // 本版本才纳入 schema 管理的字段（见下方迁移逻辑）。
    $migration_field_ids = ['wire_diameter', 'outside_diameter', 'free_length', 'quantity', 'material', 'operating_environment'];

    foreach ($defaults as $form => $form_defaults) {
        $entry = ['fields' => []];

        if (isset($form_defaults['enabled'])) {
            $entry['enabled'] = isset($stored[$form]['enabled'])
                ? (bool) $stored[$form]['enabled']
                : $form_defaults['enabled'];
        }
        $entry['turnstile'] = isset($stored[$form]['turnstile'])
            ? (bool) $stored[$form]['turnstile']
            : $form_defaults['turnstile'];

        $form_saved = isset($stored[$form]['fields']) && is_array($stored[$form]['fields']);
        $defaults_by_id = [];
        foreach ($form_defaults['fields'] as $field) {
            $defaults_by_id[$field['id']] = $field;
        }

        if (!$form_saved) {
            // 该表单从未在「表单设置」保存过：用完整默认字段，行为与线上现状一致。
            $entry['fields'] = $form_defaults['fields'];
            $schema[$form] = $entry;
            continue;
        }

        // 已配置：按存储顺序渲染，尊重运营者的增/删/改/排序。
        $seen = [];
        foreach (array_values($stored[$form]['fields']) as $field) {
            if (!is_array($field)) {
                continue;
            }
            $normalized = springapex_normalize_form_field($field, $defaults_by_id, $types);
            if ($normalized === null) {
                continue;
            }
            $seen[$normalized['id']] = true;
            $entry['fields'][] = $normalized;
        }
        // 仅锁定字段（姓名/邮箱/留言）在缺失时补回——它们是询盘成立的
        // 基础，不可删除；其余系统字段（phone/quantity/技术参数等）只做
        // meta 映射，运营者可删，删了不回填。
        $locked_fields = springapex_form_locked_fields();
        foreach ($form_defaults['fields'] as $field) {
            if (!isset($seen[$field['id']]) && in_array($field['id'], $locked_fields, true)) {
                $seen[$field['id']] = true;
                $entry['fields'][] = $field;
            }
        }

        // 一次性迁移：schema 结构版本落后时补齐字段并落版本标记。
        // 只补「本版本才纳入 schema 管理」的 6 个技术参数——PR #8 时代
        // 它们写死在模板外、schema 中不存在，「缺失」不是删除；
        // company/phone 等老字段在旧版本里就可删，缺失是升级前的合法
        // 删除，迁移必须保持原样。迁移后（版本标记已更新）删除照旧生效。
        if ($schema_version !== SPRINGAPEX_FORM_SCHEMA_VERSION) {
            foreach ($form_defaults['fields'] as $field) {
                if (!isset($seen[$field['id']]) && in_array($field['id'], $migration_field_ids, true)) {
                    $entry['fields'][] = $field;
                }
            }
            $migrated = true;
        }

        $schema[$form] = $entry;
    }

    if ($migrated) {
        update_option('springapex_form_schema', $schema, false);
        update_option('springapex_form_schema_version', SPRINGAPEX_FORM_SCHEMA_VERSION, false);
    }

    return $schema;
}

/**
 * 规范化单个字段：系统字段锁类型，自定义字段校验 id/类型唯一性。
 *
 * @param array<string, mixed> $field
 * @param array<string, array<string, mixed>> $defaults_by_id
 * @return array<string, mixed>|null
 */
function springapex_normalize_form_field(array $field, array $defaults_by_id, array $types): ?array
{
    $id = preg_replace('/[^a-z0-9_]/', '', strtolower(trim((string) ($field['id'] ?? ''))));
    if ($id === '' || strlen($id) > 60) {
        return null;
    }

    $is_system = array_key_exists($id, springapex_form_system_fields());
    $default = $defaults_by_id[$id] ?? null;

    // 系统字段未出现在默认表（如 contact 表没有 message）则拒绝——
    // 系统字段只能用于声明过它的表单。
    if ($is_system && $default === null) {
        return null;
    }

    $type = (string) ($field['type'] ?? 'text');
    if ($is_system) {
        $type = $default['type'];
    } elseif (!isset($types[$type])) {
        $type = 'text';
    }

    $label = trim((string) ($field['label'] ?? ''));
    if ($label === '') {
        $label = $is_system ? $default['label'] : 'Field';
    }

    $required = !empty($field['required']);
    // 邮箱强制必填（询盘无邮箱无法回复）；姓名由运营者自行决定是否必填。
    if ($is_system && $id === 'email') {
        $required = true;
    }

    $placeholder = trim((string) ($field['placeholder'] ?? ''));

    $options = [];
    if ($type === 'select') {
        $raw = $field['options'] ?? [];
        if (is_array($raw)) {
            foreach ($raw as $value => $option_label) {
                $value = trim((string) $value);
                if ($value !== '') {
                    $options[$value] = trim((string) $option_label) !== '' ? trim((string) $option_label) : $value;
                }
            }
        }
    }

    return [
        'id' => $id,
        'label' => $label,
        'type' => $type,
        'required' => $required,
        'placeholder' => $placeholder,
        'options' => $options,
        'width' => ($field['width'] ?? 'full') === 'half' ? 'half' : 'full',
    ];
}

/** 表单的启用态（无此语义的表单恒为 true）。 */
function springapex_form_enabled(string $form): bool
{
    $schema = springapex_form_schema();

    return !isset($schema[$form]['enabled']) || (bool) $schema[$form]['enabled'];
}

/** 该表单的 Turnstile 开关。 */
function springapex_form_turnstile_enabled(string $form): bool
{
    $schema = springapex_form_schema();

    return (bool) ($schema[$form]['turnstile'] ?? true);
}

/**
 * 固定语义字段：这些 id 即便被改名或调必填，提交时仍写回专用变量/meta
 * （name/email/message → post 标题/邮箱/正文；phone/company/country → 既有
 * 专用 meta）。询盘详情的「客户信息」列、列表过滤、邮件抬头都依赖这些键，
 * 故保证「未配置时行为与今天完全一致」。收集逻辑见 inc/contact.php 的 switch。
 * 其余（运营者新增的）字段落入 _springapex_custom_fields 桶，按 label 动态展示。
 */

/** 已知 id 的 autocomplete 提示（其余按类型推断，无则不输出）。 */
function springapex_form_field_autocomplete(array $field): string
{
    $by_id = [
        'name' => 'name',
        'email' => 'email',
        'phone' => 'tel',
        'company' => 'organization',
        'country' => 'country-name',
    ];
    if (isset($by_id[$field['id']])) {
        return $by_id[$field['id']];
    }

    return match ($field['type']) {
        'email' => 'email',
        'tel' => 'tel',
        'url' => 'url',
        default => '',
    };
}

/** 已知 id / 类型的输入长度上限，与历史写死值对齐。 */
function springapex_form_field_maxlength(array $field): int
{
    if ($field['type'] === 'textarea') {
        return 5000;
    }
    $by_id = ['name' => 120, 'email' => 190, 'phone' => 80, 'company' => 160, 'country' => 100];

    return $by_id[$field['id']] ?? ($field['type'] === 'email' ? 190 : 240);
}

/**
 * 渲染一个 schema 字段为 HTML（前台通用）。
 *
 * @param string $field_class 字段外层 label 的类名（contact/product 用 field，
 *                            快速询盘窗用 support-field），保持各表单原有样式。
 * @param string $extra_attr  追加到输入控件上的属性串（如首字段的自动聚焦钩子）。
 */
function springapex_render_form_schema_field(string $form, array $field, string $field_class = 'field', string $extra_attr = ''): void
{
    $types = springapex_form_field_types();
    $input = $types[$field['type']]['input'] ?? 'text';
    $name = 'springapex_field_' . $field['id'];
    $required = !empty($field['required']);
    $req_attr = $required ? ' required' : '';
    $placeholder = $field['placeholder'] !== '' ? ' placeholder="' . esc_attr($field['placeholder']) . '"' : '';
    $autocomplete = springapex_form_field_autocomplete($field);
    $ac_attr = $autocomplete !== '' ? ' autocomplete="' . esc_attr($autocomplete) . '"' : '';
    $maxlength = springapex_form_field_maxlength($field);
    $extra = $extra_attr !== '' ? ' ' . trim($extra_attr) : '';
    $classes = $field_class . ($field['width'] === 'half' ? ' is-half' : '');

    echo '<label class="' . esc_attr($classes) . '"><span>' . esc_html($field['label']) . ($required ? ' *' : '') . '</span>';
    if ($input === 'textarea') {
        echo '<textarea name="' . esc_attr($name) . '" rows="4" maxlength="' . (int) $maxlength . '"' . $placeholder . $ac_attr . $req_attr . $extra . '></textarea>';
    } elseif ($input === 'select') {
        echo '<select name="' . esc_attr($name) . '"' . $req_attr . $extra . '>';
        echo '<option value="">' . esc_html($field['placeholder'] !== '' ? $field['placeholder'] : 'Please select') . '</option>';
        foreach ($field['options'] as $value => $option_label) {
            echo '<option value="' . esc_attr((string) $value) . '">' . esc_html((string) $option_label) . '</option>';
        }
        echo '</select>';
    } elseif ($input === 'checkbox') {
        echo '<span class="field-checkbox"><input type="checkbox" name="' . esc_attr($name) . '" value="1"' . $req_attr . $extra . '></span>';
    } else {
        $inputmode = $field['type'] === 'number' ? ' inputmode="decimal"' : '';
        echo '<input type="' . esc_attr($input) . '" name="' . esc_attr($name) . '" maxlength="' . (int) $maxlength . '"' . $inputmode . $placeholder . $ac_attr . $req_attr . $extra . '>';
    }
    echo '</label>';
}

/** 某表单里被运营者标为必填的字段 id 列表（固定结构输入框对齐 schema 用）。 */
function springapex_form_required_ids(string $form): array
{
    $required = [];
    foreach ((springapex_form_schema()[$form]['fields'] ?? []) as $field) {
        if (!empty($field['required'])) {
            $required[] = (string) $field['id'];
        }
    }
    return $required;
}

/**
 * 渲染某个表单的整组 schema 字段，包在 .sa-schema-fields 网格里（full 跨两列、
 * half 跨一列）。前台三表单都调它替换原先写死的字段区。
 *
 * @param string $first_field_attr 附加到第一个字段控件上的属性（快速询盘窗传
 *                                 data-support-first-field 保留自动聚焦）。
 */
function springapex_render_form_schema_fields(string $form, string $field_class = 'field', string $first_field_attr = '', array $skip_ids = []): void
{
    $schema = springapex_form_schema();
    $fields = $schema[$form]['fields'] ?? [];
    if ($skip_ids !== []) {
        $fields = array_values(array_filter($fields, static fn (array $field): bool => !in_array($field['id'], $skip_ids, true)));
    }
    if ($fields === []) {
        return;
    }
    echo '<div class="sa-schema-fields" data-schema-fields>';
    foreach ($fields as $index => $field) {
        springapex_render_form_schema_field($form, $field, $field_class, $index === 0 ? $first_field_attr : '');
    }
    echo '</div>';
}
