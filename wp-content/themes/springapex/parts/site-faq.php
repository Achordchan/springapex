<?php
if (!defined('ABSPATH')) {
    exit;
}

get_template_part('parts/home-faq', null, is_array($args ?? null) ? $args : []);
