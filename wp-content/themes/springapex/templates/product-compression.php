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
$dimension_profiles = [
    'compression-springs' => [
        ['name' => 'wire_diameter', 'symbol' => 'd', 'label' => 'Wire diameter', 'description' => 'Thickness of the spring wire', 'placeholder' => 'e.g. 1.2 mm'],
        ['name' => 'outside_diameter', 'symbol' => 'D0', 'label' => 'Outside diameter', 'description' => 'Maximum diameter across the coil', 'placeholder' => 'e.g. 12 mm'],
        ['name' => 'free_length', 'symbol' => 'L0', 'label' => 'Free length', 'description' => 'Unloaded overall spring length', 'placeholder' => 'e.g. 45 mm'],
    ],
    'extension-springs' => [
        ['name' => 'wire_diameter', 'symbol' => 'd', 'label' => 'Wire diameter', 'description' => 'Thickness of the spring wire', 'placeholder' => 'e.g. 1.2 mm'],
        ['name' => 'outside_diameter', 'symbol' => 'D', 'label' => 'Coil outside diameter', 'description' => 'Maximum diameter across the coil', 'placeholder' => 'e.g. 12 mm'],
        ['name' => 'free_length', 'symbol' => 'L', 'label' => 'Body or overall length', 'description' => 'State whether hooks or loops are included', 'placeholder' => 'e.g. 65 mm overall'],
    ],
    'torsion-springs' => [
        ['name' => 'wire_diameter', 'symbol' => 'd', 'label' => 'Wire diameter', 'description' => 'Thickness of the spring wire', 'placeholder' => 'e.g. 1.0 mm'],
        ['name' => 'outside_diameter', 'symbol' => 'D', 'label' => 'Coil outside diameter', 'description' => 'Maximum diameter across the coil', 'placeholder' => 'e.g. 15 mm'],
        ['name' => 'free_length', 'symbol' => 'A', 'label' => 'Leg length or angle', 'description' => 'Include the unloaded leg position if known', 'placeholder' => 'e.g. 30 mm / 90°'],
    ],
    'disc-springs' => [
        ['name' => 'wire_diameter', 'symbol' => 'De', 'label' => 'Outside diameter', 'description' => 'Maximum outside diameter of the disc', 'placeholder' => 'e.g. 40 mm'],
        ['name' => 'outside_diameter', 'symbol' => 'Di', 'label' => 'Inside diameter', 'description' => 'Diameter of the center opening', 'placeholder' => 'e.g. 20 mm'],
        ['name' => 'free_length', 'symbol' => 't/h', 'label' => 'Thickness or cone height', 'description' => 'Material thickness and free height if known', 'placeholder' => 'e.g. 2 / 3.5 mm'],
    ],
    'wire-forms' => [
        ['name' => 'wire_diameter', 'symbol' => 'd', 'label' => 'Wire diameter', 'description' => 'Diameter or section of the wire', 'placeholder' => 'e.g. 2 mm'],
        ['name' => 'outside_diameter', 'symbol' => 'W', 'label' => 'Overall width', 'description' => 'Maximum width of the formed part', 'placeholder' => 'e.g. 35 mm'],
        ['name' => 'free_length', 'symbol' => 'L', 'label' => 'Overall length', 'description' => 'Maximum end-to-end length', 'placeholder' => 'e.g. 80 mm'],
    ],
    'die-springs' => [
        ['name' => 'wire_diameter', 'symbol' => 'S', 'label' => 'Wire section', 'description' => 'Wire section or duty class if known', 'placeholder' => 'e.g. rectangular / heavy duty'],
        ['name' => 'outside_diameter', 'symbol' => 'D', 'label' => 'Outside size', 'description' => 'Outside diameter or installed envelope', 'placeholder' => 'e.g. 25 mm'],
        ['name' => 'free_length', 'symbol' => 'L0', 'label' => 'Free length', 'description' => 'Unloaded overall spring length', 'placeholder' => 'e.g. 100 mm'],
    ],
];
$dimension_fields = $dimension_profiles[$slug] ?? [
    ['name' => 'wire_diameter', 'symbol' => 'A', 'label' => 'Primary dimension', 'description' => 'The most important size for the part', 'placeholder' => 'e.g. 12 mm'],
    ['name' => 'outside_diameter', 'symbol' => 'B', 'label' => 'Secondary dimension', 'description' => 'Another critical size or installation limit', 'placeholder' => 'e.g. 30 mm'],
    ['name' => 'free_length', 'symbol' => 'L', 'label' => 'Overall length', 'description' => 'Maximum end-to-end length if applicable', 'placeholder' => 'e.g. 80 mm'],
];
$is_compression_product = $slug === 'compression-springs';
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
            <h3><?php echo esc_html($is_compression_product ? __('Three dimensions are enough to start.', 'springapex') : __('Share the key dimensions you know.', 'springapex')); ?></h3>
            <p><?php esc_html_e('Enter any values you know. Engineering will confirm the remaining geometry before quotation.', 'springapex'); ?></p>
            <?php if ($is_compression_product) : ?>
              <figure class="sa-compression-review__dimension-figure">
                <?php echo springapex_image('product-detail/compression-dimension-guide-v2.png', __('Standard compression spring showing free length, outside diameter and wire diameter', 'springapex'), [
                    'width' => 840,
                    'height' => 350,
                    'sizes' => '(max-width: 860px) 88vw, 34vw',
                ]); ?>
              </figure>
            <?php endif; ?>
            <dl class="sa-compression-review__dimension-list">
              <?php foreach ($dimension_fields as $field) : ?>
                <div><dt><?php echo esc_html((string) $field['symbol']); ?></dt><dd><strong><?php echo esc_html((string) $field['label']); ?></strong><span><?php echo esc_html((string) $field['description']); ?></span></dd></div>
              <?php endforeach; ?>
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
          <?php foreach ($dimension_fields as $index => $field) : ?>
            <input type="hidden" name="dimension_label_<?php echo esc_attr((string) ($index + 1)); ?>" value="<?php echo esc_attr((string) $field['label']); ?>">
          <?php endforeach; ?>
          <?php
          // 尺寸字段被「表单设置」标为必填时：输入框补 required，表单默认落在
          // 「Enter Dimensions」模式（面板初始可见）——必填项藏在 hidden 面板里
          // 会被 checkValidity 拦住且聚焦不到，访客无从得知缺了什么。
          $dimension_required = array_intersect(
              springapex_form_required_ids('product'),
              array_map(static fn (array $f): string => (string) $f['name'], $dimension_fields)
          );
          $dimensions_default = $dimension_required !== [];
          ?>
          <input type="hidden" name="inquiry_type" value="<?php echo $dimensions_default ? 'Request a Quote' : 'Upload a Drawing'; ?>" data-inquiry-type>
          <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
          <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

          <div class="sa-compression-form__modes" role="tablist" aria-label="<?php esc_attr_e('How to send requirements', 'springapex'); ?>">
            <button type="button" class="<?php echo $dimensions_default ? '' : 'is-active'; ?>" role="tab" aria-selected="<?php echo $dimensions_default ? 'false' : 'true'; ?>" aria-controls="compression-drawing-panel" data-compression-inquiry-mode="drawing"><?php esc_html_e('Upload a Drawing', 'springapex'); ?></button>
            <button type="button" class="<?php echo $dimensions_default ? 'is-active' : ''; ?>" role="tab" aria-selected="<?php echo $dimensions_default ? 'true' : 'false'; ?>" aria-controls="compression-dimensions-panel" data-compression-inquiry-mode="dimensions"><?php esc_html_e('Enter Dimensions Manually', 'springapex'); ?></button>
          </div>

          <div class="sa-compression-form__drawing" id="compression-drawing-panel" role="tabpanel" data-compression-drawing-panel<?php echo $dimensions_default ? ' hidden' : ''; ?>>
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

          <div class="sa-compression-form__dimensions" id="compression-dimensions-panel" role="tabpanel" data-compression-dimensions-panel<?php echo $dimensions_default ? '' : ' hidden'; ?>>
            <h3><?php esc_html_e('Enter the dimensions you know', 'springapex'); ?></h3>
            <?php if ($dimension_required !== []) : ?>
              <p><?php esc_html_e('Required dimensions are marked with *; engineering will confirm any missing values.', 'springapex'); ?></p>
            <?php else : ?>
              <p><?php esc_html_e('All dimensions are optional; engineering will confirm any missing values.', 'springapex'); ?></p>
            <?php endif; ?>
            <div class="sa-compression-form__row">
              <?php foreach (array_slice($dimension_fields, 0, 2) as $field) : ?>
                <?php $is_dimension_required = in_array($field['name'], $dimension_required, true); ?>
                <label class="field"><span><?php echo esc_html((string) $field['label']); ?><?php echo $is_dimension_required ? ' *' : ''; ?></span><input type="text" name="springapex_field_<?php echo esc_attr((string) $field['name']); ?>" inputmode="decimal" maxlength="80" placeholder="<?php echo esc_attr((string) $field['placeholder']); ?>"<?php echo $is_dimension_required ? ' required' : ''; ?>></label>
              <?php endforeach; ?>
            </div>
            <?php
            $last_dimension = $dimension_fields[2];
            $last_dimension_required = in_array($last_dimension['name'], $dimension_required, true);
            ?>
            <label class="field"><span><?php echo esc_html((string) $last_dimension['label']); ?><?php echo $last_dimension_required ? ' *' : ''; ?></span><input type="text" name="springapex_field_<?php echo esc_attr((string) $last_dimension['name']); ?>" inputmode="decimal" maxlength="80" placeholder="<?php echo esc_attr((string) $last_dimension['placeholder']); ?>"<?php echo $last_dimension_required ? ' required' : ''; ?>></label>
          </div>

          <?php
          // 产品页表单字段按 schema 渲染：
          // - 尺寸三行由 $dimension_profiles 提供产品类型感知的标签/占位
          //   （id 对应 schema 的 wire_diameter/outside_diameter/free_length）；
          // - 其余字段（email/quantity/material/message/自定义）由 schema 直接渲染；
          //   email 必填，本页无其他 email 输入，绝不能跳过。
          $product_schema = springapex_form_schema();
          $dimension_names = array_map(static fn ($f) => $f['name'], $dimension_fields);
          $skip_ids = $dimension_names;
          $dimension_by_name = [];
          foreach ($dimension_fields as $dimension_field) {
              $dimension_by_name[$dimension_field['name']] = $dimension_field;
          }
          ?>
          <?php foreach (($product_schema['product']['fields'] ?? []) as $product_field) : ?>
            <?php if (in_array($product_field['id'], $skip_ids, true)) : ?>
              <?php continue; ?>
            <?php endif; ?>
            <?php
            // 尺寸字段渲染在 dimensions 面板内，用产品感知标签覆盖 schema 默认。
            if (isset($dimension_by_name[$product_field['id']])):
                $override = $dimension_by_name[$product_field['id']];
                $product_field['label'] = $override['label'];
                $product_field['placeholder'] = $override['placeholder'];
            endif;
            springapex_render_form_schema_field('product', $product_field);
            ?>
          <?php endforeach; ?>
          <input type="hidden" name="full_name" value="Product detail inquiry">
          <button class="btn btn-primary btn-block" type="submit" data-submit-button><?php esc_html_e('Send for Engineering Review', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></button>
          <?php if (springapex_form_turnstile_enabled('product')) : ?>
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
          <?php endif; ?>
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
