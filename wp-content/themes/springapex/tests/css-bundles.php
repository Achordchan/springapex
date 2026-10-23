<?php
/**
 * 合并样式必须与源 CSS 完全一致：每个路由都有合并文件，内容等于按清单顺序拼接的结果，
 * 也没有残留的旧合并文件。改了 CSS 忘记跑 tools/build-css-bundles.php 时这里会失败。
 */

declare(strict_types=1);

require dirname(__DIR__) . '/inc/style-manifest.php';

$css_dir = dirname(__DIR__) . '/assets/css';
$bundle_dir = $css_dir . '/bundles';
$failures = [];
$expected = [];

foreach (springapex_style_manifest_routes() as $route) {
    $files = springapex_route_style_files($route);
    $name = springapex_style_bundle_name($files);
    $expected[$name] = true;
    $path = $bundle_dir . '/' . $name;
    if (!is_file($path)) {
        $failures[] = "route {$route}: missing {$name}";
        continue;
    }
    if (file_get_contents($path) !== springapex_style_bundle_contents($css_dir, $files)) {
        $failures[] = "route {$route}: {$name} is stale";
    }
}

foreach (glob($bundle_dir . '/bundle-*.css') ?: [] as $path) {
    if (!isset($expected[basename($path)])) {
        $failures[] = 'unused bundle ' . basename($path);
    }
}

// 级联顺序的几个硬约束：基础在前，移动端覆盖晚于页面样式。
$products = springapex_route_style_files('products');
if ($products[0] !== 'foundation.css' || array_search('mobile-polish.css', $products, true) < array_search('products-page.css', $products, true)) {
    $failures[] = 'products route order changed';
}
if (springapex_route_style_files('__default') !== springapex_route_style_files('some-unknown-route')) {
    $failures[] = 'unknown routes must share the default bundle';
}

if ($failures !== []) {
    fwrite(STDERR, implode("\n", $failures) . "\nRun: php wp-content/themes/springapex/tools/build-css-bundles.php\n");
    exit(1);
}

echo 'css-bundles: ' . count($expected) . " bundles up to date\n";
