<?php

if (!defined('ABSPATH')) {
  exit;
}

if (!function_exists('ks_i18n_supported_languages')) {
  function ks_i18n_supported_languages() {
    return ['de', 'en', 'tr'];
  }
}

if (!function_exists('ks_i18n_normalize_language')) {
  function ks_i18n_normalize_language($language) {
    $language = strtolower(substr((string) $language, 0, 2));

    return in_array($language, ks_i18n_supported_languages(), true)
      ? $language
      : 'de';
  }
}

if (!function_exists('ks_i18n_get_query_language')) {
  function ks_i18n_get_query_language() {
    if (!isset($_GET['lang'])) {
      return '';
    }

    return sanitize_text_field(wp_unslash($_GET['lang']));
  }
}

if (!function_exists('ks_i18n_get_cookie_language')) {
  function ks_i18n_get_cookie_language() {
    foreach (['ks_lang', 'wpFrontendLng', 'dfs_lang', 'lang'] as $key) {
      if (!isset($_COOKIE[$key])) {
        continue;
      }

      return sanitize_text_field(wp_unslash($_COOKIE[$key]));
    }

    return '';
  }
}

if (!function_exists('ks_i18n_get_locale_language')) {
  function ks_i18n_get_locale_language() {
    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    return substr((string) $locale, 0, 2);
  }
}

if (!function_exists('ks_i18n_get_current_language')) {
  function ks_i18n_get_current_language() {
    $language = ks_i18n_get_query_language();
    $language = $language ?: ks_i18n_get_cookie_language();
    $language = $language ?: ks_i18n_get_locale_language();

    return ks_i18n_normalize_language($language);
  }
}

if (!function_exists('ks_i18n_get_scope_path')) {
  function ks_i18n_get_scope_path($scope, $language) {
    $theme_dir = get_stylesheet_directory();
    $file_name = $scope . '.' . $language . '.json';

    $primary = $theme_dir . '/assets/i18n/' . $language . '/' . $file_name;
    $fallback = $theme_dir . '/assets/i18n/de/' . $scope . '.de.json';

    return file_exists($primary) ? $primary : $fallback;
  }
}

if (!function_exists('ks_i18n_decode_file')) {
  function ks_i18n_decode_file($path) {
    if (!file_exists($path)) {
      return [];
    }

    $content = file_get_contents($path);

    if ($content === false) {
      return [];
    }

    $data = json_decode($content, true);

    return is_array($data) ? $data : [];
  }
}

if (!function_exists('ks_i18n_load_scope')) {
  function ks_i18n_load_scope($scope) {
    static $cache = [];

    $language = ks_i18n_get_current_language();
    $cache_key = $language . ':' . $scope;

    if (isset($cache[$cache_key])) {
      return $cache[$cache_key];
    }

    $path = ks_i18n_get_scope_path($scope, $language);
    $cache[$cache_key] = ks_i18n_decode_file($path);

    return $cache[$cache_key];
  }
}

if (!function_exists('ks_i18n_get_nested_value')) {
  function ks_i18n_get_nested_value($data, $key) {
    foreach (explode('.', (string) $key) as $part) {
      if (!is_array($data) || !array_key_exists($part, $data)) {
        return null;
      }

      $data = $data[$part];
    }

    return $data;
  }
}

if (!function_exists('ks_i18n_guess_scope')) {
  function ks_i18n_guess_scope($key) {
    $first_part = strtok((string) $key, '.');

    if (in_array($first_part, ['about', 'pageHero', 'offersHero'], true)) {
      return 'hero';
    }

    if (in_array($first_part, ['franchise', 'jobs'], true)) {
      return 'hero';
    }

    return $first_part ?: '';
  }
}

if (!function_exists('ks_t')) {
  function ks_t($key, $fallback = '', $scope = '') {
    if ($key === '') {
      return $fallback;
    }

    $scope = $scope ?: ks_i18n_guess_scope($key);

    if ($scope === '') {
      return $fallback;
    }

    $value = ks_i18n_get_nested_value(ks_i18n_load_scope($scope), $key);

    return is_string($value) && $value !== '' ? $value : $fallback;
  }
}

if (!function_exists('ks_echo_t')) {
  function ks_echo_t($key, $fallback = '', $scope = '') {
    echo esc_html(ks_t($key, $fallback, $scope));
  }
}