<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = springapex_news_list();
if (!$items) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('No news yet', 'springapex') . '</h1></div></section>';
    return;
}

$featured = array_shift($items);
$featured_date = (string) ($featured['date'] ?? '');
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'news',
    'title' => springapex_get('news.hero.title', 'News'),
    'subtitle' => springapex_get('news.hero.subtitle', ''),
    'image' => 'generated/springapex-news-hero-v3.webp',
    'mobile_image' => 'generated/springapex-news-hero-v3.webp',
    'image_width' => 1890,
    'image_height' => 830,
    'ctas' => [[
        'label' => 'Browse Latest News',
        'href' => '#news-index',
        'icon' => 'arrow-right',
    ]],
]);
?>

<section class="section sa-news-index" id="news-index">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('WHAT IS HAPPENING AT SPRINGAPEX', 'springapex'); ?></p>
      <h2><?php esc_html_e('Manufacturing updates, engineering guides and company news.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('Follow our latest developments in precision spring manufacturing and engineering support.', 'springapex'); ?></p>
    </div>

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
            <time datetime="<?php echo esc_attr($featured_date); ?>"><?php echo esc_html(date_i18n('M j, Y', strtotime($featured_date))); ?></time>
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
                  <time datetime="<?php echo esc_attr($item_date); ?>"><?php echo esc_html(date_i18n('M j, Y', strtotime($item_date))); ?></time>
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
  </div>
</section>

<section class="section sa-news-cta">
  <div class="container container-wide sa-news-cta__inner">
    <div>
      <p class="section-kicker"><?php esc_html_e('TALK TO ENGINEERING', 'springapex'); ?></p>
      <h2><?php esc_html_e('Have a spring project in mind?', 'springapex'); ?></h2>
      <p><?php esc_html_e('Send the drawing and operating conditions. Engineering will review the design and reply within one business day.', 'springapex'); ?></p>
    </div>
    <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
      <?php esc_html_e('Contact Engineering', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
    </a>
  </div>
</section>
