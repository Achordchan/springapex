<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$product = null;
if (!defined('SPRINGAPEX_PREVIEW') && function_exists('is_singular') && is_singular('spring_product')) {
    $post_id = (int) get_queried_object_id();
    $slug = (string) get_post_field('post_name', $post_id);
    if (function_exists('post_password_required') && post_password_required($post_id)) {
        echo '<section class="section"><div class="container">' . get_the_password_form($post_id) . '</div></section>';
        return;
    }
    $product = springapex_product_for_view($post_id);
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW') && function_exists('get_query_var')) {
    $slug = (string) get_query_var('product_slug');
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW')) {
    $slug = 'compression-springs';
}
if (!$product && $slug !== '') {
    $product = springapex_product($slug);
}

if (!$product) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('Product not found', 'springapex') . '</h1></div></section>';
    return;
}

if ($slug === 'compression-springs') {
    get_template_part('templates/product-compression', null, [
        'product' => $product,
        'slug' => $slug,
    ]);
    return;
}

$catalog_url = (string) ($product['catalog_url'] ?? '');
$details_source = trim((string) ($product['overview'] ?? ''));
$has_product_details = defined('SPRINGAPEX_PREVIEW') || $details_source !== '';
$materials = !empty($product['materials']) ? $product['materials'] : [
    ['title' => 'Music Wire', 'icon' => 'wire'],
    ['title' => 'Stainless Steel', 'icon' => 'shield'],
    ['title' => 'Alloy & Carbon Steel', 'icon' => 'disc'],
];
$applications = !empty($product['applications']) ? $product['applications'] : [
    ['title' => 'Industrial Equipment', 'icon' => 'gear'],
    ['title' => 'Mobility Systems', 'icon' => 'car'],
    ['title' => 'Precision Assemblies', 'icon' => 'gear'],
];
$specs = !empty($product['specs']) ? $product['specs'] : [
    ['label' => 'Geometry', 'value' => 'Manufactured to drawing or agreed application requirements'],
    ['label' => 'Material', 'value' => 'Selected around load, corrosion, temperature and cycle life'],
    ['label' => 'Surface Treatment', 'value' => 'Available according to material and operating environment'],
    ['label' => 'Inspection', 'value' => 'Critical dimensions and functional characteristics by agreement'],
];
$related_products = springapex_related_products($slug, 3);
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'product',
    'title' => (string) $product['title'],
    'subtitle' => (string) ($product['subtitle'] ?? ''),
    'image' => $product['image'] ?? '',
    'image_alt' => (string) $product['title'],
    'image_sizes' => '(max-width: 760px) 100vw, 52vw',
    'image_width' => 1200,
    'image_height' => 1200,
    'staged_media' => true,
    'breadcrumb' => [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Products', 'href' => '/products/'],
        ['label' => (string) $product['title']],
    ],
    'ctas' => [
        [
            'label' => 'Request a Quote',
            'href' => '/contact/?intent=quote&product=' . $slug,
            'icon' => 'arrow-right',
            'style' => 'primary',
        ],
    ],
]);
?>

<nav class="product-tabs-section" aria-label="<?php esc_attr_e('Product sections', 'springapex'); ?>" data-product-tabs>
  <div class="container container-wide product-tabs">
    <?php if ($has_product_details) : ?>
      <a class="tab is-active" href="#product-details" data-section="product-details" aria-current="true"><?php esc_html_e('Product Details', 'springapex'); ?></a>
    <?php endif; ?>
    <a class="tab<?php echo $has_product_details ? '' : ' is-active'; ?>" href="#specifications" data-section="specifications"<?php echo $has_product_details ? '' : ' aria-current="true"'; ?>><?php esc_html_e('Specifications', 'springapex'); ?></a>
    <a class="tab" href="#materials" data-section="materials"><?php esc_html_e('Materials', 'springapex'); ?></a>
    <a class="tab" href="#applications" data-section="applications"><?php esc_html_e('Applications', 'springapex'); ?></a>
    <a class="tab" href="#quality" data-section="quality"><?php esc_html_e('Quality & Documents', 'springapex'); ?></a>
  </div>
</nav>

<?php if ($has_product_details) : ?>
  <?php get_template_part('parts/product-editor-details', null, [
      'product' => $product,
      'id' => 'product-details',
  ]); ?>
<?php endif; ?>

<section class="sa-product-specifications" id="specifications" data-product-section>
  <div class="container container-wide sa-product-specifications__layout">
    <header class="sa-product-specifications__head" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('PRODUCT DATA', 'springapex'); ?></p>
      <h2><?php esc_html_e('Specifications', 'springapex'); ?></h2>
    </header>
    <div class="spec-table-wrap" data-reveal="up">
      <table class="spec-table">
        <tbody>
          <?php foreach ($specs as $row) : ?>
            <tr>
              <th scope="row"><?php echo esc_html((string) $row['label']); ?></th>
              <td><?php echo esc_html((string) $row['value']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</section>

<section class="section product-meta-grid">
  <div class="container container-wide two-col">
    <div id="materials" data-product-section data-reveal="up">
      <h2><?php esc_html_e('Materials', 'springapex'); ?></h2>
      <p class="muted"><?php esc_html_e('We offer high-performance materials selected around load, environment and service life.', 'springapex'); ?></p>
      <div class="icon-feature-row">
        <?php foreach ($materials as $material) : ?>
          <div class="icon-feature">
            <div class="icon-circle"><?php echo springapex_icon((string) ($material['icon'] ?? 'wire')); ?></div>
            <span><?php echo esc_html((string) $material['title']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/capabilities/')); ?>"><?php esc_html_e('View all capabilities', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </div>
    <div id="applications" data-product-section data-reveal="up">
      <h2><?php esc_html_e('Applications', 'springapex'); ?></h2>
      <p class="muted"><?php esc_html_e('Applications are reviewed around movement, load, environment and expected service life.', 'springapex'); ?></p>
      <div class="icon-feature-row icon-feature-row-wide">
        <?php foreach ($applications as $application) : ?>
          <div class="icon-feature">
            <div class="icon-circle"><?php echo springapex_icon((string) ($application['icon'] ?? 'gear')); ?></div>
            <span><?php echo esc_html((string) $application['title']); ?></span>
          </div>
        <?php endforeach; ?>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/solutions/')); ?>"><?php esc_html_e('View all applications', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </div>
  </div>
</section>

<?php get_template_part('parts/product-quality-documents', null, [
    'catalog_url' => $catalog_url,
    'product_slug' => $slug,
]); ?>

<?php get_template_part('parts/site-faq'); ?>

<?php if ($related_products) : ?>
<section class="section sa-related-products">
  <div class="container container-wide">
    <div class="section-head row-between">
      <h2><?php esc_html_e('Related Product Families', 'springapex'); ?></h2>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/products/')); ?>"><?php esc_html_e('View all products', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </div>
    <div class="sa-product-grid sa-product-grid--three">
      <?php foreach ($related_products as $related_product) : ?>
        <?php get_template_part('parts/product-card', null, ['product' => $related_product]); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section help-band help-band-compact">
  <div class="container container-wide help-band-inner" data-reveal="up">
    <div class="help-media help-media-sm">
      <?php echo springapex_image('spring-assortment-v2.png', __('Spring assortment', 'springapex'), [
          'width' => 800,
          'height' => 280,
          'sizes' => '240px',
      ]); ?>
    </div>
    <div class="help-copy">
      <h2><?php esc_html_e('Not sure which spring is right for your application?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Our engineers are here to help.', 'springapex'); ?></p>
    </div>
    <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer&product=' . $slug)); ?>">
      <?php esc_html_e('Talk to an Expert', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
