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
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
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
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
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
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
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
        'menu_icon' => 'dashicons-megaphone',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
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
        'supports' => ['title', 'editor'],
        'capability_type' => ['spring_inquiry', 'spring_inquiries'],
        'capabilities' => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap' => true,
    ]);
}

add_action('init', 'springapex_register_post_types');

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
        '这个产品页面的内容',
        'springapex_render_product_meta_box',
        'spring_product',
        'normal',
        'high'
    );
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
            ['key' => 'image', 'label' => '图片', 'type' => 'image'],
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

function springapex_render_product_meta_box(object $post): void
{
    wp_nonce_field('springapex_save_product', 'springapex_product_nonce');

    $post_id = (int) $post->ID;
    // Same rule as the front end (springapex_product_from_post): a key that was never
    // saved shows the seed value. Reading get_post_meta() straight would render every
    // untouched product as an empty screen, and the first save would then wipe content
    // the operator can see on the public page but never had in front of them.
    $seed = springapex_product_seed((string) ($post->post_name ?? '')) ?? [];
    $value = static function (string $key, mixed $fallback) use ($post_id): mixed {
        return metadata_exists('post', $post_id, $key) ? get_post_meta($post_id, $key, true) : $fallback;
    };

    $subtitle = (string) $value('_springapex_subtitle', $seed['subtitle'] ?? '');
    $featured = (bool) $value('_springapex_featured', !empty($seed['featured']));

    $sets = springapex_product_row_sets();
    $seed_keys = [
        'springapex_gallery' => 'gallery',
        'springapex_specs' => 'specs',
    ];
    $rows = [];
    foreach (springapex_product_row_meta_keys() as $field => $meta_key) {
        $rows[$field] = (array) $value($meta_key, $seed[$seed_keys[$field]] ?? []);
    }
    // A product always has at least its main image, so the gallery is never left
    // blank: an empty saved gallery (e.g. saved before galleries existed) falls
    // back to the Featured Image, then the preset product image. This also
    // self-heals — the fallback row is submitted on the next save.
    if (empty($rows['springapex_gallery'])) {
        $thumbnail_id = (int) get_post_thumbnail_id($post_id);
        if ($thumbnail_id > 0) {
            $rows['springapex_gallery'] = [['image_id' => $thumbnail_id, 'image' => '']];
        } elseif (!empty($seed['image'])) {
            $rows['springapex_gallery'] = [['image' => (string) $seed['image']]];
        }
    }
    ?>
    <p class="description">
      这个产品详情页显示这几项：<strong>产品名</strong>用上方的标题，<strong>正文</strong>在上方的编辑器里写。下面几项显示在页面顶部，文字会原样出现在英文前台，请用英文填写。
    </p>
    <h3>产品图</h3>
    <?php springapex_render_row_editor(
        'springapex_gallery',
        $rows['springapex_gallery'],
        $sets['springapex_gallery'],
        '第一张显示为页面顶部的大图，其余作为可切换的缩略图。用「上移/下移」调整顺序，第一张就是主图（同时用作产品列表和分享缩略图）。建议正方形、白底、1200×1200。'
    ); ?>
    <p>
      <label for="springapex-subtitle"><strong>标题下的一句话</strong></label><br>
      <textarea class="widefat" rows="2" id="springapex-subtitle" name="springapex_subtitle"><?php echo esc_textarea($subtitle); ?></textarea>
      <span class="description">显示在产品名下方的一行介绍语。</span>
    </p>

    <h3>顶部关键参数</h3>
    <?php springapex_render_row_editor(
        'springapex_specs',
        $rows['springapex_specs'],
        $sets['springapex_specs'],
        '显示在页面顶部的三个数据框（前 3 行生效，多余的不显示）。左边是项目名（例如 Wire Diameter），右边是数值或范围（例如 0.1 – 60 mm）。'
    ); ?>

    <p>
      <label><input type="checkbox" name="springapex_featured" value="1" <?php checked($featured); ?>> 在首页的「Featured Products」里显示这个产品</label>
    </p>
    <?php
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
