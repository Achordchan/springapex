<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$news_item = null;
if (!defined('SPRINGAPEX_PREVIEW') && function_exists('is_singular') && is_singular('spring_news')) {
    $post_id = (int) get_queried_object_id();
    $slug = (string) get_post_field('post_name', $post_id);
    $news_item = springapex_news($slug);
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW')) {
    $slug = (string) get_query_var('news_slug', 'new-cnc-coiling-line');
    $news_item = springapex_news($slug);
}
if (!$news_item) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('News item not found', 'springapex') . '</h1></div></section>';
    return;
}

$news_date = (string) ($news_item['date'] ?? '');
$blocks = is_array($news_item['content'] ?? null) ? $news_item['content'] : [];

/* 推荐产品：从新闻数据里的产品 slug 解析 */
$products = [];
foreach ((array) ($news_item['products'] ?? []) as $product_slug) {
    $product = springapex_product((string) $product_slug);
    if ($product) {
        $products[] = $product;
    }
}

$related = springapex_related_news($slug, 3);
?>
<section class="sa-news-single-hero">
  <div class="container container-wide">
    <nav class="sa-news-single__breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'springapex'); ?>">
      <a href="<?php echo esc_url(springapex_url('/')); ?>"><?php esc_html_e('Home', 'springapex'); ?></a>
      <span class="sep" aria-hidden="true">›</span>
      <a href="<?php echo esc_url(springapex_url('/news/')); ?>"><?php esc_html_e('News', 'springapex'); ?></a>
      <span class="sep" aria-hidden="true">›</span>
      <span class="current" aria-current="page"><?php echo esc_html((string) ($news_item['category'] ?? 'News')); ?></span>
    </nav>

    <div class="sa-news-card__meta">
      <span class="sa-news-card__category"><?php echo esc_html((string) ($news_item['category'] ?? '')); ?></span>
      <?php if ($news_date !== '') : ?>
        <time datetime="<?php echo esc_attr($news_date); ?>"><?php echo esc_html(date_i18n('F j, Y', strtotime($news_date))); ?></time>
      <?php endif; ?>
    </div>
    <h1><?php echo esc_html((string) ($news_item['title'] ?? '')); ?></h1>
    <p class="lede"><?php echo esc_html((string) ($news_item['summary'] ?? '')); ?></p>
  </div>
</section>

<div class="container container-wide sa-news-single-layout">
  <div class="sa-news-single-main">
    <?php if (!empty($news_item['image'])) : ?>
      <figure class="sa-news-single-media">
        <?php echo springapex_image($news_item['image'], (string) ($news_item['title'] ?? ''), [
            'width' => 1280,
            'height' => 720,
            'loading' => 'eager',
            'fetchpriority' => 'high',
            'sizes' => '(max-width: 860px) 100vw, 860px',
        ]); ?>
      </figure>
    <?php endif; ?>

    <article class="sa-news-single-body">
      <div class="sa-news-single-body__inner">
        <?php if ($blocks) : ?>
          <?php foreach ($blocks as $block) : ?>
            <?php
            $block_type = (string) ($block['type'] ?? 'p');
            if ($block_type === 'h2') :
            ?>
              <h2><?php echo esc_html((string) ($block['text'] ?? '')); ?></h2>
            <?php elseif ($block_type === 'list') : ?>
              <ul>
                <?php foreach ((array) ($block['items'] ?? []) as $point) : ?>
                  <li><?php echo esc_html((string) $point); ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else : ?>
              <p><?php echo esc_html((string) ($block['text'] ?? '')); ?></p>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php elseif (!defined('SPRINGAPEX_PREVIEW') && function_exists('the_content')) : ?>
          <?php the_content(); ?>
        <?php endif; ?>
      </div>
    </article>

    <div class="sa-news-single__footer">
      <a class="btn btn-outline" href="<?php echo esc_url(springapex_url('/news/')); ?>">
        <?php echo springapex_icon('arrow-right', 'icon icon-sm sa-icon-flip'); ?>
        <?php esc_html_e('Back to all news', 'springapex'); ?>
      </a>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
        <?php esc_html_e('Ask Engineering', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
  </div>

  <aside class="sa-news-single-aside">
    <?php if ($products) : ?>
      <section class="sa-news-aside-card" aria-label="<?php esc_attr_e('Recommended products', 'springapex'); ?>">
        <h2 class="sa-news-aside-card__title"><?php esc_html_e('Recommended products', 'springapex'); ?></h2>
        <ul class="sa-news-aside-list">
          <?php foreach ($products as $product) : ?>
            <li class="sa-news-aside-item">
              <a class="sa-news-aside-item__media" href="<?php echo esc_url(springapex_product_url($product)); ?>">
                <?php echo springapex_image($product['image'] ?? '', (string) ($product['title'] ?? ''), [
                    'width' => 160,
                    'height' => 160,
                    'sizes' => '72px',
                ]); ?>
              </a>
              <div class="sa-news-aside-item__body">
                <a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a>
                <a class="sa-news-aside-item__link" href="<?php echo esc_url(springapex_product_url($product)); ?>">
                  <?php esc_html_e('View product', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
                </a>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <section class="sa-news-aside-card sa-news-aside-card--contact" aria-label="<?php esc_attr_e('Contact', 'springapex'); ?>">
      <div class="sa-news-aside-card__head">
        <span class="sa-news-aside-card__icon"><?php echo springapex_icon('headset', 'icon'); ?></span>
        <h2 class="sa-news-aside-card__title"><?php esc_html_e('Talk to engineering', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('Send your drawing and operating conditions; our engineers reply within 24 hours.', 'springapex'); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
        <?php esc_html_e('Contact us', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </section>
  </aside>
</div>

<?php if ($related) : ?>
<section class="section sa-news-related">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('KEEP READING', 'springapex'); ?></p>
      <h2><?php esc_html_e('Related news from SpringApex.', 'springapex'); ?></h2>
    </div>
    <div class="sa-news-grid" data-reveal-group>
      <?php foreach ($related as $item) :
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
            <a class="sa-news-card__link" href="<?php echo esc_url(springapex_news_url($item)); ?>">
              <?php esc_html_e('Read more', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>
