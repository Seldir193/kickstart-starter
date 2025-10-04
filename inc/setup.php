<?php
/* -------------------------------------------------------
 * Theme Setup
 * -----------------------------------------------------*/
add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('custom-logo', [
    'height'      => 80,
    'width'       => 80,
    'flex-height' => true,
    'flex-width'  => true,
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'kickstart-starter'),
  ]);
});


