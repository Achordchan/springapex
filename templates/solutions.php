<?php
if (!defined('ABSPATH')) {
    exit;
}
$hero = springapex_get('solutions.hero', []);
$solutions = springapex_solutions();
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solutions',
    'title' => $hero['title'] ?? 'Solutions',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'solutions-hero-v3.png',
    'mobile_image' => 'solutions-hero-v2.png',
    'image_width' => 3840,
    'image_height' => 480,
    'ctas' => [[
        'label' => 'Explore Industries',
        'href' => '#solutions-grid',
        'icon' => 'arrow-right',
    ]],
]);
?>

<section class="section solutions-grid-section" id="solutions-grid">
  <div class="container container-wide">
    <div class="solutions-grid" data-reveal-group>
      <?php foreach ($solutions as $solution) : ?>
        <a class="solution-card" id="<?php echo esc_attr((string) $solution['slug']); ?>" href="<?php echo esc_url(springapex_solution_url($solution)); ?>">
          <span class="solution-media">
            <?php echo springapex_image($solution['image'] ?? '', (string) $solution['title'], [
                'width' => 1200,
                'height' => 900,
                'sizes' => '(max-width: 700px) 100vw, (max-width: 980px) 50vw, 33vw',
            ]); ?>
          </span>
          <span class="solution-meta">
            <span>
              <strong><?php echo esc_html((string) $solution['title']); ?></strong>
              <small><?php echo esc_html((string) ($solution['tagline'] ?? '')); ?></small>
            </span>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section sa-solution-method">
  <div class="container container-wide sa-solution-method__layout">
    <div class="sa-section-intro" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('ENGINEERING METHOD', 'springapex'); ?></p>
      <h2><?php esc_html_e('Every industry page follows the same technical chain.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('We connect the application challenge to the spring family, material and process choices, then define how performance will be verified.', 'springapex'); ?></p>
    </div>
    <ol class="sa-solution-method__steps" data-reveal-group>
      <li><span>01</span><strong><?php esc_html_e('Application challenge', 'springapex'); ?></strong></li>
      <li><span>02</span><strong><?php esc_html_e('Recommended spring', 'springapex'); ?></strong></li>
      <li><span>03</span><strong><?php esc_html_e('Material and process', 'springapex'); ?></strong></li>
      <li><span>04</span><strong><?php esc_html_e('Validation plan', 'springapex'); ?></strong></li>
    </ol>
  </div>
</section>

<section class="section bottom-cta-panel solutions-cta">
  <div class="container container-wide bottom-cta-inner" data-reveal="up">
    <div class="bottom-cta-copy">
      <h2><?php esc_html_e('Have a specific application in mind?', 'springapex'); ?></h2>
      <p><?php esc_html_e("We'll help you find the right spring solution for your needs.", 'springapex'); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=engineer')); ?>">
        <?php esc_html_e('Contact Our Engineers', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="bottom-cta-media">
      <?php echo springapex_image('solutions-cta-springs-v5.png', __('Assorted precision springs', 'springapex'), [
          'width' => 1600,
          'height' => 560,
          'sizes' => '(max-width: 760px) 100vw, 55vw',
      ]); ?>
    </div>
  </div>
</section>
