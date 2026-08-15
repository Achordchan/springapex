<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('customize_register', static function (object $customizer): void {
    $customizer->add_section('springapex_company', [
        'title' => __('ApexSpring Company Details', 'springapex'),
        'priority' => 30,
    ]);

    $brand = springapex_get('brand', []);
    $admin_email = sanitize_email((string) get_option('admin_email'));
    $fields = [
        'springapex_email' => ['Email', 'email', $brand['email'] ?? ''],
        'springapex_inquiry_email' => ['Inquiry recipient email', 'email', $admin_email],
        'springapex_phone' => ['Phone', 'text', $brand['phone'] ?? ''],
        'springapex_whatsapp' => ['WhatsApp', 'text', $brand['whatsapp'] ?? $brand['phone'] ?? ''],
        'springapex_address' => ['Address', 'textarea', $brand['address'] ?? ''],
        'springapex_hours' => ['Business hours', 'text', $brand['hours'] ?? ''],
        'springapex_linkedin' => ['LinkedIn URL', 'url', $brand['linkedin'] ?? ''],
        'springapex_facebook' => ['Facebook URL', 'url', $brand['facebook'] ?? ''],
        'springapex_x' => ['X URL', 'url', $brand['x'] ?? ''],
        'springapex_instagram' => ['Instagram URL', 'url', $brand['instagram'] ?? ''],
        'springapex_tiktok' => ['TikTok URL', 'url', $brand['tiktok'] ?? ''],
    ];

    foreach ($fields as $setting => [$label, $type, $default]) {
        $sanitize = match ($type) {
            'email' => 'sanitize_email',
            'url' => 'esc_url_raw',
            'textarea' => 'sanitize_textarea_field',
            default => 'sanitize_text_field',
        };
        $customizer->add_setting($setting, [
            'default' => $default,
            'sanitize_callback' => $sanitize,
        ]);
        $customizer->add_control($setting, [
            'section' => 'springapex_company',
            'label' => __($label, 'springapex'),
            'type' => $type,
        ]);
    }
});
