<?php
/**
 * 案例「案例内容」面板：复用产品面板的左标签 + 右内容架构。
 *
 * 案例的字段全部来自原生存储：标题（title）、一句话摘要（excerpt，
 * 前台作为卡片副标题）、封面（特色图像，这里以单图选择器维护）、
 * 详情正文（post_content，案例详情页渲染）。面板只重排交互，
 * 数据读写路径不变。
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
    if (!$screen || (string) $screen->post_type !== 'spring_case') {
        return;
    }
    wp_enqueue_media();
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

function springapex_render_case_panel(object $post): void
{
    wp_nonce_field('springapex_save_case', 'springapex_case_nonce');

    $post_id = (int) $post->ID;
    $cover_id = (int) get_post_thumbnail_id($post_id);
    $cover_url = $cover_id > 0 ? (string) wp_get_attachment_image_url($cover_id, 'medium') : '';
    ?>
    <div class="sa-pp" data-sa-product-panel>
      <div class="sa-pp__col">
        <nav class="sa-pp__tabs" role="tablist" aria-label="<?php esc_attr_e('案例内容', 'springapex'); ?>">
          <button type="button" role="tab" class="sa-pp__tab is-active" data-pp-tab="basic"><?php esc_html_e('基础信息', 'springapex'); ?></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="content"><?php esc_html_e('详情正文', 'springapex'); ?></button>
        </nav>
      </div>

      <div class="sa-pp__main">
        <section class="sa-pp__panel is-active" data-pp-panel="basic" role="tabpanel">
          <h2 class="sa-pp__title"><?php esc_html_e('基础信息', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('案例名用页面上方的标题填写；下面两项显示在英文前台，请用英文填写。', 'springapex'); ?></p>

          <div class="sa-pp__field">
            <label for="springapex-case-tagline"><?php esc_html_e('一句话摘要', 'springapex'); ?></label>
            <textarea class="widefat" rows="2" id="springapex-case-tagline" name="excerpt" placeholder="<?php esc_attr_e('例如：Torsion springs for a rail door mechanism', 'springapex'); ?>"><?php echo esc_textarea((string) $post->post_excerpt); ?></textarea>
            <p class="description"><?php esc_html_e('显示在案例卡片标题下方的一行说明。', 'springapex'); ?></p>
          </div>

          <div class="sa-pp__field">
            <label><?php esc_html_e('封面图', 'springapex'); ?></label>
            <div class="sa-pp__cover" data-pp-cover data-cover-id="<?php echo esc_attr((string) $cover_id); ?>">
              <button type="button" class="sa-pp__cover-remove" data-pp-remove-cover aria-label="<?php esc_attr_e('移除封面', 'springapex'); ?>">&times;</button>
              <div class="sa-pp__cover-frame" data-pp-cover-frame role="button" tabindex="0" aria-label="<?php esc_attr_e('选择封面图', 'springapex'); ?>">
                <?php if ($cover_url !== '') : ?>
                  <img src="<?php echo esc_url($cover_url); ?>" alt="" draggable="false">
                <?php else : ?>
                  <span class="sa-pp__add-plus" aria-hidden="true">+</span>
                  <span><?php esc_html_e('选择封面图', 'springapex'); ?></span>
                <?php endif; ?>
              </div>
              <input type="hidden" name="springapex_case_cover_id" value="<?php echo esc_attr((string) $cover_id); ?>" data-pp-cover-input>
              <p class="description"><?php esc_html_e('案例卡片的配图。建议横版 1200×900、场景或应用照片。', 'springapex'); ?></p>
            </div>
          </div>
        </section>

        <section class="sa-pp__panel" data-pp-panel="content" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('详情正文', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('点开案例后的详情页内容。', 'springapex'); ?></p>
          <div class="sa-pp__editor" data-pp-editor>
            <?php wp_editor($post->post_content, 'content', [
                'textarea_rows' => 14,
                'media_buttons' => true,
                'teeny' => false,
            ]); ?>
          </div>
        </section>
      </div>
    </div>
    <?php
}

add_action('add_meta_boxes_spring_case', static function (): void {
    add_meta_box(
        'springapex-case-details',
        '案例内容',
        'springapex_render_case_panel',
        'spring_case',
        'normal',
        'high'
    );
    // 封面与摘要在面板里维护，原生框只制造第二事实来源。
    remove_meta_box('postimagediv', 'spring_case', 'side');
    remove_meta_box('postexcerpt', 'spring_case', 'normal');
});

add_action('save_post_spring_case', static function (int $post_id): void {
    $nonce = sanitize_text_field(springapex_admin_request_scalar($_POST['springapex_case_nonce'] ?? ''));
    if (
        $nonce === '' ||
        !wp_verify_nonce($nonce, 'springapex_save_case') ||
        (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||
        !current_user_can('edit_post', $post_id) ||
        !isset($_POST['springapex_case_cover_id'])
    ) {
        return;
    }

    $cover_id = (int) springapex_admin_request_scalar($_POST['springapex_case_cover_id']);
    if ($cover_id > 0 && get_post_type($cover_id) === 'attachment') {
        set_post_thumbnail($post_id, $cover_id);
    } else {
        delete_post_thumbnail($post_id);
    }
    // excerpt 字段由核心按 name="excerpt" 自行保存。
});
