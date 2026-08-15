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
        '前台显示设置',
        'springapex_render_news_display_meta_box',
        'spring_news',
        'side',
        'default'
    );
});

function springapex_render_news_display_meta_box(WP_Post $post): void
{
    $post_id = (int) $post->ID;
    wp_nonce_field('springapex_save_news_display', 'springapex_news_display_nonce');
    ?>
    <p>
        <label for="springapex-news-date-label"><strong>日期文字</strong></label><br>
        <input class="widefat" type="text" id="springapex-news-date-label"
            name="springapex_news_date_label"
            value="<?php echo esc_attr(springapex_news_date_label_meta($post_id)); ?>">
        <span class="description">留空就按发布日期显示。展会这类跨几天的活动可以写成
            <code>June 17–20, 2024</code>。这里的文字会原样出现在前台，所以请用英文。</span>
    </p>
    <p>
        <label for="springapex-news-category"><strong>分类标签</strong></label><br>
        <input class="widefat" type="text" id="springapex-news-category"
            name="springapex_news_category"
            value="<?php echo esc_attr(springapex_news_category_meta($post_id)); ?>">
        <span class="description">显示在新闻卡片和详情页顶部的小标签。留空就用右边「News type」里选的分类名。同样会原样显示，请用英文。</span>
    </p>
    <hr>
    <p><strong>相关产品</strong></p>
    <?php
    springapex_render_product_picker(
        'springapex_news_products',
        springapex_news_products_meta($post_id),
        '勾选的产品会显示在这篇新闻详情页右侧的「Related products」里。不勾就不显示这一块。'
    );
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

    update_post_meta($post_id, SPRINGAPEX_NEWS_DATE_LABEL_META, sanitize_text_field(
        springapex_admin_request_scalar($_POST['springapex_news_date_label'] ?? '')
    ));
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
