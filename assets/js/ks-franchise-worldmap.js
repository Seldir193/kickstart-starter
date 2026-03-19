//assets\js\ks-franchise-worldmap.js
(function () {
  "use strict";

  const esc = (s) =>
    String(s ?? "").replace(/[&<>"']/g, (m) => {
      return (
        { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[
          m
        ] || m
      );
    });

  function norm(s) {
    return String(s || "")
      .trim()
      .toLowerCase()
      .normalize("NFD")
      .replace(/\p{Diacritic}/gu, "");
  }

  const COUNTRY_TO_ISO = {
    deutschland: "DE",
    germany: "DE",
    osterreich: "AT",
    österreich: "AT",
    austria: "AT",
    schweiz: "CH",
    switzerland: "CH",
    turkei: "TR",
    türkei: "TR",
    turkey: "TR",
    niederlande: "NL",
    netherlands: "NL",
    belgien: "BE",
    belgium: "BE",
    frankreich: "FR",
    france: "FR",
    spanien: "ES",
    spain: "ES",
    italien: "IT",
    italy: "IT",
  };

  function toIso2(v) {
    const t = String(v || "").trim();
    if (!t) return "";
    if (/^[a-z]{2}$/i.test(t)) return t.toUpperCase();
    return "";
  }

  function countryIsoFromItem(item) {
    const direct = toIso2(item?.country);
    if (direct) return direct;
    const raw = norm(item?.country);
    if (!raw) return "";
    return COUNTRY_TO_ISO[raw] || "";
  }

  function groupByCountry(items) {
    const map = new Map();
    for (const it of items || []) {
      const iso = countryIsoFromItem(it);
      if (!iso) continue;
      if (!map.has(iso)) map.set(iso, []);
      map.get(iso).push(it);
    }
    return map;
  }

  function groupByCity(items) {
    const map = new Map();
    for (const it of items || []) {
      const c = String(it?.city || "").trim() || "—";
      if (!map.has(c)) map.set(c, []);
      map.get(c).push(it);
    }
    return map;
  }

  function formatLine(label, value) {
    if (!value) return "";
    return `<div class="frw__row"><strong>${esc(label)}</strong><div>${esc(
      value,
    )}</div></div>`;
  }

  function buildOwnerBlock(it) {
    const fullName = [it?.licenseeFirstName, it?.licenseeLastName]
      .filter(Boolean)
      .join(" ")
      .trim();

    const addr = [it?.address, [it?.zip, it?.city].filter(Boolean).join(" ")]
      .filter(Boolean)
      .join(", ");

    return `
      <div class="frw__card">
        <div class="frw__name">${esc(fullName || "Lizenznehmer")}</div>
        <div class="frw__stack">
          ${formatLine("Adresse", addr)}
          ${formatLine("Telefon", it?.phonePublic)}
          ${formatLine("E-Mail", it?.emailPublic)}
          ${formatLine("Website", it?.website)}
        </div>
      </div>
    `;
  }

  function buildCountryTooltipHtml(iso, items) {
    const byCity = groupByCity(items);
    const cities = Array.from(byCity.keys()).sort((a, b) =>
      a.localeCompare(b, "de"),
    );

    const list = cities
      .map((city) => {
        const count = (byCity.get(city) || []).length;
        return `
          <div class="frw__cityItem" role="button" tabindex="0" data-city="${esc(
            city,
          )}">
            <span class="frw__cityName">${esc(city)}</span>
            <span class="frw__cityCount">${count}</span>
          </div>
        `;
      })
      .join("");

    return `
      <div class="frw__t1">
        <div class="frw__t1Head">
          <div class="frw__t1Title">Standorte</div>
          <div class="frw__t1Sub">Land: <strong>${esc(iso)}</strong></div>
        </div>
        <div class="frw__t1List" role="listbox">
          ${list || "<div class='frw__empty'>Keine Standorte.</div>"}
        </div>
      </div>
    `;
  }

  function buildCityTooltipHtml(iso, city, itemsInCity) {
    return `
      <div class="frw__t2">
        <div class="frw__t2Head">
          <div class="frw__t2Title">${esc(city)}</div>
          <div class="frw__t2Sub"><span>${esc(iso)}</span> · <span>${
            itemsInCity.length
          } Standort(e)</span></div>
        </div>
        <div class="frw__t2Body">
          ${itemsInCity.map(buildOwnerBlock).join("")}
        </div>
      </div>
    `;
  }

  async function loadSvg(url) {
    const r = await fetch(url, { cache: "no-store" });
    if (!r.ok) throw new Error(`SVG load failed (${r.status})`);
    return await r.text();
  }

  async function loadItems(apiUrl) {
    const r = await fetch(apiUrl, { cache: "no-store" });
    const d = await r.json().catch(() => null);
    if (!r.ok || !d || d.ok === false) {
      throw new Error(d?.error || `API error (${r.status})`);
    }
    const items = Array.isArray(d.items) ? d.items : [];
    //return items.filter((x) => x.status === "approved");
    return items.filter((x) => x.status === "approved" && x.published === true);
  }

  function ensureTooltip(wrap, selector, attrName, className) {
    let el = wrap.querySelector(selector);
    if (el) {
      el.className = className; // wichtig: überschreibt "alte" Klassen
      return el;
    }
    el = document.createElement("div");
    el.className = className;
    el.setAttribute(attrName, "");
    el.setAttribute("aria-hidden", "true");
    wrap.appendChild(el);
    return el;
  }

  // ✅ FIX: ISO wird jetzt den DOM-Baum hoch gesucht (Wiki SVG: g#de)
  function isoFromEl(el) {
    if (!(el instanceof Element)) return "";
    let node = el;
    while (node && node instanceof Element) {
      const d = node.getAttribute("data-iso");
      if (d && /^[a-z]{2}$/i.test(d)) return d.toUpperCase();

      const id = node.getAttribute("id");
      if (id && /^[a-z]{2}$/i.test(id)) return id.toUpperCase();

      node = node.parentElement;
    }
    return "";
  }

  function findCountryNodes(svgRoot, iso) {
    const lower = String(iso || "").toLowerCase();
    return svgRoot.querySelectorAll(
      `[data-iso="${iso}"], [data-iso="${lower}"], #${CSS.escape(
        lower,
      )}, #${CSS.escape(iso)}`,
    );
  }

  function setTooltipPosNearPointer(
    wrap,
    tip,
    clientX,
    clientY,
    maxW = 280,
    maxH = 220,
  ) {
    const rect = wrap.getBoundingClientRect();
    const x = clientX - rect.left;
    const y = clientY - rect.top;

    const pad = 10;
    const px = Math.max(pad, Math.min(x + 12, rect.width - maxW - pad));
    const py = Math.max(pad, Math.min(y + 12, rect.height - maxH - pad));
    tip.style.transform = `translate(${px}px, ${py}px)`;
  }

  function setTooltipPosSideOf(tip1, tip2, wrap) {
    const wrect = wrap.getBoundingClientRect();
    const r1 = tip1.getBoundingClientRect();

    const gap = 10;
    const maxW = 340;
    const maxH = 280;

    const spaceRight = wrect.right - r1.right;
    const spaceLeft = r1.left - wrect.left;

    let x;
    if (spaceRight >= maxW + gap) {
      x = r1.right - wrect.left + gap;
    } else {
      x = r1.left - wrect.left - maxW - gap;
      x = Math.max(10, x);
    }

    let y = r1.top - wrect.top;
    y = Math.max(10, Math.min(y, wrect.height - maxH - 10));

    tip2.style.transform = `translate(${Math.round(x)}px, ${Math.round(y)}px)`;
  }

  function openTip(tip) {
    tip.classList.add("is-open");
    tip.setAttribute("aria-hidden", "false");
  }

  function closeTip(tip) {
    tip.classList.remove("is-open");
    tip.setAttribute("aria-hidden", "true");
    tip.innerHTML = "";
  }

  function markActive(svgRoot, iso) {
    svgRoot
      .querySelectorAll(".frw__active")
      .forEach((n) => n.classList.remove("frw__active"));
    findCountryNodes(svgRoot, iso).forEach((n) =>
      n.classList.add("frw__active"),
    );
  }

  function clearActive(svgRoot) {
    svgRoot
      .querySelectorAll(".frw__active")
      .forEach((n) => n.classList.remove("frw__active"));
  }

  async function initOne(wrap) {
    const svgUrl = wrap.getAttribute("data-svg");
    const apiUrl = wrap.getAttribute("data-api");
    const stage = wrap.querySelector("[data-fr-stage]");
    const loading = wrap.querySelector("[data-fr-loading]");

    const tip1 = ensureTooltip(
      wrap,
      "[data-fr-tooltip]",
      "data-fr-tooltip",
      "fr-worldwide__tooltip frw__tooltip frw__tooltip--country",
    );
    const tip2 = ensureTooltip(
      wrap,
      "[data-fr-tooltip2]",
      "data-fr-tooltip2",
      "fr-worldwide__tooltip2 frw__tooltip frw__tooltip--city",
    );

    if (!svgUrl || !apiUrl || !stage) return;

    if (loading) {
      loading.style.display = "block";
      loading.textContent = "Standorte laden…";
    }

    let svgText = "";
    let items = [];

    try {
      [svgText, items] = await Promise.all([
        loadSvg(svgUrl),
        loadItems(apiUrl),
      ]);
    } catch {
      if (loading)
        loading.textContent = "Standorte konnten nicht geladen werden.";
      return;
    }

    stage.innerHTML = svgText;

    const svgRoot = stage.querySelector("svg");
    if (!svgRoot) {
      if (loading) loading.textContent = "SVG ungültig.";
      return;
    }

    if (loading) loading.style.display = "none";

    const byCountry = groupByCountry(items);

    for (const [iso] of byCountry.entries()) {
      findCountryNodes(svgRoot, iso).forEach((n) =>
        n.classList.add("frw__has"),
      );
    }

    let activeIso = "";
    let pinnedIso = "";
    let hoverCountry = false;
    let inTip1 = false;
    let inTip2 = false;
    let closeTimer = 0;
    let activeCity = "";

    function clearCloseTimer() {
      if (closeTimer) {
        clearTimeout(closeTimer);
        closeTimer = 0;
      }
    }

    function scheduleCloseAll() {
      clearCloseTimer();
      closeTimer = window.setTimeout(() => {
        if (pinnedIso) return;
        if (hoverCountry || inTip1 || inTip2) return;

        activeIso = "";
        activeCity = "";
        closeTip(tip2);
        closeTip(tip1);
        clearActive(svgRoot);
      }, 140);
    }

    function showCountry(iso, ev) {
      const list = byCountry.get(iso) || [];
      if (!list.length) return;

      if (iso !== activeIso) {
        activeIso = iso;
        activeCity = "";
        tip1.innerHTML = buildCountryTooltipHtml(iso, list);
        openTip(tip1);
        closeTip(tip2);
        markActive(svgRoot, iso);
      }

      if (ev) setTooltipPosNearPointer(wrap, tip1, ev.clientX, ev.clientY);
    }

    function showCity(city) {
      if (!activeIso) return;
      const list = byCountry.get(activeIso) || [];
      const byCity = groupByCity(list);
      const itemsInCity = byCity.get(city) || [];
      if (!itemsInCity.length) return;

      activeCity = city;
      tip2.innerHTML = buildCityTooltipHtml(activeIso, city, itemsInCity);
      openTip(tip2);
      setTooltipPosSideOf(tip1, tip2, wrap);
    }

    function closeCity() {
      if (inTip2) return;
      activeCity = "";
      closeTip(tip2);
    }

    svgRoot.addEventListener("mousemove", (ev) => {
      clearCloseTimer();
      hoverCountry = true;

      const iso = isoFromEl(ev.target);
      if (!iso || !byCountry.has(iso)) return;

      if (pinnedIso && iso !== pinnedIso) return;
      showCountry(iso, ev);
    });

    svgRoot.addEventListener("mouseleave", () => {
      hoverCountry = false;
      scheduleCloseAll();
    });

    svgRoot.addEventListener("click", (ev) => {
      const iso = isoFromEl(ev.target);
      if (!iso || !byCountry.has(iso)) return;

      pinnedIso = pinnedIso === iso ? "" : iso;
      showCountry(iso, ev);
    });

    tip1.addEventListener("pointerenter", () => {
      clearCloseTimer();
      inTip1 = true;
    });

    tip1.addEventListener("pointerleave", (ev) => {
      inTip1 = false;
      if (tip2.contains(ev.relatedTarget)) return;
      closeCity();
      scheduleCloseAll();
    });

    tip1.addEventListener("mousemove", (ev) => {
      const item = ev.target.closest(".frw__cityItem");
      if (!item) return;
      const city = item.getAttribute("data-city") || "";
      if (!city) return;
      if (city !== activeCity) showCity(city);
    });

    tip1.addEventListener("keydown", (ev) => {
      if (ev.key !== "Enter" && ev.key !== " ") return;
      const item = ev.target.closest(".frw__cityItem");
      if (!item) return;
      ev.preventDefault();
      const city = item.getAttribute("data-city") || "";
      if (city) showCity(city);
    });

    tip2.addEventListener("pointerenter", () => {
      clearCloseTimer();
      inTip2 = true;
    });

    tip2.addEventListener("pointerleave", (ev) => {
      inTip2 = false;
      if (tip1.contains(ev.relatedTarget)) {
        closeCity();
        return;
      }
      closeCity();
      scheduleCloseAll();
    });

    document.addEventListener("pointerdown", (ev) => {
      if (wrap.contains(ev.target)) return;
      if (pinnedIso) return;

      hoverCountry = false;
      inTip1 = false;
      inTip2 = false;
      activeIso = "";
      activeCity = "";
      closeTip(tip2);
      closeTip(tip1);
      clearActive(svgRoot);
    });
  }

  function init() {
    document
      .querySelectorAll("[data-fr-worldmap]")
      .forEach((wrap) => initOne(wrap));
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
