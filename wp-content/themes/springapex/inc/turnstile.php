<?php
/**
 * Cloudflare Turnstile integration.
 *
 * The site key is public and safe to ship in markup. The secret key is a
 * credential and must never live in the repo — it is read from the
 * SPRINGAPEX_TURNSTILE_SECRET constant (define it in wp-config.php, the same way
 * SPRINGAPEX_PRIVATE_UPLOADS_PROTECTED is handled) or, as a fallback, from the
 * springapex_turnstile_secret option.
 *
 * Verification is graceful: when no secret is configured the check is skipped so
 * forms keep working, and an admin notice reminds an administrator to configure
 * it. Once a secret is present the check is enforced (fail-closed).
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Public site key rendered into the widget markup. Overridable via constant for
 * staging keys; falls back to the production key.
 */
function springapex_turnstile_site_key(): string
{
    if (defined('SPRINGAPEX_TURNSTILE_SITE_KEY') && is_string(SPRINGAPEX_TURNSTILE_SITE_KEY) && SPRINGAPEX_TURNSTILE_SITE_KEY !== '') {
        return SPRINGAPEX_TURNSTILE_SITE_KEY;
    }

    return '0x4AAAAAAEUKf1Ep7E9Fafsj';
}

/**
 * Secret key for server-side verification. Constant wins; option is a fallback
 * so the key can also be set from the database without editing wp-config.php.
 */
function springapex_turnstile_secret(): string
{
    if (defined('SPRINGAPEX_TURNSTILE_SECRET') && is_string(SPRINGAPEX_TURNSTILE_SECRET)) {
        $secret = trim(SPRINGAPEX_TURNSTILE_SECRET);
        if ($secret !== '') {
            return $secret;
        }
    }

    if (!function_exists('get_option')) {
        return '';
    }

    $option = get_option('springapex_turnstile_secret', '');
    return is_string($option) ? trim($option) : '';
}

function springapex_turnstile_enabled(): bool
{
    return springapex_turnstile_secret() !== '';
}

/**
 * Notice shown in place of the widget for visitors without JavaScript.
 *
 * Turnstile is a JavaScript widget: with no JS it cannot render or mint the
 * cf-turnstile-response token, so a submission can never pass verification. When
 * verification is enabled we therefore tell no-JS visitors that JavaScript is
 * required and point them at direct contact, rather than leaving a form that
 * looks usable but always rejects. When verification is disabled the non-JS
 * admin-post.php path still works (honeypot, timing, rate limits), so nothing is
 * shown.
 */
function springapex_turnstile_noscript(): string
{
    if (!springapex_turnstile_enabled()) {
        return '';
    }

    return '<noscript><p class="sa-turnstile-noscript">'
        . esc_html__('JavaScript is required to submit this form. Please enable JavaScript, or contact us directly by email.', 'springapex')
        . '</p></noscript>';
}

/**
 * Widget actions we render. The server rejects a token whose action is not one
 * of these, so keep this in sync with the data-action attributes in the forms.
 */
function springapex_turnstile_actions(): array
{
    return ['contact-inquiry', 'product-inquiry', 'capability-inquiry', 'quick-inquiry'];
}

/**
 * Hostnames a token is allowed to originate from. Enforced only when the
 * SPRINGAPEX_TURNSTILE_HOSTNAMES constant (comma-separated) is defined; otherwise
 * the hostname is not re-checked here — Cloudflare already restricts the widget
 * to the domains configured in the dashboard.
 */
function springapex_turnstile_hostname_allowlist(): array
{
    if (!defined('SPRINGAPEX_TURNSTILE_HOSTNAMES') || !is_string(SPRINGAPEX_TURNSTILE_HOSTNAMES)) {
        return [];
    }

    $hosts = array_map('trim', explode(',', SPRINGAPEX_TURNSTILE_HOSTNAMES));
    return array_values(array_filter($hosts, static fn (string $host): bool => $host !== ''));
}

/**
 * Verify the Turnstile response token attached to the current request.
 *
 * @return true|WP_Error true when the check passes (or is intentionally skipped),
 *                       WP_Error otherwise.
 */
function springapex_verify_turnstile(): bool|WP_Error
{
    if (!springapex_turnstile_enabled()) {
        return true;
    }

    $token = springapex_request_scalar($_POST['cf-turnstile-response'] ?? '');
    if ($token === '' || strlen($token) > 2048) {
        return springapex_turnstile_error();
    }

    $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
        'timeout' => 10,
        'body' => [
            'secret' => springapex_turnstile_secret(),
            'response' => $token,
        ],
    ]);

    if (is_wp_error($response)) {
        return springapex_turnstile_error();
    }
    if ((int) wp_remote_retrieve_response_code($response) !== 200) {
        return springapex_turnstile_error();
    }

    $result = json_decode((string) wp_remote_retrieve_body($response), true);
    if (!is_array($result) || empty($result['success'])) {
        return springapex_turnstile_error();
    }

    $action = isset($result['action']) && is_string($result['action']) ? $result['action'] : '';
    if ($action !== '' && !in_array($action, springapex_turnstile_actions(), true)) {
        return springapex_turnstile_error();
    }

    $allowlist = springapex_turnstile_hostname_allowlist();
    if ($allowlist !== []) {
        $hostname = isset($result['hostname']) && is_string($result['hostname']) ? $result['hostname'] : '';
        if ($hostname === '' || !in_array($hostname, $allowlist, true)) {
            return springapex_turnstile_error();
        }
    }

    return true;
}

function springapex_turnstile_error(): WP_Error
{
    return springapex_contact_error(
        'springapex_turnstile',
        __('The anti-spam check could not be verified. Please try again.', 'springapex'),
        403
    );
}

/**
 * Nudge administrators to finish setup: the widget is visible to visitors but
 * submissions are not being verified until the secret is configured.
 */
add_action('admin_notices', static function (): void {
    if (!current_user_can('manage_options') || springapex_turnstile_enabled()) {
        return;
    }

    printf(
        '<div class="notice notice-warning"><p>%s</p></div>',
        esc_html__('ApexSpring: the Cloudflare Turnstile widget is shown on the contact forms, but submissions are NOT verified yet. Define SPRINGAPEX_TURNSTILE_SECRET in wp-config.php to activate anti-spam verification.', 'springapex')
    );
});
