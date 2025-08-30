





















// assets/js/offers-dialog.js
(function () {
  "use strict";

  const $  = (s, c = document) => c.querySelector(s);
  const esc = (s) => String(s).replace(/[&<>"']/g, m =>
    ({ "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;" }[m])
  );

  // ---- formatting helpers ----
  const DAY_ALIASES = {
    m:"Mo", mo:"Mo", montag:"Mo", monday:"Mo", mon:"Mo",
    di:"Di", dienstag:"Di", tuesday:"Di", tue:"Di",
    mi:"Mi", mittwoch:"Mi", wednesday:"Mi", wed:"Mi",
    do:"Do", donnerstag:"Do", thursday:"Do", thu:"Do",
    fr:"Fr", freitag:"Fr", friday:"Fr", fri:"Fr",
    sa:"Sa", samstag:"Sa", saturday:"Sa", sat:"Sa",
    so:"So", sonntag:"So", sunday:"So", sun:"So"
  };
  const DAY_LONG = { Mo:"Montag", Di:"Dienstag", Mi:"Mittwoch", Do:"Donnerstag", Fr:"Freitag", Sa:"Samstag", So:"Sonntag" };
  const normDay = v => v ? (DAY_ALIASES[String(v).trim().toLowerCase()] || v) : "";
  const dayLongPlural = c => (DAY_LONG[c] || c) + (c ? "s" : "");

  const nameAddr = (o) => {
    const name = o.clubName || o.club || o.provider || o.title || o.type || "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""].filter(Boolean).join(", ");
    const addr = (l1 && l2) ? `${l1} - ${l2}` : (l1 || l2 || (o.location || ""));
    return { name, addr };
  };

  // coords for Google link
  const isLat = n => Number.isFinite(n) && n >= -90 && n <= 90;
  const isLng = n => Number.isFinite(n) && n >= -180 && n <= 180;
  const parseCoord = v => {
    if (v == null) return NaN;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : NaN;
  };
  function latLngOf(o){
    const lat = parseCoord(o.lat ?? o.latitude);
    const lng = parseCoord(o.lng ?? o.lon ?? o.long ?? o.longitude);
    if (isLat(lat) && isLng(lng)) return [lat, lng];
    const c = o.coords || o.coord || o.position || o.geo || o.gps || o.map || o.center || o.centerPoint || o.point || o.location;
    if (c && typeof c === "object") {
      const la = parseCoord(c.lat ?? c.latitude);
      const lo = parseCoord(c.lng ?? c.lon ?? c.long ?? c.longitude);
      if (isLat(la) && isLng(lo)) return [la, lo];
    }
    return null;
  }
  const googleMapsHref = (o) => {
    const ll = latLngOf(o);
    if (ll) return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(ll[0]+","+ll[1])}`;
    const { addr } = nameAddr(o);
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(addr || (o.location || ""))}`;
  };

  const formatAge   = s => {
    const f = Number(s.ageFrom ?? ""), t = Number(s.ageTo ?? "");
    if (Number.isFinite(f) && Number.isFinite(t)) return `${f} - ${t} Jährige`;
    if (Number.isFinite(f)) return `${f}+ Jährige`;
    return "—";
  };
  const formatTime  = s => {
    const a = String(s.timeFrom || "").trim(), b = String(s.timeTo || "").trim();
    return a && b ? `${a} - ${b}` : (a || b || "—");
  };
  const formatCoach = s => s.coachName || s.coach || [s.coachFirst, s.coachLast].filter(Boolean).join(" ") || "—";
  const formatPrice = s => Number.isFinite(+s.price) ? `${(+s.price).toFixed(2)}€/Monat` : (s.priceText || "—");
  const bookHref    = (base, s) => {
    const id = s && s._id ? String(s._id) : "";
    const b  = (base && base.trim()) ? base.trim().replace(/\/$/,"") : "http://localhost:3000";
    return id ? `${b}/book?offerId=${encodeURIComponent(id)}&embed=1` : "#";
  };

  // session cards
  function buildSessionsHtml(nextBase, sessions){
    const root = document.getElementById("ksDir");
    const coachPH = root?.dataset?.coachph || "";

    const coachFirst = (s) =>
      s.coachFirst || (s.coachName ? String(s.coachName).split(/\s+/)[0] : (s.coach || "").split(/\s+/)[0]) || "—";
    const coachLast  = (s) => {
      if (s.coachLast) return s.coachLast;
      const full = s.coachName || s.coach || "";
      const parts = String(full).trim().split(/\s+/);
      return parts.length > 1 ? parts.slice(1).join(" ") : "—";
    };
    const coachImg   = (s) =>
      s.coachImage || s.coachPhoto || s.coachAvatar || s.coachPic || s.coachImg || coachPH;

    return (sessions || []).map((s) => {
      const day   = (Array.isArray(s.days) && s.days.length) ? dayLongPlural(normDay(s.days[0])) : "—";
      const time  = formatTime(s);
      const age   = formatAge(s);
      const fName = coachFirst(s);
      const lName = coachLast(s);
      const img   = coachImg(s);
      const href  = bookHref(nextBase, s);

      return `
        <div class="ks-session">
          <div class="ks-session__left">
            <div class="ks-session__row"><strong>${esc(day)}</strong></div>
            <div class="ks-session__row">${esc(time)}</div>
            <div class="ks-session__row">${esc(age)}</div>
          </div>

          <div class="ks-session__coach">
            ${img ? `<img class="ks-coach__avatar" src="${esc(img)}" alt="${esc(fName)} ${esc(lName)}">` : ""}
            <div class="ks-coach__name">
              <span class="ks-coach__first">${esc(fName)}</span>
              <span class="ks-coach__last">${esc(lName)}</span>
            </div>
          </div>

          <div class="ks-session__actions">
            <a class="btn btn-primary ks-session__btn" href="${esc(href)}" target="_blank" rel="noopener">Auswählen</a>
            <span class="ks-session__price">${esc(formatPrice(s))}</span>
          </div>
        </div>`;
    }).join("");
  }

  // force true overlay layout (wins against any conflicting CSS)
  function forceLayout(modal, overlay, panel){
    if (modal) {
      modal.style.position   = "fixed";
      modal.style.inset      = "0";
      modal.style.zIndex     = "4000";
      modal.style.display    = "grid";
      modal.style.placeItems = "center";
    }
    if (overlay) {
      overlay.style.position      = "absolute";
      overlay.style.inset         = "0";
      overlay.style.background    = "rgba(0,0,0,.45)";
      overlay.style.display       = "block";
      overlay.style.zIndex        = "0";
      overlay.style.pointerEvents = "auto";
    }
    if (panel) {
      panel.style.position     = "relative";
      panel.style.zIndex       = "1";
      panel.style.background   = "#fff";
      panel.style.border       = "1px solid #eaeaea";
      panel.style.borderRadius = "12px";
      panel.style.boxShadow    = "0 16px 40px rgba(0,0,0,.18)";
      panel.style.padding      = "16px";
      panel.style.width        = "min(720px, calc(100% - 24px))";
      panel.style.maxHeight    = "calc(100dvh - 24px)";
      panel.style.overflow     = "auto";
    }
  }

  // body lock
  const LOCK_ATTR = "data-ks-modal-lock";
  function lockBody(){
    if (!document.body.hasAttribute(LOCK_ATTR)) {
      document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
      document.body.style.overflow = "hidden";
    }
    document.body.classList.add("ks-modal-open");
  }
  function unlockBody(){
    if (document.body.hasAttribute(LOCK_ATTR)) {
      document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
      document.body.removeAttribute(LOCK_ATTR);
    }
    document.body.classList.remove("ks-modal-open");
  }









  // replace your current attachHandlers/detachHandlers with this:

function attachHandlers(modal, overlay){
  const panel = document.querySelector("#ksOfferModal .ks-dir__panel");

  // Handle clicks *inside* the dialog:
  // - If the click is on [data-close] (e.g. the X img inside the button), close().
  // - Otherwise stop propagation so overlay/modal don’t see inside clicks.
  const panelOnClick = (e) => {
    if (e.target.closest("[data-close]")) { close(); return; }
    e.stopPropagation();
  };
  panel && panel.addEventListener("click", panelOnClick);

  // Clicking the dimmed background closes
  const onOverlayClick = () => close();
  overlay && overlay.addEventListener("click", onOverlayClick);

  // ESC closes
  const onEsc = (e) => { if (e.key === "Escape") close(); };
  document.addEventListener("keydown", onEsc);

  modal.__ksHandlers = { panelOnClick, onOverlayClick, onEsc };
}

function detachHandlers(modal){
  const h = modal && modal.__ksHandlers;
  if (!h) return;
  const panel   = document.querySelector("#ksOfferModal .ks-dir__panel");
  const overlay = document.querySelector("#ksOfferModal .ks-dir__overlay");
  panel   && panel.removeEventListener("click", h.panelOnClick);
  overlay && overlay.removeEventListener("click", h.onOverlayClick);
  document.removeEventListener("keydown", h.onEsc);
  modal.__ksHandlers = null;
}










  // public API
  function open(offer, sessions, opts = {}){
    const modal = $("#ksOfferModal");
    if (!modal || !offer) return;

    let overlay = $(".ks-dir__overlay", modal);
    let panel   = $(".ks-dir__panel",   modal);
    if (!overlay) { overlay = document.createElement("div"); overlay.className = "ks-dir__overlay"; modal.appendChild(overlay); }
    if (!panel)   { panel   = document.createElement("div"); panel.className   = "ks-dir__panel";   modal.appendChild(panel); }

    forceLayout(modal, overlay, panel);

    const root     = $("#ksDir");
    const closeURL = opts.closeIcon || root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
    const nextBase = opts.nextBase || root?.dataset?.next || "http://localhost:3000";

    const { name, addr } = nameAddr(offer);
    const gHref = googleMapsHref(offer);
    const list  = (sessions && sessions.length ? sessions : [offer]);

    const closeBtn = closeURL
      ? `<button type="button" class="ks-dir__close" data-close aria-label="Schließen"><img src="${esc(closeURL)}" alt="Schließen" width="14" height="14"></button>`
      : `<button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>`;

    panel.innerHTML = `
      ${closeBtn}
      <h3 class="ks-dir__m-title">${esc(name)}</h3>
      <p class="ks-dir__m-sub">Kostenfreies Schnuppertraining</p>
      <p class="ks-dir__m-addr">${esc(addr)}</p>
      <p class="ks-offer__google"><a href="${esc(gHref)}" target="_blank" rel="noopener">Anfahrt mit Google</a></p>
      <div class="ks-offer__sessions">${buildSessionsHtml(nextBase, list)}</div>
    `;

    modal.hidden = false;
    lockBody();
    attachHandlers(modal, overlay);
  }

  function close(){
    const modal = $("#ksOfferModal");
    if (!modal) return;
    modal.hidden = true;
    detachHandlers(modal);
    unlockBody();
  }

  window.KSOffersDialog = { open, close };
})();
