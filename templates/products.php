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
    'ctas' => [
        ['label' => 'Browse Products', 'href' => '#product-families', 'icon' => 'arrow-right'],
        ['label' => 'Submit Custom Requirement', 'href' => '/contact/?intent=drawing', 'icon' => 'upload', 'style' => 'ghost'],
    ],
]);
?>

<section class="section sa-product-entry-paths">
  <div class="container container-wide">
    <div class="sa-product-entry-paths__grid" data-reveal-group>
      <a class="sa-entry-path-card" href="#product-families">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('spring', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Find by Product Type', 'springapex'); ?></h3>
          <p><?php esc_html_e('I know the spring type I need. Show me compression, extension, torsion, disc, wire form or die springs.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
      <a class="sa-entry-path-card" href="<?php echo esc_url(springapex_url('/contact/?intent=solution')); ?>">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('gear', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Describe Your Application', 'springapex'); ?></h3>
          <p><?php esc_html_e('I have a load, space or motion requirement but need help selecting the right spring type and parameters.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
      <a class="sa-entry-path-card" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
        <div class="sa-entry-path-card__icon"><?php echo springapex_icon('upload', 'icon'); ?></div>
        <div class="sa-entry-path-card__content">
          <h3><?php esc_html_e('Upload Drawing for Quote', 'springapex'); ?></h3>
          <p><?php esc_html_e('I have a drawing, sample or specification ready. Get engineering review and quotation.', 'springapex'); ?></p>
        </div>
        <span class="sa-entry-path-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
      </a>
    </div>
  </div>
</section>

<section class="section featured-section sa-product-families" id="product-families">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('PRODUCT RANGE', 'springapex'); ?></p>
      <h2><?php esc_html_e('Product families for different load and motion requirements.', 'springapex'); ?></h2>
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
    <div class="sa-section-intro" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('SELECTION GUIDE', 'springapex'); ?></p>
      <h2><?php echo esc_html((string) ($selection['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($selection['text'] ?? '')); ?></p>
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

<section class="section help-band">
  <div class="container container-wide help-band-inner" data-reveal="up">
    <div class="help-copy">
      <h2><?php esc_html_e('Need help choosing the right spring family?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Share the load, movement and operating conditions for a focused recommendation.', 'springapex'); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
        <?php esc_html_e('Talk to an Engineer', 'springapex'); ?> <?php echo springapex_icon('chat', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="help-media">
      <?php echo springapex_image('spring-assortment-v3.png', 'Assorted precision springs', [
          'width' => 1600,
          'height' => 560,
          'sizes' => '(max-width: 760px) 100vw, 50vw',
      ]); ?>
    </div>
  </div>
</section>

<section class="section sa-specialty-products">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('SPECIALTY COMPONENTS', 'springapex'); ?></p>
      <h2><?php esc_html_e('More spring forms for specific mechanisms.', 'springapex'); ?></h2>
    </div>
    <div class="sa-specialty-products__grid" data-reveal-group>
      <?php foreach (springapex_get('specialty_products', []) as $item) : ?>
        <article>
          <span class="sa-specialty-products__icon"><?php echo springapex_icon((string) ($item['icon'] ?? 'spring')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section sa-products-resource-link" id="resources">
  <div class="container container-wide sa-products-resource-link__inner" data-reveal="up">
    <div>
      <p class="section-kicker"><?php esc_html_e('BEFORE YOU REQUEST A QUOTE', 'springapex'); ?></p>
      <h2><?php esc_html_e('Prepare the right spring requirements.', 'springapex'); ?></h2>
      <p><?php esc_html_e('Use our RFQ, material and inspection guides to prepare a clearer engineering request.', 'springapex'); ?></p>
    </div>
    <a class="btn btn-outline" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
      <?php esc_html_e('Open Engineering Resources', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
