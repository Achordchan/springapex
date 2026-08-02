<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'resources',
    'title' => springapex_get('resources.hero.title', 'Engineering Resources'),
    'subtitle' => springapex_get('resources.hero.subtitle', ''),
    'image' => 'generated/springapex-resources-hero-v2.webp',
    'mobile_image' => 'generated/springapex-resources-hero-v2.webp',
    'image_width' => 1890,
    'image_height' => 830,
    'ctas' => [[
        'label' => 'Browse Resources',
        'href' => '#resource-index',
        'icon' => 'arrow-right',
    ]],
]);
?>

<section class="section sa-resources-index" id="resource-index">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('TECHNICAL CONTENT CENTER', 'springapex'); ?></p>
      <h2><?php esc_html_e('Plan the design, quotation and quality requirements with clearer inputs.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('These practical guides explain what information to prepare and which engineering decisions should be confirmed before production.', 'springapex'); ?></p>
    </div>
    <?php get_template_part('parts/resources-grid'); ?>
  </div>
</section>

<section class="section sa-resource-paths" id="catalog-download">
  <div class="container container-wide sa-resource-paths__grid sa-document-list--three">
    <article>
      <span><?php echo springapex_icon('download'); ?></span>
      <h2><?php esc_html_e('Catalog & Technical Documents', 'springapex'); ?></h2>
      <p><?php esc_html_e('Tell us which product family or industry you are reviewing. We will send the current catalog, drawing template or controlled technical document.', 'springapex'); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=catalog')); ?>">
        <?php esc_html_e('Request the catalog', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </article>
    <article>
      <span><?php echo springapex_icon('download'); ?></span>
      <h2><?php esc_html_e('Need a controlled product document?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Request the current catalog, drawing template or qualification document for the product and project you are reviewing.', 'springapex'); ?></p>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/?intent=catalog')); ?>"><?php esc_html_e('Request technical documents', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </article>
    <article>
      <span><?php echo springapex_icon('headset'); ?></span>
      <h2><?php esc_html_e('Need application-specific guidance?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Send the operating conditions and expected movement so engineering can identify the right product family and open questions.', 'springapex'); ?></p>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>"><?php esc_html_e('Talk to an engineer', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </article>
  </div>
</section>
