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

$recommended = [];
foreach (($solution['products'] ?? []) as $product_slug) {
    $product = springapex_product((string) $product_slug);
    if ($product) {
        $recommended[] = $product;
    }
}
$hero_cta = $recommended
    ? ['label' => 'View Recommended Springs', 'href' => '#recommended-products', 'icon' => 'arrow-right']
    : ['label' => 'Discuss This Application', 'href' => '/contact/?intent=solution&industry=' . $slug, 'icon' => 'arrow-right'];
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solution-detail',
    'title' => (string) ($solution['title'] ?? ''),
    'subtitle' => (string) ($solution['challenge_intro'] ?? $solution['tagline'] ?? ''),
    'image' => $solution['image'] ?? 'solutions-hero-v3.png',
    'image_width' => 1600,
    'image_height' => 900,
    'breadcrumb' => [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Solutions', 'href' => '/solutions/'],
        ['label' => (string) ($solution['title'] ?? '')],
    ],
    'ctas' => [$hero_cta],
]);
?>

<section class="section sa-solution-detail">
  <div class="container container-wide sa-solution-detail__grid">
    <div class="sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('THE CHALLENGE', 'springapex'); ?></p>
      <h2><?php echo esc_html((string) ($solution['title'] ?? '') . ' demands specific spring performance.'); ?></h2>
      <p class="sa-section-bridge"><?php esc_html_e('Every spring in this sector must meet these operating conditions.', 'springapex'); ?></p>
    </div>
    <ul class="sa-check-list">
      <?php foreach (($solution['challenges'] ?? []) as $challenge) : ?>
        <li><?php echo springapex_icon('check-shield'); ?><span><?php echo esc_html((string) $challenge); ?></span></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>

<?php if ($recommended) : ?>
<section class="section sa-related-products" id="recommended-products">
  <div class="container container-wide">
    <div class="section-head sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('RECOMMENDED SPRINGS', 'springapex'); ?></p>
      <h2><?php esc_html_e('Product families proven in this industry.', 'springapex'); ?></h2>
      <p class="sa-section-bridge"><?php esc_html_e('These spring types address the conditions above. Select one to see specifications.', 'springapex'); ?></p>
    </div>
    <div class="sa-product-grid">
      <?php foreach ($recommended as $product) : ?>
        <?php get_template_part('parts/product-card', null, ['product' => $product]); ?>
      <?php endforeach; ?>
    </div>
    <div class="sa-solution-mid-cta">
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=solution&industry=' . $slug)); ?>">
        <?php esc_html_e('Discuss Your Application', 'springapex'); ?>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section sa-solution-detail sa-solution-detail--split">
  <div class="container container-wide sa-solution-detail__columns">
    <article>
      <p class="section-kicker"><?php esc_html_e('HOW WE BUILD IT', 'springapex'); ?></p>
      <h2><?php esc_html_e('Material and process choices matched to service life.', 'springapex'); ?></h2>
      <ul class="sa-number-list">
        <?php foreach (($solution['processes'] ?? []) as $index => $process) : ?><li><span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span><?php echo esc_html((string) $process); ?></li><?php endforeach; ?>
      </ul>
    </article>
    <article>
      <p class="section-kicker"><?php esc_html_e('HOW WE PROVE IT', 'springapex'); ?></p>
      <h2><?php esc_html_e('Inspection and documentation defined before production.', 'springapex'); ?></h2>
      <ul class="sa-number-list">
        <?php foreach (($solution['validation'] ?? []) as $index => $validation) : ?><li><span><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span><?php echo esc_html((string) $validation); ?></li><?php endforeach; ?>
      </ul>
    </article>
  </div>
</section>

<section class="section sa-application-examples">
  <div class="container container-wide sa-application-examples__layout">
    <div class="sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('TYPICAL APPLICATIONS', 'springapex'); ?></p>
      <h2><?php esc_html_e('Where these spring solutions are commonly applied.', 'springapex'); ?></h2>
    </div>
    <div class="sa-application-examples__grid">
      <?php foreach (($solution['applications'] ?? []) as $application) : ?><span><?php echo esc_html((string) $application); ?></span><?php endforeach; ?>
    </div>
  </div>
</section>

<?php
get_template_part('parts/cta-band', null, [
    'title' => 'Ready to move forward?',
    'text' => 'Share your load, environment and volume requirements. Our engineers will respond with material recommendations and a quotation within 24 hours.',
    'cta' => ['label' => 'Start Your Inquiry', 'href' => '/contact/?intent=solution&industry=' . $slug],
]);
?>
