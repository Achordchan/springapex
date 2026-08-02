<?php
if (!defined('ABSPATH')) {
    exit;
}

$quality = is_array($args['quality'] ?? null) ? $args['quality'] : springapex_get('company.quality', []);
$standards = is_array($quality['standards'] ?? null) ? $quality['standards'] : [];
if (!$quality) {
    return;
}
?>
<section class="section sa-quality-summary">
  <div class="container container-wide sa-quality-summary__grid">
    <figure class="sa-quality-summary__media" data-reveal="up">
      <?php echo springapex_image((string) ($quality['image'] ?? ''), __('Representative spring inspection setup', 'springapex'), [
          'width' => 960,
          'height' => 720,
          'sizes' => '(max-width: 760px) 100vw, 46vw',
      ]); ?>
    </figure>
    <div class="sa-quality-summary__content" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($quality['eyebrow'] ?? 'QUALITY SYSTEMS')); ?></p>
      <h2><?php echo esc_html((string) ($quality['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($quality['text'] ?? '')); ?></p>
      <div class="sa-quality-summary__checks">
        <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Process control', 'springapex'); ?></span>
        <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Inspection equipment', 'springapex'); ?></span>
        <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Traceable records', 'springapex'); ?></span>
      </div>
      <?php if ($standards) : ?>
        <div class="sa-quality-summary__standards">
          <p class="section-kicker"><?php esc_html_e('REPORTED QUALITY SYSTEMS', 'springapex'); ?></p>
          <div>
            <?php foreach ($standards as $standard) : ?>
              <span><?php echo springapex_icon('check-shield'); ?><strong><?php echo esc_html((string) ($standard['name'] ?? '')); ?></strong></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <a class="btn btn-secondary sa-quality-summary__action" href="<?php echo esc_url(springapex_url('/contact/?intent=quality')); ?>">
        <?php esc_html_e('Request qualification documents', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
  </div>
</section>
