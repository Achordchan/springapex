<?php
if (!defined('ABSPATH')) {
    exit;
}

$sustainability = springapex_get('sustainability', []);
$lifecycle_section = is_array($sustainability['lifecycle'] ?? null) ? $sustainability['lifecycle'] : [];
$lifecycle = array_values(array_filter(
    is_array($lifecycle_section['items'] ?? null) ? $lifecycle_section['items'] : [],
    static fn(mixed $item): bool => is_array($item) && springapex_image_value_available($item['image'] ?? '')
));
$management = is_array($sustainability['management'] ?? null) ? $sustainability['management'] : [];
$safety = is_array($sustainability['safety'] ?? null) ? $sustainability['safety'] : [];
$progress = is_array($sustainability['progress'] ?? null) ? $sustainability['progress'] : [];
$proof_record = is_array($sustainability['proof_record'] ?? null) ? $sustainability['proof_record'] : [];
$safety_image = springapex_image_value_available($safety['image'] ?? '')
    ? $safety['image']
    : 'generated/springapex-news-quality-audit-v1.webp';

$certificate_names = ['ISO 14001', 'ISO 45001'];
$certificates = array_values(array_filter(
    (array) springapex_get('company.quality.certificates', []),
    static fn(array $certificate): bool => in_array((string) ($certificate['name'] ?? ''), $certificate_names, true)
));

$proof_items = [];
foreach ($certificates as $certificate) {
    $proof_items[] = [
        'icon' => 'shield',
        'title' => (string) ($certificate['name'] ?? ''),
        'text' => (string) ($certificate['scope'] ?? ''),
        'meta' => (string) ($certificate['valid_until'] ?? ''),
    ];
}
$proof_items[] = [
    'icon' => 'form',
    'title' => (string) ($proof_record['title'] ?? ''),
    'text' => (string) ($proof_record['text'] ?? ''),
    'meta' => '',
];
$sustainability_hero = springapex_get('sustainability.hero', []);
?>

<section class="sa-sustainability-hero" aria-labelledby="sa-sustainability-title">
  <div class="sa-sustainability-hero__media" aria-hidden="true">
    <?php echo springapex_image($sustainability_hero['image'] ?? 'generated/apexspring-sustainability-wire-lifecycle-v1.png', '', [
        'width' => 2023,
        'height' => 777,
        'sizes' => '100vw',
        'fetchpriority' => 'high',
        'mobile_image' => $sustainability_hero['mobile_image'] ?? '',
    ]); ?>
  </div>
  <div class="container container-wide sa-sustainability-hero__inner">
    <div class="sa-sustainability-hero__copy" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($sustainability_hero['eyebrow'] ?? 'SUSTAINABILITY')); ?></p>
      <h1 id="sa-sustainability-title"><?php echo esc_html((string) ($sustainability_hero['title'] ?? 'Responsibility in practice.')); ?></h1>
      <p><?php echo esc_html((string) ($sustainability_hero['subtitle'] ?? 'From material selection to delivery.')); ?></p>
    </div>
  </div>
</section>

<?php get_template_part('parts/about-subnav'); ?>

<section class="sa-sustainability-proof" aria-label="<?php esc_attr_e('Verified sustainability management evidence', 'springapex'); ?>">
  <div class="container container-wide sa-sustainability-proof__grid" data-reveal-group>
    <?php foreach ($proof_items as $proof) : ?>
      <article class="sa-sustainability-proof__item">
        <span class="sa-sustainability-proof__icon"><?php echo springapex_icon((string) $proof['icon']); ?></span>
        <div>
          <h2><?php echo esc_html((string) $proof['title']); ?></h2>
          <p><?php echo esc_html((string) $proof['text']); ?></p>
          <?php if ((string) $proof['meta'] !== '') : ?>
            <small><?php echo esc_html((string) $proof['meta']); ?></small>
          <?php endif; ?>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
</section>

<section class="section sa-sustainability-lifecycle" aria-labelledby="sa-sustainability-lifecycle-title">
  <div class="container container-wide">
    <header class="sa-sustainability-lifecycle__head" data-reveal="up">
      <div>
        <p class="section-kicker"><?php echo esc_html((string) ($lifecycle_section['eyebrow'] ?? '')); ?></p>
        <h2 id="sa-sustainability-lifecycle-title"><?php echo esc_html((string) ($lifecycle_section['title'] ?? '')); ?></h2>
        <p class="sa-sustainability-lifecycle__intro"><?php echo esc_html((string) ($lifecycle_section['text'] ?? '')); ?></p>
      </div>
      <p><?php echo esc_html((string) ($lifecycle_section['status'] ?? '')); ?></p>
    </header>

    <ol class="sa-sustainability-lifecycle__list">
      <?php foreach ($lifecycle as $index => $stage) : ?>
        <li class="sa-sustainability-lifecycle__item<?php echo $index % 2 === 1 ? ' is-reverse' : ''; ?>" data-reveal="up">
          <span class="sa-sustainability-lifecycle__marker"><?php echo esc_html((string) $stage['number']); ?></span>
          <figure class="sa-sustainability-lifecycle__media">
            <?php echo springapex_image((string) $stage['image'], (string) $stage['alt'], [
                'width' => 1536,
                'height' => 1024,
                'sizes' => '(max-width: 760px) 100vw, 48vw',
            ]); ?>
          </figure>
          <div class="sa-sustainability-lifecycle__copy">
            <h3><?php echo esc_html((string) $stage['title']); ?></h3>
            <p><?php echo esc_html((string) $stage['text']); ?></p>
            <ul>
              <?php foreach ((array) $stage['points'] as $point) : ?>
                <li><?php echo esc_html((string) $point); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>

<?php if ($certificates) : ?>
  <section class="section sa-sustainability-certificates" id="verified-certificates" aria-labelledby="sa-sustainability-certificates-title">
    <div class="container container-wide sa-sustainability-certificates__layout">
      <header data-reveal="up">
        <p class="section-kicker"><?php echo esc_html((string) ($management['eyebrow'] ?? '')); ?></p>
        <h2 id="sa-sustainability-certificates-title"><?php echo esc_html((string) ($management['title'] ?? '')); ?></h2>
        <p><?php echo esc_html((string) ($management['text'] ?? '')); ?></p>
        <a class="sa-sustainability-certificates__link" href="<?php echo esc_url(springapex_url((string) ($management['action_href'] ?? ''))); ?>">
          <?php echo esc_html((string) ($management['action_label'] ?? '')); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </header>

      <div class="sa-sustainability-certificates__grid" data-reveal-group>
        <?php foreach ($certificates as $certificate) : ?>
          <?php
          $document = trim((string) ($certificate['document'] ?? ''));
          $document_url = springapex_file_delivery_urls($document, 'assets/documents')['original'];
          ?>
          <a class="sa-sustainability-certificate" href="<?php echo esc_url($document_url); ?>" target="_blank" rel="noopener noreferrer">
            <figure>
              <?php echo springapex_image((string) ($certificate['image'] ?? ''), (string) ($certificate['name'] ?? ''), [
                  'width' => 640,
                  'height' => 900,
                  'sizes' => '(max-width: 760px) 42vw, 190px',
              ]); ?>
            </figure>
            <div>
              <h3><?php echo esc_html((string) ($certificate['name'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($certificate['scope'] ?? '')); ?></p>
              <small><?php echo esc_html((string) ($certificate['valid_until'] ?? '')); ?></small>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<section class="section sa-sustainability-safety" aria-labelledby="sa-sustainability-safety-title">
  <div class="container container-wide sa-sustainability-safety__layout">
    <figure data-reveal="up">
      <?php echo springapex_image($safety_image, (string) ($safety['image_alt'] ?? ''), [
          'width' => 1536,
          'height' => 1024,
          'sizes' => '(max-width: 760px) 100vw, 50vw',
      ]); ?>
    </figure>
    <div data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($safety['eyebrow'] ?? '')); ?></p>
      <h2 id="sa-sustainability-safety-title"><?php echo esc_html((string) ($safety['title'] ?? '')); ?></h2>
      <p><?php echo esc_html((string) ($safety['text'] ?? '')); ?></p>
      <ul>
        <?php foreach ((array) ($safety['points'] ?? []) as $point) : ?><li><?php echo esc_html((string) $point); ?></li><?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<section class="section sa-sustainability-progress" aria-labelledby="sa-sustainability-progress-title">
  <div class="container container-wide sa-sustainability-progress__layout">
    <div data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($progress['eyebrow'] ?? '')); ?></p>
      <h2 id="sa-sustainability-progress-title"><?php echo esc_html((string) ($progress['title'] ?? '')); ?></h2>
    </div>
    <div data-reveal="up">
      <p><?php echo esc_html((string) ($progress['text'] ?? '')); ?></p>
      <div class="sa-sustainability-progress__actions">
        <a class="sa-sustainability-progress__primary" href="<?php echo esc_url(springapex_url((string) ($progress['primary_href'] ?? ''))); ?>">
          <?php echo esc_html((string) ($progress['primary_label'] ?? '')); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
        <a class="sa-sustainability-progress__secondary" href="<?php echo esc_url(springapex_url((string) ($progress['secondary_href'] ?? ''))); ?>">
          <?php echo esc_html((string) ($progress['secondary_label'] ?? '')); ?>
        </a>
      </div>
    </div>
  </div>
</section>
