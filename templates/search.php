<?php
if (!defined('ABSPATH')) {
    exit;
}

$query = springapex_search_query();
$lower = static function (string $value): string {
    return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
};
$needle = $lower(trim($query));
$items = [];

foreach (springapex_products() as $product) {
    $items[] = [
        'type' => 'Product family',
        'title' => (string) ($product['title'] ?? ''),
        'summary' => (string) ($product['desc'] ?? ''),
        'href' => springapex_product_url($product),
    ];
}

foreach (springapex_solutions() as $solution) {
    $items[] = [
        'type' => 'Industry solution',
        'title' => (string) ($solution['title'] ?? ''),
        'summary' => (string) ($solution['tagline'] ?? ''),
        'href' => springapex_solution_url($solution),
    ];
}

foreach (springapex_get('resources.items', []) as $resource) {
    $items[] = [
        'type' => (string) ($resource['type'] ?? 'Resource'),
        'title' => (string) ($resource['title'] ?? ''),
        'summary' => (string) ($resource['summary'] ?? ''),
        'href' => springapex_url('/resources/#resource-index'),
    ];
}

$items[] = [
    'type' => 'Company',
    'title' => 'About SpringApex',
    'summary' => 'Company story, manufacturing facilities, quality systems and global program support.',
    'href' => springapex_url('/about/'),
];
$items[] = [
    'type' => 'Capability',
    'title' => 'Manufacturing & Quality',
    'summary' => 'Engineering, forming, inspection and controlled production support.',
    'href' => springapex_url('/capabilities/'),
];

$matches = [];
if ($needle !== '') {
    foreach ($items as $item) {
        $haystack = $lower(trim(implode(' ', [$item['type'], $item['title'], $item['summary']])));
        if (str_contains($haystack, $needle)) {
            $matches[] = $item;
        }
    }
}
?>

<section class="inner-hero inner-hero--search">
  <div class="container container-wide inner-hero-inner">
    <div class="inner-hero-copy">
      <p class="section-kicker">SEARCH</p>
      <h1 class="display">Find the right starting point.</h1>
      <p class="lede">Search product families, industry solutions and engineering resources before you contact our team.</p>
    </div>
  </div>
</section>

<section class="section sa-search-results">
  <div class="container container-wide">
    <form class="sa-search-results__form" action="<?php echo esc_url(springapex_url('/search/')); ?>" method="get" role="search">
      <input type="hidden" name="sa_page" value="search">
      <label class="sr-only" for="search-page-input">Search products, industries and resources</label>
      <input id="search-page-input" type="search" name="s" value="<?php echo esc_attr($query); ?>" placeholder="Search products, industries and resources" autocomplete="off">
      <button class="btn btn-primary" type="submit">Search <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></button>
    </form>

    <?php if ($query === '') : ?>
      <div class="sa-search-empty">
        <p class="section-kicker">START HERE</p>
        <h2>Search by spring type, industry or engineering topic.</h2>
        <p>Try “compression”, “automotive”, “material” or “quality”.</p>
      </div>
    <?php elseif (!$matches) : ?>
      <div class="sa-search-empty">
        <p class="section-kicker">NO MATCHES</p>
        <h2>We could not find “<?php echo esc_html($query); ?>”.</h2>
        <p>Describe your application or send a drawing and our engineers can help identify the right path.</p>
        <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">Talk to an Engineer <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      </div>
    <?php else : ?>
      <div class="section-head sa-section-intro">
        <p class="section-kicker">SEARCH RESULTS</p>
        <h2><?php echo esc_html((string) count($matches)); ?> results for “<?php echo esc_html($query); ?>”.</h2>
      </div>
      <div class="sa-search-results__grid" data-reveal-group>
        <?php foreach ($matches as $item) : ?>
          <a class="sa-search-result" href="<?php echo esc_url((string) $item['href']); ?>">
            <span class="sa-search-result__type"><?php echo esc_html((string) $item['type']); ?></span>
            <h3><?php echo esc_html((string) $item['title']); ?></h3>
            <p><?php echo esc_html((string) $item['summary']); ?></p>
            <span class="text-link">Open result <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
