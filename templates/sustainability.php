<?php
if (!defined('ABSPATH')) {
    exit;
}

$lifecycle = [
    [
        'number' => '01',
        'title' => 'Material selection',
        'text' => 'Spring wire and raw materials are selected around application requirements, performance and service life.',
        'points' => [
            'Qualified suppliers and documented material certificates.',
            'Material choice aligned to strength, fatigue and corrosion needs.',
            'Traceable records from incoming material to finished spring.',
        ],
        'image' => 'generated/springapex-news-material-selection-v1.webp',
        'alt' => 'Spring wire, material samples and finished spring components prepared for review',
    ],
    [
        'number' => '02',
        'title' => 'Controlled production',
        'text' => 'Documented instructions and process controls guide repeatable spring manufacturing across the shop floor.',
        'points' => [
            'Production settings defined for the approved part and process.',
            'Equipment condition and process parameters checked during production.',
            'Housekeeping and safe-work responsibilities applied in daily operations.',
        ],
        'image' => 'generated/springapex-factory-floor-v1.webp',
        'alt' => 'Controlled spring manufacturing equipment on the ApexSpring production floor',
    ],
    [
        'number' => '03',
        'title' => 'Inspection & traceability',
        'text' => 'Critical dimensions and functional characteristics are checked against agreed drawings and requirements.',
        'points' => [
            'Incoming material and in-process verification.',
            'Final inspection to approved drawings and specifications.',
            'Inspection and traceability records supplied as agreed.',
        ],
        'image' => 'generated/springapex-quality-lab-v1.webp',
        'alt' => 'ApexSpring technician measuring a spring in the quality laboratory',
    ],
    [
        'number' => '04',
        'title' => 'Protective packaging & delivery',
        'text' => 'Finished springs are protected, identified and prepared for reliable receiving and international delivery.',
        'points' => [
            'Protective packaging selected around part size and finish.',
            'Clear identification for part number, lot and quantity.',
            'Export-ready documentation prepared to project requirements.',
        ],
        'image' => 'generated/springapex-news-export-packaging-v1.webp',
        'alt' => 'Precision springs arranged in protective export packaging',
    ],
];

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
    'title' => 'Records before claims.',
    'text' => 'Measured targets and reduction results are published only after reviewed records are available.',
    'meta' => '',
];
?>

<section class="sa-sustainability-hero" aria-labelledby="sa-sustainability-title">
  <div class="sa-sustainability-hero__media" aria-hidden="true">
    <?php echo springapex_image('generated/apexspring-sustainability-wire-lifecycle-v1.png', '', [
        'width' => 2023,
        'height' => 777,
        'sizes' => '100vw',
        'fetchpriority' => 'high',
    ]); ?>
  </div>
  <div class="container container-wide sa-sustainability-hero__inner">
    <div class="sa-sustainability-hero__copy" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('SUSTAINABILITY', 'springapex'); ?></p>
      <h1 id="sa-sustainability-title"><?php esc_html_e('Responsibility in practice.', 'springapex'); ?></h1>
      <p><?php esc_html_e('From material selection to delivery.', 'springapex'); ?></p>
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
        <p class="section-kicker"><?php esc_html_e('MATERIAL LIFECYCLE', 'springapex'); ?></p>
        <h2 id="sa-sustainability-lifecycle-title"><?php esc_html_e('Material Lifecycle Story', 'springapex'); ?></h2>
        <p class="sa-sustainability-lifecycle__intro"><?php esc_html_e('Responsibility is engineered through the full lifecycle of every spring we build.', 'springapex'); ?></p>
      </div>
      <p><?php esc_html_e('Current framework', 'springapex'); ?></p>
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
        <p class="section-kicker"><?php esc_html_e('MANAGEMENT SYSTEMS', 'springapex'); ?></p>
        <h2 id="sa-sustainability-certificates-title"><?php esc_html_e('Certified systems. Verified records.', 'springapex'); ?></h2>
        <p><?php esc_html_e('Environmental and occupational health and safety management systems provide the framework for responsible operations and continual improvement.', 'springapex'); ?></p>
        <a class="sa-sustainability-certificates__link" href="<?php echo esc_url(springapex_url('/about/#quality-certificates')); ?>">
          <?php esc_html_e('View all certificates', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
      </header>

      <div class="sa-sustainability-certificates__grid" data-reveal-group>
        <?php foreach ($certificates as $certificate) : ?>
          <?php
          $document = trim((string) ($certificate['document'] ?? ''));
          $document_url = springapex_file_url($document, 'assets/documents');
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
      <?php echo springapex_image('generated/springapex-news-quality-audit-v1.webp', __('Spring inspection under documented quality and safety procedures', 'springapex'), [
          'width' => 1536,
          'height' => 1024,
          'sizes' => '(max-width: 760px) 100vw, 50vw',
      ]); ?>
    </figure>
    <div data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('SAFE WORK', 'springapex'); ?></p>
      <h2 id="sa-sustainability-safety-title"><?php esc_html_e('Safe work supports dependable production.', 'springapex'); ?></h2>
      <p><?php esc_html_e('The occupational health and safety management system defines responsibilities, training and workplace controls that support people and consistent production.', 'springapex'); ?></p>
      <ul>
        <li><?php esc_html_e('Documented responsibilities and operating procedures.', 'springapex'); ?></li>
        <li><?php esc_html_e('Training aligned to assigned work and equipment.', 'springapex'); ?></li>
        <li><?php esc_html_e('Routine checks and continual improvement of workplace controls.', 'springapex'); ?></li>
      </ul>
    </div>
  </div>
</section>

<section class="section sa-sustainability-progress" aria-labelledby="sa-sustainability-progress-title">
  <div class="container container-wide sa-sustainability-progress__layout">
    <div data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('TRANSPARENT PROGRESS', 'springapex'); ?></p>
      <h2 id="sa-sustainability-progress-title"><?php esc_html_e('Progress we can document.', 'springapex'); ?></h2>
    </div>
    <div data-reveal="up">
      <p><?php esc_html_e('Certifications, management-system scope and supporting records can be shared for supplier audits and qualification reviews. Contact our engineering team for the documentation your programme requires.', 'springapex'); ?></p>
      <div class="sa-sustainability-progress__actions">
        <a class="sa-sustainability-progress__primary" href="<?php echo esc_url(springapex_url('/contact/?intent=sustainability')); ?>">
          <?php esc_html_e('Contact Engineering', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
        <a class="sa-sustainability-progress__secondary" href="<?php echo esc_url(springapex_url('/resources/')); ?>">
          <?php esc_html_e('Open Download Center', 'springapex'); ?>
        </a>
      </div>
    </div>
  </div>
</section>
