<?php
if (!defined('ABSPATH')) {
    exit;
}

// 当前页、当前分类的新闻（每页 1 篇大图 + 卡片，见 inc/news-archive.php）。
$news_view = springapex_news_archive_view();
if ($news_view['total'] === 0 && $news_view['type'] === 'all') {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('No news yet', 'springapex') . '</h1></div></section>';
    return;
}

$news_types = springapex_news_types();
$active_news_type = $news_view['type'];
$items = $news_view['items'];
$news_page = $news_view['page'];
$news_total_pages = $news_view['total_pages'];
$news_archive_url = function_exists('get_post_type_archive_link')
    ? (string) get_post_type_archive_link('spring_news')
    : springapex_url('/news/');
if ($news_archive_url === '') {
    $news_archive_url = springapex_url('/news/');
}
$featured = $items ? array_shift($items) : null;
$featured_date = is_array($featured) ? (string) ($featured['date'] ?? '') : '';
$featured_date_label = is_array($featured) ? (string) ($featured['date_label'] ?? '') : '';
$active_news_heading = $news_types[$active_news_type];
$news_follow = springapex_get('news.follow', []);
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
          <?php if (isset($featured['views'])) : ?>
            <?php echo springapex_news_views_html((int) $featured['views']); ?>
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
                <?php if (isset($item['views'])) : ?>
                  <?php echo springapex_news_views_html((int) $item['views']); ?>
                <?php endif; ?>
              </div>
              <h3><a href="<?php echo esc_url(springapex_news_url($item)); ?>"><?php echo esc_html((string) ($item['title'] ?? '')); ?></a></h3>
              <p><?php echo esc_html((string) ($item['summary'] ?? '')); ?></p>
              <a class="sa-news-card__link" href="<?php echo esc_url(springapex_news_url($item)); ?>">
                <?php esc_html_e('Read more', 'springapex'); ?><span class="sr-only">: <?php echo esc_html((string) ($item['title'] ?? '')); ?></span> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
              </a>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php $news_page_numbers = springapex_news_page_numbers($news_page, $news_total_pages); ?>
    <?php if ($news_page_numbers) : ?>
      <nav class="sa-news-pagination" aria-label="<?php esc_attr_e('News pages', 'springapex'); ?>">
        <?php if ($news_page > 1) : ?>
          <a class="sa-news-pagination__step" href="<?php echo esc_url(springapex_news_page_url($news_archive_url, $news_page - 1, $active_news_type) . '#news-index'); ?>" rel="prev">
            <?php echo springapex_icon('arrow-right', 'icon icon-sm sa-news-pagination__back'); ?> <?php esc_html_e('Previous', 'springapex'); ?>
          </a>
        <?php endif; ?>
        <ol class="sa-news-pagination__pages">
          <?php foreach ($news_page_numbers as $news_page_number) : ?>
            <?php if ($news_page_number === null) : ?>
              <li class="sa-news-pagination__gap" aria-hidden="true">&hellip;</li>
            <?php elseif ($news_page_number === $news_page) : ?>
              <li><span class="sa-news-pagination__page is-current" aria-current="page"><span class="sr-only"><?php esc_html_e('Page', 'springapex'); ?> </span><?php echo (int) $news_page_number; ?></span></li>
            <?php else : ?>
              <li><a class="sa-news-pagination__page" href="<?php echo esc_url(springapex_news_page_url($news_archive_url, $news_page_number, $active_news_type) . '#news-index'); ?>"><span class="sr-only"><?php esc_html_e('Page', 'springapex'); ?> </span><?php echo (int) $news_page_number; ?></a></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ol>
        <?php if ($news_page < $news_total_pages) : ?>
          <a class="sa-news-pagination__step" href="<?php echo esc_url(springapex_news_page_url($news_archive_url, $news_page + 1, $active_news_type) . '#news-index'); ?>" rel="next">
            <?php esc_html_e('Next', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        <?php endif; ?>
      </nav>
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
      <p class="section-kicker"><?php echo esc_html((string) ($news_follow['eyebrow'] ?? '')); ?></p>
      <h2><?php echo esc_html((string) ($news_follow['title'] ?? '')); ?></h2>
      <p><?php echo esc_html((string) ($news_follow['text'] ?? '')); ?></p>
    </div>
    <a class="btn btn-primary" href="<?php echo esc_url(springapex_url((string) ($news_follow['action_href'] ?? ''))); ?>">
      <?php echo esc_html((string) ($news_follow['action_label'] ?? '')); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
