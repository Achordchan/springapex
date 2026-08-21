<?php
if (!defined('ABSPATH')) {
    exit;
}

$all_items = springapex_news_list();
if (!$all_items) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('No news yet', 'springapex') . '</h1></div></section>';
    return;
}

$news_types = [
    'all' => [
        'label' => __('All News', 'springapex'),
        'kicker' => __('LATEST FROM APEXSPRING', 'springapex'),
        'title' => __('News and engineering insights.', 'springapex'),
    ],
    'industry-news' => [
        'label' => __('Industry News', 'springapex'),
        'kicker' => __('INDUSTRY NEWS', 'springapex'),
        'title' => __('Engineering and manufacturing developments.', 'springapex'),
    ],
    'exhibitions' => [
        'label' => __('Exhibitions', 'springapex'),
        'kicker' => __('EXHIBITIONS', 'springapex'),
        'title' => __('Meet ApexSpring at industry events.', 'springapex'),
    ],
    'company-news' => [
        'label' => __('Company News', 'springapex'),
        'kicker' => __('COMPANY NEWS', 'springapex'),
        'title' => __('Updates from ApexSpring.', 'springapex'),
    ],
];
$requested_news_type = is_scalar($_GET['news_type'] ?? null)
    ? sanitize_key((string) wp_unslash($_GET['news_type']))
    : 'all';
$active_news_type = array_key_exists($requested_news_type, $news_types) ? $requested_news_type : 'all';
$category_fallbacks = [
    'industry news' => 'industry-news',
    'exhibition' => 'exhibitions',
    'exhibitions' => 'exhibitions',
    'company news' => 'company-news',
];
$resolve_news_type = static function (array $item) use ($category_fallbacks): string {
    $news_type = sanitize_key((string) ($item['news_type'] ?? ''));
    if ($news_type !== '') {
        return $news_type;
    }
    return $category_fallbacks[strtolower(trim((string) ($item['category'] ?? '')))] ?? 'company-news';
};
$items = $active_news_type === 'all'
    ? array_values($all_items)
    : array_values(array_filter(
        $all_items,
        static fn(array $item): bool => $resolve_news_type($item) === $active_news_type
    ));
$featured = $items ? array_shift($items) : null;
$featured_date = is_array($featured) ? (string) ($featured['date'] ?? '') : '';
$featured_date_label = is_array($featured) ? (string) ($featured['date_label'] ?? '') : '';
$active_news_heading = $news_types[$active_news_type];
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'news',
    'title' => springapex_get('news.hero.title', 'News'),
    'subtitle' => springapex_get('news.hero.subtitle', ''),
    'image' => springapex_get('news.hero.image', 'generated/springapex-news-hero-v3.webp'),
    'mobile_image' => springapex_get('news.hero.mobile_image', 'news-hero-mobile-v1.png'),
    'image_width' => 1890,
    'image_height' => 830,
]);
?>

<nav class="sa-news-subnav" aria-label="<?php esc_attr_e('News categories', 'springapex'); ?>">
  <div class="container container-wide sa-news-subnav__inner">
    <?php foreach ($news_types as $news_type => $config) : ?>
      <?php
      $href = $news_type === 'all'
          ? springapex_url('/news/')
          : springapex_url('/news/?news_type=' . rawurlencode($news_type));
      $is_active = $news_type === $active_news_type;
      ?>
      <a class="sa-news-subnav__link<?php echo $is_active ? ' is-active' : ''; ?>" href="<?php echo esc_url($href); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
        <?php echo esc_html((string) $config['label']); ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<section class="section sa-news-index" id="news-index">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php echo esc_html((string) $active_news_heading['kicker']); ?></p>
      <h2><?php echo esc_html((string) $active_news_heading['title']); ?></h2>
    </div>

    <?php if ($featured) : ?>
    <article class="sa-news-featured" data-reveal>
      <a class="sa-news-featured__media" href="<?php echo esc_url(springapex_news_url($featured)); ?>" tabindex="-1" aria-hidden="true">
        <?php echo springapex_image($featured['image'] ?? '', (string) ($featured['title'] ?? ''), [
            'width' => 1280,
            'height' => 720,
            'loading' => 'eager',
            'fetchpriority' => 'high',
            'sizes' => '(max-width: 860px) 100vw, 62vw',
        ]); ?>
      </a>
      <div class="sa-news-featured__body">
        <div class="sa-news-card__meta">
          <span class="sa-news-card__category"><?php echo esc_html((string) ($featured['category'] ?? '')); ?></span>
          <?php if ($featured_date !== '') : ?>
            <time datetime="<?php echo esc_attr($featured_date); ?>"><?php echo esc_html($featured_date_label !== '' ? $featured_date_label : date_i18n('M j, Y', strtotime($featured_date))); ?></time>
          <?php endif; ?>
        </div>
        <h3><a href="<?php echo esc_url(springapex_news_url($featured)); ?>"><?php echo esc_html((string) ($featured['title'] ?? '')); ?></a></h3>
        <p class="sa-news-featured__summary"><?php echo esc_html((string) ($featured['summary'] ?? '')); ?></p>
        <a class="text-link" href="<?php echo esc_url(springapex_news_url($featured)); ?>">
          <?php esc_html_e('Read the full story', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </div>
    </article>

    <?php if ($items) : ?>
      <div class="sa-news-grid" data-reveal-group>
        <?php foreach ($items as $item) :
            $item_date = (string) ($item['date'] ?? '');
            $item_date_label = (string) ($item['date_label'] ?? '');
        ?>
          <article class="sa-news-card">
            <a class="sa-news-card__media" href="<?php echo esc_url(springapex_news_url($item)); ?>">
              <?php echo springapex_image($item['image'] ?? '', (string) ($item['title'] ?? ''), [
                  'width' => 800,
                  'height' => 500,
                  'sizes' => '(max-width: 860px) 100vw, (max-width: 1180px) 50vw, 33vw',
              ]); ?>
            </a>
            <div class="sa-news-card__body">
              <div class="sa-news-card__meta">
                <span class="sa-news-card__category"><?php echo esc_html((string) ($item['category'] ?? '')); ?></span>
                <?php if ($item_date !== '') : ?>
                  <time datetime="<?php echo esc_attr($item_date); ?>"><?php echo esc_html($item_date_label !== '' ? $item_date_label : date_i18n('M j, Y', strtotime($item_date))); ?></time>
                <?php endif; ?>
              </div>
              <h3><a href="<?php echo esc_url(springapex_news_url($item)); ?>"><?php echo esc_html((string) ($item['title'] ?? '')); ?></a></h3>
              <p><?php echo esc_html((string) ($item['summary'] ?? '')); ?></p>
              <a class="sa-news-card__link" href="<?php echo esc_url(springapex_news_url($item)); ?>">
                <?php esc_html_e('Read more', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <?php else : ?>
      <div class="sa-news-empty">
        <h3><?php esc_html_e('No articles in this category yet.', 'springapex'); ?></h3>
        <p><?php esc_html_e('New updates will appear here when they are published.', 'springapex'); ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="section sa-news-cta">
  <div class="container container-wide sa-news-cta__inner">
    <div>
      <p class="section-kicker"><?php esc_html_e('FOLLOW APEXSPRING', 'springapex'); ?></p>
      <h2><?php esc_html_e('Keep up with ApexSpring.', 'springapex'); ?></h2>
      <p><?php esc_html_e('Follow exhibition news, manufacturing updates and company developments through our official channels.', 'springapex'); ?></p>
    </div>
    <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/about/#official-channels')); ?>">
      <?php esc_html_e('View Official Channels', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
