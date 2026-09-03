<?php
/**
 * NorenSpring theme bootstrap.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

define('SPRINGAPEX_VERSION', '2.9.109');
define('SPRINGAPEX_DIR', get_template_directory());
$springapex_uri = get_template_directory_uri();
if (defined('SPRINGAPEX_CDN_URL') && is_string(SPRINGAPEX_CDN_URL) && SPRINGAPEX_CDN_URL !== '') {
    $springapex_uri = rtrim(SPRINGAPEX_CDN_URL, '/') . '/theme/' . SPRINGAPEX_VERSION;
}
define('SPRINGAPEX_URI', $springapex_uri);
unset($springapex_uri);

require_once SPRINGAPEX_DIR . '/inc/content.php';
require_once SPRINGAPEX_DIR . '/inc/content-overrides.php';
require_once SPRINGAPEX_DIR . '/inc/helpers.php';
require_once SPRINGAPEX_DIR . '/inc/locks.php';
require_once SPRINGAPEX_DIR . '/inc/post-types.php';
require_once SPRINGAPEX_DIR . '/inc/product-picker.php';
require_once SPRINGAPEX_DIR . '/inc/admin/product-panel.php';
require_once SPRINGAPEX_DIR . '/inc/admin/solution-panel.php';
require_once SPRINGAPEX_DIR . '/inc/admin/case-panel.php';
require_once SPRINGAPEX_DIR . '/inc/admin/inquiry-view.php';
require_once SPRINGAPEX_DIR . '/inc/admin/form-settings.php';
require_once SPRINGAPEX_DIR . '/inc/admin/row-editor.php';
require_once SPRINGAPEX_DIR . '/inc/solution-meta.php';
require_once SPRINGAPEX_DIR . '/inc/news-meta.php';
require_once SPRINGAPEX_DIR . '/inc/form-schema.php';
require_once SPRINGAPEX_DIR . '/inc/s3-storage.php';
require_once SPRINGAPEX_DIR . '/inc/system-status.php';
require_once SPRINGAPEX_DIR . '/inc/seo.php';
require_once SPRINGAPEX_DIR . '/inc/analytics.php';
require_once SPRINGAPEX_DIR . '/inc/contact.php';
require_once SPRINGAPEX_DIR . '/inc/mail-template.php';
require_once SPRINGAPEX_DIR . '/inc/turnstile.php';
require_once SPRINGAPEX_DIR . '/inc/hardening.php';
require_once SPRINGAPEX_DIR . '/inc/seed.php';
require_once SPRINGAPEX_DIR . '/inc/setup.php';

// Signposts also run on the front end (admin bar), so they load outside the
// is_admin() branch; admin.php requires them again harmlessly.
require_once SPRINGAPEX_DIR . '/inc/admin/signposts.php';

if (is_admin()) {
    require_once SPRINGAPEX_DIR . '/inc/admin/admin.php';
}
