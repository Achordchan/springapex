<?php
/**
 * 站点地图补充列表页。
 *
 * WP 核心站点地图只列文章本身，不含自定义文章类型的列表页（/products/、/solutions/、/case-studies/），
 * Google 因此迟迟发现不了这几个列表页。这里注册一个 archives 提供者，
 * 输出 /wp-sitemap-archives-1.xml，lastmod 取该类型最近一次修改时间。
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
    exit;
}

/** @return list<string> */
function springapex_sitemap_archive_post_types(): array
{
    return ['spring_product', 'spring_solution', 'spring_case'];
}

/** @return list<array{loc:string,lastmod?:string}> */
function springapex_sitemap_archive_entries(): array
{
    $entries = [];
    foreach (springapex_sitemap_archive_post_types() as $post_type) {
        $link = get_post_type_archive_link($post_type);
        if (!is_string($link) || $link === '') {
            continue;
        }
        $entry = ['loc' => $link];
        $latest = get_posts([
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'orderby' => 'modified',
            'order' => 'DESC',
            'fields' => 'ids',
            'no_found_rows' => true,
        ]);
        if ($latest !== []) {
            $modified = get_post_modified_time(DATE_W3C, true, (int) $latest[0]);
            if (is_string($modified) && $modified !== '') {
                $entry['lastmod'] = $modified;
            }
        }
        $entries[] = $entry;
    }
    return $entries;
}

add_action('wp_sitemaps_init', static function (): void {
    if (!class_exists('WP_Sitemaps_Provider')) {
        return;
    }

    if (!class_exists('Springapex_Sitemaps_Archives')) {
        final class Springapex_Sitemaps_Archives extends WP_Sitemaps_Provider
        {
            public function __construct()
            {
                $this->name = 'archives';
                $this->object_type = 'archive';
            }

            /** @return list<array{loc:string,lastmod?:string}> */
            public function get_url_list($page_num, $object_subtype = '')
            {
                return (int) $page_num === 1 ? springapex_sitemap_archive_entries() : [];
            }

            public function get_max_num_pages($object_subtype = '')
            {
                return 1;
            }
        }
    }

    wp_register_sitemap_provider('archives', new Springapex_Sitemaps_Archives());
});
