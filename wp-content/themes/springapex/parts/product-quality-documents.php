<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = springapex_get('quality_evidence', []);
$catalog_url = (string) ($args['catalog_url'] ?? '');
$product_slug = sanitize_key((string) ($args['product_slug'] ?? ''));
$document_url = $catalog_url !== ''
    ? $catalog_url
    : springapex_url('/contact/?intent=catalog&product=' . $product_slug);
$document_label = $catalog_url !== '' ? __('Download Catalog', 'springapex') : __('Request Technical Documents', 'springapex');
$document_icon = $catalog_url !== '' ? 'download' : 'arrow-right';
?>
<section class="section sa-product-quality-documents" id="quality" data-product-section>
  <div class="container container-wide">
    <div class="sa-section-intro" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('QUALITY & DOCUMENTS', 'springapex'); ?></p>
      <h2><?php esc_html_e('Verification and project records defined together.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('Confirm the critical checks, report format and controlled documents during quotation so production and release follow the same requirements.', 'springapex'); ?></p>
    </div>

    <div class="sa-product-quality-documents__layout">
      <div class="sa-product-quality-documents__evidence" data-reveal-group>
        <?php foreach ($items as $item) : ?>
          <article>
            <span class="sa-evidence__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'check-shield')); ?></span>
            <div>
              <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <div class="sa-product-quality-documents__documents" data-reveal="up">
        <h3><?php esc_html_e('Available project documentation', 'springapex'); ?></h3>
        <ul class="sa-documentation__list">
          <li><?php echo springapex_icon('download'); ?><span><strong><?php esc_html_e('Product catalog or technical data', 'springapex'); ?></strong><small><?php esc_html_e('Available when the relevant controlled document exists.', 'springapex'); ?></small></span></li>
          <li><?php echo springapex_icon('check-shield'); ?><span><strong><?php esc_html_e('Inspection and material records', 'springapex'); ?></strong><small><?php esc_html_e('Defined during quotation and supplier qualification.', 'springapex'); ?></small></span></li>
          <li><?php echo springapex_icon('pen'); ?><span><strong><?php esc_html_e('Drawing and RFQ guidance', 'springapex'); ?></strong><small><?php esc_html_e('Use the resource center to prepare application inputs.', 'springapex'); ?></small></span></li>
        </ul>
        <div class="sa-documentation__actions">
          <a class="btn btn-primary" href="<?php echo esc_url($document_url); ?>">
            <?php echo esc_html($document_label); ?> <?php echo springapex_icon($document_icon, 'icon icon-sm'); ?>
          </a>
          <a class="text-link" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
            <?php esc_html_e('Open engineering resources', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
</section>
