<?php
if (!defined('ABSPATH')) {
    exit;
}
$nav = springapex_navigation_items();
$brand = springapex_brand();
$products = springapex_products();
$product_menu = springapex_get('products.mega_menu', []);
$product_menu = is_array($product_menu) ? $product_menu : [];
$product_menu_feature_image = $product_menu['feature_image'] ?? 'product-compression-menu-v2.png';
?>
<a class="skip-link" href="#main"><?php esc_html_e('Skip to content', 'springapex'); ?></a>
<header class="site-header" data-header>
  <div class="container container-wide header-inner">
    <div class="brand-wrap">
      <?php if (!defined('SPRINGAPEX_PREVIEW') && function_exists('has_custom_logo') && has_custom_logo()) : ?>
        <?php echo get_custom_logo(); ?>
      <?php else : ?>
        <a class="brand brand--image" href="<?php echo esc_url(springapex_url('/')); ?>" aria-label="<?php echo esc_attr($brand['name'] ?? 'NorenSpring'); ?> home">
          <?php echo springapex_image('logo-site.png', (string) ($brand['name'] ?? 'NorenSpring'), [
              'class' => 'site-logo site-logo--header',
              'loading' => 'eager',
              'fetchpriority' => 'high',
              'width' => 916,
              'height' => 529,
              'sizes' => '110px',
          ]); ?>
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
          <?php
          $is_products = (string) ($item['slug'] ?? '') === 'products';
          $children = is_array($item['children'] ?? null) ? $item['children'] : [];
          $dropdown_id = 'nav-dropdown-' . sanitize_key((string) ($item['slug'] ?? ''));
          ?>
          <li class="nav-desktop__item<?php echo $is_products ? ' nav-desktop__item--products' : ''; ?><?php echo $children ? ' nav-desktop__item--has-children' : ''; ?>">
            <a
              href="<?php echo esc_url($href); ?>"
              class="<?php echo $active ? 'is-active' : ''; ?>"
              <?php if ($is_products && $products) : ?>
                data-product-menu-trigger
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="products-mega-menu"
              <?php elseif ($children) : ?>
                data-nav-dropdown-trigger
                aria-haspopup="true"
                aria-expanded="false"
                aria-controls="<?php echo esc_attr($dropdown_id); ?>"
              <?php endif; ?>
            ><?php echo esc_html((string) ($item['label'] ?? '')); ?></a>
            <?php if ($children) : ?>
              <div class="nav-dropdown" id="<?php echo esc_attr($dropdown_id); ?>" data-nav-dropdown hidden>
                <ul>
                  <?php foreach ($children as $child) :
                      $child_href = springapex_navigation_href((string) ($child['href'] ?? '/'));
                  ?>
                    <li>
                      <a href="<?php echo esc_url($child_href); ?>">
                        <?php echo esc_html((string) ($child['label'] ?? '')); ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <div class="header-actions">
      <button class="icon-btn site-search__toggle" type="button" data-search-toggle aria-label="<?php esc_attr_e('Search the site', 'springapex'); ?>">
        <?php echo springapex_icon('search', 'icon'); ?>
        <span class="site-search__label"><?php esc_html_e('Search', 'springapex'); ?></span>
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
            <input id="overlay-search-input" class="site-search-overlay__input" type="search" name="s" placeholder="<?php esc_attr_e('Search products...', 'springapex'); ?>" autocomplete="off">
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

  <?php if ($products) : ?>
    <section class="products-mega-menu" id="products-mega-menu" data-product-menu-panel aria-label="<?php esc_attr_e('Product categories', 'springapex'); ?>" hidden>
      <div class="products-mega-menu__intro">
        <div class="products-mega-menu__intro-copy">
          <p class="products-mega-menu__kicker"><?php esc_html_e('PRODUCTS', 'springapex'); ?></p>
          <h2><?php esc_html_e('Engineered for the way your part carries load.', 'springapex'); ?></h2>
          <p><?php esc_html_e('Explore our core spring families and engineered solutions.', 'springapex'); ?></p>
          <a class="products-mega-menu__all" href="<?php echo esc_url(springapex_url('/products/')); ?>">
            <span><?php esc_html_e('View complete range', 'springapex'); ?></span>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
          </a>
        </div>
        <?php if ($product_menu_feature_image !== '') : ?>
          <div class="products-mega-menu__feature-media">
            <?php
            echo springapex_image(
                $product_menu_feature_image,
                __('NorenSpring precision spring product showcase', 'springapex'),
                [
                    'class' => 'products-mega-menu__feature-image',
                    'sizes' => '340px',
                    'width' => 560,
                    'height' => 560,
                ]
            );
            ?>
          </div>
        <?php endif; ?>
      </div>

      <div class="products-mega-menu__index">
        <?php foreach ($products as $product) :
            $title = (string) ($product['title'] ?? '');
            $image = $product['featured_image'] ?? $product['category_image'] ?? $product['image'] ?? '';
            $summary = (string) ($product['desc'] ?? '');
        ?>
          <a class="products-mega-menu__item" href="<?php echo esc_url(springapex_product_url($product)); ?>">
            <span class="products-mega-menu__thumb">
              <?php
              echo springapex_image($image, $title, [
                  'class' => 'products-mega-menu__image',
                  'sizes' => '92px',
                  'width' => 180,
                  'height' => 180,
              ]);
              ?>
            </span>
            <span class="products-mega-menu__item-copy">
              <strong><?php echo esc_html($title); ?></strong>
              <small><?php echo esc_html($summary); ?></small>
            </span>
            <?php echo springapex_icon('arrow-right', 'icon icon-sm products-mega-menu__arrow'); ?>
          </a>
        <?php endforeach; ?>
      </div>
    </section>
  <?php endif; ?>
</header>

<nav class="nav-mobile" id="mobile-navigation" data-mobile-nav aria-label="<?php esc_attr_e('Mobile navigation', 'springapex'); ?>" hidden>
  <div class="container">
    <ul class="nav-mobile__links">
      <?php foreach ($nav as $index => $item) :
          $href = (string) ($item['href'] ?? '/');
          $href = springapex_navigation_href($href);
          $active = !empty($item['current']) || springapex_nav_is_active((string) ($item['slug'] ?? ''));
      ?>
        <?php
        $mobile_children = is_array($item['children'] ?? null) ? $item['children'] : [];
        $mobile_slug = sanitize_key((string) ($item['slug'] ?? ''));
        ?>
        <?php if ((string) ($item['slug'] ?? '') === 'products' && $products) : ?>
          <li class="nav-mobile__products-item">
            <button class="nav-mobile__products-toggle<?php echo $active ? ' is-active' : ''; ?>" type="button" data-mobile-products-toggle aria-expanded="false" aria-controls="mobile-products-menu">
              <span><?php echo esc_html((string) ($item['label'] ?? '')); ?></span>
              <?php echo springapex_icon('arrow-right', 'icon icon-sm nav-mobile__products-arrow'); ?>
            </button>
            <div class="nav-mobile__products-menu" id="mobile-products-menu" data-mobile-products-panel hidden>
              <a class="nav-mobile__products-all" href="<?php echo esc_url($href); ?>">
                <span><?php esc_html_e('View all products', 'springapex'); ?></span>
                <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
              </a>
              <div class="nav-mobile__products-grid">
                <?php foreach ($products as $product) : ?>
                  <a href="<?php echo esc_url(springapex_product_url($product)); ?>">
                    <span><?php echo esc_html((string) ($product['title'] ?? '')); ?></span>
                    <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </li>
        <?php elseif ($mobile_children) : ?>
          <li class="nav-mobile__products-item">
            <button class="nav-mobile__products-toggle<?php echo $active ? ' is-active' : ''; ?>" type="button" data-mobile-submenu-toggle aria-expanded="false" aria-controls="mobile-submenu-<?php echo esc_attr($mobile_slug); ?>">
              <span><?php echo esc_html((string) ($item['label'] ?? '')); ?></span>
              <?php echo springapex_icon('arrow-right', 'icon icon-sm nav-mobile__products-arrow'); ?>
            </button>
            <div class="nav-mobile__products-menu" id="mobile-submenu-<?php echo esc_attr($mobile_slug); ?>" data-mobile-submenu-panel hidden>
              <?php foreach ($mobile_children as $child) :
                  $child_href = springapex_navigation_href((string) ($child['href'] ?? '/'));
              ?>
                <a href="<?php echo esc_url($child_href); ?>">
                  <span><?php echo esc_html((string) ($child['label'] ?? '')); ?></span>
                  <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
                </a>
              <?php endforeach; ?>
            </div>
          </li>
        <?php else : ?>
          <li>
            <a href="<?php echo esc_url($href); ?>" class="<?php echo $active ? 'is-active' : ''; ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>>
              <span><?php echo esc_html((string) ($item['label'] ?? '')); ?></span>
              <?php echo springapex_icon('arrow-right', 'icon icon-sm nav-mobile__arrow'); ?>
            </a>
          </li>
        <?php endif; ?>
      <?php endforeach; ?>
    </ul>
    <div class="nav-mobile__footer">
      <a class="nav-mobile__cta" href="<?php echo esc_url(springapex_url('/contact/?intent=quote')); ?>">
        <?php esc_html_e('Get a Quote', 'springapex'); ?>
        <?php echo springapex_icon('arrow-right', 'icon icon-sm'); ?>
      </a>
      <div class="nav-mobile__contact">
        <a href="mailto:<?php echo esc_attr($brand['email'] ?? ''); ?>"><?php echo springapex_icon('mail', 'icon icon-sm'); ?><?php esc_html_e('Email', 'springapex'); ?></a>
        <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9]/', '', (string) ($brand['whatsapp'] ?? $brand['phone'] ?? ''))); ?>"><?php echo springapex_icon('whatsapp', 'icon icon-sm'); ?><?php esc_html_e('WhatsApp', 'springapex'); ?></a>
      </div>
    </div>
  </div>
</nav>
