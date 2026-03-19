<?php
/* -------------------------------------------------------
 * [ks_jobs] – Jobs Page (Offers-Directory Hero + global Accordion + i18n JSON)
 * Datei: inc/jobs.php
 * -----------------------------------------------------*/

if (!defined('ABSPATH')) exit;

if (!function_exists('ks_jobs_enqueue_assets')) {
  function ks_jobs_enqueue_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    

    $utils_abs = $theme_dir . '/assets/css/ks-utils.css';
    if (file_exists($utils_abs) && !wp_style_is('ks-utils', 'enqueued')) {
      wp_enqueue_style(
        'ks-utils',
        $theme_uri . '/assets/css/ks-utils.css',
        ['kickstart-style'],
        filemtime($utils_abs)
      );
    }

    $home_abs = $theme_dir . '/assets/css/ks-home.css';
    if (file_exists($home_abs) && !wp_style_is('ks-home', 'enqueued')) {
      wp_enqueue_style(
        'ks-home',
        $theme_uri . '/assets/css/ks-home.css',
        ['kickstart-style', 'ks-utils'],
        filemtime($home_abs)
      );
    }

    $dir_abs = $theme_dir . '/assets/css/ks-dir.css';
    if (file_exists($dir_abs) && !wp_style_is('ks-dir', 'enqueued')) {
      wp_enqueue_style(
        'ks-dir',
        $theme_uri . '/assets/css/ks-dir.css',
        ['ks-home'],
        filemtime($dir_abs)
      );
    }

    $handle = wp_style_is('ks-dir', 'enqueued')
      ? 'ks-dir'
      : (wp_style_is('ks-home', 'enqueued')
        ? 'ks-home'
        : (wp_style_is('ks-utils', 'enqueued') ? 'ks-utils' : 'kickstart-style'));

    // Watermark im Jobs-Titleblock genauso wie bei FAQ/Offers (top:-80px)
    wp_add_inline_style($handle, '#jobs .ks-title-wrap::after{top:-80px !important;}');
  }
}







if (!function_exists('ks_jobs_lang_from_locale')) {
  function ks_jobs_lang_from_locale(): string {

    // ✅ 1) URL-Override (Test): /jobs/?lang=en oder ?lang=tr
    if (isset($_GET['lang'])) {
      $q = strtolower(sanitize_text_field(wp_unslash($_GET['lang'])));
      if (in_array($q, ['de', 'en', 'tr'], true)) {
        return $q;
      }
    }

    // ✅ 2) Standard: WP Locale
    $loc = function_exists('determine_locale') ? determine_locale() : get_locale();
    $loc = (string) $loc;

    $lang = strtolower(substr($loc, 0, 2));
    if (!$lang) $lang = 'de';

    if (!in_array($lang, ['de', 'en', 'tr'], true)) {
      $lang = 'de';
    }

    return $lang;
  }
}








if (!function_exists('ks_jobs_load_json')) {
  function ks_jobs_load_json(string $lang): array {
    $theme_dir = get_stylesheet_directory();

    $primary = $theme_dir . '/assets/i18n/jobs.' . $lang . '.json';
    $fallback = $theme_dir . '/assets/i18n/jobs.de.json';

    $file = file_exists($primary) ? $primary : $fallback;
    if (!file_exists($file)) return [];

    $raw = file_get_contents($file);
    if ($raw === false) return [];

    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
  }
}

if (!function_exists('ks_jobs_render_list')) {
  function ks_jobs_render_list(array $items): string {
    if (empty($items)) return '';

    $out = '<ul>';
    foreach ($items as $li) {
      $out .= '<li>' . esc_html((string) $li) . '</li>';
    }
    $out .= '</ul>';

    return $out;
  }
}

if (!function_exists('ks_jobs_build_body_html')) {
  function ks_jobs_build_body_html(array $job, string $apply_email): string {
    $title = isset($job['title']) ? (string) $job['title'] : '';
    $req   = isset($job['requirements']) && is_array($job['requirements']) ? $job['requirements'] : [];
    $tasks = isset($job['tasks']) && is_array($job['tasks']) ? $job['tasks'] : [];
    $ben   = isset($job['benefits']) && is_array($job['benefits']) ? $job['benefits'] : [];

    $email = sanitize_email($apply_email);
    $mailto = $email ? 'mailto:' . $email : '';

    $out  = '';

   

    $out .= '<h4>ANFORDERUNGEN:</h4>';
    $out .= ks_jobs_render_list($req);

    $out .= '<h4>DEINE AUFGABEN SIND:</h4>';
    $out .= ks_jobs_render_list($tasks);

    $out .= '<h4>DAS BIETEN WIR:</h4>';
    $out .= ks_jobs_render_list($ben);

    if ($email) {
      $out .= '<p>Alle Anforderungen erfüllt? Dann bewirb dich jetzt mit einer vollständigen Bewerbung bei uns für ein Bewerbungsgespräch unter:<br>';
      $out .= '<a href="' . esc_url($mailto) . '">' . esc_html($email) . '</a></p>';
    }

    return $out;
  }
}

if (!function_exists('ks_get_jobs_items')) {
  function ks_get_jobs_items(): array {
    $lang = ks_jobs_lang_from_locale();
    $data = ks_jobs_load_json($lang);
  

    $apply_email = isset($data['apply_email']) ? (string) $data['apply_email'] : 'fussballschule@selcuk-kocyigit.de';
    $positions   = isset($data['positions']) && is_array($data['positions']) ? $data['positions'] : [];

    $items = [];
    foreach ($positions as $job) {
      if (!is_array($job)) continue;

      $title = isset($job['title']) ? (string) $job['title'] : '';
      if (!$title) continue;

      $items[] = [
        'title' => $title,
        'body'  => ks_jobs_build_body_html($job, $apply_email),
      ];
    }

    return $items;
  }
}

if (!function_exists('ks_register_jobs_shortcode')) {
  function ks_register_jobs_shortcode() {
    add_shortcode('ks_jobs', function ($atts = []) {
      ks_jobs_enqueue_assets();

      $theme_dir = get_stylesheet_directory();
      $theme_uri = get_stylesheet_directory_uri();

      $atts = shortcode_atts([
        'title'    => 'Aktuelle Jobangebote',
        'subtitle' => 'Arbeiten bei der Dortmunder Fussball Schule',
        'bgword'   => 'JOBS',
      ], $atts, 'ks_jobs');

      // Icons: wie dein globaler Mask-Style (SVG bevorzugt, PNG fallback)
      $plus_candidates  = ['/assets/img/home/plus.svg',  '/assets/img/home/plus.png'];
      $minus_candidates = ['/assets/img/home/minus.svg', '/assets/img/home/minus.png'];

      $plus_rel = $plus_candidates[0];
      foreach ($plus_candidates as $rel) {
        if (file_exists($theme_dir . $rel)) { $plus_rel = $rel; break; }
      }

      $minus_rel = $minus_candidates[0];
      foreach ($minus_candidates as $rel) {
        if (file_exists($theme_dir . $rel)) { $minus_rel = $rel; break; }
      }

      if (!file_exists($theme_dir . $minus_rel)) {
        $minus_rel = $plus_rel;
      }

      $plus_url  = $theme_uri . $plus_rel;
      $minus_url = $theme_uri . $minus_rel;

      // Hero Bild wie Offer-Directory (Featured Image fallback)
      $hero_url = get_the_post_thumbnail_url(get_queried_object_id(), 'full');
      if (!$hero_url) {
        $hero_url = $theme_uri . '/assets/img/mfs.png';
      }

      // Offers-Directory Hero Struktur (Watermark via data-watermark)
      $hero_html = '
        <div class="ks-dir__hero"
             data-watermark="' . esc_attr($atts['bgword']) . '"
             style="--hero-img:url(\'' . esc_url($hero_url) . '\')">
          <div class="ks-dir__hero-inner">
            <div class="ks-dir__crumb">
              <a class="ks-dir__crumb-home" href="' . esc_url(home_url('/')) . '">Home</a>
              <span class="sep">/</span>
              ' . esc_html($atts['title']) . '
            </div>
            <h1 class="ks-dir__hero-title">' . esc_html($atts['title']) . '</h1>
          </div>
        </div>
      ';

      $jobs = ks_get_jobs_items();

      ob_start();
      get_template_part('inc/partials/pages/jobs', null, [
        'title'     => $atts['title'],
        'subtitle'  => $atts['subtitle'],
        'bgword'    => $atts['bgword'],
        'plus_url'  => $plus_url,
        'minus_url' => $minus_url,
        'items'     => $jobs,
      ]);
      $inner = ob_get_clean();

      return $hero_html . $inner;
    });
  }

  add_action('init', 'ks_register_jobs_shortcode');
}










