<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$case = null;
// The queried post itself, not a publish-only slug lookup, so a logged-in
// editor's draft / "Preview changes" autosave renders here too.
$case_post = springapex_singular_post_for_view('spring_case');
if ($case_post) {
    $slug = (string) $case_post->post_name;
    $case = springapex_case_from_post($case_post);
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

// Case images are labelled product collages; behind the hero copy they make
// the title unreadable, so the hero reuses the Case Studies listing artwork
// and light overlay like the other inner pages. The collage stays in the body.
$hero = springapex_get('case_studies.hero', []);
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solutions',
    'title' => (string) ($case['title'] ?? ''),
    'subtitle' => (string) ($case['tagline'] ?? ''),
    'image' => $hero['image'] ?? 'solutions-hero-v2.png',
    'mobile_image' => $hero['mobile_image'] ?? 'solutions-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
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
