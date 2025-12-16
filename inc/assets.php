














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

  // 🔹 ZENTRALE UTILITIES (NEU / WICHTIG für Shortcodes & Layers)
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
        ['kickstart-style','ks-utils'],
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








  // Leaflet
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

















  // Offers Directory JS
  $dir_js = get_stylesheet_directory() . '/assets/js/offers-directory.js';
  if (file_exists($dir_js)) {
    wp_enqueue_script(
      'kickstart-offers-directory',
      get_stylesheet_directory_uri() . '/assets/js/offers-directory.js',
      ['leaflet-js','kickstart-offers-dialog'],
      filemtime($dir_js),
      true
    );
  }


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
      ['kickstart-book-dialog'], // 🔸 WICHTIG: erst book-dialog laden
      filemtime($dlg_js),
      true
    );
  }
  



wp_enqueue_style('ks-offers-dialog', get_stylesheet_directory_uri() . '/assets/css/offers-dialog.css', [], null);
wp_enqueue_style('ks-book-dialog', get_stylesheet_directory_uri() . '/assets/css/book-dialog.css', [], null);


  // Newsletter
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

  // Offers Directory CSS (kleine Abhängigkeits-Optimierung)
  $dir_css = get_stylesheet_directory() . '/assets/css/offers-directory.css';
  if (file_exists($dir_css)) {
    wp_enqueue_style(
      'kickstart-offers-directory',
      get_stylesheet_directory_uri() . '/assets/css/offers-directory.css',
      ['kickstart-style','ks-utils','ks-components','leaflet-css'],
      filemtime($dir_css)
    );
  }

















});











