<?php
/**
 * 「表单设置」页：三个询盘表单的字段构建器。
 *
 * 每个表单一段可编辑的字段卡片列表（类型/名称/必填/占位/宽度/选项、
 * 拖拽排序、新增任意字段、删除自定义字段），加上整体启停与 Turnstile
 * 开关。保存为 springapex_form_schema option，前台渲染与服务端校验
 * 都从 inc/form-schema.php 读同一份数据。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_menu', static function (): void {
    add_submenu_page(
        'edit.php?post_type=spring_inquiry',
        '表单设置',
        '表单设置',
        'manage_options',
        'springapex-form-settings',
        'springapex_render_form_settings_page'
    );
});

add_action('admin_enqueue_scripts', static function (string $hook): void {
    if ($hook !== 'spring_inquiry_page_springapex-form-settings') {
        return;
    }
    wp_enqueue_style(
        'springapex-form-builder',
        SPRINGAPEX_URI . '/assets/css/form-builder.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-form-builder',
        SPRINGAPEX_URI . '/assets/js/form-builder.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
});

function springapex_render_form_settings_page(): void
{
    $saved = false;
    $error = '';

    if (
        isset($_POST['springapex_form_settings_nonce']) &&
        wp_verify_nonce(sanitize_text_field((string) wp_unslash($_POST['springapex_form_settings_nonce'])), 'springapex_save_form_settings') &&
        current_user_can('manage_options')
    ) {
        $recipient = sanitize_email((string) wp_unslash($_POST['springapex_inquiry_email'] ?? ''));
        if ($recipient === '' || !is_email($recipient)) {
            $error = '请输入有效的收件邮箱。';
        } else {
            set_theme_mod('springapex_inquiry_email', $recipient);

        update_option('springapex_turnstile_site_key', sanitize_text_field((string) wp_unslash($_POST['springapex_turnstile_site_key'] ?? '')), false);
        update_option('springapex_turnstile_secret', sanitize_text_field((string) wp_unslash($_POST['springapex_turnstile_secret'] ?? '')), false);

        // 三表单 schema：把提交的字段卡片数组规范化后整体入库。
        $types = springapex_form_field_types();
        $defaults = springapex_form_schema_defaults();
        $system_field_ids = springapex_form_system_fields();
        $submitted = isset($_POST['schema']) && is_array($_POST['schema']) ? wp_unslash($_POST['schema']) : [];
        $schema = [];
        foreach ($defaults as $form => $form_defaults) {
            $entry = ['fields' => []];
            if (isset($form_defaults['enabled'])) {
                $entry['enabled'] = !empty($submitted[$form]['enabled']);
            }
            $entry['turnstile'] = !empty($submitted[$form]['turnstile']);

            $defaults_by_id = [];
            foreach ($form_defaults['fields'] as $field) {
                $defaults_by_id[$field['id']] = $field;
            }

            $rows = isset($submitted[$form]['fields']) && is_array($submitted[$form]['fields'])
                ? array_values($submitted[$form]['fields'])
                : [];
            $used_ids = [];
            foreach ($rows as $row) {
                if (!is_array($row)) {
                    continue;
                }
                // select 选项以 textarea 一行一项提交，转回关联数组。
                if (($row['type'] ?? '') === 'select') {
                    $options = [];
                    foreach (preg_split('/\r\n|\r|\n/', (string) ($row['options'] ?? '')) ?: [] as $line) {
                        $line = trim($line);
                        if ($line !== '') {
                            $options[$line] = $line;
                        }
                    }
                    $row['options'] = $options;
                }
                $field = springapex_normalize_form_field($row, $defaults_by_id, $types);
                if ($field === null || isset($used_ids[$field['id']])) {
                    continue;
                }
                $used_ids[$field['id']] = true;
                $entry['fields'][] = $field;
            }

            // 仅系统字段（姓名/邮箱/留言）缺失时补回——询盘成立的基础，不可删除；
            // 其余默认字段允许运营者删除，不回填（与前台渲染同一规则）。
            foreach ($form_defaults['fields'] as $field) {
                if (!isset($used_ids[$field['id']]) && array_key_exists($field['id'], $system_field_ids)) {
                    $entry['fields'][] = $field;
                }
            }

            $schema[$form] = $entry;
        }
        update_option('springapex_form_schema', $schema, false);
        // 上一代配置退役，避免两套真相。
        delete_option('springapex_form_config');
        delete_option('springapex_form_fields');
        $saved = true;
        }
    }

    $recipient = (string) get_theme_mod('springapex_inquiry_email', (string) get_option('admin_email'));
    $site_key = (string) get_option('springapex_turnstile_site_key', '');
    $secret = (string) get_option('springapex_turnstile_secret', '');
    $site_key_locked = defined('SPRINGAPEX_TURNSTILE_SITE_KEY');
    $secret_locked = defined('SPRINGAPEX_TURNSTILE_SECRET') && is_string(SPRINGAPEX_TURNSTILE_SECRET) && SPRINGAPEX_TURNSTILE_SECRET !== '';

    $schema = springapex_form_schema();
    $types = springapex_form_field_types();
    $system_fields = springapex_form_system_fields();
    $form_meta = [
        'quick' => '快速询盘（右下角浮动窗）',
        'contact' => '联系页主表单',
        'product' => '产品 / 能力页表单',
    ];
    ?>
    <div class="wrap">
      <h1>表单设置</h1>
      <p class="description">三个询盘表单的字段结构：可添加/删除/改名/排序、设置类型与必填。姓名与邮箱是询盘成立的基础（不可删除）；产品表单的图纸与尺寸区块属固定结构，在字段列表之外单独开关。</p>

      <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible"><p>设置已保存。</p></div>
      <?php endif; ?>
      <?php if ($error !== '') : ?>
        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error); ?></p></div>
      <?php endif; ?>

      <form method="post">
        <?php wp_nonce_field('springapex_save_form_settings', 'springapex_form_settings_nonce'); ?>

        <?php foreach ($form_meta as $form => $form_title) : ?>
          <?php $can_disable = isset($schema[$form]['enabled']); ?>
          <div class="springapex-form-builder" data-form-builder="<?php echo esc_attr($form); ?>">
            <h2 class="title"><?php echo esc_html($form_title); ?></h2>
            <div class="form-builder-flags">
              <?php if ($can_disable) : ?>
                <label><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][enabled]" value="1" <?php checked(!empty($schema[$form]['enabled'])); ?>> 启用这个表单</label>
              <?php endif; ?>
              <label><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][turnstile]" value="1" <?php checked(!empty($schema[$form]['turnstile'])); ?>> 人机验证（Turnstile）</label>
              <?php if ($form === 'product') : ?>
                <label><input type="checkbox" name="schema[product][fixed][drawing]" value="1" checked disabled> 图纸上传模式</label>
                <label><input type="checkbox" name="schema[product][fixed][specs]" value="1" checked disabled> 尺寸参数模式</label>
              <?php endif; ?>
            </div>

            <div class="form-builder-list" data-builder-list>
              <?php foreach ($schema[$form]['fields'] as $index => $field) : ?>
                <?php springapex_render_builder_field_row($form, (int) $index, $field, $types, $system_fields); ?>
              <?php endforeach; ?>
            </div>
            <button type="button" class="button button-secondary" data-builder-add>添加字段</button>
          </div>
        <?php endforeach; ?>

        <h2 class="title">通知与人机验证</h2>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="springapex_inquiry_email">询盘收件邮箱</label></th>
            <td>
              <input name="springapex_inquiry_email" id="springapex_inquiry_email" type="email" class="regular-text" value="<?php echo esc_attr($recipient); ?>" required>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="springapex_turnstile_site_key">Turnstile 站点密钥</label></th>
            <td>
              <?php if ($site_key_locked) : ?>
                <input type="text" class="regular-text" value="已被常量覆盖" disabled>
              <?php else : ?>
                <input name="springapex_turnstile_site_key" id="springapex_turnstile_site_key" type="text" class="regular-text code" value="<?php echo esc_attr($site_key); ?>" placeholder="留空用内置站点密钥">
              <?php endif; ?>
            </td>
          </tr>
          <tr>
            <th scope="row"><label for="springapex_turnstile_secret">Turnstile 密钥</label></th>
            <td>
              <?php if ($secret_locked) : ?>
                <input type="text" class="regular-text" value="已被常量覆盖" disabled>
              <?php else : ?>
                <input name="springapex_turnstile_secret" id="springapex_turnstile_secret" type="password" class="regular-text code" value="<?php echo esc_attr($secret); ?>" autocomplete="new-password" placeholder="留空则不做人机校验">
              <?php endif; ?>
            </td>
          </tr>
        </table>
        <?php submit_button('保存设置'); ?>
      </form>

      <template id="springapex-builder-row-template" data-template-form="__FORM__">
        <?php springapex_render_builder_field_row('__FORM__', 0, [
            'id' => '',
            'label' => '',
            'type' => 'text',
            'required' => false,
            'placeholder' => '',
            'options' => [],
            'width' => 'full',
        ], springapex_form_field_types(), springapex_form_system_fields()); ?>
      </template>
    </div>
    <?php
}

/** 一张字段编辑卡。 */
function springapex_render_builder_field_row(string $form, int $index, array $field, array $types, array $system_fields): void
{
    $is_system = array_key_exists((string) $field['id'], $system_fields);
    $row_id = 'springapex_field_' . uniqid();
    ?>
    <div class="builder-field" data-builder-field draggable="true">
      <div class="builder-field__head">
        <span class="builder-field__drag" aria-hidden="true">⠿</span>
        <strong><?php echo $field['label'] !== '' ? esc_html($field['label']) : '（新字段）'; ?></strong>
        <span class="builder-field__type"><?php echo esc_html($types[$field['type']]['label'] ?? ''); ?></span>
        <?php if ($is_system) : ?><span class="builder-field__system">系统字段</span><?php endif; ?>
        <?php if (!$is_system) : ?>
          <button type="button" class="builder-field__remove" data-builder-remove>删除</button>
        <?php endif; ?>
      </div>
      <div class="builder-field__grid">
        <label>名称<input type="text" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][label]" value="<?php echo esc_attr((string) $field['label']); ?>" data-field-label></label>
        <label>类型
          <select name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][type]" data-field-type <?php disabled($is_system); ?>>
            <?php foreach ($types as $type_key => $type_meta) : ?>
              <option value="<?php echo esc_attr($type_key); ?>" <?php selected($field['type'], $type_key); ?>><?php echo esc_html($type_meta['label']); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>占位提示<input type="text" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][placeholder]" value="<?php echo esc_attr((string) $field['placeholder']); ?>"></label>
        <label>宽度
          <select name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][width]">
            <option value="full" <?php selected($field['width'], 'full'); ?>>整行</option>
            <option value="half" <?php selected($field['width'], 'half'); ?>>半行</option>
          </select>
        </label>
        <label class="builder-field__required"><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][required]" value="1" <?php checked(!empty($field['required'])); ?> <?php disabled($is_system && in_array($field['id'], ['name', 'email'], true)); ?>> 必填</label>
        <input type="hidden" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][id]" value="<?php echo esc_attr((string) $field['id']); ?>" data-field-id>
        <label class="builder-field__options" hidden>选项（每行一项）<textarea name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][options]" rows="3" data-field-options><?php echo esc_textarea(implode("\n", array_map('strval', array_keys($field['options'])))); ?></textarea></label>
      </div>
    </div>
    <?php
}
