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
 * staging keys; 「表单设置」页写入的 option 次之；falls back to the built-in key.
 */
function springapex_turnstile_site_key(): string
{
    if (defined('SPRINGAPEX_TURNSTILE_SITE_KEY') && is_string(SPRINGAPEX_TURNSTILE_SITE_KEY) && SPRINGAPEX_TURNSTILE_SITE_KEY !== '') {
        return SPRINGAPEX_TURNSTILE_SITE_KEY;
    }

    // 「表单设置」页（inc/admin/form-settings.php）写入的 option 优先于内置密钥。
    $option = get_option('springapex_turnstile_site_key', '');
    if (is_string($option) && $option !== '') {
        return $option;
    }

    return '0x4AAAAAAEaVC1_swOetjJ-b';
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
    return ['contact-inquiry', 'product-inquiry', 'capability-inquiry', 'quick-inquiry', 'wp-login'];
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
function springapex_verify_turnstile(string $form_key = ''): bool|WP_Error
{
    if (!springapex_turnstile_enabled()) {
        return true;
    }
    // 按表单开关：设置页对每个表单单独启停（渲染侧同源）。
    if ($form_key !== '' && !springapex_form_turnstile_enabled($form_key)) {
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

    // Fail-closed: every widget we render sets a data-action, so the verified
    // action must be present and in the allowlist. An absent/empty action is
    // treated as a mismatch and rejected.
    $action = isset($result['action']) && is_string($result['action']) ? $result['action'] : '';
    if (!in_array($action, springapex_turnstile_actions(), true)) {
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
        esc_html__('NorenSpring: the Cloudflare Turnstile widget is shown on the contact forms, but submissions are NOT verified yet. Define SPRINGAPEX_TURNSTILE_SECRET in wp-config.php to activate anti-spam verification.', 'springapex')
    );
});

/**
 * Login-page protection.
 *
 * 挂件只在 wp-login.php 渲染；authenticate 校验也只在 login_init 之后注册，
 * 因此 REST / 应用密码等非表单登录永远不受影响。未配置 secret 时全部跳过——
 * 与联系表单同一套优雅降级（既有 admin notice 会提醒补配置）。
 * 登录保护刻意不走「表单设置」的按表单开关（verify 时传空 key），避免被
 * 误关掉唯一的人机验证防线。
 */
add_action('login_enqueue_scripts', static function (): void {
    if (!springapex_turnstile_enabled()) {
        return;
    }

    wp_enqueue_script(
        'springapex-login-turnstile',
        SPRINGAPEX_URI . '/assets/js/login-turnstile.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
    wp_add_inline_script(
        'springapex-login-turnstile',
        'window.NorenSpringLogin={turnstileUrl:"https://challenges.cloudflare.com/turnstile/v0/api.js"};',
        'before'
    );
});

add_action('login_head', static function (): void {
    if (!springapex_turnstile_enabled()) {
        return;
    }

    echo '<style>.sa-login-turnstile{margin:16px 0 4px}</style>';
});

add_action('login_form', static function (): void {
    if (!springapex_turnstile_enabled()) {
        return;
    }
    ?>
    <div class="sa-login-turnstile">
      <div
        class="cf-turnstile"
        data-sitekey="<?php echo esc_attr(springapex_turnstile_site_key()); ?>"
        data-theme="light"
        data-language="en"
        data-action="wp-login"
      ></div>
      <?php echo springapex_turnstile_noscript(); ?>
    </div>
    <?php
});

add_action('login_init', static function (): void {
    if (!springapex_turnstile_enabled()) {
        return;
    }

    // 只处理真实的登录提交（含 interim login 的 iframe 登录）。首次 GET 打开
    // 登录页、找回密码等其他动作不注册任何过滤——否则 GET 路径上的 wp_signon()
    // 会产生空字段错误并被这里的统一拒绝替换，访客第一次打开就报人机验证失败。
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || !isset($_POST['log'], $_POST['pwd'])) {
        return;
    }

    // apply_filters('authenticate') 不短路：留在链上的核心回调只对 WP_User
    // 入参提前返回，非空凭据下无视一切前置错误，一定会执行用户查询和密码
    // 哈希再覆盖结果。所以人机验证失败的请求直接把核心三个凭据回调从本次
    // 请求里摘除，让统一拒绝成为唯一结论——不出用户查询、不算密码哈希。
    // 空 token 由 verify helper 就地判错，不产生对 Cloudflare 的出站请求。
    if (springapex_verify_turnstile('') !== true) {
        remove_filter('authenticate', 'wp_authenticate_username_password', 20);
        remove_filter('authenticate', 'wp_authenticate_email_password', 20);
        remove_filter('authenticate', 'wp_authenticate_application_password', 20);

        add_filter('authenticate', static fn () => new WP_Error(
            'springapex_login_turnstile',
            __('The anti-spam check could not be verified. Please try again.', 'springapex')
        ));
        return;
    }

    // 验证通过的提交不注册任何拦截，走完整原生认证链。
});
