<?php
/**
 * 行业方案条目的编辑界面。
 *
 * Every repeating section here used to be a textarea of pipe-delimited rows
 * (`Title | Description | icon-key | product-slug,… | image path`). It is now the
 * row editor in inc/admin/row-editor.php: one card per row, a real control per
 * field, and add / remove / reorder buttons.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('add_meta_boxes_spring_solution', static function (): void {
    add_meta_box(
        'springapex-solution-details',
        '方案内容',
        'springapex_render_solution_panel',
        'spring_solution',
        'normal',
        'high'
    );
});

/**
 * Column sets for the four repeating sections. Keyed by the request field so the
 * renderer and the save handler can never disagree about a row's shape.
 *
 * @return array<string, array<int, array{key: string, label: string, type: string, help?: string, half?: bool, default?: string}>>
 */
function springapex_solution_row_sets(): array
{
    $title = ['key' => 'title', 'label' => '标题', 'type' => 'text'];
    $text = ['key' => 'text', 'label' => '说明', 'type' => 'textarea'];
    $icon = ['key' => 'icon', 'label' => '图标', 'type' => 'icon', 'default' => 'target'];

    return [
        'springapex_solution_challenges' => [$title, $text, $icon],
        'springapex_solution_applications' => [
            $title,
            $text,
            $icon,
            ['key' => 'image', 'label' => '配图', 'type' => 'image'],
            ['key' => 'products', 'label' => '相关产品', 'type' => 'products'],
        ],
        'springapex_solution_steps' => [
            $title,
            $text,
            ['key' => 'image', 'label' => '配图', 'type' => 'image'],
        ],
        'springapex_solution_quality_items' => [$title, $text, $icon],
    ];
}

/** 请求字段 ⇒ 存储的 meta key。 */
function springapex_solution_row_meta_keys(): array
{
    return [
        'springapex_solution_challenges' => '_springapex_solution_challenges',
        'springapex_solution_applications' => '_springapex_solution_applications',
        'springapex_solution_steps' => '_springapex_solution_steps',
        'springapex_solution_quality_items' => '_springapex_solution_quality_items',
    ];
}


add_action('save_post_spring_solution', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_solution_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_solution') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id)
    ) {
        return;
    }

    $scalars = [
        '_springapex_solution_hero_title' => 'springapex_solution_hero_title',
        '_springapex_solution_challenge_intro' => 'springapex_solution_challenge_intro',
        '_springapex_solution_requirements_title' => 'springapex_solution_requirements_title',
        '_springapex_solution_requirements_text' => 'springapex_solution_requirements_text',
        '_springapex_solution_quality_image' => 'springapex_solution_quality_image',
    ];
    foreach ($scalars as $meta_key => $request_key) {
        update_post_meta($post_id, $meta_key, sanitize_textarea_field(springapex_admin_request_scalar($_POST[$request_key] ?? '')));
    }
    $quality_image_id = (int) springapex_admin_request_scalar($_POST['springapex_solution_quality_image_id'] ?? 0);
    update_post_meta(
        $post_id,
        '_springapex_solution_quality_image_id',
        get_post_type($quality_image_id) === 'attachment' ? $quality_image_id : 0
    );

    foreach (springapex_solution_row_sets() as $field => $columns) {
        $meta_key = springapex_solution_row_meta_keys()[$field] ?? '';
        if ($meta_key === '') {
            continue;
        }
        update_post_meta($post_id, $meta_key, springapex_sanitize_row_editor(
            isset($_POST[$field]) ? wp_unslash($_POST[$field]) : [],
            $columns
        ));
    }

    // Guarded by the picker's own presence marker rather than the box's nonce: with
    // no products in the site yet the picker renders nothing, and an unguarded write
    // would then wipe a list the operator cannot even see.
    if (!empty($_POST['springapex_solution_products_present'])) {
        $submitted = isset($_POST['springapex_solution_products']) && is_array($_POST['springapex_solution_products'])
            ? (array) wp_unslash($_POST['springapex_solution_products'])
            : [];
        update_post_meta($post_id, '_springapex_solution_products', springapex_sanitize_product_slugs($submitted));
    }
});

function springapex_solution_saved_details(int $post_id, array $defaults): array
{
    $fields = [
        'hero_title' => '_springapex_solution_hero_title',
        'challenge_intro' => '_springapex_solution_challenge_intro',
        'requirements_title' => '_springapex_solution_requirements_title',
        'requirements_text' => '_springapex_solution_requirements_text',
        'challenges' => '_springapex_solution_challenges',
        'application_items' => '_springapex_solution_applications',
        'products' => '_springapex_solution_products',
        'program_steps' => '_springapex_solution_steps',
        'quality_image' => '_springapex_solution_quality_image',
        'quality_image_id' => '_springapex_solution_quality_image_id',
        'quality_items' => '_springapex_solution_quality_items',
    ];
    foreach ($fields as $detail_key => $meta_key) {
        if (metadata_exists('post', $post_id, $meta_key)) {
            $defaults[$detail_key] = get_post_meta($post_id, $meta_key, true);
        }
    }
    return $defaults;
}
