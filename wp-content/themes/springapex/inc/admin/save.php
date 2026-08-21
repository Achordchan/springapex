<?php
/**
 * Save, reset and feedback handlers for the website-content admin screens.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

add_action('admin_post_springapex_save_content', 'springapex_admin_handle_save');
add_action('admin_notices', 'springapex_admin_content_notices');

function springapex_admin_schema_insert(array &$tree, string $path, array $field): void
{
    $parts = array_values(array_filter(explode('.', $path), static fn(string $part): bool => $part !== ''));
    if ($parts === []) {
        return;
    }

    $node = &$tree;
    foreach ($parts as $index => $part) {
        $node['children'][$part] ??= ['children' => []];
        $node = &$node['children'][$part];
        if ($index === count($parts) - 1) {
            $node['field'] = $field;
        }
    }
}

function springapex_admin_screen_schema_tree(string $screen_key): array
{
    static $trees = [];
    if (isset($trees[$screen_key])) {
        return $trees[$screen_key];
    }

    $screen = springapex_admin_screens()[$screen_key] ?? null;
    $tree = ['children' => []];
    if (!is_array($screen)) {
        return $tree;
    }

    foreach ((array) ($screen['sections'] ?? []) as $section) {
        foreach ((array) ($section['fields'] ?? []) as $field) {
            $path = (string) ($field['path'] ?? '');
            if ($path !== '') {
                springapex_admin_schema_insert($tree, $path, $field);
            }
        }
    }
    $trees[$screen_key] = $tree;
    return $tree;
}

function springapex_admin_screen_roots(string $screen_key): array
{
    return array_keys((array) (springapex_admin_screen_schema_tree($screen_key)['children'] ?? []));
}

function springapex_admin_sanitize_schema_node(
    mixed $raw,
    array $schema,
    mixed $current,
    string $path,
    array &$warnings
): array {
    if (isset($schema['field']) && is_array($schema['field'])) {
        $field = $schema['field'];
        $label = (string) ($field['label'] ?? $path);
        return springapex_admin_sanitize_field($field, $raw, $current, $label, $warnings);
    }

    if (!is_array($raw)) {
        return springapex_admin_reject($warnings, $path === '' ? '提交内容' : $path, '的数据结构不正确，没有保存。');
    }

    $children = (array) ($schema['children'] ?? []);
    foreach (array_keys($raw) as $key) {
        if (!isset($children[(string) $key])) {
            $unknown_path = ltrim($path . '.' . (string) $key, '.');
            springapex_admin_add_warning($warnings, '未声明字段「' . $unknown_path . '」没有保存。');
        }
    }

    $current_array = is_array($current) ? $current : [];
    $clean = [];
    $accepted = false;
    foreach ($children as $key => $child_schema) {
        if (!array_key_exists($key, $raw)) {
            continue;
        }
        $child_path = ltrim($path . '.' . $key, '.');
        $result = springapex_admin_sanitize_schema_node(
            $raw[$key],
            $child_schema,
            $current_array[$key] ?? null,
            $child_path,
            $warnings
        );
        if ($result['accepted']) {
            $clean[$key] = $result['value'];
            $accepted = true;
        }
    }

    return ['accepted' => $accepted, 'value' => $clean];
}

function springapex_admin_feedback_key(int $user_id, string $screen): string
{
    return 'springapex_content_feedback_' . $user_id . '_' . sanitize_key($screen);
}

function springapex_admin_store_feedback(string $screen, string $status, array $warnings = []): void
{
    set_transient(
        springapex_admin_feedback_key(get_current_user_id(), $screen),
        [
            'screen' => $screen,
            'status' => $status,
            'warnings' => array_values(array_unique(array_map('strval', $warnings))),
        ],
        60
    );
}

function springapex_admin_redirect_after_save(string $screen, string $query_key): void
{
    $fallback = admin_url('admin.php?page=' . SPRINGAPEX_ADMIN_SLUG . '-' . $screen);
    $target = wp_get_referer() ?: $fallback;
    $target = remove_query_arg(['sa-saved', 'sa-reset'], $target);
    wp_safe_redirect(add_query_arg($query_key, '1', $target));
    exit;
}

function springapex_admin_reset_screen(string $screen): void
{
    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    $overrides = is_array($overrides) ? $overrides : [];

    // Retired collection editors are now signposts to their CPT lists. Older
    // installations can still carry saved rows that provide seed fallbacks, so
    // a visible page reset must not silently delete data the operator can no
    // longer inspect or restore from this screen.
    $retired_collections = [
        'home' => ['home' => ['applications']],
        'products' => ['products' => ['categories']],
        'solutions' => ['solutions' => ['items']],
        'contact' => ['contact' => ['hero']],
    ];
    $preserved = [];
    foreach ($retired_collections[$screen] ?? [] as $root => $keys) {
        if (!isset($overrides[$root]) || !is_array($overrides[$root])) {
            continue;
        }
        foreach ($keys as $key) {
            if (array_key_exists($key, $overrides[$root])) {
                $preserved[$root][$key] = $overrides[$root][$key];
            }
        }
    }

    foreach (springapex_admin_screen_roots($screen) as $root) {
        unset($overrides[$root]);
    }
    foreach ($preserved as $root => $values) {
        $overrides[$root] = $values;
    }

    springapex_content_store_overrides($overrides);
    springapex_content_flush_caches($screen);
    springapex_admin_store_feedback($screen, 'reset');
    springapex_admin_redirect_after_save($screen, 'sa-reset');
}

function springapex_admin_handle_save(): void
{
    if (!current_user_can(SPRINGAPEX_ADMIN_CAP)) {
        wp_die('你没有修改网站内容的权限。', '权限不足', ['response' => 403]);
    }

    $screen_value = isset($_POST['screen']) && is_scalar($_POST['screen'])
        ? (string) wp_unslash($_POST['screen'])
        : '';
    $screen = sanitize_key($screen_value);
    $screens = springapex_admin_screens();
    if ($screen === '' || !isset($screens[$screen])) {
        wp_die('无法确认要保存哪个页面，请返回后台重新操作。', '页面无效', ['response' => 400]);
    }

    check_admin_referer('springapex_save_content_' . $screen);
    if (isset($_POST['springapex_reset_content'])) {
        springapex_admin_reset_screen($screen);
    }

    $raw = isset($_POST['springapex_content']) ? wp_unslash($_POST['springapex_content']) : [];
    $warnings = [];
    $result = springapex_admin_sanitize_schema_node(
        $raw,
        springapex_admin_screen_schema_tree($screen),
        springapex_content(),
        '',
        $warnings
    );

    $overrides = get_option(SPRINGAPEX_CONTENT_OVERRIDES_OPTION, []);
    $overrides = is_array($overrides) ? $overrides : [];
    if ($result['accepted'] && is_array($result['value'])) {
        foreach (springapex_admin_screen_roots($screen) as $root) {
            if (!array_key_exists($root, $result['value'])) {
                continue;
            }
            $overrides[$root] = array_key_exists($root, $overrides)
                ? springapex_content_merge($overrides[$root], $result['value'][$root])
                : $result['value'][$root];
        }
        springapex_content_store_overrides($overrides);
        springapex_content_flush_caches($screen);
    } else {
        springapex_admin_add_warning($warnings, '没有识别到可保存的字段，原有内容未改变。');
    }

    springapex_admin_store_feedback($screen, 'saved', $warnings);
    springapex_admin_redirect_after_save($screen, 'sa-saved');
}

function springapex_admin_content_notices(): void
{
    $page_value = isset($_GET['page']) && is_scalar($_GET['page'])
        ? (string) wp_unslash($_GET['page'])
        : '';
    $prefix = SPRINGAPEX_ADMIN_SLUG . '-';
    if (!str_starts_with($page_value, $prefix)) {
        return;
    }

    $screen = sanitize_key(substr($page_value, strlen($prefix)));
    $screens = springapex_admin_screens();
    if (!isset($screens[$screen])) {
        return;
    }

    $saved = isset($_GET['sa-saved']);
    $reset = isset($_GET['sa-reset']);
    if (!$saved && !$reset) {
        return;
    }

    $feedback_key = springapex_admin_feedback_key(get_current_user_id(), $screen);
    $feedback = get_transient($feedback_key);
    delete_transient($feedback_key);
    $warnings = is_array($feedback) && is_array($feedback['warnings'] ?? null)
        ? array_values(array_map('strval', $feedback['warnings']))
        : [];
    $preview_url = home_url((string) ($screens[$screen]['preview'] ?? '/'));
    ?>
    <div class="notice notice-success is-dismissible">
        <p>
            <?php echo $reset ? '已恢复这个页面的默认内容。' : '已保存，去前台刷新看看效果。'; ?>
            <a href="<?php echo esc_url($preview_url); ?>" target="_blank" rel="noopener">查看前台页面 ↗</a>
        </p>
    </div>
    <?php if ($warnings !== []) : ?>
        <div class="notice notice-warning is-dismissible">
            <p><strong>以下字段没有按原值保存，请检查：</strong></p>
            <ul>
                <?php foreach ($warnings as $warning) : ?>
                    <li><?php echo esc_html($warning); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif;
}
