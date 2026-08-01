<?php
if (!defined('ABSPATH')) {
    exit;
}

$process = is_array($args['process'] ?? null) ? $args['process'] : springapex_get('manufacturing_process', []);
if (!$process) {
    return;
}
?>
<section class="section sa-process-detail">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('CONTROLLED PROCESS', 'springapex'); ?></p>
      <h2><?php esc_html_e('From requirement review to verified delivery.', 'springapex'); ?></h2>
    </div>
    <ol class="sa-process-detail__grid" data-reveal-group>
      <?php foreach ($process as $item) : ?>
        <li>
          <span class="sa-process-detail__step"><?php echo esc_html((string) ($item['step'] ?? '')); ?></span>
          <span class="sa-process-detail__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'settings')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
