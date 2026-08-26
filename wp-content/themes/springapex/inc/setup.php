<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('after_setup_theme', static function (): void {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height' => 48,
        'width' => 240,
        'flex-height' => true,
        'flex-width' => true,
        'unlink-homepage-logo' => false,
    ]);

    register_nav_menus([
        'primary' => __('Primary Navigation', 'springapex'),
        'footer' => __('Footer Navigation', 'springapex'),
    ]);

    add_image_size('springapex-card', 800, 600, true);
    add_image_size('springapex-hero', 1800, 1000, false);
    $GLOBALS['content_width'] = 1120;
});

add_action('wp_enqueue_scripts', static function (): void {
    $route = springapex_current_route();
    $last_style = '';
    // 保持历史 CSS 的精确级联顺序，但只输出当前路由真正需要的文件。
    // 每个已加载文件依赖上一个已加载文件，避免条件分支改变覆盖顺序。
    $enqueue_style = static function (string $handle, string $file) use (&$last_style): void {
        wp_enqueue_style(
            $handle,
            SPRINGAPEX_URI . '/assets/css/' . $file,
            $last_style !== '' ? [$last_style] : [],
            SPRINGAPEX_VERSION
        );
        $last_style = $handle;
    };

    $enqueue_style('springapex-foundation', 'foundation.css');
    $enqueue_style('springapex-components', 'components.css');
    $enqueue_style('springapex-product-mega-menu', 'product-mega-menu.css');
    $enqueue_style('springapex-pages', 'pages.css');

    if (in_array($route, ['home', 'about', 'about-story'], true)) {
        $enqueue_style('springapex-company-introduction', 'company-introduction.css');
    }

    $enqueue_style('springapex-responsive', 'responsive.css');
    $enqueue_style('springapex-enhancements', 'enhancements.css');
    $enqueue_style('springapex-audit-fixes', 'audit-fixes.css');

    if (in_array($route, ['home', 'products', 'product', 'capabilities', 'about', 'about-story'], true)) {
        $enqueue_style('springapex-content-dedup', 'content-dedup.css');
    }
    if (in_array($route, ['news', 'news-single'], true)) {
        $enqueue_style('springapex-news', 'news.css');
    }
    if ($route === 'capabilities') {
        $enqueue_style('springapex-capabilities-page', 'capabilities-page.css');
    }
    if (in_array($route, ['about', 'about-story'], true)) {
        $enqueue_style('springapex-about-page', 'about-page.css');
        $enqueue_style('springapex-about-team', 'about-team.css');
    }
    if (in_array($route, ['about', 'about-story', 'sustainability', 'resources'], true)) {
        $enqueue_style('springapex-about-sections', 'about-sections.css');
    }
    if ($route === 'search') {
        $enqueue_style('springapex-search-page', 'search-page.css');
    }
    if ($route === 'products') {
        $enqueue_style('springapex-products-page', 'products-page.css');
    }

    // 跨页面移动端覆盖必须晚于上面的基础和第一组页面样式。
    $enqueue_style('springapex-mobile-polish', 'mobile-polish.css');

    if ($route === 'contact') {
        $enqueue_style('springapex-contact-network', 'contact-network.css');
    }
    if (in_array($route, ['solutions', 'case-studies', 'case-study'], true)) {
        $enqueue_style('springapex-case-studies', 'case-studies.css');
    }
    if (in_array($route, ['capabilities', 'manufacturing-videos'], true)) {
        $enqueue_style('springapex-manufacturing-videos', 'manufacturing-videos.css');
    }
    if ($route === 'product') {
        $enqueue_style('springapex-product-details', 'product-details.css');
        $enqueue_style('springapex-product-compression', 'product-compression.css');
    }
    if ($route === 'solution') {
        $enqueue_style('springapex-solution-detail', 'solution-detail.css');
        $enqueue_style('springapex-solution-detail-responsive', 'solution-detail-responsive.css');
    }

    wp_enqueue_script(
        'springapex-main',
        SPRINGAPEX_URI . '/assets/js/main.js',
        [],
        SPRINGAPEX_VERSION,
        ['strategy' => 'defer', 'in_footer' => true]
    );
    if ($route === 'product') {
        wp_enqueue_script(
            'springapex-product-compression',
            SPRINGAPEX_URI . '/assets/js/product-compression.js',
            ['springapex-main'],
            SPRINGAPEX_VERSION,
            ['strategy' => 'defer', 'in_footer' => true]
        );
    }

    // Interactive contact map: Leaflet + open tiles, only on the contact page.
    if (springapex_current_route() === 'contact') {
        wp_enqueue_style(
            'springapex-leaflet',
            SPRINGAPEX_URI . '/assets/vendor/leaflet/leaflet.css',
            [],
            '1.9.4'
        );
        wp_enqueue_style(
            'springapex-contact-map',
            SPRINGAPEX_URI . '/assets/css/contact-map.css',
            ['springapex-leaflet', 'springapex-contact-network'],
            SPRINGAPEX_VERSION
        );
        wp_enqueue_script(
            'springapex-leaflet',
            SPRINGAPEX_URI . '/assets/vendor/leaflet/leaflet.js',
            [],
            '1.9.4',
            ['strategy' => 'defer', 'in_footer' => true]
        );
        wp_enqueue_script(
            'springapex-contact-map',
            SPRINGAPEX_URI . '/assets/js/contact-map.js',
            ['springapex-leaflet'],
            SPRINGAPEX_VERSION,
            ['strategy' => 'defer', 'in_footer' => true]
        );
    }

    $brand = springapex_brand();
    wp_localize_script('springapex-main', 'NorenSpring', [
        'homeUrl' => home_url('/'),
        'themeUrl' => SPRINGAPEX_URI,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('springapex_contact'),
        'maxFileSize' => 10 * MB_IN_BYTES,
        'contactEmail' => $brand['email'] ?? '',
        // 第三方 CAPTCHA 不进入首屏关键路径；main.js 在用户接近表单时加载。
        'turnstileUrl' => 'https://challenges.cloudflare.com/turnstile/v0/api.js',
        // 非弹窗表单提交成功后跳转的落地页，用于转化统计。
        'successUrl' => home_url('/success/'),
    ]);
});

// /success is served by the theme, not a seeded WP page — so it works the
// moment the code is deployed (no seed/flush timing) and never adopts or
// clobbers an operator's own page at that slug. If such a page exists, WP
// resolves it (is_404() false) and it renders instead; otherwise this handler
// renders the thank-you template for the form-success redirect target.
// Priority 9 keeps this ahead of redirect_canonical (priority 10, registered
// by core before the theme), whose 404 permalink guessing could otherwise
// redirect /success to an unrelated post with a similar slug.
add_action('template_redirect', static function (): void {
    if (is_admin() || !is_404()) {
        return;
    }
    $request_path = trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    $success_path = trim((string) parse_url(home_url('/success/'), PHP_URL_PATH), '/');
    if ($success_path === '' || $request_path !== $success_path) {
        return;
    }
    // status_header() alone leaves the 404 query flags in place, which would
    // leak "Page not found" into the document title and error404 into
    // body_class() — reset them so the response is treated as a real page.
    global $wp_query;
    $wp_query->is_404 = false;
    $wp_query->is_page = true;
    add_filter('pre_get_document_title', static function (): string {
        return __('Thank You', 'springapex') . ' — ' . get_bloginfo('name');
    });
    status_header(200);
    get_header();
    get_template_part('templates/success');
    get_footer();
    exit;
}, 9);

add_action('wp_head', static function (): void {
    if (is_admin()) {
        return;
    }

    $route = springapex_current_route();
    $images = [
        'home' => springapex_get('home.hero.image', ''),
        'products' => springapex_get('products.hero.image', ''),
        'solutions' => springapex_get('solutions.hero.image', ''),
        'case-studies' => springapex_get('case_studies.hero.image', ''),
        'capabilities' => springapex_get('capabilities.hero.image', ''),
        'manufacturing-videos' => springapex_get('manufacturing_videos.hero_image', ''),
        'about' => springapex_get('about.hero.image', 'about-building-v3.png'),
        'sustainability' => springapex_get('sustainability.hero.image', 'generated/apexspring-sustainability-wire-lifecycle-v1.png'),
        'news' => springapex_get('news.hero.image', 'generated/springapex-news-hero-v3.webp'),
        'contact' => springapex_get('contact_network.facility_image', 'facility-aerial-original.webp'),
        'resources' => springapex_get('resources.hero.image', 'generated/springapex-resources-hero-v2.webp'),
    ];
    $mobile_images = [
        'home' => springapex_get('home.hero.mobile_image', ''),
        'products' => springapex_get('products.hero.mobile_image', ''),
        'solutions' => springapex_get('solutions.hero.mobile_image', ''),
        'case-studies' => springapex_get('case_studies.hero.mobile_image', ''),
        'capabilities' => springapex_get('capabilities.hero.mobile_image', ''),
        'manufacturing-videos' => springapex_get('manufacturing_videos.hero_mobile_image', ''),
        'about' => springapex_get('about.hero.mobile_image', ''),
        'sustainability' => springapex_get('sustainability.hero.mobile_image', ''),
        'news' => springapex_get('news.hero.mobile_image', ''),
        'resources' => springapex_get('resources.hero.mobile_image', ''),
    ];
    $static_sizes = [
        'home' => '(max-width: 760px) 100vw, 62vw',
        'products' => '100vw',
        'solutions' => '100vw',
        'case-studies' => '100vw',
        'capabilities' => '100vw',
        'manufacturing-videos' => '100vw',
        'about' => '100vw',
        'sustainability' => '100vw',
        'news' => '100vw',
        'resources' => '100vw',
        'contact' => '(max-width: 980px) 100vw, 50vw',
    ];

    $resolve_preload_image = static function (mixed $image, string $sizes): array {
        $attachment_id = 0;
        $fallback = '';
        if (is_array($image)) {
            $attachment_id = (int) ($image['id'] ?? 0);
            $fallback = trim((string) ($image['file'] ?? ''));
        } elseif (is_int($image) || (is_string($image) && $image !== '' && ctype_digit($image))) {
            $attachment_id = (int) $image;
        } elseif (is_string($image)) {
            $fallback = trim($image);
        }

        if ($attachment_id > 0 && function_exists('wp_get_attachment_image_url')) {
            $url = (string) wp_get_attachment_image_url($attachment_id, 'full');
            if ($url !== '') {
                $srcset = function_exists('wp_get_attachment_image_srcset')
                    ? (string) (wp_get_attachment_image_srcset($attachment_id, 'full') ?: '')
                    : '';
                return [$url, '', $srcset, $sizes];
            }
        }
        if ($fallback === '') {
            return ['', '', '', ''];
        }
        if (str_starts_with($fallback, 'http://') || str_starts_with($fallback, 'https://')) {
            return [$fallback, '', '', $sizes];
        }

        $variants = springapex_static_image_variants($fallback);
        if ($variants) {
            return [
                (string) $variants[0]['url'],
                (string) $variants[0]['type'],
                (string) ($variants[0]['srcset'] ?? ''),
                $sizes,
            ];
        }
        return [springapex_asset('assets/images/' . $fallback), '', '', $sizes];
    };

    $image_url = '';
    $image_type = '';
    $image_srcset = '';
    $image_sizes = '';

    if ($route === 'product' && is_singular('spring_product')) {
        $post_id = get_queried_object_id();
        $thumbnail_id = $post_id > 0 ? (int) get_post_thumbnail_id($post_id) : 0;
        if ($thumbnail_id > 0) {
            $image_url = (string) wp_get_attachment_image_url($thumbnail_id, 'full');
            $image_srcset = (string) (wp_get_attachment_image_srcset($thumbnail_id, 'full') ?: '');
            $image_sizes = '(max-width: 760px) 100vw, 50vw';
        } elseif ($post_id > 0) {
            $has_seed_image = metadata_exists('post', $post_id, '_springapex_seed_image');
            $image = $has_seed_image ? (string) get_post_meta($post_id, '_springapex_seed_image', true) : '';
            if (!$has_seed_image) {
                $slug = (string) get_post_field('post_name', $post_id);
                $image = (string) (springapex_product_seed($slug)['image'] ?? '');
            }
            if ($image !== '') {
                $variants = springapex_static_image_variants($image);
                if ($variants) {
                    $image_url = (string) $variants[0]['url'];
                    $image_type = (string) $variants[0]['type'];
                    $image_srcset = (string) ($variants[0]['srcset'] ?? '');
                    $image_sizes = '(max-width: 760px) 100vw, 50vw';
                } else {
                    $image_url = springapex_asset('assets/images/' . $image);
                }
            }
        }
    } elseif ($route === 'solution' && is_singular('spring_solution')) {
        $post_id = get_queried_object_id();
        $thumbnail_id = $post_id > 0 ? (int) get_post_thumbnail_id($post_id) : 0;
        if ($thumbnail_id > 0) {
            $image_url = (string) wp_get_attachment_image_url($thumbnail_id, 'full');
            $image_srcset = (string) (wp_get_attachment_image_srcset($thumbnail_id, 'full') ?: '');
            $image_sizes = '100vw';
        } elseif ($post_id > 0) {
            $slug = (string) get_post_field('post_name', $post_id);
            $image = (string) (springapex_solution_seed($slug)['image'] ?? '');
            $variants = springapex_static_image_variants($image);
            if ($variants) {
                $image_url = (string) $variants[0]['url'];
                $image_type = (string) $variants[0]['type'];
                $image_srcset = (string) ($variants[0]['srcset'] ?? '');
                $image_sizes = '100vw';
            } elseif ($image !== '') {
                $image_url = springapex_asset('assets/images/' . $image);
            }
        }
    } elseif ($route === 'case-study' && is_singular('spring_case')) {
        $post_id = get_queried_object_id();
        $thumbnail_id = $post_id > 0 ? (int) get_post_thumbnail_id($post_id) : 0;
        if ($thumbnail_id > 0) {
            $image_url = (string) wp_get_attachment_image_url($thumbnail_id, 'full');
            $image_srcset = (string) (wp_get_attachment_image_srcset($thumbnail_id, 'full') ?: '');
            $image_sizes = '100vw';
        }
    } else {
        [$image_url, $image_type, $image_srcset, $image_sizes] = $resolve_preload_image(
            $images[$route] ?? '',
            (string) ($static_sizes[$route] ?? '100vw')
        );
    }

    [$mobile_url, $mobile_type, $mobile_srcset, $mobile_sizes] = $resolve_preload_image(
        $mobile_images[$route] ?? '',
        '100vw'
    );
    $emit_preload = static function (
        string $url,
        string $type,
        string $srcset,
        string $sizes,
        string $media = ''
    ): void {
        if ($url === '') {
            return;
        }
        $attributes = ' href="' . esc_url($url) . '"';
        if ($srcset !== '') {
            $attributes .= ' imagesrcset="' . esc_attr($srcset) . '"';
        }
        if ($sizes !== '') {
            $attributes .= ' imagesizes="' . esc_attr($sizes) . '"';
        }
        if ($type !== '') {
            $attributes .= ' type="' . esc_attr($type) . '"';
        }
        if ($media !== '') {
            $attributes .= ' media="' . esc_attr($media) . '"';
        }
        echo '<link rel="preload" as="image"' . $attributes . ' fetchpriority="high">' . "\n";
    };

    $has_mobile_preload = $mobile_url !== '';
    if ($has_mobile_preload) {
        $emit_preload($mobile_url, $mobile_type, $mobile_srcset, $mobile_sizes, '(max-width: 860px)');
    }
    if ($image_url !== '') {
        $emit_preload(
            $image_url,
            $image_type,
            $image_srcset,
            $image_sizes,
            $has_mobile_preload ? '(min-width: 860.01px)' : ''
        );
    }
}, 2);

add_filter('body_class', static function (array $classes): array {
    $route = springapex_current_route();
    $classes[] = 'sa-route-' . sanitize_html_class($route);
    if (in_array($route, ['about', 'about-story', 'sustainability', 'resources'], true)) {
        if ($route !== 'about') {
            $classes[] = 'sa-route-about';
        }
        $classes[] = 'sa-route-about-family';
    }
    return $classes;
});

add_filter('excerpt_length', static fn(): int => 24, 99);
