<?php
/**
 * 允许 CloudFront 短时缓存未登录访客看到的网页。
 *
 * 整站分配原先用托管的 CachingDisabled 策略（见 deploy/README.md），每次打开网页都要
 * 回到美东的源站现做；越南、东南亚的手机访客因此 TTFB 实测 2.9 秒（CrUX，2026-10）。
 * 本文件只负责「哪些回应可以缓存、缓存多久」，由主题在回应头里声明：
 *
 *     Cache-Control: public, max-age=0, s-maxage=600
 *
 * max-age=0 让浏览器每次仍向 CloudFront 要最新版，s-maxage 只对 CloudFront 生效。
 * CloudFront 那边要换成「遵从源站缓存头」的自定义缓存策略后，这个头才起作用；
 * 在那之前它对线上没有任何影响（CachingDisabled 会忽略它）。
 *
 * 只给明确安全的回应加：GET/HEAD、200、未登录、没有密码文章/评论者 Cookie、
 * 回应本身没有 Set-Cookie、也没有别的代码先声明过 Cache-Control（下载私有附件、
 * 404 等 WP 会自己调用 nocache_headers()）。表单里的 nonce 对未登录访客 12–24 小时
 * 内有效，started_at 由 main.js 在页面打开时重写，缓存 10 分钟不影响提交。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** CloudFront 缓存网页的秒数。发布或修改内容后，最多这么久所有访客都能看到。 */
const SPRINGAPEX_EDGE_CACHE_TTL = 600;

/**
 * 请求层面能否缓存：在模板输出之前判断。
 *
 * @param array{method:string,logged_in:bool,preview:bool,password_required:bool,cookies:array<string,mixed>} $request
 */
function springapex_edge_cache_request_allowed(array $request): bool
{
    if (!in_array(strtoupper($request['method']), ['GET', 'HEAD'], true)) {
        return false;
    }
    if ($request['logged_in'] || $request['preview'] || $request['password_required']) {
        return false;
    }
    foreach (array_keys($request['cookies']) as $name) {
        // 输入过密码的受保护文章、留过评论的访客，看到的页面因人而异。
        if (str_starts_with((string) $name, 'wp-postpass_') || str_starts_with((string) $name, 'comment_author_')) {
            return false;
        }
    }

    return true;
}

/**
 * 回应层面要输出的 Cache-Control；空串表示不加。
 *
 * @param list<string> $headers headers_list() 的结果
 */
function springapex_edge_cache_header(int $status, array $headers): string
{
    if ($status !== 200) {
        return '';
    }
    foreach ($headers as $header) {
        $name = strtolower(trim((string) strstr($header, ':', true)));
        if ($name === 'set-cookie' || $name === 'cache-control') {
            return '';
        }
    }

    return 'Cache-Control: public, max-age=0, s-maxage=' . SPRINGAPEX_EDGE_CACHE_TTL;
}

// 尽量晚：让主题和插件自己的 template_redirect（跳转、下载、nocache_headers）先跑完。
add_action('template_redirect', static function (): void {
    if (is_admin() || wp_doing_ajax() || is_robots() || is_trackback()) {
        return;
    }
    $allowed = springapex_edge_cache_request_allowed([
        'method' => (string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'),
        'logged_in' => is_user_logged_in(),
        'preview' => is_preview(),
        'password_required' => is_singular() && post_password_required(),
        'cookies' => $_COOKIE,
    ]);
    if (!$allowed) {
        return;
    }

    // 回应头在第一次输出时才发出：到那时再看状态码和已有的头，模板渲染过程中
    // 设置的 Cookie 或 404 也能被识别。
    header_register_callback(static function (): void {
        $header = springapex_edge_cache_header((int) http_response_code(), headers_list());
        if ($header !== '') {
            header($header);
        }
    });
}, PHP_INT_MAX);
