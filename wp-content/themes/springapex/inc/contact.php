<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_springapex_contact', 'springapex_handle_contact_ajax');
add_action('wp_ajax_nopriv_springapex_contact', 'springapex_handle_contact_ajax');
add_action('admin_post_springapex_contact', 'springapex_handle_contact_post');
add_action('admin_post_nopriv_springapex_contact', 'springapex_handle_contact_post');
add_action('admin_post_springapex_download_inquiry_file', 'springapex_download_inquiry_file');
add_action('admin_post_springapex_dismiss_contact_warning', 'springapex_dismiss_contact_warning');
add_action('admin_notices', 'springapex_contact_admin_notices');

function springapex_handle_contact_ajax(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        wp_send_json_error(['message' => __('Method not allowed.', 'springapex')], 405);
    }

    if (!springapex_verify_contact_form_identity()) {
        wp_send_json_error(['message' => __('The form session expired. Refresh the page and try again.', 'springapex')], 403);
    }

    $result = springapex_process_contact_submission();
    if (is_wp_error($result)) {
        wp_send_json_error(
            ['message' => $result->get_error_message()],
            springapex_contact_error_status($result)
        );
    }

    wp_send_json_success(['message' => $result['message']]);
}

function springapex_handle_contact_post(): void
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST') {
        springapex_redirect_contact_status('error');
    }

    if (!springapex_verify_contact_form_identity()) {
        wp_die(
            esc_html__('The form session expired. Return to the contact page and try again.', 'springapex'),
            esc_html__('Security check failed', 'springapex'),
            ['response' => 403]
        );
    }

    $result = springapex_process_contact_submission();
    if (is_wp_error($result)) {
        $status = match ($result->get_error_code()) {
            'springapex_invalid' => 'invalid',
            'springapex_turnstile' => 'captcha',
            'springapex_rate_limit' => 'rate',
            'springapex_upload_protection' => 'upload_unavailable',
            'springapex_upload', 'springapex_upload_size', 'springapex_upload_type', 'springapex_upload_storage' => 'upload',
            default => 'error',
        };
        springapex_redirect_contact_status($status);
    }

    // 提交成功统一落到 /success 落地页（含无 JS 回退），便于转化统计。
    // 邮件即便未即时发出（saved），询盘也已保存，对访客而言同样是成功。
    wp_safe_redirect(springapex_url('/success/'), 303);
    exit;
}

function springapex_redirect_contact_status(string $status): void
{
    $url = add_query_arg('contact_status', sanitize_key($status), springapex_url('/contact/')) . '#contact-form';
    wp_safe_redirect($url, 303);
    exit;
}

function springapex_process_contact_submission(): array|WP_Error
{
    $honeypot = springapex_request_scalar($_POST['website'] ?? '');
    $started_at = absint(springapex_request_scalar($_POST['started_at'] ?? '0'));
    if ($honeypot !== '' || $started_at < 1 || (time() - $started_at) < 2) {
        return springapex_contact_error('springapex_invalid', __('Unable to submit this request.', 'springapex'), 400);
    }

    // 表单上下文（quick / full / product …）映射到「表单设置」的配置键，
    // 必填与人机验证均按表单取值，与渲染侧同源。form_context 必须在此处先取，
    // 下面的 Turnstile 与 schema 校验都依赖它。
    $form_context = springapex_contact_form_context();
    $form_key = springapex_contact_form_key($form_context);
    if ($form_key === '' || !springapex_form_enabled($form_key)) {
        return springapex_contact_error(
            'springapex_invalid',
            __('This form is unavailable.', 'springapex'),
            400
        );
    }

    // Turnstile 按表单开关校验（springapex_form_turnstile_enabled 同时
    // 控制前台渲染；密钥未配置时全局禁用）。
    $turnstile = springapex_verify_turnstile($form_key);
    if (is_wp_error($turnstile)) {
        return $turnstile;
    }

    $name = springapex_limited_text($_POST['full_name'] ?? '', 120);
    $email = sanitize_email(springapex_request_scalar($_POST['email'] ?? ''));
    $company = springapex_limited_text($_POST['company'] ?? '', 160);
    $phone = springapex_limited_text($_POST['phone'] ?? '', 80);
    $country = springapex_limited_text($_POST['country'] ?? $_POST['region'] ?? '', 100);
    $type = springapex_limited_text($_POST['inquiry_type'] ?? '', 100);
    $message = springapex_limited_textarea($_POST['message'] ?? '', 5000);
    $wire_diameter = springapex_limited_text($_POST['wire_diameter'] ?? '', 80);
    $outside_diameter = springapex_limited_text($_POST['outside_diameter'] ?? '', 80);
    $free_length = springapex_limited_text($_POST['free_length'] ?? '', 80);
    $dimension_labels = [
        springapex_limited_text($_POST['dimension_label_1'] ?? 'Wire diameter', 80) ?: 'Wire diameter',
        springapex_limited_text($_POST['dimension_label_2'] ?? 'Outside diameter', 80) ?: 'Outside diameter',
        springapex_limited_text($_POST['dimension_label_3'] ?? 'Free length', 80) ?: 'Free length',
    ];
    $quantity = springapex_limited_text($_POST['quantity'] ?? '', 80);
    $material = springapex_limited_text($_POST['material'] ?? '', 120);
    $operating_environment = springapex_limited_text($_POST['operating_environment'] ?? '', 240);
    $intent = sanitize_key(springapex_request_scalar($_POST['intent'] ?? ''));
    $product = sanitize_title(springapex_request_scalar($_POST['product'] ?? ''));
    $industry = sanitize_title(springapex_request_scalar($_POST['industry'] ?? ''));
    // Source page: the hidden `source` field carries the queried page/post ID
    // where the form was rendered; the referer is a fallback for non-singular
    // pages or when the field is missing. Together they let admins tell which
    // page a generic form was submitted from.
    $source_id = absint(springapex_request_scalar($_POST['source'] ?? '0'));
    if ($source_id > 0 && get_post_status($source_id) === false) {
        $source_id = 0;
    }
    $source_url = esc_url_raw((string) wp_get_referer());
    // Canonical referring path, stored alongside the URL so the admin source
    // filter can match it exactly (no LIKE substring bleed between /products/
    // and /products/compression-springs/).
    $source_path = $source_url !== '' ? (string) (wp_parse_url($source_url, PHP_URL_PATH) ?: '') : '';
    $allowed_types = springapex_get('contact.inquiry_types', []);

    // Schema 字段：渲染名 springapex_field_{id} → 值按类型校验后收集。
    // 固定语义 id（name/email/message/phone/company/country）写回上方核心变量，
    // 保持询盘标题、通知抬头、详情专用列与列表过滤不变；其余（运营者新增的）
    // 自定义字段整包存 meta，询盘详情/邮件按 label 动态展示。
    $schema = springapex_form_schema();
    $schema_fields = $schema[$form_key]['fields'] ?? [];
    $custom_fields = [];
    $schema_missing = [];
    foreach ($schema_fields as $field) {
        $trimmed = springapex_request_scalar($_POST['springapex_field_' . $field['id']] ?? '');
        if ($trimmed === '') {
            if (!empty($field['required'])) {
                $schema_missing[] = (string) $field['label'];
            }
            continue;
        }
        // 按类型校验；不合规当作缺失（前台已有原生校验，这里是服务端兜底）。
        if ($field['type'] === 'email' && !is_email($trimmed)) {
            $schema_missing[] = (string) $field['label'];
            continue;
        }
        if ($field['type'] === 'number' && !is_numeric($trimmed)) {
            $schema_missing[] = (string) $field['label'];
            continue;
        }
        if ($field['type'] === 'url' && esc_url_raw($trimmed) === '') {
            $schema_missing[] = (string) $field['label'];
            continue;
        }
        if ($field['type'] === 'select' && !isset($field['options'][$trimmed])) {
            // 提交的选项不在白名单：必填则报缺失，否则丢弃。
            if (!empty($field['required'])) {
                $schema_missing[] = (string) $field['label'];
            }
            continue;
        }
        // 固定语义 id 写回核心变量（各自的历史长度上限）。
        switch ($field['id']) {
            case 'name':
                $name = springapex_limited_text($trimmed, 120);
                break;
            case 'email':
                $email = sanitize_email($trimmed);
                break;
            case 'message':
                $message = springapex_limited_textarea($trimmed, 5000);
                break;
            case 'phone':
                $phone = springapex_limited_text($trimmed, 80);
                break;
            case 'company':
                $company = springapex_limited_text($trimmed, 160);
                break;
            case 'country':
                $country = springapex_limited_text($trimmed, 100);
                break;
            case 'wire_diameter':
                $wire_diameter = springapex_limited_text($trimmed, 80);
                break;
            case 'outside_diameter':
                $outside_diameter = springapex_limited_text($trimmed, 80);
                break;
            case 'free_length':
                $free_length = springapex_limited_text($trimmed, 80);
                break;
            case 'quantity':
                $quantity = springapex_limited_text($trimmed, 80);
                break;
            case 'material':
                $material = springapex_limited_text($trimmed, 120);
                break;
            case 'operating_environment':
                $operating_environment = springapex_limited_text($trimmed, 240);
                break;
            default:
                $custom_value = $field['type'] === 'textarea'
                    ? springapex_limited_textarea($trimmed, 5000)
                    : springapex_limited_text($trimmed, 240);
                // 以稳定字段 ID 为键，并把可变的展示名称与值一同保存。
                // 这样两个同名字段也不会相互覆盖。
                $custom_fields[(string) $field['id']] = [
                    'label' => (string) $field['label'],
                    'value' => $custom_value,
                ];
                break;
        }
    }

    // 姓名可按表单关闭：关闭且为空时用邮箱前缀兜底，询盘标题不至于空白。
    if ($name === '') {
        $at = strpos($email, '@');
        $name = $at !== false ? substr($email, 0, $at) : 'Visitor';
    }

    if (
        !is_email($email) ||
        $schema_missing !== [] ||
        $type === '' ||
        !is_array($allowed_types) ||
        !in_array($type, $allowed_types, true)
    ) {
        $detail = $schema_missing !== [] ? ' (' . implode(', ', array_slice($schema_missing, 0, 4)) . ')' : '';
        return springapex_contact_error(
            'springapex_invalid',
            sprintf('%s%s', __('Please complete the required fields with a valid email address.', 'springapex'), esc_html($detail)),
            422
        );
    }

    if (springapex_drawing_upload_requested() && !springapex_private_uploads_are_protected()) {
        return springapex_private_upload_protection_error();
    }

    if (springapex_contact_rate_limited($email)) {
        return springapex_contact_error(
            'springapex_rate_limit',
            __('Too many requests were submitted. Please wait before trying again.', 'springapex'),
            429
        );
    }

    $drawings = springapex_validate_drawing_upload();
    if (is_wp_error($drawings)) {
        return $drawings;
    }

    if (!post_type_exists('spring_inquiry') && function_exists('springapex_register_post_types')) {
        springapex_register_post_types();
    }

    $inquiry_id = wp_insert_post([
        'post_type' => 'spring_inquiry',
        'post_status' => 'private',
        'post_title' => sprintf('%s — %s', $name, $type),
        'post_content' => $message,
    ], true);

    if (is_wp_error($inquiry_id)) {
        return springapex_contact_error(
            'springapex_storage',
            __('We could not save your request. Please contact us by email.', 'springapex'),
            500
        );
    }

    $meta = [
        '_springapex_name' => $name,
        '_springapex_email' => $email,
        '_springapex_company' => $company,
        '_springapex_phone' => $phone,
        '_springapex_country' => $country,
        '_springapex_type' => $type,
        '_springapex_wire_diameter' => $wire_diameter,
        '_springapex_outside_diameter' => $outside_diameter,
        '_springapex_free_length' => $free_length,
        '_springapex_dimension_label_1' => $dimension_labels[0],
        '_springapex_dimension_label_2' => $dimension_labels[1],
        '_springapex_dimension_label_3' => $dimension_labels[2],
        '_springapex_quantity' => $quantity,
        '_springapex_material' => $material,
        '_springapex_operating_environment' => $operating_environment,
        '_springapex_intent' => $intent,
        '_springapex_form_context' => $form_context,
        '_springapex_source_id' => (string) $source_id,
        '_springapex_source_url' => $source_url,
        '_springapex_source_path' => $source_path,
        '_springapex_product' => $product,
        '_springapex_industry' => $industry,
        '_springapex_document' => sanitize_key(springapex_request_scalar($_POST['document'] ?? '')),
        '_springapex_mail_sent' => 'pending',
    ];
    // 自定义字段（表单设置新增的）：id => {label, value}，询盘详情与邮件动态展示。
    if ($custom_fields !== []) {
        $meta['_springapex_custom_fields'] = $custom_fields;
    }
    foreach ($meta as $key => $value) {
        if (!springapex_contact_update_meta((int) $inquiry_id, $key, $value)) {
            wp_delete_post((int) $inquiry_id, true);
            return springapex_contact_error(
                'springapex_storage',
                __('We could not save your request. Please contact us by email.', 'springapex'),
                500
            );
        }
    }

    $private_files = [];
    $persistent_private_files = [];
    if (is_array($drawings)) {
        foreach ($drawings as $drawing) {
            $private_file = springapex_store_private_drawing($drawing);
            if (!is_wp_error($private_file)) {
                $private_files[] = $private_file;
                $persistent_private_files[] = springapex_persistent_private_file_metadata($private_file);
                continue;
            }
            springapex_delete_private_files($private_files);
            wp_delete_post((int) $inquiry_id, true);
            return $private_file;
        }
        if (
            $persistent_private_files &&
            (
                !springapex_contact_update_meta((int) $inquiry_id, '_springapex_private_files', $persistent_private_files) ||
                !springapex_contact_update_meta((int) $inquiry_id, '_springapex_private_file', $persistent_private_files[0])
            )
        ) {
            springapex_delete_private_files($private_files);
            wp_delete_post((int) $inquiry_id, true);
            return springapex_contact_error('springapex_upload_storage', __('The drawings could not be stored securely.', 'springapex'), 500);
        }
    }

    $recipient = springapex_inquiry_recipient();
    // 通知邮件模板在「表单设置」维护：占位符替换成询盘真实值；
    // 块占位符（尺寸/自定义字段）有内容时以换行结尾，空块不占行。
    // 自定义字段随 main 的 PR #8 格式演进为 id => {label, value} 列表。
    $dimension_block = '';
    foreach ([
        [$dimension_labels[0], $wire_diameter],
        [$dimension_labels[1], $outside_diameter],
        [$dimension_labels[2], $free_length],
    ] as [$dimension_label, $dimension_value]) {
        $dimension_block .= sprintf("%s: %s\n", $dimension_label, $dimension_value);
    }
    $custom_block = '';
    foreach ($custom_fields as $custom_field) {
        $custom_block .= sprintf("%s: %s\n", (string) ($custom_field['label'] ?? ''), (string) ($custom_field['value'] ?? ''));
    }
    $mail_vars = [
        '{name}' => $name,
        '{email}' => $email,
        '{company}' => $company,
        '{phone}' => $phone,
        '{country}' => $country,
        '{type}' => $type,
        '{product}' => $product,
        '{industry}' => $industry,
        '{intent}' => $intent,
        '{quantity}' => $quantity,
        '{material}' => $material,
        '{operating_environment}' => $operating_environment,
        '{dimensions}' => $dimension_block,
        '{custom_fields}' => $custom_block,
        '{message}' => $message,
        '{document}' => sanitize_key(springapex_request_scalar($_POST['document'] ?? '')),
        '{drawings}' => $private_files
            ? implode(', ', array_map(static fn(array $file): string => (string) ($file['original_name'] ?? ''), $private_files))
            : 'None',
        '{inquiry_link}' => admin_url('post.php?post=' . (int) $inquiry_id . '&action=edit'),
        '{site_name}' => wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
        '{site_url}' => home_url('/'),
    ];
    $subject = springapex_fill_mail_template(springapex_inquiry_mail_subject(), $mail_vars);
    $body = springapex_fill_mail_template(springapex_inquiry_mail_body(), $mail_vars);
    $headers = ['Content-Type: text/plain; charset=UTF-8', "Reply-To: {$name} <{$email}>"];
    $attachments = [];
    foreach ($private_files as $private_file) {
        $private_path = springapex_private_file_path($private_file);
        if ($private_path !== '') {
            $attachments[] = $private_path;
        }
    }

    $sent = $recipient !== '' && wp_mail($recipient, $subject, $body, $headers, $attachments);
    springapex_cleanup_temporary_private_files($private_files);
    // 与后台询盘视图（inquiry-view.php）约定的状态值：sent / failed（初始 pending）。
    if (!springapex_contact_update_meta((int) $inquiry_id, '_springapex_mail_sent', $sent ? 'sent' : 'failed')) {
        springapex_record_contact_admin_warning('mail_status_meta');
    }

    $brand = springapex_brand();
    return [
        'sent' => $sent,
        'message' => $sent
            ? __('Thank you. Your request has been received.', 'springapex')
            : sprintf(__('Thank you. Your request is saved; you may also email %s.', 'springapex'), $brand['email'] ?? ''),
    ];
}

function springapex_contact_error(string $code, string $message, int $status): WP_Error
{
    return new WP_Error($code, $message, ['status' => $status]);
}

function springapex_private_upload_protection_error(): WP_Error
{
    return springapex_contact_error(
        'springapex_upload_protection',
        __('Drawing uploads are temporarily unavailable. Submit without a drawing or contact us by email.', 'springapex'),
        503
    );
}

function springapex_contact_update_meta(int $post_id, string $key, mixed $value): bool
{
    $updated = update_post_meta($post_id, $key, $value);
    if ($updated !== false) {
        return true;
    }
    return metadata_exists('post', $post_id, $key) && get_post_meta($post_id, $key, true) === $value;
}

function springapex_contact_error_status(WP_Error $error): int
{
    $data = $error->get_error_data();
    return is_array($data) ? (int) ($data['status'] ?? 500) : 500;
}

function springapex_contact_form_context(): string
{
    return sanitize_key(springapex_request_scalar($_POST['form_context'] ?? ''));
}

function springapex_contact_form_key(string $form_context): string
{
    return match ($form_context) {
        'quick' => 'quick',
        'full' => 'contact',
        'product' => 'product',
        default => '',
    };
}

function springapex_verify_contact_form_identity(): bool
{
    $form_context = springapex_contact_form_context();
    if (springapex_contact_form_key($form_context) === '') {
        return false;
    }

    // wp_verify_nonce 返回 int(1/2)|false，合法 nonce 恰好触发 bool 签名的
    // TypeError（PR #8 引入，表单有效提交 500），必须显式强转。
    return (bool) wp_verify_nonce(
        springapex_contact_nonce(),
        'springapex_contact_' . $form_context
    );
}

function springapex_contact_nonce(): string
{
    $nonce = $_POST['springapex_contact_nonce'] ?? ($_POST['nonce'] ?? '');
    return springapex_request_scalar($nonce);
}

function springapex_request_scalar(mixed $value): string
{
    return is_scalar($value) ? trim((string) wp_unslash($value)) : '';
}

function springapex_limited_text(mixed $value, int $max_length): string
{
    return springapex_limit_string(sanitize_text_field(springapex_request_scalar($value)), $max_length);
}

function springapex_limited_textarea(mixed $value, int $max_length): string
{
    return springapex_limit_string(sanitize_textarea_field(springapex_request_scalar($value)), $max_length);
}

function springapex_limit_string(string $value, int $max_length): string
{
    return function_exists('mb_substr')
        ? mb_substr($value, 0, $max_length)
        : substr($value, 0, $max_length);
}

function springapex_inquiry_recipient(): string
{
    $fallback = sanitize_email((string) get_option('admin_email'));
    $recipient = sanitize_email((string) get_theme_mod('springapex_inquiry_email', $fallback));
    return is_email($recipient) ? $recipient : $fallback;
}

function springapex_contact_rate_limited(string $email): bool
{
    $buckets = [];
    $ip = filter_var((string) ($_SERVER['REMOTE_ADDR'] ?? ''), FILTER_VALIDATE_IP);
    if (is_string($ip) && $ip !== '') {
        $packed_ip = inet_pton($ip);
        $buckets[] = ['scope' => 'ip', 'subject' => $packed_ip !== false ? bin2hex($packed_ip) : $ip, 'limit' => 10, 'ttl' => 10 * MINUTE_IN_SECONDS];
    }
    $buckets[] = ['scope' => 'email', 'subject' => strtolower(trim($email)), 'limit' => 5, 'ttl' => 30 * MINUTE_IN_SECONDS];

    $resolved = [];
    foreach ($buckets as $bucket) {
        $hash = hash_hmac('sha256', $bucket['subject'], wp_salt('auth'));
        $key = 'sa_inquiry_' . $bucket['scope'] . '_' . $hash;
        $resolved[] = [
            'key' => $key,
            'lock' => 'sa_rate_lock_' . hash('sha256', $key),
            'limit' => (int) $bucket['limit'],
            'ttl' => (int) $bucket['ttl'],
        ];
    }

    $locks = [];
    try {
        foreach ($resolved as $bucket) {
            $token = springapex_acquire_option_lock($bucket['lock'], 15);
            if ($token === '') {
                return true;
            }
            $locks[] = [$bucket['lock'], $token];
        }

        foreach ($resolved as &$bucket) {
            $value = get_transient($bucket['key']);
            $bucket['count'] = $value === false ? 0 : max(0, (int) $value);
            if ($bucket['count'] >= $bucket['limit']) {
                return true;
            }
        }
        unset($bucket);

        foreach ($resolved as $bucket) {
            if (!set_transient($bucket['key'], $bucket['count'] + 1, $bucket['ttl'])) {
                return true;
            }
        }
        return false;
    } finally {
        foreach (array_reverse($locks) as [$lock_name, $token]) {
            springapex_release_option_lock($lock_name, $token);
        }
    }
}

function springapex_allowed_drawing_mimes(): array
{
    return [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'zip' => 'application/zip',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'step|stp' => 'application/step',
        'iges|igs' => 'model/iges',
        'jpg|jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ];
}

function springapex_drawing_canonical_mime(string $extension): string
{
    return match ($extension) {
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'zip' => 'application/zip',
        'dwg' => 'image/vnd.dwg',
        'dxf' => 'image/vnd.dxf',
        'step', 'stp' => 'application/step',
        'iges', 'igs' => 'model/iges',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        default => '',
    };
}

function springapex_normalize_drawing_uploads(): array|WP_Error
{
    if (!isset($_FILES['drawing'])) {
        return [];
    }

    $field = $_FILES['drawing'];
    if (!is_array($field) || !array_key_exists('error', $field)) {
        return springapex_contact_error('springapex_upload', __('The file upload did not complete.', 'springapex'), 422);
    }

    if (!is_array($field['error'])) {
        return is_scalar($field['error']) ? [$field] : springapex_contact_error(
            'springapex_upload',
            __('The file upload did not complete.', 'springapex'),
            422
        );
    }

    $count = count($field['error']);
    if ($count > 10) {
        return springapex_contact_error('springapex_upload', __('Upload no more than 10 files.', 'springapex'), 422);
    }
    if ($count === 0) {
        return [];
    }

    $files = [];
    for ($index = 0; $index < $count; $index++) {
        $file = [];
        foreach (['name', 'type', 'tmp_name', 'error', 'size'] as $key) {
            $values = $field[$key] ?? null;
            if (!is_array($values) || !array_key_exists($index, $values)) {
                return springapex_contact_error('springapex_upload', __('The file upload did not complete.', 'springapex'), 422);
            }
            $file[$key] = $values[$index];
        }
        $files[] = $file;
    }
    return $files;
}

function springapex_validate_drawing_upload(): array|WP_Error|null
{
    $files = springapex_normalize_drawing_uploads();
    if (is_wp_error($files)) {
        return $files;
    }

    $validated = [];
    $total_size = 0;
    foreach ($files as $file) {
        if (!is_array($file) || !is_scalar($file['error'] ?? null)) {
            return springapex_contact_error('springapex_upload', __('The file upload did not complete.', 'springapex'), 422);
        }

        $error = (int) $file['error'];
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        if ($error !== UPLOAD_ERR_OK) {
            return springapex_contact_error('springapex_upload', __('The file upload did not complete.', 'springapex'), 422);
        }

        $name = is_string($file['name'] ?? null) ? $file['name'] : '';
        $tmp_name = is_string($file['tmp_name'] ?? null) ? $file['tmp_name'] : '';
        $size = is_scalar($file['size'] ?? null) ? (int) $file['size'] : 0;
        if ($name === '' || $tmp_name === '' || $size < 1 || !is_uploaded_file($tmp_name) || !is_readable($tmp_name)) {
            return springapex_contact_error('springapex_upload', __('The file upload did not complete.', 'springapex'), 422);
        }
        $total_size += $size;
        if ($size > 10 * MB_IN_BYTES || $total_size > 10 * MB_IN_BYTES) {
            return springapex_contact_error('springapex_upload_size', __('The combined upload must be 10 MB or smaller.', 'springapex'), 422);
        }
        if (!springapex_private_uploads_are_protected()) {
            return springapex_private_upload_protection_error();
        }

        $extension = strtolower((string) pathinfo($name, PATHINFO_EXTENSION));
        $mime = springapex_drawing_canonical_mime($extension);
        if ($mime === '' || !springapex_drawing_signature_is_valid($extension, $tmp_name)) {
            return springapex_contact_error(
                'springapex_upload_type',
                __('Use valid PDF, Word, ZIP, DWG, DXF, STEP, IGES, JPG or PNG files.', 'springapex'),
                422
            );
        }

        $validated[] = [
            'file' => $file,
            'extension' => $extension,
            'mime' => $mime,
            'original_name' => sanitize_file_name($name) ?: 'drawing.' . $extension,
            'size' => $size,
        ];
    }

    return $validated ?: null;
}

function springapex_drawing_upload_requested(): bool
{
    $files = springapex_normalize_drawing_uploads();
    if (is_wp_error($files)) {
        return false;
    }
    foreach ($files as $file) {
        if (is_array($file) && is_scalar($file['error'] ?? null) && (int) $file['error'] === UPLOAD_ERR_OK) {
            return true;
        }
    }
    return false;
}

function springapex_drawing_signature_is_valid(string $extension, string $path): bool
{
    $sample = file_get_contents($path, false, null, 0, 4096);
    if (!is_string($sample) || $sample === '') {
        return false;
    }

    if (
        in_array($extension, ['jpg', 'jpeg', 'png'], true) &&
        function_exists('wp_get_image_mime')
    ) {
        return wp_get_image_mime($path) === springapex_drawing_canonical_mime($extension);
    }

    return match ($extension) {
        'doc' => str_starts_with($sample, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"),
        'docx' => in_array(substr($sample, 0, 4), ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true),
        'pdf' => strpos(substr($sample, 0, 1024), '%PDF-') !== false,
        'zip' => in_array(substr($sample, 0, 4), ["PK\x03\x04", "PK\x05\x06", "PK\x07\x08"], true),
        'dwg' => (bool) preg_match('/^AC10[0-9]{2}/', $sample),
        'dxf' => str_starts_with($sample, "AutoCAD Binary DXF\r\n\x1a\0") || (bool) preg_match('/^\s*0\s*\R\s*SECTION\b/i', $sample),
        'step', 'stp' => str_starts_with(ltrim($sample, "\xEF\xBB\xBF \t\r\n"), 'ISO-10303-21;'),
        'iges', 'igs' => springapex_iges_signature_is_valid($sample),
        'jpg', 'jpeg' => str_starts_with($sample, "\xFF\xD8\xFF"),
        'png' => str_starts_with($sample, "\x89PNG\r\n\x1a\n"),
        default => false,
    };
}

function springapex_iges_signature_is_valid(string $sample): bool
{
    if (strpbrk($sample, "\r\n") === false) {
        $complete_length = intdiv(strlen($sample), 80) * 80;
        $lines = $complete_length >= 80 ? str_split(substr($sample, 0, $complete_length), 80) : [];
    } else {
        $lines = preg_split('/\r\n|\n|\r/', $sample);
    }
    if (!is_array($lines)) {
        return false;
    }

    $record_count = 0;
    $last_index = count($lines) - 1;
    foreach ($lines as $index => $line) {
        $length = strlen($line);
        if ($index === $last_index && $length > 0 && $length < 80) {
            break;
        }
        if ($length === 0 && $index === $last_index) {
            continue;
        }
        if ($length !== 80) {
            return false;
        }

        $section = strtoupper($line[72]);
        $sequence = substr($line, 73, 7);
        if (
            !in_array($section, ['S', 'G', 'D', 'P', 'T'], true) ||
            !preg_match('/^[ 0-9]{7}$/', $sequence) ||
            !preg_match('/^[0-9]+$/', trim($sequence)) ||
            (int) trim($sequence) < 1
        ) {
            return false;
        }
        if ($record_count === 0 && ($section !== 'S' || (int) trim($sequence) !== 1)) {
            return false;
        }
        $record_count++;
    }

    return $record_count >= 2;
}

function springapex_private_uploads_are_protected(): bool
{
    return springapex_s3_private_storage_enabled()
        || (defined('SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED') && SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED === true);
}

function springapex_private_upload_root(bool $create = false): string|WP_Error
{
    $uploads = wp_upload_dir(null, false);
    if (!empty($uploads['error']) || empty($uploads['basedir'])) {
        return springapex_contact_error('springapex_upload_storage', __('Private upload storage is unavailable.', 'springapex'), 500);
    }

    $root = wp_normalize_path(trailingslashit((string) $uploads['basedir']) . 'springapex-private');
    if ($create && !is_dir($root) && !wp_mkdir_p($root)) {
        return springapex_contact_error('springapex_upload_storage', __('Private upload storage is unavailable.', 'springapex'), 500);
    }
    if ($create && !springapex_write_private_upload_guards($root)) {
        return springapex_contact_error('springapex_upload_storage', __('Private upload protection could not be initialized.', 'springapex'), 500);
    }
    return $root;
}

function springapex_write_private_upload_guards(string $root): bool
{
    $guards = [
        '.htaccess' => "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n",
        'index.php' => "<?php\nhttp_response_code(404);\nexit;\n",
        'web.config' => "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<configuration><system.webServer><security><authorization><remove users=\"*\" roles=\"\" verbs=\"\"/><add accessType=\"Deny\" users=\"*\"/></authorization></security></system.webServer></configuration>\n",
    ];
    foreach ($guards as $name => $contents) {
        $path = trailingslashit($root) . $name;
        if (is_file($path) && is_readable($path) && file_get_contents($path) === $contents) {
            continue;
        }
        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            return false;
        }
    }
    return true;
}

function springapex_private_upload_dir(array $uploads): array
{
    $subdir = '/springapex-private/' . gmdate('Y/m');
    $uploads['path'] = (string) $uploads['basedir'] . $subdir;
    $uploads['url'] = (string) $uploads['baseurl'] . $subdir;
    $uploads['subdir'] = $subdir;
    return $uploads;
}

function springapex_store_private_drawing(array $drawing): array|WP_Error
{
    if (!springapex_private_uploads_are_protected()) {
        return springapex_private_upload_protection_error();
    }

    $root = springapex_private_upload_root(true);
    if (is_wp_error($root)) {
        return $root;
    }

    require_once ABSPATH . 'wp-admin/includes/file.php';
    $file = $drawing['file'];
    $file['name'] = wp_generate_password(40, false, false) . '.' . $drawing['extension'];
    $uploaded = [];
    add_filter('upload_dir', 'springapex_private_upload_dir');
    try {
        $uploaded = wp_handle_upload($file, [
            'test_form' => false,
            // Type validation is handled by the exact extension allowlist and pre/post-upload signature checks above.
            'test_type' => false,
            'mimes' => springapex_allowed_drawing_mimes(),
        ]);
    } finally {
        remove_filter('upload_dir', 'springapex_private_upload_dir');
    }

    if (!empty($uploaded['error']) || empty($uploaded['file'])) {
        return springapex_contact_error('springapex_upload_storage', __('The drawing could not be stored securely.', 'springapex'), 500);
    }

    $file_path = realpath((string) $uploaded['file']);
    $root_path = realpath($root);
    if (
        $file_path === false ||
        $root_path === false ||
        !str_starts_with(wp_normalize_path($file_path), trailingslashit(wp_normalize_path($root_path)))
    ) {
        if ($file_path !== false && is_file($file_path)) {
            wp_delete_file($file_path);
        }
        return springapex_contact_error('springapex_upload_storage', __('The drawing could not be stored securely.', 'springapex'), 500);
    }

    $stored_size = filesize($file_path);
    if (
        $stored_size === false ||
        $stored_size < 1 ||
        $stored_size > 10 * MB_IN_BYTES ||
        !springapex_drawing_signature_is_valid((string) $drawing['extension'], $file_path)
    ) {
        wp_delete_file($file_path);
        return springapex_contact_error(
            'springapex_upload_type',
            __('Use a valid PDF, Word, ZIP, DWG, DXF, STEP, IGES, JPG or PNG file.', 'springapex'),
            422
        );
    }

    @chmod($file_path, 0640);
    $sha256 = hash_file('sha256', $file_path);
    if (!is_string($sha256) || $sha256 === '') {
        wp_delete_file($file_path);
        return springapex_contact_error('springapex_upload_storage', __('The drawing could not be stored securely.', 'springapex'), 500);
    }
    $metadata = [
        'relative_path' => ltrim(substr(wp_normalize_path($file_path), strlen(wp_normalize_path($root_path))), '/'),
        'original_name' => (string) $drawing['original_name'],
        'mime' => (string) $drawing['mime'],
        'size' => (int) $stored_size,
        'sha256' => $sha256,
    ];

    if (springapex_s3_private_storage_enabled()) {
        $s3_metadata = springapex_s3_store_private_file(
            $file_path,
            (string) $drawing['original_name'],
            (string) $drawing['mime'],
            $sha256
        );
        if (is_wp_error($s3_metadata)) {
            wp_delete_file($file_path);
            return springapex_contact_error(
                'springapex_upload_storage',
                __('The drawing could not be stored securely.', 'springapex'),
                500
            );
        }
        return $s3_metadata;
    }

    return $metadata;
}

/** @param array<string, mixed> $metadata */
function springapex_persistent_private_file_metadata(array $metadata): array
{
    unset($metadata['_temporary_path']);
    return $metadata;
}

function springapex_private_file_path(mixed $metadata): string
{
    if (!is_array($metadata)) {
        return '';
    }
    $temporary_path = is_string($metadata['_temporary_path'] ?? null) ? $metadata['_temporary_path'] : '';
    if ($temporary_path !== '' && is_file($temporary_path) && is_readable($temporary_path)) {
        return $temporary_path;
    }
    if (($metadata['storage'] ?? '') === 's3' || !is_string($metadata['relative_path'] ?? null)) {
        return '';
    }
    $root = springapex_private_upload_root(false);
    if (is_wp_error($root)) {
        return '';
    }

    $root_path = realpath($root);
    $file_path = realpath(trailingslashit($root) . ltrim($metadata['relative_path'], '/'));
    if (
        $root_path === false ||
        $file_path === false ||
        !str_starts_with(wp_normalize_path($file_path), trailingslashit(wp_normalize_path($root_path))) ||
        !is_file($file_path) ||
        !is_readable($file_path)
    ) {
        return '';
    }
    return $file_path;
}

/** @param array<int, array<string, mixed>> $files */
function springapex_delete_private_files(array $files): void
{
    foreach ($files as $metadata) {
        if (is_array($metadata) && ($metadata['storage'] ?? '') === 's3') {
            springapex_s3_delete_private_file($metadata);
        }
        $path = springapex_private_file_path($metadata);
        if ($path !== '') {
            wp_delete_file($path);
        }
    }
}

/** @param array<int, array<string, mixed>> $files */
function springapex_cleanup_temporary_private_files(array $files): void
{
    foreach ($files as $metadata) {
        $path = is_string($metadata['_temporary_path'] ?? null) ? $metadata['_temporary_path'] : '';
        if ($path !== '' && is_file($path)) {
            wp_delete_file($path);
        }
    }
}

/** @return array<int, array<string, mixed>> */
function springapex_inquiry_private_files(int $inquiry_id): array
{
    $files = get_post_meta($inquiry_id, '_springapex_private_files', true);
    if (is_array($files)) {
        $files = array_values(array_filter($files, 'is_array'));
        if ($files) {
            return $files;
        }
    }

    $legacy = get_post_meta($inquiry_id, '_springapex_private_file', true);
    return is_array($legacy) && !empty($legacy['relative_path']) ? [$legacy] : [];
}

add_action('before_delete_post', static function (int $post_id): void {
    if (get_post_type($post_id) !== 'spring_inquiry') {
        return;
    }
    springapex_delete_private_files(springapex_inquiry_private_files($post_id));
});

function springapex_download_inquiry_file(): void
{
    $inquiry_id = absint(springapex_request_scalar($_GET['inquiry_id'] ?? '0'));
    if (
        $inquiry_id < 1 ||
        get_post_type($inquiry_id) !== 'spring_inquiry' ||
        !current_user_can('read_post', $inquiry_id)
    ) {
        wp_die(esc_html__('You are not allowed to access this file.', 'springapex'), '', ['response' => 403]);
    }

    $file_index = absint(springapex_request_scalar($_GET['file'] ?? '0'));
    check_admin_referer('springapex_download_inquiry_' . $inquiry_id . '_' . $file_index);
    $files = springapex_inquiry_private_files($inquiry_id);
    $metadata = $files[$file_index] ?? null;
    $delete_after_download = false;
    if (is_array($metadata) && ($metadata['storage'] ?? '') === 's3') {
        $download = springapex_s3_download_private_file($metadata);
        if (is_wp_error($download)) {
            wp_die(esc_html__('The requested file is unavailable.', 'springapex'), '', ['response' => 404]);
        }
        $path = $download;
        $delete_after_download = true;
    } else {
        $path = springapex_private_file_path($metadata);
    }
    if ($path === '' || !is_file($path)) {
        wp_die(esc_html__('The requested file is unavailable.', 'springapex'), '', ['response' => 404]);
    }

    $filename = sanitize_file_name((string) ($metadata['original_name'] ?? 'drawing')) ?: 'drawing';
    $filename = str_replace(["\r", "\n", '"'], '', $filename);
    $mime = sanitize_mime_type((string) ($metadata['mime'] ?? 'application/octet-stream')) ?: 'application/octet-stream';
    nocache_headers();
    header('Content-Type: ' . $mime);
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . (string) filesize($path));
    readfile($path);
    if ($delete_after_download) {
        wp_delete_file($path);
    }
    exit;
}

/**
 * Human labels for the form each inquiry came from (the hidden `form_context`).
 * Shared by the admin column and the source filter dropdown.
 */
function springapex_inquiry_form_context_labels(): array
{
    return [
        'full' => '整表单（联系页）',
        'product' => '产品询价',
        'quick' => '快速留言',
    ];
}

add_filter('manage_spring_inquiry_posts_columns', static function (array $columns): array {
    return [
        'cb' => $columns['cb'] ?? '<input type="checkbox">',
        'title' => __('Inquiry', 'springapex'),
        'springapex_email' => __('Email', 'springapex'),
        'springapex_company' => __('Company', 'springapex'),
        'springapex_country' => __('Country', 'springapex'),
        'springapex_type' => __('Type', 'springapex'),
        'springapex_source' => '来源',
        'springapex_specs' => __('Spring Specs', 'springapex'),
        'springapex_file' => __('Drawings', 'springapex'),
        'date' => __('Date', 'springapex'),
    ];
});

add_action('manage_spring_inquiry_posts_custom_column', static function (string $column, int $post_id): void {
    $map = [
        'springapex_email' => '_springapex_email',
        'springapex_company' => '_springapex_company',
        'springapex_type' => '_springapex_type',
    ];
    if (isset($map[$column])) {
        echo esc_html((string) get_post_meta($post_id, $map[$column], true));
        return;
    }
    if ($column === 'springapex_country') {
        $country = (string) get_post_meta($post_id, '_springapex_country', true);
        if ($country === '') {
            $country = (string) get_post_meta($post_id, '_springapex_region', true);
        }
        echo $country !== '' ? esc_html($country) : '&mdash;';
        return;
    }
    if ($column === 'springapex_source') {
        $context = (string) get_post_meta($post_id, '_springapex_form_context', true);
        $labels = springapex_inquiry_form_context_labels();
        $label = $labels[$context] ?? ($context !== '' ? $context : '&mdash;');

        $source_id = (int) get_post_meta($post_id, '_springapex_source_id', true);
        $page = '';
        if ($source_id > 0) {
            $title = get_the_title($source_id);
            if ($title !== '') {
                $page = $title;
            }
        }
        if ($page === '') {
            $url = (string) get_post_meta($post_id, '_springapex_source_url', true);
            if ($url !== '') {
                $page = wp_parse_url($url, PHP_URL_PATH) ?: $url;
            }
        }

        echo '<strong>' . wp_kses($label, ['br' => []]) . '</strong>';
        if ($page !== '') {
            echo '<br><span class="description">' . esc_html($page) . '</span>';
        }
        return;
    }
    if ($column === 'springapex_specs') {
        $wire = (string) get_post_meta($post_id, '_springapex_wire_diameter', true);
        $outside = (string) get_post_meta($post_id, '_springapex_outside_diameter', true);
        $length = (string) get_post_meta($post_id, '_springapex_free_length', true);
        $dimension_labels = [
            (string) get_post_meta($post_id, '_springapex_dimension_label_1', true) ?: __('Wire', 'springapex'),
            (string) get_post_meta($post_id, '_springapex_dimension_label_2', true) ?: __('OD', 'springapex'),
            (string) get_post_meta($post_id, '_springapex_dimension_label_3', true) ?: __('Length', 'springapex'),
        ];
        $quantity = (string) get_post_meta($post_id, '_springapex_quantity', true);
        $values = array_filter([
            $wire !== '' ? sprintf('%s: %s', $dimension_labels[0], $wire) : '',
            $outside !== '' ? sprintf('%s: %s', $dimension_labels[1], $outside) : '',
            $length !== '' ? sprintf('%s: %s', $dimension_labels[2], $length) : '',
            $quantity !== '' ? sprintf(__('Qty: %s', 'springapex'), $quantity) : '',
        ]);
        echo $values ? esc_html(implode(' · ', $values)) : '&mdash;';
        return;
    }
    if ($column !== 'springapex_file') {
        return;
    }

    $files = springapex_inquiry_private_files($post_id);
    if (!$files) {
        echo '&mdash;';
        return;
    }
    foreach ($files as $index => $metadata) {
        $url = wp_nonce_url(
            add_query_arg([
                'action' => 'springapex_download_inquiry_file',
                'inquiry_id' => $post_id,
                'file' => $index,
            ], admin_url('admin-post.php')),
            'springapex_download_inquiry_' . $post_id . '_' . $index
        );
        $label = sanitize_file_name((string) ($metadata['original_name'] ?? ''));
        printf(
            '%s<a href="%s">%s</a>',
            $index > 0 ? '<br>' : '',
            esc_url($url),
            esc_html($label !== '' ? $label : sprintf(__('Download %d', 'springapex'), $index + 1))
        );
    }
}, 10, 2);

/**
 * Source filters above the inquiry list: one dropdown for form type, one for
 * the specific page an inquiry was submitted from. Native restrict_manage_posts
 * UI; the actual filtering happens in the pre_get_posts handler below.
 */
add_action('restrict_manage_posts', static function (string $post_type): void {
    if ($post_type !== 'spring_inquiry') {
        return;
    }

    $selected_context = isset($_GET['sa_form_context']) ? sanitize_key(wp_unslash($_GET['sa_form_context'])) : '';
    echo '<label class="screen-reader-text" for="sa_form_context">按表单类型筛选</label>';
    echo '<select name="sa_form_context" id="sa_form_context">';
    echo '<option value="">全部来源类型</option>';
    foreach (springapex_inquiry_form_context_labels() as $value => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($value),
            selected($selected_context, $value, false),
            esc_html($label)
        );
    }
    echo '</select>';

    global $wpdb;
    $options = [];

    // Pages we can resolve to a post: label them by title, filter by post ID.
    $source_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT pm.meta_value FROM {$wpdb->postmeta} pm
         INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
         WHERE pm.meta_key = %s AND pm.meta_value <> '' AND pm.meta_value <> '0'
           AND p.post_type = %s",
        '_springapex_source_id',
        'spring_inquiry'
    ));
    foreach ($source_ids as $source_id) {
        $source_id = (int) $source_id;
        $title = get_the_title($source_id);
        if ($title === '') {
            continue;
        }
        $options['id:' . $source_id] = $title;
    }

    // Site-wide widgets and CPT archive pages (e.g. /products/) submit without a
    // usable post ID, so those inquiries have no resolvable title — only the
    // canonical referring path. Offer that path as a filter option, restricted to
    // URL-only records so a singular page is never listed here as well as by title.
    $source_paths = $wpdb->get_col($wpdb->prepare(
        "SELECT DISTINCT pm_path.meta_value FROM {$wpdb->postmeta} pm_path
         INNER JOIN {$wpdb->posts} p ON p.ID = pm_path.post_id
         LEFT JOIN {$wpdb->postmeta} pm_id ON pm_id.post_id = pm_path.post_id
             AND pm_id.meta_key = %s
         WHERE pm_path.meta_key = %s AND pm_path.meta_value <> ''
           AND p.post_type = %s
           AND (pm_id.meta_value IS NULL OR pm_id.meta_value = '' OR pm_id.meta_value = '0')",
        '_springapex_source_id',
        '_springapex_source_path',
        'spring_inquiry'
    ));
    foreach ($source_paths as $path) {
        $path = (string) $path;
        if ($path === '') {
            continue;
        }
        $options['url:' . $path] = $path;
    }

    if (empty($options)) {
        return;
    }

    $selected_source = isset($_GET['sa_source_id']) ? sanitize_text_field(wp_unslash($_GET['sa_source_id'])) : '';
    echo '<label class="screen-reader-text" for="sa_source_id">按来源页面筛选</label>';
    echo '<select name="sa_source_id" id="sa_source_id">';
    echo '<option value="">全部来源页面</option>';
    foreach ($options as $value => $label) {
        printf(
            '<option value="%s"%s>%s</option>',
            esc_attr($value),
            selected($selected_source, $value, false),
            esc_html($label)
        );
    }
    echo '</select>';
});

add_action('pre_get_posts', static function (WP_Query $query): void {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }
    if (($query->get('post_type') ?: '') !== 'spring_inquiry') {
        return;
    }

    $meta_query = [];
    $context = isset($_GET['sa_form_context']) ? sanitize_key(wp_unslash($_GET['sa_form_context'])) : '';
    if ($context !== '' && array_key_exists($context, springapex_inquiry_form_context_labels())) {
        $meta_query[] = [
            'key' => '_springapex_form_context',
            'value' => $context,
        ];
    }

    $source = isset($_GET['sa_source_id']) ? sanitize_text_field(wp_unslash($_GET['sa_source_id'])) : '';
    if ($source !== '') {
        if (str_starts_with($source, 'url:')) {
            $path = substr($source, 4);
            if ($path !== '') {
                // Exact path match, and only URL-only records, so selecting the
                // /products/ archive never drags in singular product inquiries.
                $meta_query[] = [
                    'relation' => 'AND',
                    [
                        'key' => '_springapex_source_path',
                        'value' => $path,
                    ],
                    [
                        'relation' => 'OR',
                        ['key' => '_springapex_source_id', 'value' => ['', '0'], 'compare' => 'IN'],
                        ['key' => '_springapex_source_id', 'compare' => 'NOT EXISTS'],
                    ],
                ];
            }
        } else {
            // "id:<n>" from the dropdown, or a bare numeric id for back-compat.
            $source_id = str_starts_with($source, 'id:') ? absint(substr($source, 3)) : absint($source);
            if ($source_id > 0) {
                $meta_query[] = [
                    'key' => '_springapex_source_id',
                    'value' => (string) $source_id,
                ];
            }
        }
    }

    if ($meta_query === []) {
        return;
    }
    if (count($meta_query) > 1) {
        $meta_query['relation'] = 'AND';
    }
    $query->set('meta_query', $meta_query);
});

function springapex_record_contact_admin_warning(string $code): void
{
    $warning = get_option('springapex_contact_admin_warning', []);
    $count = is_array($warning) ? max(0, (int) ($warning['count'] ?? 0)) : 0;
    update_option('springapex_contact_admin_warning', [
        'code' => sanitize_key($code),
        'count' => $count + 1,
        'last_at' => time(),
    ], false);
}

function springapex_contact_admin_notices(): void
{
    if (!current_user_can('manage_options')) {
        return;
    }

    if (!springapex_private_uploads_are_protected()) {
        printf(
            '<div class="notice notice-warning"><p>%s</p></div>',
            esc_html__('NorenSpring drawing uploads are disabled until the private uploads path is blocked by the Web Server/CDN and SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED is enabled in wp-config.php.', 'springapex')
        );
    }

    $warning = get_option('springapex_contact_admin_warning', []);
    if (!is_array($warning) || ($warning['code'] ?? '') !== 'mail_status_meta') {
        return;
    }

    $count = max(1, (int) ($warning['count'] ?? 1));
    $review_url = admin_url('edit.php?post_type=spring_inquiry');
    $dismiss_url = wp_nonce_url(
        admin_url('admin-post.php?action=springapex_dismiss_contact_warning'),
        'springapex_dismiss_contact_warning'
    );
    printf(
        '<div class="notice notice-warning"><p>%1$s <a href="%2$s">%3$s</a> <a href="%4$s">%5$s</a></p></div>',
        esc_html(sprintf(
            _n(
                'NorenSpring could not finalize the email status for %d inquiry. The inquiry is still saved.',
                'NorenSpring could not finalize the email status for %d inquiries. The inquiries are still saved.',
                $count,
                'springapex'
            ),
            $count
        )),
        esc_url($review_url),
        esc_html__('Review inquiries', 'springapex'),
        esc_url($dismiss_url),
        esc_html__('Dismiss after review', 'springapex')
    );
}

function springapex_dismiss_contact_warning(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You are not allowed to dismiss this warning.', 'springapex'), '', ['response' => 403]);
    }

    check_admin_referer('springapex_dismiss_contact_warning');
    delete_option('springapex_contact_admin_warning');
    wp_safe_redirect(admin_url('edit.php?post_type=spring_inquiry'), 303);
    exit;
}
