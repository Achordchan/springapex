<?php
if (!defined('ABSPATH')) {
    exit;
}
$capabilities = springapex_get('capabilities', []);
$hero = $capabilities['hero'] ?? [];
$quality = springapex_get('company.quality', []);
$standards = is_array($quality['standards'] ?? null) ? $quality['standards'] : [];
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'capabilities',
    'title' => $hero['title'] ?? 'Capabilities',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'generated/springapex-capabilities-hero-v2.webp',
    'mobile_image' => 'generated/springapex-capabilities-hero-v2.webp',
    'image_width' => 1890,
    'image_height' => 830,
    'ctas' => [[
        'label' => $hero['cta']['label'] ?? 'Upload Your Drawing',
        'href' => $hero['cta']['href'] ?? '/contact/?intent=drawing',
        'icon' => $hero['cta']['icon'] ?? 'upload',
    ]],
]);
?>

<?php get_template_part('parts/capabilities-overview', null, ['capabilities' => $capabilities]); ?>

<?php get_template_part('parts/manufacturing-process'); ?>

<section class="section sa-capability-metrics">
  <div class="container container-wide sa-capability-metrics__layout">
    <div class="sa-section-intro" data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('MANUFACTURING RANGE', 'springapex'); ?></p>
      <h2><?php esc_html_e('Capability from fine wire to heavy-duty applications.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('Final feasibility depends on geometry, material, tooling, load and inspection requirements. The figures below describe the reported company-wide production range.', 'springapex'); ?></p>
    </div>
    <div class="sa-capability-metrics__grid" data-reveal-group>
      <?php foreach (array_slice(springapex_get('company.facts', []), 1) as $fact) : ?>
        <article>
          <span><?php echo springapex_icon((string) ($fact['icon'] ?? 'factory')); ?></span>
          <strong><?php echo esc_html((string) ($fact['value'] ?? '')); ?></strong>
          <h3><?php echo esc_html((string) ($fact['label'] ?? '')); ?></h3>
          <p><?php echo esc_html((string) ($fact['detail'] ?? '')); ?></p>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php get_template_part('parts/quality-evidence', null, ['title' => __('What we inspect and verify before release.', 'springapex')]); ?>

<section class="section sa-capability-lab">
  <div class="container container-wide sa-capability-lab__grid">
    <figure data-reveal="up">
      <?php echo springapex_image('generated/springapex-quality-lab-v1.webp', __('Representative spring inspection setup', 'springapex'), [
          'width' => 710,
          'height' => 550,
          'sizes' => '(max-width: 760px) 100vw, 46vw',
      ]); ?>
    </figure>
    <div data-reveal="up">
      <p class="section-kicker"><?php esc_html_e('INSPECTION & DOCUMENTATION', 'springapex'); ?></p>
      <h2><?php esc_html_e('Define the evidence before the first production run.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('Inspection reports, material documents, traceability and application-specific validation should be agreed during quotation, not added after production.', 'springapex'); ?></p>
      <ul class="sa-check-list">
        <li><?php echo springapex_icon('check-shield'); ?><span><?php esc_html_e('Critical dimension and load checkpoints', 'springapex'); ?></span></li>
        <li><?php echo springapex_icon('check-shield'); ?><span><?php esc_html_e('Sampling and report format', 'springapex'); ?></span></li>
        <li><?php echo springapex_icon('check-shield'); ?><span><?php esc_html_e('Material, finishing and batch traceability', 'springapex'); ?></span></li>
        <li><?php echo springapex_icon('check-shield'); ?><span><?php esc_html_e('Fatigue or environmental validation where required', 'springapex'); ?></span></li>
      </ul>
      <?php if ($standards) : ?>
        <div class="sa-capability-standards">
          <p class="section-kicker"><?php esc_html_e('REPORTED QUALITY SYSTEMS', 'springapex'); ?></p>
          <ul>
            <?php foreach ($standards as $standard) : ?>
              <li>
                <?php echo springapex_icon('check-shield'); ?>
                <span><strong><?php echo esc_html((string) ($standard['name'] ?? '')); ?></strong><small><?php echo esc_html((string) ($standard['scope'] ?? '')); ?></small></span>
              </li>
            <?php endforeach; ?>
          </ul>
          <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/?intent=quality')); ?>">
            <?php esc_html_e('Request qualification documents', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php
get_template_part('parts/cta-band', null, [
    'title' => "Bring Us Your Most\nDemanding Application.",
    'text' => 'Share your drawing, load requirements and operating conditions with our engineering team.',
    'cta' => ['label' => 'Start Your Project', 'href' => '/contact/?intent=quote'],
]);
?>
