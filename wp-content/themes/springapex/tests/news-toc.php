<?php
/**
 * 新闻详情页文章目录：H2 补 id、保留已有 id、去重、短文不出目录。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

require __DIR__ . '/../inc/news-toc.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

// 补 id；已有 id 保留；同名标题和正文里已占用的 id 都避开。
$html = '<p id="faq">Intro</p>'
    . '<h2>Key takeaways</h2>'
    . '<h2 id="send-your-drawing" class="x">Have a drawing?</h2>'
    . '<h2>FAQ</h2>'
    . '<h3>Is 50CrVA the same as SAE 6150?</h3>'
    . '<h2>Key takeaways</h2>'
    . '<h2>Stainless 304 &amp; <em>zinc-plated</em> steel</h2>';
$toc = springapex_news_toc_prepare($html);
$ids = array_column($toc['items'], 'id');
springapex_test_assert($ids === ['key-takeaways', 'send-your-drawing', 'faq-2', 'key-takeaways-2', 'stainless-304-zinc-plated-steel'], 'ids: ' . implode(',', $ids));
springapex_test_assert(str_contains($toc['html'], '<h2 id="send-your-drawing" class="x">'), 'existing id kept');
springapex_test_assert(str_contains($toc['html'], '<h2 id="faq-2">FAQ</h2>'), 'id added to plain h2');
springapex_test_assert(str_contains($toc['html'], '<h3>Is 50CrVA'), 'h3 untouched');
springapex_test_assert($toc['items'][4]['text'] === 'Stainless 304 & zinc-plated steel', 'text decoded and stripped');

$list = springapex_news_toc_list_html($toc['items']);
springapex_test_assert(str_contains($list, '<a href="#stainless-304-zinc-plated-steel">Stainless 304 &amp; zinc-plated steel</a>'), 'list escapes text');

// 少于 3 个 H2 不出目录，但 id 照补，锚点链接仍可用。
$short = springapex_news_toc_prepare('<h2>One</h2><p>x</p><h2>Two</h2>');
springapex_test_assert($short['items'] === [], 'short article has no toc');
springapex_test_assert(str_contains($short['html'], '<h2 id="one">One</h2>'), 'short article still gets ids');

springapex_test_assert(springapex_news_toc_prepare('<p>No headings</p>')['html'] === '<p>No headings</p>', 'no headings unchanged');
springapex_test_assert(springapex_news_toc_slug('常见问题') === 'section', 'non-latin fallback');

// 不带引号的 id 原样保留，不再补第二个 id；也算作已占用。
$unquoted = springapex_news_toc_prepare('<h2 id=existing-anchor>Overview</h2><h2>Existing anchor</h2><h2>C</h2>');
springapex_test_assert(str_contains($unquoted['html'], '<h2 id=existing-anchor>Overview</h2>'), 'unquoted id kept as is');
springapex_test_assert(array_column($unquoted['items'], 'id') === ['existing-anchor', 'existing-anchor-2', 'c'], 'unquoted id: ' . implode(',', array_column($unquoted['items'], 'id')));

// 已有 id 里的字符引用按浏览器的真实 id 解码；链接做 URL 编码。
$encoded = springapex_news_toc_prepare('<h2 id="r&amp;d">Research</h2><h2 id=\'strain-0.2%\'>Strain</h2><h2>Z</h2>');
springapex_test_assert($encoded['items'][0]['id'] === 'r&d', 'entity id decoded');
$encoded_list = springapex_news_toc_list_html($encoded['items']);
springapex_test_assert(str_contains($encoded_list, 'href="#r%26d"'), 'ampersand id url-encoded');
springapex_test_assert(str_contains($encoded_list, 'href="#strain-0.2%25"'), 'percent id url-encoded');

echo "news-toc: ok\n";
