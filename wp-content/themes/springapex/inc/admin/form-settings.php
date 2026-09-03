<?php
/**
 * 「表单设置」页：三个询盘表单的字段构建器、通知配置与 HTML 邮件模板。
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
    $code_editor_settings = wp_enqueue_code_editor(['type' => 'text/html']);
    if ($code_editor_settings !== false) {
        // 邮件模板使用两空格缩进；覆盖 WordPress 默认的 Tab 规则，避免编辑器
        // 把内置模板每一行都标成 "Please use tab for indentation"。
        $code_editor_settings['codemirror']['indentUnit'] = 2;
        $code_editor_settings['codemirror']['indentWithTabs'] = false;
        $code_editor_settings['htmlhint']['space-tab-mixed-disabled'] = 'space';
    }
    // 复用「网站内容」屏的可折叠卡片样式（.sa-card）：三个表单折叠展示与
    // brand 页一致。form-builder.css 依赖它、排在其后，可按需覆盖。
    wp_enqueue_style(
        'springapex-admin',
        SPRINGAPEX_URI . '/assets/css/admin.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-form-builder',
        SPRINGAPEX_URI . '/assets/css/form-builder.css',
        ['springapex-admin'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-form-builder',
        SPRINGAPEX_URI . '/assets/js/form-builder.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
    wp_enqueue_script(
        'springapex-mail-template-editor',
        SPRINGAPEX_URI . '/assets/js/mail-template-editor.js',
        $code_editor_settings === false ? [] : ['wp-codemirror'],
        SPRINGAPEX_VERSION,
        true
    );
    // 预览要和真正发出去的邮件一致：带图纸的邮件在渲染之后会补一段清单 + 取件
    // 入口（见 springapex_inquiry_mail_with_drawing_notice()）。那段 HTML 由 PHP
    // 生成后交给预览用，两边不各写一份，免得样式和文案各自漂移。
    $preview_drawings = 'compression-spring-drawing.pdf (3.0 MB)';
    $preview_inquiry_link = 'https://example.com/wp-admin/post.php?post=123&action=edit';
    wp_add_inline_script(
        'springapex-mail-template-editor',
        'window.springapexMailTemplateEditor = ' . wp_json_encode([
            'codeEditor' => $code_editor_settings,
            'defaultSubject' => springapex_inquiry_mail_default_subject(),
            'defaultBody' => springapex_inquiry_mail_default_body(),
            'previewDrawings' => $preview_drawings,
            'previewInquiryLink' => $preview_inquiry_link,
            'drawingNotice' => springapex_inquiry_mail_with_drawing_notice('', $preview_drawings, $preview_inquiry_link),
        ]) . ';',
        'before'
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

        // 常量锁定的输入为 disabled，不会出现在 POST；此时保留数据库备用值。
        if (array_key_exists('springapex_turnstile_site_key', $_POST)) {
            update_option('springapex_turnstile_site_key', sanitize_text_field((string) wp_unslash($_POST['springapex_turnstile_site_key'])), false);
        }
        if (array_key_exists('springapex_turnstile_secret', $_POST)) {
            update_option('springapex_turnstile_secret', sanitize_text_field((string) wp_unslash($_POST['springapex_turnstile_secret'])), false);
        }

        // 邮件通知模板：标题/正文清空并保存 = 恢复默认模板（删除存储值）。
        // 正文是 HTML：用 wp_kses_post 过滤（保留表格/内联样式，挡住脚本等危险标签）。
        $mail_subject = sanitize_text_field((string) wp_unslash($_POST['springapex_inquiry_mail_subject'] ?? ''));
        $mail_body = trim(wp_kses_post((string) wp_unslash($_POST['springapex_inquiry_mail_body'] ?? '')));
        if ($mail_subject !== '') {
            set_theme_mod('springapex_inquiry_mail_subject', $mail_subject);
        } else {
            remove_theme_mod('springapex_inquiry_mail_subject');
        }
        if ($mail_body !== '') {
            set_theme_mod('springapex_inquiry_mail_body', $mail_body);
        } else {
            remove_theme_mod('springapex_inquiry_mail_body');
        }

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
                // 选项的解析交给 springapex_normalize_form_field()：那里才知道
                // 字段最终生效的类型。映射字段的「类型」下拉是 disabled 的，
                // 不会随表单提交，在这里按 $row['type'] 判断会漏掉它们。
                $field = springapex_normalize_form_field($row, $defaults_by_id, $types);
                if ($field === null || isset($used_ids[$field['id']])) {
                    continue;
                }
                $used_ids[$field['id']] = true;
                $entry['fields'][] = $field;
            }

            // 仅锁定字段（姓名/邮箱/留言）缺失时补回——询盘成立的基础，
            // 不可删除；其余默认字段（含技术参数等系统映射字段）允许
            // 运营者删除，不回填（与前台渲染同一规则）。
            $locked_field_ids = springapex_form_locked_fields();
            foreach ($form_defaults['fields'] as $field) {
                if (!isset($used_ids[$field['id']]) && in_array($field['id'], $locked_field_ids, true)) {
                    $entry['fields'][] = $field;
                }
            }

            $schema[$form] = $entry;
        }
        update_option('springapex_form_schema', $schema, false);
        // 本次保存即当前结构版本：防止「首次保存删除默认字段 → 加载迁移
        // 视为版本落后而复活」的窗口（与加载侧的全新安装盖章双保险）。
        update_option('springapex_form_schema_version', SPRINGAPEX_FORM_SCHEMA_VERSION, false);
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
    <div class="wrap springapex-form-settings">
      <h1 class="wp-heading-inline">表单设置</h1>
      <p class="description sa-fs-intro">管理三个询盘表单的字段、通知与人机验证、以及提交后发给你的邮件模板。邮箱是询盘回复的基础，系统固定必填、不可删除；姓名可按需设为非必填或删除（提交留空时该询盘记为「匿名」）。产品表单的图纸与尺寸区块为固定结构，单独开关。</p>

      <?php if ($saved) : ?>
        <div class="notice notice-success is-dismissible"><p>设置已保存。</p></div>
      <?php endif; ?>
      <?php if ($error !== '') : ?>
        <div class="notice notice-error is-dismissible"><p><?php echo esc_html($error); ?></p></div>
      <?php endif; ?>

      <form method="post" class="sa-fs-form">
        <?php wp_nonce_field('springapex_save_form_settings', 'springapex_form_settings_nonce'); ?>

        <?php // 单表单、单保存；标签页只切换显示，隐藏面板里的输入照常提交。 ?>
        <nav class="nav-tab-wrapper sa-fs-tabs" role="tablist" aria-label="表单设置分区">
          <a href="#sa-fs-fields" class="nav-tab nav-tab-active" data-fs-tab="fields" role="tab" aria-selected="true">表单字段</a>
          <a href="#sa-fs-notify" class="nav-tab" data-fs-tab="notify" role="tab" aria-selected="false">通知与人机验证</a>
          <a href="#sa-fs-mail" class="nav-tab" data-fs-tab="mail" role="tab" aria-selected="false">邮件模板</a>
        </nav>

        <section class="sa-fs-panel is-active" id="sa-fs-fields" data-fs-panel="fields" role="tabpanel" aria-label="表单字段">
          <p class="description sa-fs-fields-hint">三个表单默认折叠，点标题展开你要编辑的那个。</p>
          <?php $form_num = 0; ?>
          <?php foreach ($form_meta as $form => $form_title) : ?>
            <?php
            $form_num++;
            $can_disable = isset($schema[$form]['enabled']);
            // 折叠摘要：字段数 + 启停 + 人机验证状态，收起时也能一眼认出这张卡。
            $meta_bits = [count((array) $schema[$form]['fields']) . ' 个字段'];
            if ($can_disable) {
                $meta_bits[] = !empty($schema[$form]['enabled']) ? '已启用' : '已停用';
            }
            $meta_bits[] = !empty($schema[$form]['turnstile']) ? '人机验证：开' : '人机验证：关';
            $card_meta = implode(' · ', $meta_bits);
            ?>
            <details class="sa-card springapex-form-builder" data-form-builder="<?php echo esc_attr($form); ?>" data-sa-form-card>
              <summary class="sa-card__head">
                <h2 class="sa-card__title">
                  <span class="sa-card__num" aria-hidden="true"><?php echo (int) $form_num; ?></span>
                  <span class="sa-card__name"><?php echo esc_html($form_title); ?></span>
                  <span class="sa-card__meta"><?php echo esc_html($card_meta); ?></span>
                </h2>
              </summary>
              <div class="sa-card__body">
                <div class="form-builder-flags">
                  <?php if ($can_disable) : ?>
                    <label><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][enabled]" value="1" <?php checked(!empty($schema[$form]['enabled'])); ?>> 启用这个表单</label>
                  <?php endif; ?>
                  <label><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][turnstile]" value="1" <?php checked(!empty($schema[$form]['turnstile'])); ?>> 人机验证（Turnstile）</label>
                  <?php if ($form === 'product') : ?>
                    <label class="sa-fs-flag--fixed"><input type="checkbox" name="schema[product][fixed][drawing]" value="1" checked disabled> 图纸上传模式</label>
                    <label class="sa-fs-flag--fixed"><input type="checkbox" name="schema[product][fixed][specs]" value="1" checked disabled> 尺寸参数模式</label>
                  <?php endif; ?>
                </div>

                <div class="form-builder-list" data-builder-list>
                  <?php foreach ($schema[$form]['fields'] as $index => $field) : ?>
                    <?php springapex_render_builder_field_row($form, (int) $index, $field, $types, $system_fields); ?>
                  <?php endforeach; ?>
                </div>
                <button type="button" class="button button-secondary" data-builder-add>+ 添加字段</button>
              </div>
            </details>
          <?php endforeach; ?>
        </section>

        <section class="sa-fs-panel" id="sa-fs-notify" data-fs-panel="notify" role="tabpanel" aria-label="通知与人机验证" hidden>
          <section class="sa-card">
            <header class="sa-card__head"><h2 class="sa-card__title">通知与人机验证</h2></header>
            <div class="sa-card__body">
            <p class="description">询盘提交后往哪里发通知、以及是否启用 Cloudflare Turnstile 拦截垃圾提交。密钥被 wp-config 常量覆盖时此处只读。</p>
            <table class="form-table" role="presentation">
              <tr>
                <th scope="row"><label for="springapex_inquiry_email">询盘收件邮箱</label></th>
                <td>
                  <input name="springapex_inquiry_email" id="springapex_inquiry_email" type="email" class="regular-text" value="<?php echo esc_attr($recipient); ?>" required>
                  <?php
                  $springapex_recipient_user = $recipient !== '' ? get_user_by('email', $recipient) : false;
                  $springapex_recipient_backend = $springapex_recipient_user instanceof WP_User
                      && user_can($springapex_recipient_user, 'edit_posts');
                  ?>
                  <p class="description">
                    <?php if ($springapex_recipient_backend) : ?>
                      这个邮箱是本站后台账号（<?php echo esc_html($springapex_recipient_user->user_login); ?>），图纸不夹带在邮件里，改到询盘详情页下载 —— 客户提交会快很多。
                    <?php else : ?>
                      这个邮箱没有对应的后台账号，图纸会照旧作为附件发出，收件人才拿得到。想让提交更快，可以改填一个有后台账号的邮箱。
                    <?php endif; ?>
                  </p>
                  <p class="description">每条询盘的通知邮件发送到这里；访客邮箱会设为回复地址。</p>
                </td>
              </tr>
              <tr>
                <th scope="row"><label for="springapex_turnstile_site_key">Turnstile 站点密钥</label></th>
                <td>
                  <?php if ($site_key_locked) : ?>
                    <input type="text" class="regular-text" value="已被常量覆盖" disabled>
                    <p class="description">由 wp-config 的 <code>SPRINGAPEX_TURNSTILE_SITE_KEY</code> 常量提供。</p>
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
                    <p class="description">由 wp-config 的 <code>SPRINGAPEX_TURNSTILE_SECRET</code> 常量提供。</p>
                  <?php else : ?>
                    <input name="springapex_turnstile_secret" id="springapex_turnstile_secret" type="password" class="regular-text code" value="<?php echo esc_attr($secret); ?>" autocomplete="new-password" placeholder="留空则不做人机校验">
                    <p class="description">留空则表单不做人机校验；两把密钥都填齐才会生效。</p>
                  <?php endif; ?>
                </td>
              </tr>
            </table>
            </div>
          </section>
        </section>

        <section class="sa-fs-panel" id="sa-fs-mail" data-fs-panel="mail" role="tabpanel" aria-label="邮件模板" hidden>
          <section class="sa-card">
            <header class="sa-card__head"><h2 class="sa-card__title">邮件通知模板</h2></header>
            <div class="sa-card__body">
            <p class="description">询盘提交后发给运营方的通知邮件，正文为 <strong>HTML</strong>：内置一套好看的默认模板，一般无需改动；也可直接编辑下面的 HTML 自定义。清空标题或正文并保存即恢复默认。</p>
            <table class="form-table" role="presentation">
              <tr>
                <th scope="row"><label for="springapex_inquiry_mail_subject">邮件标题</label></th>
                <td>
                  <input name="springapex_inquiry_mail_subject" id="springapex_inquiry_mail_subject" type="text" class="large-text" value="<?php echo esc_attr(springapex_inquiry_mail_subject()); ?>">
                  <p class="description">纯文本；支持下方占位符。建议保持简短，例如 <code>[{site_name}] {type} inquiry from {name}</code>。</p>
                </td>
              </tr>
              <tr>
                <th scope="row"><label for="springapex_inquiry_mail_body">邮件正文（HTML）</label></th>
                <td>
                  <div class="sa-mail-editor" data-mail-editor>
                    <div class="sa-mail-editor__toolbar">
                      <div class="sa-mail-editor__modes" role="tablist" aria-label="邮件模板查看模式">
                        <button type="button" id="sa-mail-mode-code" class="button is-active" data-mail-mode="code" role="tab" aria-controls="sa-mail-panel-code" aria-selected="true">HTML 代码</button>
                        <button type="button" id="sa-mail-mode-preview" class="button" data-mail-mode="preview" role="tab" aria-controls="sa-mail-panel-preview" aria-selected="false" tabindex="-1">邮件预览</button>
                      </div>
                      <button type="button" class="button button-secondary" data-mail-reset>载入默认模板</button>
                    </div>
                    <div id="sa-mail-panel-code" data-mail-panel="code" role="tabpanel" aria-labelledby="sa-mail-mode-code">
                      <textarea name="springapex_inquiry_mail_body" id="springapex_inquiry_mail_body" class="large-text code" rows="24" spellcheck="false"><?php echo esc_textarea(springapex_inquiry_mail_body()); ?></textarea>
                    </div>
                    <div id="sa-mail-panel-preview" class="sa-mail-preview" data-mail-panel="preview" role="tabpanel" aria-labelledby="sa-mail-mode-preview" hidden>
                      <div class="sa-mail-preview__subject"><span>预览主题</span><strong data-mail-preview-subject></strong></div>
                      <iframe title="询盘通知邮件预览" sandbox="allow-same-origin" referrerpolicy="no-referrer" data-mail-preview-frame></iframe>
                    </div>
                    <p class="description sa-mail-editor__help">推荐保留 <code>{fields_table}</code> 自动生成完整询盘信息表，<code>{message}</code> 显示客户留言。系统会另外生成纯文本版本并设置 Reply-To；无需写进模板。<code>{fields_table}</code> 里会列出图纸的文件名和大小。上面那个收件邮箱如果是本站后台账号，图纸就不再夹带在邮件里（大附件会拖慢客户提交、也容易被邮件网关拦下），改到后台询盘详情页下载；如果是没有后台账号的外部邮箱，图纸仍按原样作为附件发出，否则收件人就取不到了。预览使用示例数据，且在禁止脚本的沙箱中渲染。</p>
                    <?php
                    // 自定义过模板的站点不会跟着默认模板一起更新文案：图纸已经不随
                    // 邮件发送，模板里若还写着「附件」，收件人会去找一个不存在的附件。
                    // 这里只提示，不去改运营自己写的内容。
                    $springapex_mail_body_now = springapex_inquiry_mail_body();
                    $springapex_mail_mentions_attachment = $springapex_mail_body_now !== springapex_inquiry_mail_default_body()
                        && preg_match('/附件|attachment/iu', wp_strip_all_tags($springapex_mail_body_now)) === 1;
                    ?>
                    <?php if ($springapex_mail_mentions_attachment) : ?>
                      <p class="description sa-mail-editor__help" style="color:#8a6100;">
                        提醒：你的自定义模板里提到了「附件」，但图纸已经不随邮件发送了。建议把那句话改成让收件人到后台询盘详情页下载，或点「载入默认模板」取用新版文案。
                      </p>
                    <?php endif; ?>
                    <p class="sa-mail-editor__status" role="status" aria-live="polite" data-mail-status></p>
                  </div>
                </td>
              </tr>
              <tr>
                <th scope="row">可用占位符</th>
                <td class="springapex-mail-tokens">
                  <p class="description sa-mail-token-hint">点击占位符可插入到邮件正文当前光标位置。</p>
                  <?php foreach (springapex_inquiry_mail_placeholders() as $token => $token_desc) : ?>
                    <button type="button" class="springapex-mail-token" data-mail-token="<?php echo esc_attr($token); ?>" title="插入 <?php echo esc_attr($token); ?>">
                      <code><?php echo esc_html($token); ?></code><small><?php echo esc_html($token_desc); ?></small>
                    </button>
                  <?php endforeach; ?>
                </td>
              </tr>
            </table>
            </div>
          </section>
        </section>

        <div class="sa-fs-savebar">
          <?php submit_button('保存全部设置', 'primary', 'submit', false); ?>
          <span class="sa-fs-savebar__hint">一个保存入口会同时保存三个标签页中的全部改动。</span>
        </div>
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
    // 映射字段（电话/数量/技术参数等）类型锁定但可删；只有锁定字段不可删。
    $is_locked = in_array((string) $field['id'], springapex_form_locked_fields(), true);
    $row_id = 'springapex_field_' . uniqid();
    ?>
    <div class="builder-field" data-builder-field draggable="true">
      <div class="builder-field__head">
        <span class="builder-field__drag" aria-hidden="true">⠿</span>
        <strong><?php echo $field['label'] !== '' ? esc_html($field['label']) : '（新字段）'; ?></strong>
        <span class="builder-field__type"><?php echo esc_html($types[$field['type']]['label'] ?? ''); ?></span>
        <?php if ($is_locked) : ?>
          <span class="builder-field__system builder-field__system--locked" title="询盘成立与通知的基础字段，不可删除">系统字段</span>
        <?php elseif ($is_system) : ?>
          <span class="builder-field__system" title="映射到询盘专用列/参数的字段：可删除，但类型固定">映射字段</span>
        <?php endif; ?>
        <?php if (!$is_locked) : ?>
          <button type="button" class="builder-field__remove" data-builder-remove>删除</button>
        <?php else : ?>
          <span class="builder-field__locknote" aria-hidden="true">不可删除</span>
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
        <?php $required_locked = $is_system && (string) $field['id'] === 'email'; ?>
        <label class="builder-field__required<?php echo $required_locked ? ' is-locked' : ''; ?>"<?php echo $required_locked ? ' title="邮箱是询盘回复的基础，系统固定为必填"' : ''; ?>><input type="checkbox" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][required]" value="1" <?php checked(!empty($field['required'])); ?> <?php disabled($required_locked); ?>> 必填<?php echo $required_locked ? '<span class="builder-field__reqlock">固定</span>' : ''; ?></label>
        <input type="hidden" name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][id]" value="<?php echo esc_attr((string) $field['id']); ?>" data-field-id>
        <label class="builder-field__options" hidden>选项（每行一项）<textarea name="schema[<?php echo esc_attr($form); ?>][fields][<?php echo esc_attr((string) $index); ?>][options]" rows="3" data-field-options><?php echo esc_textarea(implode("\n", array_map('strval', array_keys($field['options'])))); ?></textarea></label>
      </div>
    </div>
    <?php
}
