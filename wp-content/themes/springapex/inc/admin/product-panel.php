<?php
/**
 * 产品编辑「产品数据」面板（WooCommerce 式）。
 *
 * 单个全宽面板 + 左侧标签导航，替代「原生编辑器在顶 + 元框字段在底」
 * 的拼装布局：结构固定、不依赖元框拖拽。所有表单字段名与保存逻辑
 * （inc/post-types.php 的 save_post_spring_product）保持不变——这里是
 * 纯渲染层重做，无数据迁移。
 *
 * 标签：基础信息 / 产品图（网格多选 + 拖拽排序）/ 关键参数 / 详情正文
 * （原生 wp_editor 挪入面板，CPT 已移除 editor support）。
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
    if (!$screen || (string) $screen->post_type !== 'spring_product') {
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

// 面板编辑器 iframe 的内容样式（产品/方案/案例/新闻共用）：让图库区块等
// 以接近前台的形态展示，避免「编辑器逐张堆叠、前台两列网格」的困惑。
add_filter('mce_css', static function (string $mce_css): string {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $panel_types = ['spring_product', 'spring_solution', 'spring_case', 'spring_news'];
    if (
        function_exists('get_current_screen') &&
        $screen &&
        in_array((string) $screen->post_type, $panel_types, true)
    ) {
        $mce_css .= ',' . SPRINGAPEX_URI . '/assets/css/editor-content.css?ver=' . rawurlencode(SPRINGAPEX_VERSION);
    }

    return $mce_css;
});

/**
 * 面板内容与旧渲染（springapex_render_product_meta_box）取值口径一致：
 * 未保存过的字段回退到 seed，保证运营者第一眼看到的就是前台在显示的内容。
 */
function springapex_render_product_panel(object $post): void
{
    wp_nonce_field('springapex_save_product', 'springapex_product_nonce');

    $post_id = (int) $post->ID;
    $seed = springapex_product_seed((string) ($post->post_name ?? '')) ?? [];
    $value = static function (string $key, mixed $fallback) use ($post_id): mixed {
        return metadata_exists('post', $post_id, $key) ? get_post_meta($post_id, $key, true) : $fallback;
    };

    $subtitle = (string) $value('_springapex_subtitle', $seed['subtitle'] ?? '');
    $featured = (bool) $value('_springapex_featured', !empty($seed['featured']));
    $mega_menu = (bool) $value('_springapex_mega_menu', true);
    $menu_order = (int) $post->menu_order;

    // 与前台 springapex_product_from_post() 使用相同的有效性口径：
    // 已删除的附件、空行和损坏行不应阻止特色图 / seed 画廊兜底。
    $normalize_gallery = static function (array $rows): array {
        $normalized = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }
            $image_id = (int) ($row['image_id'] ?? 0);
            $image = trim((string) ($row['image'] ?? ''));
            if ($image_id > 0 && wp_get_attachment_image_url($image_id, 'medium') === false) {
                $image_id = 0;
            }
            if (
                $image !== '' &&
                !is_file(SPRINGAPEX_DIR . '/assets/images/' . ltrim($image, '/'))
            ) {
                $image = '';
            }
            if ($image_id > 0 || $image !== '') {
                $normalized[] = ['image_id' => (string) $image_id, 'image' => $image];
            }
        }
        return $normalized;
    };

    $thumbnail_id = (int) get_post_thumbnail_id($post_id);
    $gallery = metadata_exists('post', $post_id, '_springapex_gallery')
        ? $normalize_gallery((array) $value('_springapex_gallery', []))
        : [];
    if ($gallery === [] && $thumbnail_id > 0) {
        $gallery = $normalize_gallery([['image_id' => (string) $thumbnail_id, 'image' => '']]);
    }
    if ($gallery === []) {
        $gallery = $normalize_gallery((array) ($seed['gallery'] ?? []));
    }

    $specs = (array) $value('_springapex_specs', $seed['specs'] ?? []);
    $specs = array_values(array_filter($specs, 'is_array'));
    ?>
    <div class="sa-pp" data-sa-product-panel>
      <div class="sa-pp__col">
        <nav class="sa-pp__tabs" role="tablist" aria-label="<?php esc_attr_e('产品数据', 'springapex'); ?>">
          <button type="button" role="tab" class="sa-pp__tab is-active" data-pp-tab="basic"><?php esc_html_e('基础信息', 'springapex'); ?></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="gallery"><?php esc_html_e('产品图', 'springapex'); ?><span class="sa-pp__count" data-pp-count="gallery"><?php echo esc_html((string) count($gallery)); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="specs"><?php esc_html_e('关键参数', 'springapex'); ?><span class="sa-pp__count" data-pp-count="specs"><?php echo esc_html((string) count($specs)); ?></span></button>
          <button type="button" role="tab" class="sa-pp__tab" data-pp-tab="content"><?php esc_html_e('详情正文', 'springapex'); ?></button>
        </nav>
      </div>

      <div class="sa-pp__main">
        <!-- 基础信息 -->
        <section class="sa-pp__panel is-active" data-pp-panel="basic" role="tabpanel">
          <h2 class="sa-pp__title"><?php esc_html_e('基础信息', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('产品名用页面上方的标题填写；下面两项显示在英文前台，请用英文填写。', 'springapex'); ?></p>

          <div class="sa-pp__field">
            <label for="springapex-subtitle"><?php esc_html_e('标题下的一句话', 'springapex'); ?></label>
            <textarea class="widefat" rows="2" id="springapex-subtitle" name="springapex_subtitle"><?php echo esc_textarea($subtitle); ?></textarea>
            <p class="description"><?php esc_html_e('显示在产品名下方的一行介绍语。', 'springapex'); ?></p>
          </div>

          <div class="sa-pp__field">
            <label><?php esc_html_e('首页推荐', 'springapex'); ?></label>
            <label class="sa-pp__switch">
              <input type="checkbox" name="springapex_featured" value="1" <?php checked($featured); ?>>
              <span class="sa-pp__switch-track" aria-hidden="true"></span>
              <span class="sa-pp__switch-text"><?php esc_html_e('在首页的产品推荐区域显示这个产品', 'springapex'); ?></span>
            </label>
          </div>

          <div class="sa-pp__field">
            <label><?php esc_html_e('大菜单展示', 'springapex'); ?></label>
            <label class="sa-pp__switch">
              <input type="checkbox" name="springapex_mega_menu" value="1" <?php checked($mega_menu); ?>>
              <span class="sa-pp__switch-track" aria-hidden="true"></span>
              <span class="sa-pp__switch-text"><?php esc_html_e('在页头 Products 大菜单中显示这个产品', 'springapex'); ?></span>
            </label>
            <p class="description"><?php esc_html_e('大菜单按「菜单排序」最多显示 12 个产品；未入选的产品仍会出现在产品列表页和页脚。', 'springapex'); ?></p>
          </div>

          <div class="sa-pp__field">
            <label for="springapex-menu-order"><?php esc_html_e('菜单排序', 'springapex'); ?></label>
            <input type="number" class="small-text" min="0" step="1" id="springapex-menu-order" name="menu_order" value="<?php echo esc_attr((string) $menu_order); ?>">
            <p class="description"><?php esc_html_e('数字越小越靠前。这个顺序同时决定首页推荐、产品列表页和页脚里产品的先后。', 'springapex'); ?></p>
          </div>
        </section>

        <!-- 产品图 -->
        <section class="sa-pp__panel" data-pp-panel="gallery" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('产品图', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('第一张是页面顶部的大图与主图（自动同步为特色图像），其余作为可切换缩略图。拖拽缩略图调整顺序。建议正方形、白底、1200×1200。', 'springapex'); ?></p>

          <div class="sa-pp__gallery" data-pp-gallery>
            <?php foreach ($gallery as $index => $row) : ?>
              <?php springapex_render_product_panel_gallery_item((int) $index, (array) $row); ?>
            <?php endforeach; ?>
            <button type="button" class="sa-pp__add" data-pp-add-image>
              <span class="sa-pp__add-plus" aria-hidden="true">+</span>
              <span><?php esc_html_e('添加图片', 'springapex'); ?></span>
            </button>
          </div>
        </section>

        <!-- 关键参数 -->
        <section class="sa-pp__panel" data-pp-panel="specs" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('顶部关键参数', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('显示在页面顶部的数据框（前 3 行生效）。左列是项目名（例如 Wire Diameter），右列是数值或范围（例如 0.1 – 60 mm）。', 'springapex'); ?></p>

          <div class="sa-pp__specs" data-pp-specs>
            <?php foreach ($specs as $index => $row) : ?>
              <?php springapex_render_product_panel_spec_row((int) $index, (string) ($row['label'] ?? ''), (string) ($row['value'] ?? '')); ?>
            <?php endforeach; ?>
            <button type="button" class="sa-pp__spec-add button" data-pp-add-spec><?php esc_html_e('添加一行参数', 'springapex'); ?></button>
          </div>
        </section>

        <!-- 详情正文 -->
        <section class="sa-pp__panel" data-pp-panel="content" role="tabpanel" hidden>
          <h2 class="sa-pp__title"><?php esc_html_e('详情正文', 'springapex'); ?></h2>
          <p class="sa-pp__desc"><?php esc_html_e('产品详情页的正文内容（原「Product Details」区）。', 'springapex'); ?></p>
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

/**
 * 单张产品图缩略块。字段名与旧行编辑器一致：springapex_gallery[i][image_id] / [i][image]。
 */
function springapex_render_product_panel_gallery_item(int $index, array $row): void
{
    $attachment_id = (int) ($row['image_id'] ?? 0);
    $legacy = (string) ($row['image'] ?? '');
    $preview = $attachment_id > 0 ? (string) wp_get_attachment_image_url($attachment_id, 'medium') : '';
    if ($preview === '' && $legacy !== '') {
        $preview = (string) springapex_asset('assets/images/' . ltrim($legacy, '/'));
    }
    ?>
    <figure class="sa-pp__shot" data-pp-shot draggable="true">
      <button type="button" class="sa-pp__shot-remove" data-pp-remove-shot aria-label="<?php esc_attr_e('移除这张图', 'springapex'); ?>">&times;</button>
      <span class="sa-pp__shot-badge" data-pp-badge><?php esc_html_e('主图', 'springapex'); ?></span>
      <div class="sa-pp__shot-frame">
        <?php if ($preview !== '') : ?>
          <img src="<?php echo esc_url($preview); ?>" alt="" draggable="false">
        <?php else : ?>
          <span class="sa-pp__shot-empty"><?php esc_html_e('无预览', 'springapex'); ?></span>
        <?php endif; ?>
      </div>
      <input type="hidden" name="springapex_gallery[<?php echo esc_attr((string) $index); ?>][image_id]" value="<?php echo esc_attr((string) $attachment_id); ?>" data-pp-image-id>
      <input type="hidden" name="springapex_gallery[<?php echo esc_attr((string) $index); ?>][image]" value="<?php echo esc_attr($legacy); ?>" data-pp-image-legacy>
    </figure>
    <?php
}

/**
 * 单行关键参数。字段名与旧行编辑器一致：springapex_specs[i][label] / [i][value]。
 */
function springapex_render_product_panel_spec_row(int $index, string $label, string $value): void
{
    ?>
    <div class="sa-pp__spec" data-pp-spec>
      <input type="text" name="springapex_specs[<?php echo esc_attr((string) $index); ?>][label]" value="<?php echo esc_attr($label); ?>" placeholder="<?php esc_attr_e('项目，如 Wire Diameter', 'springapex'); ?>" data-pp-spec-label>
      <input type="text" name="springapex_specs[<?php echo esc_attr((string) $index); ?>][value]" value="<?php echo esc_attr($value); ?>" placeholder="<?php esc_attr_e('数值或范围，如 0.1 – 60 mm', 'springapex'); ?>" data-pp-spec-value>
      <button type="button" class="sa-pp__spec-remove" data-pp-remove-spec aria-label="<?php esc_attr_e('删除这行', 'springapex'); ?>">&times;</button>
    </div>
    <?php
}
