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
    ? 'mailto:' . $email . '?subject=' . rawurlencode('ApexSpring Project Inquiry')
    : '';
$whatsapp_href = $whatsapp_number !== ''
    ? 'https://wa.me/' . $whatsapp_number . '?text=' . rawurlencode('Hello ApexSpring, I would like to discuss a spring project.')
    : '';
$hours = trim((string) ($brand['hours'] ?? ''));
$form_action = defined('SPRINGAPEX_PREVIEW')
    ? springapex_url('/contact/')
    : admin_url('admin-post.php');
?>
<aside class="support-widget" data-support-widget aria-label="<?php esc_attr_e('Customer support', 'springapex'); ?>">
  <section class="support-panel" id="springapex-support-panel" data-support-panel hidden>
    <div class="support-panel-main">
      <header class="support-panel-header">
        <div>
          <span class="support-eyebrow"><?php esc_html_e('Quick inquiry', 'springapex'); ?></span>
          <h2><?php esc_html_e('Tell Us What You Need', 'springapex'); ?></h2>
        </div>
        <button class="support-close" type="button" data-support-close aria-label="<?php esc_attr_e('Close customer support', 'springapex'); ?>">
          <?php echo springapex_icon('close', 'icon'); ?>
        </button>
      </header>

      <div class="support-panel-body">
        <p class="support-intro"><?php esc_html_e('A quick message is enough — we reply within one business day.', 'springapex'); ?></p>

      <form class="support-quick-form" data-contact-form method="post" action="<?php echo esc_url($form_action); ?>">
        <input type="hidden" name="action" value="springapex_contact">
        <?php if (defined('SPRINGAPEX_PREVIEW')) : ?>
          <input type="hidden" name="springapex_contact_nonce" value="">
        <?php else : ?>
          <?php wp_nonce_field('springapex_contact', 'springapex_contact_nonce', false); ?>
        <?php endif; ?>
        <input type="hidden" name="intent" value="quote">
        <input type="hidden" name="form_context" value="quick">
        <input type="hidden" name="source" value="<?php echo esc_attr((string) get_queried_object_id()); ?>">
        <input type="hidden" name="inquiry_type" value="Request a Quote">
        <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
        <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

        <label class="support-field">
          <span><?php esc_html_e('Name', 'springapex'); ?></span>
          <input type="text" name="full_name" maxlength="120" autocomplete="name" required data-support-first-field>
        </label>
        <label class="support-field">
          <span><?php esc_html_e('Work email', 'springapex'); ?></span>
          <input type="email" name="email" maxlength="190" autocomplete="email" required>
        </label>
        <label class="support-field">
          <span><?php esc_html_e('What spring or application do you need?', 'springapex'); ?></span>
          <textarea name="message" rows="3" maxlength="1500" required></textarea>
        </label>
        <button class="btn btn-primary support-submit" type="submit" data-submit-button>
          <span><?php esc_html_e('Send Inquiry', 'springapex'); ?></span>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </button>
        <div class="support-turnstile-widget">
          <div
            class="cf-turnstile"
            data-sitekey="1x00000000000000000000AA"
            data-theme="light"
            data-language="en"
            data-action="quick-inquiry-demo"
          ></div>
        </div>
        <p class="form-status support-form-status" data-form-status role="status" aria-live="polite" hidden></p>
      </form>

        <a class="support-full-form-link" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
          <span><?php esc_html_e('Need to upload a drawing? Open the full form', 'springapex'); ?></span>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </div>
    </div>

  </section>

  <nav class="support-contact-dock" aria-label="<?php esc_attr_e('Direct contact options', 'springapex'); ?>">
    <?php if ($email !== '' && $email_href !== '') : ?>
      <a class="support-contact-dock__email" href="<?php echo esc_url($email_href); ?>" aria-label="<?php esc_attr_e('Email our team', 'springapex'); ?>">
        <span class="support-contact-dock__icon"><?php echo springapex_icon('mail', 'icon'); ?></span>
        <span class="support-contact-dock__label"><small><?php esc_html_e('Email our team', 'springapex'); ?></small><strong><?php echo esc_html($email); ?></strong></span>
      </a>
    <?php endif; ?>
    <?php if ($whatsapp !== '' && $whatsapp_href !== '') : ?>
      <a class="support-contact-dock__whatsapp" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Chat on WhatsApp', 'springapex'); ?>">
        <span class="support-contact-dock__icon"><?php echo springapex_icon('whatsapp', 'icon'); ?></span>
        <span class="support-contact-dock__label"><small><?php esc_html_e('Chat on WhatsApp', 'springapex'); ?></small><strong><?php echo esc_html($whatsapp); ?></strong></span>
      </a>
    <?php endif; ?>
    <?php if ($hours !== '') : ?>
      <span class="support-contact-dock__hours" aria-label="<?php echo esc_attr($hours); ?>">
        <span class="support-contact-dock__icon"><?php echo springapex_icon('clock', 'icon'); ?></span>
        <span class="support-contact-dock__label"><small><?php esc_html_e('Working hours', 'springapex'); ?></small><strong><?php echo esc_html($hours); ?></strong></span>
      </span>
    <?php endif; ?>
  </nav>

  <button class="support-launcher" type="button" data-support-toggle aria-label="<?php esc_attr_e('Open quick inquiry form', 'springapex'); ?>" aria-expanded="false" aria-controls="springapex-support-panel">
    <span class="support-launcher-label"><?php esc_html_e('Quick Inquiry', 'springapex'); ?></span>
    <span class="support-launcher-icon">
      <?php echo springapex_icon('chat', 'icon support-icon-open'); ?>
      <?php echo springapex_icon('close', 'icon support-icon-close'); ?>
    </span>
  </button>

  <div class="support-bottom-actions">
    <button class="support-bottom-link support-bottom-link--quote" type="button" data-support-toggle aria-label="<?php esc_attr_e('Open quick inquiry form', 'springapex'); ?>" aria-expanded="false" aria-controls="springapex-support-panel">
      <?php echo springapex_icon('arrow-right', 'icon'); ?>
      <span><?php esc_html_e('Get a Quote', 'springapex'); ?></span>
    </button>
    <?php if ($email !== '' && $email_href !== '') : ?>
      <a class="support-bottom-link support-bottom-link--email" href="<?php echo esc_url($email_href); ?>">
        <?php echo springapex_icon('mail', 'icon'); ?>
        <span><?php esc_html_e('Email', 'springapex'); ?></span>
      </a>
    <?php endif; ?>
    <?php if ($whatsapp !== '' && $whatsapp_href !== '') : ?>
      <a class="support-bottom-link support-bottom-link--whatsapp" href="<?php echo esc_url($whatsapp_href); ?>" target="_blank" rel="noopener noreferrer">
        <?php echo springapex_icon('whatsapp', 'icon'); ?>
        <span><?php esc_html_e('WhatsApp', 'springapex'); ?></span>
      </a>
    <?php endif; ?>
  </div>
</aside>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
