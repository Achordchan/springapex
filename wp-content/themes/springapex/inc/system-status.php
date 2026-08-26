<?php
/**
 * Infrastructure status and safe connectivity probes.
 *
 * This file never accepts AWS credentials or changes infrastructure settings.
 * Configuration authority remains wp-config.php, the EC2 role and deploy tools.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** Mask an infrastructure identifier without pretending it is a secret. */
function springapex_system_status_mask_identifier(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '—';
    }
    if (strlen($value) <= 10) {
        return substr($value, 0, 2) . '••••';
    }
    return substr($value, 0, 6) . '••••' . substr($value, -4);
}

/** @return array{imagick:bool,webp:bool,avif:bool} */
function springapex_system_status_image_formats(): array
{
    $imagick = extension_loaded('imagick') && class_exists('Imagick');
    $webp = false;
    $avif = false;
    if ($imagick) {
        try {
            $webp = Imagick::queryFormats('WEBP') !== [];
            $avif = Imagick::queryFormats('AVIF') !== [];
        } catch (Throwable) {
            $webp = false;
            $avif = false;
        }
    }
    return compact('imagick', 'webp', 'avif');
}

function springapex_system_status_s3_retry_count(): int
{
    global $wpdb;
    if (!isset($wpdb->options) || !is_string($wpdb->options)) {
        return 0;
    }
    $prefix = 'springapex_s3_delete_retry_v1_';
    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like($prefix) . '%'
    ));
    return max(0, (int) $count);
}

/** @return array<string, mixed> */
function springapex_system_status_snapshot(): array
{
    $environment = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';
    $site_host = strtolower((string) wp_parse_url(home_url('/'), PHP_URL_HOST));
    $loopback = in_array($site_host, ['127.0.0.1', 'localhost', '::1'], true)
        || str_ends_with($site_host, '.local')
        || str_ends_with($site_host, '.test');
    // WordPress 未定义 WP_ENVIRONMENT_TYPE 时默认返回 production；本地回环
    // 站点不能因此被误报为“生产缺少 S3/CDN”。
    if ($environment === 'production' && $loopback) {
        $environment = 'local';
    }
    $s3_enabled = springapex_s3_private_storage_enabled();
    $cdn_url = defined('SPRINGAPEX_CDN_URL') && is_string(SPRINGAPEX_CDN_URL)
        ? rtrim((string) SPRINGAPEX_CDN_URL, '/')
        : '';
    $private_local = defined('SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED')
        && SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED === true;
    $images = springapex_system_status_image_formats();
    $upload_max_raw = (string) ini_get('upload_max_filesize');
    $post_max_raw = (string) ini_get('post_max_size');
    $upload_max_bytes = wp_convert_hr_to_bytes($upload_max_raw);
    $post_max_bytes = wp_convert_hr_to_bytes($post_max_raw);
    $next_retry = wp_next_scheduled('springapex_retry_s3_deletions');

    return [
        'environment' => [
            'type' => $environment,
            'site_host' => $site_host,
            'wordpress' => get_bloginfo('version'),
            'php' => PHP_VERSION,
            'theme' => defined('SPRINGAPEX_VERSION') ? SPRINGAPEX_VERSION : '',
        ],
        's3' => [
            'enabled' => $s3_enabled,
            'bucket' => $s3_enabled ? springapex_system_status_mask_identifier(springapex_s3_bucket()) : '—',
            'region' => $s3_enabled ? springapex_s3_region() : '—',
            'prefix' => $s3_enabled ? springapex_s3_private_prefix() : '—',
            'credentials' => 'EC2 Instance Profile / IMDSv2',
            'retry_count' => springapex_system_status_s3_retry_count(),
            'next_retry' => is_int($next_retry) ? $next_retry : 0,
        ],
        'cdn' => [
            'enabled' => $cdn_url !== '',
            'host' => $cdn_url !== '' ? (string) wp_parse_url($cdn_url, PHP_URL_HOST) : '—',
            'asset_base' => $cdn_url !== '' ? $cdn_url . '/theme/' . SPRINGAPEX_VERSION : '—',
            'versioned' => $cdn_url !== '' && str_contains(SPRINGAPEX_URI, '/theme/' . SPRINGAPEX_VERSION),
        ],
        'private_uploads' => [
            'enabled' => $s3_enabled || $private_local,
            'mode' => $s3_enabled ? 'S3 私有对象' : ($private_local ? '本地受保护目录' : '未启用'),
        ],
        'images' => $images,
        'uploads' => [
            'upload_max' => $upload_max_raw,
            'post_max' => $post_max_raw,
            'wordpress_max' => wp_max_upload_size(),
            'meets_recommendation' => $upload_max_bytes >= 10 * MB_IN_BYTES
                && $post_max_bytes >= 12 * MB_IN_BYTES,
        ],
        'operations' => [
            'deployment' => 'GitHub Actions + 服务器受限部署命令',
            'backup' => '宝塔定时任务 + S3 备份脚本',
            'status_feed' => '尚未接入服务器任务结果回传',
        ],
    ];
}

/** @return array{state:string,label:string,message:string,details:array<string,string|int>} */
function springapex_system_status_s3_probe(): array
{
    if (!springapex_s3_private_storage_enabled()) {
        return [
            'state' => 'neutral',
            'label' => '未执行',
            'message' => '当前实例未配置 S3 私有存储。',
            'details' => [],
        ];
    }

    $bucket = springapex_s3_bucket();
    $region = springapex_s3_region();
    $key = springapex_s3_private_prefix()
        . '/.health/'
        . substr(hash('sha256', home_url('/')), 0, 12)
        . '-'
        . gmdate('YmdHis')
        . '-'
        . strtolower(wp_generate_password(8, false, false))
        . '.txt';
    $payload = 'springapex-health-' . wp_generate_uuid4();
    $started = microtime(true);

    $put = springapex_s3_signed_request('PUT', $bucket, $region, $key, $payload, 'text/plain');
    if (is_wp_error($put) || is_wp_error($put['response'] ?? null)) {
        springapex_s3_signed_request('DELETE', $bucket, $region, $key);
        $error_code = is_wp_error($put) ? $put->get_error_code() : 's3_request_failed';
        return [
            'state' => 'error',
            'label' => '连接失败',
            'message' => '无法取得 EC2 临时凭据或访问 S3（' . sanitize_key((string) $error_code) . '）。',
            'details' => ['耗时' => (int) round((microtime(true) - $started) * 1000) . ' ms'],
        ];
    }
    $put_code = wp_remote_retrieve_response_code($put['response']);
    if (!in_array($put_code, [200, 201], true)) {
        springapex_s3_signed_request('DELETE', $bucket, $region, $key);
        return [
            'state' => 'error',
            'label' => '写入失败',
            'message' => 'S3 返回 HTTP ' . $put_code . '。',
            'details' => ['耗时' => (int) round((microtime(true) - $started) * 1000) . ' ms'],
        ];
    }

    $get = springapex_s3_signed_request('GET', $bucket, $region, $key);
    $read_ok = !is_wp_error($get)
        && !is_wp_error($get['response'] ?? null)
        && wp_remote_retrieve_response_code($get['response']) === 200
        && hash_equals($payload, (string) wp_remote_retrieve_body($get['response']));
    $delete = springapex_s3_signed_request('DELETE', $bucket, $region, $key);
    $delete_code = !is_wp_error($delete) && !is_wp_error($delete['response'] ?? null)
        ? wp_remote_retrieve_response_code($delete['response'])
        : 0;
    $delete_ok = in_array($delete_code, [200, 204, 404], true);
    $duration = (int) round((microtime(true) - $started) * 1000);

    if (!$read_ok || !$delete_ok) {
        return [
            'state' => 'error',
            'label' => '校验失败',
            'message' => !$read_ok ? '测试对象未能完整读取。' : '测试对象未能确认删除。',
            'details' => ['耗时' => $duration . ' ms', '删除响应' => $delete_code ?: '请求失败'],
        ];
    }

    return [
        'state' => 'ok',
        'label' => '连接正常',
        'message' => '临时对象的写入、完整读取和删除均已通过。',
        'details' => [
            '存储桶' => springapex_system_status_mask_identifier($bucket),
            '区域' => $region,
            '耗时' => $duration . ' ms',
        ],
    ];
}

function springapex_system_status_response_header(array $response, string $name): string
{
    $value = wp_remote_retrieve_header($response, $name);
    if (is_array($value)) {
        $value = implode(', ', array_map('sanitize_text_field', $value));
    }
    return sanitize_text_field((string) $value);
}

function springapex_system_status_has_immutable_cache(string $cache_control): bool
{
    if (!str_contains(strtolower($cache_control), 'immutable')) {
        return false;
    }
    if (!preg_match('/(?:^|,)\s*max-age=(\d+)/i', $cache_control, $matches)) {
        return false;
    }
    return (int) ($matches[1] ?? 0) >= YEAR_IN_SECONDS;
}

/** @return array{state:string,label:string,message:string,details:array<string,string|int>} */
function springapex_system_status_cdn_probe(): array
{
    if (!defined('SPRINGAPEX_CDN_URL') || !is_string(SPRINGAPEX_CDN_URL) || SPRINGAPEX_CDN_URL === '') {
        return [
            'state' => 'neutral',
            'label' => '未执行',
            'message' => '当前实例未配置 CDN 地址。',
            'details' => [],
        ];
    }

    $url = rtrim(SPRINGAPEX_URI, '/') . '/assets/css/foundation.css';
    $started = microtime(true);
    $response = wp_remote_head($url, [
        'timeout' => 12,
        'redirection' => 3,
        'sslverify' => true,
    ]);
    $duration = (int) round((microtime(true) - $started) * 1000);
    if (is_wp_error($response)) {
        return [
            'state' => 'error',
            'label' => '连接失败',
            'message' => 'CDN 请求失败（' . sanitize_key((string) $response->get_error_code()) . '）。',
            'details' => ['耗时' => $duration . ' ms'],
        ];
    }

    $code = wp_remote_retrieve_response_code($response);
    $x_cache = springapex_system_status_response_header($response, 'x-cache');
    $pop = springapex_system_status_response_header($response, 'x-amz-cf-pop');
    $via = springapex_system_status_response_header($response, 'via');
    $cache_control = springapex_system_status_response_header($response, 'cache-control');
    $cloudfront = $x_cache !== '' || $pop !== '' || $via !== '';
    $versioned = str_contains($url, '/theme/' . SPRINGAPEX_VERSION . '/');
    $immutable_cache = springapex_system_status_has_immutable_cache($cache_control);
    $ok = $code === 200 && $cloudfront && $versioned && $immutable_cache;

    return [
        'state' => $ok ? 'ok' : ($code === 200 ? 'warning' : 'error'),
        'label' => $ok ? '连接正常' : ($code === 200 ? '需要检查' : '访问失败'),
        'message' => $ok
            ? '当前版本资源可访问，并检测到 CloudFront 与一年 immutable 缓存策略。'
            : ($code === 200 ? '资源可访问，但版本路径、CloudFront 响应头或长期缓存策略不完整。' : 'CDN 返回 HTTP ' . $code . '。'),
        'details' => array_filter([
            '资源地址' => $url,
            'HTTP' => $code,
            'Cache-Control' => $cache_control,
            'X-Cache' => $x_cache,
            'POP' => $pop,
            'Via' => $via,
            '耗时' => $duration . ' ms',
        ], static fn(string|int $value): bool => $value !== ''),
    ];
}

/** @return array<string, mixed> */
function springapex_system_status_diagnostic_report(array $snapshot, ?array $probe): array
{
    return [
        'generated_at' => gmdate('c'),
        'environment' => $snapshot['environment'],
        's3' => $snapshot['s3'],
        'cdn' => $snapshot['cdn'],
        'private_uploads' => $snapshot['private_uploads'],
        'images' => $snapshot['images'],
        'uploads' => $snapshot['uploads'],
        'operations' => $snapshot['operations'],
        'last_probe' => $probe,
    ];
}
