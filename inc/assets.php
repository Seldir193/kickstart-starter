

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
















  // ---------------------------------------
  // Conditional Assets (nur wenn Shortcode auf der Seite)
  // ---------------------------------------
  $has_shortcode = function (string $tag): bool {
    if (!is_singular()) return false;
    global $post;
    if (!$post) return false;
    return has_shortcode($post->post_content ?? '', $tag);
  };
 // 1) Dropdown Hover (nur für Seiten, die es brauchen)
  if ($has_shortcode('ks_home') || $has_shortcode('ks_franchise')) {
    $dd_hover_css = get_stylesheet_directory() . '/assets/css/dropdown-hover.css';
    if (file_exists($dd_hover_css) && !wp_style_is('ks-dropdown-hover', 'enqueued')) {
      wp_enqueue_style(
        'ks-dropdown-hover',
        get_stylesheet_directory_uri() . '/assets/css/dropdown-hover.css',
        ['kickstart-style', 'ks-utils'], // ks-home ist evtl. nicht global - daher minimal sicher
        filemtime($dd_hover_css)
      );
    }
  }


// 3) ks-dir.css auch für Trainer (damit Hero identisch ist wie Offers)
// if ($has_shortcode('ks_trainer_profile')) {
//   $ks_dir_css = get_stylesheet_directory() . '/assets/css/ks-dir.css';
//   if (file_exists($ks_dir_css) && !wp_style_is('ks-dir', 'enqueued')) {
//     wp_enqueue_style(
//       'ks-dir',
//       get_stylesheet_directory_uri() . '/assets/css/ks-dir.css',
//       ['kickstart-style', 'ks-utils'],
//       filemtime($ks_dir_css)
//     );
//   }
// }

 
  // 2) Franchise Worldmap Script (nur auf Franchise-Seite)
  if ($has_shortcode('ks_franchise')) {
    $wm_js = get_stylesheet_directory() . '/assets/js/ks-franchise-worldmap.js';
    if (file_exists($wm_js) && !wp_script_is('ks-franchise-worldmap', 'enqueued')) {
      wp_enqueue_script(
        'ks-franchise-worldmap',
        get_stylesheet_directory_uri() . '/assets/js/ks-franchise-worldmap.js',
        [],
        filemtime($wm_js),
        true
      );
    }
  }




// Contact CSS (global)
$contact_css = get_stylesheet_directory() . '/assets/css/ks-contact.css';
if (file_exists($contact_css)) {
  wp_enqueue_style(
    'ks-contact',
    get_stylesheet_directory_uri() . '/assets/css/ks-contact.css',
    ['kickstart-style', 'ks-utils'], // nutzt dein System
    filemtime($contact_css)
  );
}




// Contact Form JS (Validation + sent auto-hide)
$contact_js = get_stylesheet_directory() . '/assets/js/ks-contact-form.js';
if (file_exists($contact_js)) {
  wp_enqueue_script(
    'ks-contact-form',
    get_stylesheet_directory_uri() . '/assets/js/ks-contact-form.js',
    [],
    filemtime($contact_js),
    true
  );
}


$sb_path = get_stylesheet_directory() . '/assets/css/ks-scrollbars.css';
if (file_exists($sb_path)) {
  wp_enqueue_style(
    'ks-scrollbars',
    get_stylesheet_directory_uri() . '/assets/css/ks-scrollbars.css',
    ['ks-utils'],
    filemtime($sb_path)
  );
}





add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  if (!is_page('impressum')) return;

  $file = get_stylesheet_directory() . '/assets/css/ks-impressum.css';
  if (file_exists($file)) {
    wp_enqueue_style(
      'ks-impressum',
      get_stylesheet_directory_uri() . '/assets/css/ks-impressum.css',
      ['kickstart-style', 'ks-utils'],
      filemtime($file)
    );
  }
}, 40);



// add_action('wp_enqueue_scripts', function () {
//   if (is_admin()) return;

//   // slug: /datenschutz/
//   if (!is_page('datenschutz')) return;

//   $file = get_stylesheet_directory() . '/assets/css/ks-privacy.css';
//   if (file_exists($file)) {
//     wp_enqueue_style(
//       'ks-privacy',
//       get_stylesheet_directory_uri() . '/assets/css/ks-privacy.css',
//       ['kickstart-style', 'ks-utils'],
//       filemtime($file)
//     );
//   }
// }, 40);



add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  if (!is_page(['datenschutz'])) return;

  $file = get_stylesheet_directory() . '/assets/css/ks-privacy.css';
  if (file_exists($file)) {
    wp_enqueue_style(
      'ks-privacy',
      get_stylesheet_directory_uri() . '/assets/css/ks-privacy.css',
      ['kickstart-style', 'ks-utils'],
      filemtime($file)
    );
  }
}, 40);




add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // slug: /agb/
  if (!is_page('agb')) return;

  $file = get_stylesheet_directory() . '/assets/css/ks-agb.css';
  if (file_exists($file)) {
    wp_enqueue_style(
      'ks-agb',
      get_stylesheet_directory_uri() . '/assets/css/ks-agb.css',
      ['kickstart-style', 'ks-utils'],
      filemtime($file)
    );
  }
}, 40);




//   $file = get_stylesheet_directory() . '/assets/css/ks-faq.css';
//   if (file_exists($file)) {
//     wp_enqueue_style(
//       'ks-faq',
//       get_stylesheet_directory_uri() . '/assets/css/ks-faq.css',
//       ['ks-home'],
//       filemtime($file)
//     );
//   }
// }, 40);


});









