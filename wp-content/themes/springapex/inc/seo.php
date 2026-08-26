<?php
/** Native SEO/TDK output for theme-managed routes and editable content. */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** @return array<string, array{label:string,path:string,title:string,description:string}> */
function springapex_seo_route_definitions(): array
{
    return [
        'home' => [
            'label' => '首页',
            'path' => '/',
            'title' => 'Precision Spring Manufacturer & Custom Springs | NorenSpring',
            'description' => 'NorenSpring manufactures compression, extension, torsion and custom springs with engineering support, prototyping, inspection and global delivery.',
        ],
        'products' => [
            'label' => '产品列表',
            'path' => '/products/',
            'title' => 'Precision Spring Products | NorenSpring',
            'description' => 'Explore compression, extension, torsion, disc, die and custom spring products manufactured for demanding industrial applications.',
        ],
        'solutions' => [
            'label' => '行业方案列表',
            'path' => '/solutions/',
            'title' => 'Spring Solutions by Industry | NorenSpring',
            'description' => 'Explore engineered spring solutions for automotive, industrial equipment, medical, aerospace, energy and rail applications.',
        ],
        'case-studies' => [
            'label' => '案例列表',
            'path' => '/case-studies/',
            'title' => 'Precision Spring Case Studies | NorenSpring',
            'description' => 'Review NorenSpring case studies covering spring design, manufacturing, quality control and application-specific production solutions.',
        ],
        'news' => [
            'label' => '新闻列表',
            'path' => '/news/',
            'title' => 'Spring Manufacturing News & Updates | NorenSpring',
            'description' => 'Read NorenSpring company news, manufacturing updates, quality developments and precision spring engineering insights.',
        ],
        'capabilities' => [
            'label' => '定制能力',
            'path' => '/capabilities/',
            'title' => 'Custom Spring Manufacturing Capabilities | NorenSpring',
            'description' => 'Learn about NorenSpring custom spring engineering, prototyping, CNC forming, heat treatment, inspection and production capabilities.',
        ],
        'manufacturing-videos' => [
            'label' => '制造视频',
            'path' => '/manufacturing-videos/',
            'title' => 'Precision Spring Manufacturing Videos | NorenSpring',
            'description' => 'Watch NorenSpring manufacturing videos covering spring forming, machine setup, inspection, material control and production processes.',
        ],
        'about' => [
            'label' => '关于我们',
            'path' => '/about/',
            'title' => 'About NorenSpring | Precision Spring Manufacturer',
            'description' => 'Learn about NorenSpring, a precision spring manufacturer supporting global projects from engineering review through controlled production and delivery.',
        ],
        'sustainability' => [
            'label' => '可持续发展',
            'path' => '/sustainability/',
            'title' => 'Sustainable Spring Manufacturing | NorenSpring',
            'description' => 'See how NorenSpring approaches materials, workplace safety, environmental management and continual improvement in spring manufacturing.',
        ],
        'resources' => [
            'label' => '资源下载',
            'path' => '/resources/',
            'title' => 'Spring Engineering Resources & Downloads | NorenSpring',
            'description' => 'Download NorenSpring product information, engineering references and company resources for precision spring sourcing and project planning.',
        ],
        'contact' => [
            'label' => '联系页面',
            'path' => '/contact/',
            'title' => 'Contact NorenSpring | Request a Spring Quote',
            'description' => 'Contact NorenSpring to discuss a spring application, upload a drawing or request engineering feedback and a production quotation.',
        ],
        'privacy' => [
            'label' => '隐私政策',
            'path' => '/privacy/',
            'title' => 'Privacy Policy | NorenSpring',
            'description' => 'Read the NorenSpring privacy policy and learn how website inquiries and personal information are handled.',
        ],
        'terms' => [
            'label' => '使用条款',
            'path' => '/terms/',
            'title' => 'Terms of Use | NorenSpring',
            'description' => 'Read the terms governing use of the NorenSpring website, content, downloads and inquiry services.',
        ],
        'sitemap' => [
            'label' => '网站地图',
            'path' => '/sitemap/',
            'title' => 'Sitemap | NorenSpring',
            'description' => 'Browse the NorenSpring website sitemap for products, industries, capabilities, resources, company information and contact pages.',
        ],
    ];
}

function springapex_seo_external_plugin_active(): bool
{
    return defined('WPSEO_VERSION')
        || defined('RANK_MATH_VERSION')
        || defined('AIOSEO_VERSION')
        || defined('SEOPRESS_VERSION')
        || defined('THE_SEO_FRAMEWORK_VERSION');
}

/** @return array<string, mixed> */
function springapex_seo_saved_settings(): array
{
    $settings = get_option('springapex_seo_settings', []);
    return is_array($settings) ? $settings : [];
}

/** @return array{title:string,description:string,keywords:string} */
function springapex_seo_route_values(string $route): array
{
    $definitions = springapex_seo_route_definitions();
    $definition = $definitions[$route] ?? ['title' => '', 'description' => ''];
    $settings = springapex_seo_saved_settings();
    $saved = is_array($settings['routes'][$route] ?? null) ? $settings['routes'][$route] : [];

    return [
        'title' => trim((string) ($saved['title'] ?? '')) ?: (string) ($definition['title'] ?? ''),
        'description' => trim((string) ($saved['description'] ?? '')) ?: (string) ($definition['description'] ?? ''),
        'keywords' => trim((string) ($saved['keywords'] ?? '')),
    ];
}

/** @return array<string, mixed> */
function springapex_sanitize_seo_settings(mixed $input): array
{
    $input = is_array($input) ? $input : [];
    $submitted_routes = is_array($input['routes'] ?? null) ? $input['routes'] : [];
    $output = ['routes' => []];

    foreach (springapex_seo_route_definitions() as $route => $_definition) {
        $row = is_array($submitted_routes[$route] ?? null) ? $submitted_routes[$route] : [];
        $output['routes'][$route] = [
            'title' => sanitize_text_field((string) ($row['title'] ?? '')),
            'description' => sanitize_textarea_field((string) ($row['description'] ?? '')),
            'keywords' => sanitize_text_field((string) ($row['keywords'] ?? '')),
        ];
    }

    return $output;
}

function springapex_seo_managed_route_for_page(WP_Post $post): string
{
    if ((int) get_option('page_on_front') === (int) $post->ID) {
        return 'home';
    }
    $slug = sanitize_key((string) $post->post_name);
    if ($slug === 'about-us') {
        return 'about';
    }
    return isset(springapex_seo_route_definitions()[$slug]) ? $slug : '';
}

function springapex_seo_clean_description(string $text): string
{
    $text = strip_shortcodes($text);
    $text = trim((string) preg_replace('/\s+/', ' ', wp_strip_all_tags($text)));
    return $text !== '' ? wp_html_excerpt($text, 300, '…') : '';
}

function springapex_seo_post_fallback_description(WP_Post $post): string
{
    $title = trim(get_the_title($post));
    return match ($post->post_type) {
        'spring_product' => sprintf(
            'Explore %s from NorenSpring, including application details, manufacturing capabilities, quality information and inquiry options.',
            $title
        ),
        'spring_solution' => sprintf(
            'Learn how NorenSpring supports %s applications with engineered spring design, controlled manufacturing and quality assurance.',
            $title
        ),
        'spring_case' => sprintf(
            'Read the %s case study from NorenSpring, covering application requirements, spring manufacturing and project results.',
            $title
        ),
        'spring_news' => sprintf(
            'Read %s from NorenSpring for company, manufacturing, quality and precision spring engineering updates.',
            $title
        ),
        default => get_bloginfo('description') !== ''
            ? (string) get_bloginfo('description')
            : sprintf('Learn more about %s at NorenSpring.', $title),
    };
}

/** @return array{title:string,description:string,keywords:string} */
function springapex_seo_post_values(WP_Post $post): array
{
    $title = trim((string) get_post_meta($post->ID, '_springapex_seo_title', true));
    $description = trim((string) get_post_meta($post->ID, '_springapex_seo_description', true));
    $keywords = trim((string) get_post_meta($post->ID, '_springapex_seo_keywords', true));

    if ($description === '') {
        $description = springapex_seo_clean_description(
            $post->post_excerpt !== '' ? $post->post_excerpt : $post->post_content
        );
    }
    if ($description === '') {
        $description = springapex_seo_post_fallback_description($post);
    }

    return compact('title', 'description', 'keywords');
}

/** @return array{title:string,description:string,keywords:string} */
function springapex_seo_current_values(): array
{
    $empty = ['title' => '', 'description' => '', 'keywords' => ''];
    if (is_admin() || springapex_seo_external_plugin_active()) {
        return $empty;
    }

    $detail_types = ['spring_product', 'spring_solution', 'spring_case', 'spring_news'];
    if (is_singular($detail_types)) {
        $post = get_queried_object();
        return $post instanceof WP_Post ? springapex_seo_post_values($post) : $empty;
    }

    $route = springapex_current_route();
    if (isset(springapex_seo_route_definitions()[$route])) {
        return springapex_seo_route_values($route);
    }

    if (is_page()) {
        $post = get_queried_object();
        return $post instanceof WP_Post ? springapex_seo_post_values($post) : $empty;
    }

    return $empty;
}

add_filter('pre_get_document_title', static function (string $title): string {
    $values = springapex_seo_current_values();
    return $values['title'] !== '' ? $values['title'] : $title;
}, 20);

add_action('wp_head', static function (): void {
    if (is_admin() || is_feed() || is_404() || springapex_seo_external_plugin_active()) {
        return;
    }
    $values = springapex_seo_current_values();
    if ($values['description'] !== '') {
        printf("<meta name=\"description\" content=\"%s\">\n", esc_attr($values['description']));
    }
    if ($values['keywords'] !== '') {
        printf("<meta name=\"keywords\" content=\"%s\">\n", esc_attr($values['keywords']));
    }
}, 1);

function springapex_seo_is_success_request(): bool
{
    $request_path = trim((string) wp_parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH), '/');
    $success_path = trim((string) wp_parse_url(home_url('/success/'), PHP_URL_PATH), '/');
    return $success_path !== '' && $request_path === $success_path;
}

add_filter('wp_robots', static function (array $robots): array {
    $success = springapex_seo_is_success_request();
    if (is_search() || is_404() || $success) {
        unset($robots['index']);
        $robots['noindex'] = true;
        if ($success) {
            unset($robots['follow']);
            $robots['nofollow'] = true;
        } else {
            unset($robots['nofollow']);
            $robots['follow'] = true;
        }
    }
    return $robots;
});
