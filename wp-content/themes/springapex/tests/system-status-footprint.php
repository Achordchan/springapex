<?php
/**
 * 询盘附件存储占用统计（「系统与存储」页展示用）。
 *
 * 关键点：
 * - 先按「询盘」取上限、再取这些询盘的两种 meta（新版 _springapex_private_files
 *   与旧版单文件 _springapex_private_file），同一询盘的新旧两行必须一起处理、
 *   优先新版回退旧版，避免被截断劈开或重复计数。
 * - 每条记录按其 storage 类型判定是否真实存储：S3 看 key、本地看 relative_path，
 *   所以旧版单文件里 S3 形态（有 key 无 relative_path）也要算进去。
 * - S3 字节单独统计（成本只按 S3 体积算，本地文件不进 S3 账单）。
 * - 回收站（post_status=trash）的附件单独计出，永久删除前仍在计费。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('MINUTE_IN_SECONDS', 60);

function get_transient(string $key): mixed
{
    return false;
}

function set_transient(string $key, mixed $value, int $ttl): bool
{
    return true;
}

function is_serialized(mixed $value): bool
{
    return is_string($value) && (bool) preg_match('/^[aOsbidN]:/', $value);
}

/**
 * Minimal $wpdb double. Two queries run: one over wp_posts (inquiries), one over
 * wp_postmeta (their meta). Route each to its prepared row set by table name.
 */
class Springapex_Test_Wpdb
{
    public string $posts = 'wp_posts';
    public string $postmeta = 'wp_postmeta';
    /** @var array<int, object> */
    public array $inquiry_rows = [];
    /** @var array<int, object> */
    public array $meta_rows = [];

    public function prepare(string $query, mixed ...$args): string
    {
        return $query;
    }

    /** @return array<int, object> */
    public function get_results(string $query): array
    {
        // 第一步查询主表是 wp_posts（其 EXISTS 子查询里也含 wp_postmeta，故按主表
        // 判定）；第二步只查 wp_postmeta。
        if (str_contains($query, 'FROM wp_posts')) {
            return $this->inquiry_rows;
        }
        return $this->meta_rows;
    }
}

$GLOBALS['wpdb'] = new Springapex_Test_Wpdb();

require __DIR__ . '/../inc/system-status.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

function springapex_test_inquiry(int $id, string $status): object
{
    $row = new stdClass();
    $row->ID = $id;
    $row->post_status = $status;
    return $row;
}

/** @param array<string, mixed> $value */
function springapex_test_meta(int $post_id, string $key, array $value): object
{
    $row = new stdClass();
    $row->post_id = $post_id;
    $row->meta_key = $key;
    $row->meta_value = serialize($value);
    return $row;
}

$GLOBALS['wpdb']->inquiry_rows = [
    springapex_test_inquiry(1, 'private'),
    springapex_test_inquiry(2, 'trash'),
    springapex_test_inquiry(3, 'private'),
    springapex_test_inquiry(4, 'private'),
    springapex_test_inquiry(5, 'private'),
    springapex_test_inquiry(6, 'private'),
];
$GLOBALS['wpdb']->meta_rows = [
    // 普通询盘，两个 S3 附件。
    springapex_test_meta(1, '_springapex_private_files', [
        ['size' => 1000, 'storage' => 's3', 'key' => 'a'],
        ['size' => 2000, 'storage' => 's3', 'key' => 'b'],
    ]),
    // 回收站里的询盘：单独计入 trashed_*。
    springapex_test_meta(2, '_springapex_private_files', [
        ['size' => 500, 'storage' => 's3', 'key' => 'c'],
    ]),
    // 旧版单文件（本地）：进总量、不进 S3 账单。
    springapex_test_meta(3, '_springapex_private_file', [
        'relative_path' => 'x/y.pdf', 'size' => 400, 'storage' => 'local',
    ]),
    // 同一询盘同时有新旧两条 meta，旧版排在前：新版胜出，旧版 9999 不能算进去。
    springapex_test_meta(4, '_springapex_private_file', [
        'relative_path' => 'z.pdf', 'size' => 9999, 'storage' => 'local',
    ]),
    springapex_test_meta(4, '_springapex_private_files', [
        ['size' => 100, 'storage' => 's3', 'key' => 'd'],
    ]),
    // 旧版单文件但是 S3 形态（有 key、无 relative_path）：必须被算进来。
    springapex_test_meta(5, '_springapex_private_file', [
        'key' => 'legacy-s3', 'size' => 700, 'storage' => 's3',
    ]),
    // 半截/空记录要被剔除，只有最后一条有效。
    springapex_test_meta(6, '_springapex_private_files', [
        ['size' => 999, 'storage' => 's3', 'key' => ''],
        ['size' => 888, 'storage' => 'local', 'relative_path' => ''],
        ['size' => 50, 'storage' => 's3', 'key' => 'ok'],
    ]),
];

$fp = springapex_system_status_attachment_footprint();

springapex_test_assert($fp['files'] === 7, 'File count wrong: got ' . $fp['files']);
springapex_test_assert($fp['bytes'] === 3000 + 500 + 400 + 100 + 700 + 50, 'Byte total wrong: got ' . $fp['bytes']);
springapex_test_assert($fp['s3_files'] === 6, 'S3 object count wrong: got ' . $fp['s3_files']);
springapex_test_assert($fp['s3_bytes'] === 3000 + 500 + 100 + 700 + 50, 'S3 byte total wrong: got ' . $fp['s3_bytes']);
springapex_test_assert($fp['inquiries'] === 6, 'Inquiry-with-attachment count wrong: got ' . $fp['inquiries']);
springapex_test_assert($fp['trashed_files'] === 1, 'Trashed file count wrong: got ' . $fp['trashed_files']);
springapex_test_assert($fp['trashed_bytes'] === 500, 'Trashed byte total wrong: got ' . $fp['trashed_bytes']);
springapex_test_assert($fp['trashed_inquiries'] === 1, 'Trashed inquiry count wrong: got ' . $fp['trashed_inquiries']);
springapex_test_assert($fp['truncated'] === false, 'Small dataset must not be flagged truncated.');

// 没有任何有附件的询盘时返回全零，不报错，且不查第二步 meta。
$GLOBALS['wpdb']->inquiry_rows = [];
$GLOBALS['wpdb']->meta_rows = [];
$empty = springapex_system_status_attachment_footprint();
springapex_test_assert(
    $empty['files'] === 0 && $empty['bytes'] === 0 && $empty['s3_bytes'] === 0
        && $empty['inquiries'] === 0 && $empty['trashed_files'] === 0,
    'Empty dataset should produce an all-zero footprint.'
);

echo "system-status-footprint: totals, s3-bytes split, legacy s3-shape, invalid filter, trash split, empty case ok\n";
