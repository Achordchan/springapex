<?php
/**
 * 行业方案「方案内容」面板：复用产品面板的左标签 + 右内容架构。
 *
 * 各区块的行编辑器/选品器控件原样保留（inc/admin/row-editor.php、
 * inc/product-picker.php），只把它们装进固定的标签结构——字段名与
 * 保存逻辑（inc/solution-meta.php 的 save_post_spring_solution）
 * 保持不变，纯渲染层重做。
 *
 * 方案正文（post_content）前台从不渲染，CPT 已移除 editor support。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_enqueue_scripts', static function (string $hook): void {
    if (!in_array($hook, ['post.php', 'post-new.php'], true)) {
        return;
    }
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || (string) $screen->post_type !== 'spring_solution') {
        return;
    }
    // 与产品面板共用一套面板样式与标签交互（句柄相同不会重复入队）。
    wp_enqueue_style(
        'springapex-product-panel',
        SPRINGAPEX_URI . '/assets/css/product-panel.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-product-panel',
        SPRINGAPEX_URI . '/assets/js/product-panel.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
});

function springapex_render_solution_panel(object $post): void
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
    $count = static fn (string $key): string => (string) count(array_filter($rows[$key], 'is_array'));

    wp_nonce_field('springapex_save_solution', 'springapex_solution_nonce');
    ?>
    <p class="description">每个行业用的是同一套版式。某一块留空，公开页面上就不显示这一块。这里的文字会原样出现在前台，所以请用英文填写。</p>

    <div class="sa-pp" data-sa-product-panel>
      <div class="sa-pp__col">
        <nav class="sa-pp__tabs" role="tablist" aria-label="<?php esc_attr_e('方案内容', 'springapex'); ?>">
          <button type="button" role="tab" class="sa-pp__tab is-active" data-pp-tab="basic"><?php esc_html_e('基础信息', 'springapex'); ?></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="challenges"><?php esc_html_e('行业要求', 'springapex'); ?><span class="sa-pp__count"><?php echo esc_html($count('springapex_solution_challenges')); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="applications"><?php esc_html_e('典型应用', 'springapex'); ?><span class="sa-pp__count"><?php echo esc_html($count('springapex_solution_applications')); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="products"><?php esc_html_e('推荐产品', 'springapex'); ?><span class="sa-pp__count"><?php echo esc_html((string) count($products)); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="steps"><?php esc_html_e('服务流程', 'springapex'); ?><span class="sa-pp__count"><?php echo esc_html($count('springapex_solution_steps')); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="quality"><?php esc_html_e('质量佐证', 'springapex'); ?><span class="sa-pp__count"><?php echo esc_html($count('springapex_solution_quality_items')); ?></span></button>
        </nav>
      </div>

      <div class="sa-pp__main">
        <section class="sa-pp__panel is-active" data-pp-panel="basic" role="tabpanel">
          <h2 class="sa-pp__title"><?php esc_html_e('基础信息', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('方案名用页面上方的标题填写；行业名与图标由系统按固定结构提供。以下文字显示在英文前台，请用英文填写。', 'springapex'); ?></p>

          <div class="sa-pp__field">
            <label for="springapex-solution-hero-title"><?php esc_html_e('大标题', 'springapex'); ?></label>
            <textarea class="widefat" rows="2" id="springapex-solution-hero-title" name="springapex_solution_hero_title"><?php echo esc_textarea($hero_title); ?></textarea>
          </div>

          <div class="sa-pp__field">
            <label for="springapex-solution-intro"><?php esc_html_e('大标题下的一段话', 'springapex'); ?></label>
            <textarea class="widefat" rows="3" id="springapex-solution-intro" name="springapex_solution_challenge_intro"><?php echo esc_textarea($challenge_intro); ?></textarea>
          </div>

          <div class="sa-pp__field">
            <label for="springapex-solution-requirements-title"><?php esc_html_e('行业要求：小标题', 'springapex'); ?></label>
            <input class="widefat" type="text" id="springapex-solution-requirements-title" name="springapex_solution_requirements_title" value="<?php echo esc_attr($requirements_title); ?>">
          </div>

          <div class="sa-pp__field">
            <label for="springapex-solution-requirements-text"><?php esc_html_e('行业要求：引导语', 'springapex'); ?></label>
            <textarea class="widefat" rows="2" id="springapex-solution-requirements-text" name="springapex_solution_requirements_text"><?php echo esc_textarea($requirements_text); ?></textarea>
          </div>
        </section>

        <section class="sa-pp__panel" data-pp-panel="challenges" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('行业要求', 'springapex'); ?></h2>
          <?php springapex_render_row_editor(
              'springapex_solution_challenges',
              $rows['springapex_solution_challenges'],
              $sets['springapex_solution_challenges'],
              '这个行业对弹簧的要求，一条一张卡片，显示在「行业要求」小标题下面。'
          ); ?>
        </section>

        <section class="sa-pp__panel" data-pp-panel="applications" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('典型应用', 'springapex'); ?></h2>
          <?php springapex_render_row_editor(
              'springapex_solution_applications',
              $rows['springapex_solution_applications'],
              $sets['springapex_solution_applications'],
              '这个行业里弹簧用在哪些地方。每条可以配一张图（不配就显示图标），并勾选相关的产品，前台会在卡片下方列成链接。'
          ); ?>
        </section>

        <section class="sa-pp__panel" data-pp-panel="products" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('推荐产品', 'springapex'); ?></h2>
          <?php springapex_render_product_picker(
              'springapex_solution_products',
              $products,
              '勾选的产品会显示在这个行业方案页面下方的「Recommended products」里。不勾就不显示这一块。'
          ); ?>
        </section>

        <section class="sa-pp__panel" data-pp-panel="steps" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('服务流程', 'springapex'); ?></h2>
          <?php springapex_render_row_editor(
              'springapex_solution_steps',
              $rows['springapex_solution_steps'],
              $sets['springapex_solution_steps'],
              '从询价到交付的步骤，按这里的先后顺序显示。用上下箭头调整顺序。'
          ); ?>
        </section>

        <section class="sa-pp__panel" data-pp-panel="quality" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('质量佐证', 'springapex'); ?></h2>
          <div class="sa-pp__field">
            <label><?php esc_html_e('这一块的配图', 'springapex'); ?></label>
            <?php springapex_render_row_editor_image(
                'springapex-solution-quality-image',
                'springapex_solution_quality_image',
                ['image' => $quality_image, 'image_id' => $quality_image_id]
            ); ?>
          </div>
          <?php springapex_render_row_editor(
              'springapex_solution_quality_items',
              $rows['springapex_solution_quality_items'],
              $sets['springapex_solution_quality_items'],
              '检验和测试方面的说明，显示在上面这张图旁边。'
          ); ?>
        </section>
      </div>
    </div>
    <?php
}
