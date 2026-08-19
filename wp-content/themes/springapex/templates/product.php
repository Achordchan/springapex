<?php
if (!defined('ABSPATH')) {
    exit;
}

$slug = '';
$product = null;
if (!defined('SPRINGAPEX_PREVIEW') && function_exists('is_singular') && is_singular('spring_product')) {
    $post_id = (int) get_queried_object_id();
    $slug = (string) get_post_field('post_name', $post_id);
    if (function_exists('post_password_required') && post_password_required($post_id)) {
        echo '<section class="section"><div class="container">' . get_the_password_form($post_id) . '</div></section>';
        return;
    }
    $product = springapex_product_for_view($post_id);
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW') && function_exists('get_query_var')) {
    $slug = (string) get_query_var('product_slug');
}
if ($slug === '' && defined('SPRINGAPEX_PREVIEW')) {
    $slug = 'compression-springs';
}
if (!$product && $slug !== '') {
    $product = springapex_product($slug);
}

if (!$product) {
    status_header(404);
    echo '<section class="section"><div class="container"><h1>' . esc_html__('Product not found', 'springapex') . '</h1></div></section>';
    return;
}

// Every product shares the unified premium detail layout (originally built for
// compression springs), driven by each product's own editable fields — so the
// backend edits (小标题 / 技术参数 / 正文 / 产品图) always match the front end.
get_template_part('templates/product-compression', null, [
    'product' => $product,
    'slug' => $slug,
]);
