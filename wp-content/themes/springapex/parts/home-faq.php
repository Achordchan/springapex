<?php
if (!defined('ABSPATH')) {
    exit;
}

$faq_args = is_array($args ?? null) ? $args : [];
$faq_items = is_array($faq_args['items'] ?? null)
    ? $faq_args['items']
    : springapex_get('home_faq', []);
if (!$faq_items) {
    return;
}

$section_id = sanitize_key((string) ($faq_args['id'] ?? ''));
$is_product_section = !empty($faq_args['product_section']);
$kicker = (string) ($faq_args['kicker'] ?? __('COMMON QUESTIONS', 'springapex'));
$title = (string) ($faq_args['title'] ?? __('Answers Before You Ask', 'springapex'));
$bridge = (string) ($faq_args['bridge'] ?? __('Clear answers to common purchasing questions.', 'springapex'));
$link_label = (string) ($faq_args['link_label'] ?? __('Still have questions?', 'springapex'));
$link_url = (string) ($faq_args['link_url'] ?? springapex_url('/contact/'));
?>
<section class="section sa-home-faq"<?php echo $section_id !== '' ? ' id="' . esc_attr($section_id) . '"' : ''; ?><?php echo $is_product_section ? ' data-product-section' : ''; ?>>
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php echo esc_html($kicker); ?></p>
        <h2><?php echo esc_html($title); ?></h2>
        <p class="sa-section-bridge"><?php echo esc_html($bridge); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url($link_url); ?>">
        <?php echo esc_html($link_label); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
    </div>
    <div class="sa-home-faq__grid" data-reveal-group>
      <?php foreach ($faq_items as $item) : ?>
        <details class="sa-faq-item">
          <summary>
            <span class="sa-faq-item__icon"><?php echo springapex_icon('chat', 'icon'); ?></span>
            <span class="sa-faq-item__question"><?php echo esc_html((string) ($item['question'] ?? '')); ?></span>
            <span class="sa-faq-item__toggle"><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?></span>
          </summary>
          <div class="sa-faq-item__answer">
            <p><?php echo esc_html((string) ($item['answer'] ?? '')); ?></p>
          </div>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
