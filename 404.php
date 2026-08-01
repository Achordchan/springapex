<?php
get_header();
?>
<section class="section empty-page">
  <div class="container content-narrow">
    <p class="section-kicker">404</p>
    <h1 class="display-sm"><?php esc_html_e('Page not found', 'springapex'); ?></h1>
    <p><?php esc_html_e('The requested page is unavailable. Return to the product catalog or contact our engineering team.', 'springapex'); ?></p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/products/')); ?>"><?php esc_html_e('View Products', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></a>
      <a class="btn btn-outline" href="<?php echo esc_url(springapex_url('/contact/')); ?>"><?php esc_html_e('Contact Us', 'springapex'); ?></a>
    </div>
  </div>
</section>
<?php
get_footer();
