<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

// content.php declares the production fallback only when this helper is absent.
if (!function_exists('springapex_url')) {
    function springapex_url(string $path = '/'): string
    {
        return springapex_preview_url($path);
    }
}

require SPRINGAPEX_DIR . '/inc/content.php';
require SPRINGAPEX_DIR . '/inc/helpers.php';
// 表单 schema：contact / product / 快速询盘窗按同一份 schema 动态渲染字段，
// 预览用默认 schema（get_option 桩回退），呈现与线上一致的默认字段。
require SPRINGAPEX_DIR . '/inc/form-schema.php';

$route = springapex_preview_route();
$_GET['sa_page'] = $route === 'home' ? '' : $route;
if ($route === 'product') {
    $_GET['product'] = springapex_preview_product_slug();
}
if ($route === 'solution') {
    $_GET['solution'] = springapex_preview_solution_slug();
}
if ($route === 'case-study') {
    $_GET['case'] = springapex_preview_case_slug();
}
if ($route === 'news-single') {
    $_GET['news'] = springapex_preview_news_slug();
}

$templates = [
    'home' => 'templates/home',
    'products' => 'templates/products',
    'product' => 'templates/product',
    'solutions' => 'templates/solutions',
    'solution' => 'templates/solution',
    'case-studies' => 'templates/case-studies',
    'case-study' => 'templates/case-study',
    'news' => 'templates/news',
    'news-single' => 'templates/news-single',
    'capabilities' => 'templates/capabilities',
    'manufacturing-videos' => 'templates/manufacturing-videos',
    'about' => 'templates/about',
    'sustainability' => 'templates/sustainability',
    'contact' => 'templates/contact',
    'resources' => 'templates/resources',
    'search' => 'templates/search',
    'privacy' => 'templates/privacy',
    'terms' => 'templates/terms',
    'sitemap' => 'templates/sitemap',
];

status_header(200);
get_header();
get_template_part($templates[$route]);

get_footer();
