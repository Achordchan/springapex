<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<section class="section sa-products-resource-link" id="resources">
  <div class="container container-wide sa-products-resource-link__inner" data-reveal="up">
    <div>
      <p class="section-kicker"><?php esc_html_e('BEFORE YOU REQUEST A QUOTE', 'springapex'); ?></p>
      <h2><?php esc_html_e('Prepare the right spring requirements.', 'springapex'); ?></h2>
      <p><?php esc_html_e('Use our RFQ, material and inspection guides to prepare a clearer engineering request.', 'springapex'); ?></p>
    </div>
    <a class="btn btn-outline" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
      <?php esc_html_e('Open Engineering Resources', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
