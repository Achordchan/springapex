<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
</main>
<?php $footer_route = function_exists('springapex_current_route') ? springapex_current_route() : ''; ?>
<?php
$footer_product_slug = '';
if ($footer_route === 'product') {
    if (defined('SPRINGAPEX_PREVIEW') && function_exists('get_query_var')) {
        $footer_product_slug = sanitize_key((string) get_query_var('product_slug'));
    } elseif (function_exists('get_queried_object_id') && function_exists('get_post_field')) {
        $footer_product_slug = sanitize_key((string) get_post_field('post_name', (int) get_queried_object_id()));
    }
}
$hide_support_widget = $footer_route === 'contact' || $footer_product_slug === 'compression-springs';
?>
<?php if (!in_array($footer_route, ['home', 'about', 'about-story', 'sustainability', 'resources', 'product', 'solutions', 'contact', 'case-studies', 'news', 'search', 'privacy', 'terms', 'sitemap', 'capabilities', 'manufacturing-videos'], true)) : ?>
  <?php get_template_part('parts/site-faq'); ?>
<?php endif; ?>
<?php if ($footer_route === 'products') : ?>
  <?php get_template_part('parts/products-resource-link'); ?>
<?php endif; ?>
<?php get_template_part('parts/site-footer'); ?>
<?php if (!$hide_support_widget) : ?>
  <?php get_template_part('parts/support-widget'); ?>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
