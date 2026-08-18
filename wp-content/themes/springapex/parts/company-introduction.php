<?php
if (!defined('ABSPATH')) {
    exit;
}

$intro_args = is_array($args ?? null) ? $args : [];
$variant = sanitize_key((string) ($intro_args['variant'] ?? 'home'));
$profile = springapex_get('company.profile', []);
if (!$profile) {
    return;
}

$is_about = $variant === 'about';
$company_facts = $is_about ? array_slice((array) springapex_get('company.facts', []), 0, 4) : [];
?>
<section class="section sa-company-intro sa-company-intro--<?php echo esc_attr($is_about ? 'about' : 'home'); ?>" aria-labelledby="company-intro-<?php echo esc_attr($variant); ?>-title">
  <div class="container container-wide sa-company-intro__layout">
    <?php if (!$is_about) : ?>
      <figure class="sa-company-intro__media" data-reveal="up">
        <?php echo springapex_image((string) ($profile['image'] ?? ''), (string) ($profile['image_alt'] ?? ''), [
            'width' => 1920,
            'height' => 700,
            'sizes' => '(max-width: 760px) 100vw, 52vw',
        ]); ?>
      </figure>
    <?php endif; ?>

    <div class="sa-company-intro__content" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($profile['eyebrow'] ?? 'ABOUT APEXSPRING')); ?></p>
      <h2 id="company-intro-<?php echo esc_attr($variant); ?>-title"><?php echo esc_html((string) ($is_about ? ($profile['title'] ?? '') : ($profile['home_title'] ?? ''))); ?></h2>

      <?php if ($is_about) : ?>
        <div class="sa-company-intro__prose">
          <?php foreach ((array) ($profile['paragraphs'] ?? []) as $paragraph) : ?>
            <p><?php echo esc_html((string) $paragraph); ?></p>
          <?php endforeach; ?>
        </div>

        <?php if ($company_facts) : ?>
          <dl class="sa-about-facts" data-reveal-group>
            <?php foreach ($company_facts as $stat) : ?>
              <?php
              $stat_value = (string) ($stat['value'] ?? '0');
              $stat_target = (int) (preg_replace('/[^0-9]/', '', $stat_value) ?: '0');
              ?>
              <div>
                <dt><?php echo esc_html((string) ($stat['label'] ?? '')); ?></dt>
                <dd>
                  <span class="stat-value-visual" aria-hidden="true" data-count-target="<?php echo esc_attr((string) $stat_target); ?>" data-count-display="<?php echo esc_attr($stat_value); ?>"><?php echo esc_html($stat_value); ?></span>
                  <span class="sr-only"><?php echo esc_html($stat_value); ?></span>
                </dd>
              </div>
            <?php endforeach; ?>
          </dl>
        <?php endif; ?>
      <?php else : ?>
        <p class="sa-company-intro__lede"><?php echo esc_html((string) ($profile['home_text'] ?? '')); ?></p>
        <p class="sa-company-intro__support"><?php echo esc_html((string) ($profile['home_support'] ?? '')); ?></p>
      <?php endif; ?>

      <?php if (!$is_about) : ?>
        <dl class="sa-company-intro__facts">
          <?php foreach ((array) ($profile['highlights'] ?? []) as $highlight) : ?>
            <div>
              <dt><?php echo esc_html((string) ($highlight['label'] ?? '')); ?></dt>
              <dd><?php echo esc_html((string) ($highlight['value'] ?? '')); ?></dd>
            </div>
          <?php endforeach; ?>
        </dl>
        <a class="text-link sa-company-intro__link" href="<?php echo esc_url(springapex_url('/about/')); ?>">
          <?php esc_html_e('Learn about ApexSpring', 'springapex'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      <?php endif; ?>
    </div>

  </div>
</section>
