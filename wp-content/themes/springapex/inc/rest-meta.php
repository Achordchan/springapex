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
    // 分类不是 meta：卡片/详情页上的小标签就是所选「新闻分类」
    // （spring_news_type）的名称，REST 里用核心的 spring_news_type 字段传分类 ID。
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
    register_post_meta('spring_news', SPRINGAPEX_NEWS_AUTHOR_META, [
        'type' => 'integer',
        'description' => 'ID of the spring_news_author shown in the article sidebar; 0 shows no author. Only published authors appear on the site.',
        'single' => true,
        'default' => 0,
        'show_in_rest' => true,
        'sanitize_callback' => static fn(mixed $value): int => springapex_sanitize_news_author_id($value),
        'auth_callback' => $can_edit,
    ]);

    // 阅读数基准（inc/news-views.php）。只在 edit 上下文输出：匿名 GET 看不出
    // 前台数字里有多少是预设的。真实阅读不注册成 meta，REST 写不进去。
    register_post_meta('spring_news', SPRINGAPEX_NEWS_VIEWS_BASE_META, [
        'type' => 'integer',
        'description' => 'Preset views added to real views; the site shows the sum. Real views are counted separately and cannot be written.',
        'single' => true,
        'default' => 0,
        'show_in_rest' => [
            'schema' => ['type' => 'integer', 'context' => ['edit']],
        ],
        'sanitize_callback' => static fn(mixed $value): int => springapex_sanitize_news_views_base($value),
        'auth_callback' => $can_edit,
    ]);

    // 新闻作者条目自身的字段（inc/news-author.php）：姓名是 title，头像是 featured_media。
    add_post_type_support('spring_news_author', 'custom-fields');
    register_post_meta('spring_news_author', SPRINGAPEX_NEWS_AUTHOR_ROLE_META, $string_meta(
        'Job title shown under the author name.',
        'sanitize_text_field'
    ));
    register_post_meta('spring_news_author', SPRINGAPEX_NEWS_AUTHOR_BIO_META, $string_meta(
        'Optional one- or two-sentence bio shown on the author card.',
        'sanitize_textarea_field'
    ));
});

// 阅读数的只读拆分，给外部工具看统计用；同样只在 edit 上下文（需要编辑权限）输出。
add_action('rest_api_init', static function (): void {
    register_rest_field('spring_news', 'springapex_views', [
        'get_callback' => static fn(array $post): array => [
            'base' => springapex_news_views_base((int) $post['id']),
            'real' => springapex_news_views_real((int) $post['id']),
            'total' => springapex_news_views_total((int) $post['id']),
        ],
        'schema' => [
            'description' => 'View count shown on the site (total) = preset base + real views.',
            'type' => 'object',
            'context' => ['edit'],
            'readonly' => true,
            'properties' => [
                'base' => ['type' => 'integer'],
                'real' => ['type' => 'integer'],
                'total' => ['type' => 'integer'],
            ],
        ],
    ]);
});

// custom-fields 支持只为 REST 开；这些类型的字段都有专用面板，
// 不要在编辑页多出一块原生「自定义字段」框。page 保持核心默认。
add_action('add_meta_boxes', static function (): void {
    foreach (['spring_product', 'spring_solution', 'spring_case', 'spring_news', 'spring_news_author'] as $post_type) {
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
