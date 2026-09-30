<?php
/**
 * REST access to the 网站内容 screens.
 *
 * Application passwords only authenticate REST requests, and the website
 * content lives in the springapex_content_overrides option, which core's
 * /wp/v2/settings does not expose. These routes let an authorised client read
 * and edit a screen with exactly the same schema, sanitizers and
 * compare-and-swap write as the wp-admin form (springapex_admin_save_screen_content()).
 *
 *   GET  /wp-json/springapex/v1/content              screens and their field paths
 *   GET  /wp-json/springapex/v1/content/<screen>     current values of one screen
 *   POST /wp-json/springapex/v1/content/<screen>     {"content": {...}} partial update,
 *                                                    returns {saved, warnings}
 *
 * Nested objects merge into what is stored; lists (repeaters, lines) replace
 * as a whole, so send the full list when changing one row.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', 'springapex_rest_content_routes');

function springapex_rest_content_load_admin(): void
{
    // REST requests are not is_admin(), so functions.php has not loaded these.
    require_once SPRINGAPEX_DIR . '/inc/admin/signposts.php';
    require_once SPRINGAPEX_DIR . '/inc/admin/schema.php';
    require_once SPRINGAPEX_DIR . '/inc/admin/sanitize.php';
    require_once SPRINGAPEX_DIR . '/inc/admin/save.php';
}

function springapex_rest_content_routes(): void
{
    $permission = static fn(): bool => current_user_can(SPRINGAPEX_ADMIN_CAP);

    register_rest_route('springapex/v1', '/content', [
        'methods' => WP_REST_Server::READABLE,
        'callback' => 'springapex_rest_content_index',
        'permission_callback' => $permission,
    ]);

    register_rest_route('springapex/v1', '/content/(?P<screen>[a-z0-9_-]+)', [
        [
            'methods' => WP_REST_Server::READABLE,
            'callback' => 'springapex_rest_content_get',
            'permission_callback' => $permission,
        ],
        [
            'methods' => WP_REST_Server::EDITABLE,
            'callback' => 'springapex_rest_content_update',
            'permission_callback' => $permission,
            'args' => [
                'content' => [
                    'required' => true,
                    'type' => 'object',
                ],
            ],
        ],
    ]);
}

/**
 * @return array<int, string>
 */
function springapex_rest_content_field_paths(array $screen): array
{
    $paths = [];
    foreach ((array) ($screen['sections'] ?? []) as $section) {
        foreach ((array) ($section['fields'] ?? []) as $field) {
            $path = (string) ($field['path'] ?? '');
            if ($path !== '') {
                $paths[] = $path;
            }
        }
    }
    return $paths;
}

function springapex_rest_content_index(): WP_REST_Response
{
    springapex_rest_content_load_admin();

    $screens = [];
    foreach (springapex_admin_screens() as $key => $screen) {
        $screens[] = [
            'screen' => (string) $key,
            'label' => (string) ($screen['label'] ?? $key),
            'preview' => (string) ($screen['preview'] ?? ''),
            'fields' => springapex_rest_content_field_paths($screen),
        ];
    }
    return new WP_REST_Response(['screens' => $screens]);
}

/**
 * Current values of one screen, nested the same way a POST expects them.
 */
function springapex_rest_content_values(string $screen_key): array
{
    $screen = springapex_admin_screens()[$screen_key];
    $content = springapex_content();
    $values = [];

    foreach (springapex_rest_content_field_paths($screen) as $path) {
        $parts = explode('.', $path);
        $source = $content;
        $found = true;
        foreach ($parts as $part) {
            if (!is_array($source) || !array_key_exists($part, $source)) {
                $found = false;
                break;
            }
            $source = $source[$part];
        }
        if (!$found) {
            continue;
        }

        $node = &$values;
        foreach ($parts as $part) {
            $node[$part] ??= [];
            $node = &$node[$part];
        }
        $node = $source;
        unset($node);
    }

    return $values;
}

function springapex_rest_content_screen(WP_REST_Request $request): string|WP_Error
{
    springapex_rest_content_load_admin();

    $screen = sanitize_key((string) $request['screen']);
    if ($screen === '' || !isset(springapex_admin_screens()[$screen])) {
        return new WP_Error('springapex_unknown_screen', 'Unknown content screen.', ['status' => 404]);
    }
    return $screen;
}

function springapex_rest_content_get(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $screen = springapex_rest_content_screen($request);
    if ($screen instanceof WP_Error) {
        return $screen;
    }

    return new WP_REST_Response([
        'screen' => $screen,
        'content' => springapex_rest_content_values($screen),
    ]);
}

function springapex_rest_content_update(WP_REST_Request $request): WP_REST_Response|WP_Error
{
    $screen = springapex_rest_content_screen($request);
    if ($screen instanceof WP_Error) {
        return $screen;
    }

    $raw = $request->get_param('content');
    if (!is_array($raw)) {
        return new WP_Error('springapex_invalid_content', 'content must be an object.', ['status' => 400]);
    }

    $warnings = [];
    $saved = springapex_admin_save_screen_content($screen, $raw, $warnings);

    // springapex_content() is memoised per request, so the saved values are not
    // echoed back here; GET the screen again to read them.
    return new WP_REST_Response([
        'screen' => $screen,
        'saved' => $saved,
        'warnings' => array_values($warnings),
    ], $saved ? 200 : 400);
}
