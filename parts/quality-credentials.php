<?php
if (!defined('ABSPATH')) {
    exit;
}

$quality = springapex_get('company.quality', []);
$standards = is_array($quality['standards'] ?? null) ? $quality['standards'] : [];
$variant = sanitize_key((string) ($args['variant'] ?? 'full')) ?: 'full';
if (!$quality || !$standards) {
    return;
}
?>
<section class="section sa-quality sa-quality--<?php echo esc_attr($variant); ?>">
  <div class="container container-wide sa-quality__grid">
    <div class="sa-quality__copy" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($quality['eyebrow'] ?? 'QUALITY')); ?></p>
      <h2><?php echo esc_html((string) ($quality['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($quality['text'] ?? '')); ?></p>
      <?php if (!empty($quality['detail'])) : ?>
        <p class="sa-section-detail"><?php echo esc_html((string) $quality['detail']); ?></p>
      <?php endif; ?>
      <div class="sa-standard-list" data-reveal-group>
        <?php foreach ($standards as $standard) : ?>
          <?php
          $standard_name = (string) ($standard['name'] ?? '');
          $standard_slug = sanitize_title($standard_name);
          $standard_url = trim((string) ($standard['url'] ?? ''));
          $standard_href = $standard_url !== ''
              ? $standard_url
              : springapex_url('/contact/?intent=quality&document=' . rawurlencode($standard_slug));
          $external = $standard_url !== '' && preg_match('#^(?:https?:|//)#i', $standard_url);
          ?>
          <a class="sa-standard" href="<?php echo esc_url($standard_href); ?>"<?php echo $external ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>>
            <span class="sa-standard__icon"><?php echo springapex_icon('check-shield'); ?></span>
            <span><strong><?php echo esc_html($standard_name); ?></strong><small><?php echo esc_html((string) ($standard['scope'] ?? '')); ?></small></span>
            <span class="sa-standard__action"><?php esc_html_e('View document', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/?intent=quality')); ?>">
        <?php esc_html_e('Request qualification documents', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <figure class="sa-quality__media" data-reveal="left">
      <?php echo springapex_image((string) ($quality['image'] ?? ''), __('Representative spring dimensional inspection setup', 'springapex'), [
          'width' => 960,
          'height' => 400,
          'sizes' => '(max-width: 760px) 100vw, 44vw',
      ]); ?>
    </figure>
  </div>
</section>
