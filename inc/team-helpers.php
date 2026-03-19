<?php
/**
 * Shared helpers for Team section (Home + About)
 */

if (!function_exists('ks_get_coaches')) {
  function ks_get_coaches(int $limit = 48): array {
    $next_base = function_exists('ks_next_base') ? ks_next_base() : '';
    if (!$next_base) return [];

    $candidates = [
      trailingslashit($next_base) . 'api/coaches?limit=' . $limit,
      trailingslashit($next_base) . 'api/admin/coaches?limit=' . $limit,
    ];

    foreach ($candidates as $api) {
      $res = wp_remote_get($api, ['timeout' => 10, 'headers' => ['Accept' => 'application/json']]);
      if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
        $json = json_decode(wp_remote_retrieve_body($res), true);

        if (isset($json['items']) && is_array($json['items'])) return $json['items'];
        if (is_array($json)) return $json;
      }
    }

    return [];
  }
}

if (!function_exists('ks_get_trainer_url')) {
  function ks_get_trainer_url(): string {
    $page_by_path = get_page_by_path('trainer');
    if ($page_by_path) return get_permalink($page_by_path->ID);

    $pages = get_posts([
      'post_type'      => 'page',
      's'              => '[ks_trainer_profile]',
      'posts_per_page' => 1,
    ]);
    if (!empty($pages)) return get_permalink($pages[0]->ID);

    return home_url('/trainer/');
  }
}

if (!function_exists('ks_enqueue_team_assets')) {
  function ks_enqueue_team_assets(): void {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $team_js = $theme_dir . '/assets/js/ks-team.js';
    if (file_exists($team_js)) {
      wp_enqueue_script('ks-team', $theme_uri . '/assets/js/ks-team.js', [], filemtime($team_js), true);
    }
  }
}

if (!function_exists('ks_normalize_next_img')) {
  function ks_normalize_next_img($u): string {
    $u = trim((string)$u);
    if ($u === '') return '';

    // Absolut (http/https) ODER Base64-Data-URL → direkt zurückgeben
    if (preg_match('~^(https?://|data:image/)~i', $u)) {
      return $u;
    }

    // führendes "admin/" entfernen
    $u = preg_replace('#^/?admin/#i', '', $u);

    // relative Pfade an NEXT-Base anhängen
    $base = function_exists('ks_next_base') ? rtrim(ks_next_base(), '/') : '';
    if ($base) {
      if ($u[0] !== '/') $u = '/' . $u;
      return $base . $u;
    }

    return $u;
  }
}
