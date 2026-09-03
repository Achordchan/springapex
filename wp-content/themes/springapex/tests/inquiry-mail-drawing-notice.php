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

/** 站内用户表：邮箱 => [是否能读这条询盘] */
$springapex_test_users = [];

class WP_User
{
    /**
     * @param bool $can_edit_private CPT 的 edit_private_posts（设置页/预览用的近似判断）
     * @param array<int, int> $editable_inquiries 能打开哪几条询盘详情页（发送时的精确判断）
     */
    public function __construct(
        public string $user_email,
        public bool $can_edit_private = true,
        public array $editable_inquiries = []
    ) {
    }
}

function get_user_by(string $field, string $value): WP_User|false
{
    global $springapex_test_users;
    return $springapex_test_users[$value] ?? false;
}

function user_can(WP_User $user, string $capability, mixed ...$args): bool
{
    // 邮件里的链接是 post.php?action=edit，WordPress 用 per-post 的 edit_post 把关。
    if ($capability === 'edit_post') {
        return in_array((int) ($args[0] ?? 0), $user->editable_inquiries, true);
    }
    return $capability === 'edit_private_spring_inquiries' ? $user->can_edit_private : false;
}

/** 询盘 CPT 注册时用 capability_type ['spring_inquiry','spring_inquiries']。 */
function get_post_type_object(string $post_type): ?object
{
    return $post_type === 'spring_inquiry'
        ? (object) ['cap' => (object) ['edit_private_posts' => 'edit_private_spring_inquiries']]
        : null;
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

// 收件人能不能自己去后台取件，决定了这封邮件还要不要夹带图纸。
$springapex_test_users = [
    // 管理员：能打开询盘 #12 的详情页
    'admin@example.com' => new WP_User('admin@example.com', true, [12]),
    // 只读角色：CPT 能力不足，详情页也打不开
    'editor@example.com' => new WP_User('editor@example.com', false, []),
    // 能读私密文章、却没有这条询盘的编辑权 —— 打不开带下载入口的详情页
    'readonly@example.com' => new WP_User('readonly@example.com', true, []),
];
springapex_test_assert(
    springapex_inquiry_recipient_reads_backend('admin@example.com'),
    'An administrator able to open inquiries was treated as unable to fetch the files.'
);
// 发送时带上具体询盘 id，走 per-post 判断。
springapex_test_assert(
    springapex_inquiry_recipient_reads_backend('admin@example.com', 12),
    'An administrator could not open the very inquiry being mailed.'
);
// 能读私密文章不等于打得开 post.php?action=edit 那一页 —— 那里才有带 nonce 的
// 下载入口。这种收件人必须继续收到附件。
springapex_test_assert(
    !springapex_inquiry_recipient_reads_backend('readonly@example.com', 12),
    'A user who cannot edit the inquiry was assumed to reach its download links.'
);
// 外部共享销售邮箱：没有站内账号，后台链接对他没用，必须继续收到附件。
springapex_test_assert(
    !springapex_inquiry_recipient_reads_backend('sales@partner.example'),
    'A recipient without a WordPress account was assumed to have backend access.'
);
// 有账号但读不了询盘（本主题只把 read_private_spring_inquiries 给管理员，
// Editor 的 edit_posts 在这里不算数），同样取不到文件。
springapex_test_assert(
    !springapex_inquiry_recipient_reads_backend('editor@example.com'),
    'A user without the inquiry read capability was assumed to have access.'
);
springapex_test_assert(
    !springapex_inquiry_recipient_reads_backend(''),
    'An empty recipient was treated as a backend user.'
);
springapex_test_assert(
    !springapex_inquiry_recipient_reads_backend('   '),
    'A blank recipient was treated as a backend user.'
);

// 照旧默认模板存下来的副本：清单和入口都在，但底部还写着「附件为客户上传的图纸
// 文件」。附件已经不发了，这句话会让收件人去翻一个不存在的附件。
$legacy_default = '<p>' . esc_html($drawings) . '</p><a href="' . esc_url($link) . '">查看询盘</a>'
    . '<p>本邮件自动发送。附件为客户上传的图纸文件，请按内部流程妥善处理。</p>';
springapex_test_assert(
    springapex_inquiry_mail_with_drawing_notice($legacy_default, $drawings, $link) === $legacy_default,
    'A template that already lists the drawings got a duplicate notice.'
);
$clarified = springapex_inquiry_mail_clarify_missing_attachments($legacy_default, $drawings);
springapex_test_assert($clarified !== $legacy_default, 'Legacy attachment wording was left uncorrected.');
springapex_test_assert(str_contains($clarified, '没有夹带附件'), 'The clarification text is missing.');

// 模板没提附件时不该平白多一句。
$clean = '<p>' . esc_html($drawings) . '</p><a href="' . esc_url($link) . '">查看询盘</a>';
springapex_test_assert(
    springapex_inquiry_mail_clarify_missing_attachments($clean, $drawings) === $clean,
    'A template without attachment wording got an unnecessary clarification.'
);
// 没有图纸的询盘同样不需要澄清。
springapex_test_assert(
    springapex_inquiry_mail_clarify_missing_attachments($legacy_default, '') === $legacy_default,
    'An inquiry without drawings got an attachment clarification.'
);

echo "inquiry-mail-drawing-notice: legacy templates, complete templates, partial templates, no-drawing inquiries, unsafe names, recipient access and legacy attachment wording ok\n";
