<?php

if (!function_exists('ks_enqueue_team_assets')) {
  function ks_enqueue_team_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $deps = ['kickstart-style', 'ks-utils'];

    if (wp_style_is('ks-home', 'enqueued') || wp_style_is('ks-home', 'registered')) {
      $deps[] = 'ks-home';
    }

    if (wp_style_is('ks-dir', 'enqueued') || wp_style_is('ks-dir', 'registered')) {
      $deps[] = 'ks-dir';
    }

    $team_css = $theme_dir . '/assets/css/ks-home-team.css';

    if (file_exists($team_css) && !wp_style_is('ks-home-team', 'enqueued')) {
      wp_enqueue_style(
        'ks-home-team',
        $theme_uri . '/assets/css/ks-home-team.css',
        $deps,
        filemtime($team_css)
      );
    }

    $team_js = $theme_dir . '/assets/js/ks-home-team.js';

    if (file_exists($team_js) && !wp_script_is('ks-home-team', 'enqueued')) {
      wp_enqueue_script(
        'ks-home-team',
        $theme_uri . '/assets/js/ks-home-team.js',
        [],
        filemtime($team_js),
        true
      );
    }
  }
}

if (!function_exists('ks_enqueue_info_section_assets')) {
  function ks_enqueue_info_section_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $info_css = $theme_dir . '/assets/css/ks-info-section.css';

    if (!file_exists($info_css) || wp_style_is('ks-info-section', 'enqueued')) {
      return;
    }

    wp_enqueue_style(
      'ks-info-section',
      $theme_uri . '/assets/css/ks-info-section.css',
      ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
      filemtime($info_css)
    );
  }
}

if (!function_exists('ks_enqueue_contact_section_assets')) {
  function ks_enqueue_contact_section_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $contact_css = $theme_dir . '/assets/css/ks-contact-section.css';

    if (!file_exists($contact_css) || wp_style_is('ks-contact-section', 'enqueued')) {
      return;
    }

    wp_enqueue_style(
      'ks-contact-section',
      $theme_uri . '/assets/css/ks-contact-section.css',
      ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
      filemtime($contact_css)
    );
  }
}

add_action('wp_enqueue_scripts', function () {
  
  $fonts_path = get_stylesheet_directory() . '/assets/css/fonts.css';

if (file_exists($fonts_path)) {
  wp_enqueue_style(
    'kickstart-fonts',
    get_stylesheet_directory_uri() . '/assets/css/fonts.css',
    [],
    filemtime($fonts_path)
  );
}

  $style_path = get_stylesheet_directory() . '/style.css';

  wp_enqueue_style(
    'kickstart-style',
    get_stylesheet_uri(),
    ['kickstart-fonts'],
    file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
  );

  $utils_path = get_stylesheet_directory() . '/assets/css/ks-utils.css';

  if (file_exists($utils_path)) {
    wp_enqueue_style(
      'ks-utils',
      get_stylesheet_directory_uri() . '/assets/css/ks-utils.css',
      ['kickstart-style'],
      filemtime($utils_path)
    );
  }
$styles = [
  'base' => [
  'path' => '/assets/css/base.css',
  'deps' => ['kickstart-fonts', 'kickstart-style', 'ks-utils'],
],
  'layout' => [
    'path' => '/assets/css/layout.css',
    'deps' => ['kickstart-style', 'ks-utils'],
  ],
  'components' => [
    'path' => '/assets/css/components.css',
    'deps' => ['kickstart-style', 'ks-utils'],
  ],
  'header' => [
    'path' => '/assets/css/header.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-layout'],
  ],
  'header-dropdown' => [
    'path' => '/assets/css/header-dropdown.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-layout', 'ks-header'],
  ],
  'language' => [
    'path' => '/assets/css/language.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-header', 'ks-header-dropdown'],
  ],
  'partner-network' => [
    'path' => '/assets/css/partner-network.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
  ],
  'page-hero' => [
  'path' => '/assets/css/ks-page-hero.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
],
  'about' => [
  'path' => '/assets/css/ks-about.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components', 'ks-page-hero'],
],
  'home-values' => [
    'path' => '/assets/css/ks-home-values.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-base'],
  ],
  'faq' => [
  'path' => '/assets/css/ks-faq.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
],

  'home-news' => [
    'path' => '/assets/css/ks-home-news.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
  ],
  'home-program-cta' => [
  'path' => '/assets/css/ks-home-program-cta.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
],
  'home-team' => [
    'path' => '/assets/css/ks-home-team.css',
    'deps' => ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
  ],
  'trainer' => [
    'path' => '/assets/css/ks-trainer.css',
    'deps' => ['kickstart-style', 'ks-utils'],
  ],
 'footer' => [
  'path' => '/assets/css/footer.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base'],
],
'back-top' => [
  'path' => '/assets/css/ks-back-top.css',
  'deps' => ['kickstart-style', 'ks-utils', 'ks-base'],
],
];
  foreach ($styles as $handle => $config) {
    $abs = get_stylesheet_directory() . $config['path'];

    if (file_exists($abs)) {
      wp_enqueue_style(
        'ks-' . $handle,
        get_stylesheet_directory_uri() . $config['path'],
        $config['deps'],
        filemtime($abs)
      );
    }
  }

  wp_register_script('ks-smooth', false, [], null, true);
  wp_enqueue_script('ks-smooth');

  wp_add_inline_script(
    'ks-smooth',
    "
    document.addEventListener('click', function(e){
      const a = e.target.closest('a.js-scroll[href^=\"#\"]');
      if (!a) return;
      const el = document.querySelector(a.getAttribute('href'));
      if (!el) return;
      e.preventDefault();
      el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    "
  );

  $dropdown_motion_js = get_stylesheet_directory() . '/assets/js/ks-dropdown-motion.js';

  if (file_exists($dropdown_motion_js)) {
    wp_enqueue_script(
      'ks-dropdown-motion',
      get_stylesheet_directory_uri() . '/assets/js/ks-dropdown-motion.js',
      [],
      filemtime($dropdown_motion_js),
      true
    );
  }

  wp_add_inline_style('kickstart-style', '.ks-sec{scroll-margin-top:90px}');

  $mega_js = get_stylesheet_directory() . '/assets/js/mega-menu.js';

  if (file_exists($mega_js)) {
    wp_enqueue_script(
      'kickstart-mega-menu',
      get_stylesheet_directory_uri() . '/assets/js/mega-menu.js',
      ['ks-dropdown-motion'],
      filemtime($mega_js),
      true
    );
  }

  $language_js = get_stylesheet_directory() . '/assets/js/language-switcher.js';

  if (file_exists($language_js)) {
    wp_enqueue_script(
      'kickstart-language-switcher',
      get_stylesheet_directory_uri() . '/assets/js/language-switcher.js',
      ['ks-dropdown-motion'],
      filemtime($language_js),
      true
    );
  }

  $partner_network_js = get_stylesheet_directory() . '/assets/js/partner-network.js';

if (file_exists($partner_network_js)) {
  wp_enqueue_script(
    'ks-partner-network',
    get_stylesheet_directory_uri() . '/assets/js/partner-network.js',
    [],
    filemtime($partner_network_js),
    true
  );
}

  $whatsapp_js = get_stylesheet_directory() . '/assets/js/ks-whatsapp-locations.js';

  if (file_exists($whatsapp_js)) {
    wp_enqueue_script(
      'ks-whatsapp-locations',
      get_stylesheet_directory_uri() . '/assets/js/ks-whatsapp-locations.js',
      [],
      filemtime($whatsapp_js),
      true
    );
  }

  $should_enqueue_dropdown = true;

  if (is_admin()) {
    $should_enqueue_dropdown = false;
  }

  if ($should_enqueue_dropdown) {
    global $post;
    $content = is_object($post) ? (string) $post->post_content : '';

    if (has_shortcode($content, 'ks_offers_directory')) {
      $should_enqueue_dropdown = false;
    }
  }

  if ($should_enqueue_dropdown) {
    $dropdown_js = get_stylesheet_directory() . '/assets/js/ks-dropdown.js';

    if (file_exists($dropdown_js)) {
      wp_enqueue_script(
        'ks-dropdown',
        get_stylesheet_directory_uri() . '/assets/js/ks-dropdown.js',
        ['ks-dropdown-motion'],
        filemtime($dropdown_js),
        true
      );
    }
  }

  $back_top_js = get_stylesheet_directory() . '/assets/js/ks-back-top.js';

if (file_exists($back_top_js)) {
  wp_enqueue_script(
    'ks-back-top',
    get_stylesheet_directory_uri() . '/assets/js/ks-back-top.js',
    [],
    filemtime($back_top_js),
    true
  );
}

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

  $map_deps = ['leaflet-js'];

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

  $dir_css = get_stylesheet_directory() . '/assets/css/offers-directory.css';

  if (file_exists($dir_css)) {
    wp_enqueue_style(
      'kickstart-offers-directory',
      get_stylesheet_directory_uri() . '/assets/css/offers-directory.css',
      ['kickstart-style', 'ks-utils', 'ks-components', 'leaflet-css'],
      filemtime($dir_css)
    );
  }

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

  $contact_css = get_stylesheet_directory() . '/assets/css/ks-contact.css';

  if (file_exists($contact_css)) {
    wp_enqueue_style(
      'ks-contact',
      get_stylesheet_directory_uri() . '/assets/css/ks-contact.css',
      ['kickstart-style', 'ks-utils'],
      filemtime($contact_css)
    );
  }

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

  $has_shortcode = function (string $tag): bool {
    if (!is_singular()) {
      return false;
    }

    global $post;

    if (!$post) {
      return false;
    }

    return has_shortcode($post->post_content ?? '', $tag);
  };

  if ($has_shortcode('ks_home') || $has_shortcode('ks_franchise')) {
    $dd_hover_css = get_stylesheet_directory() . '/assets/css/dropdown-hover.css';

    if (file_exists($dd_hover_css) && !wp_style_is('ks-dropdown-hover', 'enqueued')) {
      wp_enqueue_style(
        'ks-dropdown-hover',
        get_stylesheet_directory_uri() . '/assets/css/dropdown-hover.css',
        ['kickstart-style', 'ks-utils'],
        filemtime($dd_hover_css)
      );
    }
  }

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

  $accordion_js = get_stylesheet_directory() . '/assets/js/ks-accordion.js';

  if (file_exists($accordion_js)) {
    wp_enqueue_script(
      'ks-accordion',
      get_stylesheet_directory_uri() . '/assets/js/ks-accordion.js',
      [],
      filemtime($accordion_js),
      true
    );
  }
});

add_action('wp_enqueue_scripts', function () {
  if (is_admin()) {
    return;
  }

  if (!is_page('impressum')) {
    return;
  }

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

add_action('wp_enqueue_scripts', function () {
  if (is_admin()) {
    return;
  }

  if (!is_page(['datenschutz'])) {
    return;
  }

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
  if (is_admin()) {
    return;
  }

  if (!is_page('agb')) {
    return;
  }

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























