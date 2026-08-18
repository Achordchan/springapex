<?php
if (!defined('ABSPATH')) {
    exit;
}

$hero_args = is_array($args ?? null) ? $args : [];
$variant = sanitize_key((string) ($hero_args['variant'] ?? 'default')) ?: 'default';
$title = (string) ($hero_args['title'] ?? '');
$subtitle = (string) ($hero_args['subtitle'] ?? '');
$subtitle_text = trim((string) preg_replace('/\s+/u', ' ', $subtitle));
$image = $hero_args['image'] ?? '';
$mobile_image = $hero_args['mobile_image'] ?? '';
$image_alt = (string) ($hero_args['image_alt'] ?? '');
$image_sizes = (string) ($hero_args['image_sizes'] ?? '100vw');
$image_width = (int) ($hero_args['image_width'] ?? 1890);
$image_height = (int) ($hero_args['image_height'] ?? 830);
$ctas = is_array($hero_args['ctas'] ?? null) ? $hero_args['ctas'] : [];
$breadcrumb = is_array($hero_args['breadcrumb'] ?? null) ? $hero_args['breadcrumb'] : [];
$staged_media = !empty($hero_args['staged_media']);
?>
<section class="hero inner-hero inner-hero--<?php echo esc_attr($variant); ?>">
  <div class="inner-hero-media" aria-hidden="true">
    <?php if ($staged_media) : ?>
      <div class="inner-hero-product-stage">
    <?php endif; ?>
    <?php echo springapex_image($image, $image_alt, [
        'width' => $image_width,
        'height' => $image_height,
        'loading' => 'eager',
        'fetchpriority' => 'high',
        'sizes' => $image_sizes,
        'mobile_image' => $mobile_image,
    ]); ?>
    <?php if ($staged_media) : ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="container container-wide inner-hero-inner">
    <?php if ($breadcrumb) : ?>
      <nav class="inner-hero-breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'springapex'); ?>">
        <?php foreach ($breadcrumb as $index => $item) : ?>
          <?php if ($index > 0) : ?><span class="sep" aria-hidden="true">›</span><?php endif; ?>
          <?php if (!empty($item['href'])) : ?>
            <a href="<?php echo esc_url(springapex_navigation_href((string) $item['href'])); ?>"><?php echo esc_html((string) ($item['label'] ?? '')); ?></a>
          <?php else : ?>
            <span class="current" aria-current="page"><?php echo esc_html((string) ($item['label'] ?? '')); ?></span>
          <?php endif; ?>
        <?php endforeach; ?>
      </nav>
    <?php endif; ?>

    <div class="hero-copy inner-hero-copy">
      <h1 class="display"><?php echo esc_html($title); ?></h1>
      <?php if ($subtitle_text !== '') : ?>
        <p class="lede"><?php echo esc_html($subtitle_text); ?></p>
      <?php endif; ?>
      <?php if ($ctas) : ?>
        <div class="hero-actions">
          <?php foreach ($ctas as $cta) : ?>
            <?php
            $style = (string) ($cta['style'] ?? 'primary');
            $button_class = in_array($style, ['primary', 'ghost', 'outline'], true) ? 'btn-' . $style : 'btn-primary';
            $icon = (string) ($cta['icon'] ?? 'arrow-right');
            ?>
            <a class="btn <?php echo esc_attr($button_class); ?>" href="<?php echo esc_url(springapex_navigation_href((string) ($cta['href'] ?? '/'))); ?>">
              <?php echo springapex_icon($icon, 'icon icon-sm'); ?>
              <?php echo esc_html((string) ($cta['label'] ?? 'Learn More')); ?>
            </a>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>
