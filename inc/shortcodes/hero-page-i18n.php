<?php

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('ks_page_hero_supported_languages')) {
  function ks_page_hero_supported_languages() {
    return ['de', 'en', 'tr'];
  }
}

if (!function_exists('ks_page_hero_normalize_language')) {
  function ks_page_hero_normalize_language($language) {
    $language = strtolower(substr((string) $language, 0, 2));

    return in_array($language, ks_page_hero_supported_languages(), true)
      ? $language
      : 'de';
  }
}

if (!function_exists('ks_page_hero_get_query_language')) {
  function ks_page_hero_get_query_language() {
    if (!isset($_GET['lang'])) {
      return '';
    }

    return sanitize_text_field(wp_unslash($_GET['lang']));
  }
}

if (!function_exists('ks_page_hero_get_cookie_language')) {
  function ks_page_hero_get_cookie_language() {
    foreach (['ks_lang', 'dfs_lang', 'lang'] as $key) {
      if (isset($_COOKIE[$key])) {
        return sanitize_text_field(wp_unslash($_COOKIE[$key]));
      }
    }

    return '';
  }
}

if (!function_exists('ks_page_hero_get_locale_language')) {
  function ks_page_hero_get_locale_language() {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    return substr((string) $locale, 0, 2);
  }
}

if (!function_exists('ks_page_hero_get_current_language')) {
  function ks_page_hero_get_current_language() {
    $language = ks_page_hero_get_query_language();
    $language = $language ?: ks_page_hero_get_cookie_language();
    $language = $language ?: ks_page_hero_get_locale_language();

    return ks_page_hero_normalize_language($language);
  }
}

if (!function_exists('ks_page_hero_get_i18n_path')) {
  function ks_page_hero_get_i18n_path($language) {
    $theme_dir = get_stylesheet_directory();
    $primary = $theme_dir . '/assets/i18n/' . $language . '/hero.' . $language . '.json';
    $fallback = $theme_dir . '/assets/i18n/de/hero.de.json';

    return file_exists($primary) ? $primary : $fallback;
  }
}

if (!function_exists('ks_page_hero_decode_i18n_file')) {
  function ks_page_hero_decode_i18n_file($language) {
    $path = ks_page_hero_get_i18n_path($language);
    $content = file_exists($path) ? file_get_contents($path) : false;

    if ($content === false) {
      return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
  }
}

if (!function_exists('ks_page_hero_load_i18n')) {
  function ks_page_hero_load_i18n() {
    static $cache = [];

    $language = ks_page_hero_get_current_language();

    if (isset($cache[$language])) {
      return $cache[$language];
    }

    $cache[$language] = ks_page_hero_decode_i18n_file($language);

    return $cache[$language];
  }
}

if (!function_exists('ks_page_hero_get_nested_value')) {
  function ks_page_hero_get_nested_value($data, $key) {
    foreach (explode('.', (string) $key) as $part) {
      if (!is_array($data) || !array_key_exists($part, $data)) {
        return null;
      }

      $data = $data[$part];
    }

    return $data;
  }
}

if (!function_exists('ks_page_hero_translate')) {
  function ks_page_hero_translate($key, $fallback = '') {
    if ($key === '') {
      return $fallback;
    }

    $value = ks_page_hero_get_nested_value(ks_page_hero_load_i18n(), $key);

    return is_string($value) && $value !== '' ? $value : $fallback;
  }
}

if (!function_exists('ks_apply_page_hero_i18n')) {
  function ks_apply_page_hero_i18n($data) {
    foreach (ks_get_page_hero_i18n_map() as $value_key => $i18n_key) {
      $data[$value_key] = ks_page_hero_translate(
        $data[$i18n_key],
        $data[$value_key]
      );
    }

    return $data;
  }
}

if (!function_exists('ks_get_page_hero_i18n_map')) {
  function ks_get_page_hero_i18n_map() {
    return [
      'title' => 'title_i18n',
      'subtitle' => 'subtitle_i18n',
      'breadcrumb' => 'breadcrumb_i18n',
      'eyebrow' => 'eyebrow_i18n',
      'primary_label' => 'primary_i18n',
      'secondary_label' => 'secondary_i18n',
    ];
  }
}