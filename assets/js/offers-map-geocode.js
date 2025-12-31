(function () {
  "use strict";

  const geo_cache = new Map();
  const geo_inflight = new Map();

  function geocode_url() {
    return window.KS_MAP_GEOCODE_URL || "";
  }

  function parse_coord(v) {
    if (v == null) return NaN;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : NaN;
  }

  function parse_proxy_result(j) {
    if (j && typeof j === "object" && !Array.isArray(j)) {
      const lat = parse_coord(j.lat);
      const lng = parse_coord(j.lon ?? j.lng);
      return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    }
    if (Array.isArray(j) && j.length) {
      const lat = parse_coord(j[0].lat);
      const lng = parse_coord(j[0].lon);
      return Number.isFinite(lat) && Number.isFinite(lng) ? [lat, lng] : null;
    }
    return null;
  }

  function set_geo_cache(key, value) {
    geo_cache.set(key, value);
    geo_inflight.delete(key);
    return value;
  }

  async function geocode_query(cache_key_fn, q) {
    const key = cache_key_fn(q);
    if (!key) return null;
    if (geo_cache.has(key)) return geo_cache.get(key);
    if (geo_inflight.has(key)) return geo_inflight.get(key);

    const proxy = geocode_url();
    if (!proxy) return set_geo_cache(key, null);

    const url = `${proxy}?q=${encodeURIComponent(q)}`;
    const p = fetch(url, { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((j) => set_geo_cache(key, parse_proxy_result(j)))
      .catch(() => set_geo_cache(key, null));

    geo_inflight.set(key, p);
    return p;
  }

  window.KSOffersMapGeocode = { geocode_query };
})();
