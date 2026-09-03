<?php
/**
 * 询盘通知邮件模板：标题与正文在后台「表单设置 → 邮件通知模板」编辑，
 * 存 theme_mod，留空走内置默认；发送时占位符替换成询盘真实值。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_inquiry_mail_default_subject(): string
{
    return '[{site_name}] {type} inquiry from {name}';
}

/**
 * 默认正文为 HTML（邮件安全的内联样式 + 表格布局，兼容主流邮件客户端）。
 * 运营者可在「表单设置 → 邮件通知模板」直接改这段 HTML；{fields_table}
 * 自动渲染整张询盘信息表，{message} 为留言，其余为标量占位符。
 * 发送前会用 springapex_inquiry_mail_document() 套一层最小外壳。
 */
function springapex_inquiry_mail_default_body(): string
{
    return <<<'HTML'
<div style="display:none;max-height:0;overflow:hidden;color:#f4f7fa;font-size:1px;line-height:1px;">来自 {name} 的新询盘，点击查看完整提交内容。</div>
<table role="presentation" cellpadding="0" cellspacing="0" width="100%" bgcolor="#f4f7fa" style="width:100%;margin:0;padding:0;background:#f4f7fa;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#111827;">
  <tr>
    <td align="center" style="padding:32px 12px;">
      <table role="presentation" cellpadding="0" cellspacing="0" width="640" style="width:100%;max-width:640px;background:#ffffff;border:1px solid #e5e7eb;border-radius:14px;overflow:hidden;">
        <tr><td style="height:5px;background:#0ea5e9;font-size:0;line-height:0;">&nbsp;</td></tr>
        <tr>
          <td style="padding:26px 30px 22px;background:#0f172a;">
            <table role="presentation" cellpadding="0" cellspacing="0" width="100%">
              <tr>
                <td style="vertical-align:middle;">
                  <div style="color:#ffffff;font-size:20px;font-weight:700;line-height:1.25;">{site_name}</div>
                  <div style="margin-top:5px;color:#a9bad0;font-size:13px;line-height:1.5;">询盘通知 · Inquiry notification</div>
                </td>
                <td align="right" style="vertical-align:middle;">
                  <span style="display:inline-block;padding:6px 10px;border:1px solid #334155;border-radius:999px;color:#bae6fd;font-size:11px;font-weight:700;letter-spacing:.08em;">NEW INQUIRY</span>
                </td>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td style="padding:28px 30px 8px;">
            <div style="font-size:21px;font-weight:700;line-height:1.4;color:#111827;">收到一条新的{type}询盘</div>
            <p style="margin:8px 0 0;font-size:14px;line-height:1.7;color:#6b7280;">客户 <strong style="color:#111827;">{name}</strong> 已提交需求。直接回复本邮件即可联系客户。</p>
          </td>
        </tr>
        <tr>
          <td style="padding:16px 30px 4px;">
            <div style="margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:.08em;color:#6b7280;">询盘信息 · DETAILS</div>
            {fields_table}
          </td>
        </tr>
        <tr>
          <td style="padding:10px 30px 4px;">
            <div style="margin-bottom:8px;font-size:11px;font-weight:700;letter-spacing:.08em;color:#6b7280;">客户留言 · MESSAGE</div>
            <div style="padding:16px 18px;background:#f8fafc;border-left:4px solid #0ea5e9;border-radius:8px;font-size:14px;line-height:1.8;color:#334155;white-space:pre-wrap;">{message}</div>
          </td>
        </tr>
        <tr>
          <td style="padding:22px 30px 30px;">
            <a href="{inquiry_link}" target="_blank" style="display:inline-block;padding:12px 20px;background:#f97316;border-radius:8px;color:#ffffff;font-size:14px;font-weight:700;line-height:1.2;text-decoration:none;">在后台查看询盘 →</a>
            <div style="margin-top:12px;font-size:12px;line-height:1.6;color:#9ca3af;">按钮无法打开时，可登录 WordPress 后台进入“询盘”查看。</div>
          </td>
        </tr>
        <tr>
          <td style="padding:17px 30px;background:#f8fafc;border-top:1px solid #e5e7eb;font-size:12px;line-height:1.6;color:#9ca3af;">
            本邮件由 {site_name} 自动发送。客户上传的图纸不随邮件发送，请点上方按钮到后台询盘详情页下载。
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
HTML;
}

function springapex_inquiry_mail_subject(): string
{
    $stored = sanitize_text_field((string) get_theme_mod('springapex_inquiry_mail_subject', ''));
    return $stored !== '' ? $stored : springapex_inquiry_mail_default_subject();
}

/**
 * 正文（HTML）。新模板在保存时已过 wp_kses_post；旧版本若留有纯文本
 * 自定义模板，则安全转换为保留换行的 HTML，不能静默丢弃运营者内容。
 */
function springapex_inquiry_mail_body(): string
{
    $stored = (string) get_theme_mod('springapex_inquiry_mail_body', '');
    if ($stored === '') {
        return springapex_inquiry_mail_default_body();
    }
    if (str_contains($stored, '<')) {
        return $stored;
    }
    return '<div style="font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;font-size:14px;line-height:1.7;color:#1d2327;">'
        . nl2br(esc_html($stored))
        . '</div>';
}

/**
 * 把询盘字段渲染成一张邮件安全的信息表（内联样式、隔行浅色）。
 * $rows 为 [label, value] 列表（用列表而非关联数组，避免同名标签相互覆盖）；
 * 值经 esc_html，空值跳过。
 */
function springapex_inquiry_mail_fields_table(array $rows): string
{
    $body = '';
    $i = 0;
    foreach ($rows as $row) {
        $label = trim((string) ($row[0] ?? ''));
        $value = trim((string) ($row[1] ?? ''));
        if ($value === '' || $label === '') {
            continue;
        }
        $bg = ($i % 2 === 0) ? '#ffffff' : '#f9fafb';
        $body .= sprintf(
            '<tr>'
            . '<td style="padding:9px 12px;background:%1$s;border:1px solid #eceef0;font-size:13px;color:#646970;width:38%%;vertical-align:top;">%2$s</td>'
            . '<td style="padding:9px 12px;background:%1$s;border:1px solid #eceef0;font-size:13px;color:#1d2327;vertical-align:top;">%3$s</td>'
            . '</tr>',
            $bg,
            esc_html($label),
            nl2br(esc_html($value))
        );
        $i++;
    }
    if ($body === '') {
        return '';
    }
    return '<table role="presentation" cellpadding="0" cellspacing="0" width="100%" style="border-collapse:collapse;margin:4px 0 10px;">' . $body . '</table>';
}

/** 把若干字段转换成纯文本行，供主题和 HTML 兼容占位符共用。 */
function springapex_inquiry_mail_text_lines(array $rows, string $separator = "\n"): string
{
    $lines = [];
    foreach ($rows as $row) {
        $label = trim((string) ($row[0] ?? ''));
        $value = trim((string) ($row[1] ?? ''));
        if ($label !== '' && $value !== '') {
            $lines[] = $label . '：' . $value;
        }
    }
    return implode($separator, $lines);
}

/** 把若干字段转换成 HTML 多行块，兼容 {dimensions}/{custom_fields}。 */
function springapex_inquiry_mail_lines(array $rows): string
{
    return nl2br(esc_html(springapex_inquiry_mail_text_lines($rows)));
}

/** 发送前给正文套的最小 HTML 外壳（doctype/charset/viewport）。 */
function springapex_inquiry_mail_document(string $body): string
{
    return '<!doctype html><html lang="zh"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1"></head>'
        . '<body style="margin:0;padding:0;background:#f4f5f7;">' . $body . '</body></html>';
}

/** 纯文本兜底（multipart 的 AltBody），由字段数据直接构建，利于送达与老客户端。 */
function springapex_inquiry_mail_plaintext(array $rows, string $message, string $link): string
{
    $lines = [];
    foreach ($rows as $row) {
        $label = trim((string) ($row[0] ?? ''));
        $value = trim((string) ($row[1] ?? ''));
        if ($label !== '' && $value !== '') {
            $lines[] = $label . ': ' . $value;
        }
    }
    $out = implode("\n", $lines);
    if (trim($message) !== '') {
        $out .= "\n\n留言 / Message:\n" . $message;
    }
    if ($link !== '') {
        $out .= "\n\n查看询盘 / Inquiry record: " . $link;
    }
    return trim($out);
}

/**
 * 占位符 => 中文说明，供设置页速查表。
 * {dimensions}/{custom_fields} 是兼容旧模板的 HTML 多行块；新模板优先使用
 * {fields_table}，自动包含尺寸、自定义字段和图纸信息。
 */
function springapex_inquiry_mail_placeholders(): array
{
    return [
        '{fields_table}' => '整张询盘信息表（自动生成，推荐直接用）',
        '{name}' => '姓名',
        '{email}' => '邮箱',
        '{phone}' => '电话',
        '{company}' => '公司',
        '{country}' => '国家/地区',
        '{type}' => '询盘类型',
        '{product}' => '相关产品',
        '{industry}' => '相关行业',
        '{intent}' => '合作意向',
        '{quantity}' => '数量',
        '{material}' => '材料',
        '{operating_environment}' => '工作环境',
        '{dimensions}' => '尺寸参数块（兼容旧模板，HTML 多行）',
        '{custom_fields}' => '自定义字段块（兼容旧模板，HTML 多行）',
        '{message}' => '留言正文',
        '{document}' => '随附文档标识',
        '{drawings}' => '图纸文件名与大小列表（无则 None）；图纸本身在后台询盘详情页下载',
        '{inquiry_link}' => '后台询盘记录链接',
        '{site_name}' => '站点名称',
        '{site_url}' => '站点地址',
    ];
}

/**
 * 占位符替换：未识别的 {token} 原样保留（运营者能立刻看出写错了）；
 * 不改写模板空白，避免破坏运营者在 HTML / pre 标签中的排版。
 */
function springapex_fill_mail_template(string $template, array $vars): string
{
    return strtr($template, $vars);
}
