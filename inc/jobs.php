<?php

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('ks_jobs_get_supported_languages')) {
  function ks_jobs_get_supported_languages() {
    return ['de', 'en', 'tr'];
  }
}

if (!function_exists('ks_jobs_get_query_language')) {
  function ks_jobs_get_query_language() {
    if (!isset($_GET['lang'])) {
      return '';
    }

    return strtolower(sanitize_text_field(wp_unslash($_GET['lang'])));
  }
}

if (!function_exists('ks_jobs_get_locale_language')) {
  function ks_jobs_get_locale_language() {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();
    $language = strtolower(substr((string) $locale, 0, 2));

    return $language ?: 'de';
  }
}

if (!function_exists('ks_jobs_lang_from_locale')) {
  function ks_jobs_lang_from_locale() {
    $language = ks_jobs_get_query_language() ?: ks_jobs_get_locale_language();

    return in_array($language, ks_jobs_get_supported_languages(), true) ? $language : 'de';
  }
}

if (!function_exists('ks_jobs_get_json_path')) {
  function ks_jobs_get_json_path($language) {
    $theme_dir = get_stylesheet_directory();
    $primary = $theme_dir . '/assets/i18n/jobs.' . $language . '.json';
    $fallback = $theme_dir . '/assets/i18n/jobs.de.json';

    return file_exists($primary) ? $primary : $fallback;
  }
}

if (!function_exists('ks_jobs_load_json')) {
  function ks_jobs_load_json($language) {
    $file_path = ks_jobs_get_json_path($language);

    if (!file_exists($file_path)) {
      return [];
    }

    return ks_jobs_decode_json_file($file_path);
  }
}

if (!function_exists('ks_jobs_decode_json_file')) {
  function ks_jobs_decode_json_file($file_path) {
    $content = file_get_contents($file_path);

    if ($content === false) {
      return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
  }
}

if (!function_exists('ks_jobs_render_list')) {
  function ks_jobs_render_list($items) {
    if (empty($items)) {
      return '';
    }

    return '<ul>' . ks_jobs_render_list_items($items) . '</ul>';
  }
}

if (!function_exists('ks_jobs_render_list_items')) {
  function ks_jobs_render_list_items($items) {
    $html = '';

    foreach ($items as $item) {
      $html .= '<li>' . esc_html((string) $item) . '</li>';
    }

    return $html;
  }
}

if (!function_exists('ks_jobs_get_job_field')) {
  function ks_jobs_get_job_field($job, $key) {
    return isset($job[$key]) && is_array($job[$key]) ? $job[$key] : [];
  }
}

if (!function_exists('ks_jobs_get_apply_email')) {
  function ks_jobs_get_apply_email($data) {
    $email = $data['apply_email'] ?? 'fussballschule@selcuk-kocyigit.de';

    return sanitize_email((string) $email);
  }
}

if (!function_exists('ks_jobs_build_body_html')) {
  function ks_jobs_build_body_html($job, $apply_email) {
    $html = ks_jobs_build_section_html('ANFORDERUNGEN:', ks_jobs_get_job_field($job, 'requirements'));
    $html .= ks_jobs_build_section_html('DEINE AUFGABEN SIND:', ks_jobs_get_job_field($job, 'tasks'));
    $html .= ks_jobs_build_section_html('DAS BIETEN WIR:', ks_jobs_get_job_field($job, 'benefits'));

    return $html . ks_jobs_build_apply_html($apply_email);
  }
}

if (!function_exists('ks_jobs_build_section_html')) {
  function ks_jobs_build_section_html($title, $items) {
    return '<h4>' . esc_html($title) . '</h4>' . ks_jobs_render_list($items);
  }
}

if (!function_exists('ks_jobs_build_apply_html')) {
  function ks_jobs_build_apply_html($email) {
    if (!$email) {
      return '';
    }

    return ks_jobs_build_apply_paragraph($email);
  }
}

if (!function_exists('ks_jobs_build_apply_paragraph')) {
  function ks_jobs_build_apply_paragraph($email) {
    $mailto = 'mailto:' . $email;
    $text = 'Alle Anforderungen erfüllt? Dann bewirb dich jetzt mit einer vollständigen Bewerbung bei uns für ein Bewerbungsgespräch unter:';

    return '<p>' . esc_html($text) . '<br><a href="' . esc_url($mailto) . '">' . esc_html($email) . '</a></p>';
  }
}

if (!function_exists('ks_jobs_get_position_title')) {
  function ks_jobs_get_position_title($job) {
    return isset($job['title']) ? (string) $job['title'] : '';
  }
}

if (!function_exists('ks_jobs_build_item')) {
  function ks_jobs_build_item($job, $apply_email) {
    return [
      'title' => ks_jobs_get_position_title($job),
      'body' => ks_jobs_build_body_html($job, $apply_email),
    ];
  }
}

if (!function_exists('ks_get_jobs_items')) {
  function ks_get_jobs_items() {
    $data = ks_jobs_load_json(ks_jobs_lang_from_locale());
    $positions = isset($data['positions']) && is_array($data['positions']) ? $data['positions'] : [];

    return ks_jobs_build_items($positions, ks_jobs_get_apply_email($data));
  }
}

if (!function_exists('ks_jobs_build_items')) {
  function ks_jobs_build_items($positions, $apply_email) {
    $items = [];

    foreach ($positions as $job) {
      ks_jobs_append_item($items, $job, $apply_email);
    }

    return $items;
  }
}

if (!function_exists('ks_jobs_append_item')) {
  function ks_jobs_append_item(&$items, $job, $apply_email) {
    if (!is_array($job) || ks_jobs_get_position_title($job) === '') {
      return;
    }

    $items[] = ks_jobs_build_item($job, $apply_email);
  }
}

if (!function_exists('ks_jobs_get_icon_url')) {
  function ks_jobs_get_icon_url($candidates) {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    return $theme_uri . ks_jobs_get_existing_icon_path($theme_dir, $candidates);
  }
}

if (!function_exists('ks_jobs_get_existing_icon_path')) {
  function ks_jobs_get_existing_icon_path($theme_dir, $candidates) {
    foreach ($candidates as $relative_path) {
      if (file_exists($theme_dir . $relative_path)) {
        return $relative_path;
      }
    }

    return $candidates[0];
  }
}

if (!function_exists('ks_jobs_get_plus_icon_url')) {
  function ks_jobs_get_plus_icon_url() {
    return ks_jobs_get_icon_url(['/assets/img/home/plus.svg', '/assets/img/home/plus.png']);
  }
}

if (!function_exists('ks_jobs_get_minus_icon_url')) {
  function ks_jobs_get_minus_icon_url() {
    return ks_jobs_get_icon_url(['/assets/img/home/minus.svg', '/assets/img/home/minus.png']);
  }
}

if (!function_exists('ks_jobs_get_hero_image_url')) {
  function ks_jobs_get_hero_image_url() {
    return get_stylesheet_directory_uri() . '/assets/img/hero/mfs.png';
  }
}

if (!function_exists('ks_jobs_get_hero_shortcode')) {
  function ks_jobs_get_hero_shortcode($atts) {
    $image = esc_url(ks_jobs_get_hero_image_url());

    return '[ks_hero_page title="' . esc_attr($atts['title']) . '" subtitle="' . esc_attr($atts['subtitle']) . '" breadcrumb="Home" watermark="' . esc_attr($atts['bgword']) . '" image="' . $image . '" variant="jobs" title_i18n="jobs.hero.title" subtitle_i18n="jobs.hero.subtitle" breadcrumb_i18n="common.home" watermark_i18n="jobs.hero.watermark"]';
  }
}

if (!function_exists('ks_jobs_get_shortcode_atts')) {
  function ks_jobs_get_shortcode_atts($atts) {
    return shortcode_atts([
      'title' => 'Aktuelle Jobangebote',
      'subtitle' => 'Arbeiten bei der Dortmunder Fussball Schule',
      'bgword' => 'JOBS',
    ], $atts, 'ks_jobs');
  }
}

if (!function_exists('ks_jobs_get_template_args')) {
  function ks_jobs_get_template_args($atts) {
    return [
      'title' => $atts['title'],
      'subtitle' => $atts['subtitle'],
      'bgword' => $atts['bgword'],
      'plus_url' => ks_jobs_get_plus_icon_url(),
      'minus_url' => ks_jobs_get_minus_icon_url(),
      'items' => ks_get_jobs_items(),
    ];
  }
}

if (!function_exists('ks_jobs_render_inner_content')) {
  function ks_jobs_render_inner_content($atts) {
    ob_start();
    get_template_part('inc/partials/pages/jobs', null, ks_jobs_get_template_args($atts));

    return ob_get_clean();
  }
}

if (!function_exists('ks_render_jobs_shortcode')) {
  function ks_render_jobs_shortcode($atts = []) {
    $data = ks_jobs_get_shortcode_atts($atts);
    $hero = do_shortcode(ks_jobs_get_hero_shortcode($data));
    $content = ks_jobs_render_inner_content($data);

    return $hero . $content;
  }
}

if (!function_exists('ks_register_jobs_shortcode')) {
  function ks_register_jobs_shortcode() {
    add_shortcode('ks_jobs', 'ks_render_jobs_shortcode');
  }

  add_action('init', 'ks_register_jobs_shortcode');
}













