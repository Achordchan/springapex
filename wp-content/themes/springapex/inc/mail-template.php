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
    return '[ApexSpring] {type} inquiry from {name}';
}

function springapex_inquiry_mail_default_body(): string
{
    return <<<'TXT'
Name: {name}
Email: {email}
Company: {company}
Phone: {phone}
Country: {country}
Type: {type}
{dimensions}Quantity: {quantity}
Material: {material}
Operating environment: {operating_environment}
Intent: {intent}
Product: {product}
Industry: {industry}
{custom_fields}Document: {document}
Drawings: {drawings}

{message}

Inquiry record: {inquiry_link}
TXT;
}

function springapex_inquiry_mail_subject(): string
{
    $stored = sanitize_text_field((string) get_theme_mod('springapex_inquiry_mail_subject', ''));
    return $stored !== '' ? $stored : springapex_inquiry_mail_default_subject();
}

function springapex_inquiry_mail_body(): string
{
    $stored = sanitize_textarea_field((string) get_theme_mod('springapex_inquiry_mail_body', ''));
    return $stored !== '' ? $stored : springapex_inquiry_mail_default_body();
}

/**
 * 占位符 => 中文说明，供设置页速查表。
 * {dimensions}/{custom_fields} 是多行块：有内容时以换行结尾，空块不占行，
 * 所以默认正文里它们后面直接跟下一行标签。
 */
function springapex_inquiry_mail_placeholders(): array
{
    return [
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
        '{dimensions}' => '尺寸参数块（按产品自动换标签，多行）',
        '{custom_fields}' => '自定义字段块（多行，空则不占行）',
        '{message}' => '留言正文',
        '{document}' => '随附文档标识',
        '{drawings}' => '图纸文件名列表（无则 None）',
        '{inquiry_link}' => '后台询盘记录链接',
        '{site_name}' => '站点名称',
        '{site_url}' => '站点地址',
    ];
}

/**
 * 占位符替换：未识别的 {token} 原样保留（运营者能立刻看出写错了）；
 * 连续空行折叠为一行，避免空块留下大段空白。
 */
function springapex_fill_mail_template(string $template, array $vars): string
{
    $filled = strtr($template, $vars);
    return (string) preg_replace('/\n{3,}/', "\n\n", $filled);
}
