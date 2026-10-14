<?php
/**
 * 主题自带图片的 <img src>：有 WebP 副本时指向 1200px WebP（或整张 WebP），不再给爬虫 1–2.5 MB 的 PNG 原图。
 */

declare(strict_types=1);

define('ABSPATH', __DIR__ . '/');
define('SPRINGAPEX_DIR', dirname(__DIR__));
define('SPRINGAPEX_URI', 'https://www.norenspring.com/theme');

function esc_attr(string $text): string
{
    return htmlspecialchars($text, ENT_QUOTES);
}
function esc_url(string $url): string
{
    return $url;
}

require dirname(__DIR__) . '/inc/helpers.php';

function check(bool $ok, string $message): void
{
    if (!$ok) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

$base = 'https://www.norenspring.com/theme/assets/images/';
check(
    springapex_static_image_fallback_src('solutions/automotive-seat-latch-v1.png') === $base . 'solutions/automotive-seat-latch-v1-1200.webp',
    'PNG with responsive WebP uses the 1200px WebP'
);
check(
    springapex_static_image_fallback_src('contact/contact-world-map-v1.png') === $base . 'contact/contact-world-map-v1.webp',
    'PNG with only a full WebP uses it'
);
check(
    springapex_static_image_fallback_src('/no-such-image.png') === $base . 'no-such-image.png',
    'missing WebP keeps the original file'
);
check(
    springapex_static_image_fallback_src('../solutions/automotive-seat-latch-v1.png') === $base . '../solutions/automotive-seat-latch-v1.png',
    'paths with .. are not resolved to WebP'
);

$html = springapex_image('solutions/automotive-seat-latch-v1.png', 'Seat latch');
check(str_contains($html, 'src="' . $base . 'solutions/automotive-seat-latch-v1-1200.webp"'), 'img src is WebP: ' . $html);
check(str_contains($html, 'type="image/avif"'), 'AVIF source kept');

echo "static-image-fallback: OK\n";
