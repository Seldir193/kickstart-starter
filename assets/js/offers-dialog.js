


// assets/js/offers-dialog.js
(function () {
  "use strict";

  /* ========== tiny helpers ========== */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const esc = (s) =>
    String(s).replace(/[&<>"']/g, (m) =>
      ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[m])
    );
  const pick = (o, path) => {
    try {
      return (
        path.reduce((a, k) => (a && a[k] != null ? a[k] : null), o) ?? null
      );
    } catch {
      return null;
    }
  };

  /* ========== day/name/address helpers ========== */
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
  const dayLongPlural = (c) => (DAY_LONG[c] || c) + (c ? "s" : "");

  const nameAddr = (o) => {
    const name =
      o.clubName || o.club || o.provider || o.title || o.type || "Standort";
    const l1 = o.address || o.street || "";
    const l2 = [o.zip || o.postalCode || "", o.city || ""]
      .filter(Boolean)
      .join(", ");
    const addr =
      l1 && l2
        ? `${l1} - ${l2}`
        : l1 || l2 || (o.location || "");
    return { name, addr };
  };

  /* Club-Programme (keine normale Sessions-Tabelle) */
  function isNonTrialProgram(offer) {
    if (!offer) return false;
    const key = (offer.sub_type || offer.type || "").trim();
    return (
      key === "RentACoach_Generic" ||
      key === "ClubProgram_Generic" ||
      key === "CoachEducation"
    );
  }

  /* ========== coords → google maps link ========== */
  const isLat = (n) => Number.isFinite(n) && n >= -90 && n <= 90;
  const isLng = (n) => Number.isFinite(n) && n >= -180 && n <= 180;
  const parseCoord = (v) => {
    if (v == null) return NaN;
    const n = Number(String(v).trim().replace(",", "."));
    return Number.isFinite(n) ? n : NaN;
  };
  function latLngOf(o) {
    const lat = parseCoord(o.lat ?? o.latitude),
      lng = parseCoord(o.lng ?? o.lon ?? o.long ?? o.longitude);
    if (isLat(lat) && isLng(lng)) return [lat, lng];
    const c =
      o.coords ||
      o.coord ||
      o.position ||
      o.geo ||
      o.gps ||
      o.map ||
      o.center ||
      o.centerPoint ||
      o.point ||
      o.location;
    if (c && typeof c === "object") {
      const la = parseCoord(c.lat ?? c.latitude),
        lo = parseCoord(c.lng ?? c.lon ?? c.long ?? c.longitude);
      if (isLat(la) && isLng(lo)) return [la, lo];
    }
    return null;
  }
  const googleMapsHref = (o) => {
    const ll = latLngOf(o);
    if (ll)
      return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
        ll[0] + "," + ll[1]
      )}`;
    const { addr } = nameAddr(o);
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
      addr || o.location || ""
    )}`;
  };

  /* ========== formatters & booking link ========== */
  const formatAge = (s) => {
    const f = Number(s.ageFrom ?? ""),
      t = Number(s.ageTo ?? "");
    if (Number.isFinite(f) && Number.isFinite(t)) return `${f} - ${t} Jährige`;
    if (Number.isFinite(f)) return `${f}+ Jährige`;
    return "—";
  };
  const formatTime = (s) => {
    const a = String(s.timeFrom || "").trim(),
      b = String(s.timeTo || "").trim();
    return a && b ? `${a} - ${b}` : a || b || "—";
  };
  const formatPrice = (s) =>
    Number.isFinite(+s.price)
      ? `${(+s.price).toFixed(2)}€`
      : s.priceText || "—";

  function formatDateDE(v) {
    if (!v) return "";
    const d = new Date(v);
    if (isNaN(d.getTime())) return String(v);
    return d.toLocaleDateString("de-DE", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  function formatRangeDE(from, to) {
    const hasFrom = !!from;
    const hasTo = !!to;
    if (!hasFrom && !hasTo) return "";
    if (hasFrom && hasTo) return `${formatDateDE(from)} - ${formatDateDE(to)}`;
    if (hasFrom) return formatDateDE(from);
    return formatDateDE(to);
  }

  function getHolidayTitle(offer, session) {
    const label =
      session.holidayWeekLabel ||
      session.holidayLabel ||
      session.holidayWeek ||
      offer.holidayWeekLabel ||
      offer.holidayLabel ||
      offer.holidayWeek ||
      offer.holiday_name ||
      "";

    const from =
      session.dateFrom ||
      session.holidayDateFrom ||
      session.holidayFrom ||
      offer.dateFrom ||
      offer.holidayDateFrom ||
      offer.holidayFrom ||
      "";

    const to =
      session.dateTo ||
      session.holidayDateTo ||
      session.holidayTo ||
      offer.dateTo ||
      offer.holidayDateTo ||
      offer.holidayTo ||
      "";

    const range = formatRangeDE(from, to);
    const rangePart = range ? ` (${range})` : "";
    const combined = (label + rangePart).trim();
    return combined || range;
  }

  /* Query-String für Ferieninfos (an Next.js Booking) */
  function buildHolidayQuery(offer) {
    if (!offer) return "";

    const label =
      offer.holidayWeekName ||
      offer.holidayLabel ||
      offer.holidayWeek ||
      offer.holiday_name ||
      offer.holidayName ||
      offer.holiday ||
      "";

    const from =
      offer.holidayDateFrom ||
      offer.holidayFrom ||
      offer.dateFrom ||
      offer.startDate ||
      offer.start ||
      "";

    const to =
      offer.holidayDateTo ||
      offer.holidayTo ||
      offer.dateTo ||
      offer.endDate ||
      offer.end ||
      "";

    const params = [];
    if (label) params.push(`holidayLabel=${encodeURIComponent(label)}`);
    if (from) params.push(`holidayFrom=${encodeURIComponent(from)}`);
    if (to) params.push(`holidayTo=${encodeURIComponent(to)}`);

    return params.length ? `&${params.join("&")}` : "";
  }

  const bookHref = (base, s, offer) => {
    const id = s && s._id ? String(s._id) : "";
    const b =
      base && base.trim()
        ? base.trim().replace(/\/$/, "")
        : "http://localhost:3000";
    if (!id) return "#";

    let url = `${b}/book?offerId=${encodeURIComponent(id)}&embed=1`;

    // Für Ferienprogramme (Camps + Powertraining) Ferien-Infos mitgeben
    const cat = String(offer?.category || "").toLowerCase();
    if (cat === "holiday" || cat === "holidayprograms") {
      url += buildHolidayQuery(offer || {});
    }

    return url;
  };

  /* ========== coach helpers ========== */
  function getCoachFull(o) {
    return (
      o.coachName ||
      [o.coachFirst, o.coachLast].filter(Boolean).join(" ") ||
      o.coach ||
      ""
    ).trim();
  }
  function splitName(full) {
    const parts = String(full).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return { first: "—", last: "" };
    if (parts.length === 1) return { first: parts[0], last: "" };
    return { first: parts[0], last: parts.slice(1).join(" ") };
  }
  function getCoachFirst(o) {
    return splitName(getCoachFull(o)).first;
  }
  function getCoachLast(o) {
    return splitName(getCoachFull(o)).last;
  }

  function getNextBase() {
    const root = document.getElementById("ksDir");
    const base = (root?.dataset?.next || "").trim();
    return base ? base.replace(/\/+$/, "") : "";
  }

  function normalizeCoachSrc(src) {
    if (!src) return "";

    if (/^https?:\/\//i.test(src)) return src;

    const next = getNextBase();

    if (src.startsWith("/api/uploads/coach/")) {
      return next ? `${next}${src}` : src;
    }

    if (/^\/?uploads\/coach\//i.test(src)) {
      const p = src.startsWith("/") ? `/api${src}` : `/api/${src}`;
      return next ? `${next}${p}` : p;
    }

    if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src)) {
      const p = `/api/uploads/coach/${src}`;
      return next ? `${next}${p}` : p;
    }

    return src;
  }

  function getCoachAvatar(o) {
    const root = document.getElementById("ksDir");
    const ph = root?.dataset?.coachph || "";
    const direct =
      o.coachImage ||
      o.coachPhoto ||
      o.coachAvatar ||
      o.coachPic ||
      o.coachImg ||
      o.coach_image ||
      o.coach_photo ||
      o.coach_avatar ||
      o.coach_pic ||
      o.coach_img ||
      pick(o, ["coach", "image"]) ||
      pick(o, ["coach", "photo"]) ||
      pick(o, ["coach", "avatar"]) ||
      pick(o, ["provider", "coachImage"]) ||
      pick(o, ["provider", "coach_image"]) ||
      pick(o, ["owner", "avatarUrl"]) ||
      pick(o, ["owner", "coachImage"]) ||
      pick(o, ["creator", "avatarUrl"]) ||
      pick(o, ["user", "avatarUrl"]);
    return normalizeCoachSrc(direct || ph || "");
  }

  /* ========== body lock (einziger style-Zugriff) ========== */
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

  /* ========== Session-Cards ========== */
  function buildSessionsHtml(nextBase, sessions, offer, isPowertraining) {
    const list = Array.isArray(sessions) ? sessions : [];
    const cat = String(offer?.category || "").toLowerCase();
    const isHoliday = cat === "holiday" || cat === "holidayprograms";

    return list
      .map((s, idx) => {
        const dayCode = normDay(
          Array.isArray(s.days) && s.days.length ? s.days[0] : ""
        );
        const weekdayShort = dayCode || "";
        const weekdayLong = weekdayShort ? dayLongPlural(weekdayShort) : "";

        const time = formatTime(s);
        const age = formatAge(s);
        const fName = getCoachFirst(s);
        const lName = getCoachLast(s);
        const img = getCoachAvatar(s);
        const href = bookHref(nextBase, s, offer);
        const price = formatPrice(s);

        let topLine = "";
        let middleLine = "";

        const isPower = isPowertraining;

        if (isHoliday) {
          const holidayTitle = getHolidayTitle(offer || {}, s);

          if (!isPower) {
            topLine = holidayTitle || "—";
            middleLine = "";
          } else {
            topLine = holidayTitle || weekdayLong || "—";

            if (weekdayLong && time && time !== "—") {
              middleLine = `${weekdayLong} · ${time}`;
            } else if (weekdayLong) {
              middleLine = weekdayLong;
            } else {
              middleLine = time || "—";
            }
          }
        } else {
          topLine = weekdayLong || "—";
          middleLine = time || "—";
        }

        if (isPower) {
          return `
          <div class="ks-session ks-session--selectable" data-session-index="${idx}">
            <div class="ks-session__left">
              <div class="ks-session__row"><strong>${esc(topLine)}</strong></div>
              <div class="ks-session__row">${esc(middleLine)}</div>
              <div class="ks-session__row">${esc(age)}</div>
            </div>

            <div class="ks-session__coach">
              ${
                img
                  ? `<img class="ks-coach__avatar" src="${esc(
                      img
                    )}" alt="${esc(fName)} ${esc(lName)}">`
                  : ""
              }
              <div class="ks-coach__name">
                <span class="ks-coach__first">${esc(fName)}</span>
                <span class="ks-coach__last">${esc(lName)}</span>
              </div>
            </div>

            <div class="ks-session__actions">
              <span class="ks-session__price">${esc(price)}</span>
            </div>
          </div>`;
        }

        const btnLabel = "Weiter";

        return `
        <div class="ks-session ks-session--selectable" data-book-href="${esc(
          href
        )}">
          <div class="ks-session__left">
            <div class="ks-session__row"><strong>${esc(topLine)}</strong></div>
            <div class="ks-session__row">${esc(middleLine)}</div>
            <div class="ks-session__row">${esc(age)}</div>
          </div>

          <div class="ks-session__coach">
            ${
              img
                ? `<img class="ks-coach__avatar" src="${esc(
                    img
                  )}" alt="${esc(fName)} ${esc(lName)}">`
                : ""
            }
            <div class="ks-coach__name">
              <span class="ks-coach__first">${esc(fName)}</span>
              <span class="ks-coach__last">${esc(lName)}</span>
            </div>
          </div>

          <div class="ks-session__actions">
            <a class="btn btn-primary ks-session__btn" href="${esc(
              href
            )}" data-book-href="${esc(href)}">${esc(btnLabel)}</a>
            <span class="ks-session__price">${esc(price)}</span>
          </div>
        </div>`;
      })
      .join("");
  }

  /* ========== public state ========== */
  const LAST = {
    offer: null,
    sessions: null,
    opts: null,
    isPowertraining: false,
    selected: [],
    nextBase: "",
  };

  /* ========== offer modal: wiring ========== */
  function attachHandlers(modal, overlay) {
    const panel = $(".ks-dir__panel", modal);

    const panelOnClick = (e) => {
      if (e.target.closest("[data-close]")) {
        close();
        return;
      }

      // Powertraining: Multi-Select + globaler Weiter-Button
      if (LAST.isPowertraining) {
        const row = e.target.closest(".ks-session--selectable");
        if (row && row.hasAttribute("data-session-index")) {
          const idx = Number(row.getAttribute("data-session-index"));
          if (!Number.isNaN(idx)) {
            const i = LAST.selected.indexOf(idx);
            if (i === -1) {
              LAST.selected.push(idx);
              row.classList.add("ks-session--selected");
            } else {
              LAST.selected.splice(i, 1);
              row.classList.remove("ks-session--selected");
            }
          }
          return;
        }

        const cont = e.target.closest("[data-pt-continue]");
        if (cont) {
          if (
            !Array.isArray(LAST.sessions) ||
            !LAST.sessions.length ||
            !LAST.selected.length
          ) {
            return;
          }

          const base = (LAST.nextBase || getNextBase() || "").replace(
            /\/$/,
            ""
          );
          const baseOfferId =
            (LAST.offer && LAST.offer._id) ||
            (LAST.sessions[0] && LAST.sessions[0]._id) ||
            "";

          if (!baseOfferId) return;

          const selectedDays = [];
          const ptMeta = [];

          LAST.selected.forEach((i) => {
            const s = LAST.sessions[i];
            if (!s) return;

            const dayCode =
              Array.isArray(s.days) && s.days.length
                ? normDay(s.days[0])
                : "";
            const dayLabel = dayCode ? dayLongPlural(dayCode) : "";

            if (dayLabel) selectedDays.push(dayLabel);

            ptMeta.push({
              id: s._id || "",
              day: dayCode || "",
              dateFrom:
                s.dateFrom ||
                s.holidayDateFrom ||
                s.holidayFrom ||
                "",
              dateTo:
                s.dateTo ||
                s.holidayDateTo ||
                s.holidayTo ||
                "",
              timeFrom: s.timeFrom || "",
              timeTo: s.timeTo || "",
              price: s.price,
            });
          });

          let url = `${base || "http://localhost:3000"}/book?offerId=${encodeURIComponent(
            baseOfferId
          )}&embed=1`;

          if (selectedDays.length) {
            url += `&days=${encodeURIComponent(selectedDays.join(","))}`;
          }

          const holidayQuery = buildHolidayQuery(LAST.offer || {});
          if (holidayQuery) {
            url += holidayQuery;
          }

          if (ptMeta.length) {
            try {
              url += `&ptmeta=${encodeURIComponent(
                JSON.stringify(ptMeta)
              )}`;
            } catch {}
          }

          const root = $("#ksDir");
          const icon =
            root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
          close();
          BookDialog.open(url, { closeIcon: icon });
          return;
        }

        e.stopPropagation();
        return;
      }

      // Standard-Fall: Klick mit data-book-href öffnet Buchungsdialog
      const book = e.target.closest("[data-book-href]");
      if (book) {
        e.preventDefault();
        const url =
          book.getAttribute("data-book-href") ||
          book.getAttribute("href") ||
          "";
        const root = $("#ksDir");
        const icon =
          root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
        close();
        BookDialog.open(url, { closeIcon: icon });
        return;
      }

      e.stopPropagation();
    };

    panel && panel.addEventListener("click", panelOnClick);

    const onOverlayClick = () => close();
    overlay && overlay.addEventListener("click", onOverlayClick);

    const onEsc = (e) => {
      if (e.key === "Escape") close();
    };
    document.addEventListener("keydown", onEsc);

    modal.__ksHandlers = { panelOnClick, onOverlayClick, onEsc };
  }

  function detachHandlers(modal) {
    const h = modal && modal.__ksHandlers;
    if (!h) return;
    const panel = $(".ks-dir__panel", modal);
    const overlay = $(".ks-dir__overlay", modal);
    panel && panel.removeEventListener("click", h.panelOnClick);
    overlay && overlay.removeEventListener("click", h.onOverlayClick);
    document.removeEventListener("keydown", h.onEsc);
    modal.__ksHandlers = null;
  }

  /* ========== public API ========== */
  function open(offer, sessions, opts = {}) {
    const modal = $("#ksOfferModal");
    if (!modal || !offer) return;

    let overlay = $(".ks-dir__overlay", modal);
    let panel = $(".ks-dir__panel", modal);
    if (!overlay) {
      overlay = document.createElement("div");
      overlay.className = "ks-dir__overlay";
      modal.appendChild(overlay);
    }
    if (!panel) {
      panel = document.createElement("div");
      panel.className = "ks-dir__panel";
      modal.appendChild(panel);
    }

    const root = $("#ksDir");
    const closeURL =
      opts.closeIcon ||
      root?.dataset?.closeIcon ||
      root?.dataset?.closeicon ||
      "";
    const nextBase =
      opts.nextBase || root?.dataset?.next || "http://localhost:3000";

    const { name, addr } = nameAddr(offer);
    const gHref = googleMapsHref(offer);
    const list = sessions && sessions.length ? sessions : [offer];

    const closeBtn = closeURL
      ? `<button type="button" class="ks-dir__close" data-close aria-label="Schließen"><img src="${esc(
          closeURL
        )}" alt="Schließen" width="14" height="14"></button>`
      : `<button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>`;

    const nonTrial = isNonTrialProgram(offer);

    const cat = String(offer.category || "").toLowerCase();
    const isPowertraining =
      cat === "holiday" &&
      String(offer.sub_type || "").toLowerCase() === "powertraining";

    // state merken
    LAST.offer = offer;
    LAST.sessions = list;
    LAST.opts = opts;
    LAST.isPowertraining = isPowertraining;
    LAST.selected = [];
    LAST.nextBase = nextBase;

    const sublineHtml = "";

    let sessionsHtml;
    if (nonTrial) {
      const href = bookHref(nextBase, offer, offer);
      sessionsHtml = `
        <div class="ks-session ks-session--simple ks-session--selectable" data-book-href="${esc(
          href
        )}">
          <div class="ks-session__info">
            Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.
          </div>
          <div class="ks-session__actions">
            <a class="btn btn-primary ks-session__btn" href="${esc(
              href
            )}" data-book-href="${esc(href)}">Weiter</a>
          </div>
        </div>`;
    } else {
      sessionsHtml = buildSessionsHtml(nextBase, list, offer, isPowertraining);
    }

    const footerHtml = isPowertraining
      ? `<div class="ks-offer__footer">
           <button type="button" class="btn btn-primary ks-offer__continue" data-pt-continue>Weiter</button>
         </div>`
      : "";

    panel.innerHTML = `
      ${closeBtn}
      <h3 class="ks-dir__m-title">${esc(name)}</h3>
      ${sublineHtml}
      <p class="ks-dir__m-addr">${esc(addr)}</p>
      <p class="ks-offer__google">
        <a href="${esc(gHref)}" target="_blank" rel="noopener">
          Anfahrt mit Google
        </a>
      </p>
      <div class="ks-offer__sessions">${sessionsHtml}</div>
      ${footerHtml}
    `;

    modal.hidden = false;
    lockBody();
    attachHandlers(modal, overlay);
  }

  function close() {
    const modal = $("#ksOfferModal");
    if (!modal) return;
    modal.hidden = true;
    detachHandlers(modal);
    unlockBody();
  }

  window.KSOffersDialog = { open, close, __last: LAST };
})();










