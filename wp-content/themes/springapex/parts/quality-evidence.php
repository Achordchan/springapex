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
          <?php wp_nonce_field('springapex_contact', 'springapex_contact_nonce', false); ?>
        <?php endif; ?>
        <input type="hidden" name="intent" value="drawing">
        <input type="hidden" name="form_context" value="product">
        <input type="hidden" name="inquiry_type" value="Upload a Drawing" data-inquiry-type>
        <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
        <input type="hidden" name="full_name" value="Capabilities inquiry">
        <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

        <div class="sa-compression-form__modes" role="tablist" aria-label="<?php esc_attr_e('How to send requirements', 'springapex'); ?>">
          <button type="button" class="is-active" role="tab" aria-selected="true" aria-controls="capability-drawing-panel" data-compression-inquiry-mode="drawing"><?php esc_html_e('Upload a Drawing', 'springapex'); ?></button>
          <button type="button" role="tab" aria-selected="false" aria-controls="capability-dimensions-panel" data-compression-inquiry-mode="dimensions"><?php esc_html_e('Enter Dimensions Manually', 'springapex'); ?></button>
        </div>

        <div class="sa-compression-form__drawing" id="capability-drawing-panel" role="tabpanel" data-compression-drawing-panel>
          <h3><?php esc_html_e('Upload a technical drawing', 'springapex'); ?></h3>
          <p><?php esc_html_e('Dimensions are optional when a drawing is provided.', 'springapex'); ?></p>
          <label class="sa-compression-dropzone" data-compression-dropzone>
            <div class="sa-compression-dropzone__content">
              <?php echo springapex_icon('upload', 'icon'); ?>
              <strong><?php esc_html_e('Drag and drop your files here', 'springapex'); ?></strong>
              <span><?php esc_html_e('or choose files', 'springapex'); ?></span>
              <small><?php esc_html_e('Accepted files: DWG, DXF, STEP, PDF, JPG or PNG (max 10 files, 10 MB each)', 'springapex'); ?></small>
            </div>
            <ul class="sa-compression-dropzone__files" data-compression-file-list hidden></ul>
            <input type="file" name="drawing" accept=".pdf,.doc,.docx,.dwg,.dxf,.step,.stp,.iges,.igs,.jpg,.jpeg,.png" multiple data-compression-file-input>
          </label>
        </div>

        <div class="sa-compression-form__dimensions" id="capability-dimensions-panel" role="tabpanel" data-compression-dimensions-panel hidden>
          <h3><?php esc_html_e('Enter the dimensions you know', 'springapex'); ?></h3>
          <p><?php esc_html_e('All dimensions are optional; engineering will confirm any missing values.', 'springapex'); ?></p>
          <div class="sa-compression-form__row">
            <label class="field"><span><?php esc_html_e('Wire diameter (d)', 'springapex'); ?></span><input type="text" name="wire_diameter" inputmode="decimal" maxlength="80" placeholder="e.g. 1.2 mm"></label>
            <label class="field"><span><?php esc_html_e('Outside diameter (D₀)', 'springapex'); ?></span><input type="text" name="outside_diameter" inputmode="decimal" maxlength="80" placeholder="e.g. 12 mm"></label>
          </div>
          <label class="field"><span><?php esc_html_e('Free length (L₀)', 'springapex'); ?></span><input type="text" name="free_length" inputmode="decimal" maxlength="80" placeholder="e.g. 45 mm"></label>
        </div>

        <div class="sa-compression-form__row">
          <label class="field"><span><?php esc_html_e('Quantity', 'springapex'); ?></span><input type="text" name="quantity" inputmode="numeric" maxlength="80" placeholder="e.g. 5,000 pcs"></label>
          <label class="field"><span><?php esc_html_e('Material', 'springapex'); ?></span><select name="material"><option value=""><?php esc_html_e('Select material', 'springapex'); ?></option><option>Music Wire</option><option>Stainless Steel</option><option>Carbon Steel</option><option>Alloy or special material</option><option>Need engineering recommendation</option></select></label>
        </div>
        <label class="field"><span><?php esc_html_e('Other requirements', 'springapex'); ?></span><textarea name="message" rows="4" maxlength="5000" placeholder="Coating, load, end type, environment, tolerance, testing, or any additional notes."></textarea></label>
        <label class="field"><span><?php esc_html_e('Work Email', 'springapex'); ?> *</span><input type="email" name="email" maxlength="190" autocomplete="email" placeholder="name@company.com" required></label>
        <button class="btn btn-primary btn-block" type="submit" data-submit-button><?php esc_html_e('Send for Engineering Review', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></button>
        <div class="sa-turnstile-widget">
          <div
            class="cf-turnstile"
            data-sitekey="1x00000000000000000000AA"
            data-size="flexible"
            data-theme="light"
            data-language="en"
            data-action="contact-inquiry-demo"
          ></div>
        </div>
        <p class="sa-compression-form__privacy"><?php esc_html_e('Your file and project details are used only to review this inquiry.', 'springapex'); ?></p>
        <p class="form-status" data-form-status role="status" aria-live="polite" hidden></p>
      </form>
    </div>
  </div>
</section>
