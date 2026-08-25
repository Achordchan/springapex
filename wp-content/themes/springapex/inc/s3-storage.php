<?php
/**
 * Private S3 storage backed by the EC2 instance profile.
 *
 * No long-lived AWS key is accepted here. Production obtains short-lived
 * credentials from IMDSv2 and signs S3 requests with AWS Signature Version 4.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_s3_private_storage_enabled(): bool
{
    return defined('SPRINGAPEX_S3_BUCKET')
        && is_string(SPRINGAPEX_S3_BUCKET)
        && SPRINGAPEX_S3_BUCKET !== '';
}

function springapex_s3_bucket(): string
{
    return springapex_s3_private_storage_enabled() ? (string) SPRINGAPEX_S3_BUCKET : '';
}

function springapex_s3_region(): string
{
    if (defined('SPRINGAPEX_S3_REGION') && is_string(SPRINGAPEX_S3_REGION) && SPRINGAPEX_S3_REGION !== '') {
        return (string) SPRINGAPEX_S3_REGION;
    }
    return 'us-east-1';
}

function springapex_s3_private_prefix(): string
{
    $prefix = defined('SPRINGAPEX_S3_PRIVATE_PREFIX') && is_string(SPRINGAPEX_S3_PRIVATE_PREFIX)
        ? (string) SPRINGAPEX_S3_PRIVATE_PREFIX
        : 'private/inquiries';
    return trim($prefix, '/');
}

/** @return array{access_key:string,secret_key:string,token:string}|WP_Error */
function springapex_s3_instance_credentials(): array|WP_Error
{
    static $credentials = null;
    if (is_array($credentials)) {
        return $credentials;
    }

    $token_response = wp_remote_request('http://169.254.169.254/latest/api/token', [
        'method' => 'PUT',
        'timeout' => 2,
        'redirection' => 0,
        'headers' => ['X-aws-ec2-metadata-token-ttl-seconds' => '21600'],
    ]);
    if (is_wp_error($token_response) || wp_remote_retrieve_response_code($token_response) !== 200) {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $token = trim((string) wp_remote_retrieve_body($token_response));
    if ($token === '') {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $metadata_args = [
        'timeout' => 2,
        'redirection' => 0,
        'headers' => ['X-aws-ec2-metadata-token' => $token],
    ];
    $role_response = wp_remote_get(
        'http://169.254.169.254/latest/meta-data/iam/security-credentials/',
        $metadata_args
    );
    if (is_wp_error($role_response) || wp_remote_retrieve_response_code($role_response) !== 200) {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $role = trim((string) wp_remote_retrieve_body($role_response));
    if ($role === '' || str_contains($role, '/') || str_contains($role, "\n")) {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $credential_response = wp_remote_get(
        'http://169.254.169.254/latest/meta-data/iam/security-credentials/' . rawurlencode($role),
        $metadata_args
    );
    if (is_wp_error($credential_response) || wp_remote_retrieve_response_code($credential_response) !== 200) {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $payload = json_decode((string) wp_remote_retrieve_body($credential_response), true);
    if (
        !is_array($payload)
        || !is_string($payload['AccessKeyId'] ?? null)
        || !is_string($payload['SecretAccessKey'] ?? null)
        || !is_string($payload['Token'] ?? null)
        || $payload['AccessKeyId'] === ''
        || $payload['SecretAccessKey'] === ''
        || $payload['Token'] === ''
    ) {
        return new WP_Error('springapex_s3_credentials', 'EC2 instance credentials are unavailable.');
    }

    $credentials = [
        'access_key' => $payload['AccessKeyId'],
        'secret_key' => $payload['SecretAccessKey'],
        'token' => $payload['Token'],
    ];
    return $credentials;
}

function springapex_s3_encoded_key(string $key): string
{
    return implode('/', array_map('rawurlencode', explode('/', trim($key, '/'))));
}

/** @return array{response:array|WP_Error,payload_hash:string}|WP_Error */
function springapex_s3_signed_request(
    string $method,
    string $bucket,
    string $region,
    string $key,
    string $body = '',
    string $content_type = ''
): array|WP_Error {
    $credentials = springapex_s3_instance_credentials();
    if (is_wp_error($credentials)) {
        return $credentials;
    }

    $method = strtoupper($method);
    $service = 's3';
    $host = $bucket . '.s3.' . $region . '.amazonaws.com';
    $canonical_uri = '/' . springapex_s3_encoded_key($key);
    $amz_date = gmdate('Ymd\THis\Z');
    $date_stamp = substr($amz_date, 0, 8);
    $payload_hash = hash('sha256', $body);
    $headers = [
        'host' => $host,
        'x-amz-content-sha256' => $payload_hash,
        'x-amz-date' => $amz_date,
        'x-amz-security-token' => $credentials['token'],
    ];
    if ($content_type !== '') {
        $headers['content-type'] = $content_type;
    }
    if ($method === 'PUT') {
        $headers['x-amz-server-side-encryption'] = 'AES256';
    }
    ksort($headers);

    $canonical_headers = '';
    foreach ($headers as $name => $value) {
        $canonical_headers .= $name . ':' . preg_replace('/\s+/', ' ', trim((string) $value)) . "\n";
    }
    $signed_headers = implode(';', array_keys($headers));
    $canonical_request = implode("\n", [
        $method,
        $canonical_uri,
        '',
        $canonical_headers,
        $signed_headers,
        $payload_hash,
    ]);
    $algorithm = 'AWS4-HMAC-SHA256';
    $credential_scope = $date_stamp . '/' . $region . '/' . $service . '/aws4_request';
    $string_to_sign = implode("\n", [
        $algorithm,
        $amz_date,
        $credential_scope,
        hash('sha256', $canonical_request),
    ]);
    $date_key = hash_hmac('sha256', $date_stamp, 'AWS4' . $credentials['secret_key'], true);
    $region_key = hash_hmac('sha256', $region, $date_key, true);
    $service_key = hash_hmac('sha256', $service, $region_key, true);
    $signing_key = hash_hmac('sha256', 'aws4_request', $service_key, true);
    $signature = hash_hmac('sha256', $string_to_sign, $signing_key);
    $headers['authorization'] = $algorithm
        . ' Credential=' . $credentials['access_key'] . '/' . $credential_scope
        . ', SignedHeaders=' . $signed_headers
        . ', Signature=' . $signature;

    $request_args = [
        'method' => $method,
        'timeout' => 60,
        'redirection' => 0,
        'headers' => $headers,
    ];
    if ($method === 'PUT') {
        $request_args['body'] = $body;
    }
    $response = wp_remote_request('https://' . $host . $canonical_uri, $request_args);
    return ['response' => $response, 'payload_hash' => $payload_hash];
}

/** @return array<string, mixed>|WP_Error */
function springapex_s3_store_private_file(
    string $path,
    string $original_name,
    string $mime,
    string $sha256
): array|WP_Error {
    $body = file_get_contents($path);
    if (!is_string($body) || $body === '') {
        return new WP_Error('springapex_s3_upload', 'The private file could not be read.');
    }
    $extension = strtolower((string) pathinfo($original_name, PATHINFO_EXTENSION));
    $name = wp_generate_password(40, false, false) . ($extension !== '' ? '.' . $extension : '');
    $key = springapex_s3_private_prefix() . '/' . gmdate('Y/m') . '/' . $name;
    $bucket = springapex_s3_bucket();
    $region = springapex_s3_region();
    $result = springapex_s3_signed_request('PUT', $bucket, $region, $key, $body, $mime);
    if (is_wp_error($result)) {
        return $result;
    }
    $response = $result['response'];
    if (is_wp_error($response) || !in_array(wp_remote_retrieve_response_code($response), [200, 201], true)) {
        return new WP_Error('springapex_s3_upload', 'The private file could not be stored.');
    }
    return [
        'storage' => 's3',
        'bucket' => $bucket,
        'region' => $region,
        'key' => $key,
        'original_name' => $original_name,
        'mime' => $mime,
        'size' => strlen($body),
        'sha256' => $sha256,
        '_temporary_path' => $path,
    ];
}

function springapex_s3_delete_private_file(array $metadata): bool
{
    if (($metadata['storage'] ?? '') !== 's3') {
        return false;
    }
    $bucket = is_string($metadata['bucket'] ?? null) ? $metadata['bucket'] : '';
    $region = is_string($metadata['region'] ?? null) ? $metadata['region'] : '';
    $key = is_string($metadata['key'] ?? null) ? $metadata['key'] : '';
    if ($bucket === '' || $region === '' || $key === '') {
        return false;
    }
    $result = springapex_s3_signed_request('DELETE', $bucket, $region, $key);
    if (is_wp_error($result) || is_wp_error($result['response'])) {
        return false;
    }
    return in_array(wp_remote_retrieve_response_code($result['response']), [200, 204, 404], true);
}

function springapex_s3_download_private_file(array $metadata): string|WP_Error
{
    $bucket = is_string($metadata['bucket'] ?? null) ? $metadata['bucket'] : '';
    $region = is_string($metadata['region'] ?? null) ? $metadata['region'] : '';
    $key = is_string($metadata['key'] ?? null) ? $metadata['key'] : '';
    if (($metadata['storage'] ?? '') !== 's3' || $bucket === '' || $region === '' || $key === '') {
        return new WP_Error('springapex_s3_download', 'The private file is unavailable.');
    }
    $result = springapex_s3_signed_request('GET', $bucket, $region, $key);
    if (is_wp_error($result) || is_wp_error($result['response'])) {
        return new WP_Error('springapex_s3_download', 'The private file is unavailable.');
    }
    $response = $result['response'];
    if (wp_remote_retrieve_response_code($response) !== 200) {
        return new WP_Error('springapex_s3_download', 'The private file is unavailable.');
    }
    $body = (string) wp_remote_retrieve_body($response);
    $expected_size = (int) ($metadata['size'] ?? 0);
    $expected_hash = is_string($metadata['sha256'] ?? null) ? $metadata['sha256'] : '';
    if (
        $body === ''
        || ($expected_size > 0 && strlen($body) !== $expected_size)
        || ($expected_hash !== '' && !hash_equals($expected_hash, hash('sha256', $body)))
    ) {
        return new WP_Error('springapex_s3_download', 'The private file failed integrity validation.');
    }
    $temp = wp_tempnam((string) ($metadata['original_name'] ?? 'drawing'));
    if (!is_string($temp) || $temp === '') {
        return new WP_Error('springapex_s3_download', 'The private file is unavailable.');
    }
    $written = file_put_contents($temp, $body, LOCK_EX);
    if ($written !== strlen($body)) {
        @unlink($temp);
        return new WP_Error('springapex_s3_download', 'The private file failed local integrity validation.');
    }
    @chmod($temp, 0600);
    return $temp;
}
