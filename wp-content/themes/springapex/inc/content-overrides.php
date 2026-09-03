<?php
/**
 * Persisted content overrides shared by the front end and wp-admin.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

const SPRINGAPEX_CONTENT_OVERRIDES_OPTION = 'springapex_content_overrides';
const SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT = 204800;
const SPRINGAPEX_PUBLIC_BRAND_VERSION = '2';

/**
 * Replace the retired public brand name without touching stable technical
 * identifiers such as option names, theme paths, domains, email addresses or
 * social handles.
 */
function springapex_replace_public_brand(mixed $value): mixed
{
    if (is_string($value)) {
        return str_replace(
            ['ApexSpring', 'APEXSPRING', 'Apexspring', 'SpringApex'],
            ['NorenSpring', 'NORENSPRING', 'NorenSpring', 'NorenSpring'],
            $value
        );
    }

    if (!is_array($value)) {
        return $value;
    }

    foreach ($value as $key => $item) {
        $value[$key] = springapex_replace_public_brand($item);
    }

    return $value;
}

/**
 * Persist the public-brand migration once so existing wp-admin values and the
 * front end agree immediately after deployment. Exact legacy names only are
 * replaced; legal company names and technical identifiers are left intact.
 */
function springapex_migrate_public_brand_options(): void
{
    if ((string) get_option('springapex_public_brand_version', '') === SPRINGAPEX_PUBLIC_BRAND_VERSION) {
        return;
    }

    $success = true;
    foreach (['blogname', 'blogdescription'] as $option_name) {
        $current = get_option($option_name, '');
        $updated = springapex_replace_public_brand($current);
        if ($updated !== $current) {
            update_option($option_name, $updated, false);
            $success = get_option($option_name, '') === $updated && $success;
        }
    }

    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    if (is_array($overrides)) {
        $updated_overrides = springapex_replace_public_brand($overrides);
        if ($updated_overrides !== $overrides) {
            springapex_content_store_overrides($updated_overrides);
            $success = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []) === $updated_overrides && $success;
        }
    }

    // The bundled NorenSpring wordmark is the release authority. Clear the
    // currently selected media-library logo once, even when its attachment
    // title/alt text is generic and cannot identify the old pixels. Operators
    // can still upload a new custom logo after this one-time migration.
    $custom_logo_id = function_exists('get_theme_mod') ? (int) get_theme_mod('custom_logo', 0) : 0;
    if ($custom_logo_id > 0) {
        remove_theme_mod('custom_logo');
        $success = (int) get_theme_mod('custom_logo', 0) === 0 && $success;
    }

    if ($success) {
        update_option('springapex_public_brand_version', SPRINGAPEX_PUBLIC_BRAND_VERSION, false);
    }
}
add_action('init', 'springapex_migrate_public_brand_options', 1);

const SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION = '1';

/**
 * 按类型清洗一份来自 customizer 的旧值，脏数据一律丢弃（返回空串）。
 */
function springapex_content_clean_legacy_brand_value(string $value, string $type): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }

    switch ($type) {
        case 'email':
            $email = sanitize_email($value);
            return is_email($email) ? $email : '';

        case 'url':
            $url = (string) esc_url_raw($value, ['http', 'https']);
            return preg_match('#^https?://#i', $url) === 1 ? $url : '';

        case 'textarea':
            return sanitize_textarea_field($value);

        default:
            return sanitize_text_field($value);
    }
}

/**
 * 联系方式和社交链接过去有两个入口：后台「网站内容」写内容覆盖表，
 * customizer 写 theme_mod，而 theme_mod 只要非空就压过覆盖表 —— 运营在后台
 * 清空的社交链接会被旧 theme_mod 顶回来。customizer 的这些控件已下线，这里
 * 把残留的 theme_mod 值一次性搬进覆盖表（迁移前 theme_mod 胜出，所以照搬即可
 * 保持访客看到的内容不变），再清掉 theme_mod，让覆盖表成为唯一来源。
 *
 * 全程可重入，不需要加锁：写覆盖表在前、删 theme_mod 在后，任何一步中断都还
 * 留着源值，下次请求原样再跑一遍；写之前才读覆盖表，读到的已经包含并发请求
 * 刚搬完的值，比对一致就不写，所以两个请求同时跑也不会互相顶掉。
 */
function springapex_migrate_brand_contact_source(): void
{
    if ((string) get_option('springapex_brand_contact_source_version', '') === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION) {
        return;
    }

    // 询盘收件邮箱和邮件模板不在这里：它们归「表单设置」页管，仍然存 theme_mod。
    $legacy = [
        'email'     => ['springapex_email', 'email'],
        'phone'     => ['springapex_phone', 'text'],
        'whatsapp'  => ['springapex_whatsapp', 'text'],
        'address'   => ['springapex_address', 'textarea'],
        'hours'     => ['springapex_hours', 'text'],
        'linkedin'  => ['springapex_linkedin', 'url'],
        'facebook'  => ['springapex_facebook', 'url'],
        'x'         => ['springapex_x', 'url'],
        'instagram' => ['springapex_instagram', 'url'],
        'tiktok'    => ['springapex_tiktok', 'url'],
    ];

    $incoming = [];
    foreach ($legacy as $key => [$setting, $type]) {
        $stored = get_theme_mod($setting, '');
        if (!is_string($stored)) {
            continue;
        }
        // 空的 theme_mod 当年也不会覆盖内容，丢掉就是。
        $value = springapex_content_clean_legacy_brand_value($stored, $type);
        if ($value === '') {
            continue;
        }
        $incoming[$key] = $value;
    }

    $persisted = true;
    if ($incoming !== []) {
        // 落库前会跑一次公开品牌名替换，所以拿替换后的值做对照。
        $expected = (array) springapex_replace_public_brand($incoming);

        // 紧挨着写入才读覆盖表，不用函数开头的旧快照：并发的另一个请求可能刚
        // 搬完，拿旧快照回写会把它的结果连同运营的编辑一起顶掉。
        $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
        if (!is_array($overrides)) {
            $overrides = [];
        }
        $brand = isset($overrides['brand']) && is_array($overrides['brand']) ? $overrides['brand'] : [];

        $needs_write = false;
        foreach ($expected as $key => $value) {
            if (($brand[$key] ?? null) !== $value) {
                $needs_write = true;
                break;
            }
        }

        if ($needs_write) {
            $overrides['brand'] = array_merge($brand, $incoming);
            springapex_content_store_overrides($overrides);
        }

        $stored_overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
        $stored_brand = is_array($stored_overrides) && isset($stored_overrides['brand']) && is_array($stored_overrides['brand'])
            ? $stored_overrides['brand']
            : [];
        foreach ($expected as $key => $value) {
            $persisted = ($stored_brand[$key] ?? null) === $value && $persisted;
        }
    }

    // 覆盖表没落库就绝不动源值：先删后写的话，写失败或进程中断就把 customizer
    // 里的联系方式永久丢掉了，而且下一个请求会看到「没有值可搬」直接收工。
    if (!$persisted) {
        return;
    }

    $cleared = true;
    foreach ($legacy as [$setting, $_type]) {
        remove_theme_mod($setting);
        $cleared = get_theme_mod($setting, '') === '' && $cleared;
    }

    // 没清干净就不写版本号，下次请求再试一遍。
    if ($cleared) {
        update_option('springapex_brand_contact_source_version', SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION, false);
    }
}
add_action('init', 'springapex_migrate_brand_contact_source', 1);

// Keep old database-authored post content from leaking the retired brand while
// the versioned seed migration updates untouched records in the background.
add_filter('the_title', 'springapex_replace_public_brand', 20);
add_filter('the_content', 'springapex_replace_public_brand', 20);
add_filter('get_the_excerpt', 'springapex_replace_public_brand', 20);

add_action('init', static function (): void {
    add_filter('option_blogname', 'springapex_replace_public_brand', 20);
    add_filter('option_blogdescription', 'springapex_replace_public_brand', 20);
}, 2);

function springapex_content_is_list(array $value): bool
{
    $index = 0;
    foreach ($value as $key => $_item) {
        if ($key !== $index) {
            return false;
        }
        $index++;
    }
    return true;
}

/**
 * Lists replace as a whole; associative arrays merge recursively.
 */
function springapex_content_merge(mixed $base, mixed $override): mixed
{
    if (!is_array($base) || !is_array($override)) {
        return $override;
    }

    if (springapex_content_is_list($override)) {
        return array_values($override);
    }
    if (springapex_content_is_list($base)) {
        return $override;
    }

    $merged = $base;
    foreach ($override as $key => $value) {
        $merged[$key] = array_key_exists($key, $merged)
            ? springapex_content_merge($merged[$key], $value)
            : $value;
    }
    return $merged;
}

function springapex_content_apply_overrides(array $data): array
{
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    // An empty option means "nothing overridden". It must not reach the merge:
    // an empty array counts as a list there, and a list override replaces
    // wholesale — which would blank the entire content tree.
    if (!is_array($overrides) || $overrides === []) {
        return springapex_replace_public_brand($data);
    }
    return springapex_replace_public_brand(springapex_content_merge($data, $overrides));
}
add_filter('springapex_content', 'springapex_content_apply_overrides');

function springapex_content_store_overrides(array $overrides): void
{
    $overrides = springapex_replace_public_brand($overrides);

    if ($overrides === []) {
        delete_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION);
        return;
    }

    $autoload = strlen(serialize($overrides)) <= SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT;
    update_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $overrides, $autoload);
}

/**
 * update_option() already invalidates the options cache, so there is nothing
 * to flush here. Deliberately no wp_cache_flush(): with a persistent object
 * cache that would drop every site visitor's cache on each text edit.
 * Page-cache plugins can hook the action below to purge precisely.
 */
function springapex_content_flush_caches(string $screen): void
{
    do_action('springapex_content_cache_flushed', $screen);
}
