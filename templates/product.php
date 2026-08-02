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

$catalog_url = (string) ($product['catalog_url'] ?? '');
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
$gallery_images = [];
foreach ([$product['image'] ?? '', $product['category_image'] ?? '', $product['featured_image'] ?? '', 'quality-inspection-original.jpg'] as $gallery_image) {
    $key = is_array($gallery_image) ? (string) ($gallery_image['file'] ?? '') : (string) $gallery_image;
    if ($key !== '' && !isset($gallery_images[$key])) {
        $gallery_images[$key] = $gallery_image;
    }
}
$gallery_images = array_slice(array_values($gallery_images), 0, 3);
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
    <a class="tab is-active" href="#overview" data-section="overview" aria-current="true"><?php esc_html_e('Overview', 'springapex'); ?></a>
    <a class="tab" href="#specifications" data-section="specifications"><?php esc_html_e('Specifications', 'springapex'); ?></a>
    <a class="tab" href="#materials" data-section="materials"><?php esc_html_e('Materials', 'springapex'); ?></a>
    <a class="tab" href="#applications" data-section="applications"><?php esc_html_e('Applications', 'springapex'); ?></a>
    <a class="tab" href="#quality" data-section="quality"><?php esc_html_e('Quality & Documents', 'springapex'); ?></a>
  </div>
</nav>

<?php if ($gallery_images) : ?>
<section class="section sa-product-gallery" aria-labelledby="product-gallery-title">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('PRODUCT VIEW', 'springapex'); ?></p>
      <h2 id="product-gallery-title"><?php esc_html_e('Geometry, finish and inspection context.', 'springapex'); ?></h2>
    </div>
    <div class="sa-product-gallery__track">
      <?php foreach ($gallery_images as $index => $gallery_image) : ?>
        <figure>
          <?php echo springapex_image($gallery_image, sprintf(__('%s product view %d', 'springapex'), (string) $product['title'], $index + 1), [
              'width' => 1000,
              'height' => 760,
              'sizes' => '(max-width: 760px) 86vw, 33vw',
          ]); ?>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section product-overview" id="overview" data-product-section>
  <div class="container container-wide product-overview-grid">
    <div class="overview-copy" data-reveal="up">
      <h2><?php esc_html_e('Overview', 'springapex'); ?></h2>
      <p><?php echo esc_html((string) ($product['overview'] ?? '')); ?></p>
      <?php if (!empty($product['diagram'])) : ?>
        <div class="diagram-card">
          <?php echo springapex_image((string) $product['diagram'], __('Compression spring dimension diagram', 'springapex'), [
              'width' => 840,
              'height' => 440,
              'sizes' => '(max-width: 760px) 100vw, 45vw',
          ]); ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="spec-table-wrap" id="specifications" data-product-section data-reveal="up">
      <h2 class="sr-only"><?php esc_html_e('Specifications', 'springapex'); ?></h2>
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

<section class="section sa-faq">
  <div class="container container-wide sa-faq__layout">
    <div class="sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('ENGINEERING FAQ', 'springapex'); ?></p>
      <h2><?php esc_html_e('Questions that help define the right spring.', 'springapex'); ?></h2>
    </div>
    <div class="sa-faq__list">
      <details><summary><?php esc_html_e('What information should I send for a quotation?', 'springapex'); ?></summary><p><?php esc_html_e('Send the drawing or installation geometry, required load or torque, material or environment, quantity and expected cycle life.', 'springapex'); ?></p></details>
      <details><summary><?php esc_html_e('Can you review an existing design?', 'springapex'); ?></summary><p><?php esc_html_e('Yes. Engineering review can identify manufacturability, tolerance, material and inspection questions before sampling.', 'springapex'); ?></p></details>
      <details><summary><?php esc_html_e('How are materials and finishes selected?', 'springapex'); ?></summary><p><?php esc_html_e('Selection depends on stress, fatigue, corrosion, temperature, cleanliness and any industry-specific documentation.', 'springapex'); ?></p></details>
      <details><summary><?php esc_html_e('Can inspection reports be supplied?', 'springapex'); ?></summary><p><?php esc_html_e('Inspection, material and traceability documents can be defined during quotation according to project requirements.', 'springapex'); ?></p></details>
    </div>
  </div>
</section>

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
