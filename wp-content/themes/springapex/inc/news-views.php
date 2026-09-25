<?php
/**
 * 新闻阅读数：前台显示「基准数 + 真实阅读」。
 *
 * 两个数分开存：基准数由运营在新闻「基础信息」里填；真实阅读只由访客浏览
 * 累加，后台和 REST 都改不了。前台卡片和详情页只显示两者之和。
 *
 * 计数规则取公开计数器的主流做法（Post Views Counter 的默认值、Medium）：
 * - 同一浏览器同一篇 24 小时内只算一次（assets/js/news-views.js，localStorage）；
 * - 服务器兜底：同一 IP 同一篇 10 分钟内只算一次，挡住脚本反复刷；
 * - 不算：爬虫（按 User-Agent）、登录后台的人、预览；只数已发布的文章。
 *
 * 计数由浏览器在页面加载后发请求完成，而不是服务器渲染时累加：站点在
 * CloudFront 后面，一旦整页缓存，渲染时计数只会数到生成缓存的那一次。
 * 24 小时内再打开同一篇，浏览器仍会请求一次，但只取最新合计、不计数，
 * 免得缓存页面上的旧数字一直留着。
 *
 * 两处「先查再写」都放在 MySQL 命名锁里（springapex_news_views_locked()）：
 * 同一 IP 抢 10 分钟名额、一篇新闻第一次计数时建计数行。不加锁时，本地 20 个
 * 并发请求实测能让 16 个同时通过限流，首次计数插出 16 行、页面只显示 5。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_NEWS_VIEWS_META = '_springapex_news_views';
const SPRINGAPEX_NEWS_VIEWS_BASE_META = '_springapex_news_views_base';
const SPRINGAPEX_NEWS_VIEWS_BASE_MAX = 100000000;
const SPRINGAPEX_NEWS_VIEWS_IP_WINDOW = 10 * MINUTE_IN_SECONDS;

function springapex_news_views_real(int $post_id): int
{
    return absint(get_post_meta($post_id, SPRINGAPEX_NEWS_VIEWS_META, true));
}

function springapex_news_views_base(int $post_id): int
{
    return absint(get_post_meta($post_id, SPRINGAPEX_NEWS_VIEWS_BASE_META, true));
}

function springapex_news_views_total(int $post_id): int
{
    return springapex_news_views_base($post_id) + springapex_news_views_real($post_id);
}

/** 后台和 REST 写基准数共用：非数字、负数都当 0，上限一亿。 */
function springapex_sanitize_news_views_base(mixed $value): int
{
    $number = is_numeric($value) ? (int) $value : 0;

    return min(max(0, $number), SPRINGAPEX_NEWS_VIEWS_BASE_MAX);
}

/**
 * 详情页计数元素上的属性。只有真正该计数的页面才带：已发布、非预览、访客
 * 未登录。登录后台的人（自己人编辑、检查页面）不带属性，脚本就不发请求。
 *
 * @return array<string, string>
 */
function springapex_news_view_beacon_attributes(?object $post): array
{
    if (
        !($post instanceof WP_Post) ||
        $post->post_type !== 'spring_news' ||
        $post->post_status !== 'publish' ||
        is_preview() ||
        is_user_logged_in()
    ) {
        return [];
    }

    return [
        'data-news-views-id' => (string) $post->ID,
        'data-news-views-url' => rest_url('springapex/v1/news/' . (int) $post->ID . '/view'),
        'data-news-views-one' => __('%s view', 'springapex'),
        'data-news-views-other' => __('%s views', 'springapex'),
    ];
}

add_action('rest_api_init', static function (): void {
    register_rest_route('springapex/v1', '/news/(?P<id>\d+)/view', [
        'methods' => WP_REST_Server::CREATABLE,
        'callback' => 'springapex_rest_count_news_view',
        // 公开接口：访客没有账号。能做的只有给已发布新闻 +1，且受上面的规则约束。
        'permission_callback' => '__return_true',
        'args' => [
            'id' => ['type' => 'integer', 'required' => true, 'minimum' => 1],
            // false = 只取最新合计（同一浏览器 24 小时内再次打开时）。
            'count' => ['type' => 'boolean', 'default' => true],
        ],
    ]);
});

function springapex_rest_count_news_view(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $post_id = (int) $request['id'];
    $post = get_post($post_id);
    if (!($post instanceof WP_Post) || $post->post_type !== 'spring_news' || $post->post_status !== 'publish') {
        return new WP_Error('springapex_news_not_found', 'News item not found.', ['status' => 404]);
    }

    $counted = (bool) $request['count']
        && springapex_news_view_countable($post_id)
        && springapex_news_view_increment($post_id);

    // 只回合计，不回基准数和真实阅读各是多少。
    $response = new WP_REST_Response([
        'total' => springapex_news_views_total($post_id),
        'counted' => $counted,
    ], 200);
    $response->header('Cache-Control', 'no-store');

    return $response;
}

/**
 * 在 MySQL 命名锁里执行 $callback；等不到锁（超时或出错）返回 false，调用方
 * 按「不计数」处理。锁在数据库服务器上，跨 PHP 进程有效，与对象缓存用什么
 * 后端无关。锁名是服务器级的，带上库名和表前缀，免得和同一台 MySQL 上的
 * 其他站点撞名（MySQL 限 64 字符）。
 */
function springapex_news_views_locked(string $name, int $timeout, callable $callback): bool
{
    global $wpdb;

    $lock = 'sa_nv:' . md5(DB_NAME . '|' . $wpdb->prefix . '|' . $name);
    if ((string) $wpdb->get_var($wpdb->prepare('SELECT GET_LOCK(%s, %d)', $lock, $timeout)) !== '1') {
        return false;
    }
    try {
        return (bool) $callback();
    } finally {
        $wpdb->query($wpdb->prepare('SELECT RELEASE_LOCK(%s)', $lock));
    }
}

function springapex_news_view_countable(int $post_id): bool
{
    // 前台请求不带 REST nonce，登录用户在这里本来就是匿名的；这一条挡的是
    // 用应用密码调接口的工具。
    if (is_user_logged_in()) {
        return false;
    }
    if (springapex_news_view_is_crawler((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''))) {
        return false;
    }

    // REMOTE_ADDR 已由 Nginx 按 CloudFront 回源地址还原成真实访客 IP
    //（deploy/springapex-cloudfront-real-ip-update）。只存 IP 的散列，10 分钟后过期。
    $ip = filter_var((string) ($_SERVER['REMOTE_ADDR'] ?? ''), FILTER_VALIDATE_IP);
    if (!is_string($ip) || $ip === '') {
        return true;
    }
    $key = 'sa_nv_' . substr(hash_hmac('sha256', $ip . '|' . $post_id, wp_salt('auth')), 0, 40);

    // 查和占必须在同一把锁里：同一 IP 同时开几个标签页（或脚本并发）时，
    // 只有第一个能占到这 10 分钟的名额。
    return springapex_news_views_locked('ip:' . $key, 3, static function () use ($key): bool {
        if (get_transient($key) !== false) {
            return false;
        }

        return set_transient($key, 1, SPRINGAPEX_NEWS_VIEWS_IP_WINDOW);
    });
}

/** 空 User-Agent、常见爬虫、链接预览、监控和脚本客户端都不算阅读。 */
function springapex_news_view_is_crawler(string $user_agent): bool
{
    $user_agent = trim($user_agent);
    if ($user_agent === '') {
        return true;
    }

    return (bool) preg_match(
        '/bot|crawl|spider|slurp|mediapartners|facebookexternalhit|embedly|preview|headless|phantomjs|lighthouse|pingdom|uptime|curl|wget|python|java\/|go-http-client|okhttp|axios|node-fetch|httpclient|scrapy/i',
        $user_agent
    );
}

/**
 * 真实阅读 +1。用一条 UPDATE 原子累加，不走「读出来加一再写回」，同时到达的
 * 两个请求不会互相覆盖。
 */
function springapex_news_view_increment(int $post_id): bool
{
    global $wpdb;

    $increment = $wpdb->prepare(
        "UPDATE {$wpdb->postmeta} SET meta_value = CAST(meta_value AS UNSIGNED) + 1 WHERE post_id = %d AND meta_key = %s",
        $post_id,
        SPRINGAPEX_NEWS_VIEWS_META
    );
    if ((int) $wpdb->query($increment) === 0) {
        // 第一次计数还没有这一行。postmeta 没有唯一约束，add_post_meta 的
        // 「唯一」只是先查再插，并发时会插出多行，所以建行放进锁里。
        $initialized = springapex_news_views_locked('init:' . $post_id, 5, static function () use ($wpdb, $increment, $post_id): bool {
            // 等锁期间别的请求可能已经建好了行，先再累加一次。
            if ((int) $wpdb->query($increment) > 0) {
                return true;
            }

            return (bool) add_post_meta($post_id, SPRINGAPEX_NEWS_VIEWS_META, 1, true);
        });
        if (!$initialized) {
            return false;
        }
    }
    wp_cache_delete($post_id, 'post_meta');

    return true;
}

// ---- 新闻列表：阅读数列（合计，下面小字拆开基准和真实）。

add_filter('manage_spring_news_posts_columns', static function (array $columns): array {
    $result = [];
    foreach ($columns as $key => $label) {
        if ($key === 'date') {
            $result['springapex_views'] = '阅读数';
        }
        $result[$key] = $label;
    }
    if (!isset($result['springapex_views'])) {
        $result['springapex_views'] = '阅读数';
    }

    return $result;
});

add_action('manage_spring_news_posts_custom_column', static function (string $column, int $post_id): void {
    if ($column !== 'springapex_views') {
        return;
    }
    $base = springapex_news_views_base($post_id);
    $real = springapex_news_views_real($post_id);
    echo esc_html(number_format_i18n($base + $real));
    echo '<br><span class="description">' . esc_html(sprintf(
        '基准 %s + 真实 %s',
        number_format_i18n($base),
        number_format_i18n($real)
    )) . '</span>';
}, 10, 2);
