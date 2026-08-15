<?php
if (!defined('ABSPATH')) {
    exit;
}
$products_data = springapex_get('products', []);
$hero = $products_data['hero'] ?? [];
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
        ['label' => 'Browse Products', 'href' => '#product-families', 'icon' => 'arrow-right'],
        ['label' => 'Send a Drawing', 'href' => '/contact/?intent=drawing', 'icon' => 'upload', 'style' => 'ghost'],
    ],
]);
?>

<section class="section sa-product-entry-paths">
  <div class="container container-wide">
    <div class="sa-products-intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('START HERE', 'springapex'); ?></p>
        <h2><?php esc_html_e('Choose How to Start', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('Describe your application or send a drawing for review.', 'springapex'); ?></p>
    </div>
    <div class="sa-product-entry-paths__grid" data-reveal-group>
      <a class="sa-entry-path-card" href="<?php echo esc_url(springapex_url('/contact/?intent=solution')); ?>">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('gear', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Describe Your Application', 'springapex'); ?></h3>
          <p><?php esc_html_e('Share the load, space and motion requirements for engineering guidance.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
      <a class="sa-entry-path-card" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('upload', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Upload Drawing for Quote', 'springapex'); ?></h3>
          <p><?php esc_html_e('Send a drawing or specification for review and quotation.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
      <a class="sa-entry-path-card" href="#product-families">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('spring', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Find by Product Type', 'springapex'); ?></h3>
          <p><?php esc_html_e('Browse spring families by load direction and component type.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
    </div>
  </div>
</section>

<section class="section featured-section sa-product-families" id="product-families">
  <div class="container container-wide">
    <div class="sa-products-intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('PRODUCT RANGE', 'springapex'); ?></p>
        <h2><?php esc_html_e('Spring families for every load and motion.', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('Compare force direction, space, material and operating conditions.', 'springapex'); ?></p>
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
        <p class="section-kicker"><?php esc_html_e('SELECTION GUIDE', 'springapex'); ?></p>
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
