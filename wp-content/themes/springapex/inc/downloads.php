<?php
/** Public brochure files: Media Library PDFs plus legacy bundled documents. */
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_download_document_url(mixed $document): string
{
    if (!is_int($document) && !is_string($document)) {
        return '';
    }
    $value = trim((string) $document);
    if ($value === '') {
        return '';
    }

    if (ctype_digit($value)) {
        $id = (int) $value;
        if ($id < 1 || !function_exists('get_post_type') || !function_exists('wp_get_attachment_url')
            || get_post_type($id) !== 'attachment'
            || get_post_mime_type($id) !== 'application/pdf'
            || !in_array(get_post_status($id), ['inherit', 'publish'], true)) {
            return '';
        }
        return (string) wp_get_attachment_url($id);
    }

    // Preserve previously configured public PDF URLs without making a remote request.
    if (preg_match('#^https?://#i', $value)) {
        $path = parse_url($value, PHP_URL_PATH);
        return filter_var($value, FILTER_VALIDATE_URL) !== false && is_string($path)
            && preg_match('/\.pdf$/i', $path) ? $value : '';
    }

    if (str_contains($value, '..') || str_contains($value, '\\') || str_starts_with($value, '/')
        || str_contains($value, ':') || preg_match('/[\x00-\x1F\x7F]/', $value)
        || !preg_match('/\.pdf$/i', $value)) {
        return '';
    }
    return is_file(springapex_asset_path('assets/documents/' . $value))
        ? springapex_file_url($value, 'assets/documents') : '';
}

/** Prefer the uploaded file's actual size over an old manually entered label. */
function springapex_download_document_size(mixed $document): string
{
    if (springapex_download_document_url($document) === '') {
        return '';
    }
    $value = trim((string) $document);
    $bytes = 0;
    if (ctype_digit($value)) {
        $metadata = wp_get_attachment_metadata((int) $value);
        $bytes = is_array($metadata) ? (int) ($metadata['filesize'] ?? 0) : 0;
        $file = get_attached_file((int) $value);
    } else {
        $file = str_contains($value, '://') ? false : springapex_asset_path('assets/documents/' . $value);
    }
    if ($bytes <= 0 && is_string($file) && is_file($file)) {
        $bytes = (int) filesize($file);
    }
    if ($bytes <= 0) {
        return '';
    }
    return $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB'
        : number_format($bytes / 1024, 1) . ' KB';
}
