<?php
/**
 * Minimal WordPress compatibility layer for local theme previews.
 *
 * This file intentionally implements only APIs used by the public templates.
 * It is not loaded by WordPress and must never be treated as a CMS runtime.
 */

declare(strict_types=1);

if (!in_array(PHP_SAPI, ['cli', 'cli-server'], true)) {
    http_response_code(404);
    exit;
}

defined('ABSPATH') || define('ABSPATH', __DIR__ . '/');
defined('SPRINGAPEX_PREVIEW') || define('SPRINGAPEX_PREVIEW', true);
defined('SPRINGAPEX_VERSION') || define('SPRINGAPEX_VERSION', '2.9.84');
defined('SPRINGAPEX_DIR') || define('SPRINGAPEX_DIR', dirname(__DIR__));
defined('SPRINGAPEX_URI') || define('SPRINGAPEX_URI', '');

$_SERVER['REQUEST_METHOD'] ??= 'GET';
$_SERVER['HTTP_HOST'] ??= '127.0.0.1';

function esc_html(mixed $text): string { return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_attr(mixed $text): string { return htmlspecialchars((string) $text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_url(mixed $url): string { return htmlspecialchars((string) $url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function esc_textarea(mixed $text): string { return esc_html($text); }
function wp_kses_post(mixed $text): string { return (string) $text; }
function wp_json_encode(mixed $data, int $options = 0, int $depth = 512): string|false { return json_encode($data, $options, $depth); }
// Turnstile is a live-WordPress feature; in the no-WP preview the widget cannot
// verify anything, so the site key is rendered for representative markup and the
// no-JS notice is suppressed (there is no secret configured here).
function springapex_turnstile_site_key(): string { return '0x4AAAAAAEUKf1Ep7E9Fafsj'; }
function springapex_turnstile_noscript(): string { return ''; }
function __(string $text, string $domain = 'default'): string { return $text; }
function esc_html__(string $text, string $domain = 'default'): string { return esc_html($text); }
function esc_html_e(string $text, string $domain = 'default'): void { echo esc_html($text); }
function esc_attr_e(string $text, string $domain = 'default'): void { echo esc_attr($text); }

function date_i18n(string $format, int|string $timestamp = 0): string
{
    if (is_string($timestamp)) {
        $timestamp = strtotime($timestamp) ?: 0;
    }
    return date($format, $timestamp);
}

function selected(mixed $selected, mixed $current = true, bool $display = true): string
{
    $result = (string) $selected === (string) $current ? ' selected="selected"' : '';
    if ($display) {
        echo $result;
    }
    return $result;
}

function checked(mixed $checked, mixed $current = true, bool $display = true): string
{
    $result = (string) $checked === (string) $current ? ' checked="checked"' : '';
    if ($display) {
        echo $result;
    }
    return $result;
}

function sanitize_key(mixed $key): string
{
    $key = strtolower((string) $key);
    return preg_replace('/[^a-z0-9_\-]/', '', $key) ?? '';
}

function sanitize_title(mixed $title): string
{
    $title = strtolower(trim((string) $title));
    return trim(preg_replace('/[^a-z0-9]+/', '-', $title) ?? '', '-');
}

function sanitize_text_field(mixed $value): string { return trim(strip_tags((string) $value)); }
function sanitize_email(mixed $email): string { return filter_var((string) $email, FILTER_SANITIZE_EMAIL) ?: ''; }
function sanitize_textarea_field(mixed $value): string { return trim(strip_tags((string) $value)); }

function wp_unslash(mixed $value): mixed
{
    if (is_array($value)) {
        return array_map('wp_unslash', $value);
    }
    return is_string($value) ? stripslashes($value) : $value;
}

// 无 WP 的预览里没有 options 表：表单 schema 与主题设置回退到默认值，
// 这样 contact / product / 快速询盘窗按内置默认字段渲染（与线上现状一致）。
function get_option(string $option, mixed $default = false): mixed { return $default; }
function get_theme_mod(string $name, mixed $default = false): mixed { return $default; }
// 预览是只读的：schema 迁移/版本盖章等写入调用（footer 的快速询盘窗会
// 触发 schema 加载）一律空操作，否则没有 options 表的预览运行时直接 fatal。
function update_option(string $option, mixed $value, mixed $autoload = null): bool { return true; }
function delete_option(string $option): bool { return true; }
function set_theme_mod(string $name, mixed $value): bool { return true; }
function remove_theme_mod(string $name): bool { return true; }

function springapex_preview_query_value(string $key): string
{
    $value = $_GET[$key] ?? '';
    return is_scalar($value) ? (string) $value : '';
}

function springapex_preview_route(): string
{
    $route = sanitize_key(springapex_preview_query_value('sa_page')) ?: 'home';
    $aliases = [
        'about-us' => 'about',
        'about-story' => 'about',
        'about-global' => 'about',
        'about-quality' => 'about',
        'news-item' => 'news-single',
    ];
    $route = $aliases[$route] ?? $route;

    return in_array($route, ['home', 'products', 'product', 'solutions', 'solution', 'case-studies', 'case-study', 'news', 'news-single', 'capabilities', 'manufacturing-videos', 'about', 'sustainability', 'contact', 'resources', 'search', 'privacy', 'terms', 'sitemap'], true)
        ? $route
        : 'home';
}

function springapex_preview_product_slug(): string
{
    return sanitize_title(springapex_preview_query_value('product')) ?: 'compression-springs';
}

function springapex_preview_solution_slug(): string
{
    return sanitize_title(springapex_preview_query_value('solution')) ?: 'automotive';
}

function springapex_preview_case_slug(): string
{
    return sanitize_title(springapex_preview_query_value('case'));
}

function springapex_preview_news_slug(): string
{
    return sanitize_title(springapex_preview_query_value('news')) ?: 'manufacturing-expo-bangkok-2024';
}

function springapex_preview_url(string $path = '/'): string
{
    if (preg_match('#^(?:https?:|mailto:|tel:)#i', $path)) {
        return $path;
    }

    $fragment = '';
    if (str_contains($path, '#')) {
        [$path, $fragment_part] = explode('#', $path, 2);
        $fragment = '#' . rawurlencode($fragment_part);
    }

    $query = '';
    if (str_contains($path, '?')) {
        [$path, $query] = explode('?', $path, 2);
    }

    $page_part = trim($path, '/');
    if ($page_part === '') {
        $url = '/preview/index.php';
    } elseif (preg_match('#^products/([^/]+)/?$#', $page_part, $match)) {
        $url = '/preview/index.php?sa_page=product&product=' . rawurlencode($match[1]);
    } elseif (preg_match('#^solutions/([^/]+)/?$#', $page_part, $match)) {
        $url = '/preview/index.php?sa_page=solution&solution=' . rawurlencode($match[1]);
    } elseif (preg_match('#^case-studies/([^/]+)/?$#', $page_part, $match)) {
        $url = '/preview/index.php?sa_page=case-study&case=' . rawurlencode($match[1]);
    } elseif (preg_match('#^news/([^/]+)/?$#', $page_part, $match)) {
        $url = '/preview/index.php?sa_page=news-single&news=' . rawurlencode($match[1]);
    } else {
        $map = [
            'products' => 'products',
            'solutions' => 'solutions',
            'case-studies' => 'case-studies',
            'news' => 'news',
            'about' => 'about',
            'about-us' => 'about',
            'about-story' => 'about',
            'sustainability' => 'sustainability',
            'contact' => 'contact',
            'capabilities' => 'capabilities',
            'manufacturing-videos' => 'manufacturing-videos',
            'resources' => 'resources',
            'search' => 'search',
            'privacy' => 'privacy',
            'terms' => 'terms',
            'sitemap' => 'sitemap',
        ];
        $url = '/preview/index.php?sa_page=' . rawurlencode($map[$page_part] ?? $page_part);
    }

    $separator = str_contains($url, '?') ? '&' : '?';
    if ($query !== '') {
        $url .= $separator . $query;
    }
    return $url . $fragment;
}

function home_url(string $path = '/'): string { return springapex_preview_url($path); }
function get_template_directory(): string { return SPRINGAPEX_DIR; }
function get_template_directory_uri(): string { return SPRINGAPEX_URI; }

function get_template_part(string $slug, ?string $name = null, array $args = []): void
{
    $candidates = [];
    if ($name) {
        $candidates[] = SPRINGAPEX_DIR . '/' . $slug . '-' . $name . '.php';
    }
    $candidates[] = SPRINGAPEX_DIR . '/' . $slug . '.php';
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            include $path;
            return;
        }
    }
}

function get_header(): void { include SPRINGAPEX_DIR . '/header.php'; }
function get_footer(): void { include SPRINGAPEX_DIR . '/footer.php'; }
function language_attributes(): void { echo 'lang="en"'; }
function bloginfo(string $show): void
{
    if ($show === 'charset') {
        echo 'UTF-8';
    }
}

function body_class(string|array $class = ''): void
{
    $route = springapex_preview_route();
    $classes = ['springapex-preview', 'sa-route-' . $route];
    if (in_array($route, ['about', 'about-story', 'sustainability', 'resources'], true)) {
        if ($route !== 'about') {
            $classes[] = 'sa-route-about';
        }
        $classes[] = 'sa-route-about-family';
    }
    if (is_array($class)) {
        $classes = array_merge($classes, $class);
    } elseif ($class !== '') {
        $classes[] = $class;
    }
    echo 'class="' . esc_attr(implode(' ', array_filter($classes))) . '"';
}
function wp_body_open(): void {}

function wp_head(): void
{
    $route = springapex_preview_route();
    $titles = [
        'home' => 'ApexSpring — Precision Springs',
        'products' => 'Products — ApexSpring',
        'product' => 'Compression Springs — ApexSpring',
        'solutions' => 'Solutions — ApexSpring',
        'solution' => 'Industry Solution — ApexSpring',
        'case-studies' => 'Case Studies — ApexSpring',
        'case-study' => 'Case Study — ApexSpring',
        'news' => 'News — ApexSpring',
        'news-single' => 'News — ApexSpring',
        'capabilities' => 'Capabilities — ApexSpring',
        'manufacturing-videos' => 'Manufacturing Videos — ApexSpring',
        'about' => 'About ApexSpring — Precision Springs',
        'sustainability' => 'Sustainability — ApexSpring',
        'contact' => 'Contact ApexSpring',
        'resources' => 'Download Center — ApexSpring',
        'search' => 'Search — ApexSpring',
        'privacy' => 'Privacy Policy — ApexSpring',
        'terms' => 'Terms of Use — ApexSpring',
        'sitemap' => 'Sitemap — ApexSpring',
    ];
    $hero_images = [
        'home' => 'hero-spring-v2.png',
        'products' => 'products-hero-v3.png',
        'solutions' => 'solutions-hero-v2.png',
        'case-studies' => 'solutions-hero-v2.png',
        'product' => 'product-compression-detail-v4.png',
        'solution' => 'solutions-hero-v2.png',
        'case-study' => 'solutions-hero-v2.png',
        'news' => 'generated/springapex-news-hero-v3.webp',
        'news-single' => 'generated/springapex-news-hero-v3.webp',
        'about' => 'about-building-v3.png',
        'sustainability' => 'home-energy-v2.png',
        'contact' => 'contact-springs-v2.png',
        'capabilities' => 'generated/springapex-capabilities-hero-v2.webp',
        'manufacturing-videos' => 'manufacturing-videos/hero-engineering-studio-v2.webp',
        'resources' => 'generated/springapex-resources-hero-v2.webp',
    ];

    if ($route === 'product' && function_exists('springapex_product')) {
        $product = springapex_product(springapex_preview_product_slug());
        if ($product) {
            $titles['product'] = (string) ($product['title'] ?? 'Product') . ' — ApexSpring';
            $product_image = $product['image'] ?? '';
            $hero_images['product'] = is_array($product_image)
                ? (string) ($product_image['file'] ?? '')
                : (string) $product_image;
        }
    }
    if ($route === 'solution' && function_exists('springapex_solution')) {
        $solution = springapex_solution(springapex_preview_solution_slug());
        if ($solution) {
            $titles['solution'] = (string) ($solution['title'] ?? 'Industry Solution') . ' — ApexSpring';
            $solution_image = $solution['image'] ?? '';
            $hero_images['solution'] = is_array($solution_image)
                ? (string) ($solution_image['file'] ?? '')
                : (string) $solution_image;
        }
    }
    if ($route === 'case-study' && function_exists('springapex_case')) {
        $case = springapex_case(springapex_preview_case_slug());
        if ($case) {
            $titles['case-study'] = (string) ($case['title'] ?? 'Case Study') . ' — ApexSpring';
            $case_image = $case['image'] ?? '';
            $hero_images['case-study'] = is_array($case_image)
                ? (string) ($case_image['file'] ?? '')
                : (string) $case_image;
        }
    }
    if ($route === 'news-single' && function_exists('springapex_news')) {
        $news_item = springapex_news(springapex_preview_news_slug());
        if ($news_item) {
            $titles['news-single'] = (string) ($news_item['title'] ?? 'News') . ' — ApexSpring';
            $news_image = $news_item['image'] ?? '';
            $hero_images['news-single'] = is_array($news_image)
                ? (string) ($news_image['file'] ?? '')
                : (string) $news_image;
        }
    }

    $version = esc_attr(SPRINGAPEX_VERSION . '.' . (string) time());
    $hero_image = $hero_images[$route] ?? '';
    if ($hero_image !== '') {
        $preload_url = '/assets/images/' . ltrim($hero_image, '/');
        $preload_type = '';
        $preload_srcset = '';
        $preload_sizes = [
            'home' => '(max-width: 760px) 100vw, 62vw',
            'products' => '100vw',
            'solutions' => '100vw',
            'case-studies' => '100vw',
            'solution' => '100vw',
            'case-study' => '100vw',
            'news' => '100vw',
            'news-single' => '100vw',
            'capabilities' => '100vw',
            'manufacturing-videos' => '100vw',
            'product' => '(max-width: 760px) 100vw, 50vw',
            'about' => '100vw',
            'contact' => '100vw',
        ][$route] ?? '100vw';
        if (function_exists('springapex_static_image_variants')) {
            $variants = springapex_static_image_variants($hero_image);
            if ($variants) {
                $preload_url = (string) $variants[0]['url'];
                $preload_type = (string) $variants[0]['type'];
                $preload_srcset = (string) ($variants[0]['srcset'] ?? '');
            }
        }
        $type_attribute = $preload_type !== '' ? ' type="' . esc_attr($preload_type) . '"' : '';
        $srcset_attribute = $preload_srcset !== '' ? ' imagesrcset="' . esc_attr($preload_srcset) . '" imagesizes="' . esc_attr($preload_sizes) . '"' : '';
        echo '<link rel="preload" as="image" href="' . esc_url($preload_url) . '"' . $type_attribute . $srcset_attribute . ' fetchpriority="high">' . "\n";
    }
    echo '<link rel="stylesheet" href="/assets/css/foundation.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/components.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/product-mega-menu.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/pages.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/company-introduction.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/responsive.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/enhancements.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/audit-fixes.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/content-dedup.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/visual-upgrade.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/news.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/capabilities-page.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/about-page.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/about-team.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/about-sections.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/search-page.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/products-page.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/mobile-polish.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/contact-network.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/case-studies.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/manufacturing-videos.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/product-details.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/product-compression.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/solution-detail.css?v=' . $version . '">' . "\n";
    echo '<link rel="stylesheet" href="/assets/css/solution-detail-responsive.css?v=' . $version . '">' . "\n";
    $descriptions = [
        'case-studies' => 'ApexSpring customer-approved precision spring case studies.',
        'case-study' => 'ApexSpring precision spring project case study.',
    ];
    echo '<meta name="description" content="' . esc_attr($descriptions[$route] ?? 'ApexSpring precision spring products and engineering solutions.') . '">' . "\n";
    echo '<meta name="robots" content="noindex,nofollow">' . "\n";
    echo '<title>' . esc_html($titles[$route] ?? $titles['home']) . '</title>' . "\n";
}

function wp_footer(): void
{
    echo '<script>window.ApexSpring=' . json_encode([
        'homeUrl' => home_url('/'),
        'themeUrl' => '',
        'ajaxUrl' => '',
        'nonce' => '',
        'maxFileSize' => 10 * 1024 * 1024,
        'contactEmail' => 'victoria@springapex.cn',
    ], JSON_UNESCAPED_SLASHES) . ';</script>' . "\n";
    echo '<script src="/assets/js/main.js?v=' . esc_attr(SPRINGAPEX_VERSION) . '"></script>' . "\n";
    echo '<script src="/assets/js/product-compression.js?v=' . esc_attr(SPRINGAPEX_VERSION) . '"></script>' . "\n";
}

function is_front_page(): bool { return springapex_preview_route() === 'home'; }
function is_home(): bool { return is_front_page(); }
function is_page(string $slug = ''): bool
{
    $route = springapex_preview_route();
    $map = ['about-us' => 'about'];
    return $slug === '' || ($map[$slug] ?? $slug) === $route;
}
function is_page_template(string $template = ''): bool { return false; }
function is_singular(string|array $post_type = ''): bool
{
    $route = springapex_preview_route();
    if ($route === 'product') {
        return $post_type === '' || $post_type === 'spring_product' || (is_array($post_type) && in_array('spring_product', $post_type, true));
    }
    if ($route === 'solution') {
        return $post_type === '' || $post_type === 'spring_solution' || (is_array($post_type) && in_array('spring_solution', $post_type, true));
    }
    if ($route === 'case-study') {
        return $post_type === '' || $post_type === 'spring_case' || (is_array($post_type) && in_array('spring_case', $post_type, true));
    }
    if ($route === 'news-single') {
        return $post_type === '' || $post_type === 'spring_news' || (is_array($post_type) && in_array('spring_news', $post_type, true));
    }
    return false;
}
function get_query_var(string $var, mixed $default = ''): mixed
{
    if ($var === 'product_slug') {
        return springapex_preview_product_slug();
    }
    if ($var === 'solution_slug') {
        return springapex_preview_solution_slug();
    }
    if ($var === 'case_slug') {
        return springapex_preview_case_slug();
    }
    if ($var === 'news_slug') {
        return springapex_preview_news_slug();
    }
    if ($var === 'sa_page') {
        return springapex_preview_route();
    }
    return $default;
}
// No queried object in the no-WP preview; templates use this only to stamp a
// source-page id on the inquiry form, which is irrelevant here.
function get_queried_object_id(): int { return 0; }
function status_header(int $code): void { http_response_code($code); }
function apply_filters(string $hook, mixed $value, mixed ...$args): mixed { return $value; }
