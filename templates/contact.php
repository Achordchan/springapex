<?php
if (!defined('ABSPATH')) {
    exit;
}
$contact = springapex_get('contact', []);
$hero = $contact['hero'] ?? [];
$types = $contact['inquiry_types'] ?? [];
$brand = springapex_brand();
$intent_value = $_GET['intent'] ?? '';
$product_value = $_GET['product'] ?? '';
$industry_value = $_GET['industry'] ?? '';
$document_value = $_GET['document'] ?? '';
$intent = sanitize_key(is_scalar($intent_value) ? (string) $intent_value : '');
$product = sanitize_title(is_scalar($product_value) ? (string) $product_value : '');
$industry = sanitize_title(is_scalar($industry_value) ? (string) $industry_value : '');
$document = sanitize_key(is_scalar($document_value) ? (string) $document_value : '');
$intent_types = [
    'quote' => 'Request a Quote',
    'drawing' => 'Upload a Drawing',
    'engineer' => 'Technical Support',
    'call' => 'Technical Support',
    'solution' => 'Custom Design',
    'catalog' => 'Catalog / Technical Documents',
    'quality' => 'Supplier Qualification',
    'feedback' => 'Feedback / Suggestions',
];
$selected_type = $intent_types[$intent] ?? '';
$show_drawing_field = $selected_type === 'Upload a Drawing';
$info = [
    ['icon' => 'mail', 'label' => 'Email', 'value' => $brand['email'] ?? '', 'href' => 'mailto:' . ($brand['email'] ?? '')],
    ['icon' => 'chat', 'label' => 'WhatsApp', 'value' => $brand['whatsapp'] ?? $brand['phone'] ?? '', 'href' => 'https://wa.me/' . preg_replace('/[^0-9]/', '', (string) ($brand['whatsapp'] ?? $brand['phone'] ?? ''))],
    ['icon' => 'phone', 'label' => 'Phone', 'value' => $brand['phone'] ?? '', 'href' => 'tel:' . preg_replace('/[^+0-9]/', '', (string) ($brand['phone'] ?? ''))],
    ['icon' => 'map-pin', 'label' => 'Address', 'value' => $brand['address'] ?? '', 'href' => 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode((string) ($brand['address'] ?? ''))],
    ['icon' => 'clock', 'label' => 'Business Hours', 'value' => $brand['hours'] ?? '', 'href' => ''],
];
$status_value = $_GET['contact_status'] ?? '';
$status_key = is_scalar($status_value) ? sanitize_key((string) $status_value) : '';
$status = springapex_contact_status_message($status_key);
$form_action = defined('SPRINGAPEX_PREVIEW')
    ? springapex_url('/contact/')
    : admin_url('admin-post.php');
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'contact',
    'title' => $hero['title'] ?? 'Contact Us',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'contact-springs-v2.png',
    'ctas' => [[
        'label' => 'Send Us a Message',
        'href' => '#contact-form',
        'icon' => 'arrow-right',
    ]],
]);
?>

<section class="section contact-main">
  <div class="container container-wide contact-layout">
    <div class="contact-info" data-reveal="up">
      <h2><?php esc_html_e('Get in Touch', 'springapex'); ?></h2>
      <ul class="info-list">
        <?php foreach ($info as $row) : ?>
          <li>
            <div class="icon-circle soft"><?php echo springapex_icon((string) $row['icon']); ?></div>
            <div>
              <span class="info-label"><?php echo esc_html((string) $row['label']); ?></span>
              <?php if ($row['href']) : ?>
                <a class="info-value" href="<?php echo esc_url((string) $row['href']); ?>"><?php echo nl2br(esc_html((string) $row['value'])); ?></a>
              <?php else : ?>
                <span class="info-value"><?php echo nl2br(esc_html((string) $row['value'])); ?></span>
              <?php endif; ?>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="contact-form-wrap" data-reveal="up">
      <h2><?php esc_html_e('Send Us a Message', 'springapex'); ?></h2>

      <?php if ($product !== '' || $industry !== '' || $document !== '') : ?>
        <p class="sa-contact-context">
          <?php echo springapex_icon('extension', 'icon icon-sm'); ?>
          <?php
          if ($document !== '') {
              echo esc_html(sprintf(__('Document request: %s', 'springapex'), strtoupper(str_replace('-', ' ', $document))));
          } else {
              echo esc_html(sprintf(
                  __('Project context: %s', 'springapex'),
                  ucwords(str_replace('-', ' ', $product !== '' ? $product : $industry))
              ));
          }
          ?>
        </p>
      <?php endif; ?>
      <form id="contact-form" class="contact-form" data-contact-form method="post" action="<?php echo esc_url($form_action); ?>" enctype="multipart/form-data">
        <input type="hidden" name="action" value="springapex_contact">
        <?php if (defined('SPRINGAPEX_PREVIEW')) : ?>
          <input type="hidden" name="springapex_contact_nonce" value="">
        <?php else : ?>
          <?php wp_nonce_field('springapex_contact', 'springapex_contact_nonce', false); ?>
        <?php endif; ?>
        <input type="hidden" name="intent" value="<?php echo esc_attr($intent); ?>">
        <input type="hidden" name="product" value="<?php echo esc_attr($product); ?>">
        <input type="hidden" name="industry" value="<?php echo esc_attr($industry); ?>">
        <input type="hidden" name="document" value="<?php echo esc_attr($document); ?>">
        <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
        <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

        <label class="field">
          <span class="sr-only"><?php esc_html_e('Full Name', 'springapex'); ?></span>
          <input type="text" name="full_name" placeholder="Full Name" maxlength="120" autocomplete="name" required>
        </label>
        <label class="field">
          <span class="sr-only"><?php esc_html_e('Email Address', 'springapex'); ?></span>
          <input type="email" name="email" placeholder="Email Address" maxlength="190" autocomplete="email" required>
        </label>
        <div class="form-row-2">
          <label class="field">
            <span class="sr-only"><?php esc_html_e('Company', 'springapex'); ?></span>
            <input type="text" name="company" placeholder="Company" maxlength="160" autocomplete="organization">
          </label>
          <label class="field">
            <span class="sr-only"><?php esc_html_e('Phone Number', 'springapex'); ?></span>
            <input type="tel" name="phone" placeholder="Phone Number" maxlength="80" autocomplete="tel">
          </label>
        </div>
        <label class="field">
          <span class="sr-only"><?php esc_html_e('Inquiry Type', 'springapex'); ?></span>
          <select name="inquiry_type" data-inquiry-type required>
            <option value="" disabled <?php selected($selected_type, ''); ?>><?php esc_html_e('Inquiry Type', 'springapex'); ?></option>
            <?php foreach ($types as $type) : ?>
              <option value="<?php echo esc_attr((string) $type); ?>" <?php selected($selected_type, $type); ?>><?php echo esc_html((string) $type); ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="sr-only"><?php esc_html_e('Message', 'springapex'); ?></span>
          <textarea name="message" rows="5" placeholder="Message" maxlength="5000" required></textarea>
        </label>
        <label class="field file-field" data-drawing-field>
          <span><?php esc_html_e('Drawing, specification or supporting file (optional, PDF, Word, CAD or image; max 10 MB)', 'springapex'); ?></span>
          <input type="file" name="drawing" accept=".pdf,.doc,.docx,.zip,.dwg,.dxf,.step,.stp,.iges,.igs,.jpg,.jpeg,.png">
        </label>
        <button class="btn btn-primary btn-block" type="submit" data-submit-button>
          <span><?php esc_html_e('Submit', 'springapex'); ?></span> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </button>
        <p class="form-status<?php echo !empty($status['type']) ? ' is-' . esc_attr((string) $status['type']) : ''; ?>" data-form-status role="status" aria-live="polite" <?php echo $status ? '' : 'hidden'; ?>><?php echo $status ? esc_html((string) $status['message']) : ''; ?></p>
      </form>
    </div>
  </div>
</section>

<section class="section sa-contact-workflow">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('WHAT HAPPENS NEXT', 'springapex'); ?></p>
      <h2><?php esc_html_e('A clear path from inquiry to engineering response.', 'springapex'); ?></h2>
    </div>
    <ol class="sa-contact-workflow__grid" data-reveal-group>
      <?php foreach (springapex_get('contact_workflow', []) as $item) : ?>
        <li>
          <span><?php echo esc_html((string) ($item['step'] ?? '')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section trust-strip">
  <div class="container container-wide trust-strip-inner" data-reveal="up">
    <div class="icon-circle soft"><?php echo springapex_icon('shield'); ?></div>
    <div>
      <h3><?php esc_html_e('Your project is in good hands.', 'springapex'); ?></h3>
      <p><?php esc_html_e('Project details are reviewed by the commercial and engineering teams before the next technical step is confirmed.', 'springapex'); ?></p>
    </div>
  </div>
</section>

<section class="section schedule-section">
  <div class="container container-wide schedule-card" data-reveal="up">
    <div class="schedule-copy">
      <h2><?php esc_html_e('Prefer to talk?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Call our team to discuss your application.', 'springapex'); ?></p>
      <a class="btn btn-primary" href="tel:<?php echo esc_attr((string) preg_replace('/[^+0-9]/', '', (string) ($brand['phone'] ?? ''))); ?>">
        <?php esc_html_e('Call Our Team', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="schedule-map">
      <?php echo springapex_image($contact['map_image'] ?? 'map-xuzhou-v2.png', __('Map marker for Xuzhou, Jiangsu, China', 'springapex'), [
        'width' => 1600,
        'height' => 700,
          'sizes' => '(max-width: 760px) 100vw, 60vw',
      ]); ?>
    </div>
  </div>
</section>
