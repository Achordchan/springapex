<?php
/**
 * 前台样式清单：每个路由加载哪些 CSS、按什么顺序。
 *
 * 这份清单同时供两处使用：inc/setup.php 在前台按它入队，tools/build-css-bundles.php
 * 按它把同一路由的文件预先拼成一个合并文件。两边读同一份数据，合并文件里的级联
 * 顺序就和逐个加载时完全一致。
 *
 * 为什么要合并：每页原来有 8–10 个阻塞渲染的 CSS，手机慢网下每个都要排队，
 * 2026-10-23 本地 Lighthouse 对比，合成一个后产品列表 FCP 约 2.0 s → 1.5 s。
 *
 * 本文件不依赖 WordPress 函数，命令行构建脚本可以直接 require。
 */

declare(strict_types=1);

if (!defined('ABSPATH') && PHP_SAPI !== 'cli') {
    exit;
}

/**
 * 按级联顺序排列的样式条目：[文件名, 适用路由]；路由为 null 表示所有路由都加载。
 *
 * 顺序就是覆盖顺序，加新文件时插在正确位置，不要随手追加到末尾。
 *
 * @return list<array{0:string,1:list<string>|null}>
 */
function springapex_style_manifest(): array
{
    return [
        ['foundation.css', null],
        ['components.css', null],
        ['product-mega-menu.css', null],
        ['pages.css', null],
        ['company-introduction.css', ['home', 'about', 'about-story']],
        ['responsive.css', null],
        ['enhancements.css', null],
        ['audit-fixes.css', null],
        ['content-dedup.css', ['home', 'products', 'product', 'capabilities', 'about', 'about-story']],
        ['news.css', ['news', 'news-single']],
        ['capabilities-page.css', ['capabilities']],
        ['about-page.css', ['about', 'about-story']],
        ['about-team.css', ['about', 'about-story']],
        ['about-sections.css', ['about', 'about-story', 'sustainability', 'resources']],
        ['search-page.css', ['search']],
        ['products-page.css', ['products']],
        // 跨页面移动端覆盖必须晚于上面的基础和第一组页面样式。
        ['mobile-polish.css', null],
        ['contact-network.css', ['contact']],
        ['case-studies.css', ['solutions', 'case-studies', 'case-study']],
        ['manufacturing-videos.css', ['capabilities', 'manufacturing-videos']],
        ['product-details.css', ['product']],
        ['product-compression.css', ['product', 'capabilities']],
        ['solution-detail.css', ['solution']],
        ['solution-detail-responsive.css', ['solution']],
        ['rich-content.css', ['case-study', 'solution']],
    ];
}

/**
 * 某个路由要加载的 CSS 文件名，按级联顺序。
 *
 * @return list<string>
 */
function springapex_route_style_files(string $route): array
{
    $files = [];
    foreach (springapex_style_manifest() as [$file, $routes]) {
        if ($routes === null || in_array($route, $routes, true)) {
            $files[] = $file;
        }
    }

    return $files;
}

/**
 * 清单里出现过的全部路由，外加一个不在任何列表里的占位路由（代表其余所有页面）。
 *
 * @return list<string>
 */
function springapex_style_manifest_routes(): array
{
    $routes = ['__default'];
    foreach (springapex_style_manifest() as [, $file_routes]) {
        foreach ($file_routes ?? [] as $route) {
            $routes[] = $route;
        }
    }

    return array_values(array_unique($routes));
}

/** 一组文件对应的合并文件名；文件组合相同的路由共用同一个合并文件。 */
function springapex_style_bundle_name(array $files): string
{
    return 'bundle-' . substr(md5(implode('|', $files)), 0, 10) . '.css';
}

/** 合并文件内容：按顺序原样拼接，每段前标注来源文件，方便在浏览器里排查。 */
function springapex_style_bundle_contents(string $css_dir, array $files): string
{
    $parts = [];
    foreach ($files as $file) {
        $css = file_get_contents($css_dir . '/' . $file);
        if ($css === false) {
            throw new RuntimeException('Missing stylesheet: ' . $file);
        }
        $parts[] = '/* ' . $file . " */\n" . rtrim($css) . "\n";
    }

    return implode("\n", $parts);
}
