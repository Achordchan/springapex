<?php
/** Brochure upload associations, legacy files and public rendering regressions. */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit(1);
}
define('ABSPATH', __DIR__ . '/');
define('SPRINGAPEX_DIR', dirname(__DIR__));
define('SPRINGAPEX_URI', 'https://example.test/theme');
define('SPRINGAPEX_ADMIN_SLUG', 'springapex-content');

$attachments = [
    101 => ['type' => 'attachment', 'mime' => 'application/pdf', 'status' => 'inherit', 'url' => 'https://example.test/uploads/About%20NorenSpring.pdf', 'bytes' => 2097152],
    102 => ['type' => 'attachment', 'mime' => 'image/png', 'status' => 'inherit', 'url' => 'https://example.test/uploads/cover.png'],
    103 => ['type' => 'attachment', 'mime' => 'application/pdf', 'status' => 'trash', 'url' => 'https://example.test/uploads/deleted.pdf'],
    104 => ['type' => 'attachment', 'mime' => 'application/pdf', 'status' => 'private', 'url' => 'https://example.test/uploads/private.pdf'],
    105 => ['type' => 'attachment', 'mime' => 'application/pdf', 'status' => 'inherit', 'url' => false],
    106 => ['type' => 'page', 'mime' => 'application/pdf', 'status' => 'publish', 'url' => 'https://example.test/uploads/not-an-attachment.pdf'],
];
function get_post_type(int $id): string|false { return $GLOBALS['attachments'][$id]['type'] ?? false; }
function get_post_mime_type(int $id): string|false { return $GLOBALS['attachments'][$id]['mime'] ?? false; }
function get_post_status(int $id): string|false { return $GLOBALS['attachments'][$id]['status'] ?? false; }
function wp_get_attachment_url(int $id): string|false { return $GLOBALS['attachments'][$id]['url'] ?? false; }
function wp_get_attachment_metadata(int $id): array { return ['filesize' => $GLOBALS['attachments'][$id]['bytes'] ?? 0]; }
function get_attached_file(int $id): false { return false; }
function wp_get_attachment_image_url(int $id, string $size): false { return false; }
function sanitize_text_field(string $value): string { return trim(strip_tags($value)); }
function sanitize_textarea_field(string $value): string { return trim(strip_tags($value)); }
function esc_html(mixed $value): string { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function esc_attr(mixed $value): string { return esc_html($value); }
function esc_url(mixed $value): string { return esc_html($value); }
function __(string $value, string $domain = ''): string { return $value; }
function esc_html_e(string $value, string $domain = ''): void { echo esc_html($value); }
function esc_attr_e(string $value, string $domain = ''): void { echo esc_attr($value); }
function springapex_url(string $value): string { return $value; }
function get_template_part(string $slug, mixed $name = null, array $args = []): void {}
function springapex_get(string $path, mixed $default = null): mixed
{
    $value = $GLOBALS['content_fixture'];
    foreach (explode('.', $path) as $key) {
        if (!is_array($value) || !array_key_exists($key, $value)) {
            return $default;
        }
        $value = $value[$key];
    }
    return $value;
}
require SPRINGAPEX_DIR . '/inc/helpers.php';
require SPRINGAPEX_DIR . '/inc/admin/schema.php';
require SPRINGAPEX_DIR . '/inc/admin/sanitize.php';
require SPRINGAPEX_DIR . '/inc/admin/render.php';

$checks = 0;
function verify(bool $condition, string $message): void
{
    $GLOBALS['checks']++;
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

foreach ([101, '101', 'norenspring-company-profile.pdf', 'https://cdn.example.test/manual.pdf?version=2', 'https://example.test/download?file=catalog.pdf', 'https://example.test/media/123?token=abc'] as $value) {
    $warnings = [];
    $result = springapex_admin_sanitize_field(['type' => 'pdf'], $value, '', 'PDF', $warnings);
    verify($result['accepted'] && $warnings === [], 'Valid PDF rejected: ' . (string) $value);
    verify(springapex_download_document_url($result['value']) !== '', 'Saved PDF does not resolve');
}
foreach ([0, true, false, 101.0, 102, 103, 104, 105, 106, 999, ['id' => 101], '../private.pdf', '/private.pdf', 'missing.pdf', 'javascript:alert(1)', 'https://', 'https://invalid host.test/catalog.pdf', 'ftp://example.test/catalog.pdf'] as $value) {
    $warnings = [];
    $result = springapex_admin_sanitize_field(['type' => 'pdf'], $value, 101, 'PDF', $warnings);
    verify(!$result['accepted'] && $warnings !== [], 'Invalid PDF accepted');
    verify(springapex_download_document_url($value) === '', 'Invalid PDF exposed on front end');
}
$warnings = [];
$result = springapex_admin_sanitize_field(['type' => 'pdf'], '', 101, 'PDF', $warnings);
verify($result['accepted'] && $result['value'] === '', 'Removal must clear the association');
verify(springapex_download_document_size(101) === '2.0 MB', 'Actual upload size not used');
verify(springapex_download_document_size('norenspring-company-profile.pdf') !== '', 'Bundled size not available');

$schema = springapex_admin_screens()['resources']['sections'][1]['fields'][3];
$current = [
    ['id' => 'company-downloads', 'category' => 'Company', 'title' => 'Company profile', 'description' => '', 'cover' => '', 'document' => 101, 'pages' => '', 'size' => '5.0 MB'],
    ['id' => 'product-downloads', 'category' => 'Products', 'title' => 'Product catalog', 'description' => '', 'cover' => '', 'document' => 'norenspring-product-catalog.pdf', 'pages' => '', 'size' => ''],
];
$rows = [array_merge($current[1], ['__row' => '1', 'document' => '102']), array_merge($current[0], ['__row' => '0'])];
$warnings = [];
$result = springapex_admin_sanitize_field($schema, $rows, $current, 'Downloads', $warnings);
verify($result['accepted'] && count($result['value']) === 2, 'Rows without a cover must save');
verify($result['value'][0]['document'] === 'norenspring-product-catalog.pdf', 'Rejected replacement lost the original after reordering');
verify($result['value'][1]['document'] === 101 && $result['value'][1]['cover'] === '', 'Uploaded PDF association did not survive save');

$GLOBALS['content_fixture'] = ['resources' => ['downloads' => $result['value'], 'library' => [], 'industry' => []]];
ob_start();
require SPRINGAPEX_DIR . '/templates/resources.php';
$html = ob_get_clean();
verify(substr_count($html, '<article class="sa-download-volume"') === 2, 'Coverless brochures disappeared');
verify(substr_count($html, 'class="sa-download-volume__placeholder"') === 2, 'Missing covers need a fallback');
verify(str_contains($html, 'About%20NorenSpring.pdf') && str_contains($html, '2.0 MB'), 'Wrong download link or stale file size rendered');
verify(!str_contains($html, '<li>5.0 MB</li>'), 'Old manual size must not override upload size');

$GLOBALS['content_fixture']['resources']['downloads'] = [array_merge($current[0], ['document' => 103])];
ob_start();
require SPRINGAPEX_DIR . '/templates/resources.php';
$html = ob_get_clean();
verify(!str_contains($html, '<article class="sa-download-volume"'), 'Deleted PDFs must not expose broken download links');
verify(str_contains($html, 'No brochures are currently available'), 'Empty library needs a message');

ob_start();
springapex_admin_render_pdf_field('document', 'document', 101);
$html = ob_get_clean();
verify(str_contains($html, 'About NorenSpring.pdf') && str_contains($html, 'value="101"'), 'Admin must show the selected PDF filename and ID');
verify(str_contains($html, '更换 PDF') && str_contains($html, '移除关联'), 'Admin replacement controls missing');
ob_start();
springapex_admin_render_pdf_field('document', 'document', 'About NorenSpring.pdf');
$html = ob_get_clean();
verify(str_contains($html, '当前文件不可用'), 'Bad legacy filename needs an actionable warning');

echo "download-center: {$checks} checks passed\n";
