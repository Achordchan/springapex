<?php
/**
 * 结构化数据和导航链接里不依赖 WordPress 的纯函数：面包屑、Organization、
 * 分享配图格式、菜单链接补斜杠、行业页 H1 兜底。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

function home_url(string $path = '/'): string
{
    return 'https://www.norenspring.com' . $path;
}

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

require __DIR__ . '/../inc/helpers.php';
require __DIR__ . '/../inc/schema.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// 菜单链接：站内路径补斜杠，文件、外站、锚点、已有斜杠的不动。
$cases = [
    '/products' => 'https://www.norenspring.com/products/',
    '/solutions/' => 'https://www.norenspring.com/solutions/',
    'https://www.norenspring.com/products' => 'https://www.norenspring.com/products/',
    'https://www.norenspring.com/news?news_type=exhibitions' => 'https://www.norenspring.com/news/?news_type=exhibitions',
    '/products#families' => 'https://www.norenspring.com/products/#families',
    'https://www.norenspring.com' => 'https://www.norenspring.com',
    '/files/catalog.pdf' => 'https://www.norenspring.com/files/catalog.pdf',
    'https://example.com/products' => 'https://example.com/products',
    '//example.com/products' => '//example.com/products',
    'mailto:info@norenspring.com' => 'mailto:info@norenspring.com',
    '#contact' => '#contact',
    '' => 'https://www.norenspring.com/',
];
foreach ($cases as $input => $expected) {
    $actual = springapex_navigation_href((string) $input);
    springapex_test_assert($actual === $expected, "navigation href {$input}: expected {$expected}, got {$actual}");
}

// 行业页 H1：名字里已有 Spring 不再拼口号，没有才补，漏掉的空格补上。
$headings = [
    'Agricultural Machinery Spring Solutions' => 'Agricultural Machinery Spring Solutions',
    'R&D Prototyping & Design EngineeringSpring Solutions' => 'R&D Prototyping & Design Engineering Spring Solutions',
    'Packaging & Material Handling SpringSolutions' => 'Packaging & Material Handling Spring Solutions',
    'Energy' => 'Energy Spring Solutions',
    '' => 'Industry Spring Solutions',
];
foreach ($headings as $input => $expected) {
    $actual = springapex_solution_heading((string) $input);
    springapex_test_assert($actual === $expected, "solution heading {$input}: expected {$expected}, got {$actual}");
}

// 面包屑：跳过缺名字或网址的项，位置连续；不足两级不输出。
$breadcrumb = springapex_schema_breadcrumb('https://www.norenspring.com/products/x/#breadcrumb', [
    ['name' => 'Home', 'url' => 'https://www.norenspring.com/'],
    ['name' => '', 'url' => 'https://www.norenspring.com/ignored/'],
    ['name' => 'Products', 'url' => 'https://www.norenspring.com/products/'],
    ['name' => 'X', 'url' => 'https://www.norenspring.com/products/x/'],
]);
springapex_test_assert($breadcrumb !== null && count($breadcrumb['itemListElement']) === 3, 'breadcrumb keeps three valid items');
springapex_test_assert(array_column($breadcrumb['itemListElement'], 'position') === [1, 2, 3], 'breadcrumb positions are consecutive');
springapex_test_assert(
    springapex_schema_breadcrumb('#b', [['name' => 'Home', 'url' => 'https://www.norenspring.com/']]) === null,
    'single-item breadcrumb is omitted'
);

// Organization：只收 http(s) 社交链接，空字段不输出。
$organization = springapex_schema_organization([
    'name' => 'NorenSpring',
    'company' => 'Xuzhou APEX Spring Manufacturing Co., Ltd.',
    'email' => 'info@norenspring.com',
    'phone' => '',
    'address' => '',
    'facebook' => 'https://www.facebook.com/NorenSpring/',
    'linkedin' => 'not a url',
    'instagram' => '',
], 'https://www.norenspring.com/', ['url' => 'https://www.norenspring.com/logo.png', 'width' => 2560, 'height' => 1340], 'https://www.norenspring.com/contact/');
springapex_test_assert($organization['@id'] === 'https://www.norenspring.com/#organization', 'organization id');
springapex_test_assert($organization['sameAs'] === ['https://www.facebook.com/NorenSpring/'], 'only valid social profiles');
springapex_test_assert(!isset($organization['telephone']) && !isset($organization['address']), 'empty fields are omitted');
springapex_test_assert(!isset($organization['contactPoint']['telephone']), 'empty contact phone is omitted');
springapex_test_assert($organization['logo']['width'] === 2560, 'logo keeps its size');

// 分享配图：WebP 不收（LinkedIn 不认），查询串不影响判断。
springapex_test_assert(springapex_schema_is_shareable_image('https://x.test/a.JPG?ver=2'), 'jpg is shareable');
springapex_test_assert(!springapex_schema_is_shareable_image('https://x.test/a.webp'), 'webp is not shareable');

// 标题里的实体还原成纯文本。
springapex_test_assert(springapex_schema_text(" R&#038;D <b>Springs</b>\n") === 'R&D Springs', 'entities and tags are cleaned');

echo "seo-schema: ok\n";
