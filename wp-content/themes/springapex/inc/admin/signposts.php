<?php
/**
 * 指路牌: from a page to the screen that actually edits it.
 *
 * The seeded Pages hold almost no content of their own — their copy lives in
 * 网站内容 → X. Someone who opens the Page in the editor sees an empty document
 * and concludes the site cannot be edited. These signposts say where to go,
 * from the three places the operator is likely to be standing: the Page editor,
 * the Pages list, and the front end itself.
 *
 * Read-only on purpose. A second editable control for the same value is the one
 * thing this theme's admin must never grow.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// Defined here rather than in admin.php: the front-end admin bar signpost needs
// them, and admin.php only loads inside wp-admin.
const SPRINGAPEX_ADMIN_SLUG = 'springapex-content';
const SPRINGAPEX_ADMIN_CAP = 'edit_theme_options';

/**
 * Page template (or front-page role) => 网站内容 screen keys, in the order the
 * operator would work through them.
 */
function springapex_signpost_map(): array
{
    return [
        'front-page' => ['home'],
        'page-about.php' => ['about', 'company'],
        'page-capabilities.php' => ['capabilities'],
        'page-manufacturing-videos.php' => ['videos'],
        'page-contact.php' => ['contact'],
        'page-sustainability.php' => ['company'],
    ];
}

/** Post type archive => 网站内容 screen key. */
function springapex_signpost_archive_map(): array
{
    return [
        'spring_product' => 'products',
        'spring_solution' => 'solutions',
        'spring_news' => 'news',
    ];
}

/**
 * Which screens edit this page, if any. The front page is matched by the
 * page_on_front option rather than by its template, because it uses `default`.
 */
function springapex_signpost_keys_for_page(int $page_id): array
{
    if ($page_id <= 0) {
        return [];
    }

    $map = springapex_signpost_map();

    if ((int) get_option('page_on_front') === $page_id) {
        return $map['front-page'];
    }

    $template = (string) get_post_meta($page_id, '_wp_page_template', true);

    return $map[$template] ?? [];
}

/** Label and admin URL for one screen key, or null if the key is unknown. */
function springapex_signpost_screen(string $key): ?array
{
    require_once __DIR__ . '/schema.php';

    $screens = springapex_admin_screens();
    if (!isset($screens[$key])) {
        return null;
    }

    return [
        'label' => (string) $screens[$key]['label'],
        'intro' => (string) ($screens[$key]['intro'] ?? ''),
        'url' => admin_url('admin.php?page=' . SPRINGAPEX_ADMIN_SLUG . '-' . $key),
    ];
}

/* Signpost 1: meta box on the Page edit screen --------------------------- */

/**
 * `normal` context, not `side`: the block editor files side meta boxes at the
 * bottom of a sidebar that starts closed, and it drops plain `admin_notices`
 * entirely. A normal meta box renders in the always-visible area directly under
 * the editor — and these pages have no content above it.
 */
add_action('add_meta_boxes_page', 'springapex_signpost_meta_box');
function springapex_signpost_meta_box(WP_Post $post): void
{
    if (springapex_signpost_keys_for_page((int) $post->ID) === []) {
        return;
    }

    add_meta_box(
        'springapex-signpost',
        '这个页面的内容在哪里改',
        'springapex_signpost_meta_box_render',
        'page',
        'normal',
        'high'
    );
}

function springapex_signpost_meta_box_render(WP_Post $post): void
{
    $keys = springapex_signpost_keys_for_page((int) $post->ID);
    ?>
    <p>这一页的文字和图片不在这个编辑器里改，请点下面的按钮过去维护。这个编辑器里的标题和别名仍然有用：它决定网址和菜单里的名字。</p>
    <?php
    foreach ($keys as $key) {
        $screen = springapex_signpost_screen($key);
        if ($screen === null) {
            continue;
        }
        ?>
        <p>
            <a class="button button-primary" href="<?php echo esc_url($screen['url']); ?>">去改「<?php echo esc_html($screen['label']); ?>」</a>
            <?php if ($screen['intro'] !== '') : ?>
                <span class="description"><?php echo esc_html($screen['intro']); ?></span>
            <?php endif; ?>
        </p>
        <?php
    }
}

/* Signpost 2: row action in the Pages list ------------------------------- */

add_filter('page_row_actions', 'springapex_signpost_row_action', 10, 2);
function springapex_signpost_row_action(array $actions, WP_Post $post): array
{
    if (!current_user_can(SPRINGAPEX_ADMIN_CAP)) {
        return $actions;
    }

    $keys = springapex_signpost_keys_for_page((int) $post->ID);
    if ($keys === []) {
        return $actions;
    }

    $screen = springapex_signpost_screen($keys[0]);
    if ($screen === null) {
        return $actions;
    }

    // In front of 编辑 rather than appended: for these pages it is the action
    // the operator wants nine times out of ten.
    return array_merge(
        ['springapex-content' => sprintf(
            '<a href="%s">编辑页面内容</a>',
            esc_url($screen['url'])
        )],
        $actions
    );
}

/* Signpost 3: admin bar item on the front end ---------------------------- */

add_action('admin_bar_menu', 'springapex_signpost_admin_bar', 81);
function springapex_signpost_admin_bar(WP_Admin_Bar $bar): void
{
    if (is_admin() || !current_user_can(SPRINGAPEX_ADMIN_CAP)) {
        return;
    }

    $keys = [];

    if (is_front_page()) {
        $keys = springapex_signpost_keys_for_page((int) get_option('page_on_front'));
    } elseif (is_page()) {
        $keys = springapex_signpost_keys_for_page((int) get_queried_object_id());
    } elseif (is_post_type_archive()) {
        $key = springapex_signpost_archive_map()[(string) get_query_var('post_type')] ?? '';
        $keys = $key === '' ? [] : [$key];
    }

    if ($keys === []) {
        return;
    }

    $first = springapex_signpost_screen($keys[0]);
    if ($first === null) {
        return;
    }

    $bar->add_node([
        'id' => 'springapex-signpost',
        'title' => '编辑这个页面的内容',
        'href' => $first['url'],
    ]);

    // More than one screen feeds the page, so name them instead of guessing.
    if (count($keys) > 1) {
        foreach ($keys as $key) {
            $screen = springapex_signpost_screen($key);
            if ($screen === null) {
                continue;
            }
            $bar->add_node([
                'parent' => 'springapex-signpost',
                'id' => 'springapex-signpost-' . $key,
                'title' => $screen['label'],
                'href' => $screen['url'],
            ]);
        }
    }
}
