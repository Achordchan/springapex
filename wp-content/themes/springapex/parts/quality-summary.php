<?php
if (!defined('ABSPATH')) {
    exit;
}

$quality = is_array($args['quality'] ?? null) ? $args['quality'] : springapex_get('company.quality', []);
$standards = is_array($quality['standards'] ?? null) ? $quality['standards'] : [];
$certificates = is_array($quality['certificates'] ?? null) ? $quality['certificates'] : [];
$variant = sanitize_key((string) ($args['variant'] ?? 'default'));
$is_archive = $variant === 'archive';
if (!$quality) {
    return;
}
?>
<section class="section sa-quality-summary<?php echo $is_archive ? ' sa-quality-summary--archive' : ''; ?>">
  <div class="container container-wide sa-quality-summary__grid">
    <figure class="sa-quality-summary__media" data-reveal="up">
      <?php echo springapex_image((string) ($quality['image'] ?? ''), __('Representative spring inspection setup', 'springapex'), [
          'width' => 960,
          'height' => 720,
          'sizes' => '(max-width: 760px) 100vw, 46vw',
      ]); ?>
      <?php if ($is_archive) : ?>
        <figcaption><?php esc_html_e('Spring inspection in process.', 'springapex'); ?></figcaption>
      <?php endif; ?>
    </figure>
    <div class="sa-quality-summary__content" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($quality['eyebrow'] ?? 'QUALITY SYSTEMS')); ?></p>
      <h2><?php echo esc_html((string) ($quality['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html($is_archive ? __('Controlled inspection and traceable records.', 'springapex') : (string) ($quality['text'] ?? '')); ?></p>
      <div class="sa-quality-summary__checks">
        <?php if ($is_archive) : ?>
          <span>
            <?php echo springapex_icon('check-shield'); ?>
            <span><strong><?php esc_html_e('Process control', 'springapex'); ?></strong><small><?php esc_html_e('Defined checks at every stage.', 'springapex'); ?></small></span>
          </span>
          <span>
            <?php echo springapex_icon('search'); ?>
            <span><strong><?php esc_html_e('Inspection equipment', 'springapex'); ?></strong><small><?php esc_html_e('Calibrated tools for reliable measurement.', 'springapex'); ?></small></span>
          </span>
          <span>
            <?php echo springapex_icon('form'); ?>
            <span><strong><?php esc_html_e('Traceable records', 'springapex'); ?></strong><small><?php esc_html_e('Records linked to recognized standards.', 'springapex'); ?></small></span>
          </span>
        <?php else : ?>
          <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Process control', 'springapex'); ?></span>
          <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Inspection equipment', 'springapex'); ?></span>
          <span><?php echo springapex_icon('check-shield'); ?><?php esc_html_e('Traceable records', 'springapex'); ?></span>
        <?php endif; ?>
      </div>
      <?php if (!$is_archive && $standards) : ?>
        <div class="sa-quality-summary__standards">
          <p class="section-kicker"><?php esc_html_e('REPORTED QUALITY SYSTEMS', 'springapex'); ?></p>
          <div>
            <?php foreach ($standards as $standard) : ?>
              <span><?php echo springapex_icon('check-shield'); ?><strong><?php echo esc_html((string) ($standard['name'] ?? '')); ?></strong></span>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
      <?php if (!$is_archive) : ?>
        <a class="btn btn-secondary sa-quality-summary__action" href="#quality-certificates">
          <?php esc_html_e('View Certifications', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      <?php endif; ?>
    </div>
    <?php get_template_part('parts/certification-gallery', null, [
        'id' => 'quality-certificates',
        'certificates' => $certificates,
        'variant' => $is_archive ? 'strip' : 'default',
        'viewer' => $is_archive,
    ]); ?>
  </div>
</section>
