<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_attr(string $value): string
{
    return esc_attr($value);
}

function springapex_asset(string $path): string
{
    $path = ltrim($path, '/');
    $base = defined('SPRINGAPEX_URI') ? rtrim((string) SPRINGAPEX_URI, '/') : '';
    return $base === '' ? '/' . $path : $base . '/' . $path;
}

function springapex_asset_path(string $path): string
{
    return rtrim((string) SPRINGAPEX_DIR, '/') . '/' . ltrim($path, '/');
}

function springapex_is_route(string $route): bool
{
    if (!defined('SPRINGAPEX_PREVIEW')) {
        return false;
    }

    $page = isset($_GET['sa_page']) ? sanitize_key((string) $_GET['sa_page']) : '';
    return $page === $route;
}

function springapex_current_route(): string
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        if (springapex_is_route('product')) {
            return 'product';
        }

        if (springapex_is_route('news-single')) {
            return 'news-single';
        }

        if (isset($_GET['sa_page']) && is_scalar($_GET['sa_page']) && $_GET['sa_page'] !== '') {
            $route = sanitize_key((string) $_GET['sa_page']);
            return $route === 'about-us' ? 'about' : $route;
        }

        return 'home';
    }

    if (function_exists('is_singular') && is_singular('spring_product')) {
        return 'product';
    }

    if (function_exists('is_singular') && is_singular('spring_solution')) {
        return 'solution';
    }

    if (function_exists('is_singular') && is_singular('spring_news')) {
        return 'news-single';
    }

    if (function_exists('is_post_type_archive')) {
        if (is_post_type_archive('spring_product')) {
            return 'products';
        }
        if (is_post_type_archive('spring_solution')) {
            return 'solutions';
        }
        if (is_post_type_archive('spring_news')) {
            return 'news';
        }
    }

    if (function_exists('is_front_page') && (is_front_page() || is_home())) {
        return 'home';
    }

    if (function_exists('is_search') && is_search()) {
        return 'search';
    }

    if (function_exists('is_page')) {
        foreach (['products', 'solutions', 'contact', 'capabilities', 'resources', 'news'] as $route) {
            if (is_page($route)) {
                return $route;
            }
        }
        if (is_page('about') || is_page('about-us')) {
            return 'about';
        }
    }

    return 'generic';
}

function springapex_nav_is_active(string $slug): bool
{
    $route = springapex_current_route();

    if ($slug === 'products') {
        return in_array($route, ['products', 'product'], true);
    }

    if ($slug === 'solutions') {
        return in_array($route, ['solutions', 'solution'], true);
    }

    if ($slug === 'news') {
        return in_array($route, ['news', 'news-single'], true);
    }

    return $slug === 'about-us' ? $route === 'about' : $route === $slug;
}

function springapex_brand(): array
{
    $brand = springapex_get('brand', []);
    if (!function_exists('get_theme_mod') || defined('SPRINGAPEX_PREVIEW')) {
        return $brand;
    }

    $theme_mods = [
        'email'    => 'springapex_email',
        'phone'    => 'springapex_phone',
        'whatsapp' => 'springapex_whatsapp',
        'address'  => 'springapex_address',
        'hours'    => 'springapex_hours',
        'linkedin' => 'springapex_linkedin',
    ];

    foreach ($theme_mods as $key => $setting) {
        $value = get_theme_mod($setting, $brand[$key] ?? '');
        if (is_string($value) && $value !== '') {
            $brand[$key] = $value;
        }
    }

    return $brand;
}

function springapex_navigation_items(string $location = 'primary'): array
{
    $fallback = springapex_get('nav', []);

    if (
        defined('SPRINGAPEX_PREVIEW') ||
        !function_exists('has_nav_menu') ||
        !has_nav_menu($location) ||
        !function_exists('get_nav_menu_locations')
    ) {
        return $fallback;
    }

    $locations = get_nav_menu_locations();
    $menu_id   = $locations[$location] ?? 0;
    $items     = $menu_id && function_exists('wp_get_nav_menu_items') ? wp_get_nav_menu_items($menu_id) : [];
    if (!$items || is_wp_error($items)) {
        return $fallback;
    }

    $nav = [];
    foreach ($items as $item) {
        if ((int) $item->menu_item_parent !== 0) {
            continue;
        }
        $label = (string) $item->title;
        $slug  = sanitize_title($label);
        $href  = (string) $item->url;
        $legacy_capabilities_url = false;
        if ($slug === 'news' || $slug === 'resources' || $slug === 'catalog' || $slug === 'view-our-catalog') {
            $label = 'News';
            $slug = 'news';
            $href = springapex_url('/news/');
        }
        if (
            $slug === 'capabilities' &&
            (str_contains($href, '/about/#capabilities') || str_contains($href, '/about#capabilities'))
        ) {
            $legacy_capabilities_url = true;
            $href = springapex_url('/capabilities/');
        }
        $match = [
            'about-us'    => 'about-us',
            'about'       => 'about-us',
            'capabilities'=> 'capabilities',
        ];
        $label_map = [
            'about-us' => 'About Us',
            'about' => 'About Us',
            'solutions' => 'Industries',
            'capabilities' => 'Custom Springs',
        ];
        $label = $label_map[$slug] ?? $label;
        $nav[] = [
            'label'  => $label,
            'slug'   => $match[$slug] ?? $slug,
            'href'   => $href,
            'current'=> !$legacy_capabilities_url && (!empty($item->current) || !empty($item->current_item_ancestor)),
        ];
    }

    if (!$nav) {
        return $fallback;
    }

    $has_news = false;
    foreach ($nav as $item) {
        if (($item['slug'] ?? '') === 'news') {
            $has_news = true;
            break;
        }
    }
    if (!$has_news && $location === 'primary') {
        $nav[] = [
            'label' => 'News',
            'slug' => 'news',
            'href' => springapex_url('/news/'),
            'current' => springapex_nav_is_active('news'),
        ];
    }

    $order = [
        'about-us' => 10,
        'products' => 20,
        'solutions' => 30,
        'capabilities' => 40,
        'news' => 50,
        'contact' => 60,
    ];
    usort($nav, static function (array $left, array $right) use ($order): int {
        return ($order[$left['slug']] ?? 999) <=> ($order[$right['slug']] ?? 999);
    });

    return $nav;
}

function springapex_search_query(): string
{
    if (defined('SPRINGAPEX_PREVIEW')) {
        $value = $_GET['s'] ?? '';
        return is_scalar($value) ? trim((string) $value) : '';
    }

    return function_exists('get_search_query') ? trim((string) get_search_query()) : '';
}

function springapex_navigation_href(string $href): string
{
    $href = trim($href);
    if ($href === '') {
        return springapex_url('/');
    }

    if (preg_match('#^(?:https?:|mailto:|tel:|sms:|ftp:|//|\#)#i', $href)) {
        return $href;
    }

    return springapex_url($href);
}

function springapex_contact_status_message(string $status): array
{
    $messages = [
        'success' => [
            'type' => 'success',
            'message' => __('Thank you. Your request has been received.', 'springapex'),
        ],
        'saved' => [
            'type' => 'success',
            'message' => __('Thank you. Your request was saved, but the notification email could not be sent.', 'springapex'),
        ],
        'invalid' => [
            'type' => 'error',
            'message' => __('Please complete the required fields with a valid email address.', 'springapex'),
        ],
        'rate' => [
            'type' => 'error',
            'message' => __('Too many requests were submitted. Please wait before trying again.', 'springapex'),
        ],
        'upload' => [
            'type' => 'error',
            'message' => __('The supporting file could not be accepted. Check its format and size, then try again.', 'springapex'),
        ],
        'upload_unavailable' => [
            'type' => 'error',
            'message' => __('Drawing uploads are temporarily unavailable. Submit without a drawing or contact us by email.', 'springapex'),
        ],
        'error' => [
            'type' => 'error',
            'message' => __('Unable to submit this request right now.', 'springapex'),
        ],
    ];

    return $messages[$status] ?? [];
}

function springapex_icon(string $name, string $class = 'icon'): string
{
    $icons = [
        'arrow-right' => 'arrow-right.svg',
        'arrow-up' => 'arrow-up.svg',
        'upload' => 'upload.svg',
        'download' => 'download.svg',
        'headset' => 'headset.svg',
        'shield' => 'shield-check.svg',
        'mail' => 'mail.svg',
        'phone' => 'phone.svg',
        'map-pin' => 'map-pin.svg',
        'clock' => 'clock.svg',
        'users' => 'community.svg',
        'factory' => 'industry.svg',
        'spring' => 'spiral.svg',
        'globe' => 'globe.svg',
        'pen' => 'edit-pencil.svg',
        'cubes' => 'cube.svg',
        'check-shield' => 'shield-check.svg',
        'target' => 'precision-tool.svg',
        'award' => 'medal.svg',
        'leaf' => 'leaf.svg',
        'cnc' => 'industry.svg',
        'qc' => 'badge-check.svg',
        'heat' => 'fire-flame.svg',
        'search' => 'search.svg',
        'car' => 'car.svg',
        'gear' => 'settings.svg',
        'rocket' => 'rocket.svg',
        'box' => 'package.svg',
        'wire' => 'spiral.svg',
        'disc' => 'compact-disc.svg',
        'torsion' => 'spiral.svg',
        'extension' => 'link.svg',
        'form' => 'ruler-combine.svg',
        'linkedin' => 'linkedin.svg',
        'user' => 'user.svg',
        'menu' => 'menu.svg',
        'close' => 'xmark.svg',
        'chat' => 'chat-lines.svg',
        'delivery' => 'delivery-truck.svg',
    ];

    $file = $icons[$name] ?? $icons['arrow-right'];
    return sprintf(
        '<img class="%s" src="%s" width="24" height="24" alt="" aria-hidden="true" decoding="async">',
        esc_attr($class),
        esc_url(springapex_asset('assets/icons/iconoir/' . $file))
    );
}

function springapex_static_image_variants(string $file): array
{
    $file = ltrim($file, '/');
    if (
        $file === '' ||
        str_contains($file, '..') ||
        str_starts_with($file, 'http://') ||
        str_starts_with($file, 'https://')
    ) {
        return [];
    }

    static $cache = [];
    if (array_key_exists($file, $cache)) {
        return $cache[$file];
    }

    $base = preg_replace('/\.[^.\/]+$/', '', $file) ?: $file;
    $source_path = springapex_asset_path('assets/images/' . $file);
    $source_size = is_file($source_path) ? @getimagesize($source_path) : false;
    $source_width = is_array($source_size) ? (int) ($source_size[0] ?? 0) : 0;
    $variants = [];
    foreach (['avif' => 'image/avif', 'webp' => 'image/webp'] as $extension => $mime) {
        $candidate = $base . '.' . $extension;
        if (!is_file(springapex_asset_path('assets/images/' . $candidate))) {
            continue;
        }

        $srcset = [];
        foreach ([480, 768, 1200, 1920, 2560] as $width) {
            $responsive_candidate = $base . '-' . $width . '.' . $extension;
            if (is_file(springapex_asset_path('assets/images/' . $responsive_candidate))) {
                $srcset[] = springapex_asset('assets/images/' . $responsive_candidate) . ' ' . $width . 'w';
            }
        }
        if ($source_width > 0) {
            $srcset[] = springapex_asset('assets/images/' . $candidate) . ' ' . $source_width . 'w';
        }

        $variants[] = [
            'file' => $candidate,
            'url' => springapex_asset('assets/images/' . $candidate),
            'type' => $mime,
            'srcset' => implode(', ', $srcset),
        ];
    }
    $cache[$file] = $variants;
    return $cache[$file];
}

function springapex_image(int|string|array $image, string $alt, array $args = []): string
{
    $defaults = [
        'class' => '',
        'loading' => 'lazy',
        'fetchpriority' => 'auto',
        'width' => null,
        'height' => null,
        'sizes' => null,
        'mobile_image' => null,
        'mobile_sizes' => '100vw',
        'mobile_breakpoint' => '860px',
    ];
    $args = array_merge($defaults, $args);

    $attachment_id = 0;
    $fallback      = '';
    if (is_array($image)) {
        $attachment_id = (int) ($image['id'] ?? 0);
        $fallback = (string) ($image['file'] ?? '');
    } elseif (is_int($image) || ctype_digit((string) $image)) {
        $attachment_id = (int) $image;
    } else {
        $fallback = $image;
    }

    if (
        $attachment_id > 0 &&
        !defined('SPRINGAPEX_PREVIEW') &&
        function_exists('wp_get_attachment_image')
    ) {
        $attributes = [
            'class' => (string) $args['class'],
            'loading' => (string) $args['loading'],
            'fetchpriority' => (string) $args['fetchpriority'],
            'decoding' => 'async',
            'alt' => $alt,
        ];
        if ($args['sizes']) {
            $attributes['sizes'] = (string) $args['sizes'];
        }
        return (string) wp_get_attachment_image($attachment_id, 'full', false, $attributes);
    }

    if ($fallback === '') {
        return '';
    }

    $is_url = str_starts_with($fallback, 'http://') || str_starts_with($fallback, 'https://');
    $variants = $is_url ? [] : springapex_static_image_variants($fallback);
    $mobile_fallback = is_string($args['mobile_image']) ? trim((string) $args['mobile_image']) : '';
    $mobile_is_url = str_starts_with($mobile_fallback, 'http://') || str_starts_with($mobile_fallback, 'https://');
    $mobile_variants = $mobile_fallback === '' || $mobile_is_url
        ? []
        : springapex_static_image_variants($mobile_fallback);
    $webp_fallback = null;
    foreach ($variants as $variant) {
        if (($variant['type'] ?? '') === 'image/webp') {
            $webp_fallback = $variant;
            break;
        }
    }

    $src = $is_url
        ? $fallback
        : (string) ($webp_fallback['url'] ?? springapex_asset('assets/images/' . ltrim($fallback, '/')));
    $attrs  = [
        'src="' . esc_url($src) . '"',
        'alt="' . esc_attr($alt) . '"',
        'loading="' . esc_attr((string) $args['loading']) . '"',
        'fetchpriority="' . esc_attr((string) $args['fetchpriority']) . '"',
        'decoding="async"',
    ];
    if ($args['class']) {
        $attrs[] = 'class="' . esc_attr((string) $args['class']) . '"';
    }
    if ($args['width']) {
        $attrs[] = 'width="' . (int) $args['width'] . '"';
    }
    if ($args['height']) {
        $attrs[] = 'height="' . (int) $args['height'] . '"';
    }
    if ($args['sizes']) {
        $attrs[] = 'sizes="' . esc_attr((string) $args['sizes']) . '"';
    }
    if (!empty($webp_fallback['srcset'])) {
        $attrs[] = 'srcset="' . esc_attr((string) $webp_fallback['srcset']) . '"';
    }

    $img = '<img ' . implode(' ', $attrs) . '>';
    if ($is_url) {
        return $img;
    }

    $sources = '';
    if ($mobile_fallback !== '') {
        $mobile_media = '(max-width: ' . preg_replace('/[^0-9a-z.%-]/i', '', (string) $args['mobile_breakpoint']) . ')';
        $mobile_sizes = $args['mobile_sizes']
            ? ' sizes="' . esc_attr((string) $args['mobile_sizes']) . '"'
            : '';
        if ($mobile_is_url) {
            $sources .= sprintf(
                '<source media="%s" srcset="%s"%s>',
                esc_attr($mobile_media),
                esc_attr($mobile_fallback),
                $mobile_sizes
            );
        } else {
            foreach ($mobile_variants as $variant) {
                $sources .= sprintf(
                    '<source media="%s" srcset="%s" type="%s"%s>',
                    esc_attr($mobile_media),
                    esc_attr((string) ($variant['srcset'] ?: $variant['url'])),
                    esc_attr((string) $variant['type']),
                    $mobile_sizes
                );
            }
        }
    }
    foreach ($variants as $variant) {
        if (($variant['type'] ?? '') === 'image/webp' && $webp_fallback) {
            continue;
        }
        $source_sizes = $args['sizes'] ? ' sizes="' . esc_attr((string) $args['sizes']) . '"' : '';
        $sources .= sprintf(
            '<source srcset="%s" type="%s"%s>',
            esc_attr((string) ($variant['srcset'] ?: $variant['url'])),
            esc_attr((string) $variant['type']),
            $source_sizes
        );
    }
    return $sources === '' ? $img : '<picture class="springapex-picture" style="display:contents">' . $sources . $img . '</picture>';
}

function springapex_product_url(array $product): string
{
    if (!empty($product['id']) && !defined('SPRINGAPEX_PREVIEW') && function_exists('get_permalink')) {
        return (string) get_permalink((int) $product['id']);
    }
    return springapex_url('/products/' . ($product['slug'] ?? '') . '/');
}

function springapex_related_products(string $current_slug, int $limit = 3): array
{
    $products = array_values(array_filter(
        springapex_products(),
        static fn(array $product): bool => (string) ($product['slug'] ?? '') !== $current_slug
    ));

    return array_slice($products, 0, max(0, $limit));
}

function springapex_solution_seed(string $slug): ?array
{
    foreach (springapex_get('solutions.items', []) as $item) {
        if ((string) ($item['slug'] ?? '') !== $slug) {
            continue;
        }

        return array_merge($item, springapex_get('solution_details.' . $slug, []));
    }

    return null;
}

function springapex_solution_url(array $solution): string
{
    if (!empty($solution['id']) && !defined('SPRINGAPEX_PREVIEW') && function_exists('get_permalink')) {
        return (string) get_permalink((int) $solution['id']);
    }

    return springapex_url('/solutions/' . ($solution['slug'] ?? '') . '/');
}

function springapex_news_url(array $news): string
{
    if (!empty($news['id']) && !defined('SPRINGAPEX_PREVIEW') && function_exists('get_permalink')) {
        return (string) get_permalink((int) $news['id']);
    }

    return springapex_url('/news/' . ($news['slug'] ?? '') . '/');
}

function springapex_related_news(string $current_slug, int $limit = 3): array
{
    $items = array_values(array_filter(
        springapex_news_list(),
        static fn(array $item): bool => (string) ($item['slug'] ?? '') !== $current_slug
    ));

    return array_slice($items, 0, max(0, $limit));
}

function springapex_solution(string $slug): ?array
{
    $slug = sanitize_title($slug);
    if ($slug === '') {
        return null;
    }

    if (defined('SPRINGAPEX_PREVIEW')) {
        return springapex_solution_seed($slug);
    }

    foreach (springapex_solutions() as $solution) {
        if ((string) ($solution['slug'] ?? '') === $slug) {
            return array_merge($solution, springapex_get('solution_details.' . $slug, []));
        }
    }

    return null;
}

if (!function_exists('springapex_url')) {
    function springapex_url(string $path = '/'): string
    {
        return function_exists('home_url') ? home_url($path) : $path;
    }
}
