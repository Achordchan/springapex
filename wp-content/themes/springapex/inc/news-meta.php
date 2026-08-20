<?php
/**
 * 新闻条目的「前台显示设置」。
 *
 * Three values the news templates read but that used to exist only in the seed
 * array, keyed by slug: the date caption, the category label, and the related
 * products in the sidebar. None of them could be changed, and a newly written
 * article got none of them at all.
 *
 * The product control lives in inc/product-picker.php, shared with the industry
 * solution meta box.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_NEWS_PRODUCTS_META = '_springapex_news_products';
const SPRINGAPEX_NEWS_DATE_LABEL_META = '_springapex_news_date_label';
const SPRINGAPEX_NEWS_CATEGORY_META = '_springapex_news_category';

add_action('add_meta_boxes_spring_news', static function (): void {
    add_meta_box(
        'springapex-news-display',
        '新闻内容',
        'springapex_render_news_display_meta_box',
        'spring_news',
        'normal',
        'high'
    );
});

function springapex_render_news_display_meta_box(WP_Post $post): void
{
    $post_id = (int) $post->ID;
    wp_nonce_field('springapex_save_news_display', 'springapex_news_display_nonce');

    wp_enqueue_media();
    wp_enqueue_style(
        'springapex-product-panel',
        SPRINGAPEX_URI . '/assets/css/product-panel.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-product-panel',
        SPRINGAPEX_URI . '/assets/js/product-panel.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
    ?>
    <div class="sa-pp" data-sa-product-panel>
      <div class="sa-pp__col">
        <nav class="sa-pp__tabs" role="tablist" aria-label="<?php esc_attr_e('新闻内容', 'springapex'); ?>">
          <button type="button" role="tab" class="sa-pp__tab is-active" data-pp-tab="basic"><?php esc_html_e('基础信息', 'springapex'); ?></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="products"><?php esc_html_e('相关产品', 'springapex'); ?></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="content"><?php esc_html_e('正文', 'springapex'); ?></button>
        </nav>
      </div>

      <div class="sa-pp__main">
        <section class="sa-pp__panel is-active" data-pp-panel="basic" role="tabpanel">
          <h2 class="sa-pp__title"><?php esc_html_e('基础信息', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('文章名用页面上方的标题填写；封面用右侧的特色图像。下面的文字会原样出现在前台，请用英文填写。', 'springapex'); ?></p>

          <div class="sa-pp__field">
            <label><?php esc_html_e('日期', 'springapex'); ?></label>
            <?php
            $date_label = springapex_news_date_label_meta($post_id);
            [$date_start, $date_end] = springapex_news_parse_date_label($date_label);
            $date_custom = $date_start === '' && $date_label !== '' ? $date_label : '';
            ?>
            <div class="sa-pp__date-row">
              <label class="sa-pp__date-cell">
                <span><?php esc_html_e('开始日期', 'springapex'); ?></span>
                <input type="date" name="springapex_news_date_start" value="<?php echo esc_attr($date_start); ?>">
              </label>
              <label class="sa-pp__date-cell">
                <span><?php esc_html_e('结束日期（跨天活动选）', 'springapex'); ?></span>
                <input type="date" name="springapex_news_date_end" value="<?php echo esc_attr($date_end); ?>">
              </label>
            </div>
            <details class="sa-pp__date-custom"<?php echo $date_custom !== '' ? ' open' : ''; ?>>
              <summary><?php esc_html_e('需要特殊写法？直接填写显示文字', 'springapex'); ?></summary>
              <input class="widefat" type="text" id="springapex-news-date-label"
                  name="springapex_news_date_label"
                  placeholder="<?php esc_attr_e('留空则按上面选择的日期显示', 'springapex'); ?>"
                  value="<?php echo esc_attr($date_custom); ?>">
            </details>
            <p class="description">留空就按发布日期显示。日期会显示成
                <code>June 17, 2024</code>（跨天自动写成 <code>June 17–20, 2024</code>）。</p>
          </div>

          <div class="sa-pp__field">
            <label for="springapex-news-category"><?php esc_html_e('分类标签', 'springapex'); ?></label>
            <input class="widefat" type="text" id="springapex-news-category"
                name="springapex_news_category"
                value="<?php echo esc_attr(springapex_news_category_meta($post_id)); ?>">
            <p class="description">显示在新闻卡片和详情页顶部的小标签。留空就用右边「News type」里选的分类名。</p>
          </div>
        </section>

        <section class="sa-pp__panel" data-pp-panel="products" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('相关产品', 'springapex'); ?></h2>
          <?php
          springapex_render_product_picker(
              'springapex_news_products',
              springapex_news_products_meta($post_id),
              '勾选的产品会显示在这篇新闻详情页右侧的「Related products」里。不勾就不显示这一块。'
          );
          ?>
        </section>

        <section class="sa-pp__panel" data-pp-panel="content" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('正文', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('这篇新闻的正文内容，显示在详情页。', 'springapex'); ?></p>
          <div class="sa-pp__editor" data-pp-editor>
            <?php wp_editor($post->post_content, 'content', [
                'textarea_rows' => 14,
                'media_buttons' => true,
                'teeny' => false,
            ]); ?>
          </div>
        </section>
      </div>
    </div>
    <?php
}

/**
 * Stored value, or the seed's while the article has never been saved, so the
 * pre-built articles keep what they shipped with. An article that *has* been
 * saved with the field cleared genuinely has no label.
 */
function springapex_news_meta_or_seed(int $post_id, string $meta_key, string $seed_key): string
{
    if (metadata_exists('post', $post_id, $meta_key)) {
        return (string) get_post_meta($post_id, $meta_key, true);
    }

    $seed = springapex_news_seed((string) get_post_field('post_name', $post_id)) ?? [];

    return (string) ($seed[$seed_key] ?? '');
}

function springapex_news_date_label_meta(int $post_id): string
{
    return springapex_news_meta_or_seed($post_id, SPRINGAPEX_NEWS_DATE_LABEL_META, 'date_label');
}

function springapex_news_category_meta(int $post_id): string
{
    return springapex_news_meta_or_seed($post_id, SPRINGAPEX_NEWS_CATEGORY_META, 'category');
}

function springapex_news_products_meta(int $post_id): array
{
    if (!metadata_exists('post', $post_id, SPRINGAPEX_NEWS_PRODUCTS_META)) {
        $seed = springapex_news_seed((string) get_post_field('post_name', $post_id)) ?? [];
        return array_values(array_filter(array_map('strval', (array) ($seed['products'] ?? []))));
    }

    $stored = get_post_meta($post_id, SPRINGAPEX_NEWS_PRODUCTS_META, true);

    return is_array($stored) ? array_values(array_filter(array_map('strval', $stored))) : [];
}

/**
 * 把已存的显示文字解析回 [开始日, 结束日]（Y-m-d），供日期选择器回填。
 * 认识 "June 17, 2024"、"June 17–20, 2024"、"June 30 – July 3, 2024"
 * 三种本站格式；认不出的原样留给「自定义文字」输入框。
 *
 * @return array{0: string, 1: string}
 */
function springapex_news_parse_date_label(string $label): array
{
    $label = trim($label);
    if ($label === '') {
        return ['', ''];
    }

    $to_day = static function (string $text): string {
        $time = strtotime($text);
        return $time !== false ? gmdate('Y-m-d', $time) : '';
    };

    // January 1, 2026 – January 2, 2027（显式跨年）
    if (preg_match('/^(\p{L}+ \d{1,2}),\s*(\d{4})\s*[–-]\s*(\p{L}+ \d{1,2}),\s*(\d{4})$/u', $label, $m)) {
        return [$to_day($m[1] . ', ' . $m[2]), $to_day($m[3] . ', ' . $m[4])];
    }

    // June 30 – July 3, 2024（跨月）；旧格式若开始日晚于结束日，则为跨年范围。
    if (preg_match('/^(\p{L}+ \d{1,2})\s*[–-]\s*(\p{L}+ \d{1,2}),\s*(\d{4})$/u', $label, $m)) {
        $end = $to_day($m[2] . ', ' . $m[3]);
        $start = $to_day($m[1] . ', ' . $m[3]);
        // 显示文字只在末尾保留结束年份；例如 December 31 – January 2, 2027
        // 的开始年份应回推为 2026。
        if ($start !== '' && $end !== '' && $start > $end) {
            $start = $to_day($m[1] . ', ' . ((int) $m[3] - 1));
        }
        return [$start, $end];
    }
    // June 17–20, 2024（同月省略写法）
    if (preg_match('/^(\p{L}+) (\d{1,2})\s*[–-]\s*(\d{1,2}),\s*(\d{4})$/u', $label, $m)) {
        return [$to_day($m[1] . ' ' . $m[2] . ', ' . $m[4]), $to_day($m[1] . ' ' . $m[3] . ', ' . $m[4])];
    }
    // June 17, 2024（单日）
    if (preg_match('/^(\p{L}+ \d{1,2}),?\s+(\d{4})$/u', $label)) {
        return [$to_day($label), ''];
    }

    return ['', ''];
}

/**
 * 由提交内容生成日期显示文字：自定义文字优先，否则按日期选择器的
 * 开始/结束日期格式化（单日 "June 17, 2024"、同月跨天 "June 17–20, 2024"、
 * 跨月 "June 30 – July 3, 2024"）。都没有则留空，前台回落发布日期。
 */
function springapex_news_date_label_from_submission(): string
{
    $custom = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_news_date_label'] ?? ''));
    if ($custom !== '') {
        return $custom;
    }

    $start = springapex_admin_request_scalar($_POST['springapex_news_date_start'] ?? '');
    $end = springapex_admin_request_scalar($_POST['springapex_news_date_end'] ?? '');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start)) {
        return '';
    }

    $start_time = strtotime($start);
    if ($start_time === false) {
        return '';
    }
    if ($end === '' || $end === $start || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end)) {
        return gmdate('F j, Y', $start_time);
    }

    $end_time = strtotime($end);
    if ($end_time === false || $end_time < $start_time) {
        return gmdate('F j, Y', $start_time);
    }
    // 跨年时两端都保留年份，避免长跨度范围无法从显示文字反推开始年份。
    if (gmdate('Y', $start_time) !== gmdate('Y', $end_time)) {
        return gmdate('F j, Y', $start_time) . ' – ' . gmdate('F j, Y', $end_time);
    }
    if (gmdate('n Y', $start_time) === gmdate('n Y', $end_time)) {
        return gmdate('F j', $start_time) . '–' . gmdate('j, Y', $end_time);
    }

    return gmdate('F j', $start_time) . ' – ' . gmdate('F j, Y', $end_time);
}

add_action('save_post_spring_news', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_news_display_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_news_display') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    update_post_meta($post_id, SPRINGAPEX_NEWS_DATE_LABEL_META, springapex_news_date_label_from_submission());
    update_post_meta($post_id, SPRINGAPEX_NEWS_CATEGORY_META, sanitize_text_field(
        springapex_admin_request_scalar($_POST['springapex_news_category'] ?? '')
    ));

    // Guarded by the picker's own presence marker rather than the box's nonce:
    // with no products in the site the picker renders nothing, and an unguarded
    // write would then wipe a list the operator cannot even see.
    if (!empty($_POST['springapex_news_products_present'])) {
        $submitted = isset($_POST['springapex_news_products']) && is_array($_POST['springapex_news_products'])
            ? (array) wp_unslash($_POST['springapex_news_products'])
            : [];
        update_post_meta($post_id, SPRINGAPEX_NEWS_PRODUCTS_META, springapex_sanitize_product_slugs($submitted));
    }
});
