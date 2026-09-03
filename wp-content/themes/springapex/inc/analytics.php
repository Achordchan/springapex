<?php
/**
 * 跟踪代码输出：Google Tag Manager 容器，以及后台维护的三个自定义代码位。
 *
 * WordPress 核心没有「在 head / body 前后插代码」的界面，只有 wp_head、
 * wp_body_open、wp_footer 三个钩子，而 header.php 与 footer.php 三个都已在位。
 * 本文件负责往这三处输出，后台界面在 inc/admin/tracking-settings.php。
 *
 * GTM 容器号与三段自定义代码都存在同一个 option 里，运营可以自己维护，改容器号
 * 或加一段客服代码不再需要动主题文件。容器号单独成字段而不是让运营粘贴整段
 * GTM 代码：这样能做格式校验、noscript 兜底由主题自动补齐，也就没有「两段只贴了
 * 一段」或「贴重复导致双重计数」的余地。
 *
 * 取值优先级：wp-config.php 的 SPRINGAPEX_GTM_ID 常量 > 后台保存的值 > 客户提供
 * 的种子容器号。注意后台保存的空串是「运营主动关掉」，不回退到种子值——只有从未
 * 保存过设置的站点才用种子。
 *
 * 环境判断只在站点被明确声明为非生产时才闭嘴：wp_get_environment_type() 在没有
 * 配置 WP_ENVIRONMENT_TYPE 时返回 'production'，所以生产站不会因为漏配环境变量
 * 而静默丢掉统计——「客户说 GA 收不到数据」是这里最难查的故障。
 *
 * 关于同意门控：本文件不做 cookie 同意判断，容器在生产站上无条件加载，因此面向
 * 欧盟访客的「事前同意」要求目前并未满足；隐私页给出的浏览器设置、拦截器和
 * Google 退出插件是过渡期的拒绝途径。这是站点所有者的决定——同意管理会在后续于
 * 生产环境统一引入，而不是在主题里先做半套。
 *
 * 届时不要在这里再叠一层门控：同意管理方案（Complianz、CookieYes 之类）几乎都会
 * 自己接管容器加载或对接 Google Consent Mode，两套门控并存只会互相打架，表现为
 * 标签该拦的没拦、该放的没放。正确做法是把容器交给那套方案，然后把后台的容器号
 * 清空（或在 wp-config.php 定义 SPRINGAPEX_GTM_ID = ''）让本文件干净退场。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** 后台「跟踪代码」页写入的 option；前台与后台共用这一个名字。 */
const SPRINGAPEX_TRACKING_OPTION = 'springapex_tracking_settings';

/** 客户提供的容器号，仅作为站点从未保存过跟踪设置时的种子值。 */
const SPRINGAPEX_TRACKING_DEFAULT_GTM_ID = 'GTM-W3B3NW5N';

/**
 * 三个自定义代码位：挂哪个钩子，以及后台怎么向运营解释它。
 *
 * @return array<string, array{hook:string,label:string,help:string}>
 */
function springapex_tracking_slots(): array
{
    return [
        'head' => [
            'hook' => 'wp_head',
            'label' => '页面头部（</head> 之前）',
            'help' => '需要尽早执行或必须待在 head 里的代码放这里：站长平台的 meta 验证标签、Bing 统计、Microsoft Clarity 等。',
        ],
        'body_open' => [
            'hook' => 'wp_body_open',
            'label' => '正文开始（<body> 之后）',
            'help' => '第三方明确要求「紧跟 body 起始标签」的代码放这里，通常是 noscript 兜底那一段。',
        ],
        'body_close' => [
            'hook' => 'wp_footer',
            'label' => '页面底部（</body> 之前）',
            'help' => '在线客服、聊天窗口，以及一切不必抢先加载的脚本放这里，对打开速度影响最小。拿不准就放这里。',
        ],
    ];
}

/** 后台保存的跟踪设置；从未保存过时是空数组。 */
function springapex_tracking_settings(): array
{
    $settings = get_option(SPRINGAPEX_TRACKING_OPTION, []);

    return is_array($settings) ? $settings : [];
}

/** 本站当前是否应当向访客输出跟踪代码。 */
function springapex_tracking_is_enabled(): bool
{
    return wp_get_environment_type() === 'production';
}

/**
 * 当前请求应当输出的 GTM 容器号；空串表示不输出。
 *
 * 格式校验挡的是配置笔误：容器号会被拼进 JS 字面量和 URL，与其让一个写坏的值
 * 产出半截脚本，不如整段不输出，故障现象也更直白。后台保存时已经校验过一遍，
 * 这里再校验一次，覆盖常量写错和历史脏数据。
 */
function springapex_gtm_container_id(): string
{
    if (defined('SPRINGAPEX_GTM_ID')) {
        $container_id = trim((string) SPRINGAPEX_GTM_ID);
    } else {
        $settings = springapex_tracking_settings();
        $container_id = array_key_exists('gtm_id', $settings)
            ? trim((string) $settings['gtm_id'])
            : SPRINGAPEX_TRACKING_DEFAULT_GTM_ID;
    }

    if ($container_id === '' || !springapex_tracking_is_enabled()) {
        return '';
    }

    return preg_match('/^GTM-[A-Z0-9]+$/', $container_id) === 1 ? $container_id : '';
}

/** 某个代码位当前应当输出的内容；空串表示没有配置或本环境不输出。 */
function springapex_tracking_custom_code(string $slot): string
{
    if (!springapex_tracking_is_enabled()) {
        return '';
    }

    $settings = springapex_tracking_settings();
    $slots = is_array($settings['slots'] ?? null) ? $settings['slots'] : [];

    return trim((string) ($slots[$slot] ?? ''));
}

// GTM 第一段：<head> 内尽量靠上的主脚本。
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

// GTM 第二段：紧跟 <body> 的 noscript 兜底。
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

// 后台维护的三段自定义代码，排在 GTM 之后。
foreach (springapex_tracking_slots() as $springapex_tracking_slot => $springapex_tracking_definition) {
    add_action((string) $springapex_tracking_definition['hook'], static function () use ($springapex_tracking_slot): void {
        $code = springapex_tracking_custom_code($springapex_tracking_slot);
        if ($code === '') {
            return;
        }

        // 原样输出：这里存的就是运营从第三方平台复制来的代码，转义它等于让它
        // 失效，本页面的功能也就没有了。写入门槛见 admin/tracking-settings.php，
        // 由 WP 原生的 unfiltered_html 能力把关。
        echo "\n" . $code . "\n";
    }, 20);
}
unset($springapex_tracking_slot, $springapex_tracking_definition);
