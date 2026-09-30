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

    // 先记下正文里已经占用的 id，新补的避开它们。
    $used = [];
    if (preg_match_all('/<[a-z][^>]*>/i', $html, $tags)) {
        foreach ($tags[0] as $tag) {
            $existing = springapex_news_toc_attr_id($tag);
            if ($existing !== null) {
                $used[strtolower($existing)] = true;
            }
        }
    }

    $items = [];
    $html = (string) preg_replace_callback(
        '/<h2(\s[^>]*)?>(.*?)<\/h2>/is',
        static function (array $match) use (&$used, &$items): string {
            $attrs = (string) ($match[1] ?? '');
            $inner = (string) $match[2];
            $text = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($inner), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
            if ($text === '') {
                return $match[0];
            }

            $existing = springapex_news_toc_attr_id($attrs);
            if ($existing !== null) {
                $items[] = ['id' => $existing, 'text' => $text];
                return $match[0];
            }

            $base = springapex_news_toc_slug($text);
            $id = $base;
            for ($n = 2; isset($used[$id]); $n++) {
                $id = $base . '-' . $n;
            }
            $used[$id] = true;
            $items[] = ['id' => $id, 'text' => $text];

            return '<h2 id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"' . $attrs . '>' . $inner . '</h2>';
        },
        $html
    );

    if (count($items) < SPRINGAPEX_NEWS_TOC_MIN_HEADINGS) {
        $items = [];
    }

    return ['html' => $html, 'items' => $items];
}

/**
 * 取标签属性里的 id（双引号、单引号、不带引号三种写法都认），返回解码后的值，
 * 也就是浏览器里真正的 id；没有或为空时返回 null。
 */
function springapex_news_toc_attr_id(string $attrs): ?string
{
    if (!preg_match('/\sid\s*=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'=<>`]+))/i', ' ' . $attrs, $m)) {
        return null;
    }
    $raw = ($m[1] ?? '') !== '' ? $m[1] : ((($m[2] ?? '') !== '') ? $m[2] : ($m[3] ?? ''));
    $id = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    return $id !== '' ? $id : null;
}

/** 标题文字转成锚点：小写英文、数字和连字符。 */
function springapex_news_toc_slug(string $text): string
{
    $slug = strtolower($text);
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
