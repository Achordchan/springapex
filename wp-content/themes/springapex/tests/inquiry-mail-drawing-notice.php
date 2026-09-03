<?php
/**
 * 停发附件之后，带图纸的询盘邮件必须在 HTML 正文里留下图纸清单和取件入口 ——
 * 包括那些在「图纸一律作为附件发出」年代写成、既没有 {fields_table} 也没有
 * {drawings}/{inquiry_link} 的存量自定义模板。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

function esc_html(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function esc_url(string $url): string
{
    return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
}

function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

function get_theme_mod(string $name, mixed $default_value = false): mixed
{
    return $default_value;
}

function wp_strip_all_tags(string $text): string
{
    return trim(strip_tags($text));
}

function sanitize_text_field(string $text): string
{
    return trim(strip_tags($text));
}

function wp_kses_post(string $text): string
{
    return $text;
}

require __DIR__ . '/../inc/mail-template.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$drawings = 'drawing-A.pdf (3.0 MB), drawing-B.dwg (1.1 MB)';
$link = 'https://example.com/wp-admin/post.php?post=12&action=edit';

// 老写法：模板只有留言，图纸当年靠附件送达。停发附件后必须补上清单和入口。
$legacy = '<p>收到新询盘</p><p>留言：需要报价</p>';
$patched = springapex_inquiry_mail_with_drawing_notice($legacy, $drawings, $link);
springapex_test_assert($patched !== $legacy, 'A legacy template got no drawing notice at all.');
springapex_test_assert(str_contains($patched, esc_html($drawings)), 'The drawing list is missing from a legacy template.');
springapex_test_assert(str_contains($patched, esc_url($link)), 'The backend link is missing from a legacy template.');

// 模板已经把清单和入口都渲染出来了，不重复追加。
$complete = '<p>' . esc_html($drawings) . '</p><a href="' . esc_url($link) . '">查看询盘</a>';
springapex_test_assert(
    springapex_inquiry_mail_with_drawing_notice($complete, $drawings, $link) === $complete,
    'A template that already shows both got a duplicate notice.'
);

// 只有清单没有入口：收件人知道有图纸却不知道去哪拿，仍要补。
$list_only = '<p>' . esc_html($drawings) . '</p>';
$fixed = springapex_inquiry_mail_with_drawing_notice($list_only, $drawings, $link);
springapex_test_assert(str_contains($fixed, esc_url($link)), 'A template without the backend link kept no route to the files.');

// 只有入口没有清单：收件人不知道有图纸，可能根本不会点进去。
$link_only = '<a href="' . esc_url($link) . '">查看询盘</a>';
$fixed2 = springapex_inquiry_mail_with_drawing_notice($link_only, $drawings, $link);
springapex_test_assert(str_contains($fixed2, esc_html($drawings)), 'A template without the file list never mentions the drawings.');

// 没有图纸的询盘不该凭空多出一段。
springapex_test_assert(
    springapex_inquiry_mail_with_drawing_notice($legacy, '', $link) === $legacy,
    'An inquiry without drawings still got a drawing notice.'
);
springapex_test_assert(
    springapex_inquiry_mail_with_drawing_notice($legacy, '   ', $link) === $legacy,
    'A blank drawing list still got a notice.'
);

// 后台链接拿不到时也要给出清单，至少让收件人知道有图纸存在。
$no_link = springapex_inquiry_mail_with_drawing_notice($legacy, $drawings, '');
springapex_test_assert(str_contains($no_link, esc_html($drawings)), 'Without a link the notice dropped the file list too.');

// 默认模板同时含 {fields_table} 和 {inquiry_link}，渲染后不该再追加。
$default_rendered = springapex_fill_mail_template(springapex_inquiry_mail_default_body(), [
    '{fields_table}' => '<table><tr><td>图纸</td><td>' . esc_html($drawings) . '</td></tr></table>',
    '{inquiry_link}' => esc_url($link),
    '{message}' => '需要报价',
    '{name}' => '客户',
    '{site_name}' => 'NorenSpring',
    '{type}' => '报价',
]);
springapex_test_assert(
    springapex_inquiry_mail_with_drawing_notice($default_rendered, $drawings, $link) === $default_rendered,
    'The shipped default template triggered a redundant notice.'
);

// 文件名里的特殊字符不能把 HTML 撑破。
$tricky = '<script>alert(1)</script>.pdf (1.0 MB)';
$escaped = springapex_inquiry_mail_with_drawing_notice($legacy, $tricky, $link);
springapex_test_assert(!str_contains($escaped, '<script>'), 'A crafted file name was injected into the mail body.');
springapex_test_assert(str_contains($escaped, esc_html($tricky)), 'The escaped file name is missing.');

echo "inquiry-mail-drawing-notice: legacy templates, complete templates, partial templates, no-drawing inquiries and unsafe names ok\n";
