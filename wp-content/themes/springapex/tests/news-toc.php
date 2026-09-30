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

// 引号里的 > 不截断标签；title 里的 "id=" 不算 id。
$quoted = springapex_news_toc_prepare('<h2 title="Stress > strain" id="saved-anchor">Overview</h2><h2 title="see id=fake">Two</h2><h2>Three</h2>');
springapex_test_assert(array_column($quoted['items'], 'id') === ['saved-anchor', 'two', 'three'], 'quoted >: ' . implode(',', array_column($quoted['items'], 'id')));
springapex_test_assert($quoted['items'][0]['text'] === 'Overview', 'quoted > text');
springapex_test_assert(str_contains($quoted['html'], '<h2 title="Stress > strain" id="saved-anchor">Overview</h2>'), 'quoted > heading untouched');
springapex_test_assert(str_contains($quoted['html'], '<h2 id="two" title="see id=fake">Two</h2>'), 'id inside value ignored');

// 注释、script 里的 <h2> 不是标题：不补 id、不进目录、原样保留。
$raw = '<script>const t = "<h2>Overview</h2>";</script><!-- <h2>Hidden</h2> -->'
    . '<style>h2::after{content:"<h2>x</h2>"}</style>'
    . '<h2>A<!-- note --></h2><h2>B</h2><h2>C</h2><!-- unclosed <h2>D</h2>';
$raw_toc = springapex_news_toc_prepare($raw);
springapex_test_assert(array_column($raw_toc['items'], 'text') === ['A', 'B', 'C'], 'raw: ' . implode(',', array_column($raw_toc['items'], 'text')));
springapex_test_assert(str_contains($raw_toc['html'], '<script>const t = "<h2>Overview</h2>";</script><!-- <h2>Hidden</h2> -->'), 'script and comment untouched');
springapex_test_assert(str_contains($raw_toc['html'], '<h2 id="a">A<!-- note --></h2>'), 'comment inside heading restored');
springapex_test_assert(str_ends_with($raw_toc['html'], '<!-- unclosed <h2>D</h2>'), 'unclosed comment untouched');

// 属性值里的 <h2> 不是标题；iframe 等整段跳过的元素上的 id 也算已占用。
$attr = '<div data-content="<h2>Overview</h2>"></div><iframe id="specifications" src="x"></iframe>'
    . '<h2>Specifications</h2><h2>B</h2><h2>C</h2>';
$attr_toc = springapex_news_toc_prepare($attr);
springapex_test_assert(array_column($attr_toc['items'], 'id') === ['specifications-2', 'b', 'c'], 'attr: ' . implode(',', array_column($attr_toc['items'], 'id')));
springapex_test_assert(str_starts_with($attr_toc['html'], '<div data-content="<h2>Overview</h2>"></div>'), 'attribute value untouched');

// 没有闭合的 h2、零散的 < 都原样保留。
$broken = '<p>a < b</p><h2>One</h2><h2>Two</h2><h2>Three';
springapex_test_assert(str_ends_with(springapex_news_toc_prepare($broken)['html'], '<p>a < b</p><h2 id="one">One</h2><h2 id="two">Two</h2><h2>Three'), 'broken html kept');

echo "news-toc: ok\n";
