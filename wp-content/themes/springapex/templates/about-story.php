<?php
if (!defined('ABSPATH')) {
    exit;
}

$about = springapex_get('about', []);
$company = springapex_get('company', []);
$team = is_array($about['team'] ?? null) ? $about['team'] : [];
$timeline = array_values(array_filter((array) ($company['timeline'] ?? []), 'is_array'));
$brand = springapex_brand();
$company_video = is_array($about['company_video'] ?? null) ? $about['company_video'] : [];
$youtube_id = (string) ($company_video['youtube_id'] ?? '');
$youtube_embed_url = $youtube_id !== '' ? 'https://www.youtube.com/embed/' . rawurlencode($youtube_id) . '?rel=0' : '';
$social_links = [
    ['key' => 'facebook', 'label' => 'Facebook', 'href' => (string) ($brand['facebook'] ?? ''), 'text' => 'Company and manufacturing updates.'],
    ['key' => 'instagram', 'label' => 'Instagram', 'href' => (string) ($brand['instagram'] ?? ''), 'text' => 'Products, facilities and events.'],
    ['key' => 'youtube', 'label' => 'YouTube', 'href' => $youtube_id !== '' ? 'https://www.youtube.com/watch?v=' . rawurlencode($youtube_id) : '', 'text' => 'Watch the NorenSpring company film.'],
];
$social_links = array_values(array_filter($social_links, static fn(array $item): bool => trim((string) ($item['href'] ?? '')) !== ''));

get_template_part('parts/inner-hero', null, [
    'variant' => 'about',
    'title' => $about['hero']['title'] ?? 'About NorenSpring',
    'subtitle' => $about['hero']['subtitle'] ?? 'Precision spring manufacturing since 2001.',
    'image' => $about['hero']['image'] ?? 'about-building-v3.png',
    'mobile_image' => $about['hero']['mobile_image'] ?? 'about-hero-mobile-v1.png',
]);

get_template_part('parts/about-subnav');

$brand_window_image = springapex_asset('assets/images/generated/springapex-factory-floor-v1.webp');
?>

<?php if ($youtube_embed_url !== '') : ?>
  <section class="section sa-company-film" aria-label="<?php esc_attr_e('NorenSpring company film', 'springapex'); ?>">
    <div class="container container-wide">
      <div class="sa-company-film__player" data-reveal="up">
        <iframe
          src="<?php echo esc_url($youtube_embed_url); ?>"
          title="<?php echo esc_attr((string) ($company_video['title'] ?? 'Inside NorenSpring')); ?>"
          loading="lazy"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
          referrerpolicy="strict-origin-when-cross-origin"
          allowfullscreen
        ></iframe>
      </div>
    </div>
  </section>
<?php endif; ?>

<section class="sa-brand-window" data-brand-window aria-label="<?php esc_attr_e('NorenSpring precision manufacturing', 'springapex'); ?>">
  <div class="container container-wide">
    <span class="sr-only">NorenSpring</span>
    <svg class="sa-brand-window__art" viewBox="0 0 1600 300" role="presentation" aria-hidden="true" focusable="false">
      <defs>
        <clipPath id="sa-apexspring-wordmark-clip" clipPathUnits="userSpaceOnUse">
          <text class="sa-brand-window__clip-text" x="800" y="226" text-anchor="middle">NorenSpring</text>
        </clipPath>
      </defs>
      <g clip-path="url(#sa-apexspring-wordmark-clip)">
        <image
          class="sa-brand-window__image"
          data-brand-window-image
          href="<?php echo esc_url($brand_window_image); ?>"
          x="-80"
          y="-180"
          width="1760"
          height="660"
          preserveAspectRatio="xMidYMid slice"
        />
      </g>
      <text class="sa-brand-window__outline" x="800" y="226" text-anchor="middle">NorenSpring</text>
    </svg>
  </div>
</section>

<?php

get_template_part('parts/company-introduction', null, ['variant' => 'about']);

get_template_part('parts/why-apexspring');
?>

<section class="section sa-about-certificates" id="quality-certificates" aria-label="<?php esc_attr_e('Quality certificates', 'springapex'); ?>">
  <div class="container container-wide">
    <?php get_template_part('parts/certification-gallery', null, [
        'id' => 'about-certificate-gallery',
        'certificates' => springapex_get('company.quality.certificates', []),
        'variant' => 'strip',
        'viewer' => true,
    ]); ?>
  </div>
</section>

<?php if ($team) : ?>
  <section class="section sa-team" aria-labelledby="sa-team-statement">
    <div class="container container-wide">
      <div class="sa-team__manifesto">
        <div class="sa-team__statement" data-reveal="up">
          <p class="sa-team__eyebrow"><?php echo esc_html((string) ($team['eyebrow'] ?? '')); ?></p>
          <span class="sa-team__accent" aria-hidden="true"></span>
          <h2 id="sa-team-statement">
            <span class="sa-team__statement-lead"><?php echo esc_html((string) ($team['statement_lead'] ?? 'Women-owned precision.')); ?></span>
            <span class="sa-team__statement-signature"><?php echo esc_html((string) ($team['statement_signature'] ?? 'Driven by innovation.')); ?></span>
          </h2>
        </div>

        <?php $founder = is_array($team['founder'] ?? null) ? $team['founder'] : []; ?>
        <div class="sa-team__founder" data-reveal="up">
          <div class="sa-team__founder-media">
            <?php echo springapex_image((string) ($founder['image'] ?? ''), sprintf(
                __('%1$s, %2$s of NorenSpring', 'springapex'),
                (string) ($founder['name'] ?? ''),
                (string) ($founder['role'] ?? '')
            ), [
                'width' => 900,
                'height' => 1222,
                'sizes' => '(max-width: 760px) 150px, 34vw',
            ]); ?>
          </div>
          <div class="sa-team__founder-caption">
            <span aria-hidden="true"></span>
            <p>
              <strong><?php echo esc_html((string) ($founder['name'] ?? '')); ?></strong>
              <small><?php echo esc_html((string) ($founder['role'] ?? '')); ?></small>
            </p>
          </div>
        </div>
      </div>

      <div class="sa-team__groups" data-reveal-group>
        <?php foreach ((array) ($team['groups'] ?? []) as $group) : ?>
          <section class="sa-team__group" aria-label="<?php echo esc_attr((string) ($group['title'] ?? '')); ?>">
            <header class="sa-team__group-header">
              <h3><?php echo esc_html((string) ($group['title'] ?? '')); ?></h3>
              <span aria-hidden="true"></span>
            </header>
            <div class="sa-team__grid">
              <?php foreach ((array) ($group['members'] ?? []) as $member) : ?>
                <figure class="sa-team__member">
                  <div class="sa-team__member-media">
                    <?php echo springapex_image((string) ($member['image'] ?? ''), sprintf(
                        __('%1$s, %2$s at NorenSpring', 'springapex'),
                        (string) ($member['name'] ?? ''),
                        (string) ($member['role'] ?? '')
                    ), [
                        'width' => 960,
                        'height' => 720,
                        'sizes' => '(max-width: 760px) 50vw, 25vw',
                    ]); ?>
                  </div>
                  <figcaption>
                    <strong><?php echo esc_html((string) ($member['name'] ?? '')); ?></strong>
                    <span><?php echo esc_html((string) ($member['role'] ?? '')); ?></span>
                  </figcaption>
                </figure>
              <?php endforeach; ?>
            </div>
          </section>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php if ($timeline) : ?>
<section class="section sa-timeline" aria-labelledby="sa-company-development-title">
  <div class="container container-wide">
    <div class="sa-timeline__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('COMPANY DEVELOPMENT', 'springapex'); ?></p>
        <h2 id="sa-company-development-title"><?php esc_html_e('Built for repeat production.', 'springapex'); ?></h2>
      </div>
    </div>
    <ol class="sa-timeline__list" data-reveal-group>
      <?php foreach ($timeline as $item) : ?>
        <li>
          <figure class="sa-timeline__media">
            <?php echo springapex_image((string) ($item['image'] ?? ''), (string) ($item['alt'] ?? ''), [
                'width' => 1536,
                'height' => 1024,
                'sizes' => '(max-width: 760px) 100vw, 33vw',
            ]); ?>
          </figure>
          <span class="sa-timeline__marker" aria-hidden="true"></span>
          <div class="sa-timeline__copy">
            <span class="sa-timeline__year"><?php echo esc_html((string) ($item['year'] ?? '')); ?></span>
            <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
            <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
<?php endif; ?>

<section class="section sa-global-network" id="global-network" aria-labelledby="sa-global-network-title">
  <div class="container container-wide">
    <span class="sa-global-network__wordmark" aria-hidden="true">GLOBAL</span>
    <div class="sa-global-network__grid">
      <header class="sa-global-network__intro" data-reveal="up">
        <p class="section-kicker"><?php esc_html_e('GLOBAL SUPPORT', 'springapex'); ?></p>
        <h2 id="sa-global-network-title"><?php esc_html_e('One manufacturing base. Connected project support.', 'springapex'); ?></h2>
        <p><?php esc_html_e('International projects are coordinated from Xuzhou through direct engineering review, production communication and delivery documentation.', 'springapex'); ?></p>
      </header>
      <figure class="sa-global-network__map" data-reveal="up">
        <?php echo springapex_image('about-global-support-map-v1.png', __('International project support coordinated from Xuzhou, China', 'springapex'), [
            'width' => 1672,
            'height' => 941,
            'sizes' => '(max-width: 760px) 100vw, 62vw',
        ]); ?>
      </figure>
    </div>
    <div class="sa-global-network__action" data-reveal="up">
      <span class="sa-global-network__location"><?php echo springapex_icon('map-pin', 'icon icon-sm'); ?> <?php esc_html_e('Xuzhou, China', 'springapex'); ?></span>
      <a href="<?php echo esc_url(springapex_url('/contact/#contact-network')); ?>"><?php esc_html_e('View Contact Network', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon'); ?></a>
    </div>
  </div>
</section>

<?php if ($social_links) : ?>
  <section class="section sa-social-hub" id="official-channels" aria-labelledby="sa-social-hub-title">
    <div class="container container-wide">
      <header class="sa-social-hub__head" data-reveal="up">
        <p class="section-kicker"><?php esc_html_e('OFFICIAL CHANNELS', 'springapex'); ?></p>
        <h2 id="sa-social-hub-title"><?php esc_html_e('Follow NorenSpring.', 'springapex'); ?></h2>
        <span class="sa-social-hub__rule" aria-hidden="true"></span>
        <p><?php esc_html_e('Only confirmed public profile links are shown here.', 'springapex'); ?></p>
      </header>

      <div class="sa-social-hub__rail" data-reveal="up">
        <span class="sa-social-hub__label" aria-hidden="true"><?php esc_html_e('FOLLOW', 'springapex'); ?></span>
        <div class="sa-social-hub__items" data-reveal-group>
          <?php foreach ($social_links as $social) : ?>
            <?php $social_label = (string) ($social['label'] ?? ''); ?>
            <a
              class="sa-social-hub__item"
              href="<?php echo esc_url((string) $social['href']); ?>"
              target="_blank"
              rel="noopener noreferrer"
              aria-label="<?php echo esc_attr(sprintf(__('Follow NorenSpring on %s', 'springapex'), $social_label)); ?>"
            >
              <span class="sa-social-hub__icon sa-social-hub__icon--<?php echo esc_attr((string) ($social['key'] ?? '')); ?>"><?php echo springapex_icon((string) ($social['key'] ?? '')); ?></span>
              <span class="sa-social-hub__copy">
                <strong><?php echo esc_html($social_label); ?></strong>
                <small><?php echo esc_html((string) ($social['text'] ?? '')); ?></small>
              </span>
              <?php echo springapex_icon('arrow-right', 'icon icon-sm sa-social-hub__arrow'); ?>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php endif; ?>
