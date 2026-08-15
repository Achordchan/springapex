<?php
if (!defined('ABSPATH')) {
    exit;
}
$capabilities = springapex_get('capabilities', []);
$hero = $capabilities['hero'] ?? [];
$project_brief = is_array($capabilities['project_brief'] ?? null) ? $capabilities['project_brief'] : [];
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'capabilities',
    'title' => $hero['title'] ?? 'Capabilities',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'generated/springapex-capabilities-hero-v2.webp',
    'mobile_image' => $hero['mobile_image'] ?? 'capabilities-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
    'ctas' => [[
        'label' => $hero['cta']['label'] ?? 'Upload Your Drawing',
        'href' => $hero['cta']['href'] ?? '/contact/?intent=drawing',
        'icon' => $hero['cta']['icon'] ?? 'upload',
    ]],
]);
?>

<?php get_template_part('parts/capabilities-subnav'); ?>

<?php get_template_part('parts/capabilities-overview', null, ['capabilities' => $capabilities]); ?>

<?php get_template_part('parts/manufacturing-process'); ?>

<?php get_template_part('parts/quality-evidence', null, ['title' => __('Verification matched to your drawing and application.', 'springapex')]); ?>

<?php if ($project_brief) : ?>
<section class="section sa-custom-brief sa-custom-brief--closing">
  <div class="container container-wide sa-custom-brief__grid">
    <figure class="sa-custom-brief__media" data-reveal="up">
      <?php echo springapex_image((string) ($project_brief['image'] ?? ''), __('Spring engineering review workspace', 'springapex'), [
          'width' => 960,
          'height' => 720,
          'sizes' => '(max-width: 760px) 100vw, 44vw',
      ]); ?>
    </figure>
    <div class="sa-custom-brief__content" data-reveal="up">
      <p class="section-kicker"><?php echo esc_html((string) ($project_brief['eyebrow'] ?? 'PROJECT INPUTS')); ?></p>
      <h2><?php echo esc_html((string) ($project_brief['title'] ?? '')); ?></h2>
      <p class="sa-section-lede"><?php echo esc_html((string) ($project_brief['text'] ?? '')); ?></p>
      <div class="sa-custom-brief__items" data-reveal-group>
        <?php foreach (($project_brief['items'] ?? []) as $item) : ?>
          <article>
            <span><?php echo springapex_icon((string) ($item['icon'] ?? 'form')); ?></span>
            <div>
              <h3><?php echo esc_html((string) ($item['title'] ?? '')); ?></h3>
              <p><?php echo esc_html((string) ($item['text'] ?? '')); ?></p>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
      <a class="btn btn-primary sa-custom-brief__action" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
        <?php echo springapex_icon('upload', 'icon icon-sm'); ?>
        <?php esc_html_e('Send Your Project Details', 'springapex'); ?>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>
