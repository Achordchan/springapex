<?php
if (!defined('ABSPATH')) {
    exit;
}

$items = array_merge(
    springapex_get('home_faq', []),
    [
        [
            'question' => 'What is the expected lead time?',
            'answer' => 'Lead time depends on the spring type, material, tooling, quantity and approval requirements. We confirm the production and sample schedule during quotation.',
        ],
        [
            'question' => 'How are payment and delivery terms confirmed?',
            'answer' => 'Commercial terms, delivery address and documentation requirements are confirmed with the quotation before production starts.',
        ],
        [
            'question' => 'What support is available after delivery?',
            'answer' => 'After-delivery questions can be routed through the project contact for drawing questions, quality documents, repeat orders and application feedback.',
        ],
    ],
);
?>
<section class="section sa-site-faq">
  <div class="container container-wide sa-faq__layout">
    <div class="sa-section-intro">
      <p class="section-kicker"><?php esc_html_e('BEFORE YOU START', 'springapex'); ?></p>
      <h2><?php esc_html_e('Common purchasing questions.', 'springapex'); ?></h2>
      <p class="sa-section-lede"><?php esc_html_e('The final terms are confirmed for each project, but these answers explain how the next step works.', 'springapex'); ?></p>
    </div>
    <div class="sa-faq__list">
      <?php foreach ($items as $item) : ?>
        <details>
          <summary><?php echo esc_html((string) ($item['question'] ?? '')); ?></summary>
          <p><?php echo esc_html((string) ($item['answer'] ?? '')); ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>
