<?php
if (!defined('ABSPATH')) {
    exit;
}

$brand = springapex_brand();
$email = (string) ($brand['email'] ?? 'victoria@springapex.cn');
?>
<div class="sa-legal-page">
  <header class="sa-legal-hero">
    <div class="container sa-legal-container">
      <p class="section-kicker"><?php esc_html_e('WEBSITE TERMS', 'springapex'); ?></p>
      <h1><?php esc_html_e('Terms of Use', 'springapex'); ?></h1>
      <p><?php esc_html_e('The practical terms that apply when you browse this website or submit a project inquiry.', 'springapex'); ?></p>
      <span><?php esc_html_e('Last updated: August 3, 2026', 'springapex'); ?></span>
    </div>
  </header>

  <div class="container sa-legal-container sa-legal-layout">
    <nav class="sa-legal-toc" aria-label="<?php esc_attr_e('Page contents', 'springapex'); ?>">
      <strong><?php esc_html_e('On this page', 'springapex'); ?></strong>
      <a href="#information"><?php esc_html_e('Website information', 'springapex'); ?></a>
      <a href="#quotations"><?php esc_html_e('Quotations and specifications', 'springapex'); ?></a>
      <a href="#use"><?php esc_html_e('Acceptable use', 'springapex'); ?></a>
      <a href="#contact"><?php esc_html_e('Contact', 'springapex'); ?></a>
    </nav>

    <article class="sa-legal-content">
      <section id="information">
        <h2><?php esc_html_e('Website information', 'springapex'); ?></h2>
        <p><?php esc_html_e('This website provides general information about ApexSpring products, manufacturing capabilities and engineering support. Content may be updated as products, processes and documentation change.', 'springapex'); ?></p>
      </section>

      <section id="quotations">
        <h2><?php esc_html_e('Quotations and specifications', 'springapex'); ?></h2>
        <p><?php esc_html_e('Website content, examples and downloadable materials are not a binding quotation or production specification. Price, lead time, material, tolerance, testing and delivery requirements are confirmed separately for each order.', 'springapex'); ?></p>
        <p><?php esc_html_e('You are responsible for providing accurate drawings, operating conditions and approval requirements. Product suitability must be confirmed for the final application before production or use.', 'springapex'); ?></p>
      </section>

      <section>
        <h2><?php esc_html_e('Intellectual property', 'springapex'); ?></h2>
        <p><?php esc_html_e('The website design, text, graphics and product materials are owned by ApexSpring or used with permission. You may use them to evaluate our products and services, but may not republish or commercially exploit them without permission.', 'springapex'); ?></p>
      </section>

      <section id="use">
        <h2><?php esc_html_e('Acceptable use', 'springapex'); ?></h2>
        <p><?php esc_html_e('Do not misuse the website, interfere with its operation, attempt unauthorized access, submit unlawful material or upload files that contain malicious code.', 'springapex'); ?></p>
      </section>

      <section>
        <h2><?php esc_html_e('Availability and external services', 'springapex'); ?></h2>
        <p><?php esc_html_e('We may change or temporarily suspend website features. Links, hosting, email and other external services are operated by their respective providers and may have separate terms.', 'springapex'); ?></p>
      </section>

      <section>
        <h2><?php esc_html_e('Liability boundary', 'springapex'); ?></h2>
        <p><?php esc_html_e('To the extent permitted by applicable law, ApexSpring is not responsible for losses arising from use of general website information.', 'springapex'); ?></p>
      </section>

      <section id="contact">
        <h2><?php esc_html_e('Contact', 'springapex'); ?></h2>
        <p><?php esc_html_e('Questions about these terms can be sent to:', 'springapex'); ?> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>.</p>
      </section>
    </article>
  </div>
</div>
