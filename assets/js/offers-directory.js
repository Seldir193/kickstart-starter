// assets/js/offers-directory.js
(function () {
  "use strict";

  /* ===== helpers (dom, text) ===== */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const esc = (s) => String(s).replace(/[&<>"']/g, m => (
    { "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;" }[m]
  ));

  /* ===== day helpers ===== */
  const DAY_ALIASES = {
    m:"Mo", mo:"Mo", montag:"Mo", monday:"Mo", mon:"Mo",
    di:"Di", dienstag:"Di", tuesday:"Di", tue:"Di",
    mi:"Mi", mittwoch:"Mi", wednesday:"Mi", wed:"Mi",
    do:"Do", donnerstag:"Do", thursday:"Do", thu:"Do",
    fr:"Fr", freitag:"Fr", friday:"Fr", fri:"Fr",
    sa:"Sa", samstag:"Sa", saturday:"Sa", sat:"Sa",
    so:"So", sonntag:"So", sunday:"So", sun:"So"
  };
  const DAY_LONG = {
    Mo:"Montag", Di:"Dienstag", Mi:"Mittwoch",
    Do:"Donnerstag", Fr:"Freitag", Sa:"Samstag", So:"Sonntag"
  };
  const normDay = (v) => v ? (DAY_ALIASES[String(v).trim().toLowerCase()] || v) : "";
  const offerHasDay = (o, code) => {
    if (!code) return true;
    const arr = Array.isArray(o.days) ? o.days : [];
    return arr.some(d => normDay(d) === code);
  };

  /* ===== city helpers ===== */
  function normalizeCity(s){
    if (!s) return "";
    let out = String(s).normalize("NFD").replace(/\p{Diacritic}/gu, "");
    return out.replace(/[^a-z0-9]+/gi, " ").trim().toLowerCase();
  }

  function cityMatches(itemLoc, selectedLoc){
    const a = normalizeCity(itemLoc), b = normalizeCity(selectedLoc);
    if (!a || !b) return false;
    return a === b || a.includes(b) || b.includes(a);
  }

  function cityFromLocationString(s){
    const raw = String(s || "").trim();
    if (!raw) return "";
    const split = raw.split(/\s*[-–—,•|]\s*/);
    return split[0] || raw;
  }

  const normalizeStandort = (o) => String(o.location || "").trim();

  // Liefert eine Stadt / Standortnamen aus einem Offer
  function cityFromOffer(o) {
    if (o.city) return String(o.city).trim();       // bevorzugt "city" aus der API
    if (o.standort) return String(o.standort).trim(); // falls später so benannt
    if (o.location) return cityFromLocationString(o.location);
    return "";
  }

  /* ===== coords parser ===== */
  const isLat = (n) => Number.isFinite(n) && n >= -90 && n <= 90;
  const isLng = (n) => Number.isFinite(n) && n >= -180 && n <= 180;
  const parseCoord = (v) => {
    if (v == null) return NaN;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : NaN;
  };
  const normalizePair = (a,b) =>
    (isLat(a) && isLng(b)) ? [a,b] :
    (isLng(a) && isLat(b) ? [b,a] : [a,b]);

  const parseLatLngString = (s) => {
    const m = String(s||"").trim().match(
      /(-?\d+(?:[.,]\d+)?)[\s,;]+(-?\d+(?:[.,]\d+)?)/);
    if (!m) return null;
    const a = parseCoord(m[1]), b = parseCoord(m[2]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    return normalizePair(a,b);
  };

  const coordsFromArray = (arr) => {
    if (!Array.isArray(arr) || arr.length < 2) return null;
    const a = parseCoord(arr[0]), b = parseCoord(arr[1]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    return normalizePair(a,b);
  };

  const firstFinite = (cands) => {
    for (const c of cands) {
      const v = parseCoord(c);
      if (Number.isFinite(v)) return v;
    }
    return NaN;
  };

  function latLngOf(o){
    { // flat fields
      const lat = firstFinite([o.lat, o.latitude, o.latDeg]);
      const lng = firstFinite([o.lng, o.lon, o.long, o.longitude]);
      if (isLat(lat) && isLng(lng)) return [lat, lng];
    }
    const C = [
      o.coords, o.coord, o.position, o.geo, o.gps, o.map,
      o.center, o.centerPoint, o.point, o.location
    ].filter(Boolean);

    for (const c of C) {
      const lat = firstFinite([c?.lat, c?.latitude]);
      const lng = firstFinite([c?.lng, c?.lon, c?.long, c?.longitude]);
      if (isLat(lat) && isLng(lng)) return [lat, lng];

      if (typeof c === "string") {
        const p = parseLatLngString(c);
        if (p) return p;
      }
      for (const k of Object.keys(c || {})) {
        if (typeof c[k] === "string") {
          const p = parseLatLngString(c[k]);
          if (p) return p;
        }
      }
      if (c?.coordinates) {
        const p = coordsFromArray(c.coordinates);
        if (p) return p;
      }
      if (c?.coords) {
        const p = coordsFromArray(c.coords);
        if (p) return p;
      }
      if (c?.latlng) {
        const p = coordsFromArray(c.latlng);
        if (p) return p;
      }
    }

    for (const key of ["latlng","lat_lon","lon_lat"]) {
      const p = parseLatLngString(o[key]);
      if (p) return p;
    }
    for (const [k,v] of Object.entries(o)) {
      if (typeof v === "string") {
        const p = parseLatLngString(v);
        if (p) return p;
      }
    }
    return null;
  }

  const pointsFrom = (items=[]) => {
    const pts = [];
    items.forEach(o => {
      const ll = latLngOf(o);
      if (ll) pts.push(ll);
    });
    return pts;
  };

  /* ===== geocoding (Nominatim) ===== */
  const GEOCODE_ENDPOINT = "https://nominatim.openstreetmap.org/search";
  const geoCache = new Map(), inflight = new Map();

  async function geocodeCity(name){
    const key = normalizeCity(name);
    if (!key) return null;
    if (geoCache.has(key)) return geoCache.get(key);
    if (inflight.has(key))  return inflight.get(key);

    const url = `${GEOCODE_ENDPOINT}?format=jsonv2&q=${encodeURIComponent(name)}&limit=1&addressdetails=0`;
    const p = fetch(url, { headers: { "Accept": "application/json" } })
      .then(r => r.json())
      .then(j => {
        let res = null;
        if (Array.isArray(j) && j.length) {
          const lat = parseCoord(j[0].lat), lon = parseCoord(j[0].lon);
          if (Number.isFinite(lat) && Number.isFinite(lon)) res = [lat, lon];
        }
        geoCache.set(key, res);
        inflight.delete(key);
        return res;
      })
      .catch(() => {
        inflight.delete(key);
        geoCache.set(key, null);
        return null;
      });
    inflight.set(key, p);
    return p;
  }

  async function geocodeCities(names, limit = 8){
    const pts = [], seen = new Set();
    for (const n of names) {
      const k = normalizeCity(n);
      if (!k || seen.has(k)) continue;
      seen.add(k);
      const p = await geocodeCity(n);
      if (p) pts.push(p);
      if (pts.length >= limit) break;
    }
    return pts;
  }

  /* ===== map setup ===== */
  const DEFAULT_CENTER = [51.1657, 10.4515]; // Germany
  const DEFAULT_Z = 6;

  function ensureMapHeight(el){
    if (el && el.getBoundingClientRect().height < 50) {
      el.style.height = "360px";
    }
  }

  function initMap(el){
    if (!el || !window.L) return null;
    const map = L.map(el, { scrollWheelZoom: true, zoomControl: true });
    L.tileLayer(
      "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      { maxZoom: 19, attribution: "© OpenStreetMap" }
    ).addTo(map);
    map.setView(DEFAULT_CENTER, DEFAULT_Z);
    map.whenReady(() => map.invalidateSize());
    requestAnimationFrame(() => map.invalidateSize());
    setTimeout(() => map.invalidateSize(), 150);
    if (window.ResizeObserver) {
      new ResizeObserver(() => map.invalidateSize()).observe(el);
    }
    return map;
  }

  const clearMarkers = (ms) => { ms.forEach(m => m.remove()); return []; };

  function setMarkers(map, arr, listEl){
    const ms = [];
    arr.forEach((o, i) => {
      const ll = latLngOf(o);
      if (!ll) return;
      const mk = L.marker(ll).addTo(map);
      mk.bindPopup(
        `<strong>${esc(o.title || o.type || "Standort")}</strong><br>${esc(o.location || "")}`
      );
      mk.on("click", () => {
        const li = listEl?.querySelector(`.ks-offer[data-offer-index="${i}"]`);
        if (li) li.scrollIntoView({ behavior: "smooth", block: "start" });
      });
      ms.push(mk);
    });
    return ms;
  }

  function flyTo(map, pts){
    if (!pts.length) return;
    if (pts.length === 1) {
      map.flyTo(pts[0], 12, { duration: 0.5 });
    } else {
      map.flyToBounds(L.latLngBounds(pts), { padding: [24,24] });
    }
  }

  function resetView(map, allPts){
    if (!map) return;
    if (allPts.length > 1) flyTo(map, allPts);
    else if (allPts.length === 1) map.flyTo(allPts[0], 12, { duration: 0.5 });
    else map.setView(DEFAULT_CENTER, DEFAULT_Z);
  }

  /* ===== UI rendering ===== */
  function buildUrl(base, q){
    const u = new URL(base, window.location.origin);
    Object.entries(q || {}).forEach(([k,v]) => {
      if (v != null && v !== "") u.searchParams.set(k, v);
    });
    return u.toString();
  }

  function fillLocations(selectEl, arr){
    if (!selectEl) return;
    const cities = Array.from(
      new Set(
        arr
          .map(o => cityFromOffer(o))
          .filter(Boolean)
      )
    ).sort((a,b) => a.localeCompare(b, "de"));

    selectEl.innerHTML =
      `<option value="">Alle Standorte</option>` +
      cities.map(c => `<option>${esc(c)}</option>`).join("");
  }

  function nameAddr(o){
    const name = o.clubName || o.club || o.provider || o.title || o.type || "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""]
      .filter(Boolean).join(", ");
    const addr = (l1 && l2)
      ? `${l1} - ${l2}`
      : (l1 || l2 || (o.location || ""));
    return { name, addr };
  }

  function renderList(listEl, arr, ref, map, onOpen){
    if (!listEl) return;
    if (!arr.length) {
      listEl.innerHTML = '<li><div class="card">Keine Angebote gefunden.</div></li>';
      return;
    }
    listEl.innerHTML = arr.map((o,i) => {
      const { name, addr } = nameAddr(o);
      return `<li class="ks-offer" data-offer-index="${i}">
        <article class="card">
          <h3 class="card-title">${esc(name)}</h3>
          ${addr ? `<div class="offer-meta">${esc(addr)}</div>` : ""}
        </article>
      </li>`;
    }).join("");

    $$(".ks-offer", listEl).forEach(li => {
      li.addEventListener("click", () => {
        const i = parseInt(li.dataset.offerIndex || "-1", 10);
        const offer = ref[i];
        const ll = offer ? latLngOf(offer) : null;
        if (ll && map) map.setView(ll, 14, { animate: true });

        // Sessions am selben Standort (exakter location-String)
        const key = normalizeStandort(offer);
        const sessions = ref.filter(x => normalizeStandort(x) === key);

        const root = $("#ksDir");
        const NEXT = root?.dataset?.next || "http://localhost:3000";
        if (window.KSOffersDialog) {
          window.KSOffersDialog.open(
            offer,
            sessions.length ? sessions : [offer],
            { nextBase: NEXT }
          );
        }
      });
    });
  }

  function setCounters(root, arr){
    const o = $("[data-count-offers]", root);
    const l = $("[data-count-locations]", root);
    if (o) o.textContent = String(arr.length);
    if (l) {
      const s = new Set(
        arr.map(x => cityFromOffer(x)).filter(Boolean)
      );
      l.textContent = String(s.size);
    }
  }

  function setAgeHeadline(el, arr, courseKey) {
    if (!el) return;

    const key = String(courseKey || "").trim();

    // 1) feste Bereiche je nach Kurs
    switch (key) {
      // Weekly Courses
      case "Kindergarten":
        el.textContent = "4–6 Jahre";
        return;

      case "Foerdertraining":
      case "Foerdertraining_Athletik":
      case "Torwarttraining":
        el.textContent = "7–17 Jahre";
        return;

      // Holiday Programs
      case "Camp":
        el.textContent = "6–13 Jahre";
        return;

      case "AthleticTraining":
      case "Powertraining":
      case "AthletikTraining":
        el.textContent = "7–17 Jahre";
        return;

      // Individual Courses
      case "PersonalTraining":
      case "Einzeltraining_Athletik":
      case "Einzeltraining_Torwart":
        el.textContent = "6–25 Jahre";
        return;

      // Coach Education
      case "CoachEducation":
        el.textContent = "alle Altersstufen";
        return;
    }

    // 2) Fallback: dynamisch aus den Offer-Daten
    let min = null, max = null;
    arr.forEach(o => {
      const from = Number(o.ageFrom);
      const to   = Number(o.ageTo);
      if (Number.isFinite(from)) {
        min = (min == null) ? from : Math.min(min, from);
      }
      if (Number.isFinite(to)) {
        max = (max == null) ? to : Math.max(max, to);
      }
    });

    if (min != null && max != null) {
      el.textContent = `${min}–${max} Jahre`;
    } else {
      el.textContent = "alle Altersstufen";
    }
  }

  /* ===== movement logic ===== */
  async function moveForLoc(map, loc, items, filtered){
    const pts = pointsFrom(filtered);
    if (pts.length) { flyTo(map, pts); return; }

    const locItems = items.filter(o => {
      const c = cityFromOffer(o);
      return cityMatches(c, loc);
    });

    const p2 = pointsFrom(locItems);
    if (p2.length) { flyTo(map, p2); return; }

    const g = await geocodeCity(loc);
    if (g) flyTo(map, [g]);
  }

  async function moveForDay(map, day, items, filtered){
    const pts = pointsFrom(filtered);
    if (pts.length) { flyTo(map, pts); return; }
    const dItems = items.filter(o => offerHasDay(o, day));
    const p2 = pointsFrom(dItems);
    if (p2.length) { flyTo(map, p2); return; }
    const names = Array.from(new Set(
      dItems.map(o => cityFromLocationString(o.location)).filter(Boolean)
    ));
    const gs = await geocodeCities(names, 8);
    if (gs.length) flyTo(map, gs);
  }

  async function moveForAge(map, age, items, filtered){
    const pts = pointsFrom(filtered);
    if (pts.length) { flyTo(map, pts); return; }

    const aItems = items.filter(o => {
      const f = Number(o.ageFrom ?? 0);
      const t = Number(o.ageTo ?? 99);
      return Number.isFinite(f) && Number.isFinite(t) &&
             age >= f && age <= t;
    });

    const p2 = pointsFrom(aItems);
    if (p2.length) { flyTo(map, p2); return; }
    const names = Array.from(new Set(
      aItems.map(o => cityFromLocationString(o.location)).filter(Boolean)
    ));
    const gs = await geocodeCities(names, 8);
    if (gs.length) flyTo(map, gs);
  }

  /* ===== type helper (legacy) ===== */
  function matchesType(o, t) {
    if (!t) return true;
    const a = String(o.type || "");
    const b = String(o.legacy_type || "");
    return a === t || b === t;
  }

  /* ===== Program-/Kurs-Gruppen (Standard vs. Spezialprogramme) ===== */

  // Normalisiert Strings wie "RentACoach_Generic", "Rent a Coach", "rent-a-coach"
  function normalizeProgramKey(str) {
    return String(str || "")
      .toLowerCase()
      .replace(/[\s_\-]+/g, "");
  }

  // Ermittelt die Programm-Gruppe eines Keys (TYPE / SUBTYPE / CATEGORY)
  function getProgramGroupFromKey(key) {
    const n = normalizeProgramKey(key);
    if (!n) return "standard";

    if (n.includes("rentacoach"))     return "rentacoach";
    if (n.includes("clubprogram"))    return "clubprogram";
    if (n.includes("coacheducation")) return "coacheducation";
    if (n.includes("camp") || n.includes("trainingscamp")) return "camp";

    // alles andere sind Standard-Programme (Fördertraining, Kindergarten, usw.)
    return "standard";
  }

  // Ermittelt die Programm-Gruppe für ein Offer (aus type / sub_type / category)
  function getProgramGroupForOffer(o) {
    const parts = [
      o.sub_type,
      o.category,
      o.type
    ].filter(Boolean);

    const combined = parts.join("|");
    return getProgramGroupFromKey(combined);
  }

  // Filtert die Gesamtliste passend zum aktuell ausgewählten Programm
  function filterByProgram(allItems, currentKey) {
    const currentGroup = getProgramGroupFromKey(currentKey);

    return allItems.filter((o) => {
      const g = getProgramGroupForOffer(o);

      if (currentGroup === "standard") {
        // z.B. Fördertraining-Seite:
        // → NUR Standard-Programme anzeigen, Spezialprogramme ausblenden
        return g === "standard";
      }

      // Spezialseite (rentacoach, clubprogram, coacheducation, camp)
      // → NUR die passende Gruppe anzeigen
      return g === currentGroup;
    });
  }

  /* ===== main ===== */
  document.addEventListener("DOMContentLoaded", async () => {
    const root = $("#ksDir");
    if (!root) return;

    const daySel   = $("#ksFilterDay", root);
    const ageSel   = $("#ksFilterAge", root);
    const locSel   = $("#ksFilterLoc", root);
    const listEl   = $("#ksDirList", root);
    const ageTitle = $("[data-age-title]", root);

    const TYPE = root.dataset.type || "";
    const API  = root.dataset.api  || "http://localhost:5000";
    const CITY = root.dataset.city || "";

    const CATEGORY = root.dataset.category || "";
    const SUBTYPE  = root.dataset.subtype  || "";

    const mapNode = $("#ksMap", root);
    ensureMapHeight(mapNode);
    const map = initMap(mapNode);

    let items = [], filtered = [], markers = [], allPts = [];

    // Backend direkt mitfiltern lassen
    const url = buildUrl(`${API}/api/offers`, {
      type: TYPE || undefined,
      category: CATEGORY || undefined,
      sub_type: SUBTYPE || undefined,
      limit: 500
    });

    try {
      const data = await fetch(url).then(r => r.json());
      items = Array.isArray(data?.items) ? data.items
             : (Array.isArray(data) ? data : []);

      // Program-Filter anwenden (z.B. Fördertraining vs. Rent-a-Coach etc.)
      const currentProgramKey = (SUBTYPE || TYPE || "").trim();
      items = filterByProgram(items, currentProgramKey);

      fillLocations(locSel, items);

      if (CITY && locSel) {
        const opt = Array.from(locSel.options).find(
          o => normalizeCity(o.value) === normalizeCity(CITY)
        );
        if (opt) locSel.value = opt.value;
      }

      allPts = pointsFrom(items);
    } catch (e) {
      if (listEl) {
        listEl.innerHTML =
          '<li><div class="card">Keine Angebote gefunden.</div></li>';
      }
    }

    async function apply(){
      const day = normDay(daySel?.value || "");
      const age = (ageSel && ageSel.value !== "")
        ? parseInt(ageSel.value, 10)
        : NaN;
      const loc = (locSel?.value || "").trim();

      filtered = items.filter(o => {
        if (TYPE && !matchesType(o, TYPE)) return false;
        if (CATEGORY && o.category !== CATEGORY) return false;
        if (SUBTYPE && o.sub_type !== SUBTYPE) return false;

        if (day && !offerHasDay(o, day)) return false;

        if (!isNaN(age)) {
          const f = Number(o.ageFrom ?? 0);
          const t = Number(o.ageTo ?? 99);
          if (!(age >= f && age <= t)) return false;
        }

        const offerCity = cityFromOffer(o);
        if (loc && !cityMatches(offerCity, loc)) return false;

        return true;
      });

      renderList(listEl, filtered, filtered, map, (offer) => {
        if (!offer) return;
        const key = normalizeStandort(offer);
        const sessions = items.filter(x => normalizeStandort(x) === key);
        const NEXT = root?.dataset?.next || "http://localhost:3000";
        if (window.KSOffersDialog) {
          window.KSOffersDialog.open(
            offer,
            sessions.length ? sessions : [offer],
            { nextBase: NEXT }
          );
        }
      });

      if (map) {
        markers = clearMarkers(markers);
        markers = setMarkers(map, filtered, listEl);
      }
      setCounters(root, filtered);
      setAgeHeadline(ageTitle, filtered, SUBTYPE || TYPE);

      if (!map) return;
      const noneSet = (!day && isNaN(age) && !loc);
      if (noneSet) { resetView(map, allPts); return; }
      if (loc)  { await moveForLoc(map, loc, items, filtered); return; }
      if (day)  { await moveForDay(map, day, items, filtered); return; }
      if (!isNaN(age)) { await moveForAge(map, age, items, filtered); return; }
    }

    daySel && daySel.addEventListener("change", apply);
    ageSel && ageSel.addEventListener("change", apply);
    locSel && locSel.addEventListener("change", apply);

    apply(); // initial
  });
})();
















