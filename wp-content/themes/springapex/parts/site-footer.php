<?php
if (!defined('ABSPATH')) {
    exit;
}
$nav = springapex_navigation_items('footer');
$brand = springapex_brand();
$products = springapex_products();
$route = springapex_current_route();
$show_footer_cta = $route === 'home';
$privacy_url = springapex_url('/privacy/');
$terms_url = springapex_url('/terms/');
$sitemap_url = springapex_url('/sitemap/');
$company_name = rtrim((string) ($brand['company'] ?? 'NorenSpring'), ". \t\n\r\0\x0B");
$phone = trim((string) ($brand['phone'] ?? ''));
$phone_href = preg_replace('/[^0-9+]/', '', $phone);
$youtube_id = trim((string) springapex_get('home.hero.video_cta.youtube_id', ''));
$footer_socials = [
    ['key' => 'youtube', 'label' => 'YouTube', 'url' => $youtube_id !== '' ? 'https://www.youtube.com/watch?v=' . rawurlencode($youtube_id) : ''],
    ['key' => 'facebook', 'label' => 'Facebook', 'url' => (string) ($brand['facebook'] ?? '')],
    ['key' => 'x', 'label' => 'X', 'url' => (string) ($brand['x'] ?? '')],
    ['key' => 'instagram', 'label' => 'Instagram', 'url' => (string) ($brand['instagram'] ?? '')],
    ['key' => 'tiktok', 'label' => 'TikTok', 'url' => (string) ($brand['tiktok'] ?? '')],
];
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
        <a class="brand brand-footer brand--image" href="<?php echo esc_url(springapex_url('/')); ?>" aria-label="<?php echo esc_attr($brand['name'] ?? 'NorenSpring'); ?> home">
          <?php $footer_logo = springapex_logo(); ?>
          <?php echo springapex_image($footer_logo !== '' ? $footer_logo : 'logo-site-norenspring-v1.png', (string) ($brand['name'] ?? 'NorenSpring'), [
              'class' => 'site-logo site-logo--footer',
              'width' => 916,
              'height' => 529,
              'sizes' => '180px',
          ]); ?>
        </a>
        <p><?php esc_html_e('Precision spring engineering and manufacturing, from early design support through dependable production.', 'springapex'); ?></p>
        <nav class="footer-socials" aria-label="<?php esc_attr_e('Social media', 'springapex'); ?>">
          <?php foreach ($footer_socials as $social) : ?>
            <?php if ($social['url'] !== '') : ?>
              <a class="footer-social footer-social--<?php echo esc_attr($social['key']); ?>" href="<?php echo esc_url($social['url']); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr($social['label']); ?>">
                <?php echo springapex_icon((string) $social['key'], 'icon'); ?>
              </a>
            <?php else : ?>
              <span class="footer-social footer-social--<?php echo esc_attr($social['key']); ?> is-pending" aria-label="<?php echo esc_attr($social['label'] . ' profile link pending'); ?>" role="img">
                <?php echo springapex_icon((string) $social['key'], 'icon'); ?>
              </span>
            <?php endif; ?>
          <?php endforeach; ?>
        </nav>
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
          <li><a href="<?php echo esc_url(springapex_url('/resources/')); ?>"><?php esc_html_e('Download Center', 'springapex'); ?></a></li>
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
      </div>
    </div>

    <div class="container container-wide footer-bottom">
      <p>© <?php echo esc_html(date('Y')); ?> <?php echo esc_html($company_name); ?>. <?php esc_html_e('All rights reserved.', 'springapex'); ?></p>
      <nav class="footer-legal" aria-label="<?php esc_attr_e('Legal navigation', 'springapex'); ?>">
        <a href="<?php echo esc_url($privacy_url); ?>"><?php esc_html_e('Privacy Policy', 'springapex'); ?></a>
        <a href="<?php echo esc_url($terms_url); ?>"><?php esc_html_e('Terms of Use', 'springapex'); ?></a>
        <a href="<?php echo esc_url($sitemap_url); ?>"><?php esc_html_e('Sitemap', 'springapex'); ?></a>
      </nav>
    </div>
  </div>
</footer>
