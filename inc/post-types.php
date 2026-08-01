<?php
declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_register_post_types(): void
{
    register_post_type('spring_product', [
        'labels' => [
            'name' => __('Spring Products', 'springapex'),
            'singular_name' => __('Spring Product', 'springapex'),
            'add_new_item' => __('Add Spring Product', 'springapex'),
            'edit_item' => __('Edit Spring Product', 'springapex'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'products',
        'rewrite' => ['slug' => 'products', 'with_front' => false],
        'menu_icon' => 'dashicons-editor-contract',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_solution', [
        'labels' => [
            'name' => __('Industry Solutions', 'springapex'),
            'singular_name' => __('Industry Solution', 'springapex'),
            'add_new_item' => __('Add Industry Solution', 'springapex'),
            'edit_item' => __('Edit Industry Solution', 'springapex'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'solutions',
        'rewrite' => ['slug' => 'solutions', 'with_front' => false],
        'menu_icon' => 'dashicons-admin-site-alt3',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_news', [
        'labels' => [
            'name' => __('News', 'springapex'),
            'singular_name' => __('News Item', 'springapex'),
            'add_new_item' => __('Add News Item', 'springapex'),
            'edit_item' => __('Edit News Item', 'springapex'),
        ],
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => 'news',
        'rewrite' => ['slug' => 'news', 'with_front' => false],
        'menu_icon' => 'dashicons-megaphone',
        'supports' => ['title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'],
    ]);

    register_post_type('spring_inquiry', [
        'labels' => [
            'name' => __('Inquiries', 'springapex'),
            'singular_name' => __('Inquiry', 'springapex'),
            'edit_item' => __('View Inquiry', 'springapex'),
        ],
        'public' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => false,
        'rewrite' => false,
        'query_var' => false,
        'menu_icon' => 'dashicons-email-alt2',
        'supports' => ['title', 'editor'],
        'capability_type' => ['spring_inquiry', 'spring_inquiries'],
        'capabilities' => [
            'create_posts' => 'do_not_allow',
        ],
        'map_meta_cap' => true,
    ]);
}

add_action('init', 'springapex_register_post_types');

function springapex_inquiry_primitive_capabilities(): array
{
    return [
        'edit_spring_inquiries',
        'edit_others_spring_inquiries',
        'edit_private_spring_inquiries',
        'edit_published_spring_inquiries',
        'publish_spring_inquiries',
        'read_private_spring_inquiries',
        'delete_spring_inquiries',
        'delete_private_spring_inquiries',
        'delete_published_spring_inquiries',
        'delete_others_spring_inquiries',
    ];
}

function springapex_grant_inquiry_capabilities(): bool
{
    if (!function_exists('get_role')) {
        return false;
    }

    $administrator = get_role('administrator');
    if (!$administrator) {
        return false;
    }

    foreach (springapex_inquiry_primitive_capabilities() as $capability) {
        $administrator->add_cap($capability);
    }

    if ((string) get_option('springapex_inquiry_cap_version', '') === '1') {
        return true;
    }

    return update_option('springapex_inquiry_cap_version', '1', false);
}

add_action('admin_init', static function (): void {
    if (
        current_user_can('activate_themes') &&
        (string) get_option('springapex_inquiry_cap_version', '') !== '1'
    ) {
        springapex_grant_inquiry_capabilities();
    }
});

add_action('add_meta_boxes_spring_product', static function (): void {
    add_meta_box(
        'springapex-product-details',
        __('Product Details', 'springapex'),
        'springapex_render_product_meta_box',
        'spring_product',
        'normal',
        'high'
    );
});

function springapex_render_product_meta_box(object $post): void
{
    wp_nonce_field('springapex_save_product', 'springapex_product_nonce');

    $subtitle = (string) get_post_meta((int) $post->ID, '_springapex_subtitle', true);
    $specs = springapex_meta_rows_to_text(get_post_meta((int) $post->ID, '_springapex_specs', true), 'label', 'value');
    $materials = springapex_meta_rows_to_text(get_post_meta((int) $post->ID, '_springapex_materials', true), 'title', 'icon');
    $applications = springapex_meta_rows_to_text(get_post_meta((int) $post->ID, '_springapex_applications', true), 'title', 'icon');
    $catalog_url = (string) get_post_meta((int) $post->ID, '_springapex_catalog_url', true);
    $featured = (bool) get_post_meta((int) $post->ID, '_springapex_featured', true);
    ?>
    <p>
      <label for="springapex-subtitle"><strong><?php esc_html_e('Hero subtitle', 'springapex'); ?></strong></label><br>
      <textarea class="widefat" rows="3" id="springapex-subtitle" name="springapex_subtitle"><?php echo esc_textarea($subtitle); ?></textarea>
    </p>
    <p>
      <label for="springapex-specs"><strong><?php esc_html_e('Specifications', 'springapex'); ?></strong></label><br>
      <span class="description"><?php esc_html_e('One row per line: Label | Value', 'springapex'); ?></span>
      <textarea class="widefat" rows="7" id="springapex-specs" name="springapex_specs"><?php echo esc_textarea($specs); ?></textarea>
    </p>
    <p>
      <label for="springapex-materials"><strong><?php esc_html_e('Materials', 'springapex'); ?></strong></label><br>
      <span class="description"><?php esc_html_e('One row per line: Title | icon-key', 'springapex'); ?></span>
      <textarea class="widefat" rows="4" id="springapex-materials" name="springapex_materials"><?php echo esc_textarea($materials); ?></textarea>
    </p>
    <p>
      <label for="springapex-applications"><strong><?php esc_html_e('Applications', 'springapex'); ?></strong></label><br>
      <span class="description"><?php esc_html_e('One row per line: Title | icon-key', 'springapex'); ?></span>
      <textarea class="widefat" rows="5" id="springapex-applications" name="springapex_applications"><?php echo esc_textarea($applications); ?></textarea>
    </p>
    <p>
      <label for="springapex-catalog"><strong><?php esc_html_e('Catalog URL', 'springapex'); ?></strong></label><br>
      <input class="widefat" type="url" id="springapex-catalog" name="springapex_catalog_url" value="<?php echo esc_attr($catalog_url); ?>">
    </p>
    <p>
      <label><input type="checkbox" name="springapex_featured" value="1" <?php checked($featured); ?>> <?php esc_html_e('Show in Featured Products', 'springapex'); ?></label>
    </p>
    <?php
}

function springapex_meta_rows_to_text(mixed $rows, string $left_key, string $right_key): string
{
    if (!is_array($rows)) {
        return is_string($rows) ? $rows : '';
    }

    $lines = [];
    foreach ($rows as $row) {
        if (!is_array($row) || empty($row[$left_key])) {
            continue;
        }
        $lines[] = trim((string) $row[$left_key]) . ' | ' . trim((string) ($row[$right_key] ?? ''));
    }
    return implode("\n", $lines);
}

function springapex_parse_icon_rows(string $value): array
{
    $rows = [];
    foreach (preg_split('/\R/', $value) ?: [] as $line) {
        $parts = array_map('trim', explode('|', $line, 2));
        if (($parts[0] ?? '') === '') {
            continue;
        }
        $rows[] = [
            'title' => sanitize_text_field($parts[0]),
            'icon' => sanitize_key($parts[1] ?? 'spring'),
        ];
    }
    return $rows;
}

function springapex_admin_request_scalar(mixed $value): string
{
    return is_scalar($value) ? (string) wp_unslash($value) : '';
}

add_action('save_post_spring_product', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_product_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_product') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $subtitle = sanitize_textarea_field(springapex_admin_request_scalar($_POST['springapex_subtitle'] ?? ''));
    $specs_text = sanitize_textarea_field(springapex_admin_request_scalar($_POST['springapex_specs'] ?? ''));
    $materials_text = sanitize_textarea_field(springapex_admin_request_scalar($_POST['springapex_materials'] ?? ''));
    $applications_text = sanitize_textarea_field(springapex_admin_request_scalar($_POST['springapex_applications'] ?? ''));
    $catalog_url = esc_url_raw(springapex_admin_request_scalar($_POST['springapex_catalog_url'] ?? ''));

    update_post_meta($post_id, '_springapex_subtitle', $subtitle);
    update_post_meta($post_id, '_springapex_specs', springapex_parse_meta_rows($specs_text));
    update_post_meta($post_id, '_springapex_materials', springapex_parse_icon_rows($materials_text));
    update_post_meta($post_id, '_springapex_applications', springapex_parse_icon_rows($applications_text));
    update_post_meta($post_id, '_springapex_catalog_url', $catalog_url);
    update_post_meta($post_id, '_springapex_featured', isset($_POST['springapex_featured']) ? '1' : '0');
});
