<?php
if (!defined('ABSPATH')) {
    exit;
}
$nav = springapex_navigation_items('footer');
$brand = springapex_brand();
$products = springapex_products();
$route = springapex_current_route();
$show_footer_cta = $route === 'home';
$privacy_url = !defined('SPRINGAPEX_PREVIEW') && function_exists('get_privacy_policy_url') ? get_privacy_policy_url() : '';
$sitemap_url = defined('SPRINGAPEX_PREVIEW') ? '' : springapex_url('/wp-sitemap.xml');
$phone = trim((string) ($brand['phone'] ?? ''));
$phone_href = preg_replace('/[^0-9+]/', '', $phone);
?>
<footer class="site-footer footer-universal">
  <?php if ($show_footer_cta) : ?>
  <div class="footer-cta">
    <div class="container container-wide footer-cta-inner">
      <div class="footer-cta-heading">
        <span class="footer-kicker"><?php esc_html_e('Ready to start?', 'springapex'); ?></span>
        <h2><?php esc_html_e("Send us your drawing. We will handle the rest.", 'springapex'); ?></h2>
      </div>
      <div class="footer-cta-copy">
        <p><?php esc_html_e('Upload your drawing or describe your application. Our team will review it and respond with engineering feedback or a quotation within 24 hours.', 'springapex'); ?></p>
        <div class="footer-cta-actions">
          <a class="btn btn-primary" href="<?php echo esc_url(springapex_url('/contact/?intent=drawing')); ?>">
            <?php esc_html_e('Upload Your Drawing', 'springapex'); ?>
            <?php echo springapex_icon('upload', 'icon icon-sm'); ?>
          </a>
          <a class="footer-inline-link" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
            <?php esc_html_e('Request a Quote', 'springapex'); ?>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="footer-directory">
    <div class="container container-wide footer-directory-grid">
      <div class="footer-brand-column">
        <a class="brand brand-footer" href="<?php echo esc_url(springapex_url('/')); ?>">
          <span class="brand-name"><?php echo esc_html($brand['name'] ?? 'APEX SPRING'); ?></span>
          <span class="brand-tag"><?php echo esc_html($brand['tagline'] ?? 'SPRING MANUFACTURING EXPERT'); ?></span>
        </a>
        <p><?php esc_html_e('Precision spring engineering and manufacturing, from early design support through dependable production.', 'springapex'); ?></p>
      </div>

      <nav class="footer-column" aria-label="<?php esc_attr_e('Product navigation', 'springapex'); ?>">
        <h3><?php esc_html_e('Products', 'springapex'); ?></h3>
        <ul>
          <?php foreach ($products as $product) : ?>
            <li><a href="<?php echo esc_url(springapex_product_url($product)); ?>"><?php echo esc_html((string) ($product['title'] ?? '')); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </nav>

      <nav class="footer-column" aria-label="<?php esc_attr_e('Company navigation', 'springapex'); ?>">
        <h3><?php esc_html_e('Explore', 'springapex'); ?></h3>
        <ul>
          <?php foreach ($nav as $item) :
              $href = springapex_navigation_href((string) ($item['href'] ?? '/'));
          ?>
            <li><a href="<?php echo esc_url($href); ?>"><?php echo esc_html((string) ($item['label'] ?? '')); ?></a></li>
          <?php endforeach; ?>
          <li><a href="<?php echo esc_url(springapex_url('/resources/')); ?>"><?php esc_html_e('Resources', 'springapex'); ?></a></li>
        </ul>
      </nav>

      <div class="footer-column footer-contact-column">
        <h3><?php esc_html_e('Contact', 'springapex'); ?></h3>
        <address>
          <?php if (!empty($brand['email'])) : ?><a href="mailto:<?php echo esc_attr($brand['email']); ?>"><?php echo esc_html($brand['email']); ?></a><?php endif; ?>
          <?php if ($phone && $phone_href) : ?><a href="tel:<?php echo esc_attr($phone_href); ?>"><?php echo esc_html($phone); ?></a><?php endif; ?>
          <?php if (!empty($brand['address'])) : ?><span><?php echo esc_html($brand['address']); ?></span><?php endif; ?>
          <?php if (!empty($brand['hours'])) : ?><span><?php echo esc_html($brand['hours']); ?></span><?php endif; ?>
        </address>
        <?php if (!empty($brand['linkedin'])) : ?>
          <a class="footer-social-link" href="<?php echo esc_url($brand['linkedin']); ?>" target="_blank" rel="noopener noreferrer">
            <?php echo springapex_icon('linkedin', 'icon icon-sm'); ?>
            <span><?php esc_html_e('LinkedIn', 'springapex'); ?></span>
          </a>
        <?php endif; ?>
        <a class="footer-social-link" href="<?php echo esc_url(springapex_url('/contact/?intent=feedback')); ?>">
          <?php echo springapex_icon('chat', 'icon icon-sm'); ?>
          <span><?php esc_html_e('Share site feedback', 'springapex'); ?></span>
        </a>
      </div>
    </div>

    <div class="container container-wide footer-bottom">
      <p>© <?php echo esc_html(date('Y')); ?> <?php echo esc_html($brand['company'] ?? 'SpringApex'); ?>. <?php esc_html_e('All rights reserved.', 'springapex'); ?></p>
      <nav class="footer-legal" aria-label="<?php esc_attr_e('Legal navigation', 'springapex'); ?>">
        <?php if ($privacy_url) : ?><a href="<?php echo esc_url($privacy_url); ?>"><?php esc_html_e('Privacy Policy', 'springapex'); ?></a><?php endif; ?>
        <?php if ($sitemap_url) : ?><a href="<?php echo esc_url($sitemap_url); ?>"><?php esc_html_e('Sitemap', 'springapex'); ?></a><?php endif; ?>
        <a href="<?php echo esc_url(springapex_url('/contact/')); ?>"><?php esc_html_e('Contact', 'springapex'); ?></a>
      </nav>
    </div>
  </div>
</footer>
