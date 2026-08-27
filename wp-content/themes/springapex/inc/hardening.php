<?php
/**
 * 站点加固：关闭 XML-RPC 及其发现面。
 *
 * 本站没有任何远程发布/移动端管理需求，XML-RPC 反而是爆破流量最偏好的
 * 入口（system.multicall 可在单请求里塞入大量凭据猜测）。防线分两层：
 *
 * - Nginx 层：deploy/nginx-norenspring.com.conf 对 /xmlrpc.php 直接 404，
 *   请求根本到不了 PHP。该文件需手动安装到 BT Panel vhost。
 * - 本文件：主题层再关一次（xmlrpc_enabled=false），当 vhost 被宝塔面板
 *   升级覆写、或请求经其他入口直达 PHP 时兜底；两层任一存活即安全。
 *
 * 同步移除对 xmlrpc 的发现面——X-Pingback 响应头与 RSD/WLW manifest 输出，
 * 避免向扫描器继续广播一个已不存在的端点。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_filter('xmlrpc_enabled', '__return_false');

add_filter('wp_headers', static function (array $headers): array {
    unset($headers['X-Pingback']);
    return $headers;
});

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');
