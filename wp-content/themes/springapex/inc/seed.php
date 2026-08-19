<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

defined('SPRINGAPEX_SEED_VERSION') || define('SPRINGAPEX_SEED_VERSION', '2.7.9');

add_action('after_switch_theme', 'springapex_seed_site');
add_action('admin_notices', 'springapex_seed_admin_notice');

add_action('admin_init', static function (): void {
    if (!current_user_can('activate_themes')) {
        return;
    }
    if ((string) get_option('springapex_seed_version', '') !== SPRINGAPEX_SEED_VERSION) {
        springapex_seed_site();
    }
});

function springapex_seed_site(): bool
{
    $lock_token = springapex_acquire_seed_lock();
    if ($lock_token === '') {
        return false;
    }

    try {
        return springapex_seed_site_locked();
    } finally {
        springapex_release_seed_lock($lock_token);
    }
}

function springapex_acquire_seed_lock(): string
{
    return springapex_acquire_option_lock('springapex_seed_lock', 300);
}

function springapex_release_seed_lock(string $token): void
{
    springapex_release_option_lock('springapex_seed_lock', $token);
}

function springapex_seed_failure(string $code): bool
{
    update_option('springapex_seed_last_error', sanitize_key($code), false);
    return false;
}

function springapex_seed_admin_notice(): void
{
    if (!current_user_can('activate_themes')) {
        return;
    }

    $code = (string) get_option('springapex_seed_last_error', '');
    if ($code === '') {
        return;
    }

    $messages = [
        'runtime' => __('WordPress content APIs were unavailable.', 'springapex'),
        'capabilities' => __('Post types or inquiry permissions could not be initialized.', 'springapex'),
        'content' => __('One or more pages, products, solutions or menu items could not be initialized.', 'springapex'),
        'version' => __('The initialization result could not be recorded.', 'springapex'),
    ];
    $message = $messages[$code] ?? __('Theme initialization did not complete.', 'springapex');
    printf(
        '<div class="notice notice-error"><p>%s</p></div>',
        esc_html(sprintf(__('ApexSpring setup will retry automatically: %s', 'springapex'), $message))
    );
}

function springapex_seed_site_locked(): bool
{
    if (!function_exists('wp_insert_post')) {
        return springapex_seed_failure('runtime');
    }

    $current_version = (string) get_option('springapex_seed_version', '');
    $initialized = (string) get_option('springapex_seed_initialized', '') === '1' || $current_version !== '';
    $allow_create = !$initialized;

    $legacy_brand = 'Spring' . 'Apex';
    foreach (['blogname', 'blogdescription'] as $option_name) {
        $current_value = (string) get_option($option_name, '');
        $updated_value = str_replace($legacy_brand, 'ApexSpring', $current_value);
        if (
            $updated_value !== $current_value &&
            !springapex_seed_update_option($option_name, $updated_value)
        ) {
            return springapex_seed_failure('content');
        }
    }

    if (
        function_exists('springapex_register_post_types') &&
        (
            !post_type_exists('spring_product') ||
            !post_type_exists('spring_solution') ||
            !post_type_exists('spring_case') ||
            !post_type_exists('spring_news') ||
            !post_type_exists('spring_inquiry') ||
            !taxonomy_exists('spring_news_type')
        )
    ) {
        springapex_register_post_types();
    }

    if (
        !post_type_exists('spring_product') ||
        !post_type_exists('spring_solution') ||
        !post_type_exists('spring_case') ||
        !post_type_exists('spring_news') ||
        !post_type_exists('spring_inquiry') ||
        !taxonomy_exists('spring_news_type') ||
        !springapex_grant_inquiry_capabilities()
    ) {
        return springapex_seed_failure('capabilities');
    }

    $home_id = springapex_seed_page('Home', 'home', '', 'default', $allow_create);
    $about_id = springapex_seed_page('About', 'about', '', 'page-about.php', $allow_create);
    $sustainability_id = springapex_seed_page('Sustainability', 'sustainability', '', 'page-sustainability.php', true);
    $capabilities_id = springapex_seed_page('Capabilities', 'capabilities', '', 'page-capabilities.php', true);
    $manufacturing_videos_id = springapex_seed_page('Manufacturing Videos', 'manufacturing-videos', '', 'page-manufacturing-videos.php', true);
    $contact_id = springapex_seed_page('Contact', 'contact', '', 'page-contact.php', $allow_create);
    $resources_id = springapex_seed_page(
        'Resources',
        'resources',
        '<h2>Engineering resources</h2><p>Catalogs, material guidance and technical support are available from the ApexSpring engineering team.</p>',
        'page-resources.php',
        $allow_create
    );
    if (
        is_int($resources_id) &&
        $resources_id > 0 &&
        (string) get_post_field('post_title', $resources_id) === 'Resources'
    ) {
        $renamed_resources = wp_update_post(['ID' => $resources_id, 'post_title' => 'Download Center'], true);
        if (is_wp_error($renamed_resources) || (int) $renamed_resources <= 0) {
            return springapex_seed_failure('content');
        }
    }
    $privacy_id = springapex_seed_page('Privacy Policy', 'privacy', '', 'page-privacy.php', true);
    $terms_id = springapex_seed_page('Terms of Use', 'terms', '', 'page-terms.php', true);
    $sitemap_id = springapex_seed_page('Sitemap', 'sitemap', '', 'page-sitemap.php', true);
    // 表单提交成功后的落地页（域名/success），便于统计转化。
    springapex_seed_page('Thank You', 'success', '', 'page-success.php', true);

    $front_page_ready = true;
    if (is_int($home_id) && $home_id > 0 && (int) get_option('page_on_front') === 0) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $home_id);
        $front_page_ready = (string) get_option('show_on_front') === 'page' &&
            (int) get_option('page_on_front') === $home_id;
    }

    $success = $front_page_ready &&
        $home_id !== 0 &&
        $about_id !== 0 &&
        $sustainability_id !== 0 &&
        $capabilities_id !== 0 &&
        $manufacturing_videos_id !== 0 &&
        $contact_id !== 0 &&
        $resources_id !== 0 &&
        $privacy_id !== 0 &&
        $terms_id !== 0 &&
        $sitemap_id !== 0;
    $success = springapex_seed_products($allow_create) && $success;
    $success = springapex_seed_solutions($allow_create) && $success;
    $success = springapex_seed_news(true) && $success;
    $success = springapex_seed_primary_menu($allow_create) && $success;
    $success = springapex_seed_capabilities_menu_url() && $success;

    if (!$success) {
        return springapex_seed_failure('content');
    }

    if (
        (string) get_option('springapex_seed_initialized', '') !== '1' &&
        !springapex_seed_update_option('springapex_seed_initialized', '1')
    ) {
        return springapex_seed_failure('version');
    }

    if ($current_version !== SPRINGAPEX_SEED_VERSION && !springapex_seed_update_option('springapex_seed_version', SPRINGAPEX_SEED_VERSION)) {
        return springapex_seed_failure('version');
    }

    delete_option('springapex_seed_last_error');
    flush_rewrite_rules(false);
    return true;
}

/**
 * @return int|false Post ID, or false when an upgrade intentionally preserves a missing page.
 */
function springapex_seed_page(string $title, string $slug, string $content, string $template, bool $allow_create = true): int|false
{
    $existing = springapex_seed_find_post_by_slug($slug, 'page');
    if ($existing) {
        if (
            $content !== '' &&
            !springapex_seed_upgrade_brand_fields($existing, ['post_content' => $content])
        ) {
            return 0;
        }
        if (
            $template !== 'default' &&
            !metadata_exists('post', (int) $existing->ID, '_wp_page_template') &&
            !springapex_seed_update_meta((int) $existing->ID, '_wp_page_template', $template)
        ) {
            return 0;
        }
        return (int) $existing->ID;
    }

    if (!$allow_create) {
        return false;
    }

    $post_id = wp_insert_post([
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => $title,
        'post_name' => $slug,
        'post_content' => $content,
    ], true);

    if (is_wp_error($post_id)) {
        return 0;
    }

    if ($template !== 'default') {
        if (!springapex_seed_update_meta((int) $post_id, '_wp_page_template', $template)) {
            return 0;
        }
    }
    return (int) $post_id;
}

function springapex_seed_find_post_by_slug(string $slug, string $post_type): ?object
{
    $posts = get_posts([
        'name' => $slug,
        'post_type' => $post_type,
        'post_status' => 'any',
        'posts_per_page' => 1,
        'orderby' => 'ID',
        'order' => 'ASC',
    ]);

    return $posts[0] ?? null;
}

function springapex_seed_update_meta(int $post_id, string $key, mixed $value): bool
{
    $updated = update_post_meta($post_id, $key, $value);
    if ($updated !== false) {
        return true;
    }

    return metadata_exists('post', $post_id, $key) && get_post_meta($post_id, $key, true) === $value;
}

function springapex_seed_update_option(string $name, string $value): bool
{
    $updated = update_option($name, $value, false);
    return $updated || (string) get_option($name, '') === $value;
}

/**
 * Update only untouched seed text that still contains the previous brand name.
 *
 * @param array<string, string> $target_fields
 */
function springapex_seed_upgrade_brand_fields(object $post, array $target_fields): bool
{
    $update = ['ID' => (int) $post->ID];
    $legacy_brand = 'Spring' . 'Apex';

    foreach ($target_fields as $field => $target) {
        $legacy = str_replace('ApexSpring', $legacy_brand, $target);
        if ($legacy !== $target && (string) ($post->{$field} ?? '') === $legacy) {
            $update[$field] = $target;
        }
    }

    if (count($update) === 1) {
        return true;
    }

    return !is_wp_error(wp_update_post($update, true));
}

function springapex_seed_products(bool $allow_create = true): bool
{
    $products = springapex_get('products.categories', []);
    $featured = springapex_get('products.featured', []);
    $success = true;

    foreach ($products as $index => $product) {
        $slug = (string) ($product['slug'] ?? '');
        if ($slug === '') {
            continue;
        }

        $detail = springapex_product_seed($slug) ?? $product;
        $meta = [
            '_springapex_subtitle' => $detail['subtitle'] ?? $detail['desc'] ?? '',
            '_springapex_specs' => $detail['specs'] ?? [],
            '_springapex_materials' => $detail['materials'] ?? [],
            '_springapex_applications' => $detail['applications'] ?? [],
            '_springapex_catalog_url' => $detail['catalog_url'] ?? '',
            '_springapex_seed_image' => $detail['image'] ?? '',
            '_springapex_featured' => in_array($slug, $featured, true) ? '1' : '0',
        ];

        $existing = springapex_seed_find_post_by_slug($slug, 'spring_product');
        if ($existing) {
            foreach ($meta as $key => $value) {
                $post_id = (int) $existing->ID;
                if (
                    $key === '_springapex_seed_image' &&
                    $slug === 'compression-springs' &&
                    in_array(
                        (string) get_post_meta($post_id, $key, true),
                        ['product-compression-v2.png', 'product-compression-detail-v3.png'],
                        true
                    )
                ) {
                    if (!springapex_seed_update_meta($post_id, $key, $value)) {
                        $success = false;
                    }
                    continue;
                }
                if (!metadata_exists('post', $post_id, $key) && !springapex_seed_update_meta($post_id, $key, $value)) {
                    $success = false;
                }
            }
            continue;
        }

        if (!$allow_create) {
            continue;
        }

        $post_id = wp_insert_post([
            'post_type' => 'spring_product',
            'post_status' => 'publish',
            'post_title' => (string) ($detail['title'] ?? ''),
            'post_name' => $slug,
            'post_excerpt' => (string) ($detail['desc'] ?? ''),
            'post_content' => (string) ($detail['overview'] ?? $detail['desc'] ?? ''),
            'menu_order' => $index,
        ], true);

        if (is_wp_error($post_id)) {
            $success = false;
            continue;
        }

        foreach ($meta as $key => $value) {
            if (!springapex_seed_update_meta((int) $post_id, $key, $value)) {
                $success = false;
            }
        }
    }

    return $success;
}

function springapex_seed_solutions(bool $allow_create = true): bool
{
    $success = true;
    foreach (springapex_get('solutions.items', []) as $index => $solution) {
        $slug = (string) ($solution['slug'] ?? '');
        if ($slug === '') {
            continue;
        }

        $existing = springapex_seed_find_post_by_slug($slug, 'spring_solution');
        if ($existing) {
            $post_id = (int) $existing->ID;
            $seed_content = sprintf(
                'ApexSpring engineers precision spring solutions for %s applications, from design review and prototyping through stable production.',
                strtolower((string) ($solution['title'] ?? 'industrial'))
            );
            if (!springapex_seed_upgrade_brand_fields($existing, ['post_content' => $seed_content])) {
                $success = false;
            }
            $legacy_images = [
                'industrial-equipment' => 'solution-industrial-v2.png',
                'medical' => 'solution-medical-v2.png',
            ];
            $current_image = (string) get_post_meta($post_id, '_springapex_seed_image', true);
            if (
                isset($legacy_images[$slug]) &&
                $current_image === $legacy_images[$slug] &&
                !springapex_seed_update_meta($post_id, '_springapex_seed_image', (string) ($solution['image'] ?? ''))
            ) {
                $success = false;
            }
            if (
                !metadata_exists('post', $post_id, '_springapex_seed_image') &&
                !springapex_seed_update_meta($post_id, '_springapex_seed_image', (string) ($solution['image'] ?? ''))
            ) {
                $success = false;
            }
            continue;
        }

        if (!$allow_create) {
            continue;
        }

        $post_id = wp_insert_post([
            'post_type' => 'spring_solution',
            'post_status' => 'publish',
            'post_title' => (string) ($solution['title'] ?? ''),
            'post_name' => $slug,
            'post_excerpt' => (string) ($solution['tagline'] ?? ''),
            'post_content' => sprintf(
                'ApexSpring engineers precision spring solutions for %s applications, from design review and prototyping through stable production.',
                strtolower((string) ($solution['title'] ?? 'industrial'))
            ),
            'menu_order' => $index,
        ], true);

        if (is_wp_error($post_id)) {
            $success = false;
            continue;
        }

        if (!springapex_seed_update_meta((int) $post_id, '_springapex_seed_image', (string) ($solution['image'] ?? ''))) {
            $success = false;
        }
    }

    return $success;
}

function springapex_seed_news_content_html(array $blocks): string
{
    $html = '';
    foreach ($blocks as $block) {
        $type = (string) ($block['type'] ?? 'p');
        if ($type === 'h2') {
            $html .= '<h2>' . esc_html((string) ($block['text'] ?? '')) . '</h2>';
            continue;
        }
        if ($type === 'list') {
            $html .= '<ul>';
            foreach ((array) ($block['items'] ?? []) as $item) {
                $html .= '<li>' . esc_html((string) $item) . '</li>';
            }
            $html .= '</ul>';
            continue;
        }
        $html .= '<p>' . esc_html((string) ($block['text'] ?? '')) . '</p>';
    }
    return $html;
}

function springapex_remove_legacy_seed_news(): bool
{
    if (!function_exists('wp_trash_post')) {
        return true;
    }

    $legacy_items = [
        'new-cnc-coiling-line' => 'generated/springapex-news-cnc-coiling-v1.webp',
        'spring-material-selection-guide' => 'generated/springapex-news-material-selection-v1.webp',
        'quality-system-audit-completed' => 'generated/springapex-news-quality-audit-v1.webp',
        'prototype-to-production-process' => 'generated/springapex-news-prototype-v1.webp',
        'export-packaging-and-traceability' => 'generated/springapex-news-export-packaging-v1.webp',
        'engineering-support-response-time' => 'generated/springapex-news-engineering-support-v1.webp',
    ];

    foreach ($legacy_items as $slug => $seed_image) {
        $post = springapex_seed_find_post_by_slug($slug, 'spring_news');
        if (!$post) {
            continue;
        }

        $post_id = (int) $post->ID;
        if ((string) get_post_meta($post_id, '_springapex_seed_image', true) !== $seed_image) {
            continue;
        }

        if (!wp_trash_post($post_id)) {
            return false;
        }
    }

    return true;
}

function springapex_seed_news_types(): bool
{
    if (!function_exists('term_exists') || !function_exists('wp_insert_term')) {
        return false;
    }

    $types = [
        'industry-news' => 'Industry News',
        'exhibitions' => 'Exhibitions',
        'company-news' => 'Company News',
    ];

    foreach ($types as $slug => $name) {
        if (term_exists($slug, 'spring_news_type')) {
            continue;
        }
        if (is_wp_error(wp_insert_term($name, 'spring_news_type', ['slug' => $slug]))) {
            return false;
        }
    }

    return true;
}

function springapex_assign_news_type(int $post_id, string $news_type): bool
{
    $news_type = sanitize_key($news_type);
    if ($news_type === '' || !function_exists('wp_set_object_terms')) {
        return $news_type === '';
    }

    return !is_wp_error(wp_set_object_terms($post_id, [$news_type], 'spring_news_type', false));
}

function springapex_seed_news(bool $allow_create = true): bool
{
    $success = springapex_seed_news_types() && springapex_remove_legacy_seed_news();
    foreach (springapex_get('news.items', []) as $index => $item) {
        $slug = (string) ($item['slug'] ?? '');
        if ($slug === '') {
            continue;
        }

        $existing = springapex_seed_find_post_by_slug($slug, 'spring_news');
        if ($existing) {
            $post_id = (int) $existing->ID;
            if (!springapex_seed_upgrade_brand_fields($existing, [
                'post_title' => (string) ($item['title'] ?? ''),
                'post_excerpt' => (string) ($item['summary'] ?? ''),
                'post_content' => springapex_seed_news_content_html((array) ($item['content'] ?? [])),
            ])) {
                $success = false;
            }
            if (
                !metadata_exists('post', $post_id, '_springapex_seed_image') &&
                !springapex_seed_update_meta($post_id, '_springapex_seed_image', (string) ($item['image'] ?? ''))
            ) {
                $success = false;
            }
            if (!springapex_assign_news_type($post_id, (string) ($item['news_type'] ?? 'company-news'))) {
                $success = false;
            }
            continue;
        }

        if (!$allow_create) {
            continue;
        }

        $date = (string) ($item['date'] ?? '');
        $post_id = wp_insert_post([
            'post_type' => 'spring_news',
            'post_status' => 'publish',
            'post_title' => (string) ($item['title'] ?? ''),
            'post_name' => $slug,
            'post_excerpt' => (string) ($item['summary'] ?? ''),
            'post_date' => $date !== '' ? $date . ' 09:00:00' : '',
            'post_content' => springapex_seed_news_content_html((array) ($item['content'] ?? [])),
            'menu_order' => $index,
        ], true);

        if (is_wp_error($post_id)) {
            $success = false;
            continue;
        }

        if (!springapex_seed_update_meta((int) $post_id, '_springapex_seed_image', (string) ($item['image'] ?? ''))) {
            $success = false;
        }
        if (!springapex_seed_update_meta((int) $post_id, '_springapex_news_category', (string) ($item['category'] ?? ''))) {
            $success = false;
        }
        if (!springapex_seed_update_meta((int) $post_id, '_springapex_news_date_label', (string) ($item['date_label'] ?? ''))) {
            $success = false;
        }
        if (!springapex_assign_news_type((int) $post_id, (string) ($item['news_type'] ?? 'company-news'))) {
            $success = false;
        }
    }

    return $success;
}

/**
 * 把一条主菜单项声明转成 wp_update_nav_menu_item() 的参数。
 * 带 'archive' 的用 post_type_archive 类型（URL 动态渲染）；否则是自定义链接。
 */
function springapex_seed_menu_item_args(array $spec): array
{
    if (!empty($spec['archive'])) {
        return [
            'menu-item-title' => $spec['title'],
            'menu-item-type' => 'post_type_archive',
            'menu-item-object' => $spec['archive'],
            'menu-item-status' => 'publish',
        ];
    }

    return [
        'menu-item-title' => $spec['title'],
        'menu-item-url' => $spec['url'],
        'menu-item-type' => 'custom',
        'menu-item-status' => 'publish',
    ];
}

/**
 * 校准主菜单顶层项。幂等：已存在按标题匹配，只把归档项(Products/Industries)
 * 就地纠正为 post_type_archive 类型，其余普通链接项不覆盖运营者的手工改动。
 * 对已存在的站点也会跑（自愈早期 seed 写死的 `?post_type=` 查询串链接）。
 */
function springapex_seed_primary_menu_items(int $menu_id): bool
{
    // 指向 CPT 归档的项用 post_type_archive 类型，URL 由 WP 用
    // get_post_type_archive_link() 动态渲染——永远是 pretty、永远跟当前站点域名，
    // 不会 seed 出 `?post_type=` 查询串，也不会把某个环境的域名写死带到别处。
    $items = [
        ['title' => 'Home', 'url' => home_url('/')],
        ['title' => 'About Us', 'url' => home_url('/about/')],
        ['title' => 'Products', 'archive' => 'spring_product'],
        ['title' => 'Industries', 'archive' => 'spring_solution'],
        ['title' => 'Custom Springs', 'url' => home_url('/capabilities/')],
        ['title' => 'News', 'url' => home_url('/news/')],
        ['title' => 'Contact', 'url' => home_url('/contact/')],
    ];

    $existing = wp_get_nav_menu_items($menu_id);
    if (is_wp_error($existing)) {
        return false;
    }
    $existing = $existing ?: [];

    foreach ($existing as $existing_item) {
        $is_catalog_item = (string) $existing_item->title === 'View Our Catalog'
            || str_contains((string) $existing_item->url, '/resources/#catalog-download');
        if (!$is_catalog_item) {
            continue;
        }
        $updated = wp_update_nav_menu_item($menu_id, (int) $existing_item->ID, [
            'menu-item-title' => 'News',
            'menu-item-url' => home_url('/news/'),
            'menu-item-status' => 'publish',
        ]);
        if (is_wp_error($updated) || (int) $updated <= 0) {
            return false;
        }
    }

    foreach ($items as $spec) {
        $args = springapex_seed_menu_item_args($spec);

        // 只认顶层项：primary 与 footer 复用同一菜单，footer 里有同名子项
        // （如挂在 Industries 下的子项 Industries），按 parent===0 限定避免误匹配。
        $match = null;
        foreach ($existing as $existing_item) {
            if ((int) $existing_item->menu_item_parent === 0
                && (string) $existing_item->title === $spec['title']) {
                $match = $existing_item;
                break;
            }
        }

        if ($match) {
            // 自愈：早期 seed 把归档项存成了自定义链接 + `?post_type=` 查询串。
            // 仅当归档项类型/对象不符时才就地纠正，不覆盖普通链接项的手工改动。
            $needs_fix = !empty($spec['archive'])
                && ((string) $match->type !== 'post_type_archive'
                    || (string) $match->object !== $spec['archive']);
            if (!$needs_fix) {
                continue;
            }
            // 保留原有排序：wp_update_nav_menu_item 不带 position 会把该项重排到末尾。
            $args['menu-item-position'] = (int) $match->menu_order;
            $updated = wp_update_nav_menu_item($menu_id, (int) $match->ID, $args);
            if (is_wp_error($updated) || (int) $updated <= 0) {
                return false;
            }
            continue;
        }

        $item_id = wp_update_nav_menu_item($menu_id, 0, $args);
        if (is_wp_error($item_id) || (int) $item_id <= 0) {
            return false;
        }
    }

    return true;
}

function springapex_seed_primary_menu(bool $allow_create = true): bool
{
    $locations = get_theme_mod('nav_menu_locations', []);
    $locations = is_array($locations) ? $locations : [];
    $primary_id = (int) ($locations['primary'] ?? 0);
    $footer_id = (int) ($locations['footer'] ?? 0);
    $primary_menu = $primary_id > 0 ? wp_get_nav_menu_object($primary_id) : false;
    $footer_menu = $footer_id > 0 ? wp_get_nav_menu_object($footer_id) : false;
    $legacy_menu_name = ('Spring' . 'Apex') . ' Primary';

    if (
        $primary_menu &&
        !is_wp_error($primary_menu) &&
        (string) ($primary_menu->name ?? '') === $legacy_menu_name
    ) {
        $renamed = wp_update_nav_menu_object($primary_id, ['menu-name' => 'ApexSpring Primary']);
        if (is_wp_error($renamed)) {
            return false;
        }
        $primary_menu = wp_get_nav_menu_object($primary_id);
        if (!$primary_menu || is_wp_error($primary_menu)) {
            return false;
        }
    }

    if ($primary_menu && !is_wp_error($primary_menu)) {
        if (!$footer_menu || is_wp_error($footer_menu)) {
            $locations['footer'] = $primary_id;
            set_theme_mod('nav_menu_locations', $locations);
            $footer_id = $primary_id;
        }

        $items_seeded = springapex_seed_primary_menu_items($primary_id);
        $children_seeded = springapex_seed_primary_menu_children($primary_id);

        $saved_locations = get_theme_mod('nav_menu_locations', []);
        $saved_footer_id = is_array($saved_locations) ? (int) ($saved_locations['footer'] ?? 0) : 0;
        $saved_footer_menu = $saved_footer_id > 0 ? wp_get_nav_menu_object($saved_footer_id) : false;
        return $items_seeded &&
            $children_seeded &&
            is_array($saved_locations) &&
            (int) ($saved_locations['primary'] ?? 0) === $primary_id &&
            $saved_footer_menu &&
            !is_wp_error($saved_footer_menu);
    }

    if (!$allow_create) {
        $legacy_menu = wp_get_nav_menu_object($legacy_menu_name);
        if (is_wp_error($legacy_menu)) {
            return false;
        }
        if ($legacy_menu) {
            return !is_wp_error(wp_update_nav_menu_object(
                (int) $legacy_menu->term_id,
                ['menu-name' => 'ApexSpring Primary']
            ));
        }
        return true;
    }

    unset($locations['primary']);
    if (!$footer_menu || is_wp_error($footer_menu)) {
        unset($locations['footer']);
        $footer_id = 0;
    }

    $menu = wp_get_nav_menu_object('ApexSpring Primary');
    if (is_wp_error($menu)) {
        return false;
    }

    if (!$menu) {
        $legacy_menu = wp_get_nav_menu_object($legacy_menu_name);
        if (is_wp_error($legacy_menu)) {
            return false;
        }
        if ($legacy_menu) {
            $renamed = wp_update_nav_menu_object((int) $legacy_menu->term_id, ['menu-name' => 'ApexSpring Primary']);
            if (is_wp_error($renamed)) {
                return false;
            }
            $menu = wp_get_nav_menu_object((int) $legacy_menu->term_id);
            if (!$menu || is_wp_error($menu)) {
                return false;
            }
        }
    }

    $menu_result = $menu ?: wp_create_nav_menu('ApexSpring Primary');
    if (is_wp_error($menu_result)) {
        return false;
    }

    $menu_id = $menu ? (int) $menu->term_id : (int) $menu_result;
    if ($menu_id <= 0) {
        return false;
    }

    if (!springapex_seed_primary_menu_items($menu_id)) {
        return false;
    }

    if (!springapex_seed_primary_menu_children($menu_id)) {
        return false;
    }

    $locations['primary'] = $menu_id;
    if ($footer_id <= 0) {
        $locations['footer'] = $menu_id;
    }
    set_theme_mod('nav_menu_locations', $locations);

    $saved_locations = get_theme_mod('nav_menu_locations', []);
    $saved_footer_id = is_array($saved_locations) ? (int) ($saved_locations['footer'] ?? 0) : 0;
    $saved_footer_menu = $saved_footer_id > 0 ? wp_get_nav_menu_object($saved_footer_id) : false;
    return is_array($saved_locations) &&
        (int) ($saved_locations['primary'] ?? 0) === $menu_id &&
        $saved_footer_menu &&
        !is_wp_error($saved_footer_menu);
}

/**
 * The first menu seed only wrote the seven top-level entries, so the dropdown
 * children defined by the default navigation (About Us, Industries, Custom
 * Springs, News) never reached the database and the desktop hover menus stayed
 * empty. Only top-level items that have no children at all are filled in; when
 * an administrator has configured sub-items of their own, the menu is left
 * untouched so deleted or renamed entries are never rebuilt.
 */
function springapex_seed_primary_menu_children(int $menu_id): bool
{
    $items = wp_get_nav_menu_items($menu_id);
    if (is_wp_error($items)) {
        return false;
    }

    $top_by_title = [];
    $has_children = [];
    foreach ($items ?: [] as $item) {
        if ((int) $item->menu_item_parent === 0) {
            $top_by_title[(string) $item->title] = $item;
        } else {
            $has_children[(int) $item->menu_item_parent] = true;
        }
    }

    $children_by_parent = [
        'About Us' => [
            ['Company', home_url('/about/')],
            ['Sustainability', home_url('/sustainability/')],
            ['Download Center', home_url('/resources/')],
        ],
        'Industries' => [
            ['Industries', home_url('/solutions/')],
            ['Case Studies', home_url('/case-studies/')],
        ],
        'Custom Springs' => [
            ['Capabilities', home_url('/capabilities/')],
            ['Manufacturing Videos', home_url('/manufacturing-videos/')],
        ],
        'News' => [
            ['Industry News', home_url('/news/') . '?news_type=industry-news'],
            ['Exhibitions', home_url('/news/') . '?news_type=exhibitions'],
            ['Company News', home_url('/news/') . '?news_type=company-news'],
        ],
    ];

    foreach ($children_by_parent as $parent_title => $children) {
        $parent = $top_by_title[$parent_title] ?? null;
        if (!$parent || !empty($has_children[(int) $parent->ID])) {
            continue;
        }

        foreach ($children as [$title, $url]) {
            $item_id = wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title' => $title,
                'menu-item-url' => $url,
                'menu-item-status' => 'publish',
                'menu-item-type' => 'custom',
                'menu-item-parent-id' => (int) $parent->ID,
            ]);
            if (is_wp_error($item_id) || (int) $item_id <= 0) {
                return false;
            }
        }
    }

    return true;
}

function springapex_seed_capabilities_menu_url(): bool
{
    $locations = get_theme_mod('nav_menu_locations', []);
    if (!is_array($locations)) {
        return true;
    }

    $menu_ids = array_unique(array_filter([
        (int) ($locations['primary'] ?? 0),
        (int) ($locations['footer'] ?? 0),
    ]));

    foreach ($menu_ids as $menu_id) {
        $items = wp_get_nav_menu_items($menu_id);
        if (is_wp_error($items)) {
            return false;
        }

        foreach ($items ?: [] as $item) {
            $title = sanitize_title((string) ($item->title ?? ''));
            $url = (string) ($item->url ?? '');
            if (
                $title !== 'capabilities' ||
                (!str_contains($url, '/about/#capabilities') && !str_contains($url, '/about#capabilities'))
            ) {
                continue;
            }

            $item_id = (int) ($item->db_id ?? $item->ID ?? 0);
            if ($item_id <= 0 || !springapex_seed_update_meta($item_id, '_menu_item_url', home_url('/capabilities/'))) {
                return false;
            }
        }
    }

    return true;
}
