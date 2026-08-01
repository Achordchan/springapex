<?php
/**
 * SpringApex theme bootstrap.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SPRINGAPEX_VERSION', '2.5.5');
define('SPRINGAPEX_DIR', get_template_directory());
define('SPRINGAPEX_URI', get_template_directory_uri());

require_once SPRINGAPEX_DIR . '/inc/content.php';
require_once SPRINGAPEX_DIR . '/inc/helpers.php';
require_once SPRINGAPEX_DIR . '/inc/locks.php';
require_once SPRINGAPEX_DIR . '/inc/post-types.php';
require_once SPRINGAPEX_DIR . '/inc/customizer.php';
require_once SPRINGAPEX_DIR . '/inc/contact.php';
require_once SPRINGAPEX_DIR . '/inc/seed.php';
require_once SPRINGAPEX_DIR . '/inc/setup.php';
