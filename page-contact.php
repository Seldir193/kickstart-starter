<?php
/*
Template Name: Kontakt (KickStart)
*/

$dir = get_stylesheet_directory();
$uri = get_stylesheet_directory_uri();

$utils = $dir . '/assets/css/ks-utils.css';

if (file_exists($utils) && !wp_style_is('ks-utils', 'enqueued')) {
  wp_enqueue_style(
    'ks-utils',
    $uri . '/assets/css/ks-utils.css',
    ['kickstart-style'],
    filemtime($utils)
  );
}

$contact_css = $dir . '/assets/css/ks-contact.css';

if (file_exists($contact_css)) {
  wp_enqueue_style(
    'ks-contact',
    $uri . '/assets/css/ks-contact.css',
    ['kickstart-style', 'ks-utils'],
    filemtime($contact_css)
  );
}

get_header();

$contact_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'contact') : $fallback;
};

$address_q = rawurlencode('Hochfelder Straße 33, 47226 Duisburg');
$map_url = 'https://www.google.com/maps?q=' . $address_q . '&output=embed';

$ks_contact = [
  'show_map' => true,
  'map_url' => $map_url,
  'kicker' => $contact_t('contact.kicker', 'KONTAKT'),
  'title' => $contact_t('contact.title', 'Hast Du Fragen?'),
  'brand' => $contact_t('contact.brand', 'Dortmunder Fussball Schule'),
  'subtitle' => $contact_t(
    'contact.subtitle',
    'Unser Office-Team ist täglich von 09:00 – 12:00 Uhr für Dich da und beantwortet gerne alle Deine Fragen.'
  ),
  'address_line1' => 'Hochfelder Straße 33',
  'address_line2' => '47226 Duisburg',
  'phone' => '0176 43203362',
  'email' => 'fussballschule@selcuk-kocyigit.de',
];
?>

<main id="primary" class="site-main site-main--contact">
  <?php include get_stylesheet_directory() . '/inc/partials/shared/contact-form.php'; ?>
</main>

<?php get_footer(); ?>










