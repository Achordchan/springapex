<?php
if (!defined('ABSPATH')) {
    exit;
}
$home = springapex_get('home', []);
$hero = $home['hero'] ?? [];
$hero_lines = preg_split('/\R/', (string) ($hero['title'] ?? '')) ?: [];
?>
<section class="hero hero-home">
  <div class="container container-wide hero-grid hero-grid-home">
    <div class="hero-media hero-media-home">
      <?php echo springapex_image($hero['image'] ?? '', 'Polished precision compression spring', [
          'class' => 'hero-product-image',
          'width' => 1600,
          'height' => 900,
          'loading' => 'eager',
          'fetchpriority' => 'high',
          'sizes' => '(max-width: 760px) 100vw, 62vw',
          'mobile_image' => 'hero-spring-mobile-v1.png',
          'mobile_sizes' => '100vw',
          'mobile_breakpoint' => '860px',
      ]); ?>
    </div>
    <div class="hero-copy">
      <h1 class="display display-home">
        <?php foreach ($hero_lines as $index => $line) : ?>
          <span class="<?php echo $index === 0 ? 'headline-strong' : ''; ?>"><?php echo esc_html($line); ?></span><?php echo $index < count($hero_lines) - 1 ? '<br>' : ''; ?>
        <?php endforeach; ?>
      </h1>
      <p class="lede lede-home"><?php echo esc_html($hero['subtitle'] ?? ''); ?></p>
      <div class="hero-actions">
        <button class="btn btn-primary hero-video-trigger" type="button" data-hero-video-open aria-haspopup="dialog" aria-controls="hero-video-dialog">
          <span class="hero-video-trigger__icon" aria-hidden="true"></span>
          <?php echo esc_html($hero['video_cta']['label'] ?? 'Play a Video'); ?>
        </button>
        <a class="btn btn-text" href="<?php echo esc_url(springapex_url($hero['quote_cta']['href'] ?? '/contact/?intent=quote')); ?>">
          <?php echo esc_html($hero['quote_cta']['label'] ?? 'Request a Quote'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </div>
    </div>
  </div>
</section>

<dialog
  class="sa-video-dialog"
  id="hero-video-dialog"
  data-hero-video-dialog
  data-video-src="<?php echo esc_url('https://www.youtube-nocookie.com/embed/' . ($hero['video_cta']['youtube_id'] ?? '') . '?autoplay=1&rel=0'); ?>"
  aria-labelledby="hero-video-title"
>
  <div class="sa-video-dialog__shell">
    <div class="sa-video-dialog__header">
      <h2 id="hero-video-title"><?php esc_html_e('ApexSpring Manufacturing', 'springapex'); ?></h2>
      <button class="sa-video-dialog__close" type="button" data-hero-video-close aria-label="Close video">
        <?php echo springapex_icon('close', 'icon'); ?>
      </button>
    </div>
    <div class="sa-video-dialog__media">
      <iframe
        data-hero-video-frame
        title="ApexSpring manufacturing video"
        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
        referrerpolicy="strict-origin-when-cross-origin"
        allowfullscreen
      ></iframe>
    </div>
  </div>
</dialog>

<?php $certificates = springapex_get('company.quality.certificates', []); ?>
<section class="certification-strip" aria-label="<?php esc_attr_e('Quality certificates', 'springapex'); ?>">
  <div class="container container-wide">
    <?php get_template_part('parts/certification-gallery', null, [
        'id' => 'home-certificate-gallery',
        'certificates' => $certificates,
        'variant' => 'strip',
        'viewer' => true,
    ]); ?>
  </div>
</section>

<section class="section sa-home-products">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('PRODUCT RANGE', 'springapex'); ?></p>
        <h2><?php esc_html_e('Six core spring families. Thousands of configurations.', 'springapex'); ?></h2>
        <p class="sa-section-bridge"><?php esc_html_e('Whether you need a standard compression spring or a complex wire form, start here.', 'springapex'); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/products/')); ?>">
        <?php esc_html_e('Explore all products', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="sa-product-grid" data-reveal-group>
      <?php foreach (array_slice(springapex_products(), 0, 6) as $product) : ?>
        <?php get_template_part('parts/product-card', null, ['product' => $product]); ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_template_part('parts/company-introduction', null, ['variant' => 'home']); ?>

<section class="section pillars-section">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('WHY APEXSPRING', 'springapex'); ?></p>
        <h2><?php esc_html_e('What You Get When You Work With Us', 'springapex'); ?></h2>
        <p class="sa-section-bridge"><?php esc_html_e('Choosing a spring supplier is choosing a manufacturing partner. Here is what that partnership delivers.', 'springapex'); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/capabilities/')); ?>">
        <?php esc_html_e('Our Capabilities', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="pillar-grid" data-reveal-group>
      <?php foreach (($home['pillars'] ?? []) as $pillar) : ?>
        <article class="pillar-card">
          <div class="icon-circle soft"><?php echo springapex_icon((string) $pillar['icon']); ?></div>
          <h3><?php echo esc_html((string) $pillar['title']); ?></h3>
          <p><?php echo esc_html((string) $pillar['text']); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section process-section">
  <div class="container container-wide" data-reveal="up">
    <div class="process-header">
      <p class="section-kicker"><?php esc_html_e('HOW WE WORK', 'springapex'); ?></p>
      <h2 class="process-title"><?php esc_html_e('From Wire to Performance', 'springapex'); ?></h2>
      <p class="process-intro"><?php esc_html_e('Every order follows the same disciplined sequence — so quality is built in, not inspected in.', 'springapex'); ?></p>
    </div>
    <div class="process-track" data-reveal-group>
      <?php foreach (($home['process'] ?? []) as $step) : ?>
        <div class="process-step">
          <div class="icon-circle soft"><?php echo springapex_icon((string) $step['icon']); ?></div>
          <span><?php echo esc_html((string) $step['label']); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="process-note"><?php esc_html_e('A proven process. Precision quality. Reliable delivery.', 'springapex'); ?></p>
  </div>
</section>

<section class="section applications-section">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('INDUSTRIES WE SERVE', 'springapex'); ?></p>
        <h2><?php esc_html_e('Springs built for the demands of your industry.', 'springapex'); ?></h2>
        <p class="sa-section-bridge"><?php esc_html_e('Each sector has unique load, environment and compliance requirements. We engineer around them.', 'springapex'); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/solutions/')); ?>">
        <?php esc_html_e('View All Applications', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="application-grid" data-reveal-group>
      <?php foreach (($home['applications'] ?? []) as $application) : ?>
        <a class="application-card" href="<?php echo esc_url(springapex_url('/solutions/#' . $application['slug'])); ?>">
          <?php echo springapex_image((string) $application['image'], (string) $application['title'], [
              'class' => 'application-image',
              'width' => 800,
              'height' => 600,
              'sizes' => '(max-width: 640px) 100vw, 25vw',
          ]); ?>
          <span class="application-shade" aria-hidden="true"></span>
          <span class="application-meta">
            <span><?php echo esc_html((string) $application['title']); ?></span>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_template_part('parts/home-faq'); ?>
