<?php
/*
Template Name: Kontakt (KickStart)
*/
get_header();

/**
 * CSS für Kontakt laden (ohne Pfade zu ändern).
 * ks-utils ist bei dir oft global – wir sichern es hier nur ab.
 */
$dir = get_stylesheet_directory();
$uri = get_stylesheet_directory_uri();

$utils = $dir . '/assets/css/ks-utils.css';
if (file_exists($utils) && !wp_style_is('ks-utils', 'enqueued')) {
  wp_enqueue_style('ks-utils', $uri . '/assets/css/ks-utils.css', ['kickstart-style'], filemtime($utils));
}

$contact_css = $dir . '/assets/css/ks-contact.css';
if (file_exists($contact_css)) {
  wp_enqueue_style('ks-contact', $uri . '/assets/css/ks-contact.css', ['kickstart-style', 'ks-utils'], filemtime($contact_css));
}

/**
 * Google Maps Embed (Marker auf deine Adresse)
 * -> Fokus auf Adresse (q=...).
 * Wenn du später "Route" willst: sag Bescheid, dann bauen wir Directions-Embed.
 */
$address_q = rawurlencode('Hochfelder Straße 33, 47226 Duisburg');
$map_url = 'https://www.google.com/maps?q=' . $address_q . '&output=embed';

$ks_contact = [
  'show_map' => true,
  'map_url'  => $map_url,

  'kicker'   => 'KONTAKT',
  'title'    => 'Hast Du Fragen?',
  'brand'    => 'Dortmunder Fussball Schule',
  'subtitle' => 'Unser Office-Team ist täglich von 09:00 – 12:00 Uhr für Dich da und beantwortet gerne alle Deine Fragen.',

  'address_line1' => 'Hochfelder Straße 33',
  'address_line2' => '47226 Duisburg',
  'phone'         => '0176 43203362',
  'email'         => 'fussballschule@selcuk-kocyigit.de',
];

include get_stylesheet_directory() . '/inc/partials/shared/contact-form.php';

get_footer();

