<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin-facing labels are written in Chinese rather than wrapped in __(): the
 * operator is the only audience for them. No front-end template reads a post type
 * label — the public archive URLs come from `has_archive` / `rewrite`, and the page
 * headings are their own copy — so this cannot leak Chinese onto the English site.
 * The names match the ones used in the admin menu and the design skill.
 */
function springapex_register_post_types(): void
{
    register_post_type('spring_product', [
        'labels' => [
            'name' => '产品',
            'singular_name' => '产品条目',
            'menu_name' => '产品',
            'all_items' => '所有产品',
            'add_new' => '添加产品',
            'add_new_item' => '添加产品',
            'edit_item' => '编辑产品',
            'view_item' => '查看产品页',
            'search_items' => '搜索产品',
            'not_found' => '还没有产品条目。',
            'not_found_in_trash' => '回收站里没有产品条目。',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'products',
        'rewrite' => ['slug' => 'products', 'with_front' => false],
        'menu_icon' => 'dashicons-editor-contract',
        // editor 支持已移除：正文编辑器渲染在「产品数据」面板的
        // 「详情正文」标签里（inc/admin/product-panel.php），表单字段
        // 仍是 content，保存路径不变。
        'supports' => ['title', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_solution', [
        'labels' => [
            'name' => '行业方案',
            'singular_name' => '行业方案条目',
            'menu_name' => '行业方案',
            'all_items' => '所有行业方案',
            'add_new' => '添加行业方案',
            'add_new_item' => '添加行业方案',
            'edit_item' => '编辑行业方案',
            'view_item' => '查看方案页',
            'search_items' => '搜索行业方案',
            'not_found' => '还没有行业方案条目。',
            'not_found_in_trash' => '回收站里没有行业方案条目。',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'solutions',
        'rewrite' => ['slug' => 'solutions', 'with_front' => false],
        'menu_icon' => 'dashicons-admin-site-alt3',
        // editor 已移除：方案的 post_content 前台从不渲染，编辑器只是占位。
        'supports' => ['title', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_case', [
        'labels' => [
            'name' => '案例',
            'singular_name' => '案例条目',
            'menu_name' => '案例',
            'all_items' => '所有案例',
            'add_new' => '添加案例',
            'add_new_item' => '添加案例',
            'edit_item' => '编辑案例',
            'view_item' => '查看案例页',
            'search_items' => '搜索案例',
            'not_found' => '还没有案例条目。',
            'not_found_in_trash' => '回收站里没有案例条目。',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'case-studies',
        'rewrite' => ['slug' => 'case-studies', 'with_front' => false],
        'menu_icon' => 'dashicons-portfolio',
        // editor/excerpt 由「案例内容」面板维护（inc/admin/case-panel.php）。
        'supports' => ['title', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_news', [
        'labels' => [
            'name' => '新闻',
            'singular_name' => '新闻文章',
            'menu_name' => '新闻',
            'all_items' => '所有新闻',
            'add_new' => '写新闻',
            'add_new_item' => '写新闻',
            'edit_item' => '编辑新闻',
            'view_item' => '查看新闻页',
            'search_items' => '搜索新闻',
            'not_found' => '还没有新闻文章。',
            'not_found_in_trash' => '回收站里没有新闻文章。',
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'news',
        'rewrite' => ['slug' => 'news', 'with_front' => false],
        // editor 挪进「新闻内容」面板、excerpt 前台不用（inc/news-meta.php）。
        'supports' => ['title', 'thumbnail', 'page-attributes'],
    ]);

    register_taxonomy('spring_news_type', ['spring_news'], [
        'labels' => [
            'name' => '新闻分类',
            'singular_name' => '新闻分类',
            'menu_name' => '新闻分类',
            'search_items' => '搜索新闻分类',
            'all_items' => '所有新闻分类',
            'edit_item' => '编辑新闻分类',
            'update_item' => '更新新闻分类',
            'add_new_item' => '添加新闻分类',
            'new_item_name' => '新分类名称',
            'not_found' => '还没有新闻分类。',
        ],
        'public' => false,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'show_in_nav_menus' => false,
        'hierarchical' => false,
        'rewrite' => false,
        'query_var' => false,
    ]);

    register_post_type('spring_inquiry', [
        'labels' => [
            'name' => '客户询盘',
            'singular_name' => '询盘',
            'menu_name' => '客户询盘',
            'all_items' => '所有询盘',
            'edit_item' => '查看询盘',
            'search_items' => '搜索询盘',
            'not_found' => '还没有收到询盘。',
            'not_found_in_trash' => '回收站里没有询盘。',
        ],
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => false,
        'rewrite' => false,
        'query_var' => false,
        'menu_icon' => 'dashicons-email-alt2',
        // 询盘是访客提交的只读数据：post.php 上只有「询盘内容」只读面板
        //（inc/admin/inquiry-view.php），没有可编辑字段。注意必须是 false——
        // 空数组会被核心回落成默认的 title+editor。
        'supports' => false,
        'capability_type' => ['spring_inquiry', 'spring_inquiries'],
        'capabilities' => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap' => true,
    ]);
}

add_action('init', 'springapex_register_post_types');

/**
 * Structured content types (product / solution / case) render into a fixed theme
 * layout, so their body only needs plain rich text and images — not the full
 * Gutenberg block palette. Curating the inserter to a short, relevant set removes
 * the "wall of modules" that makes adding an entry confusing, while the structured
 * fields live in the meta box below.
 *
 * This only limits what can be INSERTED; any block already in existing content
 * keeps rendering and stays editable. News keeps the full editor (it is a blog).
 *
 * @param bool|string[] $allowed
 * @return bool|string[]
 */
function springapex_curated_block_types(mixed $allowed, WP_Block_Editor_Context $context): mixed
{
    $post = $context->post ?? null;
    if (!($post instanceof WP_Post)) {
        return $allowed;
    }
    if (!in_array($post->post_type, ['spring_product', 'spring_solution', 'spring_case'], true)) {
        return $allowed;
    }

    return [
        'core/paragraph',
        'core/heading',
        'core/list',
        'core/list-item',
        'core/image',
        'core/quote',
    ];
}
add_filter('allowed_block_types_all', 'springapex_curated_block_types', 10, 2);

function springapex_inquiry_primitive_capabilities(): array
{
    return [
        'edit_spring_inquiries',
        'edit_others_spring_inquiries',
        'edit_private_spring_inquiries',
        'edit_published_spring_inquiries',
        'publish_spring_inquiries',
        'read_private_spring_inquiries',
        'delete_spring_inquiries',
        'delete_private_spring_inquiries',
        'delete_published_spring_inquiries',
        'delete_others_spring_inquiries',
    ];
}

function springapex_grant_inquiry_capabilities(): bool
{
    if (!function_exists('get_role')) {
        return false;
    }

    $administrator = get_role('administrator');
    if (!$administrator) {
        return false;
    }

    foreach (springapex_inquiry_primitive_capabilities() as $capability) {
        $administrator->add_cap($capability);
    }

    if ((string) get_option('springapex_inquiry_cap_version', '') === '1') {
        return true;
    }

    return update_option('springapex_inquiry_cap_version', '1', false);
}

add_action('admin_init', static function (): void {
    if (
        current_user_can('activate_themes') &&
        (string) get_option('springapex_inquiry_cap_version', '') !== '1'
    ) {
        springapex_grant_inquiry_capabilities();
    }
});

add_action('add_meta_boxes_spring_product', static function (): void {
    add_meta_box(
        'springapex-product-details',
        '产品数据',
        'springapex_render_product_panel',
        'spring_product',
        'normal',
        'high'
    );
    // 画廊是唯一图片来源（第一张自动同步特色图像），原生特色图像框只会
    // 制造第二个事实来源，藏掉。原生「属性」框同理：排序字段已移入产品
    // 数据面板（同名 menu_order 输入，仍走 WP 标准持久化流程）。
    remove_meta_box('postimagediv', 'spring_product', 'side');
    remove_meta_box('postattributes', 'spring_product', 'side');
});

/**
 * Column sets for the product screen's repeating sections. Keyed by the request
 * field, same contract as springapex_solution_row_sets().
 *
 * @return array<string, array<int, array{key: string, label: string, type: string, help?: string, half?: bool, default?: string}>>
 */
function springapex_product_row_sets(): array
{
    return [
        'springapex_gallery' => [
            ['key' => 'image', 'label' => '图片', 'type' => 'image', 'help' => '选择媒体库中的产品图片；清空后保存会移除这一张。'],
        ],
        'springapex_specs' => [
            ['key' => 'label', 'label' => '项目', 'type' => 'text', 'half' => true],
            ['key' => 'value', 'label' => '数值或范围', 'type' => 'text', 'half' => true],
        ],
    ];
}

/** 请求字段 ⇒ 存储的 meta key。 */
function springapex_product_row_meta_keys(): array
{
    return [
        'springapex_gallery' => '_springapex_gallery',
        'springapex_specs' => '_springapex_specs',
    ];
}


function springapex_admin_request_scalar(mixed $value): string
{
    return is_scalar($value) ? (string) wp_unslash($value) : '';
}

add_action('save_post_spring_product', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_product_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_product') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $subtitle = sanitize_textarea_field(springapex_admin_request_scalar($_POST['springapex_subtitle'] ?? ''));

    update_post_meta($post_id, '_springapex_subtitle', $subtitle);
    foreach (springapex_product_row_sets() as $field => $columns) {
        $meta_key = springapex_product_row_meta_keys()[$field] ?? '';
        if ($meta_key === '') {
            continue;
        }
        update_post_meta($post_id, $meta_key, springapex_sanitize_row_editor(
            isset($_POST[$field]) ? wp_unslash($_POST[$field]) : [],
            $columns
        ));
    }
    update_post_meta($post_id, '_springapex_featured', isset($_POST['springapex_featured']) ? '1' : '0');
    update_post_meta($post_id, '_springapex_mega_menu', isset($_POST['springapex_mega_menu']) ? '1' : '0');

    // The first gallery image is the primary; mirror it to the native Featured
    // Image so product cards and share/OG thumbnails stay in sync. A first image
    // that is a preset theme file (no attachment) leaves no featured image, and
    // the front end falls back to that preset — same as before.
    if (isset($_POST['springapex_gallery'])) {
        $gallery = get_post_meta($post_id, '_springapex_gallery', true);
        $primary_id = is_array($gallery) && isset($gallery[0]['image_id']) ? (int) $gallery[0]['image_id'] : 0;
        if ($primary_id > 0 && get_post_type($primary_id) === 'attachment') {
            set_post_thumbnail($post_id, $primary_id);
        } else {
            delete_post_thumbnail($post_id);
        }
    }
});
