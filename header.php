<?php ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
  <div class="container header-inner">
    <div class="brand">
      <?php if (has_custom_logo()) { the_custom_logo(); } ?>
      <a class="site-title" href="<?php echo esc_url(home_url('/')); ?>">
        <?php bloginfo('name'); ?>
      </a>
    </div>

    <nav class="main-nav" aria-label="<?php esc_attr_e('Primary', 'kickstart-starter'); ?>">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container'      => false,
          'menu_class'     => 'menu',
          'fallback_cb'    => false,
        ]);
      ?>
      <div class="ks-programs" data-mega>
        <?php get_template_part('template-parts/mega-programs'); ?>
      </div>
    </nav>

    <div class="header-actions">
      <div
        class="language-switcher"
        data-i18n-base="<?php echo esc_attr(trailingslashit(get_stylesheet_directory_uri()) . 'assets/i18n'); ?>"
        data-fallback-language="en"
      >
        <button
          type="button"
          class="language-switcher__trigger"
          aria-haspopup="menu"
          aria-expanded="false"
          aria-label="Change language"
           data-i18n-attr="aria-label"
  data-i18n="language.label"
        >
          
        <span class="language-switcher__label">English</span>

          <span class="ks-selectbox__chevron" aria-hidden="true">
  <img
    src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/header/select-caret.svg'); ?>"
    alt=""
    width="14"
    height="14"
  >
</span>
        </button>

        <div class="language-switcher__menu" role="menu" hidden>
          <button
            type="button"
            class="language-switcher__item"
            role="menuitemradio"
            aria-checked="false"
            data-language="de"
             data-i18n="language.de"
          >
            Deutsch
          </button>

          <button
            type="button"
            class="language-switcher__item is-active"
            role="menuitemradio"
            aria-checked="true"
            data-language="en"
             data-i18n="language.en"
          >
            English
          </button>

          <button
            type="button"
            class="language-switcher__item"
            role="menuitemradio"
            aria-checked="false"
            data-language="tr"
             data-i18n="language.tr"
          >
            Türkçe
          </button>
        </div>
      </div>
    </div>
  </div>
</header>

<main class="site-main">