<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$solution = null;
if (!defined('SPRINGAPEX_PREVIEW') && function_exists('is_singular') && is_singular('spring_solution')) {
    $post_id = (int) get_queried_object_id();
    $slug = (string) get_post_field('post_name', $post_id);
    $solution = springapex_solution($slug);
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW')) {
    $slug = (string) get_query_var('solution_slug', 'automotive');
    $solution = springapex_solution($slug);
}
if (!$solution) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('Solution not found', 'springapex') . '</h1></div></section>';
    return;
}

$industry_title = trim((string) ($solution['title'] ?? 'Industry'));
$hero_title = trim((string) ($solution['hero_title'] ?? ''));
$hero_title = $hero_title !== '' ? $hero_title : sprintf(__('%s spring programs built for repeat production.', 'springapex'), $industry_title);
$hero_text = trim((string) ($solution['challenge_intro'] ?? $solution['tagline'] ?? ''));
$hero_image = $solution['image'] ?? '';
$contact_url = springapex_url('/contact/?intent=solution&industry=' . rawurlencode($slug));
$drawing_url = springapex_url('/contact/?intent=drawing&industry=' . rawurlencode($slug));

$requirements = [];
foreach ((array) ($solution['challenges'] ?? []) as $item) {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title !== '') {
            $requirements[] = [
                'title' => $title,
                'text' => trim((string) ($item['text'] ?? '')),
                'icon' => sanitize_key((string) ($item['icon'] ?? 'target')) ?: 'target',
            ];
        }
    } elseif (trim((string) $item) !== '') {
        $requirements[] = ['title' => trim((string) $item), 'text' => '', 'icon' => 'target'];
    }
}

$recommended = [];
$product_lookup = [];
foreach ((array) ($solution['products'] ?? []) as $product_slug) {
    $product_slug = sanitize_title((string) $product_slug);
    if ($product_slug === '') {
        continue;
    }
    $product = springapex_product($product_slug);
    if ($product) {
        $recommended[] = $product;
        $product_lookup[$product_slug] = $product;
    }
}

$applications = [];
$application_source = $solution['application_items'] ?? [];
foreach ((array) $application_source as $item) {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $application_products = [];
        foreach ((array) ($item['products'] ?? []) as $product_slug) {
            $product_slug = sanitize_title((string) $product_slug);
            $product = $product_lookup[$product_slug] ?? springapex_product($product_slug);
            if ($product) {
                $application_products[] = $product;
            }
        }
        $applications[] = [
            'title' => $title,
            'text' => trim((string) ($item['text'] ?? '')),
            'icon' => sanitize_key((string) ($item['icon'] ?? 'target')) ?: 'target',
            'image' => trim((string) ($item['image'] ?? '')),
            'image_id' => (int) ($item['image_id'] ?? 0),
            'products' => $application_products,
        ];
    } elseif (trim((string) $item) !== '') {
        $applications[] = [
            'title' => trim((string) $item),
            'text' => '',
            'icon' => 'target',
            'image' => '',
            'image_id' => 0,
            'products' => [],
        ];
    }
}

$program_steps = [];
$step_source = $solution['program_steps'] ?? [];
$step_images = [
    'manufacturing-videos/application-engineering-v1.webp',
    'manufacturing-videos/featured-cnc-coiling-v1.webp',
    'manufacturing-videos/machine-setup-v1.webp',
    'manufacturing-videos/quality-inspection-v1.webp',
];
foreach ((array) $step_source as $index => $item) {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title === '') {
            continue;
        }
        $step_image_id = (int) ($item['image_id'] ?? 0);
        $program_steps[] = [
            'title' => $title,
            'text' => trim((string) ($item['text'] ?? '')),
            // The positional default only applies while the row has no picture of its
            // own; a Media Library pick clears the legacy path, so it must not come back.
            'image' => $step_image_id > 0
                ? ''
                : trim((string) ($item['image'] ?? ($step_images[$index] ?? ''))),
            'image_id' => $step_image_id,
        ];
    } elseif (trim((string) $item) !== '') {
        $program_steps[] = [
            'title' => trim((string) $item),
            'text' => '',
            'image' => $step_images[$index] ?? '',
            'image_id' => 0,
        ];
    }
}

$quality_items = [];
$quality_source = $solution['quality_items'] ?? [];
foreach ((array) $quality_source as $item) {
    if (is_array($item)) {
        $title = trim((string) ($item['title'] ?? ''));
        if ($title !== '') {
            $quality_items[] = [
                'title' => $title,
                'text' => trim((string) ($item['text'] ?? '')),
                'icon' => sanitize_key((string) ($item['icon'] ?? 'check-shield')) ?: 'check-shield',
            ];
        }
    } elseif (trim((string) $item) !== '') {
        $quality_items[] = ['title' => trim((string) $item), 'text' => '', 'icon' => 'check-shield'];
    }
}
$quality_image = trim((string) ($solution['quality_image'] ?? ''));
$quality_image_id = (int) ($solution['quality_image_id'] ?? 0);
$has_quality_image = $quality_image !== '' || $quality_image_id > 0;

$input_items = [
    ['icon' => 'pen', 'label' => __('Drawing or mechanism', 'springapex')],
    ['icon' => 'target', 'label' => __('Load and movement', 'springapex')],
    ['icon' => 'shield', 'label' => __('Environment and cycle life', 'springapex')],
    ['icon' => 'check-shield', 'label' => __('Volume and required records', 'springapex')],
];
?>

<article class="sa-industry-solution">
  <section class="sa-industry-hero">
    <div class="container container-wide sa-industry-hero__grid">
      <div class="sa-industry-hero__copy">
        <p class="section-kicker"><?php echo esc_html(strtoupper($industry_title)); ?> <?php esc_html_e('SPRING SOLUTIONS', 'springapex'); ?></p>
        <h1><?php echo esc_html($hero_title); ?></h1>
        <?php if ($hero_text !== '') : ?><p class="sa-industry-hero__lede"><?php echo esc_html($hero_text); ?></p><?php endif; ?>
        <div class="sa-industry-hero__actions">
          <a class="btn btn-primary" href="<?php echo esc_url($contact_url); ?>">
            <?php esc_html_e('Start an Engineering Review', 'springapex'); ?>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
          <a class="text-link" href="#industry-requirements">
            <?php esc_html_e('Explore the Program', 'springapex'); ?>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
      </div>
      <?php if ($hero_image !== '') : ?>
        <figure class="sa-industry-hero__media">
          <?php echo springapex_image($hero_image, sprintf(__('%s spring application', 'springapex'), $industry_title), [
              'width' => 1200,
              'height' => 900,
              'loading' => 'eager',
              'fetchpriority' => 'high',
              'sizes' => '(max-width: 860px) 100vw, 50vw',
          ]); ?>
        </figure>
      <?php endif; ?>
    </div>
  </section>

  <section class="sa-industry-inputs" aria-label="<?php esc_attr_e('Engineering review inputs', 'springapex'); ?>">
    <div class="container container-wide sa-industry-inputs__grid">
      <?php foreach ($input_items as $item) : ?>
        <div class="sa-industry-input">
          <?php echo springapex_icon((string) $item['icon'], 'sa-industry-input__icon'); ?>
          <span><?php echo esc_html((string) $item['label']); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($requirements) : ?>
    <section class="sa-industry-section sa-industry-requirements" id="industry-requirements">
      <div class="container container-wide sa-industry-requirements__grid">
        <header class="sa-industry-section__intro">
          <p class="section-kicker"><?php esc_html_e('INDUSTRY REQUIREMENTS', 'springapex'); ?></p>
          <h2><?php echo esc_html((string) ($solution['requirements_title'] ?? __('We design around the conditions that matter.', 'springapex'))); ?></h2>
          <?php if (!empty($solution['requirements_text'])) : ?><p><?php echo esc_html((string) $solution['requirements_text']); ?></p><?php endif; ?>
        </header>
        <div class="sa-industry-requirements__list">
          <?php foreach ($requirements as $item) : ?>
            <article class="sa-industry-requirement">
              <span class="sa-industry-requirement__icon"><?php echo springapex_icon((string) $item['icon'], 'icon'); ?></span>
              <h3><?php echo esc_html((string) $item['title']); ?></h3>
              <?php if ($item['text'] !== '') : ?><p><?php echo esc_html((string) $item['text']); ?></p><?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($applications) : ?>
    <section class="sa-industry-section sa-industry-applications">
      <div class="container container-wide">
        <p class="section-kicker"><?php esc_html_e('TYPICAL APPLICATIONS', 'springapex'); ?></p>
        <div class="sa-industry-applications__grid sa-industry-applications__grid--count-<?php echo esc_attr((string) count($applications)); ?>">
          <?php foreach ($applications as $item) :
            $has_image = $item['image'] !== '' || $item['image_id'] > 0;
            ?>
            <article class="sa-industry-application<?php echo $has_image ? '' : ' sa-industry-application--text'; ?>">
              <?php if ($has_image) : ?>
                <figure class="sa-industry-application__media">
                  <?php echo springapex_image(['id' => $item['image_id'], 'file' => $item['image']], (string) $item['title'], [
                      'width' => 800,
                      'height' => 520,
                      'sizes' => '(max-width: 760px) 100vw, 33vw',
                  ]); ?>
                </figure>
              <?php else : ?>
                <span class="sa-industry-application__icon"><?php echo springapex_icon((string) $item['icon'], 'icon'); ?></span>
              <?php endif; ?>
              <div class="sa-industry-application__body">
                <h3><?php echo esc_html((string) $item['title']); ?></h3>
                <?php if ($item['text'] !== '') : ?><p><?php echo esc_html((string) $item['text']); ?></p><?php endif; ?>
                <?php if ($item['products']) : ?>
                  <div class="sa-industry-application__families">
                    <span><?php esc_html_e('Relevant spring families:', 'springapex'); ?></span>
                    <div>
                      <?php foreach ($item['products'] as $product) : ?>
                        <a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($recommended) : ?>
    <section class="sa-industry-section sa-industry-products" id="recommended-products">
      <div class="container container-wide">
        <p class="section-kicker"><?php esc_html_e('RECOMMENDED SPRING FAMILIES', 'springapex'); ?></p>
        <div class="sa-industry-products__grid sa-industry-products__grid--count-<?php echo esc_attr((string) count($recommended)); ?>">
          <?php foreach ($recommended as $product) : ?>
            <article class="sa-industry-product">
              <a class="sa-industry-product__media" href="<?php echo esc_url(springapex_product_url($product)); ?>">
                <?php echo springapex_image($product['featured_image'] ?? $product['category_image'] ?? $product['image'] ?? '', (string) ($product['title'] ?? ''), [
                    'width' => 900,
                    'height' => 720,
                    'sizes' => '(max-width: 760px) 50vw, 25vw',
                ]); ?>
              </a>
              <h3><a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a></h3>
              <?php if (!empty($product['desc'])) : ?><p><?php echo esc_html((string) $product['desc']); ?></p><?php endif; ?>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($program_steps) : ?>
    <section class="sa-industry-section sa-industry-program">
      <div class="container container-wide">
        <p class="section-kicker"><?php esc_html_e('FROM REQUIREMENT TO RELEASE', 'springapex'); ?></p>
        <div class="sa-industry-program__list sa-industry-program__list--count-<?php echo esc_attr((string) count($program_steps)); ?>">
          <?php foreach ($program_steps as $index => $step) : ?>
            <article class="sa-industry-step">
              <?php if ($step['image'] !== '' || $step['image_id'] > 0) : ?>
                <figure class="sa-industry-step__media">
                  <?php echo springapex_image(['id' => $step['image_id'], 'file' => $step['image']], (string) $step['title'], [
                      'width' => 720,
                      'height' => 420,
                      'sizes' => '(max-width: 860px) 100vw, 25vw',
                  ]); ?>
                </figure>
              <?php endif; ?>
              <div class="sa-industry-step__copy">
                <span class="sa-industry-step__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span>
                <h3><?php echo esc_html((string) $step['title']); ?></h3>
                <?php if ($step['text'] !== '') : ?><p><?php echo esc_html((string) $step['text']); ?></p><?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($quality_items || $has_quality_image) : ?>
    <section class="sa-industry-section sa-industry-quality">
      <div class="container container-wide">
        <p class="section-kicker"><?php esc_html_e('QUALITY EVIDENCE', 'springapex'); ?></p>
        <div class="sa-industry-quality__grid<?php echo $has_quality_image ? '' : ' sa-industry-quality__grid--single'; ?>">
          <?php if ($has_quality_image) : ?>
            <figure class="sa-industry-quality__media">
              <?php echo springapex_image(['id' => $quality_image_id, 'file' => $quality_image], __('Spring inspection and validation', 'springapex'), [
                  'width' => 1200,
                  'height' => 720,
                  'sizes' => '(max-width: 860px) 100vw, 46vw',
              ]); ?>
            </figure>
          <?php endif; ?>
          <?php if ($quality_items) : ?>
            <div class="sa-industry-quality__list">
              <?php foreach ($quality_items as $item) : ?>
                <article class="sa-industry-quality__item">
                  <span><?php echo springapex_icon((string) $item['icon'], 'icon'); ?></span>
                  <div>
                    <h3><?php echo esc_html((string) $item['title']); ?></h3>
                    <?php if ($item['text'] !== '') : ?><p><?php echo esc_html((string) $item['text']); ?></p><?php endif; ?>
                  </div>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>
  <?php endif; ?>

  <section class="sa-industry-cta">
    <div class="container container-wide sa-industry-cta__inner">
      <div>
        <h2><?php esc_html_e('Bring the application. We will define the spring program.', 'springapex'); ?></h2>
        <p><?php esc_html_e('Share your drawing, load, environment, volume and required records.', 'springapex'); ?></p>
      </div>
      <div class="sa-industry-cta__actions">
        <a class="btn btn-primary" href="<?php echo esc_url($contact_url); ?>">
          <?php esc_html_e('Start an Industry Inquiry', 'springapex'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
        <a class="btn btn-outline" href="<?php echo esc_url($drawing_url); ?>">
          <?php echo springapex_icon('upload', 'icon icon-sm'); ?>
          <?php esc_html_e('Upload a Drawing', 'springapex'); ?>
        </a>
      </div>
    </div>
  </section>
</article>
