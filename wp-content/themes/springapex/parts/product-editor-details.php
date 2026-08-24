<?php
if (!defined('ABSPATH')) {
    exit;
}

$product = is_array($args['product'] ?? null) ? $args['product'] : [];
$section_id = sanitize_key((string) ($args['id'] ?? 'product-details')) ?: 'product-details';
$details_source = trim((string) ($product['overview'] ?? ''));
$is_preview = defined('SPRINGAPEX_PREVIEW');

if (!$is_preview && $details_source === '') {
    return;
}

$title = trim((string) ($product['title'] ?? __('Product', 'springapex')));
$slug = sanitize_key((string) ($product['slug'] ?? ''));
$preview_image = $product['category_image'] ?? ($product['image'] ?? '');
?>

<section class="sa-product-editor-details" id="<?php echo esc_attr($section_id); ?>" data-product-section aria-label="<?php esc_attr_e('Product details', 'springapex'); ?>">
  <div class="container container-wide">
    <div class="sa-product-editor-details__content entry-content" data-reveal="up">
      <?php if (!$is_preview) : ?>
        <?php echo apply_filters('the_content', $details_source); ?>
      <?php elseif ($slug === 'compression-springs') : ?>
        <h2><?php esc_html_e('Custom compression springs engineered to application requirements.', 'springapex'); ?></h2>
        <p><?php esc_html_e('NorenSpring manufactures custom compression springs for automotive, industrial equipment, medical devices and precision assemblies. Wire diameter, outside diameter, free length, spring rate, solid height and operating environment are reviewed before material, end configuration and surface treatment are confirmed.', 'springapex'); ?></p>
        <figure class="wp-block-image size-large">
          <?php echo springapex_image('product-detail/compression-advantages-infographic-v1.png', __('Compression spring advantages including flexible geometry, material options, production support and quality records', 'springapex'), [
              'width' => 1672,
              'height' => 941,
              'sizes' => '(max-width: 760px) 100vw, 1180px',
              'mobile_image' => 'product-detail/compression-advantages-infographic-mobile-v1.png',
              'mobile_sizes' => '100vw',
              'mobile_breakpoint' => '860px',
          ]); ?>
          <figcaption><?php esc_html_e('Engineering options are reviewed around geometry, material, production stage and required quality records.', 'springapex'); ?></figcaption>
        </figure>
        <h3><?php esc_html_e('Materials, ends and production support', 'springapex'); ?></h3>
        <p><?php esc_html_e('Music wire, stainless steel, carbon steel and selected alloy grades are available for both prototype batches and repeat production runs.', 'springapex'); ?></p>
        <ul>
          <li><strong><?php esc_html_e('End configurations:', 'springapex'); ?></strong> <?php esc_html_e('closed, closed-and-ground, or plain ends.', 'springapex'); ?></li>
          <li><strong><?php esc_html_e('Surface treatment:', 'springapex'); ?></strong> <?php esc_html_e('shot peening, passivation, zinc plating or powder coating.', 'springapex'); ?></li>
          <li><strong><?php esc_html_e('Documentation:', 'springapex'); ?></strong> <?php esc_html_e('dimensional reports, load test data and material traceability, agreed per project.', 'springapex'); ?></li>
        </ul>
        <figure class="wp-block-image size-large">
          <?php echo springapex_image('compression-diagram-v2.png', __('Compression spring dimensional reference showing free length, diameter, wire diameter and pitch', 'springapex'), [
              'width' => 840,
              'height' => 440,
              'sizes' => '(max-width: 760px) 100vw, 1180px',
          ]); ?>
          <figcaption><?php esc_html_e('Typical dimensions used during engineering review.', 'springapex'); ?></figcaption>
        </figure>
      <?php else : ?>
        <h2><?php echo esc_html(sprintf(__('Custom %s manufactured to application requirements.', 'springapex'), $title)); ?></h2>
        <p><?php echo esc_html($details_source); ?></p>
        <?php if ($preview_image !== '') : ?>
          <figure class="wp-block-image size-large">
            <?php echo springapex_image($preview_image, sprintf(__('%s product detail', 'springapex'), $title), [
                'width' => 1200,
                'height' => 900,
                'sizes' => '(max-width: 760px) 100vw, 1180px',
            ]); ?>
            <figcaption><?php esc_html_e('Product geometry, material and finish are confirmed against the drawing or application requirements.', 'springapex'); ?></figcaption>
          </figure>
        <?php endif; ?>
        <h3><?php esc_html_e('Designed around the working conditions', 'springapex'); ?></h3>
        <p><?php esc_html_e('Load, movement, installation space, service environment, cycle life, tolerances and inspection records can be reviewed before prototyping and repeat production.', 'springapex'); ?></p>
      <?php endif; ?>
    </div>
  </div>
</section>
