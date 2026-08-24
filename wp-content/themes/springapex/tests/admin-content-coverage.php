<?php
/**
 * Regression guard: every major public-page business section must have an
 * owning field on the expected 网站内容 screen and a seeded content value.
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}

require dirname(__DIR__) . '/preview/bootstrap.php';
defined('SPRINGAPEX_ADMIN_SLUG') || define('SPRINGAPEX_ADMIN_SLUG', 'springapex-content');
defined('SPRINGAPEX_ADMIN_CAP') || define('SPRINGAPEX_ADMIN_CAP', 'edit_theme_options');
require dirname(__DIR__) . '/inc/content.php';
require dirname(__DIR__) . '/inc/helpers.php';
require dirname(__DIR__) . '/inc/form-schema.php';
require dirname(__DIR__) . '/inc/admin/schema.php';
if (!function_exists('wp_parse_url')) {
    function wp_parse_url(string $url, int $component = -1): mixed
    {
        return $component === -1 ? parse_url($url) : parse_url($url, $component);
    }
}
if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url, array $protocols = []): string
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        return $protocols === [] || in_array($scheme, $protocols, true) ? filter_var($url, FILTER_SANITIZE_URL) : '';
    }
}
if (!function_exists('is_email')) {
    function is_email(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
require dirname(__DIR__) . '/inc/admin/sanitize.php';

$required = [
    'home' => ['home.hero.title', 'home.sections.products.title', 'home.sections.why.title', 'home.sections.process.title', 'home.sections.industries.title', 'home.pillars', 'home.process'],
    'faq' => ['home_faq'],
    'products' => ['products.hero.title', 'products.entry.title', 'products.entry.items', 'products.range.title', 'product_selection.eyebrow', 'product_selection.items', 'products.detail_media.quality.load_test.image', 'products.detail_media.delivery.protected_packaging', 'quality_evidence'],
    'solutions' => ['solutions.hero.title', 'solutions.cta.title', 'case_studies.hero.title'],
    'capabilities' => ['capabilities.hero.title', 'capabilities.intro.title', 'capabilities.items', 'capabilities.project_brief.title', 'capabilities.project_brief.action_label', 'manufacturing_process', 'capabilities.verification.title', 'capabilities.verification.image'],
    'videos' => ['manufacturing_videos.title', 'manufacturing_videos.featured.title', 'manufacturing_videos.categories'],
    'about' => ['about.hero.title', 'about.company_video.title', 'about.brand_window.image', 'about.why_choose.title', 'about.team.eyebrow', 'about.global_support.title', 'about.official_channels.title'],
    'company' => ['company.profile.title', 'company.facts', 'company.timeline_header.title', 'company.timeline', 'company.quality.title'],
    'news' => ['news.hero.title', 'news.follow.title'],
    'sustainability' => ['sustainability.hero.title', 'sustainability.lifecycle.title', 'sustainability.lifecycle.items', 'sustainability.management.title', 'sustainability.safety.title', 'sustainability.progress.title'],
    'contact' => ['contact.inquiry_types', 'contact.form.title', 'contact_network.title', 'contact_network.regions'],
    'resources' => ['resources.hero.title', 'resources.library.title', 'resources.downloads', 'resources.industry.title', 'resources.items'],
];

$screens = springapex_admin_screens();
$errors = [];
$checked = 0;

$submission_value = static function (array $field, mixed $current) use (&$submission_value): mixed {
    $type = (string) ($field['type'] ?? 'text');
    if ($type === 'lines') {
        return is_array($current) ? implode("\n", array_map('strval', $current)) : (string) $current;
    }
    if ($type !== 'repeater') {
        return $current;
    }

    $rows = [];
    foreach (array_values(is_array($current) ? $current : []) as $index => $row) {
        $row = is_array($row) ? $row : [];
        $submitted = [SPRINGAPEX_ADMIN_ROW_ORIGIN => (string) $index];
        foreach ((array) ($field['fields'] ?? []) as $subfield) {
            $key = (string) ($subfield['path'] ?? '');
            if ($key !== '' && array_key_exists($key, $row)) {
                $submitted[$key] = $submission_value($subfield, $row[$key]);
            }
        }
        $rows[] = $submitted;
    }
    return $rows;
};

foreach ($required as $screen_key => $paths) {
    $screen = $screens[$screen_key] ?? null;
    if (!is_array($screen)) {
        $errors[] = "后台页面不存在：{$screen_key}";
        continue;
    }

    $declared = [];
    foreach ((array) ($screen['sections'] ?? []) as $section) {
        foreach ((array) ($section['fields'] ?? []) as $field) {
            $path = (string) ($field['path'] ?? '');
            if ($path !== '') {
                if (isset($declared[$path])) {
                    $errors[] = "{$screen_key} 重复声明后台字段：{$path}";
                }
                $declared[$path] = true;
            }
        }
    }

    foreach ($paths as $path) {
        $checked++;
        if (!isset($declared[$path])) {
            $errors[] = "{$screen_key} 未声明后台字段：{$path}";
            continue;
        }
        $sentinel = new stdClass();
        if (springapex_get($path, $sentinel) === $sentinel) {
            $errors[] = "内容默认值不存在：{$path}";
        }
    }

    foreach ((array) ($screen['sections'] ?? []) as $section) {
        foreach ((array) ($section['fields'] ?? []) as $field) {
            $path = (string) ($field['path'] ?? '');
            if ($path === '') {
                continue;
            }
            $current = springapex_get($path);
            $submitted = $submission_value($field, $current);
            $warnings = [];
            $result = springapex_admin_sanitize_field(
                $field,
                $submitted,
                $current,
                (string) ($field['label'] ?? $path),
                $warnings
            );
            if (!$result['accepted'] || $warnings !== []) {
                $errors[] = "默认值无法无损保存：{$screen_key} → {$path}";
            }
        }
    }
}

$template_checks = [
    'templates/home.php' => ['home_sections'],
    'templates/products.php' => ['products_data[\'entry\']', 'products_data[\'range\']'],
    'templates/solutions.php' => ['solutions.cta'],
    'templates/capabilities.php' => ['project_brief[\'action_label\']'],
    'templates/about-story.php' => ['timeline_header', 'global_support', 'official_channels'],
    'templates/news.php' => ['news.follow'],
    'templates/sustainability.php' => ['sustainability[\'lifecycle\']', 'sustainability[\'safety\']', 'sustainability[\'progress\']'],
    'templates/contact.php' => ['contact.form'],
    'templates/resources.php' => ['resources[\'downloads\']', 'resources[\'industry\']'],
];

foreach ($template_checks as $relative => $needles) {
    $source = file_get_contents(dirname(__DIR__) . '/' . $relative);
    if (!is_string($source)) {
        $errors[] = "无法读取模板：{$relative}";
        continue;
    }
    foreach ($needles as $needle) {
        if (!str_contains($source, $needle)) {
            $errors[] = "模板未读取受管内容：{$relative} → {$needle}";
        }
    }
}

$required_routes = [
    '/contact/?intent=solution',
    '/contact/?intent=engineer',
    '/contact/?intent=catalog',
    '/contact/?intent=sustainability',
    '/about/#official-channels',
    '/about/#quality-certificates',
    '/contact/#contact-network',
];
$allowed_routes = springapex_admin_route_values();
foreach ($required_routes as $route) {
    if (!in_array($route, $allowed_routes, true)) {
        $errors[] = "后台路由白名单缺少：{$route}";
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, $errors) . PHP_EOL);
    exit(1);
}

echo "admin-content-coverage: {$checked} paths ok" . PHP_EOL;
