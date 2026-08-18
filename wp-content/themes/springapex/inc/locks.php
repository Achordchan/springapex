<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_acquire_option_lock(string $option_name, int $ttl): string
{
    if ($option_name === '' || $ttl < 1) {
        return '';
    }

    $now = time();
    $token = wp_generate_uuid4();
    $record = [
        'token' => $token,
        'started_at' => $now,
    ];

    if (add_option($option_name, $record, '', false)) {
        return $token;
    }

    $existing = get_option($option_name, null);
    if ($existing === null) {
        return add_option($option_name, $record, '', false) ? $token : '';
    }

    $started_at = is_array($existing) ? (int) ($existing['started_at'] ?? 0) : 0;
    if ($started_at > 0 && ($now - $started_at) <= $ttl) {
        return '';
    }

    if (!springapex_delete_option_if_unchanged($option_name, $existing)) {
        return '';
    }

    return add_option($option_name, $record, '', false) ? $token : '';
}

function springapex_release_option_lock(string $option_name, string $token): void
{
    if ($option_name === '' || $token === '') {
        return;
    }

    $record = get_option($option_name, null);
    if (
        !is_array($record) ||
        !is_string($record['token'] ?? null) ||
        !hash_equals($record['token'], $token)
    ) {
        return;
    }

    springapex_delete_option_if_unchanged($option_name, $record);
}

function springapex_delete_option_if_unchanged(string $option_name, mixed $expected_value): bool
{
    global $wpdb;

    if (!is_object($wpdb) || !is_string($wpdb->options ?? null) || $wpdb->options === '') {
        return false;
    }

    $deleted = $wpdb->delete(
        $wpdb->options,
        [
            'option_name' => $option_name,
            'option_value' => maybe_serialize($expected_value),
        ],
        ['%s', '%s']
    );

    if ($deleted !== 1) {
        return false;
    }

    wp_cache_delete($option_name, 'options');
    return true;
}
