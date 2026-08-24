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

/**
 * Whether an image value still resolves to a usable media attachment, URL or
 * bundled theme file. This keeps deleted Media Library attachments from leaving
 * fixed-aspect empty frames on the public site.
 */
function springapex_image_value_available(mixed $image, string $base = 'assets/images/'): bool
{
    if (is_array($image)) {
        $attachment_id = (int) ($image['id'] ?? 0);
        if ($attachment_id > 0 && springapex_image_value_available($attachment_id, $base)) {
            return true;
        }
        return springapex_image_value_available((string) ($image['file'] ?? ''), $base);
    }

    if (!is_scalar($image)) {
        return false;
    }

    $value = trim((string) $image);
    if ($value === '') {
        return false;
    }
    if (ctype_digit($value)) {
        return !defined('SPRINGAPEX_PREVIEW')
            && function_exists('wp_get_attachment_image_url')
            && wp_get_attachment_image_url((int) $value, 'full') !== false;
    }
    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
        return true;
    }

    return is_file(springapex_asset_path(rtrim($base, '/') . '/' . ltrim($value, '/')));
}

/**
 * URL for a content-managed file that is either a media-library attachment ID
 * (what the admin's picker stores), an absolute URL, or a filename shipped
 * with the theme under $base.
 */
function springapex_file_url(int|string $value, string $base): string
{
    $value = is_int($value) ? (string) $value : trim($value);
    if ($value === '') {
        return '';
    }

    if (ctype_digit($value)) {
        if (!defined('SPRINGAPEX_PREVIEW') && function_exists('wp_get_attachment_url')) {
            return (string) wp_get_attachment_url((int) $value);
        }
        return '';
    }

    if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
        return $value;
    }

    return springapex_asset(rtrim($base, '/') . '/' . ltrim($value, '/'));
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

    if (function_exists('is_singular') && is_singular('spring_case')) {
        return 'case-study';
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
        if (is_post_type_archive('spring_case')) {
            return 'case-studies';
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
        foreach (['products', 'solutions', 'contact', 'capabilities', 'manufacturing-videos', 'resources', 'news', 'privacy', 'terms', 'sitemap', 'about-story', 'sustainability'] as $route) {
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
        return in_array($route, ['solutions', 'solution', 'case-studies', 'case-study'], true);
    }

    if ($slug === 'news') {
        return in_array($route, ['news', 'news-single'], true);
    }

    if ($slug === 'about-us') {
        return in_array($route, ['about', 'about-story', 'sustainability', 'resources'], true);
    }

    if ($slug === 'capabilities') {
        return in_array($route, ['capabilities', 'manufacturing-videos'], true);
    }

    return $route === $slug;
}

function springapex_about_navigation_items(): array
{
    return [
        ['label' => 'Company', 'route' => 'about', 'href' => '/about/'],
        ['label' => 'Sustainability', 'route' => 'sustainability', 'href' => '/sustainability/'],
        ['label' => 'Download Center', 'route' => 'resources', 'href' => '/resources/'],
    ];
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
        'facebook' => 'springapex_facebook',
        'x'         => 'springapex_x',
        'instagram' => 'springapex_instagram',
        'tiktok'    => 'springapex_tiktok',
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

    $children_by_parent = [];
    foreach ($items as $item) {
        $parent_id = (int) $item->menu_item_parent;
        if ($parent_id === 0) {
            continue;
        }
        $children_by_parent[$parent_id][] = [
            'label' => (string) $item->title,
            'href'  => (string) $item->url,
        ];
    }

    // Label, URL and order all come straight from 外观 → 菜单. This function used to
    // rewrite titles, force a fixed order and inject missing Home/News entries; that
    // made the menu screen a decoration — renaming or reordering there changed
    // nothing — and the fixed order was keyed on slugs the menu no longer used, so
    // it actually shuffled Industries and Custom Springs to the end of the bar.
    $nav = [];
    foreach ($items as $item) {
        if ((int) $item->menu_item_parent !== 0) {
            continue;
        }
        $entry = [
            'label'  => (string) $item->title,
            'slug'   => springapex_nav_slug_for_item($item),
            'href'   => (string) $item->url,
            'current'=> !empty($item->current) || !empty($item->current_item_ancestor),
        ];
        $children = $children_by_parent[(int) $item->ID] ?? [];
        if ($children) {
            $entry['children'] = $children;
        }
        $nav[] = $entry;
    }

    return $nav ?: $fallback;
}

/**
 * Stable identifier for a menu item, used for the active-state rules
 * (springapex_nav_is_active) and to decide which item carries the products mega
 * menu.
 *
 * Derived from what the item points at, never from its title. The operator renames
 * menu items in 外观 → 菜单, and a rename must not silently drop the mega menu or
 * the highlight — which is exactly what the old sanitize_title($label) did once the
 * menu said "Industries" instead of "Solutions".
 */
function springapex_nav_slug_for_item(object $item): string
{
    $archives = [
        'spring_product' => 'products',
        'spring_solution' => 'solutions',
        'spring_news' => 'news',
        'spring_case' => 'case-studies',
    ];
    // Paths the active-state rules know by a different name than the URL uses.
    $path_aliases = ['about' => 'about-us'];

    $type = (string) ($item->type ?? '');
    $object = (string) ($item->object ?? '');

    if ($type === 'post_type_archive' && isset($archives[$object])) {
        return $archives[$object];
    }

    if ($type === 'post_type' && $object === 'page') {
        $page_id = (int) ($item->object_id ?? 0);
        if ($page_id > 0) {
            if ((int) get_option('page_on_front') === $page_id) {
                return 'home';
            }
            $slug = sanitize_title((string) get_post_field('post_name', $page_id));
            return $path_aliases[$slug] ?? $slug;
        }
    }

    // Custom links, which is what the seeded menu uses. The archive links carry the
    // post type in the query string and have no path at all, so check that first or
    // they all read as the home page.
    $url = (string) ($item->url ?? '');
    $query = (string) (wp_parse_url($url, PHP_URL_QUERY) ?? '');
    if ($query !== '') {
        parse_str($query, $args);
        $post_type = (string) ($args['post_type'] ?? '');
        if (isset($archives[$post_type])) {
            return $archives[$post_type];
        }
    }

    $path = trim((string) (wp_parse_url($url, PHP_URL_PATH) ?? ''), '/');
    if ($path === '') {
        return 'home';
    }
    $first = sanitize_title(explode('/', $path)[0]);

    return $path_aliases[$first] ?? $first;
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
        'captcha' => [
            'type' => 'error',
            'message' => __('The anti-spam check could not be verified. Please try again.', 'springapex'),
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

/**
 * Icon name => file. The keys are what content and the admin icon picker use.
 */
function springapex_icon_map(): array
{
    return [
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
        'network' => 'network.svg',
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
        'youtube' => 'youtube.svg',
        'facebook' => 'facebook.svg',
        'x' => 'x.svg',
        'instagram' => 'instagram.svg',
        'tiktok' => 'tiktok.svg',
        'whatsapp' => 'whatsapp.svg',
        'user' => 'user.svg',
        'menu' => 'menu.svg',
        'close' => 'xmark.svg',
        'chat' => 'chat-lines.svg',
        'delivery' => 'delivery-truck.svg',
    ];
}

function springapex_icon(string $name, string $class = 'icon'): string
{
    $icons = springapex_icon_map();

    $file = $icons[$name] ?? $icons['arrow-right'];
    return sprintf(
        '<img class="%s" src="%s" alt="" aria-hidden="true" decoding="async">',
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

    $split_image_value = static function (mixed $value): array {
        if (is_array($value)) {
            return [(int) ($value['id'] ?? 0), trim((string) ($value['file'] ?? ''))];
        }
        if (is_int($value) || (is_string($value) && $value !== '' && ctype_digit($value))) {
            return [(int) $value, ''];
        }
        return [0, is_string($value) ? trim($value) : ''];
    };

    [$attachment_id, $fallback] = $split_image_value($image);
    [$mobile_attachment_id, $mobile_fallback] = $split_image_value($args['mobile_image']);

    $mobile_sources = '';
    $mobile_media = '(max-width: ' . preg_replace('/[^0-9a-z.%-]/i', '', (string) $args['mobile_breakpoint']) . ')';
    $mobile_sizes = $args['mobile_sizes']
        ? ' sizes="' . esc_attr((string) $args['mobile_sizes']) . '"'
        : '';

    if (
        $mobile_attachment_id > 0
        && !defined('SPRINGAPEX_PREVIEW')
        && function_exists('wp_get_attachment_image_url')
    ) {
        $mobile_url = (string) wp_get_attachment_image_url($mobile_attachment_id, 'full');
        $mobile_srcset = function_exists('wp_get_attachment_image_srcset')
            ? (string) wp_get_attachment_image_srcset($mobile_attachment_id, 'full')
            : '';
        if ($mobile_url !== '') {
            $mobile_sources = sprintf(
                '<source media="%s" srcset="%s"%s>',
                esc_attr($mobile_media),
                esc_attr($mobile_srcset !== '' ? $mobile_srcset : $mobile_url),
                $mobile_sizes
            );
        }
    }
    if ($mobile_sources === '' && $mobile_fallback !== '') {
        $mobile_is_url = str_starts_with($mobile_fallback, 'http://') || str_starts_with($mobile_fallback, 'https://');
        if ($mobile_is_url) {
            $mobile_sources = sprintf(
                '<source media="%s" srcset="%s"%s>',
                esc_attr($mobile_media),
                esc_attr($mobile_fallback),
                $mobile_sizes
            );
        } else {
            foreach (springapex_static_image_variants($mobile_fallback) as $variant) {
                $mobile_sources .= sprintf(
                    '<source media="%s" srcset="%s" type="%s"%s>',
                    esc_attr($mobile_media),
                    esc_attr((string) ($variant['srcset'] ?: $variant['url'])),
                    esc_attr((string) $variant['type']),
                    $mobile_sizes
                );
            }
        }
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
        $img = (string) wp_get_attachment_image($attachment_id, 'full', false, $attributes);
        if ($img !== '') {
            return $mobile_sources === ''
                ? $img
                : '<picture class="springapex-picture" style="display:contents">' . $mobile_sources . $img . '</picture>';
        }
    }

    if ($fallback === '') {
        return '';
    }

    $is_url = str_starts_with($fallback, 'http://') || str_starts_with($fallback, 'https://');
    $variants = $is_url ? [] : springapex_static_image_variants($fallback);
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
    $sources = $mobile_sources;
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
            $details = springapex_get('solution_details.' . $slug, []);
            if (!empty($solution['id']) && function_exists('springapex_solution_saved_details')) {
                $details = springapex_solution_saved_details((int) $solution['id'], $details);
            }
            return array_merge($solution, $details);
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
