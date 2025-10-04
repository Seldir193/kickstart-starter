<?php
/* -------------------------------------------------------
 * [ks_hero_page] – Seite mit gleichem Hero-Look wie PROGRAMMS
 * -----------------------------------------------------*/
add_shortcode('ks_hero_page', function ($atts = [], $content = '') {
  $atts = shortcode_atts([
    'title'    => get_the_title(),
    'subtitle' => '',
  ], $atts, 'ks_hero_page');

  $theme_uri = get_stylesheet_directory_uri();
  $hero_url  = get_the_post_thumbnail_url(null, 'full');
  if (!$hero_url) $hero_url = $theme_uri . '/assets/img/mfs.png';

  // CSS laden (klein + Utilities)
  $utils_path = get_stylesheet_directory() . '/assets/css/ks-utils.css';
  $utils_uri  = $theme_uri . '/assets/css/ks-utils.css';
  wp_register_style('ks-utils', $utils_uri, ['kickstart-style'], file_exists($utils_path)?filemtime($utils_path):null);
  wp_enqueue_style('ks-utils');

  $css_path = get_stylesheet_directory() . '/assets/css/ks-hero.css';
  $css_uri  = $theme_uri . '/assets/css/ks-hero.css';
  wp_register_style('ks-hero', $css_uri, ['kickstart-style','ks-utils'], file_exists($css_path)?filemtime($css_path):null);
  wp_enqueue_style('ks-hero');

  // Hero-Bild via CSS-Variable (kein style="")
  wp_add_inline_style('ks-hero', '#ks-hero{--hero-img:url("'.esc_url($hero_url).'")}');

  ob_start(); ?>
  <div id="ks-hero" class="ks-dir__hero">
    <div class="ks-dir__hero-inner">
      <div class="ks-dir__crumb">Home <span class="sep">/</span> <?php echo esc_html($atts['title']); ?></div>
      <h1 class="ks-dir__hero-title"><?php echo esc_html($atts['title']); ?></h1>
      <?php if (!empty($atts['subtitle'])): ?>
        <p class="ks-dir__kicker"><?php echo esc_html($atts['subtitle']); ?></p>
      <?php endif; ?>
    </div>
  </div>

  <div class="container site-section">
    <?php echo do_shortcode($content); ?>
  </div>
  <?php return ob_get_clean();
});








