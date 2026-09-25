<?php
/**
 * REST 暴露主题自有的文章 meta，让外部发布工具（WordPress 应用密码 + REST API）
 * 能直接写 SEO / TDK 与新闻展示字段，不必再回后台手填。
 *
 * 读写都走 WP 核心的 meta 权限：受保护 key（下划线开头）只在 auth_callback
 * 返回 true 时可写，这里要求对该文章有 edit_post 权限。字段值与后台面板
 * 保存时用同一套清洗规则，两条写入路径存出来的数据一致。
 * 这些值本来就会出现在前台 <head> 或页面上，读取不额外设限。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('init', static function (): void {
    $can_edit = static fn(bool $allowed, string $meta_key, int $post_id): bool
        => current_user_can('edit_post', $post_id);

    $string_meta = static fn(string $description, callable $sanitize): array => [
        'type' => 'string',
        'description' => $description,
        'single' => true,
        'default' => '',
        'show_in_rest' => true,
        'sanitize_callback' => static fn(mixed $value): string => trim((string) $sanitize(is_scalar($value) ? (string) $value : '')),
        'auth_callback' => $can_edit,
    ];

    // 与 inc/admin/seo-settings.php 的 SEO / TDK 面板同一组 post type 与清洗规则。
    // 空字符串等同未填：前台 springapex_seo_post_values() 会回退到自动值。
    foreach (['spring_product', 'spring_solution', 'spring_case', 'spring_news', 'page'] as $post_type) {
        // REST 只在 post type 支持 custom-fields 时输出 meta 字段。
        add_post_type_support($post_type, 'custom-fields');
        register_post_meta($post_type, '_springapex_seo_title', $string_meta('SEO title tag; empty uses the post title.', 'sanitize_text_field'));
        register_post_meta($post_type, '_springapex_seo_description', $string_meta('Meta description; empty uses the excerpt or content.', 'sanitize_textarea_field'));
        register_post_meta($post_type, '_springapex_seo_keywords', $string_meta('Meta keywords (optional).', 'sanitize_text_field'));
    }

    // 新闻面板（inc/news-meta.php）里的展示字段。
    register_post_meta('spring_news', SPRINGAPEX_NEWS_DATE_LABEL_META, $string_meta(
        'Date text shown instead of the publish date, e.g. "June 17–20, 2024". Empty uses the publish date.',
        'sanitize_text_field'
    ));
    register_post_meta('spring_news', SPRINGAPEX_NEWS_CATEGORY_META, $string_meta(
        'Category label on news cards and the article header. Empty uses the News type term name.',
        'sanitize_text_field'
    ));
    register_post_meta('spring_news', SPRINGAPEX_NEWS_PRODUCTS_META, [
        'type' => 'array',
        'description' => 'Related product slugs shown in the article sidebar. Unpublished or unknown slugs are dropped.',
        'single' => true,
        'default' => [],
        'show_in_rest' => [
            'schema' => [
                'type' => 'array',
                'items' => ['type' => 'string'],
            ],
        ],
        'sanitize_callback' => static fn(mixed $value): array => springapex_sanitize_product_slugs($value),
        'auth_callback' => $can_edit,
    ]);
});

// custom-fields 支持只为 REST 开；这些类型的字段都有专用面板，
// 不要在编辑页多出一块原生「自定义字段」框。page 保持核心默认。
add_action('add_meta_boxes', static function (): void {
    foreach (['spring_product', 'spring_solution', 'spring_case', 'spring_news'] as $post_type) {
        remove_meta_box('postcustom', $post_type, 'normal');
    }
}, 20);

// 正文和摘要：这几个类型把正文编辑器挪进了自己的面板（post-types.php 里去掉了
// editor 支持），而 REST 只在类型支持 editor / excerpt 时才输出并接受
// content / excerpt。只在真正的 REST 请求里补上支持：rest_api_init 也可能在
// 后台页面内被触发，那时补 editor 会让经典编辑页多出一个核心正文框，
// 与面板里的 wp_editor('content') 冲突。
add_action('rest_api_init', static function (): void {
    if (!defined('REST_REQUEST') || !REST_REQUEST) {
        return;
    }
    foreach (['spring_product', 'spring_solution', 'spring_case', 'spring_news'] as $post_type) {
        add_post_type_support($post_type, ['editor', 'excerpt']);
    }
});
