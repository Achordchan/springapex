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
require_once __DIR__ . '/sanitize.php';
require_once __DIR__ . '/save.php';

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

add_action('admin_enqueue_scripts', 'springapex_admin_assets');
function springapex_admin_assets(string $hook): void
{
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
            'keys' => ['brand'],
        ],
        [
            'title' => '各个页面',
            'desc' => '按前台导航的顺序排列，改哪一页就点哪一页。',
            'keys' => ['home', 'products', 'solutions', 'capabilities', 'videos', 'about', 'company', 'news', 'contact'],
        ],
    ];

    $elsewhere = [
        ['label' => '顶部导航菜单', 'desc' => '用 WordPress 自带的菜单功能：拖动排序，拖到右边缩进就是二级菜单。', 'url' => 'nav-menus.php'],
        ['label' => '产品条目', 'desc' => '每款产品的参数、材料、适用场景和图纸下载。', 'url' => 'edit.php?post_type=spring_product'],
        ['label' => '行业方案条目', 'desc' => '每个行业的方案正文和配图。', 'url' => 'edit.php?post_type=spring_solution'],
        ['label' => '案例条目', 'desc' => '客户案例的正文、图片和相关产品。', 'url' => 'edit.php?post_type=spring_case'],
        ['label' => '新闻文章', 'desc' => '每篇新闻的正文、图集和所属类型。', 'url' => 'edit.php?post_type=spring_news'],
        ['label' => '客户询盘', 'desc' => '客户从联系页提交的询盘和附件。', 'url' => 'edit.php?post_type=spring_inquiry'],
        ['label' => '媒体库', 'desc' => '所有上传过的图片和文件。', 'url' => 'upload.php'],
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
