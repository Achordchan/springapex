<?php
if (!defined('ABSPATH')) {
    exit;
}

$downloads = [
    [
        'id' => 'company-downloads',
        'category' => 'Company',
        'title' => 'ApexSpring Company Profile',
        'description' => 'Company overview, manufacturing scale, markets, team and production capabilities.',
        'cover' => 'downloads/apexspring-company-profile-cover-v1.png',
        'document' => 'apexspring-company-profile.pdf',
        'pages' => '17 pages',
        'size' => '4.7 MB',
    ],
    [
        'id' => 'product-downloads',
        'category' => 'Products',
        'title' => 'ApexSpring Product Catalog',
        'description' => 'Product families, dimensional references, manufacturing context and application examples.',
        'cover' => 'downloads/apexspring-product-catalog-cover-v1.png',
        'document' => 'apexspring-product-catalog.pdf',
        'pages' => '14 pages',
        'size' => '4.2 MB',
    ],
];

$industry_brochures = [
    'Automotive',
    'Industrial Equipment',
    'Medical',
    'Aerospace',
    'Rail',
    'Energy',
];
$resources_hero = springapex_get('resources.hero', []);
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'resources',
    'title' => $resources_hero['title'] ?? 'Download Center',
    'subtitle' => $resources_hero['subtitle'] ?? 'Company, product and industry brochures in one place.',
    'image' => $resources_hero['image'] ?? 'generated/springapex-resources-hero-v2.webp',
    'mobile_image' => $resources_hero['mobile_image'] ?? 'resources-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
]);
?>

<?php get_template_part('parts/about-subnav'); ?>

<section class="section sa-download-center" id="catalog-download" aria-labelledby="sa-download-center-title">
  <div class="container container-wide">
    <header class="sa-download-library__head" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('AVAILABLE DOWNLOADS', 'springapex'); ?></p>
        <h2 id="sa-download-center-title"><?php esc_html_e('Brochure Library Shelf', 'springapex'); ?></h2>
        <p><?php esc_html_e('Corporate and product resources ready when you are.', 'springapex'); ?></p>
      </div>
      <nav class="sa-download-library__filters" aria-label="<?php esc_attr_e('Download categories', 'springapex'); ?>">
        <a class="is-active" href="#catalog-download"><?php esc_html_e('All', 'springapex'); ?></a>
        <a href="#company-downloads"><?php esc_html_e('Company', 'springapex'); ?></a>
        <a href="#product-downloads"><?php esc_html_e('Products', 'springapex'); ?></a>
        <a href="#industry-downloads"><?php esc_html_e('Industries', 'springapex'); ?></a>
      </nav>
    </header>

    <div class="sa-download-library__shelf" data-reveal-group>
      <?php foreach ($downloads as $download) : ?>
        <?php $document_url = springapex_asset('assets/documents/' . (string) $download['document']); ?>
        <article class="sa-download-volume" id="<?php echo esc_attr((string) $download['id']); ?>">
          <a class="sa-download-volume__cover" href="<?php echo esc_url($document_url); ?>" download aria-label="<?php echo esc_attr(sprintf(__('Download %s PDF', 'springapex'), (string) $download['title'])); ?>">
            <?php echo springapex_image((string) $download['cover'], (string) $download['title'], [
                'width' => 768,
                'height' => 960,
                'sizes' => '(max-width: 760px) 124px, 18vw',
            ]); ?>
          </a>
          <div class="sa-download-volume__content">
            <p class="sa-download-volume__category"><?php echo esc_html((string) $download['category']); ?></p>
            <h3><?php echo esc_html((string) $download['title']); ?></h3>
            <p class="sa-download-volume__description"><?php echo esc_html((string) $download['description']); ?></p>
            <ul class="sa-download-volume__meta" aria-label="<?php esc_attr_e('Document details', 'springapex'); ?>">
              <li>PDF</li>
              <li><?php echo esc_html((string) $download['pages']); ?></li>
              <li><?php echo esc_html((string) $download['size']); ?></li>
              <li><?php esc_html_e('English', 'springapex'); ?></li>
            </ul>
            <a class="sa-download-volume__action" href="<?php echo esc_url($document_url); ?>" download>
              <?php echo springapex_icon('download', 'icon icon-sm'); ?>
              <?php esc_html_e('Download PDF', 'springapex'); ?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <section class="sa-download-industries" id="industry-downloads" aria-labelledby="sa-industry-downloads-title" data-reveal="up">
      <header>
        <p class="section-kicker"><?php esc_html_e('INDUSTRY BROCHURES', 'springapex'); ?></p>
        <h3 id="sa-industry-downloads-title"><?php esc_html_e('Prepared for future industry libraries.', 'springapex'); ?></h3>
      </header>
      <ul>
        <?php foreach ($industry_brochures as $industry) : ?>
          <li><strong><?php echo esc_html($industry); ?></strong><small><?php esc_html_e('Awaiting approved file', 'springapex'); ?></small></li>
        <?php endforeach; ?>
      </ul>
      <a class="sa-download-industries__action" href="<?php echo esc_url(springapex_url('/contact/?intent=catalog')); ?>">
        <?php echo springapex_icon('form', 'icon icon-sm'); ?>
        <?php esc_html_e('Request an industry document', 'springapex'); ?>
      </a>
    </section>
  </div>
</section>
