<?php
/**
 * 网站内容 admin area.
 *
 * One top-level menu that mirrors the site's own navigation, so a service
 * colleague who knows the website can find the field without knowing the theme.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

// SPRINGAPEX_ADMIN_SLUG / SPRINGAPEX_ADMIN_CAP live in signposts.php, which is
// loaded on the front end too.
require_once __DIR__ . '/signposts.php';
require_once __DIR__ . '/schema.php';
require_once __DIR__ . '/render.php';
require_once __DIR__ . '/search.php';
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/save.php';
require_once __DIR__ . '/system-status.php';
require_once __DIR__ . '/seo-settings.php';

add_action('admin_menu', 'springapex_admin_menu');
function springapex_admin_menu(): void
{
    add_menu_page(
        '网站内容',
        '网站内容',
        SPRINGAPEX_ADMIN_CAP,
        SPRINGAPEX_ADMIN_SLUG,
        'springapex_admin_overview_page',
        'dashicons-admin-site-alt3',
        3
    );

    add_submenu_page(
        SPRINGAPEX_ADMIN_SLUG,
        '内容总览',
        '内容总览',
        SPRINGAPEX_ADMIN_CAP,
        SPRINGAPEX_ADMIN_SLUG,
        'springapex_admin_overview_page'
    );

    foreach (springapex_admin_screens() as $key => $screen) {
        add_submenu_page(
            SPRINGAPEX_ADMIN_SLUG,
            (string) $screen['title'],
            (string) $screen['label'],
            SPRINGAPEX_ADMIN_CAP,
            SPRINGAPEX_ADMIN_SLUG . '-' . $key,
            static function () use ($key): void {
                springapex_admin_render_screen($key);
            }
        );
    }
}

/**
 * Hide native menus this site never uses, so the sidebar only shows content
 * a service colleague actually edits. Runs late so the items exist first.
 *
 * - 文章 (native posts): all real content lives in spring_* CPTs; the only
 *   template that touches native posts is the generic index.php fallback.
 * - 评论: no post type registers `comments` support, so it is always empty.
 *
 * These only remove the menu items; the screens stay reachable by direct URL,
 * and admin-only menus (外观/插件/用户/工具/设置) are left untouched.
 */
add_action('admin_menu', 'springapex_hide_unused_menus', 999);
function springapex_hide_unused_menus(): void
{
    remove_menu_page('edit.php');
    remove_menu_page('edit-comments.php');
}

/**
 * WP Mail SMTP（免费版）侧边栏只留 设置/工具/关于：去掉「Upgrade to Pro」、
 * 轮换推荐插件与 Pro 功能页入口（Email Log / Email Reports 等）。
 * 只移除菜单项，不解锁任何付费功能；对应页面直链仍可访问。
 * 页内横幅的隐藏见 assets/css/admin-polish.css。
 */
/**
 * WP Mail SMTP（免费版）侧边栏去掉纯推销入口：「Upgrade to Pro」（slug 是外链
 * URL）、轮换的「推荐插件」项（wp-mail-smtp-recommended-*）与两个 Pro 卖点页
 * （Email Log / Email Reports）。Settings / Tools（含测试邮件）/ About 等功能页
 * 全部保留——remove_submenu_page 会连带锁住该页的直链访问，不能多删。
 * 页内横幅与 Pro 标签页的隐藏见 assets/css/admin-polish.css。
 */
add_action('admin_menu', 'springapex_trim_wp_mail_smtp_menu', 1000);
function springapex_trim_wp_mail_smtp_menu(): void
{
    if (!isset($GLOBALS['submenu']['wp-mail-smtp'])) {
        return;
    }
    // 该插件注册后 $submenu 键被数字重排，slug 在每项 [2] 位；remove_submenu_page
    // 会连带锁直链访问，所以只按 slug 黑名单精确删纯推销项。
    foreach ($GLOBALS['submenu']['wp-mail-smtp'] as $key => $item) {
        $slug = (string) ($item[2] ?? '');
        $is_promo = str_starts_with($slug, 'http') ||
            str_starts_with($slug, 'wp-mail-smtp-recommended-') ||
            in_array($slug, ['wp-mail-smtp-logs', 'wp-mail-smtp-reports'], true);
        if ($is_promo) {
            unset($GLOBALS['submenu']['wp-mail-smtp'][$key]);
        }
    }
}

add_action('admin_enqueue_scripts', 'springapex_admin_assets');
function springapex_admin_assets(string $hook): void
{
    // WP Mail SMTP 页面：第三方后台推销清理（配合 springapex_trim_wp_mail_smtp_menu）。
    if (str_contains($hook, 'wp-mail-smtp')) {
        wp_enqueue_style(
            'springapex-admin-polish',
            SPRINGAPEX_URI . '/assets/css/admin-polish.css',
            [],
            SPRINGAPEX_VERSION
        );
    }

    if (!str_contains($hook, SPRINGAPEX_ADMIN_SLUG)) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style(
        'springapex-admin',
        SPRINGAPEX_URI . '/assets/css/admin.css',
        [],
        SPRINGAPEX_VERSION
    );
    wp_enqueue_script(
        'springapex-admin',
        SPRINGAPEX_URI . '/assets/js/admin.js',
        [],
        SPRINGAPEX_VERSION,
        true
    );
    wp_localize_script('springapex-admin', 'springapexAdmin', [
        'icons' => springapex_admin_icon_choices(),
    ]);
}

/**
 * Landing page: says plainly where each kind of content lives, including the
 * things that are NOT on these screens (products, news, inquiries).
 */
function springapex_admin_overview_page(): void
{
    $screens = springapex_admin_screens();
    $groups = [
        [
            'title' => '网站通用',
            'desc' => '影响每一个页面的内容。',
            'keys' => ['brand', 'faq'],
        ],
        [
            'title' => '各个页面',
            'desc' => '按前台导航的顺序排列，改哪一页就点哪一页。',
            'keys' => ['home', 'products', 'solutions', 'capabilities', 'videos', 'about', 'company', 'sustainability', 'resources', 'news', 'contact'],
        ],
    ];

    $elsewhere = [
        ['label' => '顶部导航菜单', 'desc' => '用 WordPress 自带的菜单功能：拖动排序，拖到右边缩进就是二级菜单。', 'url' => 'nav-menus.php'],
        ['label' => '产品条目', 'desc' => '产品卡片以及每款产品的图片、参数、详细内容、首页推荐和大菜单展示与排序。', 'url' => 'edit.php?post_type=spring_product'],
        ['label' => '行业方案条目', 'desc' => '行业卡片以及每个行业的详细内容和配图。', 'url' => 'edit.php?post_type=spring_solution'],
        ['label' => '案例条目', 'desc' => '客户案例的正文、图片和相关产品。', 'url' => 'edit.php?post_type=spring_case'],
        ['label' => '新闻文章', 'desc' => '每篇新闻的正文、图集和所属类型。', 'url' => 'edit.php?post_type=spring_news'],
        ['label' => '客户询盘', 'desc' => '客户从联系页提交的询盘和附件。', 'url' => 'edit.php?post_type=spring_inquiry'],
        ['label' => '媒体库', 'desc' => '所有上传过的图片和文件。', 'url' => 'upload.php'],
        ['label' => 'SEO / TDK', 'desc' => '首页、静态页和列表页的搜索标题、描述与关键词。', 'url' => 'admin.php?page=springapex-content-seo'],
    ];
    ?>
    <div class="wrap sa-admin">
        <div class="sa-admin__header">
            <div>
                <h1 class="sa-admin__title">网站内容</h1>
                <p class="sa-admin__intro">改网站上的文字和图片，都从这里进。每一项旁边都写了它出现在前台的什么位置。</p>
            </div>
            <a class="button sa-admin__preview" href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">打开网站首页 ↗</a>
        </div>

        <?php springapex_admin_render_search(); ?>

        <?php foreach ($groups as $group) : ?>
            <section class="sa-card">
                <header class="sa-card__head">
                    <h2 class="sa-card__title"><?php echo esc_html($group['title']); ?></h2>
                    <p class="sa-card__desc"><?php echo esc_html($group['desc']); ?></p>
                </header>
                <div class="sa-card__body">
                    <div class="sa-tiles">
                        <?php foreach ($group['keys'] as $key) :
                            if (!isset($screens[$key])) {
                                continue;
                            }
                            $url = admin_url('admin.php?page=' . SPRINGAPEX_ADMIN_SLUG . '-' . $key);
                            ?>
                            <a class="sa-tile" href="<?php echo esc_url($url); ?>">
                                <span class="sa-tile__title"><?php echo esc_html((string) $screens[$key]['label']); ?></span>
                                <span class="sa-tile__desc"><?php echo esc_html((string) $screens[$key]['intro']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="sa-card">
            <header class="sa-card__head">
                <h2 class="sa-card__title">不在这里改的内容</h2>
                <p class="sa-card__desc">下面这些用 WordPress 自带的界面管理，点进去就是各自的列表。</p>
            </header>
            <div class="sa-card__body">
                <div class="sa-tiles sa-tiles--plain">
                    <?php foreach ($elsewhere as $item) : ?>
                        <a class="sa-tile" href="<?php echo esc_url(admin_url($item['url'])); ?>">
                            <span class="sa-tile__title"><?php echo esc_html($item['label']); ?></span>
                            <span class="sa-tile__desc"><?php echo esc_html($item['desc']); ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </div>
    <?php
}
