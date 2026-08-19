<?php
if (!defined('ABSPATH')) {
    exit;
}

$product = is_array($args['product'] ?? null) ? $args['product'] : [];
$slug = sanitize_key((string) ($args['slug'] ?? 'compression-springs'));
$form_action = defined('SPRINGAPEX_PREVIEW')
    ? springapex_url('/contact/')
    : admin_url('admin-post.php');
$quality_url = springapex_url('/about/#quality-certificates');
$traceability_url = springapex_url('/manufacturing-videos/#material-traceability');
$resources_url = springapex_url('/resources/');
$details_source = trim((string) ($product['overview'] ?? ''));
$has_product_details = defined('SPRINGAPEX_PREVIEW') || $details_source !== '';
// Hero copy is driven by the product's own editable fields so every product can
// reuse this layout: the lede comes from 「小标题」, and the three stat boxes from
// the first rows of 「技术参数」.
$hero_lede = trim((string) ($product['subtitle'] ?? ''));
$hero_facts = array_values(array_filter(array_slice((array) ($product['specs'] ?? []), 0, 3), 'is_array'));

// Hero gallery comes from the product's own editable image list, so backend and
// front end always show the same images. The first image is the big hero picture;
// the rest become switchable thumbnails (strip hidden when there is only one).
// Each item is a filename string or the ['id'=>…,'file'=>…] shape that
// springapex_image() accepts directly.
$hero_gallery = array_values(array_filter((array) ($product['gallery'] ?? []), static fn ($item): bool => $item !== '' && $item !== []));
if ($hero_gallery === []) {
    $hero_gallery = [$product['image'] ?? 'product-compression-detail-v4.png'];
}
$hero_primary_image = $hero_gallery[0];
$quality_steps = [
    ['step' => '1', 'title' => 'Drawing Review', 'text' => 'Drawing, load and material reviewed.'],
    ['step' => '2', 'title' => 'First Article', 'text' => 'Sample dimensions and force verified.'],
    ['step' => '3', 'title' => 'In-process Inspection', 'text' => 'Critical dimensions checked during production.'],
    ['step' => '4', 'title' => 'Final Report', 'text' => 'Inspection and traceability records issued.'],
];
$delivery_items = [
    ['image' => 'product-detail/compression-packed-springs.jpg', 'icon' => 'box', 'title' => 'Protected Packaging', 'text' => 'Oil paper, VCI bags, bubble film and reinforced cartons.'],
    ['image' => 'product-detail/compression-custom-crates.jpg', 'icon' => 'cubes', 'title' => 'Custom Crates', 'text' => 'Plywood crates for heavy loads and long-distance shipping.'],
    ['image' => 'product-detail/compression-parts-racks.jpg', 'icon' => 'form', 'title' => 'Palletized & Labelled', 'text' => 'Protected racks and clear labelling organize part number, lot and quantity.'],
    ['image' => 'product-detail/compression-palletized.jpg', 'icon' => 'delivery', 'title' => 'Global Delivery', 'text' => 'Export-ready loads prepared for reliable international logistics.'],
];
$documents = [
    ['icon' => 'network', 'title' => 'Material Traceability', 'text' => 'Wire identification and material checks from receipt to production.', 'href' => $traceability_url],
    ['icon' => 'search', 'title' => 'Inspection', 'text' => 'Dimensional, material and load testing with detailed reports.', 'href' => '#quality-testing'],
    ['icon' => 'form', 'title' => 'Certificates', 'text' => 'Certificates of Conformance, material certificates and PPAP.', 'href' => $quality_url],
    ['icon' => 'download', 'title' => 'Downloads', 'text' => 'Company and product documents in the Download Center.', 'href' => $resources_url],
];
?>

<article class="sa-compression-detail">
  <section class="sa-compression-hero" aria-labelledby="compression-title">
    <div class="container container-wide sa-compression-hero__inner">
      <div class="sa-compression-hero__copy">
        <nav class="sa-compression-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'springapex'); ?>">
          <a href="<?php echo esc_url(springapex_url('/')); ?>"><?php esc_html_e('Home', 'springapex'); ?></a>
          <span aria-hidden="true">›</span>
          <a href="<?php echo esc_url(springapex_url('/products/')); ?>"><?php esc_html_e('Products', 'springapex'); ?></a>
          <span aria-hidden="true">›</span>
          <span aria-current="page"><?php echo esc_html((string) ($product['title'] ?? 'Compression Springs')); ?></span>
        </nav>
        <h1 id="compression-title"><?php echo esc_html((string) ($product['title'] ?? 'Compression Springs')); ?></h1>
        <?php if ($hero_lede !== '') : ?>
          <p class="sa-compression-hero__lede"><?php echo esc_html($hero_lede); ?></p>
        <?php endif; ?>
        <?php if ($hero_facts) : ?>
          <dl class="sa-compression-hero__facts">
            <?php foreach ($hero_facts as $fact) : ?>
              <div><dt><?php echo esc_html((string) ($fact['label'] ?? '')); ?></dt><dd><?php echo esc_html((string) ($fact['value'] ?? '')); ?></dd></div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
        <div class="sa-compression-hero__actions">
          <a class="btn btn-primary" href="#engineering-review"><?php echo springapex_icon('upload', 'icon icon-sm'); ?> <?php esc_html_e('Upload a Drawing', 'springapex'); ?></a>
          <a class="btn btn-outline" href="#engineering-review" data-compression-mode-link="dimensions"><?php esc_html_e('Enter Dimensions', 'springapex'); ?></a>
        </div>
        <p class="sa-compression-hero__note"><?php esc_html_e('Upload a drawing for engineering review. Dimensions are optional when a drawing is provided.', 'springapex'); ?></p>
      </div>

      <div class="sa-compression-hero__media<?php echo count($hero_gallery) > 1 ? '' : ' sa-compression-hero__media--single'; ?>" data-compression-hero-gallery>
        <figure class="sa-compression-hero__primary">
          <?php echo springapex_image($hero_primary_image, (string) ($product['title'] ?? 'Product view'), [
              'class' => 'is-active',
              'width' => 1200,
              'height' => 1200,
              'sizes' => '(max-width: 860px) 82vw, 34vw',
              'loading' => 'eager',
              'fetchpriority' => 'high',
          ]); ?>
        </figure>
        <?php if (count($hero_gallery) > 1) : ?>
          <div class="sa-compression-hero__thumbs" aria-label="<?php esc_attr_e('Product views', 'springapex'); ?>">
            <?php foreach ($hero_gallery as $index => $item) : ?>
              <?php
              $thumb_id = is_array($item) ? (int) ($item['id'] ?? 0) : 0;
              $thumb_file = is_array($item) ? (string) ($item['file'] ?? '') : (string) $item;
              $thumb_full = $thumb_id > 0
                  ? (string) wp_get_attachment_image_url($thumb_id, 'full')
                  : springapex_asset('assets/images/' . ltrim($thumb_file, '/'));
              $thumb_alt = sprintf('%s %d', (string) ($product['title'] ?? 'Product view'), $index + 1);
              ?>
              <button class="sa-compression-thumb<?php echo $index === 0 ? ' is-active' : ''; ?>" type="button" data-compression-hero-thumb data-image="<?php echo esc_url($thumb_full); ?>" data-alt="<?php echo esc_attr($thumb_alt); ?>" aria-label="<?php echo esc_attr(sprintf(__('Show product image %d', 'springapex'), $index + 1)); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                <?php echo springapex_image($item, $thumb_alt, ['width' => 180, 'height' => 180, 'sizes' => '88px']); ?>
              </button>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <nav class="product-tabs-section sa-compression-nav" aria-label="<?php esc_attr_e('Product sections', 'springapex'); ?>" data-product-tabs>
    <div class="container container-wide product-tabs">
      <?php if ($has_product_details) : ?>
        <a class="tab is-active" href="#product-details" data-section="product-details" aria-current="true"><?php esc_html_e('Product Details', 'springapex'); ?></a>
      <?php endif; ?>
      <a class="tab<?php echo $has_product_details ? '' : ' is-active'; ?>" href="#engineering-review" data-section="engineering-review"<?php echo $has_product_details ? '' : ' aria-current="true"'; ?>><?php esc_html_e('Engineering Review', 'springapex'); ?></a>
      <a class="tab" href="#quality-testing" data-section="quality-testing"><?php esc_html_e('Quality & Testing', 'springapex'); ?></a>
      <a class="tab" href="#packing-delivery" data-section="packing-delivery"><?php esc_html_e('Packing & Delivery', 'springapex'); ?></a>
      <a class="tab" href="#compression-faq" data-section="compression-faq"><?php esc_html_e('FAQ', 'springapex'); ?></a>
    </div>
  </nav>

  <?php if ($has_product_details) : ?>
    <?php get_template_part('parts/product-editor-details', null, [
        'product' => $product,
        'id' => 'product-details',
    ]); ?>
  <?php endif; ?>

  <section class="sa-compression-review" id="engineering-review" data-product-section>
    <div class="container container-wide">
      <div class="sa-compression-review__panel">
        <div class="sa-compression-review__diagram" data-reveal="up">
          <p class="section-kicker"><?php esc_html_e('REQUEST AN ENGINEERING REVIEW', 'springapex'); ?></p>
          <h2><?php esc_html_e('Start Your Inquiry', 'springapex'); ?></h2>
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
              <?php echo springapex_image('product-detail/compression-dimension-guide-v2.png', __('Standard compression spring showing free length, outside diameter and wire diameter', 'springapex'), [
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
          <input type="hidden" name="source" value="<?php echo esc_attr((string) get_queried_object_id()); ?>">
          <input type="hidden" name="product" value="<?php echo esc_attr($slug); ?>">
          <input type="hidden" name="inquiry_type" value="Upload a Drawing" data-inquiry-type>
          <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
          <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

          <div class="sa-compression-form__modes" role="tablist" aria-label="<?php esc_attr_e('How to send requirements', 'springapex'); ?>">
            <button type="button" class="is-active" role="tab" aria-selected="true" aria-controls="compression-drawing-panel" data-compression-inquiry-mode="drawing"><?php esc_html_e('Upload a Drawing', 'springapex'); ?></button>
            <button type="button" role="tab" aria-selected="false" aria-controls="compression-dimensions-panel" data-compression-inquiry-mode="dimensions"><?php esc_html_e('Enter Dimensions Manually', 'springapex'); ?></button>
          </div>

          <div class="sa-compression-form__drawing" id="compression-drawing-panel" role="tabpanel" data-compression-drawing-panel>
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

          <div class="sa-compression-form__dimensions" id="compression-dimensions-panel" role="tabpanel" data-compression-dimensions-panel hidden>
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
          <input type="hidden" name="full_name" value="Product detail inquiry">
          <label class="field"><span><?php esc_html_e('Work Email', 'springapex'); ?> *</span><input type="email" name="email" maxlength="190" autocomplete="email" placeholder="name@company.com" required></label>
          <button class="btn btn-primary btn-block" type="submit" data-submit-button><?php esc_html_e('Send for Engineering Review', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></button>
          <div class="sa-turnstile-widget">
            <div
              class="cf-turnstile"
              data-sitekey="<?php echo esc_attr(springapex_turnstile_site_key()); ?>"
              data-size="flexible"
              data-theme="light"
              data-language="en"
              data-action="product-inquiry"
            ></div>
            <?php echo springapex_turnstile_noscript(); ?>
          </div>
          <p class="sa-compression-form__privacy"><?php esc_html_e('Your file and project details are used only to review this inquiry.', 'springapex'); ?></p>
          <p class="form-status" data-form-status role="status" aria-live="polite" hidden></p>
        </form>
      </div>
    </div>
  </section>

  <section class="sa-compression-quality" id="quality-testing" data-product-section>
    <div class="container container-wide">
      <header class="sa-compression-section-head" data-reveal="up">
        <p class="section-kicker"><?php esc_html_e('QUALITY VERIFIED AT EVERY STAGE', 'springapex'); ?></p>
      </header>
      <div class="sa-compression-quality__gallery" data-reveal="up">
        <figure class="is-large"><?php echo springapex_image('product-detail/compression-quality-load-test.jpg', __('Spring load testing equipment operated by an ApexSpring technician', 'springapex'), ['width' => 782, 'height' => 522, 'sizes' => '(max-width: 760px) 100vw, 55vw']); ?></figure>
        <figure><?php echo springapex_image('quality-inspection-original.jpg', __('Dimensional inspection with digital caliper', 'springapex'), ['width' => 710, 'height' => 550, 'sizes' => '(max-width: 760px) 100vw, 27vw']); ?><figcaption><strong><?php esc_html_e('Dimensional Inspection', 'springapex'); ?></strong><span><?php esc_html_e('Critical dimensions checked with calibrated tools.', 'springapex'); ?></span></figcaption></figure>
        <figure><?php echo springapex_image('product-detail/compression-quality-material-lab.jpg', __('Material analysis in the ApexSpring laboratory', 'springapex'), ['width' => 782, 'height' => 442, 'sizes' => '(max-width: 760px) 100vw, 27vw']); ?><figcaption><strong><?php esc_html_e('Material Analysis', 'springapex'); ?></strong><span><?php esc_html_e('Material condition and hardness confirmed.', 'springapex'); ?></span></figcaption></figure>
      </div>
      <ol class="sa-compression-quality__steps" data-reveal-group>
        <?php foreach ($quality_steps as $item) : ?>
          <li><span><?php echo esc_html($item['step']); ?></span><div><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['text']); ?></p></div></li>
        <?php endforeach; ?>
      </ol>
    </div>
  </section>

  <section class="sa-compression-delivery" id="packing-delivery" data-product-section>
    <div class="container container-wide">
      <header class="sa-compression-section-head" data-reveal="up">
        <p class="section-kicker"><?php esc_html_e('PREPARED FOR DELIVERY', 'springapex'); ?></p>
      </header>
      <div class="sa-compression-delivery__grid" data-reveal-group>
        <?php foreach ($delivery_items as $index => $item) : ?>
          <article class="<?php echo $index === 0 ? 'is-large' : ''; ?>">
            <figure><?php echo springapex_image($item['image'], $item['title'], ['width' => $index === 0 ? 676 : 403, 'height' => $index === 0 ? 820 : 270, 'sizes' => '(max-width: 760px) 100vw, 25vw']); ?></figure>
            <div><?php echo springapex_icon($item['icon'], 'icon'); ?><span><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['text']); ?></p></span></div>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <section class="sa-compression-documents" aria-labelledby="compression-documents-title">
    <div class="container container-wide">
      <p class="section-kicker" id="compression-documents-title"><?php esc_html_e('QUALITY & DOCUMENTS', 'springapex'); ?></p>
      <div class="sa-compression-documents__grid" data-reveal-group>
        <?php foreach ($documents as $item) : ?><a href="<?php echo esc_url($item['href']); ?>"><?php echo springapex_icon($item['icon'], 'icon'); ?><span><h3><?php echo esc_html($item['title']); ?></h3><p><?php echo esc_html($item['text']); ?></p></span></a><?php endforeach; ?>
      </div>
    </div>
  </section>

  <?php get_template_part('parts/site-faq', null, [
      'id' => 'compression-faq',
      'product_section' => true,
  ]); ?>

  <section class="sa-compression-final-cta" aria-label="<?php esc_attr_e('Engineering review call to action', 'springapex'); ?>">
    <div class="container container-wide sa-compression-final-cta__inner" data-reveal="up">
      <div class="sa-compression-final-cta__icon"><?php echo springapex_icon('form'); ?></div>
      <div><h2><?php esc_html_e('Two ways to start the review', 'springapex'); ?></h2><p><?php esc_html_e('Attach the file you already have, or enter the dimensions you know. Both go to the same engineering team.', 'springapex'); ?></p></div>
      <div class="sa-compression-final-cta__actions"><a class="btn btn-primary" href="#engineering-review"><?php esc_html_e('Upload PDF / CAD', 'springapex'); ?> <?php echo springapex_icon('upload', 'icon icon-sm'); ?></a><a class="btn btn-outline" href="#engineering-review" data-compression-mode-link="dimensions"><?php esc_html_e('Enter Dimensions', 'springapex'); ?></a></div>
    </div>
  </section>
</article>
