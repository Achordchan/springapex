<?php
if (!defined('ABSPATH')) {
    exit;
}

$verification = springapex_get('capabilities.verification', []);
$image = (string) ($verification['image'] ?? '');
$title = (string) ($args['title'] ?? __('Verification matched to your drawing and application.', 'springapex'));
$form_action = defined('SPRINGAPEX_PREVIEW')
    ? springapex_url('/contact/')
    : admin_url('admin-post.php');
if (!$image) {
    return;
}
?>
<section class="section sa-evidence sa-evidence--custom">
  <div class="container container-wide">
    <div class="sa-evidence__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('QUALITY EVIDENCE', 'springapex'); ?></p>
        <h2><?php echo esc_html($title); ?></h2>
      </div>
      <p><?php esc_html_e('Send a drawing or enter the dimensions you know — engineering confirms geometry, materials and verification scope before quotation.', 'springapex'); ?></p>
    </div>

    <div class="sa-compression-review__panel">
      <div class="sa-compression-review__diagram" data-reveal="up">
        <div class="sa-compression-review__guide" data-compression-review-guide="drawing">
          <h3><?php esc_html_e('Send the drawing you already have.', 'springapex'); ?></h3>
          <p><?php esc_html_e('A PDF, CAD file, sketch or clear reference image is enough to begin an engineering review.', 'springapex'); ?></p>
          <dl class="sa-compression-review__checklist">
            <div><dt>01</dt><dd><strong><?php esc_html_e('Geometry', 'springapex'); ?></strong><span><?php esc_html_e('Key dimensions or installation space', 'springapex'); ?></span></dd></div>
            <div><dt>02</dt><dd><strong><?php esc_html_e('Working point', 'springapex'); ?></strong><span><?php esc_html_e('Required load, travel or operating position', 'springapex'); ?></span></dd></div>
            <div><dt>03</dt><dd><strong><?php esc_html_e('Conditions', 'springapex'); ?></strong><span><?php esc_html_e('Material, temperature or corrosion exposure', 'springapex'); ?></span></dd></div>
            <div><dt>04</dt><dd><strong><?php esc_html_e('Order', 'springapex'); ?></strong><span><?php esc_html_e('Prototype or production quantity', 'springapex'); ?></span></dd></div>
          </dl>
          <p class="sa-compression-review__guide-note"><?php esc_html_e('No drawing available? Choose Enter Dimensions Manually.', 'springapex'); ?></p>
        </div>

        <div class="sa-compression-review__guide" data-compression-review-guide="dimensions" hidden>
          <h3><?php esc_html_e('Three dimensions are enough to start.', 'springapex'); ?></h3>
          <p><?php esc_html_e('Enter any values you know. Engineering will confirm the remaining geometry before quotation.', 'springapex'); ?></p>
          <figure class="sa-compression-review__dimension-figure">
            <?php echo springapex_image($image, (string) ($verification['image_alt'] ?? __('Spring dimension reference diagram', 'springapex')), [
                'width' => 840,
                'height' => 350,
                'sizes' => '(max-width: 860px) 88vw, 34vw',
            ]); ?>
          </figure>
          <dl class="sa-compression-review__dimension-list">
            <div><dt>d</dt><dd><strong><?php esc_html_e('Wire diameter', 'springapex'); ?></strong><span><?php esc_html_e('Thickness of the spring wire', 'springapex'); ?></span></dd></div>
            <div><dt>D<sub>0</sub></dt><dd><strong><?php esc_html_e('Outside diameter', 'springapex'); ?></strong><span><?php esc_html_e('Maximum diameter across the coil', 'springapex'); ?></span></dd></div>
            <div><dt>L<sub>0</sub></dt><dd><strong><?php esc_html_e('Free length', 'springapex'); ?></strong><span><?php esc_html_e('Unloaded overall spring length', 'springapex'); ?></span></dd></div>
          </dl>
        </div>
      </div>

      <form class="sa-compression-form" data-contact-form data-compression-inquiry method="post" action="<?php echo esc_url($form_action); ?>" enctype="multipart/form-data" novalidate data-reveal="up">
        <input type="hidden" name="action" value="springapex_contact">
        <?php if (defined('SPRINGAPEX_PREVIEW')) : ?>
          <input type="hidden" name="springapex_contact_nonce" value="">
        <?php else : ?>
          <?php wp_nonce_field('springapex_contact_product', 'springapex_contact_nonce', false); ?>
        <?php endif; ?>
        <input type="hidden" name="intent" value="drawing">
        <input type="hidden" name="form_context" value="product">
        <input type="hidden" name="source" value="<?php echo esc_attr((string) get_queried_object_id()); ?>">
        <?php
        // 尺寸字段被「表单设置」标为必填时：输入框补 required，表单默认落在
        // 「Enter Dimensions」模式（面板初始可见）——必填项藏在 hidden 面板里
        // 会被 checkValidity 拦住且聚焦不到。
        $capability_dimension_required = array_intersect(
            springapex_form_required_ids('product'),
            ['wire_diameter', 'outside_diameter', 'free_length']
        );
        // 尺寸输入按 schema 成员资格渲染：映射被运营者删除后不再输出，
        // 否则访客填了值也会被服务端按 schema 丢弃。
        $capability_schema_ids = array_map(
            static fn (array $field): string => (string) $field['id'],
            springapex_form_schema()['product']['fields'] ?? []
        );
        $capability_has_dimension = [
            'wire_diameter' => in_array('wire_diameter', $capability_schema_ids, true),
            'outside_diameter' => in_array('outside_diameter', $capability_schema_ids, true),
            'free_length' => in_array('free_length', $capability_schema_ids, true),
        ];
        $capability_any_dimension = array_filter($capability_has_dimension) !== [];
        $capability_dimensions_default = $capability_dimension_required !== [];
        $capability_req = static fn (string $id): string => in_array($id, $capability_dimension_required, true) ? ' required' : '';
        $capability_star = static fn (string $id): string => in_array($id, $capability_dimension_required, true) ? ' *' : '';
        ?>
        <input type="hidden" name="inquiry_type" value="<?php echo $capability_dimensions_default ? 'Request a Quote' : 'Upload a Drawing'; ?>" data-inquiry-type>
        <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
        <input type="hidden" name="full_name" value="Capabilities inquiry">
        <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

        <div class="sa-compression-form__modes" role="tablist" aria-label="<?php esc_attr_e('How to send requirements', 'springapex'); ?>">
          <button type="button" class="<?php echo $capability_dimensions_default ? '' : 'is-active'; ?>" role="tab" aria-selected="<?php echo $capability_dimensions_default ? 'false' : 'true'; ?>" aria-controls="capability-drawing-panel" data-compression-inquiry-mode="drawing"<?php echo $capability_dimensions_default ? ' disabled title="Required dimensions must be entered directly"' : ''; ?>><?php esc_html_e('Upload a Drawing', 'springapex'); ?></button>
          <?php if ($capability_any_dimension) : ?>
            <button type="button" class="<?php echo $capability_dimensions_default ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo $capability_dimensions_default ? 'true' : 'false'; ?>" aria-controls="capability-dimensions-panel" data-compression-inquiry-mode="dimensions"><?php esc_html_e('Enter Dimensions Manually', 'springapex'); ?></button>
          <?php endif; ?>
        </div>

        <div class="sa-compression-form__drawing" id="capability-drawing-panel" role="tabpanel" data-compression-drawing-panel<?php echo $capability_dimensions_default ? ' hidden' : ''; ?>>
          <h3><?php esc_html_e('Upload a technical drawing', 'springapex'); ?></h3>
          <p><?php esc_html_e('Dimensions are optional when a drawing is provided.', 'springapex'); ?></p>
          <label class="sa-compression-dropzone" data-compression-dropzone>
            <div class="sa-compression-dropzone__content">
              <?php echo springapex_icon('upload', 'icon'); ?>
              <strong><?php esc_html_e('Drag and drop your files here', 'springapex'); ?></strong>
              <span><?php esc_html_e('or choose files', 'springapex'); ?></span>
              <small><?php esc_html_e('Accepted files: DWG, DXF, STEP, PDF, JPG or PNG (max 10 files, 10 MB total)', 'springapex'); ?></small>
            </div>
            <ul class="sa-compression-dropzone__files" data-compression-file-list hidden></ul>
            <input type="file" name="drawing[]" accept=".pdf,.doc,.docx,.dwg,.dxf,.step,.stp,.iges,.igs,.jpg,.jpeg,.png" multiple data-compression-file-input>
          </label>
        </div>

        <div class="sa-compression-form__dimensions" id="capability-dimensions-panel" role="tabpanel" data-compression-dimensions-panel<?php echo $capability_dimensions_default ? '' : ' hidden'; ?>>
          <?php if ($capability_any_dimension) : ?>
            <h3><?php esc_html_e('Enter the dimensions you know', 'springapex'); ?></h3>
            <?php if ($capability_dimension_required !== []) : ?>
              <p><?php esc_html_e('Required dimensions are marked with *; engineering will confirm any missing values.', 'springapex'); ?></p>
            <?php else : ?>
              <p><?php esc_html_e('All dimensions are optional; engineering will confirm any missing values.', 'springapex'); ?></p>
            <?php endif; ?>
            <div class="sa-compression-form__row">
              <?php if ($capability_has_dimension['wire_diameter']) : ?>
                <label class="field"><span><?php esc_html_e('Wire diameter (d)', 'springapex'); ?><?php echo $capability_star('wire_diameter'); ?></span><input type="text" name="springapex_field_wire_diameter" inputmode="decimal" maxlength="80" placeholder="e.g. 1.2 mm"<?php echo $capability_req('wire_diameter'); ?>></label>
              <?php endif; ?>
              <?php if ($capability_has_dimension['outside_diameter']) : ?>
                <label class="field"><span><?php esc_html_e('Outside diameter (D₀)', 'springapex'); ?><?php echo $capability_star('outside_diameter'); ?></span><input type="text" name="springapex_field_outside_diameter" inputmode="decimal" maxlength="80" placeholder="e.g. 12 mm"<?php echo $capability_req('outside_diameter'); ?>></label>
              <?php endif; ?>
            </div>
            <?php if ($capability_has_dimension['free_length']) : ?>
              <label class="field"><span><?php esc_html_e('Free length (L₀)', 'springapex'); ?><?php echo $capability_star('free_length'); ?></span><input type="text" name="springapex_field_free_length" inputmode="decimal" maxlength="80" placeholder="e.g. 45 mm"<?php echo $capability_req('free_length'); ?>></label>
            <?php endif; ?>
          <?php endif; ?>
        </div>

        <?php
        // 能力页表单字段按 schema 渲染（尺寸三行在上方 dimensions 面板内
        // 已按固定标签渲染，此处跳过三个尺寸 id 避免重复；email 为必填
        // 字段且本页无其他 email 输入，必须由 schema 渲染出来）。走共享
        // 渲染函数以获得 .sa-schema-fields 网格包裹——is-half 半宽样式
        // 只对网格直接子元素生效。
        springapex_render_form_schema_fields('product', 'field', '', ['wire_diameter', 'outside_diameter', 'free_length']);
        ?>
        <button class="btn btn-primary btn-block" type="submit" data-submit-button><?php esc_html_e('Send for Engineering Review', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></button>
        <?php if (springapex_form_turnstile_enabled('product')) : ?>
        <div class="sa-turnstile-widget">
          <div
            class="cf-turnstile"
            data-sitekey="<?php echo esc_attr(springapex_turnstile_site_key()); ?>"
            data-size="flexible"
            data-theme="light"
            data-language="en"
            data-action="capability-inquiry"
          ></div>
          <?php echo springapex_turnstile_noscript(); ?>
        </div>
        <?php endif; ?>
        <p class="sa-compression-form__privacy"><?php esc_html_e('Your file and project details are used only to review this inquiry.', 'springapex'); ?></p>
        <p class="form-status" data-form-status role="status" aria-live="polite" hidden></p>
      </form>
    </div>
  </div>
</section>
