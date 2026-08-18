<?php
get_header();
?>
<?php while (have_posts()) : the_post(); ?>
  <?php
  get_template_part('parts/inner-hero', null, [
      'variant' => 'page',
      'title' => get_the_title(),
      'subtitle' => get_the_excerpt(),
      'image' => has_post_thumbnail() ? ['id' => (int) get_post_thumbnail_id(), 'file' => ''] : 'products-blueprint-v3.png',
  ]);
  ?>
  <section class="section generic-page">
    <div class="container content-narrow">
      <div class="entry-content"><?php the_content(); ?></div>
    </div>
  </section>
<?php endwhile; ?>
<?php
get_footer();
