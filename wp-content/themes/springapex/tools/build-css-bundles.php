<?php
/**
 * 重新生成 assets/css/bundles/ 下的前台合并样式。
 *
 * 改了 inc/style-manifest.php 里列出的任何 CSS，或改了清单本身，都要在仓库根目录跑：
 *
 *     php wp-content/themes/springapex/tools/build-css-bundles.php
 *
 * 然后把 bundles/ 的变化一起提交（并照常升 SPRINGAPEX_VERSION）。CI 里的
 * tests/css-bundles.php 会检查合并文件是否与源文件一致，忘了跑会直接失败。
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit;
}

require dirname(__DIR__) . '/inc/style-manifest.php';

$css_dir = dirname(__DIR__) . '/assets/css';
$bundle_dir = $css_dir . '/bundles';
if (!is_dir($bundle_dir) && !mkdir($bundle_dir, 0755, true)) {
    fwrite(STDERR, "Cannot create {$bundle_dir}\n");
    exit(1);
}

$expected = [];
foreach (springapex_style_manifest_routes() as $route) {
    $files = springapex_route_style_files($route);
    $expected[springapex_style_bundle_name($files)] = $files;
}

foreach ($expected as $name => $files) {
    file_put_contents($bundle_dir . '/' . $name, springapex_style_bundle_contents($css_dir, $files));
    echo $name . ' <- ' . implode(', ', $files) . "\n";
}

// 清掉不再被任何路由用到的旧合并文件。
foreach (glob($bundle_dir . '/bundle-*.css') ?: [] as $path) {
    if (!isset($expected[basename($path)])) {
        unlink($path);
        echo 'removed ' . basename($path) . "\n";
    }
}
