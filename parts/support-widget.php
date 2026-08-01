<?php
if (!defined('ABSPATH')) {
    exit;
}

$brand = springapex_brand();
$email = sanitize_email((string) ($brand['email'] ?? ''));
$phone = trim((string) ($brand['phone'] ?? ''));
$whatsapp = trim((string) ($brand['whatsapp'] ?? $phone));
$whatsapp_number = preg_replace('/\D+/', '', $whatsapp);
$email_href = $email !== ''
    ? 'mailto:' . $email . '?subject=' . rawurlencode('SpringApex Project Inquiry')
    : '';
$whatsapp_href = $whatsapp_number !== ''
    ? 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode('Hello SpringApex, I would like to discuss a spring project.')
    : '';
$hours = trim((string) ($brand['hours'] ?? ''));
?>
<aside class="support-widget" data-support-widget aria-label="<?php esc_attr_e('Customer support', 'springapex'); ?>">
  <section class="support-panel" id="springapex-support-panel" data-support-panel hidden>
    <header class="support-panel-header">
      <div>
        <span class="support-eyebrow"><?php esc_html_e('Customer care', 'springapex'); ?></span>
        <h2><?php esc_html_e('Engineering Support', 'springapex'); ?></h2>
      </div>
      <button class="support-close" type="button" data-support-close aria-label="<?php esc_attr_e('Close customer support', 'springapex'); ?>">
        <?php echo springapex_icon('close', 'icon'); ?>
      </button>
    </header>

    <div class="support-panel-body">
      <p class="support-intro"><?php esc_html_e('Share your application requirements and our engineering team will help you find the right spring solution.', 'springapex'); ?></p>

      <a class="support-primary-action" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
        <span>
          <strong><?php esc_html_e('Start a conversation', 'springapex'); ?></strong>
          <small><?php esc_html_e('Tell us about your project', 'springapex'); ?></small>
        </span>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>

      <a class="support-primary-action support-primary-action--quote" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
        <span>
          <strong><?php esc_html_e('Request a quote', 'springapex'); ?></strong>
          <small><?php esc_html_e('Send a drawing or requirement', 'springapex'); ?></small>
        </span>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>

      <div class="support-contact-list">
        <?php if ($email !== '' && $email_href !== '') : ?>
          <a class="support-contact-link" href="<?php echo esc_url($email_href); ?>">
            <span class="support-contact-icon"><?php echo springapex_icon('mail', 'icon'); ?></span>
            <span>
              <small><?php esc_html_e('Email our team', 'springapex'); ?></small>
              <strong><?php echo esc_html($email); ?></strong>
            </span>
          </a>
        <?php endif; ?>

        <?php if ($whatsapp !== '' && $whatsapp_href !== '') : ?>
          <a class="support-contact-link" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer">
            <span class="support-contact-icon"><?php echo springapex_icon('chat', 'icon'); ?></span>
            <span>
              <small><?php esc_html_e('Chat on WhatsApp', 'springapex'); ?></small>
              <strong><?php echo esc_html($whatsapp); ?></strong>
            </span>
          </a>
        <?php endif; ?>
      </div>

      <?php if ($hours !== '') : ?>
        <p class="support-hours">
          <?php echo springapex_icon('clock', 'icon icon-sm'); ?>
          <span><?php echo esc_html($hours); ?></span>
        </p>
      <?php endif; ?>
    </div>
  </section>

  <button class="support-launcher" type="button" data-support-toggle aria-label="<?php esc_attr_e('Open customer support', 'springapex'); ?>" aria-expanded="false" aria-controls="springapex-support-panel">
    <span class="support-launcher-label"><?php esc_html_e('Customer Support', 'springapex'); ?></span>
    <span class="support-launcher-icon">
      <?php echo springapex_icon('chat', 'icon support-icon-open'); ?>
      <?php echo springapex_icon('close', 'icon support-icon-close'); ?>
    </span>
  </button>

  <div class="support-bottom-actions">
    <a class="support-bottom-link support-bottom-link--quote" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
      <?php echo springapex_icon('arrow-right', 'icon'); ?>
      <span><?php esc_html_e('Get a Quote', 'springapex'); ?></span>
    </a>
    <?php if ($email !== '' && $email_href !== '') : ?>
      <a class="support-bottom-link support-bottom-link--email" href="<?php echo esc_url($email_href); ?>">
        <?php echo springapex_icon('mail', 'icon'); ?>
        <span><?php esc_html_e('Email', 'springapex'); ?></span>
      </a>
    <?php endif; ?>
    <?php if ($whatsapp !== '' && $whatsapp_href !== '') : ?>
      <a class="support-bottom-link support-bottom-link--whatsapp" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer">
        <?php echo springapex_icon('chat', 'icon'); ?>
        <span><?php esc_html_e('WhatsApp', 'springapex'); ?></span>
      </a>
    <?php endif; ?>
  </div>
</aside>
