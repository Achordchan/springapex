<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = springapex_about_navigation_items();
$current_route = springapex_current_route();
?>
<nav class="sa-about-subnav" aria-label="<?php esc_attr_e('About ApexSpring', 'springapex'); ?>">
  <div class="container container-wide sa-about-subnav__inner">
    <?php foreach ($items as $item) : ?>
      <?php $active = (string) ($item['route'] ?? '') === $current_route; ?>
      <a class="sa-about-subnav__link<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url(springapex_url((string) ($item['href'] ?? '/about/'))); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
        <?php echo esc_html((string) ($item['label'] ?? '')); ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
