<?php
/**
 * 新闻详情页文章目录：从正文的 H2 生成，现有和以后的文章都自动有，不用逐篇改。
 *
 * - 没有 id 的 H2 按标题文字补一个（和已有 id、彼此都不重复），目录和 Google
 *   的「跳转到」链接都靠它；已写好 id 的标题保持原样，旧的锚点链接不失效。
 * - 少于 3 个 H2 的短文不出目录。
 * - 只收 H2：长指南有十几个 H2，再加 H3 侧栏放不下。
 */

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_NEWS_TOC_MIN_HEADINGS = 3;

/** 内容按原样文本处理、里面的 <h2> 不是标题的元素。 */
const SPRINGAPEX_NEWS_TOC_RAW = 'script|style|textarea|template|title|noscript|iframe|xmp|noembed|noframes';

/** 一串标签属性：引号里的 > 和空格不会把标签截断。 */
const SPRINGAPEX_NEWS_TOC_ATTRS = '(?:\\s+[^\\s"\'>\\/=]+(?:\\s*=\\s*(?:"[^"]*"|\'[^\']*\'|[^\\s"\'=<>`]+))?)*';

/**
 * 给正文 H2 补 id 并收集目录项。
 *
 * @return array{html: string, items: list<array{id: string, text: string}>}
 */
function springapex_news_toc_prepare(string $html): array
{
    if (stripos($html, '<h2') === false) {
        return ['html' => $html, 'items' => []];
    }

    $tokens = springapex_news_toc_tokens($html);
    // 切分出错（例如超出回溯上限）或拼不回原文时原样照出、不出目录，绝不能把正文弄丢。
    if ($tokens === null || implode('', $tokens) !== $html) {
        return ['html' => $html, 'items' => []];
    }

    // 先记下所有开始标签（含 iframe、script 等整段跳过的元素）已占用的 id，新补的避开它们。
    $used = [];
    foreach ($tokens as $token) {
        $existing = springapex_news_toc_attr_id(springapex_news_toc_tag_attrs($token) ?? '');
        if ($existing !== null) {
            $used[strtolower($existing)] = true;
        }
    }

    $items = [];
    $out = '';
    $count = count($tokens);
    for ($i = 0; $i < $count; $i++) {
        $token = $tokens[$i];
        if (!preg_match('/^<h2(' . SPRINGAPEX_NEWS_TOC_ATTRS . ')\s*>$/i', $token, $open)) {
            $out .= $token;
            continue;
        }

        $close = null;
        for ($j = $i + 1; $j < $count; $j++) {
            if (preg_match('/^<\/h2\s*>$/i', $tokens[$j])) {
                $close = $j;
                break;
            }
        }
        if ($close === null) {
            $out .= $token;
            continue;
        }

        $inner_tokens = array_slice($tokens, $i + 1, $close - $i - 1);
        $inner = implode('', $inner_tokens);
        // 目录文字只取看得见的部分：注释和原样文本元素不算。
        $visible = implode('', array_filter($inner_tokens, static fn (string $t): bool => !springapex_news_toc_is_raw($t)));
        $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($visible), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        $attrs = (string) $open[1];
        $i = $close;

        $existing = springapex_news_toc_attr_id($attrs);
        if ($text === '' || $existing !== null) {
            if ($text !== '' && $existing !== null) {
                $items[] = ['id' => $existing, 'text' => $text];
            }
            $out .= $token . $inner . $tokens[$close];
            continue;
        }

        $base = springapex_news_toc_slug($text);
        $id = $base;
        for ($n = 2; isset($used[$id]); $n++) {
            $id = $base . '-' . $n;
        }
        $used[$id] = true;
        $items[] = ['id' => $id, 'text' => $text];
        $out .= '<h2 id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '>' . $inner . $tokens[$close];
    }

    if (count($items) < SPRINGAPEX_NEWS_TOC_MIN_HEADINGS) {
        $items = [];
    }

    return ['html' => $out, 'items' => $items];
}

/**
 * 把 HTML 按顺序切成记号：注释、整段原样文本元素（script、style、iframe 等，连同
 * 开始标签）、完整标签（属性值里的 < > 都在标签内部）、文本。拼起来等于原文。
 *
 * @return list<string>|null
 */
function springapex_news_toc_tokens(string $html): ?array
{
    $pattern = '/<!--.*?(?:-->|$)'
        . '|<(' . SPRINGAPEX_NEWS_TOC_RAW . ')\b' . SPRINGAPEX_NEWS_TOC_ATTRS . '\s*>.*?(?:<\/\1\s*>|$)'
        . '|<\/?[a-z][^\s\/>]*' . SPRINGAPEX_NEWS_TOC_ATTRS . '\s*\/?>'
        . '|[^<]+|</is';
    if (preg_match_all($pattern, $html, $matches) === false) {
        return null;
    }

    return $matches[0];
}

/** 注释或整段原样文本元素。 */
function springapex_news_toc_is_raw(string $token): bool
{
    return str_starts_with($token, '<!--')
        || (bool) preg_match('/^<(' . SPRINGAPEX_NEWS_TOC_RAW . ')\b/i', $token);
}

/** 开始标签（包括原样文本元素的开始标签）的属性串；不是开始标签时返回 null。 */
function springapex_news_toc_tag_attrs(string $token): ?string
{
    if (!preg_match('/^<[a-z][^\s\/>]*(' . SPRINGAPEX_NEWS_TOC_ATTRS . ')/i', $token, $m)) {
        return null;
    }

    return $m[1];
}

/**
 * 取标签属性里的 id（双引号、单引号、不带引号三种写法都认），返回解码后的值，
 * 也就是浏览器里真正的 id；没有或为空时返回 null。
 */
function springapex_news_toc_attr_id(string $attrs): ?string
{
    // 逐个属性解析，引号里的 "id=" 不会被误认；同名属性浏览器取第一个。
    preg_match_all('/([^\\s"\'>\\/=]+)(?:\\s*=\\s*(?:"([^"]*)"|\'([^\']*)\'|([^\\s"\'=<>`]+)))?/', $attrs, $found, PREG_SET_ORDER);
    foreach ($found as $attr) {
        if (strtolower($attr[1]) !== 'id') {
            continue;
        }
        $raw = ($attr[2] ?? '') . ($attr[3] ?? '') . ($attr[4] ?? '');
        $id = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return $id !== '' ? $id : null;
    }

    return null;
}

/** 标题文字转成锚点：小写英文、数字和连字符。 */
function springapex_news_toc_slug(string $text): string
{
    // WordPress 的 remove_accents 把 ü 这类字母转成 u，锚点更好读。
    $slug = strtolower(function_exists('remove_accents') ? remove_accents($text) : $text);
    $slug = (string) preg_replace('/[^a-z0-9]+/', '-', $slug);
    $slug = trim($slug, '-');
    if (strlen($slug) > 60) {
        $slug = rtrim(substr($slug, 0, 60), '-');
    }

    return $slug !== '' ? $slug : 'section';
}

/**
 * 目录列表。侧栏和窄屏折叠框共用。锚点先做 URL 编码，news-toc.js 用
 * decodeURIComponent 还原后再找标题，id 里有 % 之类字符也不会出错。
 *
 * @param list<array{id: string, text: string}> $items
 */
function springapex_news_toc_list_html(array $items): string
{
    $html = '<ol class="sa-news-toc__list">';
    foreach ($items as $item) {
        $html .= '<li><a href="#' . htmlspecialchars(rawurlencode($item['id']), ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($item['text'], ENT_QUOTES, 'UTF-8') . '</a></li>';
    }

    return $html . '</ol>';
}
