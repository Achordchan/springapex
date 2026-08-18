<?php
if (!defined('ABSPATH')) {
    exit;
}
?><!DOCTYPE html>
<html class="no-js" <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#ffffff">
  <script>document.documentElement.classList.remove('no-js');document.documentElement.classList.add('js');</script>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php get_template_part('parts/site-header'); ?>
<main id="main" class="site-main">
