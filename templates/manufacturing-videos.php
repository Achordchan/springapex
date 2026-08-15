<?php
if (!defined('ABSPATH')) {
    exit;
}

$video_page = springapex_get('manufacturing_videos', []);
$featured = is_array($video_page['featured'] ?? null) ? $video_page['featured'] : [];
$categories = is_array($video_page['categories'] ?? null) ? $video_page['categories'] : [];
$youtube_id = trim((string) ($featured['youtube_id'] ?? ''));
$video_src = $youtube_id !== ''
    ? 'https://www.youtube-nocookie.com/embed/' . rawurlencode($youtube_id) . '?autoplay=1&rel=0'
    : '';
?>

<div class="sa-manufacturing-videos">
  <section class="sa-video-library-hero" aria-labelledby="manufacturing-videos-title">
    <figure class="sa-video-library-hero__media" aria-hidden="true">
      <?php echo springapex_image((string) ($video_page['hero_image'] ?? ''), '', [
          'class' => 'sa-video-library-hero__image',
          'loading' => 'eager',
          'fetchpriority' => 'high',
          'width' => 1800,
          'height' => 584,
          'sizes' => '100vw',
      ]); ?>
    </figure>
    <div class="container container-wide">
      <div class="sa-video-library-hero__copy" data-reveal="up">
        <h1 id="manufacturing-videos-title"><?php echo esc_html((string) ($video_page['title'] ?? 'See how precision is built.')); ?></h1>
        <p><?php echo esc_html((string) ($video_page['intro'] ?? '')); ?></p>
      </div>
    </div>
  </section>

  <?php get_template_part('parts/capabilities-subnav'); ?>

  <section class="sa-video-library" aria-label="ApexSpring manufacturing video library">
    <div class="container container-wide">
      <article class="sa-video-feature" data-reveal="up">
        <?php echo springapex_image((string) ($featured['image'] ?? ''), (string) ($featured['title'] ?? ''), [
            'class' => 'sa-video-feature__image',
            'loading' => 'eager',
            'fetchpriority' => 'high',
            'width' => 1600,
            'height' => 900,
            'sizes' => '(max-width: 760px) 100vw, 86vw',
        ]); ?>
        <div class="sa-video-feature__shade" aria-hidden="true"></div>
        <div class="sa-video-feature__content">
          <p><?php echo esc_html((string) ($featured['category'] ?? 'Manufacturing Process')); ?></p>
          <h2><?php echo esc_html((string) ($featured['title'] ?? 'From Wire to Verified Performance')); ?></h2>
        </div>
        <?php if ($video_src !== '') : ?>
          <button class="sa-video-play" type="button" data-hero-video-open aria-label="<?php echo esc_attr(sprintf(__('Play %s', 'springapex'), (string) ($featured['title'] ?? 'manufacturing video'))); ?>">
            <?php echo springapex_icon('youtube', 'icon'); ?>
          </button>
        <?php endif; ?>
        <?php if (trim((string) ($featured['duration'] ?? '')) !== '') : ?>
          <span class="sa-video-feature__duration"><?php echo esc_html((string) $featured['duration']); ?></span>
        <?php endif; ?>
      </article>

      <div class="sa-video-categories" data-reveal-group>
        <?php foreach ($categories as $category) : ?>
          <?php $category_id = sanitize_title((string) ($category['title'] ?? '')); ?>
          <article class="sa-video-category"<?php echo $category_id !== '' ? ' id="' . esc_attr($category_id) . '"' : ''; ?>>
            <figure class="sa-video-category__media">
              <?php echo springapex_image((string) ($category['image'] ?? ''), (string) ($category['title'] ?? ''), [
                  'class' => 'sa-video-category__image',
                  'width' => 720,
                  'height' => 480,
                  'sizes' => '(max-width: 640px) 100vw, (max-width: 1020px) 50vw, 22vw',
              ]); ?>
              <?php if (trim((string) ($category['duration'] ?? '')) !== '') : ?>
                <span class="sa-video-category__duration"><?php echo esc_html((string) $category['duration']); ?></span>
              <?php endif; ?>
            </figure>
            <h3><?php echo esc_html((string) ($category['title'] ?? '')); ?></h3>
            <p><?php echo esc_html((string) ($category['text'] ?? '')); ?></p>
          </article>
        <?php endforeach; ?>
      </div>

    </div>
  </section>
</div>

<?php if ($video_src !== '') : ?>
  <dialog
    class="sa-video-dialog"
    id="manufacturing-video-dialog"
    data-hero-video-dialog
    data-video-src="<?php echo esc_url($video_src); ?>"
    aria-labelledby="manufacturing-video-dialog-title"
  >
    <div class="sa-video-dialog__shell">
      <div class="sa-video-dialog__header">
        <h2 id="manufacturing-video-dialog-title"><?php echo esc_html((string) ($featured['title'] ?? 'ApexSpring Manufacturing')); ?></h2>
        <button class="sa-video-dialog__close" type="button" data-hero-video-close aria-label="<?php esc_attr_e('Close video', 'springapex'); ?>">
          <?php echo springapex_icon('close', 'icon'); ?>
        </button>
      </div>
      <div class="sa-video-dialog__media">
        <iframe
          data-hero-video-frame
          title="<?php echo esc_attr((string) ($featured['title'] ?? 'ApexSpring manufacturing video')); ?>"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen
        ></iframe>
      </div>
    </div>
  </dialog>
<?php endif; ?>
