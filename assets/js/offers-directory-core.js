(function () {
  "use strict";

  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  const esc = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (m) => {
      return (
        {
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[m] || m
      );
    });

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

  const normDay = (v) =>
    v ? DAY_ALIASES[String(v).trim().toLowerCase()] || v : "";

  const offerHasDay = (o, code) => {
    if (!code) return true;
    const arr = Array.isArray(o?.days) ? o.days : [];
    return arr.some((d) => normDay(d) === code);
  };

  function normalizeCity(s) {
    if (!s) return "";
    let out = String(s)
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "");
    return out
      .replace(/[^a-z0-9]+/gi, " ")
      .trim()
      .toLowerCase();
  }

  function cityMatches(itemLoc, selectedLoc) {
    const a = normalizeCity(itemLoc);
    const b = normalizeCity(selectedLoc);
    if (!a || !b) return false;
    return a === b || a.includes(b) || b.includes(a);
  }

  function dashTail(raw) {
    const dashSplit = raw.split(/\s*[–—-]\s*/);
    return dashSplit[dashSplit.length - 1] || raw;
  }

  function lastCommaPart(tail) {
    const parts = tail
      .split(",")
      .map((x) => x.trim())
      .filter(Boolean);
    return parts.length ? parts[parts.length - 1] : tail;
  }

  function stripZip(last) {
    const m = last.match(/\b\d{5}\s+(.+)$/);
    return (m ? m[1] : last).trim();
  }

  function cityFromLocationString(s) {
    const raw = String(s || "").trim();
    if (!raw) return "";
    const tail = dashTail(raw);
    const last = lastCommaPart(tail);
    return stripZip(last);
  }

  function cityFromOffer(o) {
    if (!o) return "";
    if (o.city) return String(o.city).trim();
    if (o.standort) return String(o.standort).trim();
    if (o.location) return cityFromLocationString(o.location);
    return "";
  }

  function groupKeyOf(o) {
    return (
      String(o?.location || "").trim() ||
      cityFromOffer(o) ||
      String(o?.title || "").trim() ||
      ""
    );
  }

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
      new Set(arr.map((o) => cityFromOffer(o)).filter(Boolean))
    ).sort((a, b) => a.localeCompare(b, "de"));

    selectEl.innerHTML =
      `<option value="">Alle Standorte</option>` +
      cities.map((c) => `<option>${esc(c)}</option>`).join("");
  }

  function nameAddr(o) {
    const name =
      o.clubName || o.club || o.provider || o.title || o.type || "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""]
      .filter(Boolean)
      .join(", ");
    const addr = l1 && l2 ? `${l1} - ${l2}` : l1 || l2 || o.location || "";
    return { name, addr };
  }

  function initGroup(map, key, o) {
    const { name, addr } = nameAddr(o);
    const g = { key, rep: o, name, addr, offers: [] };
    map.set(key, g);
    return g;
  }

  function groupByLocation(arr) {
    const map = new Map();
    arr.forEach((o) => {
      const key = groupKeyOf(o);
      if (!key) return;
      const g = map.get(key) || initGroup(map, key, o);
      g.offers.push(o);
    });
    return Array.from(map.values());
  }

  function nextBase(root) {
    return root?.dataset?.next || "http://localhost:3000";
  }

  function renderEmpty(listEl) {
    listEl.innerHTML =
      '<li><div class="card">Keine Angebote gefunden.</div></li>';
  }

  function renderPowerGroups(listEl, groups) {
    listEl.innerHTML = groups
      .map(
        (g, i) => `
          <li class="ks-offer" data-offer-index="${i}" data-loc-key="${esc(
          g.key
        )}">
            <article class="card">
              <h3 class="card-title">${esc(g.name)}</h3>
              ${g.addr ? `<div class="offer-meta">${esc(g.addr)}</div>` : ""}
            </article>
          </li>
        `
      )
      .join("");
  }

  function bindPowerGroupClicks(listEl, groups, allItems, mapManager, root) {
    const NEXT = nextBase(root);
    $$(".ks-offer", listEl).forEach((li, idx) => {
      li.addEventListener("click", () => {
        const group = groups[idx];
        if (!group) return;
        const offersAtLoc = allItems.filter((o) => groupKeyOf(o) === group.key);
        const offer = offersAtLoc[0] || group.rep;
        if (!offer) return;
        mapManager?.focus_offer?.(offer, 14);
        const sessions = offersAtLoc.length ? offersAtLoc : [offer];
        window.KSOffersDialog?.open?.(offer, sessions, { nextBase: NEXT });
      });
    });
  }

  function renderStandardOffers(listEl, displayArr) {
    listEl.innerHTML = displayArr
      .map((o, i) => {
        const { name, addr } = nameAddr(o);
        return `
          <li class="ks-offer" data-offer-index="${i}">
            <article class="card">
              <h3 class="card-title">${esc(name)}</h3>
              ${addr ? `<div class="offer-meta">${esc(addr)}</div>` : ""}
            </article>
          </li>
        `;
      })
      .join("");
  }

  function bindStandardClicks(listEl, displayArr, allItems, mapManager, root) {
    const NEXT = nextBase(root);
    $$(".ks-offer", listEl).forEach((li, idx) => {
      li.addEventListener("click", () => {
        const offer = displayArr[idx];
        if (!offer) return;
        mapManager?.focus_offer?.(offer, 14);
        const key = groupKeyOf(offer);
        const sessions = allItems.filter((x) => groupKeyOf(x) === key);
        window.KSOffersDialog?.open?.(
          offer,
          sessions.length ? sessions : [offer],
          { nextBase: NEXT }
        );
      });
    });
  }

  function renderList(
    listEl,
    displayArr,
    allItems,
    mapManager,
    isPowertrainingPage,
    root,
    groups
  ) {
    if (!listEl) return;
    if (!displayArr.length) return renderEmpty(listEl);
    if (isPowertrainingPage && groups && groups.length) {
      renderPowerGroups(listEl, groups);
      bindPowerGroupClicks(listEl, groups, allItems, mapManager, root);
      return;
    }
    renderStandardOffers(listEl, displayArr);
    bindStandardClicks(listEl, displayArr, allItems, mapManager, root);
  }

  function setCounters(root, arr) {
    const o = $("[data-count-offers]", root);
    const l = $("[data-count-locations]", root);
    if (o) o.textContent = String(arr.length);
    if (!l) return;
    const s = new Set(arr.map((x) => cityFromOffer(x)).filter(Boolean));
    l.textContent = String(s.size);
  }

  function ageTextByCourseKey(key) {
    switch (key) {
      case "Kindergarten":
        return "4–6 Jahre";
      case "Foerdertraining":
      case "Foerdertraining_Athletik":
      case "Torwarttraining":
        return "7–17 Jahre";
      case "Camp":
        return "6–13 Jahre";
      case "AthleticTraining":
      case "Powertraining":
      case "AthletikTraining":
        return "7–17 Jahre";
      case "PersonalTraining":
      case "Einzeltraining_Athletik":
      case "Einzeltraining_Torwart":
        return "6–25 Jahre";
      case "CoachEducation":
        return "alle Altersstufen";
    }
    return "";
  }

  function ageRangeFromOffers(arr) {
    let min = null;
    let max = null;
    arr.forEach((o) => {
      const from = Number(o.ageFrom);
      const to = Number(o.ageTo);
      if (Number.isFinite(from)) min = min == null ? from : Math.min(min, from);
      if (Number.isFinite(to)) max = max == null ? to : Math.max(max, to);
    });
    if (min == null || max == null) return "alle Altersstufen";
    return `${min}–${max} Jahre`;
  }

  function setAgeHeadline(el, arr, courseKey) {
    if (!el) return;
    const key = String(courseKey || "").trim();
    const fixed = ageTextByCourseKey(key);
    el.textContent = fixed || ageRangeFromOffers(arr);
  }

  function matchesType(o, t) {
    if (!t) return true;
    const a = String(o.type || "");
    const b = String(o.legacy_type || "");
    return a === t || b === t;
  }

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
    return getProgramGroupFromKey(parts.join("|"));
  }

  function filterByProgram(allItems, currentKey) {
    const currentGroup = getProgramGroupFromKey(currentKey);
    return allItems.filter((o) => {
      const g = getProgramGroupForOffer(o);
      return currentGroup === "standard"
        ? g === "standard"
        : g === currentGroup;
    });
  }

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
      o.holidayDateTo || o.holidayTo || o.dateTo || o.endDate || o.end || ""
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
    return (label + range).trim() || "";
  }

  function collectHolidayOptions(arr, seasonValue) {
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
    return opts;
  }

  function fillHolidayWeeksSelect(sel, arr, seasonValue) {
    if (!sel) return;
    const opts = collectHolidayOptions(arr, seasonValue);
    sel.innerHTML =
      '<option value="">Alle Zeiträume</option>' +
      opts.map((k) => `<option value="${esc(k)}">${esc(k)}</option>`).join("");
  }

  window.KSOffersDirectoryCore = {
    $,
    $$,
    esc,
    DAY_ALIASES,
    normDay,
    offerHasDay,
    normalizeCity,
    cityMatches,
    cityFromLocationString,
    cityFromOffer,
    groupKeyOf,
    buildUrl,
    fillLocations,
    groupByLocation,
    renderList,
    setCounters,
    setAgeHeadline,
    matchesType,
    getProgramGroupFromKey,
    filterByProgram,
    getHolidayLabel,
    getHolidaySeasonKey,
    getHolidayWeekKey,
    fillHolidayWeeksSelect,
  };
})();
