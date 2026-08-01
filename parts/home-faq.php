<?php
if (!defined('ABSPATH')) {
    exit;
}

$faq_items = springapex_get('home_faq', []);
if (!$faq_items) {
    return;
}
?>
<section class="section sa-home-faq">
  <div class="container container-wide">
    <div class="section-head row-between">
      <div class="sa-section-intro">
        <p class="section-kicker"><?php esc_html_e('COMMON QUESTIONS', 'springapex'); ?></p>
        <h2><?php esc_html_e('Answers Before You Ask', 'springapex'); ?></h2>
        <p class="sa-section-bridge"><?php esc_html_e('First-time buyers and procurement engineers both ask these. Good questions deserve clear answers.', 'springapex'); ?></p>
      </div>
      <a class="text-link" href="<?php echo esc_url(springapex_url('/contact/')); ?>">
        <?php esc_html_e('Still have questions?', 'springapex'); ?> <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
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
