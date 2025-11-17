<?php
/**
 * KickStart Starter Theme – Bootstrap Loader (modular)
 */

function ks_require($rel) {
  $path = get_stylesheet_directory() . '/inc/' . ltrim($rel, '/');
  if (file_exists($path)) require_once $path;
}

add_action('wp_enqueue_scripts', function () {
  wp_enqueue_style(
    'ks-footer',
    get_stylesheet_directory_uri() . '/assets/css/footer.css',
    [],
    '1.0'
  );

    wp_enqueue_style(
    'ks-werbung',
    get_stylesheet_directory_uri() . '/assets/css/ks-werbung.css',
    [],
    '1.0'
  );
});



add_action('wp_enqueue_scripts', function () {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();
  $about = $dir . '/assets/css/ks-about.css';
  if (file_exists($about)) {
    wp_enqueue_style('ks-about', $uri . '/assets/css/ks-about.css', ['kickstart-style','ks-utils'], filemtime($about));
  }
}, 20);

add_action('wp_enqueue_scripts', function () {
  $file = get_stylesheet_directory() . '/assets/css/ks-trainer.css';
  if ( file_exists($file) ) {
    wp_enqueue_style('ks-trainer', get_stylesheet_directory_uri() . '/assets/css/ks-trainer.css', [], filemtime($file));
  }
}, 30);



require_once get_stylesheet_directory() . '/inc/newsletter.php';



/* Core */
ks_require('setup.php');
ks_require('assets.php');
ks_require('helpers.php');
ks_require('contact-form.php');

/* Shortcodes */
ks_require('shortcodes/hero-page.php');
ks_require('shortcodes/franchise.php');
ks_require('shortcodes/offers-directory.php');
ks_require('shortcodes/home.php');  
ks_require('shortcodes/trainer.php'); 

/* Bereits vorhanden (aus vorheriger Arbeit) */
ks_require('shortcodes-about.php');
ks_require('navigation-mega-about.php');

ks_require('news-archive.php');


ks_require('shortcodes/whatsapp-locations.php');





















































