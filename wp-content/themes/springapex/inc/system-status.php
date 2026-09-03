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

/**
 * 一条附件元数据是否指向一个真实存储的文件，按其 storage 类型判定：
 * S3 文件看 key，本地文件看 relative_path。用来剔除空/半截记录，也让
 * 旧版单文件里 S3 形态的条目（有 key 而无 relative_path）不被漏算。
 *
 * @param array<string, mixed> $file
 */
function springapex_system_status_is_stored_file(array $file): bool
{
    if (($file['storage'] ?? '') === 's3') {
        return is_string($file['key'] ?? null) && $file['key'] !== '';
    }
    return is_string($file['relative_path'] ?? null) && $file['relative_path'] !== '';
}

/**
 * 询盘附件在 S3/本地的存储占用统计（跨全部询盘，含回收站）。
 *
 * 「系统与存储」页用它把存储摊开：存了多少个文件、多大、其中多少在 S3（按量
 * 计费的部分），以及多少还躺在回收站里等待永久删除（仍在计费）。附件元数据是
 * 本主题自己写入的序列化数组，每个文件带 size（字节）与 storage（s3/本地）。
 *
 * 先按「询盘」取上限、再取这些询盘的两种 meta —— 不能在 JOIN 后的 meta 行上
 * 直接 LIMIT，否则同一询盘的新旧两行可能被截断边界劈开、错配或漏配。结果缓存
 * 5 分钟，运行连接检测时清掉重算。
 *
 * @return array{files:int,bytes:int,s3_files:int,s3_bytes:int,inquiries:int,trashed_files:int,trashed_bytes:int,trashed_inquiries:int,generated_at:int,truncated:bool}
 */
function springapex_system_status_attachment_footprint(): array
{
    $empty = [
        'files' => 0,
        'bytes' => 0,
        's3_files' => 0,
        's3_bytes' => 0,
        'inquiries' => 0,
        'trashed_files' => 0,
        'trashed_bytes' => 0,
        'trashed_inquiries' => 0,
        'generated_at' => time(),
        'truncated' => false,
    ];

    $cached = get_transient('springapex_attachment_footprint_v2');
    if (is_array($cached)) {
        return array_merge($empty, $cached);
    }

    global $wpdb;
    if (!isset($wpdb->posts, $wpdb->postmeta) || !is_string($wpdb->posts) || !is_string($wpdb->postmeta)) {
        return $empty;
    }

    // 第一步：先在「询盘」层面取上限（有附件的询盘远少于询盘总数）。超过上限才
    // 标记为下限统计——这样截断的单位是询盘，与页面「前 N 封」的措辞一致，也不会
    // 把某封询盘的两条 meta 拆到边界两侧。
    $limit = 20000;
    $inquiry_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, p.post_status
         FROM {$wpdb->posts} p
         WHERE p.post_type = 'spring_inquiry'
           AND EXISTS (
             SELECT 1 FROM {$wpdb->postmeta} pm
             WHERE pm.post_id = p.ID
               AND pm.meta_key IN ('_springapex_private_files', '_springapex_private_file')
           )
         ORDER BY p.ID ASC
         LIMIT %d",
        $limit + 1
    ));
    $inquiry_rows = is_array($inquiry_rows) ? $inquiry_rows : [];

    $result = $empty;
    $result['truncated'] = count($inquiry_rows) > $limit;
    if ($result['truncated']) {
        $inquiry_rows = array_slice($inquiry_rows, 0, $limit);
    }

    $status_by_id = [];
    foreach ($inquiry_rows as $row) {
        if (is_object($row) && (int) ($row->ID ?? 0) > 0) {
            $status_by_id[(int) $row->ID] = (string) ($row->post_status ?? '');
        }
    }
    if ($status_by_id === []) {
        $result['generated_at'] = time();
        set_transient('springapex_attachment_footprint_v2', $result, 5 * MINUTE_IN_SECONDS);
        return $result;
    }

    // 第二步：只取这些询盘的两种 meta —— 同一询盘的新旧两行必定一起取到，不会被
    // 截断劈开。
    $ids = array_keys($status_by_id);
    $placeholders = implode(',', array_fill(0, count($ids), '%d'));
    $meta_rows = $wpdb->get_results($wpdb->prepare(
        "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta}
         WHERE meta_key IN ('_springapex_private_files', '_springapex_private_file')
           AND post_id IN ($placeholders)",
        ...$ids
    ));
    $meta_rows = is_array($meta_rows) ? $meta_rows : [];

    // 同一询盘可能同时有新旧两个 meta：优先新版 _private_files、回退旧版单文件，
    // 与 springapex_inquiry_private_files() 的取值一致，避免重复计数。
    $by_post = [];
    foreach ($meta_rows as $row) {
        if (!is_object($row)) {
            continue;
        }
        $id = (int) ($row->post_id ?? 0);
        $key = (string) ($row->meta_key ?? '');
        if ($id < 1 || $key === '') {
            continue;
        }
        if (!isset($by_post[$id])) {
            $by_post[$id] = ['files' => null, 'legacy' => null];
        }
        $value = (string) ($row->meta_value ?? '');
        // 自有可信数据，但仍禁止实例化对象，纯取数组。
        $data = is_serialized($value) ? unserialize($value, ['allowed_classes' => false]) : $value;
        if ($key === '_springapex_private_files') {
            $by_post[$id]['files'] = is_array($data) ? $data : [];
        } else {
            $by_post[$id]['legacy'] = is_array($data) ? $data : null;
        }
    }

    foreach ($status_by_id as $id => $status) {
        $entry = $by_post[$id] ?? ['files' => null, 'legacy' => null];
        $files = is_array($entry['files']) ? array_values(array_filter($entry['files'], 'is_array')) : [];
        if ($files === [] && is_array($entry['legacy'])) {
            $files = [$entry['legacy']];
        }
        // 按 storage 类型剔除空/半截记录（S3 看 key、本地看 relative_path）。
        $files = array_values(array_filter($files, 'springapex_system_status_is_stored_file'));
        if ($files === []) {
            continue;
        }
        $trashed = $status === 'trash';
        $result['inquiries']++;
        if ($trashed) {
            $result['trashed_inquiries']++;
        }
        foreach ($files as $file) {
            $bytes = max(0, (int) ($file['size'] ?? 0));
            $is_s3 = ($file['storage'] ?? '') === 's3';
            $result['files']++;
            $result['bytes'] += $bytes;
            if ($is_s3) {
                $result['s3_files']++;
                $result['s3_bytes'] += $bytes;
            }
            if ($trashed) {
                $result['trashed_files']++;
                $result['trashed_bytes'] += $bytes;
            }
        }
    }

    $result['generated_at'] = time();
    set_transient('springapex_attachment_footprint_v2', $result, 5 * MINUTE_IN_SECONDS);
    return $result;
}

function springapex_system_status_queue_s3_probe_cleanup(
    string $bucket,
    string $region,
    string $key,
    string $payload
): bool {
    if (!function_exists('springapex_queue_s3_delete_retry')) {
        return false;
    }
    springapex_queue_s3_delete_retry([
        'storage' => 's3',
        'bucket' => $bucket,
        'region' => $region,
        'key' => $key,
        'original_name' => 'springapex-health-probe.txt',
        'mime' => 'text/plain',
        'size' => strlen($payload),
        'sha256' => hash('sha256', $payload),
    ]);
    return true;
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
        'storage' => springapex_system_status_attachment_footprint(),
        'trash' => [
            // 询盘被删除后先进回收站，多少天后自动永久删除（届时才清理 S3）。
            'empty_days' => defined('EMPTY_TRASH_DAYS') ? (int) EMPTY_TRASH_DAYS : 30,
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
        $cleanup = springapex_s3_signed_request('DELETE', $bucket, $region, $key);
        $cleanup_code = !is_wp_error($cleanup) && !is_wp_error($cleanup['response'] ?? null)
            ? wp_remote_retrieve_response_code($cleanup['response'])
            : 0;
        $cleanup_queued = !in_array($cleanup_code, [200, 204, 404], true)
            && springapex_system_status_queue_s3_probe_cleanup($bucket, $region, $key, $payload);
        $error_code = is_wp_error($put) ? $put->get_error_code() : 's3_request_failed';
        return [
            'state' => 'error',
            'label' => '连接失败',
            'message' => '无法取得 EC2 临时凭据或访问 S3（' . sanitize_key((string) $error_code) . '）。',
            'details' => array_filter([
                '耗时' => (int) round((microtime(true) - $started) * 1000) . ' ms',
                '清理队列' => $cleanup_queued ? '已加入删除重试' : '',
            ]),
        ];
    }
    $put_code = wp_remote_retrieve_response_code($put['response']);
    if (!in_array($put_code, [200, 201], true)) {
        $cleanup = springapex_s3_signed_request('DELETE', $bucket, $region, $key);
        $cleanup_code = !is_wp_error($cleanup) && !is_wp_error($cleanup['response'] ?? null)
            ? wp_remote_retrieve_response_code($cleanup['response'])
            : 0;
        $cleanup_queued = !in_array($cleanup_code, [200, 204, 404], true)
            && springapex_system_status_queue_s3_probe_cleanup($bucket, $region, $key, $payload);
        return [
            'state' => 'error',
            'label' => '写入失败',
            'message' => 'S3 返回 HTTP ' . $put_code . '。',
            'details' => array_filter([
                '耗时' => (int) round((microtime(true) - $started) * 1000) . ' ms',
                '清理队列' => $cleanup_queued ? '已加入删除重试' : '',
            ]),
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
    $cleanup_queued = !$delete_ok
        && springapex_system_status_queue_s3_probe_cleanup($bucket, $region, $key, $payload);
    $duration = (int) round((microtime(true) - $started) * 1000);

    if (!$read_ok || !$delete_ok) {
        return [
            'state' => 'error',
            'label' => '校验失败',
            'message' => !$read_ok ? '测试对象未能完整读取。' : '测试对象未能确认删除。',
            'details' => array_filter([
                '耗时' => $duration . ' ms',
                '删除响应' => $delete_code ?: '请求失败',
                '清理队列' => $cleanup_queued ? '已加入删除重试' : '',
            ]),
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
        'storage' => $snapshot['storage'] ?? [],
        'trash' => $snapshot['trash'] ?? [],
        'last_probe' => $probe,
    ];
}
