<?php
/** Focused S3 storage flow test with a stubbed WordPress HTTP boundary. */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('SPRINGAPEX_S3_BUCKET', 'example-private-bucket');
define('SPRINGAPEX_S3_REGION', 'us-east-1');
define('SPRINGAPEX_S3_PRIVATE_PREFIX', 'private/inquiries');

final class WP_Error
{
    public function __construct(public string $code, public string $message)
    {
    }
}

function is_wp_error(mixed $value): bool
{
    return $value instanceof WP_Error;
}

/** @var array<int, array<string, mixed>> */
$springapex_test_delete_retries = [];

/** @param array<string, mixed> $metadata */
function springapex_queue_s3_delete_retry(array $metadata): void
{
    global $springapex_test_delete_retries;
    $springapex_test_delete_retries[] = $metadata;
}

/** @return array{response:array{code:int},body:string}|WP_Error */
function wp_remote_request(string $url, array $args = []): array|WP_Error
{
    static $stored_body = '';
    if ($url === 'http://169.254.169.254/latest/api/token') {
        return ['response' => ['code' => 200], 'body' => 'imds-token'];
    }
    if ($url === 'http://169.254.169.254/latest/meta-data/iam/security-credentials/') {
        return ['response' => ['code' => 200], 'body' => 'NorenSpringRole'];
    }
    if (str_ends_with($url, '/iam/security-credentials/NorenSpringRole')) {
        return [
            'response' => ['code' => 200],
            'body' => json_encode([
                'AccessKeyId' => 'ASIATEST',
                'SecretAccessKey' => 'test-secret',
                'Token' => 'test-session-token',
            ], JSON_THROW_ON_ERROR),
        ];
    }

    $method = strtoupper((string) ($args['method'] ?? 'GET'));
    $headers = $args['headers'] ?? [];
    if (
        !str_starts_with($url, 'https://example-private-bucket.s3.us-east-1.amazonaws.com/private/inquiries/')
        || !str_contains((string) ($headers['authorization'] ?? ''), 'Credential=ASIATEST/')
        || ($headers['x-amz-security-token'] ?? '') !== 'test-session-token'
    ) {
        return ['response' => ['code' => 403], 'body' => ''];
    }
    if ($method === 'PUT') {
        if (($headers['x-amz-server-side-encryption'] ?? '') !== 'AES256') {
            return ['response' => ['code' => 400], 'body' => ''];
        }
        $stored_body = (string) ($args['body'] ?? '');
        if (!empty($GLOBALS['springapex_test_ambiguous_put'])) {
            return new WP_Error('http_request_failed', 'The response was lost.');
        }
        return ['response' => ['code' => 200], 'body' => ''];
    }
    if ($method === 'GET') {
        return ['response' => ['code' => $stored_body !== '' ? 200 : 404], 'body' => $stored_body];
    }
    if ($method === 'DELETE') {
        $stored_body = '';
        return ['response' => ['code' => 204], 'body' => ''];
    }
    return ['response' => ['code' => 405], 'body' => ''];
}

function wp_remote_get(string $url, array $args = []): array
{
    $args['method'] = 'GET';
    return wp_remote_request($url, $args);
}

function wp_remote_retrieve_response_code(array $response): int
{
    return (int) ($response['response']['code'] ?? 0);
}

function wp_remote_retrieve_body(array $response): string
{
    return (string) ($response['body'] ?? '');
}

function wp_generate_password(int $length): string
{
    return str_repeat('a', $length);
}

function wp_tempnam(string $filename = ''): string
{
    return (string) tempnam(sys_get_temp_dir(), 'springapex-s3-');
}

require dirname(__DIR__) . '/inc/s3-storage.php';
require dirname(__DIR__) . '/inc/system-status.php';

$source = (string) tempnam(sys_get_temp_dir(), 'springapex-source-');
$contents = 'private-drawing-test';
file_put_contents($source, $contents);
$metadata = springapex_s3_store_private_file(
    $source,
    'drawing.pdf',
    'application/pdf',
    hash('sha256', $contents)
);
if (is_wp_error($metadata) || ($metadata['storage'] ?? '') !== 's3') {
    throw new RuntimeException('S3 upload flow failed.');
}
$download = springapex_s3_download_private_file($metadata);
if (is_wp_error($download) || file_get_contents($download) !== $contents) {
    throw new RuntimeException('S3 download integrity flow failed.');
}
if (!springapex_s3_delete_private_file($metadata)) {
    throw new RuntimeException('S3 delete flow failed.');
}

$GLOBALS['springapex_test_ambiguous_put'] = true;
$ambiguous = springapex_s3_store_private_file(
    $source,
    'drawing.pdf',
    'application/pdf',
    hash('sha256', $contents)
);
$GLOBALS['springapex_test_ambiguous_put'] = false;
if (!is_wp_error($ambiguous) || count($springapex_test_delete_retries) !== 1) {
    throw new RuntimeException('Ambiguous S3 upload was not queued for cleanup.');
}
if (!springapex_s3_delete_private_file($springapex_test_delete_retries[0])) {
    throw new RuntimeException('Ambiguous S3 upload cleanup failed.');
}

$probe_payload = 'springapex-health-test';
if (!springapex_system_status_queue_s3_probe_cleanup(
    'example-private-bucket',
    'us-east-1',
    'private/inquiries/.health/probe.txt',
    $probe_payload
)) {
    throw new RuntimeException('S3 health probe cleanup was not queued.');
}
$probe_cleanup = $springapex_test_delete_retries[array_key_last($springapex_test_delete_retries)] ?? [];
if (
    ($probe_cleanup['storage'] ?? '') !== 's3'
    || ($probe_cleanup['bucket'] ?? '') !== 'example-private-bucket'
    || ($probe_cleanup['region'] ?? '') !== 'us-east-1'
    || ($probe_cleanup['key'] ?? '') !== 'private/inquiries/.health/probe.txt'
    || (int) ($probe_cleanup['size'] ?? 0) !== strlen($probe_payload)
    || ($probe_cleanup['sha256'] ?? '') !== hash('sha256', $probe_payload)
) {
    throw new RuntimeException('S3 health probe cleanup metadata is incomplete.');
}
@unlink($source);
@unlink($download);
echo "s3-storage: signed upload/download/delete and queued cleanup flows ok\n";
