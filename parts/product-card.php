<?php
if (!defined('ABSPATH')) {
    exit;
}

$product = is_array($args['product'] ?? null) ? $args['product'] : [];
if (!$product) {
    return;
}
?>
<article class="sa-product-card">
  <a class="sa-product-card__media" href="<?php echo esc_url(springapex_product_url($product)); ?>">
    <?php echo springapex_image($product['featured_image'] ?? $product['category_image'] ?? $product['image'] ?? '', (string) ($product['title'] ?? ''), [
        'width' => 900,
        'height' => 720,
        'sizes' => '(max-width: 480px) 100vw, (max-width: 860px) 50vw, 33vw',
    ]); ?>
  </a>
  <div class="sa-product-card__body">
    <h3><a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a></h3>
    <?php if (!empty($product['desc'])) : ?><p><?php echo esc_html((string) $product['desc']); ?></p><?php endif; ?>
    <a class="text-link" href="<?php echo esc_url(springapex_product_url($product)); ?>">
      <?php esc_html_e('View product family', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</article>
