<?php
/**
 * KickStart Starter Theme – functions.php
 */

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






/* -------------------------------------------------------
 * Styles & Scripts (inkl. Leaflet + Offers-Directory)
 * -----------------------------------------------------*/
add_action('wp_enqueue_scripts', function () {
    // Google Fonts
    wp_enqueue_style(
        'kickstart-fonts',
        'https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700&family=Roboto:wght@400;500;700&display=swap',
        [],
        null
    );

    // Theme CSS
    $style_path = get_stylesheet_directory() . '/style.css';
    wp_enqueue_style(
        'kickstart-style',
        get_stylesheet_uri(),
        ['kickstart-fonts'],
        file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version')
    );

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

    


    // === Leaflet (WICHTIG) ===
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



    
    // Dialog JS
$dlg_js = get_stylesheet_directory() . '/assets/js/offers-dialog.js';
if (file_exists($dlg_js)) {
    wp_enqueue_script(
        'kickstart-offers-dialog',
        get_stylesheet_directory_uri() . '/assets/js/offers-dialog.js',
        [],
        filemtime($dlg_js),
        true
    );
}



    // Offers Directory JS (abhängig von Leaflet)
    $dir_js = get_stylesheet_directory() . '/assets/js/offers-directory.js';
    if (file_exists($dir_js)) {
        wp_enqueue_script(
            'kickstart-offers-directory',
            get_stylesheet_directory_uri() . '/assets/js/offers-directory.js',
           # ['leaflet-js'], // <- Leaflet zuerst laden
             ['leaflet-js', 'kickstart-offers-dialog'],
            filemtime($dir_js),
            true
        );
    }

    // Offers Directory CSS (nach Leaflet-CSS laden)
    $dir_css = get_stylesheet_directory() . '/assets/css/offers-directory.css';
    if (file_exists($dir_css)) {
        wp_enqueue_style(
            'kickstart-offers-directory',
            get_stylesheet_directory_uri() . '/assets/css/offers-directory.css',
            ['kickstart-style', 'leaflet-css'],
            filemtime($dir_css)
        );
    }
});












/* -------------------------------------------------------
 * Kontaktformular (wie bei dir)
 * -----------------------------------------------------*/
// ... (dein bestehender Kontakt-Handler unverändert)

/* -------------------------------------------------------
 * Helpers (wie bei dir)
 * -----------------------------------------------------*/
if (!function_exists('ks_api_base')) {
    function ks_api_base() {
        $base = 'http://localhost:5000';
        return rtrim(apply_filters('ks_api_base', $base), '/');
    }
}
if (!function_exists('ks_next_base')) {
    function ks_next_base() {
        $base = 'http://localhost:3000';
        return rtrim(apply_filters('ks_next_base', $base), '/');
    }
}
if (!function_exists('ks_offers_url')) {
    function ks_offers_url() {
        $page = get_page_by_path('angebote');
        if ($page) return get_permalink($page->ID);
        return home_url('/index.php/angebote/');
    }
}











/* -------------------------------------------------------
 * [ks_offers] – (wie bei dir, inkl. city-Filter)
 * -----------------------------------------------------*/
// ... (dein ks_sc_offers() unverändert, inkl. ?city Filter)

/* -------------------------------------------------------
 * [ks_offers_directory] – Hero + Filter + Google-Maps + Liste + Modal
 * -----------------------------------------------------*/
add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {
    $type = isset($_GET['type']) ? sanitize_text_field( wp_unslash($_GET['type']) ) : '';
    $city = isset($_GET['city']) ? sanitize_text_field( wp_unslash($_GET['city']) ) : '';

    $mapTitles = [
      'Kindergarten'     => 'Fußballkindergarten',
      'Foerdertraining'  => 'Fördertraining',
      'PersonalTraining' => 'Individualtraining',
      'AthleticTraining' => 'Power Training',
      'Camp'             => 'Holiday Programs',
    ];
    $heading = $mapTitles[$type] ?? 'Programme';

    $hero_url = get_the_post_thumbnail_url(null, 'full');
    if (!$hero_url) {
      $hero_url = get_stylesheet_directory_uri() . '/assets/img/mfs.jpg';
    }

    // Altersbereich initial serverseitig (optional)
    $api_base = ks_api_base();
    $query    = ['limit' => '200'];
    if ($type !== '') $query['type'] = $type;
    $url      = add_query_arg($query, $api_base . '/api/offers');

    $ageMin = null; $ageMax = null;
    $res = wp_remote_get($url, ['timeout'=>10, 'headers'=>['Accept'=>'application/json']]);
    if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
      $data  = json_decode(wp_remote_retrieve_body($res), true);
      $items = [];
      if (isset($data['items']) && is_array($data['items'])) $items = $data['items'];
      elseif (is_array($data)) $items = $data;

      foreach ($items as $o) {
        if (isset($o['ageFrom']) && is_numeric($o['ageFrom'])) $ageMin = is_null($ageMin) ? (int)$o['ageFrom'] : min($ageMin, (int)$o['ageFrom']);
        if (isset($o['ageTo'])   && is_numeric($o['ageTo']))   $ageMax = is_null($ageMax) ? (int)$o['ageTo']   : max($ageMax, (int)$o['ageTo']);
      }
    }
    $ageText = ($ageMin !== null && $ageMax !== null) ? ($ageMin . '–' . $ageMax . ' Jahre') : 'alle Altersstufen';

    $next_base = ks_next_base();

    ob_start(); ?>
   
   
<div id="ksDir"
     class="ks-dir"
     data-api="<?php echo esc_attr($api_base); ?>"
     data-next="<?php echo esc_attr($next_base); ?>"
     data-type="<?php echo esc_attr($type); ?>"
     data-city="<?php echo esc_attr($city); ?>"
     data-close-icon="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/close.png' ); ?>"
     data-coachph="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/avatar.png' ); ?>">


      <!-- HERO -->
      <div class="ks-dir__hero" style="--hero-img: url('<?php echo esc_url($hero_url); ?>')">
        <div class="ks-dir__hero-inner">
          <div class="ks-dir__crumb">Home <span class="sep">/</span> <?php echo esc_html($heading); ?></div>
          <h1 class="ks-dir__hero-title"><?php echo esc_html($heading); ?></h1>
        </div>
      </div>

      <!-- Intro -->
      <header class="ks-dir__intro">
        <p class="ks-dir__kicker">Hier kannst du dein kostenfreies Schnuppertraining ganz einfach buchen</p>
        <h2 class="ks-dir__title">
          Unsere Angebote (<span data-age-title><?php echo esc_html($ageText); ?></span>)
        </h2>
      </header>

      <!-- Filter -->
      <form class="ks-dir__filters" data-filters>
        <label class="ks-field">
          <span>Tag</span>
          <select id="ksFilterDay">
            <option value="">Alle Tage</option>
            <option value="Mo">Mo</option><option value="Di">Di</option><option value="Mi">Mi</option>
            <option value="Do">Do</option><option value="Fr">Fr</option><option value="Sa">Sa</option><option value="So">So</option>
          </select>
        </label>

        <label class="ks-field">
          <span>Alter</span>
          <select id="ksFilterAge">
            <option value="">Alle</option>
            <?php for ($i=3; $i<=18; $i++): ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
            <?php endfor; ?>
          </select>
        </label>

        <label class="ks-field" >
          <span>Standort</span>
          <select id="ksFilterLoc">
            <option value="" >Alle Standorte</option>
          </select>
        </label>

       
      </form>

      <!-- Zähler -->
      <div class="ks-dir__meta">
        <strong><span data-count-offers>0</span> Angebote</strong>
        &nbsp;&bull;&nbsp;
        <strong><span data-count-locations>0</span> Standorte</strong>
      </div>

      <!-- 2-Spalten: Map | Liste -->

      <div class="ks-dir__layout">
  <div class="ks-dir__map">
    <div id="ksMap" class="ks-map"></div>
  </div>



  <div class="ks-dir__listwrap" aria-live="polite">
    <ul id="ksDirList" class="ks-dir__list"></ul>
  </div>
</div>




   
    


<!-- Booking Modal (iframe) -->
<div id="ksBookModal" class="ks-dir__modal" hidden>
  <div class="ks-dir__overlay" data-close></div>
  <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-label="Buchung">
    <button type="button" class="ks-dir__close" data-close aria-label="Schließen">
      <?php
      $close = get_stylesheet_directory_uri() . '/assets/img/close.png';
      echo '<img src="' . esc_url($close) . '" alt="Schließen" width="14" height="14">';
      ?>
    </button>
    <iframe class="ks-book__frame" src="" title="Buchung" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
  </div>
</div>

    
 

      <!-- Modal -->
      <div id="ksOfferModal" class="ks-dir__modal" hidden>
        <div class="ks-dir__overlay" data-close></div>
        <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-labelledby="ksOfferTitle">

          <button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>

          <h3 id="ksOfferTitle" class="ks-dir__m-title">Standort</h3>
          <p class="ks-dir__m-addr" data-address></p>
          <p class="ks-dir__m-meta">
            <span>Tag: <b data-days>-</b></span> ·
            <span>Uhrzeit: <b data-time>-</b></span> ·
            <span>Alter: <b data-age>-</b></span>
          </p>
          <p class="ks-dir__m-coach" data-coach></p>
          <p class="ks-dir__m-price"><b data-price></b></p>

          <div class="ks-dir__m-actions">
            <a class="btn btn-primary" data-select target="_blank" rel="noopener">Auswählen</a>
          </div>
        </div>
      </div>
    </div>
    <?php
    return ob_get_clean();
  });
});



































