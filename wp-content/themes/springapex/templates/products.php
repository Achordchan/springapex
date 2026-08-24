<?php
if (!defined('ABSPATH')) {
    exit;
}
$products_data = springapex_get('products', []);
$hero = $products_data['hero'] ?? [];
$entry = is_array($products_data['entry'] ?? null) ? $products_data['entry'] : [];
$range = is_array($products_data['range'] ?? null) ? $products_data['range'] : [];
$products = springapex_products();
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'products',
    'title' => $hero['title'] ?? 'Products',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'products-hero-v3.png',
    'mobile_image' => $hero['mobile_image'] ?? 'products-hero-mobile-v1.png',
    'ctas' => [
        ['label' => (string) ($hero['primary_cta']['label'] ?? ''), 'href' => (string) ($hero['primary_cta']['href'] ?? '#product-families'), 'icon' => (string) ($hero['primary_cta']['icon'] ?? 'arrow-right')],
        ['label' => (string) ($hero['drawing_cta']['label'] ?? ''), 'href' => (string) ($hero['drawing_cta']['href'] ?? '/contact/?intent=drawing'), 'icon' => (string) ($hero['drawing_cta']['icon'] ?? 'upload'), 'style' => 'ghost'],
    ],
]);
?>

<section class="section sa-product-entry-paths">
  <div class="container container-wide">
    <div class="sa-products-intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php echo esc_html((string) ($entry['eyebrow'] ?? '')); ?></p>
        <h2><?php echo esc_html((string) ($entry['title'] ?? '')); ?></h2>
      </div>
      <p><?php echo esc_html((string) ($entry['text'] ?? '')); ?></p>
    </div>
    <div class="sa-product-entry-paths__grid" data-reveal-group>
      <?php foreach ((array) ($entry['items'] ?? []) as $item) : ?>
        <a class="sa-entry-path-card" href="<?php echo esc_url(springapex_url((string) ($item['href'] ?? ''))); ?>">
          <div class="sa-entry-path-card__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'arrow-right'), 'icon'); ?></div>
          <div class="sa-entry-path-card__content">
            <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
            <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
          </div>
          <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section featured-section sa-product-families" id="product-families">
  <div class="container container-wide">
    <div class="sa-products-intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php echo esc_html((string) ($range['eyebrow'] ?? '')); ?></p>
        <h2><?php echo esc_html((string) ($range['title'] ?? '')); ?></h2>
      </div>
      <p><?php echo esc_html((string) ($range['text'] ?? '')); ?></p>
    </div>
    <div class="product-grid product-grid--all" data-reveal-group>
      <?php foreach ($products as $product) : ?>
        <article class="product-card product-card--<?php echo esc_attr(sanitize_key((string) $product['slug'])); ?>">
          <a class="product-media" href="<?php echo esc_url(springapex_product_url($product)); ?>">
            <?php echo springapex_image($product['featured_image'] ?? $product['image'] ?? '', (string) $product['title'], [
                'width' => 1200,
                'height' => 1200,
                'sizes' => '(max-width: 700px) 50vw, 33vw',
            ]); ?>
          </a>
          <h3><a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) $product['title']); ?></a></h3>
          <p><?php echo esc_html((string) ($product['desc'] ?? '')); ?></p>
          <a class="text-link" href="<?php echo esc_url(springapex_product_url($product)); ?>">
            <?php esc_html_e('View details', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php $selection = springapex_get('product_selection', []); ?>
<section class="section sa-selection-guide">
  <div class="container container-wide sa-selection-guide__layout">
    <div class="sa-selection-guide__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php echo esc_html((string) ($selection['eyebrow'] ?? '')); ?></p>
        <h2><?php echo esc_html((string) ($selection['title'] ?? '')); ?></h2>
      </div>
      <p><?php echo esc_html((string) ($selection['text'] ?? '')); ?></p>
    </div>
    <div class="sa-selection-guide__grid" data-reveal-group>
      <?php foreach (($selection['items'] ?? []) as $item) : ?>
        <article>
          <span><?php echo springapex_icon((string) ($item['icon'] ?? 'spring')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
