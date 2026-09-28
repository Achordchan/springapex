<?php
/**
 * 结构化数据（JSON-LD）、社交分享标签（Open Graph / Twitter Card）和列表页 canonical。
 *
 * 三者共用同一份「当前页面」描述 springapex_schema_page()：网址、标题、描述、配图、
 * 面包屑。标题和描述直接取 inc/seo.php 的 TDK，后台改了 SEO 标题/描述，这里跟着变；
 * 公司信息取 网站内容 → 品牌与联系方式（springapex_brand()）。
 *
 * 只写页面上真实存在的信息。产品按图定制、没有公开价格和评价，所以 Product 不写
 * offers / aggregateRating：Google 因此不给产品富结果，但伪造价格或评分有人工处罚
 * 风险。装了 SEO 插件（Yoast 等）时整段不输出，避免两套标记打架。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/**
 * 四个列表页：文章类型 ⇒ 面包屑里的名称（与列表页 H1 一致）。
 *
 * @return array<string, string>
 */
function springapex_schema_archives(): array
{
    return [
        'spring_product' => 'Products',
        'spring_solution' => 'Solutions',
        'spring_case' => 'Case Studies',
        'spring_news' => 'News',
    ];
}

/** 标题、描述里的 HTML 标签和实体（get_the_title() 会给出 R&#038;D）还原成纯文本。 */
function springapex_schema_text(string $text): string
{
    $text = html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string) preg_replace('/\s+/u', ' ', $text));
}

/**
 * 分享配图只收 JPG / PNG / GIF：LinkedIn 等平台不认 WebP，给了等于没给，
 * 不如让调用方退到下一张候选图。
 */
function springapex_schema_is_shareable_image(string $url): bool
{
    $path = (string) parse_url($url, PHP_URL_PATH);
    return (bool) preg_match('/\.(?:jpe?g|png|gif)$/i', $path);
}

/**
 * 主题里各种图片取值（附件 ID、['id' => …, 'file' => …]、主题自带文件名、绝对网址）
 * 解析成网址和尺寸。附件被删时退到 file。
 *
 * @return array{url: string, width: int, height: int}|null
 */
function springapex_schema_image(mixed $image, bool $shareable_only = false): ?array
{
    $attachment_id = 0;
    $file = '';
    if (is_array($image)) {
        $attachment_id = (int) ($image['id'] ?? 0);
        $file = trim((string) ($image['file'] ?? ''));
    } elseif (is_int($image) || (is_string($image) && $image !== '' && ctype_digit($image))) {
        $attachment_id = (int) $image;
    } elseif (is_string($image)) {
        $file = trim($image);
    }

    $resolved = null;
    if ($attachment_id > 0 && function_exists('wp_get_attachment_image_src')) {
        $src = wp_get_attachment_image_src($attachment_id, 'full');
        if (is_array($src) && !empty($src[0])) {
            $resolved = ['url' => (string) $src[0], 'width' => (int) ($src[1] ?? 0), 'height' => (int) ($src[2] ?? 0)];
        }
    }
    if ($resolved === null && $file !== '') {
        $url = springapex_file_url($file, 'assets/images/');
        if ($url !== '') {
            $resolved = ['url' => $url, 'width' => 0, 'height' => 0];
        }
    }

    if ($resolved === null || ($shareable_only && !springapex_schema_is_shareable_image($resolved['url']))) {
        return null;
    }
    return $resolved;
}

/**
 * 页头显示的 logo；后台没设时是主题自带的文字标。
 *
 * @return array{url: string, width: int, height: int}|null
 */
function springapex_schema_logo(): ?array
{
    $logo = springapex_logo();
    return springapex_schema_image($logo !== '' ? $logo : 'logo-site-norenspring-v1.png', false);
}

/**
 * 单篇内容在详情页上显示的主图：与模板取同一个字段（特色图像，没有时是种子
 * 图片），而不是只看特色图像。
 */
function springapex_schema_post_image_value(WP_Post $post): mixed
{
    $item = match ($post->post_type) {
        'spring_product' => springapex_product_for_view($post),
        'spring_solution' => springapex_solution_detail_from_post($post),
        'spring_case' => springapex_case_from_post($post),
        'spring_news' => springapex_news_from_post($post),
        default => null,
    };
    if (is_array($item) && isset($item['image'])) {
        return $item['image'];
    }

    return has_post_thumbnail($post) ? (int) get_post_thumbnail_id($post) : null;
}

/** 列表的第 N 页（N > 1）网址；固定链接关闭时用 ?paged=N。 */
function springapex_schema_paged_url(string $url, int $paged): string
{
    if ($paged <= 1 || $url === '') {
        return $url;
    }
    if ((string) get_option('permalink_structure') === '') {
        return add_query_arg('paged', $paged, $url);
    }

    return trailingslashit($url) . user_trailingslashit('page/' . $paged, 'paged');
}

/**
 * 当前页面的共享描述；搜索页、404、作者/分类归档等不输出页面级标记的地方返回 null。
 *
 * @return array{
 *     url: string,
 *     title: string,
 *     description: string,
 *     type: string,
 *     og_type: string,
 *     image: array{url: string, width: int, height: int}|null,
 *     image_source: string,
 *     share_image: array{url: string, width: int, height: int}|null,
 *     trail: list<array{name: string, url: string}>,
 *     post: WP_Post|null
 * }|null
 */
function springapex_schema_page(): ?array
{
    if (is_admin() || is_feed() || is_404() || is_search() || springapex_seo_external_plugin_active()) {
        return null;
    }

    $home = home_url('/');
    $archives = springapex_schema_archives();
    $route = springapex_current_route();
    $post = null;
    $own_image = null;
    $type = 'WebPage';
    $trail = [['name' => 'Home', 'url' => $home]];

    if (is_singular()) {
        $post = get_queried_object();
        if (!$post instanceof WP_Post) {
            return null;
        }
        $url = (string) wp_get_canonical_url($post);
        if ($url === '') {
            return null;
        }
        if (isset($archives[$post->post_type])) {
            $trail[] = ['name' => $archives[$post->post_type], 'url' => (string) get_post_type_archive_link($post->post_type)];
        }
        $trail[] = ['name' => springapex_schema_text(get_the_title($post)), 'url' => $url];
        $own_image = springapex_schema_image(springapex_schema_post_image_value($post), false);
        if ($route === 'about') {
            $type = 'AboutPage';
        } elseif ($route === 'contact') {
            $type = 'ContactPage';
        }
    } elseif (is_post_type_archive(array_keys($archives))) {
        $post_type = get_queried_object();
        $name = $post_type instanceof WP_Post_Type ? $post_type->name : '';
        if (!isset($archives[$name])) {
            return null;
        }
        // 筛选参数（/news/?news_type=…）指回列表本身；翻页各自保留，第 2 页
        // 的内容不是第 1 页的重复。
        $archive_url = (string) get_post_type_archive_link($name);
        $url = springapex_schema_paged_url($archive_url, (int) get_query_var('paged'));
        $trail[] = ['name' => $archives[$name], 'url' => $archive_url];
        $type = 'CollectionPage';
    } elseif (is_front_page()) {
        $url = $home;
    } elseif (is_home()) {
        // 阅读设置里单独指定的「文章页」（如 /blog/），不是首页。
        $posts_page = (int) get_option('page_for_posts');
        $posts_url = $posts_page > 0 ? (string) get_permalink($posts_page) : '';
        if ($posts_url === '') {
            return null;
        }
        $url = springapex_schema_paged_url($posts_url, (int) get_query_var('paged'));
        $trail[] = ['name' => springapex_schema_text(get_the_title($posts_page)), 'url' => $posts_url];
    } else {
        return null;
    }

    if (is_front_page()) {
        $trail = [];
    }
    // 页面主图（结构化数据用，任何格式）：内容自己的图 > 页面横幅。产品、文章
    // 节点只认内容自己的图。
    $route_images = springapex_route_hero_images();
    $route_image = isset($route_images[$route]) ? springapex_schema_image($route_images[$route], false) : null;
    $image = $own_image ?? $route_image;
    $image_source = $own_image !== null ? 'own' : ($route_image !== null ? 'route' : '');

    // 分享卡片另选：同样的顺序里挑第一张社交平台认的格式，都不行就用 logo。
    $share_image = null;
    foreach ([$own_image, $route_image] as $candidate) {
        if ($candidate !== null && springapex_schema_is_shareable_image($candidate['url'])) {
            $share_image = $candidate;
            break;
        }
    }
    $share_image ??= springapex_schema_logo();

    $seo = springapex_seo_current_values();

    return [
        'url' => $url,
        'title' => springapex_schema_text(wp_get_document_title()),
        'description' => springapex_schema_text($seo['description']),
        'type' => $type,
        'og_type' => $post instanceof WP_Post && $post->post_type === 'spring_news' ? 'article' : 'website',
        'image' => $image,
        'image_source' => $image_source,
        'share_image' => $share_image,
        'trail' => $trail,
        'post' => $post,
    ];
}

/**
 * @param list<array{name: string, url: string}> $trail
 * @return array<string, mixed>|null 少于两级时没有意义，返回 null
 */
function springapex_schema_breadcrumb(string $id, array $trail): ?array
{
    $items = [];
    foreach ($trail as $crumb) {
        $name = trim((string) ($crumb['name'] ?? ''));
        $url = trim((string) ($crumb['url'] ?? ''));
        if ($name === '' || $url === '') {
            continue;
        }
        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $name,
            'item' => $url,
        ];
    }

    return count($items) >= 2
        ? ['@type' => 'BreadcrumbList', '@id' => $id, 'itemListElement' => $items]
        : null;
}

/**
 * @param array<string, mixed> $brand springapex_brand()
 * @param array{url: string, width: int, height: int}|null $logo
 * @return array<string, mixed>
 */
function springapex_schema_organization(array $brand, string $home, ?array $logo, string $contact_url): array
{
    $name = trim((string) ($brand['name'] ?? '')) ?: 'NorenSpring';
    $organization = [
        '@type' => 'Organization',
        '@id' => $home . '#organization',
        'name' => $name,
        // 站内和展会新闻里出现过的叫法，连成同一个实体。
        'alternateName' => ['Noren Spring', 'Apex Spring'],
        'url' => $home,
        'foundingDate' => '2001',
    ];

    $company = trim((string) ($brand['company'] ?? ''));
    if ($company !== '') {
        $organization['legalName'] = $company;
    }
    if ($logo !== null) {
        $organization['logo'] = array_filter([
            '@type' => 'ImageObject',
            '@id' => $home . '#logo',
            'url' => $logo['url'],
            'contentUrl' => $logo['url'],
            'width' => $logo['width'] ?: null,
            'height' => $logo['height'] ?: null,
        ]);
        $organization['image'] = ['@id' => $home . '#logo'];
    }

    $email = trim((string) ($brand['email'] ?? ''));
    $phone = trim((string) ($brand['phone'] ?? ''));
    $address = trim((string) ($brand['address'] ?? ''));
    if ($email !== '') {
        $organization['email'] = $email;
    }
    if ($phone !== '') {
        $organization['telephone'] = $phone;
    }
    if ($address !== '') {
        $organization['address'] = $address;
    }
    if ($email !== '' || $phone !== '') {
        $organization['contactPoint'] = array_filter([
            '@type' => 'ContactPoint',
            'contactType' => 'sales',
            'email' => $email,
            'telephone' => $phone,
            'url' => $contact_url,
            'availableLanguage' => 'English',
        ]);
    }

    $same_as = [];
    foreach (['linkedin', 'facebook', 'x', 'instagram', 'tiktok'] as $network) {
        $profile = trim((string) ($brand[$network] ?? ''));
        if (preg_match('#^https?://#i', $profile)) {
            $same_as[] = $profile;
        }
    }
    if ($same_as) {
        $organization['sameAs'] = $same_as;
    }

    return $organization;
}

/** @return array<string, mixed> */
function springapex_schema_product_node(WP_Post $post, array $page, string $organization_id): array
{
    $brand_name = trim((string) (springapex_brand()['name'] ?? '')) ?: 'NorenSpring';
    return array_filter([
        '@type' => 'Product',
        '@id' => $page['url'] . '#product',
        'name' => springapex_schema_text(get_the_title($post)),
        'url' => $page['url'],
        'description' => $page['description'],
        'image' => $page['image_source'] === 'own' ? ['@id' => $page['url'] . '#primaryimage'] : null,
        'category' => 'Springs',
        'brand' => ['@type' => 'Brand', 'name' => $brand_name],
        'manufacturer' => ['@id' => $organization_id],
        'mainEntityOfPage' => ['@id' => $page['url'] . '#webpage'],
    ]);
}

/** @return array<string, mixed> */
function springapex_schema_article_node(WP_Post $post, array $page, string $organization_id, string $language): array
{
    $news = function_exists('springapex_news_from_post') ? springapex_news_from_post($post) : [];
    // 行业科普文章算博客文章，展会、公司动态算新闻稿。
    $type = ($news['news_type'] ?? '') === 'industry-news' ? 'BlogPosting' : 'NewsArticle';

    $author = ['@id' => $organization_id];
    $profile = is_array($news['author'] ?? null) ? $news['author'] : null;
    if ($profile !== null && trim((string) ($profile['name'] ?? '')) !== '') {
        // 与详情页右侧作者卡片一致。
        $author = array_filter([
            '@type' => 'Person',
            'name' => springapex_schema_text((string) $profile['name']),
            'jobTitle' => springapex_schema_text((string) ($profile['role'] ?? '')),
            'worksFor' => ['@id' => $organization_id],
        ]);
    }

    return array_filter([
        '@type' => $type,
        '@id' => $page['url'] . '#article',
        'headline' => springapex_schema_text(get_the_title($post)),
        'description' => $page['description'],
        'image' => $page['image_source'] === 'own' ? ['@id' => $page['url'] . '#primaryimage'] : null,
        'datePublished' => (string) get_the_date('c', $post),
        'dateModified' => (string) get_the_modified_date('c', $post),
        'author' => $author,
        'publisher' => ['@id' => $organization_id],
        'articleSection' => springapex_schema_text((string) ($news['category'] ?? '')),
        'inLanguage' => $language,
        'mainEntityOfPage' => ['@id' => $page['url'] . '#webpage'],
        'isPartOf' => ['@id' => $page['url'] . '#webpage'],
    ]);
}

/**
 * 整页一个 @graph：Organization、WebSite 全站都有；有页面描述时再加
 * WebPage、配图、面包屑，以及产品页的 Product、新闻页的文章节点。
 *
 * @return list<array<string, mixed>>
 */
function springapex_schema_graph(?array $page): array
{
    $home = home_url('/');
    $organization_id = $home . '#organization';
    $website_id = $home . '#website';
    $language = (string) get_bloginfo('language');
    $brand = springapex_brand();

    $graph = [
        springapex_schema_organization($brand, $home, springapex_schema_logo(), home_url('/contact/')),
        array_filter([
            '@type' => 'WebSite',
            '@id' => $website_id,
            'url' => $home,
            'name' => trim((string) ($brand['name'] ?? '')) ?: 'NorenSpring',
            'inLanguage' => $language,
            'publisher' => ['@id' => $organization_id],
        ]),
    ];

    if ($page === null) {
        return $graph;
    }

    $webpage = [
        '@type' => $page['type'],
        '@id' => $page['url'] . '#webpage',
        'url' => $page['url'],
        'name' => $page['title'],
        'isPartOf' => ['@id' => $website_id],
        'inLanguage' => $language,
    ];
    if ($page['description'] !== '') {
        $webpage['description'] = $page['description'];
    }
    if (is_front_page() || $page['type'] === 'AboutPage') {
        $webpage['about'] = ['@id' => $organization_id];
    }
    if ($page['image'] !== null) {
        $graph[] = array_filter([
            '@type' => 'ImageObject',
            '@id' => $page['url'] . '#primaryimage',
            'url' => $page['image']['url'],
            'contentUrl' => $page['image']['url'],
            'width' => $page['image']['width'] ?: null,
            'height' => $page['image']['height'] ?: null,
        ]);
        $webpage['primaryImageOfPage'] = ['@id' => $page['url'] . '#primaryimage'];
    }
    $breadcrumb = springapex_schema_breadcrumb($page['url'] . '#breadcrumb', $page['trail']);
    if ($breadcrumb !== null) {
        $webpage['breadcrumb'] = ['@id' => $breadcrumb['@id']];
    }
    if ($page['post'] instanceof WP_Post) {
        $webpage['datePublished'] = (string) get_the_date('c', $page['post']);
        $webpage['dateModified'] = (string) get_the_modified_date('c', $page['post']);
    }
    $graph[] = $webpage;
    if ($breadcrumb !== null) {
        $graph[] = $breadcrumb;
    }

    $post = $page['post'];
    if ($post instanceof WP_Post && $post->post_type === 'spring_product') {
        $graph[] = springapex_schema_product_node($post, $page, $organization_id);
    } elseif ($post instanceof WP_Post && $post->post_type === 'spring_news') {
        $graph[] = springapex_schema_article_node($post, $page, $organization_id, $language);
    }

    return $graph;
}

/** @param array<string, mixed>|list<mixed> $data */
function springapex_schema_json(array $data): string
{
    // JSON_HEX_TAG：内容里即使出现 </script> 也不会提前结束标签。
    return (string) wp_json_encode(
        $data,
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP
    );
}

add_action('wp_head', static function (): void {
    if (is_admin() || is_feed() || springapex_seo_external_plugin_active()) {
        return;
    }

    $page = springapex_schema_page();

    // WordPress 只给单篇内容输出 canonical，四个列表页由主题补上。
    if ($page !== null && $page['type'] === 'CollectionPage') {
        printf("<link rel=\"canonical\" href=\"%s\">\n", esc_url($page['url']));
    }

    if ($page !== null) {
        $site_name = trim((string) (springapex_brand()['name'] ?? '')) ?: 'NorenSpring';
        $tags = [
            ['property', 'og:locale', (string) get_locale()],
            ['property', 'og:site_name', $site_name],
            ['property', 'og:type', $page['og_type']],
            ['property', 'og:title', $page['title']],
            ['property', 'og:description', $page['description']],
            ['property', 'og:url', $page['url']],
        ];
        if ($page['share_image'] !== null) {
            $tags[] = ['property', 'og:image', $page['share_image']['url']];
            if ($page['share_image']['width'] > 0 && $page['share_image']['height'] > 0) {
                $tags[] = ['property', 'og:image:width', (string) $page['share_image']['width']];
                $tags[] = ['property', 'og:image:height', (string) $page['share_image']['height']];
            }
            $tags[] = ['property', 'og:image:alt', $page['title']];
        }
        if ($page['og_type'] === 'article' && $page['post'] instanceof WP_Post) {
            $tags[] = ['property', 'article:published_time', (string) get_the_date('c', $page['post'])];
            $tags[] = ['property', 'article:modified_time', (string) get_the_modified_date('c', $page['post'])];
        }
        $tags[] = ['name', 'twitter:card', $page['share_image'] !== null ? 'summary_large_image' : 'summary'];
        $tags[] = ['name', 'twitter:title', $page['title']];
        $tags[] = ['name', 'twitter:description', $page['description']];
        if ($page['share_image'] !== null) {
            $tags[] = ['name', 'twitter:image', $page['share_image']['url']];
        }

        foreach ($tags as [$attribute, $key, $value]) {
            if ($value === '') {
                continue;
            }
            $value = in_array($key, ['og:url', 'og:image', 'twitter:image'], true) ? esc_url($value) : esc_attr($value);
            printf("<meta %s=\"%s\" content=\"%s\">\n", $attribute, esc_attr($key), $value);
        }
    }

    if (is_404() || is_search()) {
        return;
    }
    printf(
        "<script type=\"application/ld+json\">%s</script>\n",
        springapex_schema_json(['@context' => 'https://schema.org', '@graph' => springapex_schema_graph($page)])
    );
}, 2);
