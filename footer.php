<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</main>
<?php if (function_exists('springapex_current_route') && !in_array(springapex_current_route(), ['home', 'product', 'search'], true)) : ?>
  <?php get_template_part('parts/site-faq'); ?>
<?php endif; ?>
<?php get_template_part('parts/site-footer'); ?>
<?php get_template_part('parts/support-widget'); ?>
<?php wp_footer(); ?>
</body>
</html>
