<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($args['items'] ?? null) ? $args['items'] : springapex_get('quality_evidence', []);
$title = (string) ($args['title'] ?? __('Verification planned around the application.', 'springapex'));
if (!$items) {
    return;
}
?>
<section class="section sa-evidence">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('QUALITY EVIDENCE', 'springapex'); ?></p>
      <h2><?php echo esc_html($title); ?></h2>
    </div>
    <div class="sa-evidence__grid" data-reveal-group>
      <?php foreach ($items as $item) : ?>
        <article>
          <span class="sa-evidence__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'check-shield')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
