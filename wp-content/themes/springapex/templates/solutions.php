<?php
if (!defined('ABSPATH')) {
    exit;
}
$hero = springapex_get('solutions.hero', []);
$solutions_cta = springapex_get('solutions.cta', []);
$solutions_cta_image = springapex_image_value_available($solutions_cta['image'] ?? '')
    ? $solutions_cta['image']
    : 'solutions-cta-springs-v5.png';
$solutions = springapex_solutions();
?>
<?php
get_template_part('parts/inner-hero', null, [
    'variant' => 'solutions',
    'title' => $hero['title'] ?? 'Solutions',
    'subtitle' => $hero['subtitle'] ?? '',
    'image' => $hero['image'] ?? 'solutions-hero-v2.png',
    'mobile_image' => $hero['mobile_image'] ?? 'solutions-hero-mobile-v1.png',
    'image_width' => 1890,
    'image_height' => 830,
]);

get_template_part('parts/solutions-subnav', null, ['active' => 'industries']);
?>

<section class="section solutions-grid-section" id="solutions-grid">
  <div class="container container-wide">
    <?php
    // 瀑布流懒加载：首屏直出 6 张（SEO/无 JS 时全部可见——html.js 才隐藏
    // 延迟卡片），其余随滚动按批显现；卡片图片本身是 loading=lazy，
    // 真正的字节开销只发生在卡片显现前后。
    $initial_count = 6;
    $lazy_batch = 6;
    $has_deferred = count($solutions) > $initial_count;
    ?>
    <div class="solutions-grid" data-reveal-group<?php echo $has_deferred ? ' data-lazy-batch="' . esc_attr((string) $lazy_batch) . '"' : ''; ?>>
      <?php foreach ($solutions as $index => $solution) : ?>
        <a class="solution-card<?php echo $index >= $initial_count ? ' is-deferred' : ''; ?>" id="<?php echo esc_attr((string) $solution['slug']); ?>" href="<?php echo esc_url(springapex_solution_url($solution)); ?>">
          <span class="solution-media">
            <?php echo springapex_image($solution['image'] ?? '', (string) $solution['title'], [
                'width' => 1200,
                'height' => 900,
                'sizes' => '(max-width: 700px) 100vw, (max-width: 980px) 50vw, 33vw',
            ]); ?>
          </span>
          <span class="solution-meta">
            <span>
              <strong><?php echo esc_html((string) $solution['title']); ?></strong>
              <small><?php echo esc_html((string) ($solution['tagline'] ?? '')); ?></small>
            </span>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </span>
        </a>
      <?php endforeach; ?>
    </div>
    <?php if ($has_deferred) : ?>
      <?php // 真实按钮而非纯哨兵：读屏器虚拟光标不做 focus，display:none 的
            // 延迟卡片在其浏览模式下不可达——渐进披露控件是无障碍的标准形态，
            // 键盘/读屏用户经此显式展开；指针用户仍有滚动自动显现。 ?>
      <button type="button" class="btn btn-outline solutions-grid__more" data-lazy-sentinel>
        <?php esc_html_e('Show more industries', 'springapex'); ?>
      </button>
    <?php endif; ?>
  </div>
</section>

<section class="section bottom-cta-panel solutions-cta">
  <div class="container container-wide bottom-cta-inner" data-reveal="up">
    <div class="bottom-cta-copy">
      <h2><?php echo esc_html((string) ($solutions_cta['title'] ?? '')); ?></h2>
      <p><?php echo esc_html((string) ($solutions_cta['text'] ?? '')); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url((string) ($solutions_cta['action_href'] ?? ''))); ?>">
        <?php echo esc_html((string) ($solutions_cta['action_label'] ?? '')); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="bottom-cta-media">
      <?php echo springapex_image($solutions_cta_image, (string) ($solutions_cta['image_alt'] ?? ''), [
          'width' => 1600,
          'height' => 560,
          'sizes' => '(max-width: 760px) 100vw, 55vw',
      ]); ?>
    </div>
  </div>
</section>
