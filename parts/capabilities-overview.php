<?php
if (!defined('ABSPATH')) {
    exit;
}

$capabilities = is_array($args['capabilities'] ?? null)
    ? $args['capabilities']
    : springapex_get('capabilities', []);
$intro = $capabilities['intro'] ?? [];
$section_id = trim((string) ($args['id'] ?? ''));
?>
<section class="section capabilities-section"<?php echo $section_id !== '' ? ' id="' . esc_attr($section_id) . '"' : ''; ?>>
  <div class="container container-wide capabilities-grid">
    <div class="capabilities-intro" data-reveal="up">
      <h2><?php echo esc_html($intro['title'] ?? 'Our Capabilities'); ?></h2>
      <p><?php echo esc_html($intro['text'] ?? ''); ?></p>
    </div>
    <div class="capabilities-list" data-reveal-group>
      <?php foreach (($capabilities['items'] ?? []) as $capability) : ?>
        <article class="capability-item">
          <div class="capability-icon"><?php echo springapex_icon((string) $capability['icon']); ?></div>
          <h3><?php echo esc_html((string) $capability['title']); ?></h3>
          <p><?php echo esc_html((string) $capability['text']); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
