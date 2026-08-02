<?php
if (!defined('ABSPATH')) {
    exit;
}
$title = $args['title'] ?? "Let's Build What's Next.\nTogether.";
$text  = $args['text'] ?? 'Have a challenge in mind? Our team is ready to help you find the right spring solution.';
$cta   = $args['cta'] ?? ['label' => 'Get a Quote', 'href' => '/contact/?intent=quote'];
$class = trim((string) ($args['class'] ?? ''));
?>
<section class="section cta-band<?php echo $class !== '' ? ' ' . esc_attr($class) : ''; ?>">
  <div class="container container-wide cta-band-inner" data-reveal="up">
    <h2 class="cta-title"><?php echo nl2br(esc_html($title)); ?></h2>
    <div class="cta-copy">
      <p><?php echo esc_html($text); ?></p>
      <a class="btn btn-primary" href="<?php echo esc_url(springapex_url($cta['href'])); ?>">
        <?php echo esc_html($cta['label']); ?>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
  </div>
</section>
