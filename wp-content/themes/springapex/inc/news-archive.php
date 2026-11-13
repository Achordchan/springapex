<?php
/**
 * 新闻列表 /news/ 分页。
 *
 * 以前列表一次输出全部新闻，文章越多页面越重，而 WordPress 自己的
 * /news/page/2/ 等网址也能打开、内容却和第一页一模一样。现在：
 *
 * - 每页 13 篇：1 篇大图 + 12 张卡片，桌面三列、平板两列、手机一列都能排满；
 *   可用 springapex_news_per_page 过滤器改。
 * - 分类筛选（?news_type=…）在当前分类里分页：/news/page/2/?news_type=exhibitions。
 * - 超出页数的网址返回 404（主查询按同样的每页数量计算，筛选后页数更少的由
 *   template_redirect 补判）。
 * - 只查一次全部已发布新闻的基本字段做筛选，组装卡片数据只针对当前这一页。
 * - canonical：未筛选的第 N 页指向自身；筛选页（任何页码）指回 /news/，与以前
 *   筛选页的做法一致（inc/schema.php）。标题和描述在第 2 页起加「Page N」，免得
 *   各页标题重复（inc/seo.php）。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

function springapex_news_per_page(): int
{
    $per_page = function_exists('apply_filters') ? apply_filters('springapex_news_per_page', 13) : 13;
    return max(1, (int) $per_page);
}

/**
 * 列表的分类标签。键是 spring_news_type 的 slug，'all' 表示不筛选。
 *
 * @return array<string, array{label:string,kicker:string,title:string}>
 */
function springapex_news_types(): array
{
    return [
        'all' => [
            'label' => __('All News', 'springapex'),
            'kicker' => __('LATEST FROM NORENSPRING', 'springapex'),
            'title' => __('News and engineering insights.', 'springapex'),
        ],
        'industry-news' => [
            'label' => __('Industry News', 'springapex'),
            'kicker' => __('INDUSTRY NEWS', 'springapex'),
            'title' => __('Engineering and manufacturing developments.', 'springapex'),
        ],
        'exhibitions' => [
            'label' => __('Exhibitions', 'springapex'),
            'kicker' => __('EXHIBITIONS', 'springapex'),
            'title' => __('Meet NorenSpring at industry events.', 'springapex'),
        ],
        'company-news' => [
            'label' => __('Company News', 'springapex'),
            'kicker' => __('COMPANY NEWS', 'springapex'),
            'title' => __('Updates from NorenSpring.', 'springapex'),
        ],
    ];
}

/** 当前请求的分类筛选；未知值按「全部」处理。 */
function springapex_news_active_type(): string
{
    $requested = is_scalar($_GET['news_type'] ?? null)
        ? sanitize_key((string) wp_unslash($_GET['news_type']))
        : 'all';
    return array_key_exists($requested, springapex_news_types()) ? $requested : 'all';
}

/** 列表某一页的网址（页码 1 即列表本身），带上分类筛选。 */
function springapex_news_page_url(string $archive_url, int $page, string $news_type = 'all'): string
{
    $url = springapex_schema_paged_url($archive_url, max(1, $page));
    if ($news_type !== '' && $news_type !== 'all') {
        $url .= (str_contains($url, '?') ? '&' : '?') . 'news_type=' . rawurlencode($news_type);
    }
    return $url;
}

/**
 * 分页条要显示的页码：首页、末页、当前页前后各一页，中间断开处用 null（省略号）。
 *
 * @return list<int|null>
 */
function springapex_news_page_numbers(int $current, int $total): array
{
    if ($total <= 1) {
        return [];
    }
    $current = min(max(1, $current), $total);
    $keep = [];
    foreach ([1, $total, $current - 1, $current, $current + 1] as $page) {
        if ($page >= 1 && $page <= $total) {
            $keep[$page] = true;
        }
    }
    // 只隔一页时直接显示那一页，比显示省略号更省地方。
    $pages = array_keys($keep);
    sort($pages);
    $result = [];
    $previous = 0;
    foreach ($pages as $page) {
        if ($previous > 0 && $page - $previous === 2) {
            $result[] = $previous + 1;
        } elseif ($previous > 0 && $page - $previous > 2) {
            $result[] = null;
        }
        $result[] = $page;
        $previous = $page;
    }
    return $result;
}

/** 文章所属分类 slug，规则与 springapex_news_from_post() 一致。 */
function springapex_news_type_of_post(WP_Post $post): string
{
    $terms = get_the_terms($post->ID, 'spring_news_type');
    if (is_array($terms) && isset($terms[0]) && (string) ($terms[0]->slug ?? '') !== '') {
        return (string) $terms[0]->slug;
    }
    $seed = springapex_news_seed((string) $post->post_name) ?? [];
    return (string) ($seed['news_type'] ?? 'company-news');
}

/** 当前请求的页码（主查询的 paged，最小为 1）。 */
function springapex_news_current_page(): int
{
    return max(1, (int) get_query_var('paged'));
}

/**
 * 列表当前页要显示的内容。同一请求内只算一次。
 *
 * @return array{type:string,page:int,per_page:int,total:int,total_pages:int,items:list<array<string,mixed>>}
 */
function springapex_news_archive_view(): array
{
    static $view = null;
    if ($view !== null) {
        return $view;
    }

    $type = springapex_news_active_type();
    $page = springapex_news_current_page();
    $per_page = springapex_news_per_page();

    if (defined('SPRINGAPEX_PREVIEW')) {
        $all = array_values(array_filter(
            springapex_news_list(),
            static fn(array $item): bool => $type === 'all' || (string) ($item['news_type'] ?? 'company-news') === $type
        ));
        $total = count($all);
        $slice = array_slice($all, ($page - 1) * $per_page, $per_page);
    } else {
        $posts = post_type_exists('spring_news') ? get_posts([
            'post_type' => 'spring_news',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => ['date' => 'DESC', 'ID' => 'DESC'],
            'no_found_rows' => true,
            'update_post_meta_cache' => false,
        ]) : [];
        if ($type !== 'all') {
            $posts = array_values(array_filter(
                $posts,
                static fn(WP_Post $post): bool => springapex_news_type_of_post($post) === $type
            ));
        }
        $total = count($posts);
        $page_posts = array_slice($posts, ($page - 1) * $per_page, $per_page);
        if ($page_posts !== [] && function_exists('update_postmeta_cache')) {
            update_postmeta_cache(array_map(static fn(WP_Post $post): int => (int) $post->ID, $page_posts));
        }
        $slice = array_map('springapex_news_from_post', $page_posts);
    }

    $view = [
        'type' => $type,
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => max(1, (int) ceil($total / $per_page)),
        'items' => array_values($slice),
    ];
    return $view;
}

function springapex_news_is_archive_request(): bool
{
    return !is_admin() && !is_feed() && is_post_type_archive('spring_news');
}

// 主查询按同样的每页数量分页，WordPress 才会对未筛选列表超出的页码直接返回 404。
add_action('pre_get_posts', static function (WP_Query $query): void {
    if (is_admin() || !$query->is_main_query() || $query->is_feed() || !$query->is_post_type_archive('spring_news')) {
        return;
    }
    $query->set('posts_per_page', springapex_news_per_page());
});

// 筛选后页数更少：/news/page/5/?news_type=exhibitions 这类超出的页码也返回 404。
add_action('template_redirect', static function (): void {
    if (!springapex_news_is_archive_request() || is_404()) {
        return;
    }
    $view = springapex_news_archive_view();
    if ($view['page'] > $view['total_pages']) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
    }
}, 5);

// 未筛选列表输出上一页/下一页提示，方便抓取按顺序发现各页。
add_action('wp_head', static function (): void {
    if (!springapex_news_is_archive_request() || is_404() || springapex_seo_external_plugin_active()) {
        return;
    }
    $view = springapex_news_archive_view();
    if ($view['type'] !== 'all') {
        return;
    }
    $archive_url = (string) get_post_type_archive_link('spring_news');
    if ($view['page'] > 1) {
        printf("<link rel=\"prev\" href=\"%s\">\n", esc_url(springapex_news_page_url($archive_url, $view['page'] - 1)));
    }
    if ($view['page'] < $view['total_pages']) {
        printf("<link rel=\"next\" href=\"%s\">\n", esc_url(springapex_news_page_url($archive_url, $view['page'] + 1)));
    }
}, 2);
