<?php
/**
 * Google 跟踪代码管理器（GTM）接入。
 *
 * 具体标签（GA4、Ads 转化等）由客户在 GTM 面板里管理，站点侧只负责挂上容器
 * 代码。官方要求两段：主脚本尽量靠近 <head> 顶部，noscript 兜底紧跟 <body>
 * 起始标签。header.php 的 wp_head() / wp_body_open() 正好是这两个位置，两个
 * 钩子都取优先级 1，让输出排在同钩子其它内容之前——因此不需要额外的
 * headers/footers 插件，也不需要一个开放任意 HTML 的后台输入框。
 *
 * 容器号默认写死为客户容器，可在 wp-config.php 定义 SPRINGAPEX_GTM_ID 覆盖；
 * 定义为空串即完全关闭输出，本地调试不想污染客户统计时用这一招。
 *
 * 环境判断只在站点被明确声明为非生产时才闭嘴：wp_get_environment_type() 在
 * 没有配置 WP_ENVIRONMENT_TYPE 时返回 'production'，所以生产站不会因为漏配
 * 环境变量而静默丢掉统计——「客户说 GA 收不到数据」是这里最难查的故障。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 当前请求应当输出的 GTM 容器号；空串表示本次请求不输出。
 *
 * 格式校验挡的是配置笔误：容器号会被拼进 JS 字面量和 URL，与其让一个写坏的
 * 常量产出半截脚本，不如整段不输出，故障现象也更直白。
 */
function springapex_gtm_container_id(): string
{
    $container_id = defined('SPRINGAPEX_GTM_ID') ? trim((string) SPRINGAPEX_GTM_ID) : 'GTM-W3B3NW5N';
    if ($container_id === '' || wp_get_environment_type() !== 'production') {
        return '';
    }

    return preg_match('/^GTM-[A-Z0-9]+$/', $container_id) === 1 ? $container_id : '';
}

// 第一段：<head> 内尽量靠上的主脚本。
add_action('wp_head', static function (): void {
    $container_id = springapex_gtm_container_id();
    if ($container_id === '') {
        return;
    }

    echo "<!-- Google Tag Manager -->\n"
        . "<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':\n"
        . "new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],\n"
        . "j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=\n"
        . "'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);\n"
        . "})(window,document,'script','dataLayer','" . esc_js($container_id) . "');</script>\n"
        . "<!-- End Google Tag Manager -->\n";
}, 1);

// 第二段：紧跟 <body> 的 noscript 兜底。
add_action('wp_body_open', static function (): void {
    $container_id = springapex_gtm_container_id();
    if ($container_id === '') {
        return;
    }

    $frame_src = add_query_arg('id', $container_id, 'https://www.googletagmanager.com/ns.html');
    printf(
        "<!-- Google Tag Manager (noscript) -->\n"
        . '<noscript><iframe src="%s" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>'
        . "\n<!-- End Google Tag Manager (noscript) -->\n",
        esc_url($frame_src)
    );
}, 1);
