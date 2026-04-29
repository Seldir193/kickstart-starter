<?php
/**
 * KickStart Starter Theme – Bootstrap Loader (modular)
 */

function ks_require($rel) {
  $path = get_stylesheet_directory() . '/inc/' . ltrim($rel, '/');
  if (file_exists($path)) {
    require_once $path;
  }
}

require_once get_stylesheet_directory() . '/inc/newsletter.php';

require_once get_stylesheet_directory() . '/inc/jobs.php';

ks_require('setup.php');
ks_require('assets.php');
ks_require('helpers.php');
ks_require('contact-form.php');
ks_require('ks-feedback.php');
ks_require('ks-geocode.php');
ks_require('navigation-mega-about.php');
ks_require('news/news-latest.php');
ks_require('news-archive.php');

ks_require('shortcodes/hero-page.php');
ks_require('shortcodes/franchise.php');
ks_require('shortcodes/offers-directory.php');
ks_require('shortcodes/home.php');
ks_require('shortcodes/trainer.php');
ks_require('shortcodes/partner-network.php');
ks_require('shortcodes/datenschutz.php');
ks_require('shortcodes/impressum.php');
ks_require('shortcodes/agb.php');
ks_require('shortcodes/faq.php');
ks_require('shortcodes/membership-cancellation-form.php');
ks_require('shortcodes/withdrawal-form.php');
ks_require('shortcodes/whatsapp-locations.php');
ks_require('shortcodes-about.php');




































