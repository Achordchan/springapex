<?php
if (!defined('ABSPATH')) {
    exit;
}
$about = springapex_get('about', []);
$hero = $about['hero'] ?? [];
$company = springapex_get('company', []);
$company_address = (string) springapex_get('brand.address', 'Xuzhou, Jiangsu, China');
$map_embed_url = 'https://www.google.com/maps?q=' . rawurlencode($company_address) . '&output=embed';
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'about',
    'title' => $hero['title'] ?? 'About SpringApex',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? '',
    'ctas' => [[
        'label' => $hero['cta']['label'] ?? 'Get to Know Us',
        'href' => $hero['cta']['href'] ?? '#story',
        'icon' => 'arrow-right',
    ]],
]);
?>

<section class="section sa-about-snapshot">
  <div class="container container-wide">
    <div class="sa-about-snapshot__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('ABOUT SPRINGAPEX', 'springapex'); ?></p>
        <h2><?php esc_html_e('Precision spring manufacturing built around real application requirements.', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('SpringApex combines engineering review, controlled manufacturing and quality documentation so customers can move from an application need to a repeatable production plan.', 'springapex'); ?></p>
    </div>
    <div class="sa-about-snapshot__grid" data-reveal-group>
      <article class="sa-about-snapshot__item">
        <div class="sa-about-snapshot__top"><div class="icon-circle soft"><?php echo springapex_icon('spring'); ?></div><span>01</span></div>
        <h3><?php esc_html_e('Products & Technology', 'springapex'); ?></h3>
        <p><?php esc_html_e('Compression, extension, torsion, disc, die, wire-form and application-specific spring families are selected around load, movement and service environment.', 'springapex'); ?></p>
        <a class="text-link" href="<?php echo esc_url(springapex_url('/products/')); ?>"><?php esc_html_e('Explore product families', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      </article>
      <article class="sa-about-snapshot__item">
        <div class="sa-about-snapshot__top"><div class="icon-circle soft"><?php echo springapex_icon('factory'); ?></div><span>02</span></div>
        <h3><?php esc_html_e('Manufacturing & Quality', 'springapex'); ?></h3>
        <p><?php esc_html_e('Requirements review, tooling, forming, heat and surface processes, inspection and delivery are organized as one controlled workflow.', 'springapex'); ?></p>
        <a class="text-link" href="<?php echo esc_url(springapex_url('/capabilities/')); ?>"><?php esc_html_e('View capabilities', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      </article>
      <article class="sa-about-snapshot__item">
        <div class="sa-about-snapshot__top"><div class="icon-circle soft"><?php echo springapex_icon('headset'); ?></div><span>03</span></div>
        <h3><?php esc_html_e('Direct Project Support', 'springapex'); ?></h3>
        <p><?php esc_html_e('Send a drawing, a sample description or an application requirement and the commercial and engineering teams will confirm the next step.', 'springapex'); ?></p>
        <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>"><?php esc_html_e('Talk to the team', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      </article>
    </div>
    <dl class="sa-about-facts" data-reveal-group>
      <?php foreach (array_slice($company['facts'] ?? [], 0, 4) as $stat) : ?>
        <?php
        $stat_value = (string) ($stat['value'] ?? '0');
        $stat_target = (int) (preg_replace('/[^0-9]/', '', $stat_value) ?: '0');
        ?>
        <div>
          <dt><?php echo esc_html((string) $stat['label']); ?></dt>
          <dd>
            <span class="stat-value-visual" aria-hidden="true" data-count-target="<?php echo esc_attr((string) $stat_target); ?>" data-count-display="<?php echo esc_attr($stat_value); ?>"><?php echo esc_html($stat_value); ?></span>
            <span class="sr-only"><?php echo esc_html($stat_value); ?></span>
          </dd>
        </div>
      <?php endforeach; ?>
    </dl>
  </div>
</section>

<section class="section story-section" id="story">
  <div class="container container-wide story-grid">
    <div class="story-copy" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html($about['story']['eyebrow'] ?? 'OUR STORY'); ?></p>
      <h2 class="display-sm"><?php echo esc_html($about['story']['title'] ?? ''); ?></h2>
      <p class="story-text"><?php echo esc_html($about['story']['text'] ?? ''); ?></p>
      <div class="value-row" data-reveal-group>
        <?php foreach (($about['story']['values'] ?? []) as $value) : ?>
          <article class="value-item">
            <div class="value-icon"><?php echo springapex_icon((string) $value['icon']); ?></div>
            <h3><?php echo esc_html((string) $value['title']); ?></h3>
            <p><?php echo esc_html((string) $value['text']); ?></p>
          </article>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="story-media" data-reveal="left">
      <?php echo springapex_image($about['story']['image'] ?? 'about-story-springs-v5.png', __('Assorted precision springs', 'springapex'), [
          'width' => 1600,
          'height' => 700,
          'sizes' => '(max-width: 760px) 100vw, 50vw',
      ]); ?>
    </div>
  </div>
</section>

<section class="section sa-timeline">
  <div class="container container-wide">
    <div class="sa-timeline__intro" data-reveal="up">
      <div>
        <p class="section-kicker"><?php esc_html_e('COMPANY DEVELOPMENT', 'springapex'); ?></p>
        <h2><?php esc_html_e('A focused manufacturing business built over time.', 'springapex'); ?></h2>
      </div>
      <p><?php esc_html_e('Production capacity, quality systems and international project support have developed around one goal: dependable repeat manufacturing.', 'springapex'); ?></p>
    </div>
    <ol class="sa-timeline__list" data-reveal-group>
      <?php foreach (($company['timeline'] ?? []) as $item) : ?>
        <li>
          <span class="sa-timeline__year"><?php echo esc_html((string) ($item['year'] ?? '')); ?></span>
          <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<section class="section sa-factory-gallery">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('FACILITIES & QUALITY', 'springapex'); ?></p>
        <h2><?php esc_html_e('Manufacturing and inspection context.', 'springapex'); ?></h2>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/capabilities/')); ?>"><?php esc_html_e('Explore capabilities', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
    </div>
    <div class="sa-factory-gallery__grid" data-reveal-group>
      <?php foreach (($company['gallery'] ?? []) as $item) : ?>
        <figure>
          <?php echo springapex_image((string) ($item['image'] ?? ''), (string) ($item['alt'] ?? ''), [
              'width' => 1400,
              'height' => 900,
              'sizes' => '(max-width: 760px) 100vw, 50vw',
          ]); ?>
          <figcaption><?php echo esc_html((string) ($item['caption'] ?? '')); ?></figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section sa-global-presence">
  <div class="container container-wide sa-global-presence__grid">
    <div class="sa-global-presence__content" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('GLOBAL PROGRAM SUPPORT', 'springapex'); ?></p>
      <h2><?php echo esc_html((string) ($company['markets']['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($company['markets']['text'] ?? '')); ?></p>
      <div class="sa-global-presence__regions">
        <?php foreach (($company['markets']['regions'] ?? []) as $region) : ?><span><?php echo esc_html((string) $region); ?></span><?php endforeach; ?>
      </div>
    </div>
    <figure class="sa-global-presence__map" data-reveal="left">
      <iframe
        src="<?php echo esc_url($map_embed_url); ?>"
        title="<?php echo esc_attr(sprintf(__('SpringApex location: %s', 'springapex'), $company_address)); ?>"
        loading="lazy"
        referrerpolicy="no-referrer-when-downgrade"
        allowfullscreen
      ></iframe>
      <figcaption><?php echo springapex_icon('map-pin', 'icon icon-sm'); ?><span><?php echo esc_html($company_address); ?></span></figcaption>
    </figure>
    <dl class="sa-company-identity">
      <div><dt><?php esc_html_e('Company', 'springapex'); ?></dt><dd><?php echo esc_html((string) springapex_get('brand.company', '')); ?></dd></div>
      <div><dt><?php esc_html_e('Location', 'springapex'); ?></dt><dd><?php echo esc_html($company_address); ?></dd></div>
      <div><dt><?php esc_html_e('Direct Contact', 'springapex'); ?></dt><dd><?php echo esc_html((string) springapex_get('brand.email', '')); ?></dd></div>
    </dl>
  </div>
</section>

<?php get_template_part('parts/quality-summary'); ?>

<?php
get_template_part('parts/cta-band', null, [
    'title' => "Let's Build What's Next Together.",
    'text' => 'Have a challenge in mind? Our team is ready to help you find the right spring solution.',
    'cta' => ['label' => 'Get a Quote', 'href' => '/contact/?intent=quote'],
    'class' => 'sa-about-cta',
]);
?>
