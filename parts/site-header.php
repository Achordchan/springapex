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
      <button class="icon-btn site-search__toggle" type="button" data-search-toggle aria-label="<?php esc_attr_e('Search the site', 'springapex'); ?>">
        <?php echo springapex_icon('search', 'icon'); ?>
      </button>
      <div class="site-search-overlay" data-search-overlay role="dialog" aria-modal="true" aria-labelledby="site-search-title" hidden>
        <div class="site-search-overlay__backdrop" data-search-backdrop></div>
        <div class="site-search-overlay__content">
          <div class="site-search-overlay__head">
            <div>
              <p class="site-search-overlay__kicker"><?php esc_html_e('SITE SEARCH', 'springapex'); ?></p>
              <h2 id="site-search-title"><?php esc_html_e('Find the right starting point.', 'springapex'); ?></h2>
            </div>
            <button class="site-search-overlay__close" type="button" data-search-close aria-label="<?php esc_attr_e('Close search', 'springapex'); ?>">
              <?php echo springapex_icon('close', 'icon'); ?>
            </button>
          </div>
          <form class="site-search-overlay__form" action="<?php echo esc_url(springapex_url('/search/')); ?>" method="get" role="search">
            <span class="site-search-overlay__icon"><?php echo springapex_icon('search', 'icon'); ?></span>
            <label class="sr-only" for="overlay-search-input"><?php esc_html_e('Search products, industries and resources', 'springapex'); ?></label>
            <input id="overlay-search-input" class="site-search-overlay__input" type="search" name="s" placeholder="<?php esc_attr_e('Search products, industries, resources...', 'springapex'); ?>" autocomplete="off">
            <button class="site-search-overlay__submit" type="submit" aria-label="<?php esc_attr_e('Submit search', 'springapex'); ?>">
              <span><?php esc_html_e('Search', 'springapex'); ?></span><?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
            </button>
          </form>
          <div class="site-search-overlay__hints">
            <span class="site-search-overlay__hints-label"><?php esc_html_e('Popular:', 'springapex'); ?></span>
            <a href="<?php echo esc_url(springapex_url('/search/?sa_page=search&s=compression')); ?>" class="site-search-overlay__tag">Compression</a>
            <a href="<?php echo esc_url(springapex_url('/search/?sa_page=search&s=automotive')); ?>" class="site-search-overlay__tag">Automotive</a>
            <a href="<?php echo esc_url(springapex_url('/search/?sa_page=search&s=material')); ?>" class="site-search-overlay__tag">Material</a>
            <a href="<?php echo esc_url(springapex_url('/search/?sa_page=search&s=quality')); ?>" class="site-search-overlay__tag">Quality</a>
          </div>
        </div>
      </div>
      <details class="language-switcher" data-language-switcher>
        <summary aria-label="<?php esc_attr_e('Choose language', 'springapex'); ?>">
          <?php echo springapex_icon('globe', 'icon icon-sm'); ?>
          <span>EN</span>
          <?php echo springapex_icon('arrow-right', 'icon icon-sm language-switcher__arrow'); ?>
        </summary>
        <div class="language-switcher__menu">
          <div class="language-switcher__head">
            <strong><?php esc_html_e('Languages', 'springapex'); ?></strong>
            <small><?php esc_html_e('Display preview', 'springapex'); ?></small>
          </div>
          <div class="language-switcher__grid" aria-label="<?php esc_attr_e('Available language placeholders', 'springapex'); ?>">
            <span class="is-current"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/gb.svg')); ?>" alt="" aria-hidden="true">English</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/de.svg')); ?>" alt="" aria-hidden="true">Deutsch</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/fr.svg')); ?>" alt="" aria-hidden="true">Français</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/es.svg')); ?>" alt="" aria-hidden="true">Español</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/jp.svg')); ?>" alt="" aria-hidden="true">日本語</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/kr.svg')); ?>" alt="" aria-hidden="true">한국어</span>
            <span aria-disabled="true"><img class="language-switcher__flag" src="<?php echo esc_url(springapex_asset('assets/icons/flags/cn.svg')); ?>" alt="" aria-hidden="true">中文</span>
          </div>
        </div>
      </details>
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
