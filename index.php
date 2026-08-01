<?php
get_header();
?>
<section class="section generic-page">
  <div class="container content-narrow">
    <?php if (have_posts()) : ?>
      <?php while (have_posts()) : the_post(); ?>
        <article <?php post_class('post-summary'); ?>>
          <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
          <?php the_excerpt(); ?>
        </article>
      <?php endwhile; ?>
      <?php the_posts_pagination(); ?>
    <?php else : ?>
      <h1 class="display-sm"><?php esc_html_e('No content found', 'springapex'); ?></h1>
    <?php endif; ?>
  </div>
</section>
<?php
get_footer();
