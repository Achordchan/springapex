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
        '这个行业方案页面的内容',
        'springapex_render_solution_meta_box',
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

function springapex_render_solution_meta_box(object $post): void
{
    $post_id = (int) $post->ID;
    $slug = (string) ($post->post_name ?? '');
    $defaults = springapex_get('solution_details.' . $slug, []);
    $value = static function (string $key, mixed $fallback) use ($post_id): mixed {
        return metadata_exists('post', $post_id, $key) ? get_post_meta($post_id, $key, true) : $fallback;
    };

    $hero_title = (string) $value('_springapex_solution_hero_title', $defaults['hero_title'] ?? '');
    $challenge_intro = (string) $value('_springapex_solution_challenge_intro', $defaults['challenge_intro'] ?? '');
    $requirements_title = (string) $value('_springapex_solution_requirements_title', $defaults['requirements_title'] ?? '');
    $requirements_text = (string) $value('_springapex_solution_requirements_text', $defaults['requirements_text'] ?? '');
    $products = array_values(array_filter(array_map(
        'strval',
        (array) $value('_springapex_solution_products', $defaults['products'] ?? [])
    )));
    $quality_image = (string) $value('_springapex_solution_quality_image', $defaults['quality_image'] ?? '');
    $quality_image_id = (int) $value('_springapex_solution_quality_image_id', 0);

    $sets = springapex_solution_row_sets();
    $rows = [
        'springapex_solution_challenges' => (array) $value('_springapex_solution_challenges', $defaults['challenges'] ?? []),
        'springapex_solution_applications' => (array) $value('_springapex_solution_applications', $defaults['application_items'] ?? []),
        'springapex_solution_steps' => (array) $value('_springapex_solution_steps', $defaults['program_steps'] ?? []),
        'springapex_solution_quality_items' => (array) $value('_springapex_solution_quality_items', $defaults['quality_items'] ?? []),
    ];

    wp_nonce_field('springapex_save_solution', 'springapex_solution_nonce');
    ?>
    <p class="description">每个行业用的是同一套版式。某一块留空，公开页面上就不显示这一块。这里的文字会原样出现在前台，所以请用英文填写。</p>

    <p><label for="springapex-solution-hero-title"><strong>大标题</strong></label><br>
      <textarea class="widefat" rows="2" id="springapex-solution-hero-title" name="springapex_solution_hero_title"><?php echo esc_textarea($hero_title); ?></textarea></p>
    <p><label for="springapex-solution-intro"><strong>大标题下的一段话</strong></label><br>
      <textarea class="widefat" rows="3" id="springapex-solution-intro" name="springapex_solution_challenge_intro"><?php echo esc_textarea($challenge_intro); ?></textarea></p>
    <p><label for="springapex-solution-requirements-title"><strong>行业要求：小标题</strong></label><br>
      <input class="widefat" type="text" id="springapex-solution-requirements-title" name="springapex_solution_requirements_title" value="<?php echo esc_attr($requirements_title); ?>"></p>
    <p><label for="springapex-solution-requirements-text"><strong>行业要求：引导语</strong></label><br>
      <textarea class="widefat" rows="2" id="springapex-solution-requirements-text" name="springapex_solution_requirements_text"><?php echo esc_textarea($requirements_text); ?></textarea></p>

    <h3>行业要求</h3>
    <?php springapex_render_row_editor(
        'springapex_solution_challenges',
        $rows['springapex_solution_challenges'],
        $sets['springapex_solution_challenges'],
        '这个行业对弹簧的要求，一条一张卡片，显示在「行业要求」小标题下面。'
    ); ?>

    <h3>典型应用</h3>
    <?php springapex_render_row_editor(
        'springapex_solution_applications',
        $rows['springapex_solution_applications'],
        $sets['springapex_solution_applications'],
        '这个行业里弹簧用在哪些地方。每条可以配一张图（不配就显示图标），并勾选相关的产品，前台会在卡片下方列成链接。'
    ); ?>

    <h3>推荐产品</h3>
    <?php springapex_render_product_picker(
        'springapex_solution_products',
        $products,
        '勾选的产品会显示在这个行业方案页面下方的「Recommended products」里。不勾就不显示这一块。'
    ); ?>

    <h3>服务流程</h3>
    <?php springapex_render_row_editor(
        'springapex_solution_steps',
        $rows['springapex_solution_steps'],
        $sets['springapex_solution_steps'],
        '从询价到交付的步骤，按这里的先后顺序显示。用上下箭头调整顺序。'
    ); ?>

    <h3>质量佐证</h3>
    <p><strong>这一块的配图</strong></p>
    <?php springapex_render_row_editor_image(
        'springapex-solution-quality-image',
        'springapex_solution_quality_image',
        ['image' => $quality_image, 'image_id' => $quality_image_id]
    ); ?>
    <?php springapex_render_row_editor(
        'springapex_solution_quality_items',
        $rows['springapex_solution_quality_items'],
        $sets['springapex_solution_quality_items'],
        '检验和测试方面的说明，显示在上面这张图旁边。'
    ); ?>
    <?php
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
