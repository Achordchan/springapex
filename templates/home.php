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
    <div class="hero-copy">
      <h1 class="display display-home">
        <?php foreach ($hero_lines as $index => $line) : ?>
          <span class="<?php echo $index === 0 ? 'headline-strong' : ''; ?>"><?php echo esc_html($line); ?></span><?php echo $index < count($hero_lines) - 1 ? '<br>' : ''; ?>
        <?php endforeach; ?>
      </h1>
      <p class="lede lede-home"><?php echo esc_html($hero['subtitle'] ?? ''); ?></p>
      <div class="hero-actions">
        <a class="btn btn-primary" href="<?php echo esc_url(springapex_url($hero['primary_cta']['href'] ?? '/contact/')); ?>">
          <?php echo esc_html($hero['primary_cta']['label'] ?? 'Upload Your Drawing'); ?>
          <?php echo springapex_icon($hero['primary_cta']['icon'] ?? 'upload', 'icon icon-sm'); ?>
        </a>
        <a class="btn btn-text" href="<?php echo esc_url(springapex_url($hero['secondary_cta']['href'] ?? '/solutions/')); ?>">
          <?php echo esc_html($hero['secondary_cta']['label'] ?? 'Explore Solutions'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </div>
    </div>
    <div class="hero-media hero-media-home">
      <?php echo springapex_image($hero['image'] ?? '', 'Polished precision compression spring', [
          'class' => 'hero-product-image',
          'width' => 1600,
          'height' => 900,
          'loading' => 'eager',
          'fetchpriority' => 'high',
          'sizes' => '(max-width: 760px) 100vw, 62vw',
      ]); ?>
    </div>
  </div>
</section>

<?php $certifications = springapex_get('company.quality.standards', []); ?>
<section class="certification-strip" aria-labelledby="certification-strip-title">
  <div class="container container-wide">
    <p class="section-kicker" id="certification-strip-title"><?php esc_html_e('Quality Certifications', 'springapex'); ?></p>
    <div class="certification-row" data-reveal-group>
      <?php foreach ($certifications as $certification) : ?>
        <?php
        $certification_name = (string) ($certification['name'] ?? '');
        $certification_url = trim((string) ($certification['url'] ?? ''));
        $certification_tag = $certification_url !== '' ? 'a' : 'span';
        ?>
        <<?php echo $certification_tag; ?> class="certification-card"<?php echo $certification_url !== '' ? ' href="' . esc_url($certification_url) . '" target="_blank" rel="noopener noreferrer"' : ''; ?> aria-label="<?php echo esc_attr($certification_name); ?>">
          <span class="certification-card__icon" aria-hidden="true"><?php echo springapex_icon('check-shield', 'icon icon-sm'); ?></span>
          <span class="certification-card__name"><?php echo esc_html($certification_name); ?></span>
        </<?php echo $certification_tag; ?>>
      <?php endforeach; ?>
    </div>
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

<section class="section pillars-section">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('WHY SPRINGAPEX', 'springapex'); ?></p>
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

<?php get_template_part('parts/manufacturing-proof'); ?>

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

<section class="section process-section">
  <div class="container container-wide process-layout" data-reveal="up">
    <p class="section-kicker"><?php esc_html_e('HOW WE WORK', 'springapex'); ?></p>
    <h2 class="process-title"><?php echo nl2br(esc_html("From Wire to\nPerformance")); ?></h2>
    <p class="sa-section-bridge"><?php esc_html_e('Every order follows the same disciplined sequence — so quality is built in, not inspected in.', 'springapex'); ?></p>
    <div class="process-content">
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
  </div>
</section>

<?php get_template_part('parts/quality-credentials', null, ['variant' => 'compact']); ?>

<section class="section sa-resources-preview">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('ENGINEERING INSIGHTS', 'springapex'); ?></p>
        <h2><?php esc_html_e('Useful guidance before you send a drawing.', 'springapex'); ?></h2>
        <p class="sa-section-bridge"><?php esc_html_e('Spring design details matter. These short guides help you spec with confidence.', 'springapex'); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
        <?php esc_html_e('View all resources', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <?php get_template_part('parts/resources-grid', null, ['limit' => 3]); ?>
  </div>
</section>
