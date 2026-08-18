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
    <div class="sa-process-detail__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('CONTROLLED PROCESS', 'springapex'); ?></p>
        <h2><?php esc_html_e('From requirement review to verified delivery.', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('A disciplined six-stage workflow keeps engineering decisions, production controls and delivery requirements aligned from the start.', 'springapex'); ?></p>
    </div>
    <ol class="sa-process-detail__grid" data-reveal-group>
      <?php foreach ($process as $item) : ?>
        <li>
          <?php if (!empty($item['image'])) : ?>
            <figure class="sa-process-detail__media">
              <?php echo springapex_image((string) $item['image'], sprintf(__('%s at ApexSpring', 'springapex'), (string) ($item['title'] ?? '')), [
                  'width' => 640,
                  'height' => 400,
                  'sizes' => '(max-width: 760px) 100vw, (max-width: 1180px) 50vw, 370px',
              ]); ?>
            </figure>
          <?php endif; ?>
          <div class="sa-process-detail__top">
            <span class="sa-process-detail__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'settings')); ?></span>
            <span class="sa-process-detail__step"><?php echo esc_html((string) ($item['step'] ?? '')); ?></span>
          </div>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
