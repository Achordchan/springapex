<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = is_array($args['items'] ?? null) ? $args['items'] : springapex_get('resources.items', []);
$limit = isset($args['limit']) ? (int) $args['limit'] : 0;
if ($limit > 0) {
    $items = array_slice($items, 0, $limit);
}
if (!$items) {
    return;
}
?>
<div class="sa-resource-grid" data-reveal-group>
  <?php foreach ($items as $index => $item) : ?>
    <article class="sa-resource-card" id="resource-<?php echo (int) $index + 1; ?>">
      <p class="section-kicker"><?php echo esc_html((string) ($item['type'] ?? 'Guide')); ?></p>
      <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
      <p><?php echo esc_html((string) ($item['summary'] ?? '')); ?></p>
      <?php if (!empty($item['points']) && is_array($item['points'])) : ?>
        <details>
          <summary><?php esc_html_e('Key points', 'springapex'); ?></summary>
          <ul>
            <?php foreach ($item['points'] as $point) : ?><li><?php echo esc_html((string) $point); ?></li><?php endforeach; ?>
          </ul>
        </details>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>
</div>
