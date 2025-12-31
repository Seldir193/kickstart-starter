<?php
add_action('wp_enqueue_scripts', function () {

  // Google Fonts
  wp_enqueue_style(
    'kickstart-fonts',
    'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Roboto:wght@400;500;700&display=swap',
    [],
    null
  );

  // Theme CSS (muss zuerst)
  $style_path = get_stylesheet_directory() . '/style.css';
  wp_enqueue_style(
    'kickstart-style',
    get_stylesheet_uri(),
    ['kickstart-fonts'],
    file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
  );

  // ZENTRALE UTILITIES
  $utils_path = get_stylesheet_directory() . '/assets/css/ks-utils.css';
  if (file_exists($utils_path)) {
    wp_enqueue_style(
      'ks-utils',
      get_stylesheet_directory_uri() . '/assets/css/ks-utils.css',
      ['kickstart-style'],
      filemtime($utils_path)
    );
  }

  // Basis / Layout / Komponenten (laden NACH utils)
  foreach ([
    'base'       => '/assets/css/base.css',
    'layout'     => '/assets/css/layout.css',
    'components' => '/assets/css/components.css',
  ] as $handle => $relPath) {
    $abs = get_stylesheet_directory() . $relPath;
    if (file_exists($abs)) {
      wp_enqueue_style(
        'ks-' . $handle,
        get_stylesheet_directory_uri() . $relPath,
        ['kickstart-style', 'ks-utils'],
        filemtime($abs)
      );
    }
  }

  // Smooth-Scroll
  wp_register_script('ks-smooth', false, [], null, true);
  wp_enqueue_script('ks-smooth');
  wp_add_inline_script('ks-smooth', "
    document.addEventListener('click', function(e){
      const a = e.target.closest('a.js-scroll[href^=\"#\"]');
      if (!a) return;
      const el = document.querySelector(a.getAttribute('href'));
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
  ");
  // Anker-Offset
  wp_add_inline_style('kickstart-style', '.ks-sec{scroll-margin-top:90px}');

  // Mega-Menu Script (optional)
  $mega_js = get_stylesheet_directory() . '/assets/js/mega-menu.js';
  if (file_exists($mega_js)) {
    wp_enqueue_script(
      'kickstart-mega-menu',
      get_stylesheet_directory_uri() . '/assets/js/mega-menu.js',
      [],
      filemtime($mega_js),
      true
    );
  }

  // ---------------------------
  // Leaflet (wie bisher global)
  // ---------------------------
  wp_enqueue_style(
    'leaflet-css',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
    [],
    '1.9.4'
  );
  wp_enqueue_script(
    'leaflet-js',
    'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
    [],
    '1.9.4',
    true
  );

 

  // ---------------------------------------
  // Book-Dialog + Offers-Dialog (Reihenfolge FIX)
  // ---------------------------------------

  // Booking-Dialog JS (iframe)
  $book_js = get_stylesheet_directory() . '/assets/js/book-dialog.js';
  if (file_exists($book_js)) {
    wp_enqueue_script(
      'kickstart-book-dialog',
      get_stylesheet_directory_uri() . '/assets/js/book-dialog.js',
      [],
      filemtime($book_js),
      true
    );
  }

  // Offers-Dialog JS (benötigt BookDialog)
  $dlg_js = get_stylesheet_directory() . '/assets/js/offers-dialog.js';
  if (file_exists($dlg_js)) {
    wp_enqueue_script(
      'kickstart-offers-dialog',
      get_stylesheet_directory_uri() . '/assets/js/offers-dialog.js',
      ['kickstart-book-dialog'],
      filemtime($dlg_js),
      true
    );
  }

  // Dialog CSS (wie bisher)
  $offers_dlg_css = get_stylesheet_directory() . '/assets/css/offers-dialog.css';
  if (file_exists($offers_dlg_css)) {
    wp_enqueue_style(
      'ks-offers-dialog',
      get_stylesheet_directory_uri() . '/assets/css/offers-dialog.css',
      [],
      filemtime($offers_dlg_css)
    );
  }

  $book_dlg_css = get_stylesheet_directory() . '/assets/css/book-dialog.css';
  if (file_exists($book_dlg_css)) {
    wp_enqueue_style(
      'ks-book-dialog',
      get_stylesheet_directory_uri() . '/assets/css/book-dialog.css',
      [],
      filemtime($book_dlg_css)
    );
  }

  // ---------------------------------------
  // Offers Directory Map + Main (Reihenfolge FIX)
  // ---------------------------------------

 
// ---------------------------------------
// Offers Directory Map + Main (Reihenfolge FIX)
// ---------------------------------------

$map_deps = ['leaflet-js'];

// geocode helper (liegt bei dir in assets/js/)
$map_helper_path = get_stylesheet_directory() . '/assets/js/offers-map-geocode.js';
if (file_exists($map_helper_path)) {
  wp_enqueue_script(
    'ks-offers-dir-map-helpers',
    get_stylesheet_directory_uri() . '/assets/js/offers-map-geocode.js',
    [],
    filemtime($map_helper_path),
    true
  );

  $geocode_url = rest_url('ks/v1/geocode');
  wp_add_inline_script(
    'ks-offers-dir-map-helpers',
    'window.KS_MAP_GEOCODE_URL = ' . wp_json_encode($geocode_url) . ';',
    'before'
  );

  $map_deps[] = 'ks-offers-dir-map-helpers';
}

// map (liegt bei dir in assets/js/)
$map_js = get_stylesheet_directory() . '/assets/js/offers-directory-map.js';
if (file_exists($map_js)) {
  wp_enqueue_script(
    'ks-offers-dir-map',
    get_stylesheet_directory_uri() . '/assets/js/offers-directory-map.js',
    $map_deps,
    filemtime($map_js),
    true
  );
}

// core (liegt bei dir in assets/js/)
$core_path = get_stylesheet_directory() . '/assets/js/offers-directory-core.js';
if (file_exists($core_path)) {
  wp_enqueue_script(
    'ks-offers-dir-core',
    get_stylesheet_directory_uri() . '/assets/js/offers-directory-core.js',
    [],
    filemtime($core_path),
    true
  );
}

// ui (liegt bei dir in assets/js/)
$ui_path = get_stylesheet_directory() . '/assets/js/offers-directory-ui.js';
if (file_exists($ui_path)) {
  wp_enqueue_script(
    'ks-offers-dir-ui',
    get_stylesheet_directory_uri() . '/assets/js/offers-directory-ui.js',
    [],
    filemtime($ui_path),
    true
  );
}

// main (liegt bei dir in assets/js/)
$dir_js = get_stylesheet_directory() . '/assets/js/offers-directory.js';
if (file_exists($dir_js)) {
  wp_enqueue_script(
    'ks-offers-directory',
    get_stylesheet_directory_uri() . '/assets/js/offers-directory.js',
    ['ks-offers-dir-core', 'ks-offers-dir-ui', 'ks-offers-dir-map'],
    filemtime($dir_js),
    true
  );
}



  // Offers Directory CSS (wie bisher)
  $dir_css = get_stylesheet_directory() . '/assets/css/offers-directory.css';
  if (file_exists($dir_css)) {
    wp_enqueue_style(
      'kickstart-offers-directory',
      get_stylesheet_directory_uri() . '/assets/css/offers-directory.css',
      ['kickstart-style', 'ks-utils', 'ks-components', 'leaflet-css'],
      filemtime($dir_css)
    );
  }



  // ---------------------------
  // Newsletter (wie bisher)
  // ---------------------------
  $news_js = get_stylesheet_directory() . '/assets/js/newsletter.js';
  if (file_exists($news_js)) {
    wp_enqueue_script(
      'ks-newsletter',
      get_stylesheet_directory_uri() . '/assets/js/newsletter.js',
      [],
      filemtime($news_js),
      true
    );

    wp_localize_script('ks-newsletter', 'KS_NEWS', [
      'api' => 'http://127.0.0.1:5000/api/public/newsletter',
    ]);
  }
});












