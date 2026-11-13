<?php
/**
 * 新闻列表分页里不依赖 WordPress 的部分：页码条、翻页网址、第 2 页起的标题和描述。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

function add_action(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function add_filter(string $hook, callable $callback, int $priority = 10, int $args = 1): bool
{
    return true;
}

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function springapex_schema_paged_url(string $url, int $paged): string
{
    return $paged <= 1 ? $url : rtrim($url, '/') . '/page/' . $paged . '/';
}

require __DIR__ . '/../inc/news-archive.php';
require __DIR__ . '/../inc/seo.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

// 页码条：首末页、当前页前后各一页，只隔一页时直接补上，否则用省略号（null）。
$cases = [
    [1, 1, []],
    [1, 2, [1, 2]],
    [1, 6, [1, 2, null, 6]],
    [3, 6, [1, 2, 3, 4, 5, 6]],
    [4, 6, [1, 2, 3, 4, 5, 6]],
    [6, 6, [1, null, 5, 6]],
    [5, 9, [1, null, 4, 5, 6, null, 9]],
    [9, 3, [1, 2, 3]],
    [0, 3, [1, 2, 3]],
];
foreach ($cases as [$current, $total, $expected]) {
    $actual = springapex_news_page_numbers($current, $total);
    springapex_test_assert(
        $actual === $expected,
        "page numbers {$current}/{$total}: expected " . json_encode($expected) . ', got ' . json_encode($actual)
    );
}

// 翻页网址：第 1 页就是列表本身；分类筛选作为查询参数保留。
$archive = 'https://www.norenspring.com/news/';
$urls = [
    [1, 'all', 'https://www.norenspring.com/news/'],
    [2, 'all', 'https://www.norenspring.com/news/page/2/'],
    [1, 'exhibitions', 'https://www.norenspring.com/news/?news_type=exhibitions'],
    [3, 'industry-news', 'https://www.norenspring.com/news/page/3/?news_type=industry-news'],
];
foreach ($urls as [$page, $type, $expected]) {
    $actual = springapex_news_page_url($archive, $page, $type);
    springapex_test_assert($actual === $expected, "page url {$page} {$type}: expected {$expected}, got {$actual}");
}

// 第 2 页起标题、描述带页码，标题里的页码放在品牌名前。
$values = [
    'title' => 'Spring Manufacturing News & Updates | NorenSpring',
    'description' => 'Read NorenSpring company news.',
    'keywords' => '',
];
springapex_test_assert(springapex_seo_paged_values($values, 0) === $values, 'page 1 values unchanged');
springapex_test_assert(springapex_seo_paged_values($values, 1) === $values, 'explicit page 1 values unchanged');
$paged = springapex_seo_paged_values($values, 2);
springapex_test_assert(
    $paged['title'] === 'Spring Manufacturing News & Updates - Page 2 | NorenSpring',
    'page 2 title: ' . $paged['title']
);
springapex_test_assert($paged['description'] === 'Read NorenSpring company news. Page 2.', 'page 2 description: ' . $paged['description']);
$bare = springapex_seo_paged_values(['title' => 'News', 'description' => '', 'keywords' => ''], 3);
springapex_test_assert($bare['title'] === 'News - Page 3' && $bare['description'] === '', 'title without brand separator');

echo "news-archive: all checks passed\n";
