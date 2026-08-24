<?php
if (!defined('ABSPATH')) {
    exit;
}

$resources = springapex_get('resources', []);
$downloads = array_values(array_filter(
    is_array($resources['downloads'] ?? null) ? $resources['downloads'] : [],
    static fn(mixed $item): bool => is_array($item)
        && springapex_image_value_available($item['cover'] ?? '')
        && trim((string) ($item['document'] ?? '')) !== ''
));
$library = is_array($resources['library'] ?? null) ? $resources['library'] : [];
$industry_section = is_array($resources['industry'] ?? null) ? $resources['industry'] : [];
$industry_brochures = (array) ($industry_section['items'] ?? []);
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
        <p class="section-kicker"><?php echo esc_html((string) ($library['eyebrow'] ?? '')); ?></p>
        <h2 id="sa-download-center-title"><?php echo esc_html((string) ($library['title'] ?? '')); ?></h2>
        <p><?php echo esc_html((string) ($library['text'] ?? '')); ?></p>
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
        <?php $document_url = springapex_file_url(is_int($download['document'] ?? null) ? $download['document'] : (string) ($download['document'] ?? ''), 'assets/documents'); ?>
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
        <p class="section-kicker"><?php echo esc_html((string) ($industry_section['eyebrow'] ?? '')); ?></p>
        <h3 id="sa-industry-downloads-title"><?php echo esc_html((string) ($industry_section['title'] ?? '')); ?></h3>
      </header>
      <ul>
        <?php foreach ($industry_brochures as $industry) : ?>
          <li><strong><?php echo esc_html((string) $industry); ?></strong><small><?php echo esc_html((string) ($industry_section['status_text'] ?? '')); ?></small></li>
        <?php endforeach; ?>
      </ul>
      <a class="sa-download-industries__action" href="<?php echo esc_url(springapex_url((string) ($industry_section['action_href'] ?? ''))); ?>">
        <?php echo springapex_icon('form', 'icon icon-sm'); ?>
        <?php echo esc_html((string) ($industry_section['action_label'] ?? '')); ?>
      </a>
    </section>
  </div>
</section>
