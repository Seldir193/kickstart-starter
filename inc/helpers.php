<?php
/* -------------------------------------------------------
 * Helpers
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
