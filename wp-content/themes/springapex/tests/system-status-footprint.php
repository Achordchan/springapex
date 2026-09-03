<?php
/**
 * 询盘附件存储占用统计（「系统与存储」页展示用）。
 *
 * 关键点：同一询盘可能同时有新版 _springapex_private_files 与旧版单文件
 * _springapex_private_file——取值必须与 springapex_inquiry_private_files() 一致
 * （优先新版、回退旧版），否则会重复计数；回收站（post_status=trash）的附件要
 * 单独计出来，它们永久删除前仍在计费。
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

/** Minimal $wpdb double: returns whatever rows the test loaded. */
class Springapex_Test_Wpdb
{
    public string $posts = 'wp_posts';
    public string $postmeta = 'wp_postmeta';
    /** @var array<int, object> */
    public array $rows = [];

    public function prepare(string $query, mixed ...$args): string
    {
        return $query;
    }

    /** @return array<int, object> */
    public function get_results(string $query): array
    {
        return $this->rows;
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

/** @param array<string, mixed> $value */
function springapex_test_meta_row(int $id, string $status, string $key, array $value): object
{
    $row = new stdClass();
    $row->ID = $id;
    $row->post_status = $status;
    $row->meta_key = $key;
    $row->meta_value = serialize($value);
    return $row;
}

$GLOBALS['wpdb']->rows = [
    // 一封普通询盘，两个 S3 附件。
    springapex_test_meta_row(1, 'private', '_springapex_private_files', [
        ['size' => 1000, 'storage' => 's3'],
        ['size' => 2000, 'storage' => 's3'],
    ]),
    // 回收站里的询盘：单独计入 trashed_*。
    springapex_test_meta_row(2, 'trash', '_springapex_private_files', [
        ['size' => 500, 'storage' => 's3'],
    ]),
    // 旧版单文件（本地存储）。
    springapex_test_meta_row(3, 'private', '_springapex_private_file', [
        'relative_path' => 'x/y.pdf', 'size' => 400, 'storage' => 'local',
    ]),
    // 同一询盘同时有新旧两条 meta，且旧版排在前面：新版必须胜出，不能把
    // 旧版的 9999 也算进去（否则重复计数）。
    springapex_test_meta_row(4, 'private', '_springapex_private_file', [
        'relative_path' => 'z.pdf', 'size' => 9999, 'storage' => 'local',
    ]),
    springapex_test_meta_row(4, 'private', '_springapex_private_files', [
        ['size' => 100, 'storage' => 's3'],
    ]),
];

$fp = springapex_system_status_attachment_footprint();

springapex_test_assert($fp['files'] === 5, 'File count wrong: got ' . $fp['files']);
springapex_test_assert($fp['bytes'] === 3000 + 500 + 400 + 100, 'Byte total wrong: got ' . $fp['bytes']);
springapex_test_assert($fp['s3_files'] === 4, 'S3 object count wrong: got ' . $fp['s3_files']);
springapex_test_assert($fp['inquiries'] === 4, 'Inquiry-with-attachment count wrong: got ' . $fp['inquiries']);
springapex_test_assert($fp['trashed_files'] === 1, 'Trashed file count wrong: got ' . $fp['trashed_files']);
springapex_test_assert($fp['trashed_bytes'] === 500, 'Trashed byte total wrong: got ' . $fp['trashed_bytes']);
springapex_test_assert($fp['trashed_inquiries'] === 1, 'Trashed inquiry count wrong: got ' . $fp['trashed_inquiries']);
springapex_test_assert($fp['truncated'] === false, 'Small dataset must not be flagged truncated.');

// 没有任何附件行时返回全零，不报错。
$GLOBALS['wpdb']->rows = [];
$empty = springapex_system_status_attachment_footprint();
springapex_test_assert(
    $empty['files'] === 0 && $empty['bytes'] === 0 && $empty['inquiries'] === 0 && $empty['trashed_files'] === 0,
    'Empty dataset should produce an all-zero footprint.'
);

echo "system-status-footprint: totals, new-over-legacy dedup, trash split, s3 count, empty case ok\n";
