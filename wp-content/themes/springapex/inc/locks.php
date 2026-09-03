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

/**
 * option 的 compare-and-swap 写入：只有当库里存的仍是 $expected_value 时才写进
 * $value，期间被别人改过就返回 false，由调用方重读重算。给 get_option() 读、
 * 改、再 update_option() 写这种流程用 —— 那中间的空档足够让另一个请求（或后台
 * 保存）的结果被整份旧快照顶掉。
 *
 * 和取一把 TTL 锁相比，这里没有需要释放的状态：进程死在中间也不留痕迹。
 */
function springapex_update_option_if_unchanged(string $option_name, mixed $expected_value, mixed $value): bool
{
    global $wpdb;

    if (!is_object($wpdb) || !is_string($wpdb->options ?? null) || $wpdb->options === '') {
        return false;
    }

    $updated = $wpdb->update(
        $wpdb->options,
        ['option_value' => maybe_serialize($value)],
        [
            'option_name' => $option_name,
            'option_value' => maybe_serialize($expected_value),
        ],
        ['%s'],
        ['%s', '%s']
    );

    if ($updated !== 1) {
        return false;
    }

    // 绕开了 update_option()，缓存得自己打掉：autoload 的 option 存在 alloptions
    // 里，只删单键的话同一请求后面读到的还是旧值。
    wp_cache_delete($option_name, 'options');
    wp_cache_delete('alloptions', 'options');
    return true;
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
