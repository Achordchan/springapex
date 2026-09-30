<?php
/**
 * 根目录 /favicon.ico：直接输出主题自带的多尺寸 ico（16/32/48，Logo 纯图案白底版）。
 *
 * Nginx 需把 /favicon.ico 交给 WordPress（见 deploy/nginx-norenspring.com.conf），
 * 否则静态规则直接 404。主题文件缺失时退回 WordPress 默认的跳转到站点图标。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_favicon_file(): string
{
    return SPRINGAPEX_DIR . '/favicon.ico';
}

function springapex_serve_favicon(): void
{
    $file = springapex_favicon_file();
    if (!is_readable($file)) {
        return;
    }

    status_header(200);
    header('Content-Type: image/x-icon');
    header('Content-Length: ' . (string) filesize($file));
    header('Cache-Control: public, max-age=604800');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'HEAD') {
        readfile($file);
    }
    exit;
}
add_action('do_favicon', 'springapex_serve_favicon', 1);
