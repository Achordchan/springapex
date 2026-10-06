<?php
/**
 * 根目录 /llms.txt：给 AI 助手读的站点说明（llmstxt.org 格式的 Markdown）。
 *
 * 开头的公司介绍存 option springapex_llms_txt_intro，可经 REST /wp/v2/settings
 * 改（应用密码），留空就用下面的默认文字；产品、行业、案例、文章、公司页面的
 * 列表每次请求从线上数据生成，标题和描述取 inc/seo.php 的 TDK，新增内容自动出现。
 *
 * Google 不用 llms.txt，所以回应带 X-Robots-Tag: noindex，不让这份纯文本进搜索结果。
 * .txt 请求由 nginx 的 location / 交给 WordPress（见 deploy/nginx-norenspring.com.conf）。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_LLMS_TXT_INTRO_OPTION = 'springapex_llms_txt_intro';

function springapex_llms_txt_default_intro(): string
{
    return <<<'MD'
NorenSpring (Xuzhou APEX Spring Manufacturing Co., Ltd.) is a custom spring manufacturer established in 2001 in Xuzhou, Jiangsu, China. It designs and makes compression, extension, torsion, die, disc, wave, conical and vibrator springs and wire forms to customer drawings, for OEMs and aftermarket buyers.

Key facts:
- Founded 2001; headquarters and factories in Liuji Town, Tongshan District, Xuzhou, Jiangsu, China
- More than 120 employees; 12,000 square meters of production area
- Wire diameters from 0.1 mm to 80 mm
- Certified to IATF 16949, ISO 9001, ISO 13485, ISO 14001 and ISO 45001
- Samples in 7-15 working days; mass production in 15-30 working days
- Springs are made to the buyer's drawing or sample; prices are quoted per project, there is no public price list
MD;
}

function springapex_llms_txt_register_setting(): void
{
    register_setting('springapex_llms', SPRINGAPEX_LLMS_TXT_INTRO_OPTION, [
        'type' => 'string',
        'description' => 'llms.txt 开头的公司介绍（Markdown）；留空用主题默认文字。',
        'sanitize_callback' => 'sanitize_textarea_field',
        'default' => '',
        'show_in_rest' => true,
    ]);
}
add_action('init', 'springapex_llms_txt_register_setting');

/** 单行纯文本：去标签、还原实体、压空白，链接文字里的方括号换成圆括号。 */
function springapex_llms_txt_line(string $text): string
{
    $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $text = trim((string) preg_replace('/\s+/u', ' ', $text));
    return strtr($text, ['[' => '(', ']' => ')']);
}

function springapex_llms_txt_link(string $title, string $url, string $description = ''): string
{
    $line = '- [' . springapex_llms_txt_line($title) . '](' . esc_url_raw($url) . ')';
    $description = springapex_llms_txt_line($description);
    return $description !== '' ? $line . ': ' . $description : $line;
}

/**
 * @return array<int, string>
 */
function springapex_llms_txt_post_links(string $post_type, array $order): array
{
    $posts = get_posts(array_merge([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => 200,
        'has_password' => false,
        'no_found_rows' => true,
        'suppress_filters' => false,
    ], $order));

    $lines = [];
    foreach ($posts as $post) {
        $seo = springapex_seo_post_values($post);
        $lines[] = springapex_llms_txt_link(get_the_title($post), (string) get_permalink($post), $seo['description']);
    }
    return $lines;
}

/**
 * @return array<int, string>
 */
function springapex_llms_txt_route_links(array $routes): array
{
    $definitions = springapex_seo_route_definitions();
    $lines = [];
    foreach ($routes as $route) {
        if (!isset($definitions[$route])) {
            continue;
        }
        $values = springapex_seo_route_values($route);
        // TDK 标题带「| NorenSpring」后缀，链接文字只要竖线前那段。
        $title = trim(explode('|', $values['title'])[0]);
        $lines[] = springapex_llms_txt_link($title, home_url($definitions[$route]['path']), $values['description']);
    }
    return $lines;
}

function springapex_llms_txt_body(): string
{
    $brand = springapex_brand();
    $name = trim((string) ($brand['name'] ?? '')) ?: 'NorenSpring';
    $home = springapex_seo_route_values('home');

    $intro = trim((string) get_option(SPRINGAPEX_LLMS_TXT_INTRO_OPTION, ''));
    if ($intro === '') {
        $intro = springapex_llms_txt_default_intro();
    }

    $sections = [
        'Products' => springapex_llms_txt_post_links('spring_product', ['orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]),
        'Industries' => springapex_llms_txt_post_links('spring_solution', ['orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]),
        'Case studies' => springapex_llms_txt_post_links('spring_case', ['orderby' => ['menu_order' => 'ASC', 'title' => 'ASC']]),
        'Guides and news' => springapex_llms_txt_post_links('spring_news', ['orderby' => 'date', 'order' => 'DESC']),
        'Company' => springapex_llms_txt_route_links(['about', 'capabilities', 'manufacturing-videos', 'sustainability', 'resources']),
    ];

    $contact = springapex_llms_txt_route_links(['contact']);
    foreach (['email' => 'Email', 'phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'address' => 'Address', 'hours' => 'Hours'] as $key => $label) {
        $value = springapex_llms_txt_line((string) ($brand[$key] ?? ''));
        if ($value !== '') {
            $contact[] = '- ' . $label . ': ' . $value;
        }
    }
    $sections['Contact'] = $contact;

    $out = '# ' . springapex_llms_txt_line($name) . "\n\n";
    $out .= '> ' . springapex_llms_txt_line($home['description']) . "\n\n";
    $out .= $intro . "\n";
    foreach ($sections as $heading => $lines) {
        if ($lines !== []) {
            $out .= "\n## " . $heading . "\n\n" . implode("\n", $lines) . "\n";
        }
    }
    return $out;
}

function springapex_serve_llms_txt(WP $wp): void
{
    if ($wp->request !== 'llms.txt') {
        return;
    }

    $body = springapex_llms_txt_body();
    status_header(200);
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Robots-Tag: noindex');
    header('Cache-Control: public, max-age=3600');
    header('Content-Length: ' . (string) strlen($body));
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        echo $body;
    }
    exit;
}
add_action('parse_request', 'springapex_serve_llms_txt', 1);
