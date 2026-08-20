<?php
if (!defined('ABSPATH')) {
    exit;
}

$network = springapex_get('contact_network', []);
$regions = is_array($network['regions'] ?? null) ? $network['regions'] : [];
$markers = is_array($network['markers'] ?? null) ? $network['markers'] : [];
$facts = is_array($network['facts'] ?? null) ? $network['facts'] : [];
$headquarters = is_array($network['headquarters'] ?? null) ? $network['headquarters'] : [];
$brand = springapex_brand();
$types = (array) springapex_get('contact.inquiry_types', []);

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
$selected_type = in_array($selected_type, $types, true) ? $selected_type : 'Request a Quote';
// Drawing-intent CTAs reveal the file-upload controls (but intentionally do NOT
// auto-expand the optional "Add project details" accordion — a deliberate UX
// choice so the form stays compact on arrival).
$drawing_upload_open = $selected_type === 'Upload a Drawing';
$status_value = $_GET['contact_status'] ?? '';
$status_key = is_scalar($status_value) ? sanitize_key((string) $status_value) : '';
$status = springapex_contact_status_message($status_key);
$form_action = defined('SPRINGAPEX_PREVIEW')
    ? springapex_url('/contact/')
    : admin_url('admin-post.php');
$whatsapp_number = preg_replace('/[^0-9]/', '', (string) ($brand['whatsapp'] ?? $brand['phone'] ?? ''));
?>

<section class="section sa-contact-network" id="contact-network">
  <div class="container container-wide">
    <div class="sa-contact-network__layout">
      <div class="sa-contact-network__main">
        <header class="sa-contact-network__intro" data-reveal="up">
          <h1><?php echo esc_html((string) ($network['eyebrow'] ?? 'CONTACT APEXSPRING')); ?></h1>
          <p><?php echo esc_html((string) ($network['title'] ?? 'Engineering support for projects worldwide.')); ?></p>
        </header>

        <div class="sa-contact-network__company" data-reveal="up">
          <figure class="sa-contact-network__facility">
            <?php echo springapex_image((string) ($network['facility_image'] ?? 'facility-aerial-original.webp'), __('ApexSpring manufacturing facility in Xuzhou', 'springapex'), [
                'width' => 1800,
                'height' => 760,
                'sizes' => '(max-width: 980px) 100vw, 50vw',
            ]); ?>
          </figure>
          <div class="sa-contact-network__headquarters">
            <h2><?php echo esc_html((string) ($headquarters['title'] ?? 'Xuzhou Global Headquarters')); ?></h2>
            <?php if (!empty($headquarters['location'])) : ?>
              <p class="sa-contact-network__location"><?php echo springapex_icon('map-pin', 'icon icon-sm'); ?><?php echo esc_html((string) $headquarters['location']); ?></p>
            <?php endif; ?>
            <p><?php echo esc_html((string) ($headquarters['text'] ?? '')); ?></p>
            <dl class="sa-contact-network__facts">
              <?php foreach ($facts as $fact) : ?>
                <div>
                  <?php echo springapex_icon((string) ($fact['icon'] ?? 'factory')); ?>
                  <dt><?php echo esc_html((string) ($fact['value'] ?? '')); ?></dt>
                  <dd><?php echo esc_html((string) ($fact['label'] ?? '')); ?></dd>
                </div>
              <?php endforeach; ?>
            </dl>
          </div>
        </div>

        <?php
        // Build the interactive-map point list from the marker records. Only
        // markers with a real numeric lat/lng make it onto the map; anything
        // still holding a placeholder or blank coordinate is skipped so the map
        // never drops a pin in the ocean.
        $map_points = [];
        foreach ($markers as $marker) {
            if (!is_array($marker)) {
                continue;
            }
            $lat_raw = (string) ($marker['lat'] ?? '');
            $lng_raw = (string) ($marker['lng'] ?? '');
            if (!is_numeric($lat_raw) || !is_numeric($lng_raw)) {
                continue;
            }
            $lat = (float) $lat_raw;
            $lng = (float) $lng_raw;
            if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
                continue;
            }
            $map_points[] = [
                'label' => (string) ($marker['label'] ?? ''),
                'address' => (string) ($marker['address'] ?? ''),
                'lat' => $lat,
                'lng' => $lng,
                'side' => in_array(($marker['label_side'] ?? 'right'), ['left', 'right', 'top', 'bottom'], true)
                    ? (string) $marker['label_side']
                    : 'right',
                'hq' => !empty($marker['headquarters']),
            ];
        }
        ?>
        <?php
        // Static world-map image, reused as the no-JavaScript fallback and as the
        // full replacement inside the no-WordPress preview (which does not load
        // Leaflet or contact-map.js, so the interactive container cannot render).
        $map_static_image = springapex_image((string) ($network['map_image'] ?? 'contact/contact-world-map-v1.png'), __('Global ApexSpring contact network map', 'springapex'), [
            'width' => 1800,
            'height' => 820,
            'sizes' => '(max-width: 980px) 100vw, 68vw',
            'loading' => 'eager',
        ]);
        ?>
        <div class="sa-contact-network__map" data-reveal="up">
          <?php if (defined('SPRINGAPEX_PREVIEW')) : ?>
            <?php echo $map_static_image; ?>
          <?php else : ?>
            <div
              class="sa-contact-map"
              data-sa-contact-map
              data-points="<?php echo esc_attr((string) wp_json_encode($map_points)); ?>"
              data-nav-label="<?php esc_attr_e('Get directions', 'springapex'); ?>"
              role="application"
              aria-label="<?php esc_attr_e('Interactive map of ApexSpring global contact locations', 'springapex'); ?>"
            >
              <noscript><?php echo $map_static_image; ?></noscript>
            </div>
          <?php endif; ?>
        </div>

        <?php if ($regions) : ?>
          <div class="sa-contact-regions" data-contact-region-tabs data-reveal="up">
            <div class="sa-contact-regions__tabs" role="tablist" aria-label="<?php esc_attr_e('Global contact regions', 'springapex'); ?>">
              <?php foreach ($regions as $index => $region) : ?>
                <?php $region_slug = sanitize_key((string) ($region['slug'] ?? 'region-' . $index)); ?>
                <button
                  type="button"
                  role="tab"
                  id="contact-region-tab-<?php echo esc_attr($region_slug); ?>"
                  aria-controls="contact-region-panel-<?php echo esc_attr($region_slug); ?>"
                  aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
                  tabindex="<?php echo $index === 0 ? '0' : '-1'; ?>"
                  data-contact-region-tab="<?php echo esc_attr($region_slug); ?>"
                ><?php echo esc_html((string) ($region['label'] ?? '')); ?></button>
              <?php endforeach; ?>
            </div>
            <div class="sa-contact-regions__panels">
              <?php foreach ($regions as $index => $region) : ?>
                <?php $region_slug = sanitize_key((string) ($region['slug'] ?? 'region-' . $index)); ?>
                <div
                  class="sa-contact-regions__panel"
                  id="contact-region-panel-<?php echo esc_attr($region_slug); ?>"
                  role="tabpanel"
                  aria-labelledby="contact-region-tab-<?php echo esc_attr($region_slug); ?>"
                  data-contact-region-panel="<?php echo esc_attr($region_slug); ?>"
                  <?php echo $index === 0 ? '' : 'hidden'; ?>
                >
                  <div class="sa-contact-regions__locations">
                    <ul>
                      <?php foreach (($region['locations'] ?? []) as $location) : ?>
                        <?php $location_phone = preg_replace('/[^0-9+]/', '', (string) ($location['phone'] ?? '')); ?>
                        <li>
                          <header>
                            <?php echo springapex_icon('map-pin', 'icon icon-sm'); ?>
                            <span>
                              <strong><?php echo esc_html((string) ($location['name'] ?? '')); ?></strong>
                              <?php if (!empty($location['detail'])) : ?><small><?php echo esc_html((string) $location['detail']); ?></small><?php endif; ?>
                            </span>
                          </header>
                          <?php if (!empty($location['company'])) : ?><p class="sa-contact-regions__company"><?php echo esc_html((string) $location['company']); ?></p><?php endif; ?>
                          <?php if (!empty($location['address'])) : ?>
                            <?php
                            $map_query = trim(trim((string) ($location['company'] ?? '')) . ' ' . trim((string) $location['address']));
                            $map_href = 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($map_query);
                            ?>
                            <a class="sa-contact-regions__address" href="<?php echo esc_url($map_href); ?>" target="_blank" rel="noopener noreferrer" title="<?php esc_attr_e('Open in Google Maps', 'springapex'); ?>"><?php echo springapex_icon('map-pin', 'icon icon-sm'); ?><span><?php echo esc_html((string) $location['address']); ?></span></a>
                          <?php endif; ?>
                          <div class="sa-contact-regions__links">
                            <?php if (!empty($location['email'])) : ?>
                              <a href="mailto:<?php echo esc_attr((string) $location['email']); ?>"><?php echo springapex_icon('mail', 'icon icon-sm'); ?><span><?php echo esc_html((string) $location['email']); ?></span></a>
                            <?php endif; ?>
                            <?php if ($location_phone !== '') : ?>
                              <a href="tel:<?php echo esc_attr($location_phone); ?>"><?php echo springapex_icon('phone', 'icon icon-sm'); ?><span><?php echo esc_html((string) $location['phone']); ?></span></a>
                            <?php endif; ?>
                            <?php if (!empty($location['website'])) : ?>
                              <a href="<?php echo esc_url((string) $location['website']); ?>" target="_blank" rel="noopener noreferrer"><?php echo springapex_icon('link', 'icon icon-sm'); ?><span><?php esc_html_e('Website', 'springapex'); ?></span></a>
                            <?php endif; ?>
                          </div>
                        </li>
                      <?php endforeach; ?>
                    </ul>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>

      <aside class="sa-contact-network__form-wrap" data-reveal="up">
        <h2><?php esc_html_e('Send an Inquiry', 'springapex'); ?></h2>
        <p><?php esc_html_e('Tell us about your project. Our team will get back to you promptly.', 'springapex'); ?></p>

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

        <form id="contact-form" class="contact-form sa-contact-network__form" data-contact-form method="post" action="<?php echo esc_url($form_action); ?>" enctype="multipart/form-data" novalidate>
          <input type="hidden" name="action" value="springapex_contact">
          <?php if (defined('SPRINGAPEX_PREVIEW')) : ?>
            <input type="hidden" name="springapex_contact_nonce" value="">
          <?php else : ?>
            <?php wp_nonce_field('springapex_contact', 'springapex_contact_nonce', false); ?>
          <?php endif; ?>
          <input type="hidden" name="intent" value="<?php echo esc_attr($intent); ?>">
          <input type="hidden" name="form_context" value="full">
          <input type="hidden" name="source" value="<?php echo esc_attr((string) get_queried_object_id()); ?>">
          <input type="hidden" name="product" value="<?php echo esc_attr($product); ?>">
          <input type="hidden" name="industry" value="<?php echo esc_attr($industry); ?>">
          <input type="hidden" name="document" value="<?php echo esc_attr($document); ?>">
          <input type="hidden" name="started_at" value="<?php echo esc_attr((string) time()); ?>" data-form-started-at>
          <label class="honeypot" aria-hidden="true">Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label>

          <?php // 联系页主表单基础字段区 + 被「表单设置」标为必填的技术参数字段：
          // 必填字段必须常驻可见（藏在默认折叠的 Optional 区里会被 checkValidity
          // 拦住且聚焦不到），可选技术参数留在下方折叠区，两处各渲染一次。
          $basic_ids = ['name', 'phone', 'company', 'email', 'country'];
          $contact_spec_fields = array_filter(
              springapex_form_schema()['contact']['fields'] ?? [],
              static fn (array $f): bool => !in_array($f['id'], $basic_ids, true)
          );
          $spec_optional_ids = [];
          foreach ($contact_spec_fields as $contact_spec_field) {
              if (empty($contact_spec_field['required'])) {
                  $spec_optional_ids[] = (string) $contact_spec_field['id'];
              }
          }
          springapex_render_form_schema_fields('contact', 'field', '', $spec_optional_ids); ?>
          <label class="field">
            <span><?php esc_html_e('How can we help?', 'springapex'); ?> *</span>
            <select name="inquiry_type" data-inquiry-type required>
              <?php foreach ($types as $type) : ?>
                <option value="<?php echo esc_attr((string) $type); ?>" <?php selected($selected_type, $type); ?>><?php echo esc_html((string) $type); ?></option>
              <?php endforeach; ?>
            </select>
          </label>

          <div class="sa-contact-upload" data-drawing-upload<?php echo $drawing_upload_open ? '' : ' hidden'; ?>>
            <?php get_template_part('parts/drawing-preparation-guide', null, [
                'visible' => true,
            ]); ?>
            <label class="field file-field" data-drawing-field>
              <span><?php esc_html_e('Upload files (PDF, Word, CAD, JPG or PNG - max 10 files, 10 MB total)', 'springapex'); ?></span>
              <input type="file" name="drawing[]" accept=".pdf,.doc,.docx,.dwg,.dxf,.step,.stp,.iges,.igs,.jpg,.jpeg,.png" multiple data-contact-file-input>
              <ul class="contact-file-list" data-contact-file-list hidden></ul>
            </label>
          </div>

          <?php if ($spec_optional_ids !== []) : ?>
          <details class="sa-contact-project-details">
            <summary>
              <span>
                <strong><?php esc_html_e('Add project details', 'springapex'); ?></strong>
                <small><?php esc_html_e('Optional — more information helps us assess your request more accurately.', 'springapex'); ?></small>
              </span>
            </summary>
            <div class="sa-contact-project-details__body">
              <fieldset class="sa-contact-specs">
                <legend><?php esc_html_e('Basic spring details', 'springapex'); ?></legend>
                <p><?php esc_html_e('Share any details you already know. Every field below is optional.', 'springapex'); ?></p>
                <?php
                // 仅渲染可选技术参数（$spec_optional_ids 上方已算好）；
                // 必填的已在主区域渲染，此处再渲染会产生重名字段相互覆盖。
                $spec_fields = array_filter(
                    springapex_form_schema()['contact']['fields'] ?? [],
                    static fn (array $f): bool => in_array($f['id'], $spec_optional_ids, true)
                );
                foreach ($spec_fields as $spec_field) {
                    springapex_render_form_schema_field('contact', $spec_field);
                }
                ?>
              </fieldset>
            </div>
          </details>
          <?php endif; ?>
          <button class="btn btn-primary btn-block" type="submit" data-submit-button>
            <span><?php esc_html_e('Send Inquiry', 'springapex'); ?></span> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </button>
          <?php if (springapex_form_turnstile_enabled('contact')) : ?>
          <div class="sa-turnstile-widget">
            <div
              class="cf-turnstile"
              data-sitekey="<?php echo esc_attr(springapex_turnstile_site_key()); ?>"
              data-size="flexible"
              data-theme="light"
              data-language="en"
              data-action="contact-inquiry"
            ></div>
            <?php echo springapex_turnstile_noscript(); ?>
          </div>
          <?php endif; ?>
          <p class="form-status<?php echo !empty($status['type']) ? ' is-' . esc_attr((string) $status['type']) : ''; ?>" data-form-status role="status" aria-live="polite" <?php echo $status ? '' : 'hidden'; ?>><?php echo $status ? esc_html((string) $status['message']) : ''; ?></p>
        </form>

        <div class="sa-contact-network__direct">
          <p><?php esc_html_e('Or contact us directly', 'springapex'); ?></p>
          <a href="mailto:<?php echo esc_attr((string) ($brand['email'] ?? '')); ?>"><?php echo springapex_icon('mail', 'icon icon-sm'); ?><span><?php echo esc_html((string) ($brand['email'] ?? '')); ?></span></a>
          <?php if ($whatsapp_number !== '') : ?>
            <a href="https://wa.me/<?php echo esc_attr($whatsapp_number); ?>"><?php echo springapex_icon('chat', 'icon icon-sm'); ?><span><?php echo esc_html((string) ($brand['whatsapp'] ?? $brand['phone'] ?? '')); ?></span></a>
          <?php endif; ?>
        </div>
      </aside>
    </div>
  </div>
</section>
