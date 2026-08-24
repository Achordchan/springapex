<?php
/**
 * Schema-driven sanitizers for the website-content admin screens.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Hidden per-row key carrying the row's index in the stored content, so a
 * rejected field can fall back to its own original value after reordering.
 * Never stored: it is stripped from the sanitized output.
 */
const SPRINGAPEX_ADMIN_ROW_ORIGIN = '__row';

function springapex_admin_add_warning(array &$warnings, string $message): void
{
    if (count($warnings) < 40 && !in_array($message, $warnings, true)) {
        $warnings[] = $message;
    }
}

function springapex_admin_reject(array &$warnings, string $label, string $reason): array
{
    springapex_admin_add_warning($warnings, $label . $reason);
    return ['accepted' => false, 'value' => null];
}

function springapex_admin_scalar(mixed $raw, string $label, array &$warnings): ?string
{
    if (!is_scalar($raw)) {
        springapex_admin_add_warning($warnings, $label . '的数据格式不正确，没有保存。');
        return null;
    }
    return (string) $raw;
}

function springapex_admin_youtube_id(string $value): string
{
    $value = trim($value);
    if (preg_match('/^[A-Za-z0-9_-]{11}$/', $value)) {
        return $value;
    }

    $parts = wp_parse_url($value);
    if (!is_array($parts)) {
        return '';
    }
    $host = strtolower((string) ($parts['host'] ?? ''));
    $host = preg_replace('/^www\./', '', $host) ?? $host;
    $candidate = '';

    if ($host === 'youtu.be') {
        $candidate = explode('/', trim((string) ($parts['path'] ?? ''), '/'))[0] ?? '';
    } elseif (in_array($host, ['youtube.com', 'm.youtube.com', 'youtube-nocookie.com'], true)) {
        parse_str((string) ($parts['query'] ?? ''), $query);
        $candidate = is_scalar($query['v'] ?? null) ? (string) $query['v'] : '';
        if ($candidate === '') {
            $segments = explode('/', trim((string) ($parts['path'] ?? ''), '/'));
            if (in_array($segments[0] ?? '', ['embed', 'shorts', 'live'], true)) {
                $candidate = (string) ($segments[1] ?? '');
            }
        }
    }

    return preg_match('/^[A-Za-z0-9_-]{11}$/', $candidate) ? $candidate : '';
}

function springapex_admin_sanitize_image(mixed $raw, string $label, array &$warnings, string $base = 'assets/images/'): array
{
    $value = springapex_admin_scalar($raw, $label, $warnings);
    if ($value === null) {
        return ['accepted' => false, 'value' => null];
    }
    $value = trim($value);
    if ($value === '') {
        return ['accepted' => true, 'value' => ''];
    }
    if (ctype_digit($value) && (int) $value > 0) {
        return ['accepted' => true, 'value' => (int) $value];
    }

    $valid_file = !str_contains($value, '..')
        && !str_starts_with($value, '/')
        && !str_contains($value, '\\')
        && !str_contains($value, '://')
        && !preg_match('/[\x00-\x1F\x7F]/', $value)
        && preg_match('/\.(?:jpe?g|png|webp|avif|svg)$/i', $value);

    if (!$valid_file) {
        return springapex_admin_reject($warnings, $label, '不是有效的媒体库图片或主题图片路径，已保留原内容。');
    }
    // A theme filename must actually exist, or the front end renders a broken
    // image. Bare filenames resolve under the field's base folder.
    if (!is_file(springapex_asset_path($base . ltrim($value, '/')))) {
        return springapex_admin_reject($warnings, $label, '指向的主题图片不存在，已保留原内容。请用「选择图片」从媒体库挑一张。');
    }
    return ['accepted' => true, 'value' => $value];
}

function springapex_admin_sanitize_repeater(
    array $field,
    mixed $raw,
    mixed $current,
    string $label,
    array &$warnings
): array {
    if ($raw === '') {
        return ['accepted' => true, 'value' => []];
    }
    if (!is_array($raw)) {
        return springapex_admin_reject($warnings, $label, '的数据格式不正确，没有保存。');
    }

    $max_items = max(0, (int) ($field['max_items'] ?? 0));
    if ($max_items > 0 && count($raw) > $max_items) {
        springapex_admin_add_warning($warnings, $label . '最多只能保存 ' . $max_items . ' 项，超出的内容没有保存。');
        $raw = array_slice($raw, 0, $max_items);
    }

    $subfields = [];
    foreach ((array) ($field['fields'] ?? []) as $subfield) {
        $key = (string) ($subfield['path'] ?? '');
        if ($key !== '') {
            $subfields[$key] = $subfield;
        }
    }

    $current_rows = is_array($current) ? array_values($current) : [];
    $clean_rows = [];
    foreach (array_values($raw) as $index => $row) {
        $row_label = $label . '第 ' . ($index + 1) . ' 项';
        if (!is_array($row)) {
            springapex_admin_add_warning($warnings, $row_label . '的数据格式不正确，整项没有保存。');
            continue;
        }

        foreach (array_keys($row) as $key) {
            if ((string) $key !== SPRINGAPEX_ADMIN_ROW_ORIGIN && !isset($subfields[(string) $key])) {
                springapex_admin_add_warning($warnings, $row_label . '包含未声明字段「' . (string) $key . '」，该字段没有保存。');
            }
        }

        // Match by the row's origin marker, not by its current position: rows
        // can be reordered or deleted, and falling back positionally would
        // silently pull another row's value into this one.
        $origin = $row[SPRINGAPEX_ADMIN_ROW_ORIGIN] ?? null;
        $current_row = [];
        if (is_scalar($origin) && ctype_digit((string) $origin) && is_array($current_rows[(int) $origin] ?? null)) {
            $current_row = $current_rows[(int) $origin];
        }
        $clean_row = [];
        foreach ($subfields as $key => $subfield) {
            if (!array_key_exists($key, $row)) {
                continue;
            }
            $field_label = $row_label . ' → ' . (string) ($subfield['label'] ?? $key);
            $result = springapex_admin_sanitize_field(
                $subfield,
                $row[$key],
                $current_row[$key] ?? null,
                $field_label,
                $warnings
            );
            if ($result['accepted']) {
                $clean_row[$key] = $result['value'];
            } elseif (array_key_exists($key, $current_row)) {
                $clean_row[$key] = $current_row[$key];
            }
        }
        $clean_rows[] = $clean_row;
    }

    return ['accepted' => true, 'value' => array_values($clean_rows)];
}

function springapex_admin_sanitize_field(
    array $field,
    mixed $raw,
    mixed $current,
    string $label,
    array &$warnings
): array {
    $type = (string) ($field['type'] ?? 'text');
    if ($type === 'repeater') {
        return springapex_admin_sanitize_repeater($field, $raw, $current, $label, $warnings);
    }
    if ($type === 'image') {
        $result = springapex_admin_sanitize_image($raw, $label, $warnings, springapex_admin_image_base($field));
        if (!empty($field['required']) && $result['accepted'] && $result['value'] === '') {
            return springapex_admin_reject($warnings, $label, '是固定版式必需图片，不能清空，已保留原内容。');
        }
        return $result;
    }

    $value = springapex_admin_scalar($raw, $label, $warnings);
    if ($value === null) {
        return ['accepted' => false, 'value' => null];
    }

    switch ($type) {
        case 'text':
            return ['accepted' => true, 'value' => sanitize_text_field($value)];

        case 'textarea':
            return ['accepted' => true, 'value' => sanitize_textarea_field($value)];

        case 'icon':
            $icon = sanitize_text_field($value);
            if ($icon !== '' && isset(springapex_icon_map()[$icon])) {
                return ['accepted' => true, 'value' => $icon];
            }
            return springapex_admin_reject($warnings, $label, '不是可用图标，没有保存。');

        case 'url':
            $url = trim($value);
            if ($url === '') {
                return ['accepted' => true, 'value' => ''];
            }
            $url = esc_url_raw($url, ['http', 'https']);
            $parsed_host = wp_parse_url($url, PHP_URL_HOST);
            $host = is_string($parsed_host) ? $parsed_host : '';
            if ($url === '' || !preg_match('#^https?://#i', $url) || $host === '') {
                return springapex_admin_reject($warnings, $label, '不是有效的 http/https 网址，没有保存。');
            }
            return ['accepted' => true, 'value' => $url];

        case 'email':
            $email = sanitize_email($value);
            if (trim($value) !== '' && ($email === '' || !is_email($email))) {
                return springapex_admin_reject($warnings, $label, '格式不对，没有保存，其他修改已保存。');
            }
            return ['accepted' => true, 'value' => $email];

        case 'tel':
            $phone = preg_replace('/[^0-9 +\-()]/', '', $value) ?? '';
            if ($phone !== $value) {
                springapex_admin_add_warning($warnings, $label . '含有不支持的字符，已自动移除。');
            }
            return ['accepted' => true, 'value' => trim($phone)];

        case 'youtube':
            if (trim($value) === '') {
                return ['accepted' => true, 'value' => ''];
            }
            $youtube_id = springapex_admin_youtube_id($value);
            if ($youtube_id === '') {
                return springapex_admin_reject($warnings, $label, '不是有效的 YouTube 视频 ID 或网址，没有保存。');
            }
            return ['accepted' => true, 'value' => $youtube_id];

        case 'route':
            $route = trim($value);
            if ($route === '') {
                return ['accepted' => true, 'value' => ''];
            }
            if (in_array($route, springapex_admin_route_values(), true)) {
                return ['accepted' => true, 'value' => $route];
            }
            // Not in the list, but unchanged from what is stored: keep it, so a
            // legacy custom path survives edits to other fields. Never accept a
            // brand-new hand-entered path.
            if (is_string($current) && $route === trim($current)) {
                return ['accepted' => true, 'value' => $route];
            }
            return springapex_admin_reject($warnings, $label, '请从下拉里选择一个目的地，没有保存。');

        case 'lines':
            $lines = [];
            foreach (preg_split('/\R/u', $value) ?: [] as $line) {
                $line = sanitize_text_field($line);
                if ($line !== '') {
                    $lines[] = $line;
                }
            }
            return ['accepted' => true, 'value' => array_values($lines)];

        default:
            return springapex_admin_reject($warnings, $label, '使用了未支持的字段类型，没有保存。');
    }
}
