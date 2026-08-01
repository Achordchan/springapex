<?php
if (!defined('ABSPATH')) {
    exit;
}

$company = springapex_get('company', []);
$manufacturing = $company['manufacturing'] ?? [];
$facts = array_slice($company['facts'] ?? [], 0, 5);
if (!$manufacturing || !$facts) {
    return;
}
?>
<section class="section sa-company-proof">
  <div class="container container-wide sa-company-proof__grid">
    <figure class="sa-company-proof__media" data-reveal="up">
      <?php echo springapex_image((string) ($manufacturing['image'] ?? ''), __('Representative precision spring manufacturing workflow', 'springapex'), [
          'width' => 1920,
          'height' => 700,
          'sizes' => '(max-width: 760px) 100vw, 52vw',
      ]); ?>
    </figure>
    <div class="sa-company-proof__copy" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($manufacturing['eyebrow'] ?? 'MANUFACTURING')); ?></p>
      <h2><?php echo esc_html((string) ($manufacturing['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($manufacturing['text'] ?? '')); ?></p>
      <dl class="sa-fact-grid">
        <?php foreach ($facts as $fact) : ?>
          <div class="sa-fact">
            <dt><?php echo esc_html((string) ($fact['label'] ?? '')); ?></dt>
            <dd><?php echo esc_html((string) ($fact['value'] ?? '')); ?></dd>
          </div>
        <?php endforeach; ?>
      </dl>
    </div>
  </div>
</section>
