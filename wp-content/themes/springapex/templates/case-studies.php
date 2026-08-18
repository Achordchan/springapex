<?php
if (!defined('ABSPATH')) {
    exit;
}

$hero = springapex_get('case_studies.hero', []);
$cases = springapex_cases();
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solutions',
    'title' => $hero['title'] ?? 'Case Studies',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'solutions-hero-v2.png',
    'mobile_image' => $hero['mobile_image'] ?? 'solutions-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
]);

get_template_part('parts/solutions-subnav', null, ['active' => 'case-studies']);
?>

<section class="section solutions-grid-section sa-case-studies" id="case-studies-grid">
  <div class="container container-wide">
    <?php if ($cases) : ?>
      <div class="solutions-grid" data-reveal-group>
        <?php foreach ($cases as $case) : ?>
          <?php
          $case_image = $case['image'] ?? '';
          $has_case_image = is_array($case_image)
              ? !empty($case_image['id']) || !empty($case_image['file'])
              : $case_image !== '';
          ?>
          <a class="solution-card sa-case-card<?php echo $has_case_image ? '' : ' has-no-image'; ?>" href="<?php echo esc_url(springapex_case_url($case)); ?>">
            <span class="solution-media">
              <?php if ($has_case_image) : ?>
                <?php echo springapex_image($case_image, (string) ($case['title'] ?? ''), [
                    'width' => 1200,
                    'height' => 900,
                    'sizes' => '(max-width: 700px) 100vw, (max-width: 980px) 50vw, 33vw',
                ]); ?>
              <?php else : ?>
                <span class="sa-case-card__placeholder"><?php echo springapex_icon('form'); ?><small><?php esc_html_e('Case Study', 'springapex'); ?></small></span>
              <?php endif; ?>
            </span>
            <span class="solution-meta">
              <span>
                <strong><?php echo esc_html((string) ($case['title'] ?? '')); ?></strong>
                <small><?php echo esc_html((string) ($case['tagline'] ?? '')); ?></small>
              </span>
              <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
            </span>
          </a>
        <?php endforeach; ?>
      </div>
    <?php else : ?>
      <div class="sa-case-studies__empty">
        <?php echo springapex_icon('form'); ?>
        <h2><?php esc_html_e('No approved case studies are published yet.', 'springapex'); ?></h2>
        <p><?php esc_html_e('Customer project evidence will appear here after publication approval.', 'springapex'); ?></p>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
get_template_part('parts/cta-band', null, [
    'title' => 'Have a project with similar requirements?',
    'text' => 'Send the application conditions or drawing for an engineering review.',
    'cta' => ['label' => 'Discuss Your Project', 'href' => '/contact/?intent=solution'],
    'class' => 'sa-solution-cta',
]);
?>
