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
      <p class="section-kicker"><?php esc_html_e('PRIVACY', 'springapex'); ?></p>
      <h1><?php esc_html_e('Privacy Policy', 'springapex'); ?></h1>
      <p><?php esc_html_e('How we handle information submitted through the NorenSpring website.', 'springapex'); ?></p>
      <span><?php esc_html_e('Last updated: August 3, 2026', 'springapex'); ?></span>
    </div>
  </header>

  <div class="container sa-legal-container sa-legal-layout">
    <nav class="sa-legal-toc" aria-label="<?php esc_attr_e('Page contents', 'springapex'); ?>">
      <strong><?php esc_html_e('On this page', 'springapex'); ?></strong>
      <a href="#information"><?php esc_html_e('Information we collect', 'springapex'); ?></a>
      <a href="#use"><?php esc_html_e('How we use it', 'springapex'); ?></a>
      <a href="#storage"><?php esc_html_e('Storage and security', 'springapex'); ?></a>
      <a href="#choices"><?php esc_html_e('Your choices', 'springapex'); ?></a>
    </nav>

    <article class="sa-legal-content">
      <section id="information">
        <h2><?php esc_html_e('Information we collect', 'springapex'); ?></h2>
        <p><?php esc_html_e('When you send an inquiry, we may receive your name, email address, company, country, phone number, inquiry type, spring dimensions, quantity, operating environment, message and other project details. If enabled, you may also attach a drawing or technical file.', 'springapex'); ?></p>
        <p><?php esc_html_e('The site also uses limited technical information, including hashed IP and email values, to prevent repeated or abusive submissions.', 'springapex'); ?></p>
      </section>

      <section id="use">
        <h2><?php esc_html_e('How we use information', 'springapex'); ?></h2>
        <p><?php esc_html_e('We use submitted information to review your application, respond to your request, prepare engineering feedback or a quotation, and maintain an internal record of the inquiry.', 'springapex'); ?></p>
        <p><?php esc_html_e('Inquiry details are stored as a private WordPress record and emailed to the responsible NorenSpring team. We do not sell inquiry information.', 'springapex'); ?></p>
      </section>

      <section id="storage">
        <h2><?php esc_html_e('Storage, sharing and retention', 'springapex'); ?></h2>
        <p><?php esc_html_e('Uploaded drawings are accepted only when protected private-file storage is available. We limit access to people and service providers who need the information to operate the website or respond to your project.', 'springapex'); ?></p>
        <p><?php esc_html_e('Information may be processed in China and in locations used by our hosting or email providers. We keep it only as long as reasonably needed for the inquiry, business records, security and legal obligations.', 'springapex'); ?></p>
      </section>

      <section>
        <h2><?php esc_html_e('Cookies and website services', 'springapex'); ?></h2>
        <p><?php esc_html_e('We use Google Tag Manager to load measurement and marketing tags, such as Google Analytics. These services may set cookies or read similar storage to record how visitors reach and use the site, and the resulting data may be processed by Google outside your country.', 'springapex'); ?></p>
        <p><?php esc_html_e('WordPress, hosting and security services may also use cookies or similar technical storage that are necessary to deliver and protect the website.', 'springapex'); ?></p>
        <p><?php esc_html_e('You can refuse these measurement tags through your browser cookie settings, a tracking blocker, or the Google Analytics opt-out browser add-on. The inquiry forms keep working without them.', 'springapex'); ?></p>
      </section>

      <section id="choices">
        <h2><?php esc_html_e('Your choices and contact', 'springapex'); ?></h2>
        <p><?php esc_html_e('You may ask us to review, correct or delete information you submitted, subject to records we must retain for security, contractual or legal reasons.', 'springapex'); ?></p>
        <p><?php esc_html_e('For privacy questions, contact:', 'springapex'); ?> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a>.</p>
      </section>
    </article>
  </div>
</div>
