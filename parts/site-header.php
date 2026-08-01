<?php
if (!defined('ABSPATH')) {
    exit;
}
$nav = springapex_navigation_items();
$brand = springapex_brand();
?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'springapex'); ?></a>
<header class="site-header" data-header>
  <div class="container container-wide header-inner">
    <div class="brand-wrap">
      <?php if (!defined('SPRINGAPEX_PREVIEW') && function_exists('has_custom_logo') && has_custom_logo()) : ?>
        <?php echo get_custom_logo(); ?>
      <?php else : ?>
        <a class="brand" href="<?php echo esc_url(springapex_url('/')); ?>" aria-label="<?php echo esc_attr($brand['name'] ?? 'SpringApex'); ?> home">
          <span class="brand-name"><?php echo esc_html($brand['name'] ?? 'APEX SPRING'); ?></span>
          <span class="brand-tag"><?php echo esc_html($brand['tagline'] ?? 'SPRING MANUFACTURING EXPERT'); ?></span>
        </a>
      <?php endif; ?>
    </div>

    <nav class="nav-desktop" aria-label="<?php esc_attr_e('Primary navigation', 'springapex'); ?>">
      <ul>
        <?php foreach ($nav as $item) :
            $href = (string) ($item['href'] ?? '/');
            $href = springapex_navigation_href($href);
            $active = !empty($item['current']) || springapex_nav_is_active((string) ($item['slug'] ?? ''));
        ?>
          <li><a href="<?php echo esc_url($href); ?>" class="<?php echo $active ? 'is-active' : ''; ?>"><?php echo esc_html((string) ($item['label'] ?? '')); ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="header-actions">
      <details class="site-search" data-site-search>
        <summary class="icon-btn site-search__toggle" aria-label="<?php esc_attr_e('Search the site', 'springapex'); ?>">
          <?php echo springapex_icon('search', 'icon'); ?>
        </summary>
        <form class="site-search__form" action="<?php echo esc_url(springapex_url('/search/')); ?>" method="get" role="search">
          <input type="hidden" name="sa_page" value="search">
          <label class="sr-only" for="site-search-input"><?php esc_html_e('Search products, industries and resources', 'springapex'); ?></label>
          <input id="site-search-input" type="search" name="s" placeholder="<?php esc_attr_e('Search products, industries and resources', 'springapex'); ?>" autocomplete="off">
          <button class="icon-btn" type="submit" aria-label="<?php esc_attr_e('Submit search', 'springapex'); ?>">
            <?php echo springapex_icon('arrow-right', 'icon'); ?>
          </button>
        </form>
      </details>
      <a class="btn btn-primary btn-sm quote-btn" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
        <?php esc_html_e('Get a Quote', 'springapex'); ?>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
      <button class="icon-btn menu-toggle" type="button" data-menu-toggle aria-label="<?php esc_attr_e('Open menu', 'springapex'); ?>" aria-expanded="false" aria-controls="mobile-navigation">
        <?php echo springapex_icon('menu', 'icon menu-icon-open'); ?>
        <?php echo springapex_icon('close', 'icon menu-icon-close'); ?>
      </button>
    </div>
  </div>

  <nav class="nav-mobile" id="mobile-navigation" data-mobile-nav aria-label="<?php esc_attr_e('Mobile navigation', 'springapex'); ?>" hidden>
    <div class="container">
      <ul class="nav-mobile__links">
        <?php foreach ($nav as $index => $item) :
            $href = (string) ($item['href'] ?? '/');
            $href = springapex_navigation_href($href);
        ?>
          <li><a href="<?php echo esc_url($href); ?>"><span class="nav-mobile__number"><?php echo esc_html(str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT)); ?></span><?php echo esc_html((string) ($item['label'] ?? '')); ?></a></li>
        <?php endforeach; ?>
      </ul>
      <div class="nav-mobile__footer">
        <a class="nav-mobile__cta" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
          <?php esc_html_e('Get a Quote', 'springapex'); ?>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
        </a>
        <div class="nav-mobile__contact">
          <a href="mailto:<?php echo esc_attr($brand['email'] ?? ''); ?>"><?php echo springapex_icon('mail', 'icon icon-sm'); ?><?php echo esc_html($brand['email'] ?? ''); ?></a>
          <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', (string) ($brand['whatsapp'] ?? $brand['phone'] ?? ''))); ?>"><?php echo springapex_icon('chat', 'icon icon-sm'); ?><?php esc_html_e('WhatsApp', 'springapex'); ?></a>
        </div>
      </div>
    </div>
  </nav>
</header>
