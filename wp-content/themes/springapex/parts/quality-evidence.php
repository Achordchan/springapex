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
    <div class="sa-evidence__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('QUALITY EVIDENCE', 'springapex'); ?></p>
        <h2><?php echo esc_html($title); ?></h2>
      </div>
      <p><?php esc_html_e('Inspection scope and release records are agreed around the drawing, operating conditions and customer qualification requirements.', 'springapex'); ?></p>
    </div>
    <div class="sa-evidence__grid" data-reveal-group>
      <?php foreach ($items as $index => $item) : ?>
        <article>
          <span class="sa-evidence__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'check-shield')); ?></span>
          <div>
            <span class="sa-evidence__index"><?php echo esc_html(sprintf('%02d', $index + 1)); ?></span>
            <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
            <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
