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
    wp_enqueue_style(
        'springapex-foundation',
        SPRINGAPEX_URI . '/assets/css/foundation.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-components',
        SPRINGAPEX_URI . '/assets/css/components.css',
        ['springapex-foundation'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-product-mega-menu',
        SPRINGAPEX_URI . '/assets/css/product-mega-menu.css',
        ['springapex-components'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-pages',
        SPRINGAPEX_URI . '/assets/css/pages.css',
        ['springapex-product-mega-menu'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-company-introduction',
        SPRINGAPEX_URI . '/assets/css/company-introduction.css',
        ['springapex-pages'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-responsive',
        SPRINGAPEX_URI . '/assets/css/responsive.css',
        ['springapex-company-introduction'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-enhancements',
        SPRINGAPEX_URI . '/assets/css/enhancements.css',
        ['springapex-responsive'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-audit-fixes',
        SPRINGAPEX_URI . '/assets/css/audit-fixes.css',
        ['springapex-enhancements'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-content-dedup',
        SPRINGAPEX_URI . '/assets/css/content-dedup.css',
        ['springapex-audit-fixes'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-news',
        SPRINGAPEX_URI . '/assets/css/news.css',
        ['springapex-content-dedup'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-capabilities-page',
        SPRINGAPEX_URI . '/assets/css/capabilities-page.css',
        ['springapex-news'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-about-page',
        SPRINGAPEX_URI . '/assets/css/about-page.css',
        ['springapex-capabilities-page'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-about-team',
        SPRINGAPEX_URI . '/assets/css/about-team.css',
        ['springapex-about-page'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-about-sections',
        SPRINGAPEX_URI . '/assets/css/about-sections.css',
        ['springapex-about-team'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-search-page',
        SPRINGAPEX_URI . '/assets/css/search-page.css',
        ['springapex-about-sections'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-products-page',
        SPRINGAPEX_URI . '/assets/css/products-page.css',
        ['springapex-search-page'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-mobile-polish',
        SPRINGAPEX_URI . '/assets/css/mobile-polish.css',
        ['springapex-products-page'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-contact-network',
        SPRINGAPEX_URI . '/assets/css/contact-network.css',
        ['springapex-mobile-polish'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-case-studies',
        SPRINGAPEX_URI . '/assets/css/case-studies.css',
        ['springapex-contact-network'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-manufacturing-videos',
        SPRINGAPEX_URI . '/assets/css/manufacturing-videos.css',
        ['springapex-case-studies'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-product-details',
        SPRINGAPEX_URI . '/assets/css/product-details.css',
        ['springapex-manufacturing-videos'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-product-compression',
        SPRINGAPEX_URI . '/assets/css/product-compression.css',
        ['springapex-product-details'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-solution-detail',
        SPRINGAPEX_URI . '/assets/css/solution-detail.css',
        ['springapex-product-compression'],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_style(
        'springapex-solution-detail-responsive',
        SPRINGAPEX_URI . '/assets/css/solution-detail-responsive.css',
        ['springapex-solution-detail'],
        SPRINGAPEX_VERSION
    );

    wp_enqueue_script(
        'springapex-main',
        SPRINGAPEX_URI . '/assets/js/main.js',
        [],
        SPRINGAPEX_VERSION,
        ['strategy' => 'defer', 'in_footer' => true]
    );
    wp_enqueue_script(
        'springapex-product-compression',
        SPRINGAPEX_URI . '/assets/js/product-compression.js',
        ['springapex-main'],
        SPRINGAPEX_VERSION,
        ['strategy' => 'defer', 'in_footer' => true]
    );

    // Cloudflare Turnstile auto-render script. Enqueued site-wide (not tied to
    // the support widget, which is hidden on the contact and compression pages
    // where the inquiry forms — and their widgets — actually live).
    wp_enqueue_script(
        'springapex-turnstile',
        'https://challenges.cloudflare.com/turnstile/v0/api.js',
        [],
        null,
        ['strategy' => 'defer', 'in_footer' => true]
    );

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
    wp_localize_script('springapex-main', 'ApexSpring', [
        'homeUrl' => home_url('/'),
        'themeUrl' => SPRINGAPEX_URI,
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('springapex_contact'),
        'maxFileSize' => 10 * MB_IN_BYTES,
        'contactEmail' => $brand['email'] ?? '',
        // 非弹窗表单提交成功后跳转的落地页，用于转化统计。
        'successUrl' => home_url('/success/'),
    ]);
});

// Fallback so /success/ renders even before the seeded page exists — e.g. the
// window right after a file-sync deploy, before an admin has triggered the
// version-gated reseed. Without it, non-widget form submissions would redirect
// to a 404 during that window. Once the page is seeded, is_404() is false here
// and the normal page-success.php template renders instead.
add_action('template_redirect', static function (): void {
    if (is_admin() || !is_404()) {
        return;
    }
    $request_path = trim((string) parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    $success_path = trim((string) parse_url(home_url('/success/'), PHP_URL_PATH), '/');
    if ($success_path === '' || $request_path !== $success_path) {
        return;
    }
    status_header(200);
    get_header();
    get_template_part('templates/success');
    get_footer();
    exit;
});

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
        'sustainability' => 'home-energy-v2.png',
        'contact' => springapex_get('contact.hero.image', ''),
        'resources' => 'generated/springapex-resources-hero-v2.webp',
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
        'resources' => '100vw',
        'contact' => '100vw',
    ];

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
        $image = (string) ($images[$route] ?? '');
        if ($image !== '') {
            $variants = springapex_static_image_variants($image);
            if ($variants) {
                $image_url = (string) $variants[0]['url'];
                $image_type = (string) $variants[0]['type'];
                $image_srcset = (string) ($variants[0]['srcset'] ?? '');
                $image_sizes = (string) ($static_sizes[$route] ?? '100vw');
            } else {
                $image_url = springapex_asset('assets/images/' . $image);
            }
        }
    }

    if ($image_url !== '') {
        $attributes = ' href="' . esc_url($image_url) . '"';
        if ($image_srcset !== '') {
            $attributes .= ' imagesrcset="' . esc_attr($image_srcset) . '"';
        }
        if ($image_sizes !== '') {
            $attributes .= ' imagesizes="' . esc_attr($image_sizes) . '"';
        }
        if ($image_type !== '') {
            $attributes .= ' type="' . esc_attr($image_type) . '"';
        }
        echo '<link rel="preload" as="image"' . $attributes . ' fetchpriority="high">' . "\n";
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
