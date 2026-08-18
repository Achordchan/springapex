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

foreach (springapex_cases() as $case) {
    $items[] = [
        'type' => 'Case study',
        'title' => (string) ($case['title'] ?? ''),
        'summary' => (string) ($case['tagline'] ?? ''),
        'href' => springapex_case_url($case),
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
    'title' => 'About ApexSpring',
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

<section class="sa-search-hero">
  <div class="container container-wide sa-search-hero__inner">
    <div class="sa-search-hero__intro">
      <p class="sa-search-hero__kicker"><?php echo $query === '' ? 'SITE DIRECTORY' : 'SEARCH RESULTS'; ?></p>
      <?php if ($query === '') : ?>
        <h1 class="sa-search-hero__title">Explore ApexSpring.</h1>
        <p class="sa-search-hero__subtitle">Use the search button in the header or browse the main product, industry and custom manufacturing routes below.</p>
      <?php elseif ($matches) : ?>
        <h1 class="sa-search-hero__title">Results for “<?php echo esc_html($query); ?>”</h1>
        <p class="sa-search-hero__subtitle"><?php echo esc_html((string) count($matches)); ?> matching pages across products, industries, resources and company information.</p>
      <?php else : ?>
        <h1 class="sa-search-hero__title">No results for “<?php echo esc_html($query); ?>”</h1>
        <p class="sa-search-hero__subtitle">Try a broader term from the header search or send the application requirements directly to our team.</p>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="section sa-search-results">
  <div class="container container-wide">
    <?php if ($query === '') : ?>
      <div class="sa-search-start" data-reveal>
        <div class="sa-search-start__intro">
          <p class="sa-search-results__kicker">START HERE</p>
          <h2>Browse the main routes into ApexSpring.</h2>
          <p>Choose a product family, an industry application or the custom manufacturing path.</p>
        </div>
        <div class="sa-search-start__grid" data-reveal-group>
          <a href="<?php echo esc_url(springapex_url('/products/')); ?>">
            <span><?php echo springapex_icon('spring'); ?></span>
            <div><h3>Product Families</h3><p>Browse springs by type and load direction.</p></div>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
          <a href="<?php echo esc_url(springapex_url('/solutions/')); ?>">
            <span><?php echo springapex_icon('factory'); ?></span>
            <div><h3>Industry Solutions</h3><p>Start from your application and operating environment.</p></div>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
          <a href="<?php echo esc_url(springapex_url('/capabilities/')); ?>">
            <span><?php echo springapex_icon('pen'); ?></span>
            <div><h3>Custom Springs</h3><p>Prepare a drawing, load requirement or project brief.</p></div>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
      </div>
    <?php elseif (!$matches) : ?>
      <div class="sa-search-empty sa-search-empty--no-results" data-reveal>
        <div class="sa-search-empty__icon-wrap sa-search-empty__icon-wrap--alert" aria-hidden="true">
          <?php echo springapex_icon('target', 'icon'); ?>
        </div>
        <p class="sa-search-empty__kicker">NO MATCHES</p>
        <h2 class="sa-search-empty__title">We could not find "<?php echo esc_html($query); ?>".</h2>
        <p class="sa-search-empty__desc">Describe your application or send a drawing and our engineers can help identify the right path.</p>
        <a class="btn btn-primary sa-search-empty__cta" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">Talk to an Engineer <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      </div>
    <?php else : ?>
      <div class="sa-search-results__grid" data-reveal-group>
        <?php foreach ($matches as $item) :
          $type_slug = sanitize_key((string) $item['type']);
        ?>
          <a class="sa-search-card" href="<?php echo esc_url((string) $item['href']); ?>" data-type="<?php echo esc_attr($type_slug); ?>">
            <div class="sa-search-card__head">
              <span class="sa-search-card__badge" data-type="<?php echo esc_attr($type_slug); ?>"><?php echo esc_html((string) $item['type']); ?></span>
            </div>
            <h3 class="sa-search-card__title"><?php echo esc_html((string) $item['title']); ?></h3>
            <p class="sa-search-card__desc"><?php echo esc_html((string) $item['summary']); ?></p>
            <span class="sa-search-card__link">
              View details
              <span class="sa-search-card__arrow"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
