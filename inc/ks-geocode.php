<?php

add_action('rest_api_init', function () {
  register_rest_route('ks/v1', '/geocode', [
    'methods' => 'GET',
    'callback' => 'ks_geocode_proxy',
    'permission_callback' => '__return_true',
  ]);
});

function ks_geocode_proxy(WP_REST_Request $req) {
  $query = ks_geocode_sanitize_query($req->get_param('q'));
  if ($query === '') return ks_geocode_json(['lat' => null, 'lon' => null]);

  $cache_key = ks_geocode_cache_key($query);
  $cached = get_transient($cache_key);
  if (is_array($cached)) return ks_geocode_json($cached);

  $result = ks_geocode_fetch_nominatim($query);
  set_transient($cache_key, $result, 12 * HOUR_IN_SECONDS);
  return ks_geocode_json($result);
}

function ks_geocode_sanitize_query($q) {
  return sanitize_text_field(trim((string) $q));
}

function ks_geocode_cache_key($query) {
  return 'ks_geo_' . md5(strtolower($query));
}

function ks_geocode_json(array $payload) {
  return new WP_REST_Response($payload, 200);
}

function ks_geocode_fetch_nominatim($query) {
  $url = ks_geocode_build_url($query);
  $res = wp_remote_get($url, ks_geocode_request_args());
  if (is_wp_error($res)) return ['lat' => null, 'lon' => null];

  $json = json_decode(wp_remote_retrieve_body($res), true);
  return ks_geocode_extract_first($json);
}

function ks_geocode_build_url($query) {
  return 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&addressdetails=0&countrycodes=de&q=' . rawurlencode($query);
}

function ks_geocode_request_args() {
  return [
    'timeout' => 8,
    'headers' => [
      'Accept' => 'application/json',
      'User-Agent' => 'KickStartTheme/1.0 (WordPress)',
    ],
  ];
}

function ks_geocode_extract_first($json) {
  if (!is_array($json) || empty($json[0]['lat']) || empty($json[0]['lon'])) {
    return ['lat' => null, 'lon' => null];
  }
  return ['lat' => (float) $json[0]['lat'], 'lon' => (float) $json[0]['lon']];
}












