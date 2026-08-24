<?php
if (!defined('ABSPATH')) {
    exit;
}

$products = springapex_products();
$solutions = springapex_solutions();
$cases = springapex_cases();
$news_items = springapex_news_list();
$main_pages = [
    ['label' => 'Home', 'path' => '/'],
    ['label' => 'About NorenSpring', 'path' => '/about/'],
    ['label' => 'Sustainability', 'path' => '/sustainability/'],
    ['label' => 'Products', 'path' => '/products/'],
    ['label' => 'Industries', 'path' => '/solutions/'],
    ['label' => 'Case Studies', 'path' => '/case-studies/'],
    ['label' => 'Custom Springs', 'path' => '/capabilities/'],
    ['label' => 'Manufacturing Videos', 'path' => '/manufacturing-videos/'],
    ['label' => 'Download Center', 'path' => '/resources/'],
    ['label' => 'News', 'path' => '/news/'],
    ['label' => 'Contact', 'path' => '/contact/'],
];
?>
<div class="sa-legal-page sa-sitemap-page">
  <header class="sa-legal-hero">
    <div class="container sa-legal-container">
      <p class="section-kicker"><?php esc_html_e('SITE DIRECTORY', 'springapex'); ?></p>
      <h1><?php esc_html_e('Sitemap', 'springapex'); ?></h1>
      <p><?php esc_html_e('Browse NorenSpring products, industries, company information and downloadable documents.', 'springapex'); ?></p>
    </div>
  </header>

  <div class="container sa-legal-container sa-sitemap-grid">
    <section>
      <h2><?php esc_html_e('Main Pages', 'springapex'); ?></h2>
      <ul>
        <?php foreach ($main_pages as $page) : ?>
          <li><a href="<?php echo esc_url(springapex_url($page['path'])); ?>"><?php echo esc_html($page['label']); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section>
      <h2><?php esc_html_e('Products', 'springapex'); ?></h2>
      <ul>
        <?php foreach ($products as $product) : ?>
          <li><a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section>
      <h2><?php esc_html_e('Industries', 'springapex'); ?></h2>
      <ul>
        <?php foreach ($solutions as $solution) : ?>
          <li><a href="<?php echo esc_url(springapex_solution_url($solution)); ?>"><?php echo esc_html((string) ($solution['title'] ?? '')); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <?php if ($cases) : ?>
      <section>
        <h2><?php esc_html_e('Case Studies', 'springapex'); ?></h2>
        <ul>
          <?php foreach ($cases as $case) : ?>
            <li><a href="<?php echo esc_url(springapex_case_url($case)); ?>"><?php echo esc_html((string) ($case['title'] ?? '')); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section>
      <h2><?php esc_html_e('News', 'springapex'); ?></h2>
      <ul>
        <?php foreach ($news_items as $news) : ?>
          <li><a href="<?php echo esc_url(springapex_news_url($news)); ?>"><?php echo esc_html((string) ($news['title'] ?? '')); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="sa-sitemap-legal">
      <h2><?php esc_html_e('Legal', 'springapex'); ?></h2>
      <ul>
        <li><a href="<?php echo esc_url(springapex_url('/privacy/')); ?>"><?php esc_html_e('Privacy Policy', 'springapex'); ?></a></li>
        <li><a href="<?php echo esc_url(springapex_url('/terms/')); ?>"><?php esc_html_e('Terms of Use', 'springapex'); ?></a></li>
      </ul>
    </section>
  </div>
</div>
