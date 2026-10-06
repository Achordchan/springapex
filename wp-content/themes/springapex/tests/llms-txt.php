<?php
/**
 * /llms.txt：只接管这一个地址，列表从线上数据生成，介绍可由 option 覆盖，回应带 noindex。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');

$GLOBALS['springapex_test_options'] = [];
$GLOBALS['springapex_test_headers'] = [];

function add_action(string $hook, callable|string $callback, int $priority = 10, int $args = 1): void {}
function register_setting(string $group, string $name, array $args = []): void {}
function get_option(string $name, mixed $default = false): mixed
{
    return $GLOBALS['springapex_test_options'][$name] ?? $default;
}
function wp_strip_all_tags(string $text): string
{
    return strip_tags($text);
}
function esc_url_raw(string $url): string
{
    return $url;
}
function home_url(string $path = ''): string
{
    return 'https://www.norenspring.com' . $path;
}
function get_the_title(WP_Post $post): string
{
    return $post->post_title;
}
function get_permalink(WP_Post $post): string
{
    return 'https://www.norenspring.com/' . $post->post_type . '/' . $post->post_name . '/';
}
function get_posts(array $args): array
{
    $GLOBALS['springapex_test_queries'][] = $args;
    return match ($args['post_type']) {
        'spring_product' => [new WP_Post('spring_product', 'torsion-springs', 'Torsion Springs', 'Repeatable [rotational] force.')],
        'spring_news' => [new WP_Post('spring_news', 'spring-tines-guide', 'Spring Tines &amp; Balers', '')],
        default => [],
    };
}
function status_header(int $code): void
{
    $GLOBALS['springapex_test_headers'][] = 'status ' . $code;
}

final class WP_Post
{
    public function __construct(
        public string $post_type,
        public string $post_name,
        public string $post_title,
        public string $description,
    ) {}
}

final class WP
{
    public function __construct(public string $request) {}
}

function springapex_brand(): array
{
    return ['name' => 'NorenSpring', 'email' => 'victoria@springapex.cn', 'phone' => '', 'address' => 'Liuji Town, Xuzhou'];
}
function springapex_seo_route_definitions(): array
{
    return [
        'home' => ['path' => '/'],
        'about' => ['path' => '/about/'],
        'contact' => ['path' => '/contact/'],
    ];
}
function springapex_seo_route_values(string $route): array
{
    return [
        'title' => ucfirst($route) . ' NorenSpring | NorenSpring',
        'description' => $route . ' description',
        'keywords' => '',
    ];
}
function springapex_seo_post_values(WP_Post $post): array
{
    return ['title' => '', 'description' => $post->description, 'keywords' => ''];
}

require __DIR__ . '/../inc/llms-txt.php';

function springapex_test_assert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$body = springapex_llms_txt_body();
springapex_test_assert(str_starts_with($body, "# NorenSpring\n\n> home description\n\nNorenSpring (Xuzhou APEX"), 'title, summary, default intro: ' . substr($body, 0, 120));
springapex_test_assert(str_contains($body, "## Products\n\n- [Torsion Springs](https://www.norenspring.com/spring_product/torsion-springs/): Repeatable (rotational) force."), 'product link, brackets escaped');
springapex_test_assert(str_contains($body, '- [Spring Tines & Balers](https://www.norenspring.com/spring_news/spring-tines-guide/)' . "\n"), 'entity decoded, empty description has no colon');
springapex_test_assert(!str_contains($body, '## Industries'), 'empty section omitted');
springapex_test_assert(str_contains($body, '- [About NorenSpring](https://www.norenspring.com/about/): about description'), 'route link drops title suffix');
springapex_test_assert(str_contains($body, "- Email: victoria@springapex.cn\n- Address: Liuji Town, Xuzhou\n"), 'contact lines, empty phone skipped');
springapex_test_assert(!str_contains($body, 'Phone:'), 'empty phone skipped');
foreach ($GLOBALS['springapex_test_queries'] as $query) {
    springapex_test_assert($query['post_status'] === 'publish' && $query['has_password'] === false, 'only public posts');
}

$GLOBALS['springapex_test_options'][SPRINGAPEX_LLMS_TXT_INTRO_OPTION] = "Custom intro.\n";
springapex_test_assert(str_contains(springapex_llms_txt_body(), "> home description\n\nCustom intro.\n\n## Products"), 'option overrides intro');

// 其他地址不接管。
springapex_serve_llms_txt(new WP('llms.txt/extra'));
springapex_serve_llms_txt(new WP(''));
springapex_test_assert($GLOBALS['springapex_test_headers'] === [], 'other requests untouched');

echo "llms-txt: ok\n";
