<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_route = springapex_current_route();
$items = [
    ['label' => 'Capabilities', 'route' => 'capabilities', 'href' => '/capabilities/'],
    ['label' => 'Manufacturing Videos', 'route' => 'manufacturing-videos', 'href' => '/manufacturing-videos/'],
];
?>
<nav class="sa-capabilities-subnav" aria-label="<?php esc_attr_e('Custom Springs', 'springapex'); ?>">
  <div class="container container-wide sa-capabilities-subnav__inner">
    <?php foreach ($items as $item) : ?>
      <?php $active = (string) $item['route'] === $current_route; ?>
      <a class="sa-capabilities-subnav__link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url(springapex_url((string) $item['href'])); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
        <?php echo esc_html((string) $item['label']); ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
