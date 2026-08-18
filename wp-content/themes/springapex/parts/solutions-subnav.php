<?php
if (!defined('ABSPATH')) {
    exit;
}

$subnav_args = is_array($args ?? null) ? $args : [];
$active = sanitize_key((string) ($subnav_args['active'] ?? 'industries'));
$items = [
    ['slug' => 'industries', 'label' => 'Industries', 'href' => '/solutions/'],
    ['slug' => 'case-studies', 'label' => 'Case Studies', 'href' => '/case-studies/'],
];
?>
<nav class="sa-solutions-subnav" aria-label="<?php esc_attr_e('Industries and case studies', 'springapex'); ?>">
  <div class="container container-wide">
    <?php foreach ($items as $item) : ?>
      <a class="<?php echo $active === $item['slug'] ? 'is-active' : ''; ?>" href="<?php echo esc_url(springapex_url($item['href'])); ?>" <?php echo $active === $item['slug'] ? 'aria-current="page"' : ''; ?>>
        <?php echo esc_html((string) $item['label']); ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>
