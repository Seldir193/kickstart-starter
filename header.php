
















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


</header>

<main class="site-main">
