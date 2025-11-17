// assets/js/offers-dialog.js
(function () {
  "use strict";

  /* ========== tiny helpers ========== */
  const $  = (sel, ctx = document) => ctx.querySelector(sel);
  const esc = (s) => String(s).replace(/[&<>"']/g, m =>
    ({ "&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;" }[m])
  );
  const pick = (o, path) => {
    try { return path.reduce((a,k)=> (a && a[k] != null ? a[k] : null), o) ?? null; } catch { return null; }
  };

  /* ========== day/name/address helpers ========== */
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
  const normDay = (v) => v ? (DAY_ALIASES[String(v).trim().toLowerCase()] || v) : "";
  const dayLongPlural = (c) => (DAY_LONG[c] || c) + (c ? "s" : "");

  const nameAddr = (o) => {
    const name = o.clubName || o.club || o.provider || o.title || o.type || "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""].filter(Boolean).join(", ");
    const addr = (l1 && l2) ? `${l1} - ${l2}` : (l1 || l2 || (o.location || ""));
    return { name, addr };
  };

  /* Club-Programme (keine normale Sessions-Tabelle) */
  function isNonTrialProgram(offer){
    if (!offer) return false;
    const key = (offer.sub_type || offer.type || '').trim();
    return (
      key === 'RentACoach_Generic' ||
      key === 'ClubProgram_Generic' ||
      key === 'CoachEducation'
    );
  }

  /* ========== coords → google maps link ========== */
  const isLat = (n) => Number.isFinite(n) && n >= -90 && n <= 90;
  const isLng = (n) => Number.isFinite(n) && n >= -180 && n <= 180;
  const parseCoord = (v) => { if (v == null) return NaN; const n = Number(String(v).trim().replace(",", ".")); return Number.isFinite(n) ? n : NaN; };
  function latLngOf(o){
    const lat = parseCoord(o.lat ?? o.latitude), lng = parseCoord(o.lng ?? o.lon ?? o.long ?? o.longitude);
    if (isLat(lat) && isLng(lng)) return [lat, lng];
    const c = o.coords || o.coord || o.position || o.geo || o.gps || o.map || o.center || o.centerPoint || o.point || o.location;
    if (c && typeof c === "object") {
      const la = parseCoord(c.lat ?? c.latitude), lo = parseCoord(c.lng ?? c.lon ?? c.long ?? c.longitude);
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

  /* ========== formatters & booking link ========== */
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
  const formatPrice = s => Number.isFinite(+s.price) ? `${(+s.price).toFixed(2)}€/Monat` : (s.priceText || "—");
  const bookHref    = (base, s) => {
    const id = s && s._id ? String(s._id) : "";
    const b  = (base && base.trim()) ? base.trim().replace(/\/$/,"") : "http://localhost:3000";
    return id ? `${b}/book?offerId=${encodeURIComponent(id)}&embed=1` : "#";
  };

  /* ========== coach helpers ========== */
  function getCoachFull(o){
    return (
      o.coachName ||
      [o.coachFirst, o.coachLast].filter(Boolean).join(" ") ||
      o.coach ||
      ""
    ).trim();
  }
  function splitName(full){
    const parts = String(full).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return { first: "—", last: "" };
    if (parts.length === 1) return { first: parts[0], last: "" };
    return { first: parts[0], last: parts.slice(1).join(" ") };
  }
  function getCoachFirst(o){ return splitName(getCoachFull(o)).first; }
  function getCoachLast(o){  return splitName(getCoachFull(o)).last;  }

  function getNextBase(){
    const root = document.getElementById('ksDir');
    const base = (root?.dataset?.next || '').trim();
    return base ? base.replace(/\/+$/, '') : '';
  }

  function normalizeCoachSrc(src){
    if (!src) return '';

    if (/^https?:\/\//i.test(src)) return src;

    const next = getNextBase();

    if (src.startsWith('/api/uploads/coach/')) {
      return next ? `${next}${src}` : src;
    }

    if (/^\/?uploads\/coach\//i.test(src)) {
      const p = src.startsWith('/') ? `/api${src}` : `/api/${src}`;
      return next ? `${next}${p}` : p;
    }

    if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src)) {
      const p = `/api/uploads/coach/${src}`;
      return next ? `${next}${p}` : p;
    }

    return src;
  }

  function getCoachAvatar(o){
    const root = document.getElementById("ksDir");
    const ph   = root?.dataset?.coachph || "";
    const direct =
      o.coachImage || o.coachPhoto || o.coachAvatar || o.coachPic || o.coachImg ||
      o.coach_image || o.coach_photo || o.coach_avatar || o.coach_pic || o.coach_img ||
      pick(o, ["coach","image"]) || pick(o, ["coach","photo"]) || pick(o, ["coach","avatar"]) ||
      pick(o, ["provider","coachImage"]) || pick(o, ["provider","coach_image"]) ||
      pick(o, ["owner","avatarUrl"]) || pick(o, ["owner","coachImage"]) ||
      pick(o, ["creator","avatarUrl"]) || pick(o, ["user","avatarUrl"]);
    return normalizeCoachSrc(direct || ph || "");
  }

  /* ========== body lock ========== */
  const LOCK_ATTR = "data-ks-modal-lock";
  const lockBody = () => {
    if (!document.body.hasAttribute(LOCK_ATTR)) {
      document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
      document.body.style.overflow = "hidden";
    }
    document.body.classList.add("ks-modal-open");
  };
  const unlockBody = () => {
    if (document.body.hasAttribute(LOCK_ATTR)) {
      document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
      document.body.removeAttribute(LOCK_ATTR);
    }
    document.body.classList.remove("ks-modal-open");
  };

  /* ========== enforce modal layout ========== */
  const forceLayout = (modal, overlay, panel, z = 4000) => {
    if (modal) { modal.style.position="fixed"; modal.style.inset="0"; modal.style.zIndex=String(z); modal.style.display="grid"; modal.style.placeItems="center"; }
    if (overlay){ overlay.style.position="absolute"; overlay.style.inset="0"; overlay.style.background="rgba(0,0,0,.45)"; overlay.style.display="block"; overlay.style.zIndex="0"; }
    if (panel) {
      panel.style.position="relative"; panel.style.zIndex="1";
      panel.style.background="#fff"; panel.style.border="1px solid #eaeaea";
      panel.style.borderRadius="12px"; panel.style.boxShadow="0 16px 40px rgba(0,0,0,.18)";
      panel.style.padding="16px"; panel.style.width="min(720px, calc(100% - 24px))";
      panel.style.maxHeight="calc(100dvh - 24px)"; panel.style.overflow="auto";
    }
  };

  /* ========== BOOKING DIALOG (iframe) ========== */
  const BookDialog = (() => {
    function ensure() {
      let modal = $("#ksBookModal");
      if (!modal) {
        modal = document.createElement("div");
        modal.id = "ksBookModal";
        modal.className = "ks-dir__modal";
        modal.hidden = true;

        modal.innerHTML = `
          <div class="ks-dir__overlay" data-close></div>
          <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-label="Buchung">
            <button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>
            <iframe class="ks-book__frame" src="" title="Buchung" loading="lazy" referrerpolicy="no-referrer-when-downgrade" style="width:100%;height:80vh;border:0;border-radius:10px;"></iframe>
          </div>`;

        document.body.appendChild(modal);
      }
      return modal;
    }

    function open(url, opts = {}) {
      const modal = ensure();
      const overlay = $(".ks-dir__overlay", modal);
      const panel   = $(".ks-dir__panel", modal);
      const frame   = $(".ks-book__frame", modal);
      const closeBtn= $(".ks-dir__close", modal);

      const root = $("#ksDir");
      const icon = opts.closeIcon || root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
      if (icon) closeBtn.innerHTML = `<img src="${esc(icon)}" alt="Schließen" width="14" height="14">`;

      forceLayout(modal, overlay, panel, 4100);

      frame.src = url || "#";
      modal.hidden = false;
      lockBody();

      const doClose = () => {
        modal.hidden = true;
        frame.src = "about:blank";
        overlay.removeEventListener("click", onOverlay);
        modal.removeEventListener("click", onAny);
        document.removeEventListener("keydown", onEsc);
        unlockBody();
      };
      const onOverlay = () => doClose();
      const onAny     = (e) => { if (e.target.closest("[data-close]")) doClose(); };
      const onEsc     = (e) => { if (e.key === "Escape") doClose(); };

      overlay.addEventListener("click", onOverlay);
      modal.addEventListener("click", onAny);
      document.addEventListener("keydown", onEsc);
    }

    function close() {
      const modal = $("#ksBookModal");
      if (!modal || modal.hidden) return;
      const frame = $(".ks-book__frame", modal);
      if (frame) frame.src = "about:blank";
      modal.hidden = true;
      unlockBody();
    }

    return { open, close };
  })();

  /* ========== session cards ========== */
  function buildSessionsHtml(nextBase, sessions){
    const list = Array.isArray(sessions) ? sessions : [];

    return list.map((s) => {
      const day   = (Array.isArray(s.days) && s.days.length) ? dayLongPlural(normDay(s.days[0])) : "—";
      const time  = formatTime(s);
      const age   = formatAge(s);
      const fName = getCoachFirst(s);
      const lName = getCoachLast(s);
      const img   = getCoachAvatar(s);
      const href  = bookHref(nextBase, s);

      const btnLabel = 'Weiter';  // ✅ EINHEITLICHER Button-Text

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
            <a class="btn btn-primary ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">${esc(btnLabel)}</a>
            <span class="ks-session__price">${esc(formatPrice(s))}</span>
          </div>
        </div>`;
    }).join("");
  }

  /* ========== offer modal: wiring ========== */
  function attachHandlers(modal, overlay){
    const panel = $(".ks-dir__panel", modal);

    const panelOnClick = (e) => {
      if (e.target.closest("[data-close]")) { close(); return; }

      const book = e.target.closest("[data-book-href]");
      if (book) {
        e.preventDefault();
        const url = book.getAttribute("data-book-href") || book.getAttribute("href") || "";
        const root = $("#ksDir");
        const icon = root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
        close();
        BookDialog.open(url, { closeIcon: icon });
        return;
      }
      e.stopPropagation();
    };
    panel && panel.addEventListener("click", panelOnClick);

    const onOverlayClick = () => close();
    overlay && overlay.addEventListener("click", onOverlayClick);

    const onEsc = (e) => { if (e.key === "Escape") close(); };
    document.addEventListener("keydown", onEsc);

    modal.__ksHandlers = { panelOnClick, onOverlayClick, onEsc };
  }
  function detachHandlers(modal){
    const h = modal && modal.__ksHandlers;
    if (!h) return;
    const panel   = $(".ks-dir__panel", modal);
    const overlay = $(".ks-dir__overlay", modal);
    panel   && panel.removeEventListener("click", h.panelOnClick);
    overlay && overlay.removeEventListener("click", h.onOverlayClick);
    document.removeEventListener("keydown", h.onEsc);
    modal.__ksHandlers = null;
  }

  /* ========== public API ========== */
  const LAST = { offer:null, sessions:null, opts:null };

  function open(offer, sessions, opts = {}){
    const modal = $("#ksOfferModal");
    if (!modal || !offer) return;

    LAST.offer = offer;
    LAST.sessions = sessions;
    LAST.opts = opts;

    let overlay = $(".ks-dir__overlay", modal);
    let panel   = $(".ks-dir__panel",   modal);
    if (!overlay) { overlay = document.createElement("div"); overlay.className = "ks-dir__overlay"; modal.appendChild(overlay); }
    if (!panel)   { panel   = document.createElement("div"); panel.className   = "ks-dir__panel";   modal.appendChild(panel); }

    forceLayout(modal, overlay, panel, 4000);

    const root     = $("#ksDir");
    const closeURL = opts.closeIcon || root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
    const nextBase = opts.nextBase  || root?.dataset?.next      || "http://localhost:3000";

    const { name, addr } = nameAddr(offer);
    const gHref = googleMapsHref(offer);
    const list  = (sessions && sessions.length ? sessions : [offer]);

    const closeBtn = closeURL
      ? `<button type="button" class="ks-dir__close" data-close aria-label="Schließen"><img src="${esc(closeURL)}" alt="Schließen" width="14" height="14"></button>`
      : `<button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>`;

    const nonTrial = isNonTrialProgram(offer);

    // 🔁 KEINE Unterzeile mehr
    const sublineHtml = '';

    // Club-Programme: einfache Karte
    let sessionsHtml;
    if (nonTrial) {
      const href = bookHref(nextBase, offer);
      sessionsHtml = `
        <div class="ks-session ks-session--simple">
          <div class="ks-session__info">
            Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.
          </div>
          <div class="ks-session__actions">
            <a class="btn btn-primary ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">Weiter</a>
          </div>
        </div>`;
    } else {
      sessionsHtml = buildSessionsHtml(nextBase, list);
    }

    panel.innerHTML = `
      ${closeBtn}
      <h3 class="ks-dir__m-title">${esc(name)}</h3>
      ${sublineHtml}
      <p class="ks-dir__m-addr">${esc(addr)}</p>
      <p class="ks-offer__google"><a href="${esc(gHref)}" target="_blank" rel="noopener">Anfahrt mit Google</a></p>
      <div class="ks-offer__sessions">${sessionsHtml}</div>
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

  window.KSOffersDialog = { open, close, __last: LAST };

  /* ========== BACK from embedded booking (Next) ========== */
  window.addEventListener("message", (e) => {
    const d = e && e.data;
    if (!d || (d.type !== "KS_BOOKING_BACK" && d.type !== "KS_BOOKING_CLOSE")) return;
    BookDialog.close();
    if (d.type === "KS_BOOKING_BACK" && window.KSOffersDialog && window.KSOffersDialog.__last?.offer) {
      const { offer, sessions, opts } = window.KSOffersDialog.__last;
      window.KSOffersDialog.open(offer, sessions, opts || {});
    }
  }, false);

})();






