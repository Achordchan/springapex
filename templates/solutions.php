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
    'image' => $hero['image'] ?? 'solutions-hero-v2.png',
    'mobile_image' => $hero['mobile_image'] ?? 'solutions-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
]);

get_template_part('parts/solutions-subnav', null, ['active' => 'industries']);
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
