<?php
/** SEO/TDK settings page and per-content meta boxes. */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_SEO_ADMIN_SLUG = 'springapex-content-seo';

add_action('admin_init', static function (): void {
    register_setting('springapex_seo', 'springapex_seo_settings', [
        'type' => 'array',
        'sanitize_callback' => 'springapex_sanitize_seo_settings',
        'default' => ['routes' => []],
    ]);
});

add_filter('option_page_capability_springapex_seo', static fn(): string => SPRINGAPEX_ADMIN_CAP);

add_action('admin_menu', static function (): void {
    add_submenu_page(
        SPRINGAPEX_ADMIN_SLUG,
        'SEO / TDK',
        'SEO / TDK',
        SPRINGAPEX_ADMIN_CAP,
        SPRINGAPEX_SEO_ADMIN_SLUG,
        'springapex_render_seo_settings_page'
    );
}, 20);

add_action('admin_enqueue_scripts', static function (string $hook): void {
    $screen = get_current_screen();
    $post_types = ['spring_product', 'spring_solution', 'spring_case', 'spring_news', 'page'];
    $is_seo_page = str_contains($hook, SPRINGAPEX_SEO_ADMIN_SLUG);
    $is_supported_editor = $screen && in_array((string) $screen->post_type, $post_types, true);
    if (!$is_seo_page && !$is_supported_editor) {
        return;
    }
    wp_enqueue_style(
        'springapex-seo-settings',
        SPRINGAPEX_URI . '/assets/css/seo-settings.css',
        $is_seo_page ? ['springapex-admin'] : [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-seo-settings',
        SPRINGAPEX_URI . '/assets/js/seo-settings.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
});

function springapex_render_seo_field(
    string $name,
    string $label,
    string $value,
    string $default,
    int $recommended,
    bool $textarea = false,
    string $help = '',
    bool $prefill = false
): void {
    // 预填模式：把内置默认当作真实值展示，运营在此基础上改而不是凭空填。
    // 与默认完全一致的提交值会在保存端归一化为空，内置默认不会被冻结。
    // 默认已在框里，就不再需要 placeholder 示例；非预填的 meta box 仍保留它。
    $show_placeholder = !$prefill || $default === '';
    if ($prefill && trim($value) === '' && $default !== '') {
        $value = $default;
    }
    ?>
    <label class="sa-seo-field">
      <span class="sa-seo-field__label"><?php echo esc_html($label); ?></span>
      <?php if ($textarea) : ?>
        <textarea name="<?php echo esc_attr($name); ?>" rows="3" maxlength="500" <?php if ($show_placeholder) : ?>placeholder="<?php echo esc_attr($default); ?>"<?php endif; ?> data-seo-field data-seo-recommended="<?php echo (int) $recommended; ?>" data-seo-default="<?php echo esc_attr($default); ?>"><?php echo esc_textarea($value); ?></textarea>
      <?php else : ?>
        <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" maxlength="500" <?php if ($show_placeholder) : ?>placeholder="<?php echo esc_attr($default); ?>"<?php endif; ?> data-seo-field data-seo-recommended="<?php echo (int) $recommended; ?>" data-seo-default="<?php echo esc_attr($default); ?>">
      <?php endif; ?>
      <span class="sa-seo-field__meta"><span data-seo-count>0</span> / 建议 <?php echo (int) $recommended; ?> 字符<?php echo $help !== '' ? ' · ' . esc_html($help) : ''; ?></span>
    </label>
    <?php
}

function springapex_render_seo_settings_page(): void
{
    if (!current_user_can(SPRINGAPEX_ADMIN_CAP)) {
        wp_die('你没有权限编辑 SEO 设置。', '权限不足', ['response' => 403]);
    }
    $saved = springapex_seo_saved_settings();
    $saved_routes = is_array($saved['routes'] ?? null) ? $saved['routes'] : [];
    ?>
    <div class="wrap sa-admin sa-seo-admin">
      <div class="sa-admin__header">
        <div>
          <h1 class="sa-admin__title">SEO / TDK</h1>
          <p class="sa-admin__intro">首页、静态页面和列表页在这里集中维护。产品、方案、案例和新闻详情请到对应内容的编辑页，在“SEO / TDK”面板中填写。</p>
        </div>
      </div>

      <?php settings_errors(); ?>

      <?php if (springapex_seo_external_plugin_active()) : ?>
        <div class="notice notice-warning inline"><p>检测到第三方 SEO 插件。为避免重复 title 和 meta 标签，主题前台 SEO 输出会自动停用；请只保留一套 SEO 管理来源。</p></div>
      <?php endif; ?>

      <div class="sa-notice">
        <strong>TDK：</strong>
        <span>Title 是搜索标题，Description 是搜索摘要候选。Keywords 仅用于兼容其他系统，Google 不使用 meta keywords 进行排名。</span>
      </div>

      <form method="post" action="options.php" class="sa-seo-form">
        <?php settings_fields('springapex_seo'); ?>
        <?php $index = 0; ?>
        <?php foreach (springapex_seo_route_definitions() as $route => $definition) : ?>
          <?php
          $index++;
          $row = is_array($saved_routes[$route] ?? null) ? $saved_routes[$route] : [];
          $title = (string) ($row['title'] ?? '');
          $description = (string) ($row['description'] ?? '');
          $keywords = (string) ($row['keywords'] ?? '');
          $customized = $title !== '' || $description !== '' || $keywords !== '';
          $base = 'springapex_seo_settings[routes][' . $route . ']';
          ?>
          <details class="sa-card sa-seo-route" id="sa-seo-<?php echo esc_attr($route); ?>" data-sa-card data-seo-scope<?php echo $index === 1 ? ' open' : ''; ?>>
            <summary class="sa-card__head">
              <h2 class="sa-card__title">
                <span class="sa-card__num" aria-hidden="true"><?php echo (int) $index; ?></span>
                <span class="sa-card__name"><?php echo esc_html((string) $definition['label']); ?></span>
                <span class="sa-card__meta"><?php echo esc_html((string) $definition['path']); ?> · <?php echo $customized ? '已自定义' : '使用内置默认'; ?></span>
              </h2>
            </summary>
            <div class="sa-card__body">
              <div class="sa-seo-fields">
                <?php springapex_render_seo_field($base . '[title]', 'Title 搜索标题', $title, (string) $definition['title'], 60, false, '', true); ?>
                <?php springapex_render_seo_field($base . '[description]', 'Description 元描述', $description, (string) $definition['description'], 160, true, '', true); ?>
                <?php springapex_render_seo_field($base . '[keywords]', 'Keywords 关键词', $keywords, '', 200, false, '使用英文逗号分隔，可留空'); ?>
              </div>
              <div class="sa-seo-preview" aria-label="搜索结果预览">
                <span class="sa-seo-preview__url"><?php echo esc_html(home_url((string) $definition['path'])); ?></span>
                <strong data-seo-preview-title><?php echo esc_html($title !== '' ? $title : (string) $definition['title']); ?></strong>
                <p data-seo-preview-description><?php echo esc_html($description !== '' ? $description : (string) $definition['description']); ?></p>
              </div>
            </div>
          </details>
        <?php endforeach; ?>

        <div class="sa-seo-savebar">
          <?php submit_button('保存全部 SEO 设置', 'primary', 'submit', false); ?>
          <span>输入框已预填主题内置默认；改成新内容即生效，改回与默认一致或清空则恢复使用内置默认。</span>
        </div>
      </form>
    </div>
    <?php
}

add_action('add_meta_boxes', static function (): void {
    foreach (['spring_product', 'spring_solution', 'spring_case', 'spring_news'] as $post_type) {
        add_meta_box('springapex-seo-meta', 'SEO / TDK', 'springapex_render_seo_meta_box', $post_type, 'normal', 'default');
    }
});

add_action('add_meta_boxes_page', static function (WP_Post $post): void {
    if (springapex_seo_managed_route_for_page($post) !== '') {
        return;
    }
    add_meta_box('springapex-seo-meta', 'SEO / TDK', 'springapex_render_seo_meta_box', 'page', 'normal', 'default');
});

function springapex_render_seo_meta_box(WP_Post $post): void
{
    wp_nonce_field('springapex_save_seo_meta', 'springapex_seo_meta_nonce');
    $title = (string) get_post_meta($post->ID, '_springapex_seo_title', true);
    $description = (string) get_post_meta($post->ID, '_springapex_seo_description', true);
    $keywords = (string) get_post_meta($post->ID, '_springapex_seo_keywords', true);
    // 预填用的回退值与前台 springapex_seo_post_values() 的解析顺序一致：
    // 先摘要/正文清洗，为空时再用按类型生成的文案。
    $fallback_description = springapex_seo_clean_description(
        $post->post_excerpt !== '' ? $post->post_excerpt : $post->post_content
    );
    if ($fallback_description === '') {
        $fallback_description = springapex_seo_post_fallback_description($post);
    }
    ?>
    <div class="springapex-seo-meta" data-seo-scope>
      <p>输入框已预填自动回退值；改成新内容即生效，改回与回退值一致或清空则恢复自动回退。</p>
      <?php springapex_render_seo_field('springapex_seo_title', 'Title 搜索标题', $title, get_the_title($post), 60, false, '', true); ?>
      <?php springapex_render_seo_field('springapex_seo_description', 'Description 元描述', $description, $fallback_description, 160, true, '', true); ?>
      <?php springapex_render_seo_field('springapex_seo_keywords', 'Keywords 关键词', $keywords, '', 200, false, 'Google 不使用，可留空'); ?>
      <input type="hidden" name="springapex_seo_prefilled[title]" value="<?php echo esc_attr(get_the_title($post)); ?>">
      <input type="hidden" name="springapex_seo_prefilled[description]" value="<?php echo esc_attr($fallback_description); ?>">
      <div class="sa-seo-preview" aria-label="搜索结果预览">
        <span class="sa-seo-preview__url"><?php echo esc_html(get_permalink($post)); ?></span>
        <strong data-seo-preview-title><?php echo esc_html($title !== '' ? $title : get_the_title($post)); ?></strong>
        <p data-seo-preview-description><?php echo esc_html($description !== '' ? $description : $fallback_description); ?></p>
      </div>
    </div>
    <?php
}

add_action('save_post', static function (int $post_id, WP_Post $post): void {
    if (!in_array($post->post_type, ['spring_product', 'spring_solution', 'spring_case', 'spring_news', 'page'], true)) {
        return;
    }
    if ($post->post_type === 'page' && springapex_seo_managed_route_for_page($post) !== '') {
        return;
    }
    if (
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        || wp_is_post_revision($post_id)
        || !current_user_can('edit_post', $post_id)
        || !isset($_POST['springapex_seo_meta_nonce'])
        || !wp_verify_nonce(
            sanitize_text_field((string) wp_unslash($_POST['springapex_seo_meta_nonce'])),
            'springapex_save_seo_meta'
        )
    ) {
        return;
    }

    // 表单预填了回退值（文章标题 / 摘要正文生成的描述），原样提交回来时
    // 归一化为空、不写 meta：留空才意味着用自动回退，正文后续更新时前台
    // 描述能跟着内容走，不被编辑时的快照冻住。比较基准用 hidden 字段里
    // 本次渲染实际预填的值（渲染时生成，保存时可能已变化），所见即所比。
    $prefilled = is_array($_POST['springapex_seo_prefilled'] ?? null)
        ? wp_unslash($_POST['springapex_seo_prefilled'])
        : [];
    $fields = [
        '_springapex_seo_title' => ['post' => 'springapex_seo_title', 'textarea' => false, 'fallback' => (string) ($prefilled['title'] ?? '')],
        '_springapex_seo_description' => ['post' => 'springapex_seo_description', 'textarea' => true, 'fallback' => (string) ($prefilled['description'] ?? '')],
        '_springapex_seo_keywords' => ['post' => 'springapex_seo_keywords', 'textarea' => false, 'fallback' => ''],
    ];
    foreach ($fields as $meta_key => $field) {
        $raw = (string) wp_unslash($_POST[$field['post']] ?? '');
        $value = $field['textarea'] ? sanitize_textarea_field($raw) : sanitize_text_field($raw);
        $value = trim($value);
        $fallback = trim((string) sanitize_text_field($field['fallback']));
        if ($value !== '' && $value === $fallback) {
            $value = '';
        }
        if ($value === '') {
            delete_post_meta($post_id, $meta_key);
        } else {
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}, 10, 2);
