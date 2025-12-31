(function () {
  "use strict";

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const GEO = window.KSOffersMapGeocode || null;

  const esc = (s) =>
    String(s ?? "").replace(
      /[&<>"']/g,
      (m) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[m])
    );

  function normalize_city(s) {
    if (!s) return "";
    let out = String(s)
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "");
    return out
      .replace(/[^a-z0-9]+/gi, " ")
      .trim()
      .toLowerCase();
  }

  function location_city_from_string(s) {
    const raw = String(s || "").trim();
    if (!raw) return "";
    const dash = raw.split(/\s*[–—-]\s*/);
    const tail = dash[dash.length - 1] || raw;
    const parts = tail
      .split(",")
      .map((x) => x.trim())
      .filter(Boolean);
    const last = parts.length ? parts[parts.length - 1] : tail;
    const m = last.match(/\b\d{5}\s+(.+)$/);
    return (m ? m[1] : last).trim();
  }

  function city_from_offer(o) {
    if (!o) return "";
    if (o.city) return String(o.city).trim();
    if (o.standort) return String(o.standort).trim();
    if (o.location) return location_city_from_string(o.location);
    return "";
  }

  const is_lat = (n) => Number.isFinite(n) && n >= -90 && n <= 90;
  const is_lng = (n) => Number.isFinite(n) && n >= -180 && n <= 180;

  function parse_coord(v) {
    if (v == null) return NaN;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : NaN;
  }

  function normalize_pair(a, b) {
    if (is_lat(a) && is_lng(b)) return [a, b];
    if (is_lng(a) && is_lat(b)) return [b, a];
    return [a, b];
  }

  function parse_latlng_string(s) {
    const m = String(s || "")
      .trim()
      .match(/(-?\d+(?:[.,]\d+)?)[\s,;]+(-?\d+(?:[.,]\d+)?)/);
    if (!m) return null;
    const a = parse_coord(m[1]),
      b = parse_coord(m[2]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    const p = normalize_pair(a, b);
    return is_lat(p[0]) && is_lng(p[1]) ? p : null;
  }

  function coords_from_array(arr) {
    if (!Array.isArray(arr) || arr.length < 2) return null;
    const a = parse_coord(arr[0]),
      b = parse_coord(arr[1]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    const p = normalize_pair(a, b);
    return is_lat(p[0]) && is_lng(p[1]) ? p : null;
  }

  function first_finite(cands) {
    for (const c of cands) {
      const v = parse_coord(c);
      if (Number.isFinite(v)) return v;
    }
    return NaN;
  }

  function top_level_latlng(o) {
    const lat = first_finite([o?.lat, o?.latitude, o?.latDeg]);
    const lng = first_finite([o?.lng, o?.lon, o?.long, o?.longitude]);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return null;
    const p = normalize_pair(lat, lng);
    return is_lat(p[0]) && is_lng(p[1]) ? p : null;
  }

  function nested_candidates(o) {
    return [
      o?.coords,
      o?.coord,
      o?.position,
      o?.geo,
      o?.gps,
      o?.map,
      o?.center,
      o?.centerPoint,
      o?.point,
      o?.location,
    ].filter(Boolean);
  }

  function object_latlng(c) {
    const lat = first_finite([c?.lat, c?.latitude]);
    const lng = first_finite([c?.lng, c?.lon, c?.long, c?.longitude]);
    return is_lat(lat) && is_lng(lng) ? [lat, lng] : null;
  }

  function nested_latlng(o) {
    for (const c of nested_candidates(o)) {
      const ll = typeof c === "object" ? object_latlng(c) : null;
      if (ll) return ll;
      if (typeof c === "string") {
        const p = parse_latlng_string(c);
        if (p) return p;
      }
      const p = extract_latlng_from_object_strings(c);
      if (p) return p;
      const a = extract_latlng_from_object_arrays(c);
      if (a) return a;
    }
    return null;
  }

  function extract_latlng_from_object_strings(c) {
    for (const k of Object.keys(c || {})) {
      if (typeof c?.[k] === "string") {
        const p = parse_latlng_string(c[k]);
        if (p) return p;
      }
    }
    return null;
  }

  function extract_latlng_from_object_arrays(c) {
    if (!c || typeof c !== "object") return null;
    if (c.coordinates) return coords_from_array(c.coordinates);
    if (c.coords) return coords_from_array(c.coords);
    if (c.latlng) return coords_from_array(c.latlng);
    return null;
  }

  function scan_latlng_in_strings(o) {
    for (const [, v] of Object.entries(o || {})) {
      if (typeof v === "string") {
        const p = parse_latlng_string(v);
        if (p) return p;
      }
    }
    return null;
  }

  function latlng_of(o) {
    return top_level_latlng(o) || nested_latlng(o) || scan_latlng_in_strings(o);
  }

  function points_from(items = []) {
    const pts = [];
    items.forEach((o) => {
      const ll = latlng_of(o);
      if (ll) pts.push(ll);
    });
    return pts;
  }

  function geocode_cache_key(q) {
    return (
      normalize_city(q) ||
      String(q || "")
        .trim()
        .toLowerCase()
    );
  }

  function offer_query(o) {
    const street = o?.address || o?.street || "";
    const zip = o?.zip || o?.postalCode || "";
    const city = o?.city || city_from_offer(o) || "";
    const loc = String(o?.location || "").trim();
    const line = [street, [zip, city].filter(Boolean).join(" ")].filter(
      Boolean
    );
    const full = line.join(", ").trim();
    if (full && full.length >= 6) return full;
    if (loc && loc.length >= 6) return loc;
    if (city) return city;
    return String(o?.title || o?.club || o?.provider || "").trim();
  }

  async function geocode_offers_to_points(arr, limit = 20) {
    const pts = [];
    const seen = new Set();
    for (const o of arr || []) {
      const q = offer_query(o);
      const k = geocode_cache_key(q) || q;
      if (!q || seen.has(k)) continue;
      seen.add(k);

      const ll = await GEO?.geocode_query?.(geocode_cache_key, q);

      if (ll) pts.push(ll);
      if (pts.length >= limit) break;
    }
    return pts;
  }

  const DEFAULT_CENTER = [51.1657, 10.4515];
  const DEFAULT_Z = 6;

  function ensure_map_height(el) {
    if (!el) return;
    if (el.getBoundingClientRect().height < 50) el.style.height = "360px";
  }

  function init_map(el) {
    if (!el || !window.L) return null;
    const map = L.map(el, { scrollWheelZoom: true, zoomControl: true });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "© OpenStreetMap",
      crossOrigin: true,
    }).addTo(map);
    map.setView(DEFAULT_CENTER, DEFAULT_Z);
    map.whenReady(() => map.invalidateSize());
    requestAnimationFrame(() => map.invalidateSize());
    setTimeout(() => map.invalidateSize(), 150);
    if (window.ResizeObserver)
      new ResizeObserver(() => map.invalidateSize()).observe(el);
    return map;
  }

  function fly_to(map, pts) {
    if (!map || !pts.length) return;
    if (pts.length === 1) map.flyTo(pts[0], 12, { duration: 0.5 });
    else map.flyToBounds(L.latLngBounds(pts), { padding: [24, 24] });
  }

  function reset_view(map, all_pts) {
    if (!map) return;
    if (all_pts.length > 1) fly_to(map, all_pts);
    else if (all_pts.length === 1) map.flyTo(all_pts[0], 12, { duration: 0.5 });
    else map.setView(DEFAULT_CENTER, DEFAULT_Z);
  }

  function clear_markers(markers) {
    (markers || []).forEach((m) => m.remove());
    return [];
  }

  function popup_html(o) {
    const t = esc(o?.title || o?.type || "Standort");
    const l = esc(o?.location || "");
    return `<strong>${t}</strong><br>${l}`;
  }

  function list_item_for_index(root, i) {
    const list = $("#ksDirList", root);
    return list?.querySelector(`.ks-offer[data-offer-index="${i}"]`) || null;
  }

  function bind_marker(mk, o, i, root) {
    mk.bindPopup(popup_html(o));
    mk.on("click", () => {
      const li = list_item_for_index(root, i);
      if (li) li.scrollIntoView({ behavior: "smooth", block: "start" });
    });
  }

  function add_marker(map, o, i, root, ll) {
    const mk = L.marker(ll).addTo(map);
    bind_marker(mk, o, i, root);
    return mk;
  }

  function split_markers_by_coords(arr) {
    const ready = [];
    const missing = [];
    (arr || []).forEach((o, i) => {
      const ll = latlng_of(o);
      if (ll) ready.push({ o, i, ll });
      else missing.push({ o, i });
    });
    return { ready, missing };
  }

  function next_render_token(state) {
    state.render_token += 1;
    return state.render_token;
  }

  function is_cancelled(state, token) {
    return token !== state.render_token;
  }

  async function geocode_missing_markers(state, missing) {
    const MAX = 60;
    for (const it of (missing || []).slice(0, MAX)) {
      if (is_cancelled(state, state.token)) return;
      const q = offer_query(it.o);
      if (!q) continue;

      const ll = await GEO?.geocode_query?.(geocode_cache_key, q);

      if (is_cancelled(state, state.token)) return;
      if (ll)
        state.markers.push(add_marker(state.map, it.o, it.i, state.root, ll));
    }
  }

  function render_markers_impl(state, display_arr) {
    if (!state.map) return;
    state.token = next_render_token(state);
    state.markers = clear_markers(state.markers);
    const { ready, missing } = split_markers_by_coords(display_arr);
    ready.forEach((x) =>
      state.markers.push(add_marker(state.map, x.o, x.i, state.root, x.ll))
    );
    geocode_missing_markers(state, missing);
  }

  async function focus_for_filters_impl(state, args) {
    if (!state.map) return;
    const filtered = Array.isArray(args?.filtered) ? args.filtered : [];

    if (!filtered.length) return reset_view(state.map, state.all_pts);

    const pts = points_from(filtered);
    if (pts.length) return fly_to(state.map, pts);

    const ge_pts = await geocode_offers_to_points(filtered, 20);
    if (ge_pts.length) return fly_to(state.map, ge_pts);

    const loc = String(args?.loc || "").trim();
    if (loc) {
      const g = await GEO?.geocode_query?.(geocode_cache_key, loc);

      if (g) return fly_to(state.map, [g]);
    }
    reset_view(state.map, state.all_pts);
  }

  function create(root) {
    const el = $("#ksMap", root);
    ensure_map_height(el);

    const map = init_map(el);
    const state = {
      root,
      map,
      markers: [],
      all_pts: [],
      render_token: 0,
      token: 0,
    };

    function set_all_points(items) {
      state.all_pts = points_from(items || []);
      reset_view(state.map, state.all_pts);
    }

    function render_markers(display_arr) {
      render_markers_impl(state, display_arr || []);
    }

    function focus_offer(offer, zoom = 14) {
      const ll = latlng_of(offer);
      if (ll && state.map) state.map.setView(ll, zoom, { animate: true });
    }

    function reset_view_public() {
      reset_view(state.map, state.all_pts);
    }

    async function focus_for_filters(args) {
      await focus_for_filters_impl(state, args || {});
    }

    return {
      map: state.map,
      set_all_points,
      render_markers,
      focus_offer,
      reset_view: reset_view_public,
      focus_for_filters,
    };
  }

  window.KSOffersDirectoryMap = { create };
})();
