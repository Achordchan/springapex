<?php
/**
 * 站点加固：彻底关闭 XML-RPC 及其发现面。
 *
 * 本站没有任何远程发布/移动端管理需求，XML-RPC 反而是爆破流量最偏好的
 * 入口（system.multicall 可在单请求里塞入大量凭据猜测）。防线分两层：
 *
 * - Nginx 层：deploy/nginx-norenspring.com.conf 对 /xmlrpc.php 直接 404，
 *   请求根本到不了 PHP。该文件需手动安装到 BT Panel vhost。
 * - 本文件：主题层独立完成同等关闭。注意 xmlrpc_enabled=false 只拦需要
 *   登录的方法，匿名的 pingback.ping 不受它影响——所以还要清空
 *   xmlrpc_methods（含 system.* 在内的全部方法），落到本层的请求对任何
 *   方法调用都只能得到「method not found」。两层任一存活即完整关闭。
 *
 * 同步移除对 xmlrpc 的发现面——X-Pingback 响应头与 RSD/WLW manifest 输出，
 * 避免向扫描器继续广播一个已不存在的端点。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// 需要登录的方法（wp.getUsersBlogs 等）。
add_filter('xmlrpc_enabled', '__return_false');

// 匿名可达的方法（pingback.ping 等）：清空方法表，任何 methodCall 都只能
// 得到 fault -32601 "requested method ... does not exist"。
add_filter('xmlrpc_methods', '__return_empty_array');

// IXR_Server::setCallbacks() 会在方法表之后无条件补回 system.multicall /
// listMethods / getCapabilities 三个自省方法（IXR/class-IXR-server.php:183），
// 过滤器摘不掉。既然本站立场是端点不存在，请求级直接 404，与 Nginx 层
// 表现完全一致；未命中（如 WP-CLI 或未来入口变化）时上面的方法表清空兜底。
add_action('init', static function (): void {
    if (defined('XMLRPC_REQUEST') && XMLRPC_REQUEST) {
        http_response_code(404);
        exit;
    }
});

add_filter('wp_headers', static function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

/*
 * 不暴露后台登录名。/?author=1 会被 WordPress 301 到 /author/achord/，作者
 * 归档、用户站点地图、REST /wp/v2/users 和 oEmbed 的 author_url 也都带着这个
 * slug——拿到它，爆破 wp-login.php 就只剩猜密码。本站新闻署名走独立的作者
 * 条目（inc/news-author.php），不用 WordPress 用户，所以这些出口全部关掉：
 *
 * - 作者归档（含 ?author=N）在 redirect_canonical（优先级 10）之前 301 到
 *   关于页，跳转目标里不再出现 slug；
 * - 站点地图去掉 users；
 * - 未登录时 REST 不提供用户列表和单个用户（后台编辑器已登录，不受影响）；
 * - oEmbed 响应去掉作者名和作者链接。
 */
add_action('template_redirect', static function (): void {
    if (is_admin() || !is_author()) {
        return;
    }
    wp_safe_redirect(home_url('/about/'), 301);
    exit;
}, 1);

add_filter('wp_sitemaps_add_provider', static function (mixed $provider, string $name): mixed {
    return $name === 'users' ? false : $provider;
}, 10, 2);

add_filter('rest_endpoints', static function (array $endpoints): array {
    if (is_user_logged_in()) {
        return $endpoints;
    }
    foreach (array_keys($endpoints) as $route) {
        if (str_starts_with((string) $route, '/wp/v2/users')) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

add_filter('oembed_response_data', static function (array $data): array {
    unset($data['author_name'], $data['author_url']);
    return $data;
});
