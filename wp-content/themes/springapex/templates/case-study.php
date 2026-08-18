<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$case = null;
if (!defined('SPRINGAPEX_PREVIEW') && function_exists('is_singular') && is_singular('spring_case')) {
    $post_id = (int) get_queried_object_id();
    $slug = (string) get_post_field('post_name', $post_id);
    $case = springapex_case($slug);
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW')) {
    $slug = (string) get_query_var('case_slug', '');
    $case = springapex_case($slug);
}
if (!$case) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('Case study not found', 'springapex') . '</h1></div></section>';
    return;
}

$case_image = $case['image'] ?? 'solutions-hero-v2.png';
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solution-detail',
    'title' => (string) ($case['title'] ?? ''),
    'subtitle' => (string) ($case['tagline'] ?? ''),
    'image' => $case_image,
    'image_width' => 1600,
    'image_height' => 900,
    'breadcrumb' => [
        ['label' => 'Home', 'href' => '/'],
        ['label' => 'Case Studies', 'href' => '/case-studies/'],
        ['label' => (string) ($case['title'] ?? '')],
    ],
]);

get_template_part('parts/solutions-subnav', null, ['active' => 'case-studies']);
?>

<section class="section sa-case-study-detail">
  <div class="container container-narrow">
    <article class="sa-case-study-detail__content">
      <?php echo wp_kses_post((string) ($case['content'] ?? '')); ?>
    </article>
  </div>
</section>

<?php
get_template_part('parts/cta-band', null, [
    'title' => 'Discuss a similar application.',
    'text' => 'Share the drawing, load conditions and expected production volume.',
    'cta' => ['label' => 'Start Your Inquiry', 'href' => '/contact/?intent=solution'],
    'class' => 'sa-solution-cta',
]);
?>
