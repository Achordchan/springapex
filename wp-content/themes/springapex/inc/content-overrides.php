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
const SPRINGAPEX_BRAND_CONTACT_SWAP_ATTEMPTS = 3;

/**
 * 一次性清掉这一批 theme_mod，整份走 compare-and-swap。
 *
 * remove_theme_mod() 是对整份 theme_mods_<主题> option 的读-改-写，而「表单设置」
 * 页的询盘收件邮箱和邮件模板存在同一份 option 里 —— 运营在读与写之间保存了那一页，
 * 这边的旧数组回写就会把人家的改动抹掉。逐个删就是逐次开这样一个窗口。
 *
 * @param array<int, string> $settings
 */
function springapex_brand_contact_clear_theme_mods(array $settings, int $attempts = 3): bool
{
    if (!function_exists('get_option') || !function_exists('springapex_update_option_if_unchanged')) {
        return false;
    }

    $option_name = 'theme_mods_' . (string) get_option('stylesheet');

    for ($attempt = 0; $attempt < $attempts; $attempt++) {
        if (function_exists('wp_cache_delete')) {
            wp_cache_delete($option_name, 'options');
            wp_cache_delete('alloptions', 'options');
            wp_cache_delete('notoptions', 'options');
        }

        $mods = get_option($option_name, null);
        if (!is_array($mods)) {
            // 连 theme_mods 都没有，自然没有要清的。
            return true;
        }

        $next = $mods;
        foreach ($settings as $setting) {
            unset($next[$setting]);
        }

        if ($next === $mods) {
            return true;
        }

        if (springapex_update_option_if_unchanged($option_name, $mods, $next)) {
            return true;
        }
    }

    return false;
}

/**
 * 迁移是否已经确认完成（覆盖表落库 + 源值清空之后才会记上版本号）。
 *
 * $fresh 用于 compare-and-swap 冲突之后再确认一次：这一整个请求开头很可能已经
 * 读过一次「没有这个 option」，get_option() 会记在 notoptions 名单里，之后哪怕
 * 别的请求刚写完版本号，这边读到的还是旧结论。
 */
function springapex_brand_contact_source_migrated(bool $fresh = false): bool
{
    if ($fresh && function_exists('wp_cache_delete')) {
        wp_cache_delete('springapex_brand_contact_source_version', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    return (string) get_option('springapex_brand_contact_source_version', '') === SPRINGAPEX_BRAND_CONTACT_SOURCE_VERSION;
}

/**
 * customizer 时代的联系方式/社交 theme_mod，键是 brand 底下的字段名，值是
 * [theme_mod 名, 清洗类型]。迁移拿它搬值，springapex_brand() 拿它在迁移落库
 * 之前兜底，两边必须是同一份名单。
 *
 * 询盘收件邮箱和邮件模板不在其中：它们归「表单设置」页管，仍然存 theme_mod。
 *
 * @return array<string, array{0: string, 1: string}>
 */
function springapex_brand_legacy_theme_mods(): array
{
    return [
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
}

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
 * 全程可重入：写覆盖表在前、删 theme_mod 在后，任何一步中断都还留着源值，下次
 * 请求原样再跑一遍。覆盖表的读-改-写走 springapex_update_option_if_unchanged()
 * 的 compare-and-swap —— 读到写之间要是另一个请求或运营的后台保存插了进来，
 * 这次写入会失败，重读最新的值再合并一遍，不会拿旧快照把别人的改动顶掉。
 */
function springapex_migrate_brand_contact_source(): void
{
    if (springapex_brand_contact_source_migrated()) {
        return;
    }

    $legacy = springapex_brand_legacy_theme_mods();

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
        $persisted = springapex_content_update_overrides(
            static function (array $overrides) use ($incoming): ?array {
                // 每次重试都会重新走到这里。别的请求要是已经把迁移做完了，手上
                // 这份 theme_mod 快照就作废了 —— 运营完全可能在那之后清空过某个
                // 链接，再把旧值合并上去就是把人家的编辑抹掉。
                if (springapex_brand_contact_source_migrated(true)) {
                    return null;
                }

                $brand = isset($overrides['brand']) && is_array($overrides['brand']) ? $overrides['brand'] : [];
                $overrides['brand'] = array_merge($brand, $incoming);
                return $overrides;
            },
            SPRINGAPEX_BRAND_CONTACT_SWAP_ATTEMPTS
        );

        // 别人已经搬完并且可能又被编辑过，这里就不该再动 theme_mod 和版本号了。
        if (springapex_brand_contact_source_migrated(true)) {
            return;
        }

        // 写进去了还得确认值真是想要的那份：并发的另一个请求可能刚好把同一批键
        // 写成了别的内容。
        if ($persisted) {
            $stored = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
            $stored_brand = is_array($stored) && isset($stored['brand']) && is_array($stored['brand'])
                ? $stored['brand']
                : [];
            foreach ($expected as $key => $value) {
                $persisted = ($stored_brand[$key] ?? null) === $value && $persisted;
            }
        }
    }

    // 覆盖表没落库就绝不动源值：先删后写的话，写失败或进程中断就把 customizer
    // 里的联系方式永久丢掉了，而且下一个请求会看到「没有值可搬」直接收工。
    if (!$persisted) {
        return;
    }

    $cleared = springapex_brand_contact_clear_theme_mods(array_column($legacy, 0));
    if ($cleared) {
        foreach ($legacy as [$setting, $_type]) {
            $cleared = get_theme_mod($setting, '') === '' && $cleared;
        }
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
 * 覆盖表的读-改-写统一入口。$mutate 拿到库里的当前值，返回改好的整份内容，
 * 写回时走 compare-and-swap：读到写之间要是别人（另一个后台保存、一次性迁移）
 * 抢先写过，这次写入会失败，重读最新的值再跑一遍 $mutate。
 *
 * 每个写入点各自 get_option() 再 update_option() 的话，一份旧快照就能把别人刚
 * 存下的内容整份顶掉 —— 覆盖表是整站文案，顶掉的是运营的实际编辑。
 *
 * autoload 跟着内容大小走（超过 SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT 就关掉），和
 * 值写在同一条语句里，中间不会被别人插一脚。
 *
 * $mutate 返回非数组表示放弃这次写入（比如发现该做的事别人已经做完了），
 * 此时不写库，函数返回 false。
 *
 * @param callable(array<string, mixed>): (array<string, mixed>|null) $mutate
 */
function springapex_content_update_overrides(callable $mutate, int $attempts = 3): bool
{
    for ($attempt = 0; $attempt < $attempts; $attempt++) {
        // 上一轮 CAS 失败正说明有人刚写过，缓存里那份已经不作数了。notoptions 也要
        // 清：这一行原本不存在时，get_option() 会把「没有这个 option」记进去，别人
        // 抢先建好行之后，这边照样读不到，add_option() 每轮都插不进去直到重试耗尽。
        wp_cache_delete(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');

        // 行在不在，问库；值解不出数组时当空数组来改，但 CAS 仍拿原值做对照，
        // 这样一行坏数据能被原子地换掉，而不是让每次写入都卡在插入失败上。
        $stored = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, null);
        $exists = $stored !== null || springapex_option_row_exists(SPRINGAPEX_CONTENT_OVERRIDES_OPTION);
        $current = is_array($stored) ? $stored : [];

        $next = $mutate($current);
        if (!is_array($next)) {
            return false;
        }
        $next = (array) springapex_replace_public_brand($next);

        if ($exists && $next === $stored) {
            return true;
        }

        if (!$exists) {
            if ($next === []) {
                return true;
            }
            $autoload = strlen(serialize($next)) <= SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT;
            // 别人抢先建好行时必须插不进去（而不是覆盖），所以走 INSERT IGNORE
            // 而不是 add_option()；插不进去就重读重来。
            if (springapex_add_option_if_absent(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $next, $autoload)) {
                return true;
            }
            continue;
        }

        if ($next === []) {
            if (springapex_delete_option_if_unchanged(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $stored)) {
                return true;
            }
            continue;
        }

        // autoload 跟着内容大小一起定，和值写在同一条语句里。
        $autoload = strlen(serialize($next)) <= SPRINGAPEX_CONTENT_AUTOLOAD_LIMIT;
        if (springapex_update_option_if_unchanged(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, $stored, $next, $autoload)) {
            return true;
        }
    }

    return false;
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
