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
