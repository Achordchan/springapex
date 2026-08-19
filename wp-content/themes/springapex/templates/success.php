<?php
if (!defined('ABSPATH')) {
    exit;
}

$brand = springapex_brand();
$email = sanitize_email((string) ($brand['email'] ?? ''));
$phone = trim((string) ($brand['phone'] ?? ''));
$whatsapp = trim((string) ($brand['whatsapp'] ?? $phone));
$whatsapp_number = preg_replace('/\D+/', '', $whatsapp);
$whatsapp_href = $whatsapp_number !== ''
    ? 'https://wa.me/' . $whatsapp_number
    : '';
?>
<div class="sa-legal-page sa-success-page">
  <header class="sa-success-hero">
    <div class="container sa-legal-container">
      <span class="sa-success-badge" aria-hidden="true"><?php echo springapex_icon('qc', 'icon'); ?></span>
      <p class="section-kicker"><?php esc_html_e('INQUIRY RECEIVED', 'springapex'); ?></p>
      <h1><?php esc_html_e('Thank You — Your Inquiry Is On Its Way', 'springapex'); ?></h1>
      <p class="sa-success-lead"><?php esc_html_e('Our engineering team has received your request and will get back to you within 24 hours (one business day). Please keep an eye on your inbox — and your spam folder, just in case.', 'springapex'); ?></p>

      <div class="sa-success-actions">
        <a class="btn btn-primary" href="<?php echo esc_url(get_post_type_archive_link('spring_product') ?: springapex_url('/products/')); ?>">
          <span><?php esc_html_e('Browse Our Springs', 'springapex'); ?></span>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
        <a class="btn btn-ghost" href="<?php echo esc_url(springapex_url('/')); ?>">
          <span><?php esc_html_e('Back to Home', 'springapex'); ?></span>
        </a>
      </div>

      <?php if ($email !== '' || $whatsapp_href !== '') : ?>
        <p class="sa-success-direct"><?php esc_html_e('Working to a deadline? Reach us directly:', 'springapex'); ?></p>
        <div class="sa-success-contacts">
          <?php if ($email !== '') : ?>
            <a class="sa-success-contact" href="<?php echo esc_url('mailto:' . $email); ?>">
              <?php echo springapex_icon('mail', 'icon icon-sm'); ?>
              <span><?php echo esc_html($email); ?></span>
            </a>
          <?php endif; ?>
          <?php if ($whatsapp_href !== '') : ?>
            <a class="sa-success-contact" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer">
              <?php echo springapex_icon('whatsapp', 'icon icon-sm'); ?>
              <span><?php echo esc_html($whatsapp); ?></span>
            </a>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </header>
</div>
