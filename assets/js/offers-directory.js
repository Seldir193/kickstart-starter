// assets/js/offers-directory.js
(function () {
  "use strict";

  let onOutsidePointerDown = null;

  /* ===== helpers (dom, text) ===== */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));
  const esc = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (m) =>
        ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
          m
        ])
    );

  /* ===== day helpers ===== */
  const DAY_ALIASES = {
    m: "Mo",
    mo: "Mo",
    montag: "Mo",
    monday: "Mo",
    mon: "Mo",
    di: "Di",
    dienstag: "Di",
    tuesday: "Di",
    tue: "Di",
    mi: "Mi",
    mittwoch: "Mi",
    wednesday: "Mi",
    wed: "Mi",
    do: "Do",
    donnerstag: "Do",
    thursday: "Do",
    thu: "Do",
    fr: "Fr",
    freitag: "Fr",
    friday: "Fr",
    fri: "Fr",
    sa: "Sa",
    samstag: "Sa",
    saturday: "Sa",
    sat: "Sa",
    so: "So",
    sonntag: "So",
    sunday: "So",
  };
  const DAY_LONG = {
    Mo: "Montag",
    Di: "Dienstag",
    Mi: "Mittwoch",
    Do: "Donnerstag",
    Fr: "Freitag",
    Sa: "Samstag",
    So: "Sonntag",
  };
  const normDay = (v) =>
    v ? DAY_ALIASES[String(v).trim().toLowerCase()] || v : "";
  const offerHasDay = (o, code) => {
    if (!code) return true;
    const arr = Array.isArray(o.days) ? o.days : [];
    return arr.some((d) => normDay(d) === code);
  };

  /* ===== city helpers ===== */
  function normalizeCity(s) {
    if (!s) return "";
    let out = String(s)
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "");
    return out.replace(/[^a-z0-9]+/gi, " ").trim().toLowerCase();
  }

  function cityMatches(itemLoc, selectedLoc) {
    const a = normalizeCity(itemLoc),
      b = normalizeCity(selectedLoc);
    if (!a || !b) return false;
    return a === b || a.includes(b) || b.includes(a);
  }

  function cityFromLocationString(s) {
    const raw = String(s || "").trim();
    if (!raw) return "";
    const split = raw.split(/\s*[-–—,•|]\s*/);
    return split[0] || raw;
  }

  const normalizeStandort = (o) => String(o.location || "").trim();

  function cityFromOffer(o) {
    if (o.city) return String(o.city).trim();
    if (o.standort) return String(o.standort).trim();
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
  const normalizePair = (a, b) =>
    isLat(a) && isLng(b)
      ? [a, b]
      : isLng(a) && isLat(b)
      ? [b, a]
      : [a, b];

  const parseLatLngString = (s) => {
    const m = String(s || "")
      .trim()
      .match(/(-?\d+(?:[.,]\d+)?)[\s,;]+(-?\d+(?:[.,]\d+)?)/);
    if (!m) return null;
    const a = parseCoord(m[1]),
      b = parseCoord(m[2]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    return normalizePair(a, b);
  };

  const coordsFromArray = (arr) => {
    if (!Array.isArray(arr) || arr.length < 2) return null;
    const a = parseCoord(arr[0]),
      b = parseCoord(arr[1]);
    if (!Number.isFinite(a) || !Number.isFinite(b)) return null;
    return normalizePair(a, b);
  };

  const firstFinite = (cands) => {
    for (const c of cands) {
      const v = parseCoord(c);
      if (Number.isFinite(v)) return v;
    }
    return NaN;
  };

  function latLngOf(o) {
    {
      const lat = firstFinite([o.lat, o.latitude, o.latDeg]);
      const lng = firstFinite([o.lng, o.lon, o.long, o.longitude]);
      if (isLat(lat) && isLng(lng)) return [lat, lng];
    }
    const C = [
      o.coords,
      o.coord,
      o.position,
      o.geo,
      o.gps,
      o.map,
      o.center,
      o.centerPoint,
      o.point,
      o.location,
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

    for (const key of ["latlng", "lat_lon", "lon_lat"]) {
      const p = parseLatLngString(o[key]);
      if (p) return p;
    }
    for (const [, v] of Object.entries(o)) {
      if (typeof v === "string") {
        const p = parseLatLngString(v);
        if (p) return p;
      }
    }
    return null;
  }

  const pointsFrom = (items = []) => {
    const pts = [];
    items.forEach((o) => {
      const ll = latLngOf(o);
      if (ll) pts.push(ll);
    });
    return pts;
  };

  /* ===== geocoding (Nominatim) ===== */
  const GEOCODE_ENDPOINT = "https://nominatim.openstreetmap.org/search";
  const geoCache = new Map(),
    inflight = new Map();

  async function geocodeCity(name) {
    const key = normalizeCity(name);
    if (!key) return null;
    if (geoCache.has(key)) return geoCache.get(key);
    if (inflight.has(key)) return inflight.get(key);

    const url = `${GEOCODE_ENDPOINT}?format=jsonv2&q=${encodeURIComponent(
      name
    )}&limit=1&addressdetails=0`;
    const p = fetch(url, { headers: { Accept: "application/json" } })
      .then((r) => r.json())
      .then((j) => {
        let res = null;
        if (Array.isArray(j) && j.length) {
          const lat = parseCoord(j[0].lat),
            lon = parseCoord(j[0].lon);
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

  async function geocodeCities(names, limit = 8) {
    const pts = [],
      seen = new Set();
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

  function ensureMapHeight(el) {
    if (el && el.getBoundingClientRect().height < 50) {
      el.style.height = "360px";
    }
  }

  function initMap(el) {
    if (!el || !window.L) return null;
    const map = L.map(el, { scrollWheelZoom: true, zoomControl: true });
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: "© OpenStreetMap",
    }).addTo(map);
    map.setView(DEFAULT_CENTER, DEFAULT_Z);
    map.whenReady(() => map.invalidateSize());
    requestAnimationFrame(() => map.invalidateSize());
    setTimeout(() => map.invalidateSize(), 150);
    if (window.ResizeObserver) {
      new ResizeObserver(() => map.invalidateSize()).observe(el);
    }
    return map;
  }

  const clearMarkers = (ms) => {
    ms.forEach((m) => m.remove());
    return [];
  };

  function setMarkers(map, arr, listEl) {
    const ms = [];
    arr.forEach((o, i) => {
      const ll = latLngOf(o);
      if (!ll) return;
      const mk = L.marker(ll).addTo(map);
      mk.bindPopup(
        `<strong>${esc(o.title || o.type || "Standort")}</strong><br>${esc(
          o.location || ""
        )}`
      );
      mk.on("click", () => {
        const li =
          listEl?.querySelector(
            `.ks-offer[data-offer-index="${i}"]`
          ) || null;
        if (li) li.scrollIntoView({ behavior: "smooth", block: "start" });
      });
      ms.push(mk);
    });
    return ms;
  }

  function flyTo(map, pts) {
    if (!pts.length) return;
    if (pts.length === 1) {
      map.flyTo(pts[0], 12, { duration: 0.5 });
    } else {
      map.flyToBounds(L.latLngBounds(pts), { padding: [24, 24] });
    }
  }

  function resetView(map, allPts) {
    if (!map) return;
    if (allPts.length > 1) flyTo(map, allPts);
    else if (allPts.length === 1)
      map.flyTo(allPts[0], 12, { duration: 0.5 });
    else map.setView(DEFAULT_CENTER, DEFAULT_Z);
  }

  /* ===== UI rendering ===== */
  function buildUrl(base, q) {
    const u = new URL(base, window.location.origin);
    Object.entries(q || {}).forEach(([k, v]) => {
      if (v != null && v !== "") u.searchParams.set(k, v);
    });
    return u.toString();
  }

  function fillLocations(selectEl, arr) {
    if (!selectEl) return;
    const cities = Array.from(
      new Set(
        arr
          .map((o) => cityFromOffer(o))
          .filter(Boolean)
      )
    ).sort((a, b) => a.localeCompare(b, "de"));

    selectEl.innerHTML =
      `<option value="">Alle Standorte</option>` +
      cities.map((c) => `<option>${esc(c)}</option>`).join("");
  }

  function nameAddr(o) {
    const name =
      o.clubName ||
      o.club ||
      o.provider ||
      o.title ||
      o.type ||
      "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""]
      .filter(Boolean)
      .join(", ");
    const addr =
      l1 && l2
        ? `${l1} - ${l2}`
        : l1 || l2 || (o.location || "");
    return { name, addr };
  }

  /* Gruppen-Helfer: Powertraining → pro Standort nur 1 Karte */
  function groupByLocation(arr) {
    const map = new Map();
    arr.forEach((o) => {
      const key = normalizeStandort(o) || cityFromOffer(o) || (o.title || "");
      if (!key) return;
      let g = map.get(key);
      if (!g) {
        const { name, addr } = nameAddr(o);
        g = { key, rep: o, name, addr, offers: [] };
        map.set(key, g);
      }
      g.offers.push(o);
    });
    return Array.from(map.values());
  }

  function renderList(listEl, displayArr, allItems, map, isPowertrainingPage, root, groups) {
    if (!listEl) return;
    if (!displayArr.length) {
      listEl.innerHTML =
        '<li><div class="card">Keine Angebote gefunden.</div></li>';
      return;
    }

    if (isPowertrainingPage && groups && groups.length) {
      // Eine Karte pro Standort
      listEl.innerHTML = groups
        .map(
          (g, i) => `
        <li class="ks-offer" data-offer-index="${i}" data-loc-key="${esc(
            g.key
          )}">
          <article class="card">
            <h3 class="card-title">${esc(g.name)}</h3>
            ${
              g.addr
                ? `<div class="offer-meta">${esc(g.addr)}</div>`
                : ""
            }
          </article>
        </li>`
        )
        .join("");

      const NEXT = root?.dataset?.next || "http://localhost:3000";

      $$(".ks-offer", listEl).forEach((li, idx) => {
        li.addEventListener("click", () => {
          const group = groups[idx];
          if (!group) return;

          const offersAtLoc = allItems.filter(
            (o) => normalizeStandort(o) === group.key
          );
          const offer = offersAtLoc[0] || group.rep;

          const ll = offer ? latLngOf(offer) : null;
          if (ll && map) {
            map.setView(ll, 14, { animate: true });
          }

          if (window.KSOffersDialog && offer) {
            window.KSOffersDialog.open(
              offer,
              offersAtLoc.length ? offersAtLoc : [offer],
              { nextBase: NEXT }
            );
          }
        });
      });

      return;
    }

    // Standard-Rendering: eine Karte pro Offer
    listEl.innerHTML = displayArr
      .map((o, i) => {
        const { name, addr } = nameAddr(o);
        return `<li class="ks-offer" data-offer-index="${i}">
        <article class="card">
          <h3 class="card-title">${esc(name)}</h3>
          ${addr ? `<div class="offer-meta">${esc(addr)}</div>` : ""}
        </article>
      </li>`;
      })
      .join("");

    const NEXT = root?.dataset?.next || "http://localhost:3000";

    $$(".ks-offer", listEl).forEach((li, idx) => {
      li.addEventListener("click", () => {
        const offer = displayArr[idx];
        if (!offer) return;

        const ll = latLngOf(offer);
        if (ll && map) map.setView(ll, 14, { animate: true });

        const key = normalizeStandort(offer);
        const sessions = allItems.filter(
          (x) => normalizeStandort(x) === key
        );

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

  function setCounters(root, arr) {
    const o = $("[data-count-offers]", root);
    const l = $("[data-count-locations]", root);
    if (o) o.textContent = String(arr.length);
    if (l) {
      const s = new Set(
        arr
          .map((x) => cityFromOffer(x))
          .filter(Boolean)
      );
      l.textContent = String(s.size);
    }
  }

  function setAgeHeadline(el, arr, courseKey) {
    if (!el) return;

    const key = String(courseKey || "").trim();

    switch (key) {
      case "Kindergarten":
        el.textContent = "4–6 Jahre";
        return;

      case "Foerdertraining":
      case "Foerdertraining_Athletik":
      case "Torwarttraining":
        el.textContent = "7–17 Jahre";
        return;

      case "Camp":
        el.textContent = "6–13 Jahre";
        return;

      case "AthleticTraining":
      case "Powertraining":
      case "AthletikTraining":
        el.textContent = "7–17 Jahre";
        return;

      case "PersonalTraining":
      case "Einzeltraining_Athletik":
      case "Einzeltraining_Torwart":
        el.textContent = "6–25 Jahre";
        return;

      case "CoachEducation":
        el.textContent = "alle Altersstufen";
        return;
    }

    let min = null,
      max = null;
    arr.forEach((o) => {
      const from = Number(o.ageFrom);
      const to = Number(o.ageTo);
      if (Number.isFinite(from)) {
        min = min == null ? from : Math.min(min, from);
      }
      if (Number.isFinite(to)) {
        max = max == null ? to : Math.max(max, to);
      }
    });

    if (min != null && max != null) {
      el.textContent = `${min}–${max} Jahre`;
    } else {
      el.textContent = "alle Altersstufen";
    }
  }

  /* ===== movement logic ===== */
  async function moveForLoc(map, loc, items, filtered) {
    const pts = pointsFrom(filtered);
    if (pts.length) {
      flyTo(map, pts);
      return;
    }

    const locItems = items.filter((o) => {
      const c = cityFromOffer(o);
      return cityMatches(c, loc);
    });

    const p2 = pointsFrom(locItems);
    if (p2.length) {
      flyTo(map, p2);
      return;
    }

    const g = await geocodeCity(loc);
    if (g) flyTo(map, [g]);
  }

  async function moveForDay(map, day, items, filtered) {
    const pts = pointsFrom(filtered);
    if (pts.length) {
      flyTo(map, pts);
      return;
    }
    const dItems = items.filter((o) => offerHasDay(o, day));
    const p2 = pointsFrom(dItems);
    if (p2.length) {
      flyTo(map, p2);
      return;
    }
    const names = Array.from(
      new Set(
        dItems
          .map((o) => cityFromLocationString(o.location))
          .filter(Boolean)
      )
    );
    const gs = await geocodeCities(names, 8);
    if (gs.length) flyTo(map, gs);
  }

  async function moveForAge(map, age, items, filtered) {
    const pts = pointsFrom(filtered);
    if (pts.length) {
      flyTo(map, pts);
      return;
    }

    const aItems = items.filter((o) => {
      const f = Number(o.ageFrom ?? 0);
      const t = Number(o.ageTo ?? 99);
      return (
        Number.isFinite(f) &&
        Number.isFinite(t) &&
        age >= f &&
        age <= t
      );
    });

    const p2 = pointsFrom(aItems);
    if (p2.length) {
      flyTo(map, p2);
      return;
    }
    const names = Array.from(
      new Set(
        aItems
          .map((o) => cityFromLocationString(o.location))
          .filter(Boolean)
      )
    );
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

  /* ===== Program-/Kurs-Gruppen ===== */
  function normalizeProgramKey(str) {
    return String(str || "")
      .toLowerCase()
      .replace(/[\s_\-]+/g, "");
  }

  function getProgramGroupFromKey(key) {
    const n = normalizeProgramKey(key);
    if (!n) return "standard";

    if (n.includes("rentacoach")) return "rentacoach";
    if (n.includes("clubprogram")) return "clubprogram";
    if (n.includes("coacheducation")) return "coacheducation";
    if (n.includes("camp") || n.includes("trainingscamp")) return "camp";

    return "standard";
  }

  function getProgramGroupForOffer(o) {
    const parts = [o.sub_type, o.category, o.type].filter(Boolean);
    const combined = parts.join("|");
    return getProgramGroupFromKey(combined);
  }

  function filterByProgram(allItems, currentKey) {
    const currentGroup = getProgramGroupFromKey(currentKey);

    return allItems.filter((o) => {
      const g = getProgramGroupForOffer(o);

      if (currentGroup === "standard") {
        return g === "standard";
      }

      return g === currentGroup;
    });
  }

  /* ===== Holiday-Helpers (Camps & Powertraining) ===== */
  function getHolidayLabel(o) {
    return (
      o.holidayWeekLabel ||
      o.holidayLabel ||
      o.holidayWeek ||
      o.holiday_name ||
      o.holidayName ||
      o.holiday ||
      ""
    );
  }

  function getHolidayFrom(o) {
    return (
      o.holidayDateFrom ||
      o.holidayFrom ||
      o.dateFrom ||
      o.startDate ||
      o.start ||
      ""
    );
  }

  function getHolidayTo(o) {
    return (
      o.holidayDateTo ||
      o.holidayTo ||
      o.dateTo ||
      o.endDate ||
      o.end ||
      ""
    );
  }

  function getHolidaySeasonKey(label) {
    const s = String(label || "").toLowerCase();
    if (!s) return "";
    if (s.includes("oster")) return "oster";
    if (s.includes("pfingst")) return "pfingst";
    if (s.includes("sommer")) return "sommer";
    if (s.includes("herbst")) return "herbst";
    if (s.includes("winter") || s.includes("weihnacht") || s.includes("xmas"))
      return "winter";
    return "";
  }

  function getHolidayWeekKey(o) {
    const label = getHolidayLabel(o).trim();
    const from = getHolidayFrom(o);
    const to = getHolidayTo(o);

    let range = "";
    if (from && to) range = ` (${from} – ${to})`;
    else if (from) range = ` (${from})`;

    const key = (label + range).trim();
    return key || "";
  }

  function fillHolidayWeeksSelect(sel, arr, seasonValue) {
    if (!sel) return;
    const seen = new Set();
    const opts = [];
     
    arr.forEach((o) => {
      const label = getHolidayLabel(o);
      const season = getHolidaySeasonKey(label);
      if (seasonValue && seasonValue !== season) return;

      const key = getHolidayWeekKey(o);
      if (!key || seen.has(key)) return;
      seen.add(key);
      opts.push(key);
    });

    sel.innerHTML =
      '<option value="">Alle Zeiträume</option>' +
      opts.map((k) => `<option value="${esc(k)}">${esc(k)}</option>`).join("");
  }

















function enhanceFilterSelects(root) {
  if (!root) return;

  const selects = Array.from(root.querySelectorAll("[data-filters] select"));
  if (!selects.length) return;

  const closeAll = () => {
    root.querySelectorAll(".ks-dir-dd.is-open").forEach((dd) => {
      dd.classList.remove("is-open");
      dd.setAttribute("aria-expanded", "false");
      const btn = dd.querySelector(".ks-dir-dd__btn");
      if (btn) btn.setAttribute("aria-expanded", "false");
      const panel = dd.querySelector(".ks-dir-dd__panel");
      if (panel) panel.innerHTML = "";
    });
  };

  selects.forEach((nativeSel) => {
    if (nativeSel.dataset.enhanced === "1") return;
    nativeSel.dataset.enhanced = "1";

    const wrapLabel = nativeSel.closest("label.ks-field");
    const control = wrapLabel?.querySelector(".ks-field__control--select");
    if (!wrapLabel || !control) return;

    const iconImg = control.querySelector(".ks-field__icon img");
    const caretSrc = iconImg?.getAttribute("src") || "";

    nativeSel.classList.add("ks-dir-native-select");
    nativeSel.tabIndex = -1;
    nativeSel.setAttribute("aria-hidden", "true");

    const dd = document.createElement("div");
    dd.className = "ks-dir-dd";
    dd.setAttribute("aria-expanded", "false");

    dd.innerHTML = `
      <button type="button" class="ks-dir-dd__btn" aria-expanded="false">
        <span class="ks-dir-dd__label"></span>
        <span class="ks-dir-dd__caret" aria-hidden="true">
          ${caretSrc ? `<img src="${caretSrc}" alt="">` : ""}
        </span>
      </button>
      <div class="ks-dir-dd__panel" role="listbox"></div>
    `;

    control.classList.add("is-enhanced");
    control.innerHTML = "";
    control.appendChild(dd);
    control.appendChild(nativeSel);

    const btn = dd.querySelector(".ks-dir-dd__btn");
    const label = dd.querySelector(".ks-dir-dd__label");
    const panel = dd.querySelector(".ks-dir-dd__panel");

    function syncLabel() {
      const opt = nativeSel.selectedOptions?.[0];
      label.textContent = opt ? opt.textContent : "Bitte auswählen …";
    }

    function buildPanel() {
      panel.innerHTML = "";
      Array.from(nativeSel.options).forEach((opt) => {
        const item = document.createElement("div");
        item.className = "ks-dir-dd__option";
        item.setAttribute("role", "option");
        item.setAttribute("tabindex", "-1");
        item.setAttribute("data-value", opt.value);
        item.textContent = opt.textContent;
        if (opt.selected) item.setAttribute("aria-selected", "true");
        panel.appendChild(item);
      });

      const sel = panel.querySelector('.ks-dir-dd__option[aria-selected="true"]');
      const first = panel.querySelector(".ks-dir-dd__option");
      (sel || first)?.focus({ preventScroll: true });
    }

    function openDD() {
      closeAll();
      buildPanel();
      dd.classList.add("is-open");
      dd.setAttribute("aria-expanded", "true");
      btn.setAttribute("aria-expanded", "true");

      onOutsidePointerDown = (e) => {
    if (!dd.contains(e.target)) closeDD();
  };

  // capture=true ist wichtig, damit es zuverlässig klappt
  document.addEventListener("pointerdown", onOutsidePointerDown, true);

  // ESC (wie du schon hast, kann bleiben)
  document.addEventListener(
    "keydown",
    (e) => {
      if (e.key === "Escape") closeDD();
    },
    { once: true }
  );
    }

    function closeDD() {
      dd.classList.remove("is-open");
      dd.setAttribute("aria-expanded", "false");
      btn.setAttribute("aria-expanded", "false");
      panel.innerHTML = "";

       if (onOutsidePointerDown) {
    document.removeEventListener("pointerdown", onOutsidePointerDown, true);
    onOutsidePointerDown = null;
  }

      try { btn.focus({ preventScroll: true }); } catch {}
    }

    btn.addEventListener("click", (e) => {
      e.preventDefault();
      e.stopPropagation();
      dd.classList.contains("is-open") ? closeDD() : openDD();
    });

    panel.addEventListener("click", (e) => {
      const item = e.target.closest(".ks-dir-dd__option");
      if (!item) return;

      nativeSel.value = item.getAttribute("data-value") ?? "";
      syncLabel();

      panel.querySelectorAll(".ks-dir-dd__option").forEach((x) => x.removeAttribute("aria-selected"));
      item.setAttribute("aria-selected", "true");

      nativeSel.dispatchEvent(new Event("change", { bubbles: true }));
      closeDD();
    });

    nativeSel.addEventListener("change", () => {
      syncLabel();
      if (dd.classList.contains("is-open")) buildPanel();
    });

    syncLabel();
  });

  // ✅ WICHTIG: erst NACH dem Enhancen sichtbar machen
  root.classList.add("is-ready");

  // nur 1x global listener
  if (!root.dataset.ddOutsideBound) {
    root.dataset.ddOutsideBound = "1";
    document.addEventListener("pointerdown", (e) => {
      if (!root.contains(e.target)) {
        root.querySelectorAll(".ks-dir-dd.is-open").forEach((dd) => {
          dd.classList.remove("is-open");
          dd.setAttribute("aria-expanded", "false");
          const btn = dd.querySelector(".ks-dir-dd__btn");
          if (btn) btn.setAttribute("aria-expanded", "false");
          const panel = dd.querySelector(".ks-dir-dd__panel");
          if (panel) panel.innerHTML = "";
        });
      }
    });
  }
}
























  /* ===== main ===== */
  document.addEventListener("DOMContentLoaded", async () => {
    const root = $("#ksDir");
    if (!root) return;
     
enhanceFilterSelects(root);




    const daySel = $("#ksFilterDay", root);
    const ageSel = $("#ksFilterAge", root);
    const locSel = $("#ksFilterLoc", root);
    const listEl = $("#ksDirList", root);
    const ageTitle = $("[data-age-title]", root);

    function resetSelect(sel) {
  if (!sel) return;
  sel.value = "";
  sel.dispatchEvent(new Event("change", { bubbles: true }));
}


    const holidaySeasonSel = $("#ksFilterHolidaySeason", root);
    const holidayWeekSel = $("#ksFilterHolidayWeek", root);

    const TYPE = root.dataset.type || "";
    const API = root.dataset.api || "http://localhost:5000";
    const CITY = root.dataset.city || "";

    const CATEGORY = root.dataset.category || "";
    const SUBTYPE = root.dataset.subtype || "";

    const catLower = (CATEGORY || "").toLowerCase();
    const isHolidayPage =
      catLower === "holiday" ||
      catLower === "holidayprograms" ||
      getProgramGroupFromKey(SUBTYPE || TYPE) === "camp";

    const normProgKey = (SUBTYPE || TYPE || "").toLowerCase();
    const isPowertrainingPage =
      normProgKey.includes("powertraining") ||
      normProgKey.includes("athletictraining") ||
      normProgKey.includes("athletiktraining");

    const mapNode = $("#ksMap", root);
    ensureMapHeight(mapNode);
    const map = initMap(mapNode);

    let items = [],
      filtered = [],
      markers = [],
      allPts = [];

    const url = buildUrl(`${API}/api/offers`, {
      type: TYPE || undefined,
      category: CATEGORY || undefined,
      sub_type: SUBTYPE || undefined,
      limit: 500,
    });

    try {
      const data = await fetch(url).then((r) => r.json());
      items = Array.isArray(data?.items)
        ? data.items
        : Array.isArray(data)
        ? data
        : [];

      const currentProgramKey = (SUBTYPE || TYPE || "").trim();
      items = filterByProgram(items, currentProgramKey);

      fillLocations(locSel, items);

      if (CITY && locSel) {
        const opt = Array.from(locSel.options).find(
          (o) => normalizeCity(o.value) === normalizeCity(CITY)
        );
        if (opt) locSel.value = opt.value;
      }

      allPts = pointsFrom(items);

      if (isHolidayPage) {
        fillHolidayWeeksSelect(
          holidayWeekSel,
          items,
          holidaySeasonSel ? holidaySeasonSel.value || "" : ""
        );
        const weekSel = $("#ksFilterHolidayWeek", root);
  if (weekSel) weekSel.dispatchEvent(new Event("change", { bubbles: true }));
      }
    } catch (e) {
      if (listEl) {
        listEl.innerHTML =
          '<li><div class="card">Keine Angebote gefunden.</div></li>';
      }
    }

    async function apply() {
      // Holiday-Seiten: kein Tag-/Altersfilter, nur Ferienfilter
      const day = isHolidayPage ? "" : normDay(daySel?.value || "");
      const age =
        isHolidayPage || !ageSel || ageSel.value === ""
          ? NaN
          : parseInt(ageSel.value, 10);
      const loc = (locSel?.value || "").trim();

      const seasonVal =
        isHolidayPage && holidaySeasonSel
          ? holidaySeasonSel.value
          : "";
      const weekVal =
        isHolidayPage && holidayWeekSel ? holidayWeekSel.value : "";

      filtered = items.filter((o) => {
        if (TYPE && !matchesType(o, TYPE)) return false;
        if (CATEGORY && o.category !== CATEGORY) return false;
        if (SUBTYPE && o.sub_type !== SUBTYPE) return false;

        if (isHolidayPage) {
          const label = getHolidayLabel(o);
          const season = getHolidaySeasonKey(label);
          const wKey = getHolidayWeekKey(o);

          if (seasonVal && seasonVal !== season) return false;
          if (weekVal && weekVal !== wKey) return false;
        } else {
          if (day && !offerHasDay(o, day)) return false;

          if (!isNaN(age)) {
            const f = Number(o.ageFrom ?? 0);
            const t = Number(o.ageTo ?? 99);
            if (!(age >= f && age <= t)) return false;
          }
        }

        const offerCity = cityFromOffer(o);
        if (loc && !cityMatches(offerCity, loc)) return false;

        return true;
      });

      // Anzeige-Liste + Marker-Basis
      let displayArr = filtered;
      let groups = null;

      if (isPowertrainingPage) {
        groups = groupByLocation(filtered);
        displayArr = groups.map((g) => g.rep);
      }

      renderList(
        listEl,
        displayArr,
        filtered,
        map,
        isPowertrainingPage,
        root,
        groups
      );

      if (map) {
        markers = clearMarkers(markers);
        markers = setMarkers(map, displayArr, listEl);
      }

      setCounters(root, filtered);
      setAgeHeadline(ageTitle, filtered, SUBTYPE || TYPE);

      if (!map) return;
      const noneSet =
        !day &&
        isNaN(age) &&
        !loc &&
        !seasonVal &&
        !weekVal;
      if (noneSet) {
        resetView(map, allPts);
        return;
      }
      if (loc) {
        await moveForLoc(map, loc, items, filtered);
        return;
      }
      if (day) {
        await moveForDay(map, day, items, filtered);
        return;
      }
      if (!isNaN(age)) {
        await moveForAge(map, age, items, filtered);
        return;
      }
    }

   // if (daySel) daySel.addEventListener("change", apply);

    if (daySel) {
  daySel.addEventListener("change", () => {
    // Tag ist "Master" → reset die anderen zwei
    resetSelect(ageSel);
    resetSelect(locSel);
    apply();
  });
}
    if (ageSel) ageSel.addEventListener("change", apply);
    if (locSel) locSel.addEventListener("change", apply);

 


if (holidaySeasonSel) {
  holidaySeasonSel.addEventListener("change", () => {
    // Ferienzeit ist "Master" → reset Zeitraum + Standort
    resetSelect(holidayWeekSel);
    resetSelect(locSel);

    fillHolidayWeeksSelect(
      holidayWeekSel,
      items,
      holidaySeasonSel.value || ""
    );

    apply();
  });
}

if (holidayWeekSel) holidayWeekSel.addEventListener("change", apply);



    apply();
  });









})();












