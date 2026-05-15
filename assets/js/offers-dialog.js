(function () {
  "use strict";

  const LOCK_ATTR = "data-ks-modal-lock";
  const $ = (selector, root = document) => root.querySelector(selector);

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

  const COACH_PATHS = [
    ["coach", "image"],
    ["coach", "photo"],
    ["coach", "avatar"],
    ["provider", "coachImage"],
    ["provider", "coach_image"],
    ["owner", "avatarUrl"],
    ["owner", "coachImage"],
    ["creator", "avatarUrl"],
    ["user", "avatarUrl"],
  ];

  const LAST = {
    offer: null,
    sessions: null,
    opts: null,
    isPowertraining: false,
    selected: [],
    nextBase: "",
  };

  function esc(value) {
    return String(value ?? "").replace(/[&<>"']/g, replaceHtml);
  }

  function replaceHtml(match) {
    return {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#39;",
    }[match];
  }

  function pick(object, path) {
    return path.reduce(getPathValue, object) ?? null;
  }

  function getPathValue(current, key) {
    return current && current[key] != null ? current[key] : null;
  }

  function getRoot() {
    return document.getElementById("ksDir");
  }

  function getModal() {
    return document.getElementById("ksOfferModal");
  }

  function getPanel(modal) {
    return $(".ks-offer-modal__panel", modal);
  }

  function getOverlay(modal) {
    return $(".ks-offer-modal__overlay", modal);
  }

  function getNextBase() {
    const base = (getRoot()?.dataset?.next || "").trim();
    return base ? base.replace(/\/+$/, "") : "";
  }

  function getIconBase() {
    const base = getRoot()?.dataset?.dialogIconBase || "";
    return base ? String(base).replace(/\/?$/, "/") : "";
  }

  function getIcon(name) {
    const base = getIconBase();
    return base ? `${base}${name}` : "";
  }

  function i18nSpan(key, fallback) {
    return `<span data-i18n="${esc(key)}">${esc(fallback)}</span>`;
  }

  function i18nAttr(key) {
    return `data-i18n="${esc(key)}" data-i18n-attr="aria-label"`;
  }

  function translateDynamicContent(root) {
    document.dispatchEvent(
      new CustomEvent("ks:i18n:content-added", { detail: { root } }),
    );
  }

  function lockBody() {
    if (!document.body.hasAttribute(LOCK_ATTR)) setBodyLock();
    document.body.classList.add("ks-modal-open");
  }

  function setBodyLock() {
    document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
    document.body.style.overflow = "hidden";
  }

  function unlockBody() {
    if (document.body.hasAttribute(LOCK_ATTR)) restoreBodyLock();
    document.body.classList.remove("ks-modal-open");
  }

  function restoreBodyLock() {
    document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
    document.body.removeAttribute(LOCK_ATTR);
  }

  function normDay(value) {
    return value
      ? DAY_ALIASES[String(value).trim().toLowerCase()] || value
      : "";
  }

  function dayLong(value) {
    const code = normDay(value);
    return code ? DAY_LONG[code] || code : "";
  }

  function isWeeklyOffer(offer) {
    const category = String(offer?.category || "").toLowerCase();
    return category === "weekly" || category === "weeklycourses";
  }

  function isHolidayOffer(offer) {
    const category = String(offer?.category || "").toLowerCase();
    return category === "holiday" || category === "holidayprograms";
  }

  function getOfferDay(session, offer, count) {
    if (session?.day) return dayLong(session.day);
    const sessionDay = Array.isArray(session?.days) ? session.days[0] : "";
    if (sessionDay) return dayLong(sessionDay);
    if (count === 1 && offer?.day) return dayLong(offer.day);
    return getSingleOfferDay(offer, count);
  }

  function getSingleOfferDay(offer, count) {
    const offerDay = Array.isArray(offer?.days) ? offer.days[0] : "";
    return count === 1 && offerDay ? dayLong(offerDay) : "";
  }

  function formatScheduleTitle(session, offer, count) {
    return getOfferDay(session, offer, count) || "—";
  }

  function nameAddr(offer) {
    const name =
      offer.clubName ||
      offer.club ||
      offer.provider ||
      offer.title ||
      offer.type ||
      "Standort";

    const lineOne = offer.address || offer.street || "";
    const lineTwo = [offer.zip || offer.postalCode || "", offer.city || ""]
      .filter(Boolean)
      .join(", ");

    return { name, addr: buildAddress(lineOne, lineTwo, offer) };
  }

  function buildAddress(lineOne, lineTwo, offer) {
    if (lineOne && lineTwo) return `${lineOne} - ${lineTwo}`;
    return lineOne || lineTwo || offer.location || "";
  }

  function isNonTrialProgram(offer) {
    const key = String(offer?.sub_type || offer?.type || "").trim();
    return (
      key === "RentACoach_Generic" ||
      key === "ClubProgram_Generic" ||
      key === "CoachEducation"
    );
  }

  function parseCoord(value) {
    if (value == null) return NaN;
    const number = Number(String(value).trim().replace(",", "."));
    return Number.isFinite(number) ? number : NaN;
  }

  function isLat(value) {
    return Number.isFinite(value) && value >= -90 && value <= 90;
  }

  function isLng(value) {
    return Number.isFinite(value) && value >= -180 && value <= 180;
  }

  function latLngOf(offer) {
    const lat = parseCoord(offer.lat ?? offer.latitude);
    const lng = parseCoord(
      offer.lng ?? offer.lon ?? offer.long ?? offer.longitude,
    );
    if (isLat(lat) && isLng(lng)) return [lat, lng];
    return nestedLatLngOf(offer);
  }

  function nestedLatLngOf(offer) {
    const coords = getCoordinateSource(offer);
    if (!coords || typeof coords !== "object") return null;
    const lat = parseCoord(coords.lat ?? coords.latitude);
    const lng = parseCoord(
      coords.lng ?? coords.lon ?? coords.long ?? coords.longitude,
    );
    return isLat(lat) && isLng(lng) ? [lat, lng] : null;
  }

  function getCoordinateSource(offer) {
    return (
      offer.coords ||
      offer.coord ||
      offer.position ||
      offer.geo ||
      offer.gps ||
      offer.map ||
      offer.center ||
      offer.centerPoint ||
      offer.point ||
      offer.location
    );
  }

  function googleMapsHref(offer) {
    const latLng = latLngOf(offer);
    if (latLng) return googleCoordHref(latLng);
    const { addr } = nameAddr(offer);
    return googleQueryHref(addr || offer.location || "");
  }

  function googleCoordHref(latLng) {
    return googleQueryHref(`${latLng[0]},${latLng[1]}`);
  }

  function googleQueryHref(query) {
    return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
  }

  function formatAgeHtml(session) {
    const from = Number(session.ageFrom ?? "");
    const to = Number(session.ageTo ?? "");
    if (Number.isFinite(from) && Number.isFinite(to)) {
      return ageRangeHtml(from, to);
    }
    if (Number.isFinite(from)) return ageFromHtml(from);
    return "—";
  }

  function ageRangeHtml(from, to) {
    return `${esc(`${from} - ${to}`)} ${i18nSpan("offersDialog.labels.yearsOld", "Jährige")}`;
  }

  function ageFromHtml(from) {
    return `${esc(`${from}+`)} ${i18nSpan("offersDialog.labels.yearsOld", "Jährige")}`;
  }

  function formatTime(session) {
    const from = String(session.timeFrom || "").trim();
    const to = String(session.timeTo || "").trim();
    return from && to ? `${from} - ${to}` : from || to || "—";
  }

  function formatPrice(session) {
    return Number.isFinite(+session.price)
      ? `${(+session.price).toFixed(2)}€`
      : session.priceText || "—";
  }

  function formatDateDE(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return String(value);
    return date.toLocaleDateString("de-DE", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    });
  }

  function formatRangeDE(from, to) {
    if (!from && !to) return "";
    if (from && to) return `${formatDateDE(from)} - ${formatDateDE(to)}`;
    return from ? formatDateDE(from) : formatDateDE(to);
  }

  function getHolidayTitle(offer, session) {
    const label = getHolidayLabel(offer, session);
    const range = formatRangeDE(
      getHolidayFrom(offer, session),
      getHolidayTo(offer, session),
    );
    const rangePart = range ? ` (${range})` : "";
    return (label + rangePart).trim() || range;
  }

  function getHolidayLabel(offer, session) {
    return (
      session.holidayWeekLabel ||
      session.holidayLabel ||
      session.holidayWeek ||
      offer.holidayWeekLabel ||
      offer.holidayLabel ||
      offer.holidayWeek ||
      offer.holiday_name ||
      ""
    );
  }

  function getHolidayFrom(offer, session) {
    return (
      session.dateFrom ||
      session.holidayDateFrom ||
      session.holidayFrom ||
      offer.dateFrom ||
      offer.holidayDateFrom ||
      offer.holidayFrom ||
      ""
    );
  }

  function getHolidayTo(offer, session) {
    return (
      session.dateTo ||
      session.holidayDateTo ||
      session.holidayTo ||
      offer.dateTo ||
      offer.holidayDateTo ||
      offer.holidayTo ||
      ""
    );
  }

  function buildHolidayQuery(offer) {
    const params = buildHolidayParams(offer || {});
    return params.length ? `&${params.join("&")}` : "";
  }

  function buildHolidayParams(offer) {
    const params = [];
    addHolidayParam(params, "holidayLabel", getHolidayQueryLabel(offer));
    addHolidayParam(params, "holidayFrom", getHolidayQueryFrom(offer));
    addHolidayParam(params, "holidayTo", getHolidayQueryTo(offer));
    return params;
  }

  function addHolidayParam(params, key, value) {
    if (value) params.push(`${key}=${encodeURIComponent(value)}`);
  }

  function getHolidayQueryLabel(offer) {
    return (
      offer.holidayWeekName ||
      offer.holidayLabel ||
      offer.holidayWeek ||
      offer.holiday_name ||
      offer.holidayName ||
      offer.holiday ||
      ""
    );
  }

  function getHolidayQueryFrom(offer) {
    return (
      offer.holidayDateFrom ||
      offer.holidayFrom ||
      offer.dateFrom ||
      offer.startDate ||
      offer.start ||
      ""
    );
  }

  function getHolidayQueryTo(offer) {
    return (
      offer.holidayDateTo ||
      offer.holidayTo ||
      offer.dateTo ||
      offer.endDate ||
      offer.end ||
      ""
    );
  }

  function bookHref(base, session, offer) {
    const id = session && session._id ? String(session._id) : "";
    if (!id) return "#";
    const cleanBase = normalizeBookBase(base);
    return buildBookUrl(cleanBase, id, session, offer);
  }

  function normalizeBookBase(base) {
    return base && base.trim()
      ? base.trim().replace(/\/$/, "")
      : "http://localhost:3000";
  }

  function buildBookUrl(base, id, session, offer) {
    let url = `${base}/book?offerId=${encodeURIComponent(id)}&embed=1`;
    if (isHolidayOffer(offer)) url += buildHolidayQuery(offer || {});
    return `${url}${buildPreviewQuery(session, offer)}`;
  }

  function buildPreviewQuery(session, offer) {
    const params = new URLSearchParams();
    setPreviewParam(params, "previewHeading", getPreviewHeading(offer));
    setPreviewParam(params, "previewTitle", getPreviewTitle(session, offer));
    setPreviewParam(params, "previewMeta", getPreviewMeta(session, offer));
    const query = params.toString();
    return query ? `&${query}` : "";
  }

  function setPreviewParam(params, key, value) {
    const text = stripHtml(value).trim();
    if (text) params.set(key, text);
  }

  function getPreviewHeading(offer) {
    if (isWeeklyOffer(offer)) return "Anmeldung Schnuppertraining";
    if (isHolidayOffer(offer)) return "Anmeldung Ferienprogramm";
    return "Anfrage";
  }

  function getPreviewTitle(session, offer) {
    if (isHolidayOffer(offer)) return getHolidayTitle(offer || {}, session);
    return offer?.title || offer?.type || "";
  }

  function getPreviewMeta(session, offer) {
    const title = getPreviewScheduleTitle(session, offer);
    const time = formatTime(session);
    const price = getPreviewPrice(session, offer);
    return [title, time, price].filter(isUsefulPreviewPart).join(" · ");
  }

  function getPreviewScheduleTitle(session, offer) {
    const title = buildSessionTitle(session, offer, 1);
    return stripHtml(title);
  }

  function getPreviewPrice(session, offer) {
    const price = formatPrice(session);
    if (price && price !== "—") return price;
    return Number.isFinite(+offer?.price)
      ? `${(+offer.price).toFixed(2)}€`
      : "";
  }

  function isUsefulPreviewPart(value) {
    const text = String(value || "").trim();
    return text && text !== "—";
  }

  function stripHtml(value) {
    const text = String(value || "");
    return decodeHtml(text.replace(/<[^>]*>/g, ""));
  }

  function decodeHtml(value) {
    const element = document.createElement("textarea");
    element.innerHTML = value;
    return element.value;
  }

  function getCoachFull(offer) {
    return (
      offer.coachName ||
      [offer.coachFirst, offer.coachLast].filter(Boolean).join(" ") ||
      offer.coach ||
      ""
    ).trim();
  }

  function splitName(full) {
    const parts = String(full).trim().split(/\s+/).filter(Boolean);
    if (!parts.length) return { first: "—", last: "" };
    if (parts.length === 1) return { first: parts[0], last: "" };
    return { first: parts[0], last: parts.slice(1).join(" ") };
  }

  function getCoachFirst(offer) {
    return splitName(getCoachFull(offer)).first;
  }

  function getCoachLast(offer) {
    return splitName(getCoachFull(offer)).last;
  }

  function normalizeCoachSrc(src) {
    if (!src) return "";
    if (/^https?:\/\//i.test(src)) return src;
    if (src.startsWith("/api/uploads/coach/")) return withNextBase(src);
    if (/^\/?uploads\/coach\//i.test(src)) {
      return withNextBase(src.startsWith("/") ? `/api${src}` : `/api/${src}`);
    }
    if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src)) {
      return withNextBase(`/api/uploads/coach/${src}`);
    }
    return src;
  }

  function withNextBase(path) {
    const next = getNextBase();
    return next ? `${next}${path}` : path;
  }

  function getCoachAvatar(offer) {
    const direct = getDirectCoachAvatar(offer) || getNestedCoachAvatar(offer);
    return normalizeCoachSrc(direct || getRoot()?.dataset?.coachph || "");
  }

  function getDirectCoachAvatar(offer) {
    return (
      offer.coachImage ||
      offer.coachPhoto ||
      offer.coachAvatar ||
      offer.coachPic ||
      offer.coachImg ||
      offer.coach_image ||
      offer.coach_photo ||
      offer.coach_avatar ||
      offer.coach_pic ||
      offer.coach_img
    );
  }

  function getNestedCoachAvatar(offer) {
    for (const path of COACH_PATHS) {
      const value = pick(offer, path);
      if (value) return value;
    }
    return "";
  }

  function buildSessionTitle(session, offer, count) {
    if (isHolidayOffer(offer)) {
      return esc(getHolidayTitle(offer || {}, session) || "—");
    }
    const title = formatScheduleTitle(session, offer, count);
    if (isWeeklyOffer(offer) && title !== "—") return regularCourseTitle(title);
    return esc(title);
  }

  function regularCourseTitle(title) {
    return `${i18nSpan("offersDialog.labels.regularCourseTime", "Reguläre Kurszeit")}: ${esc(title)}`;
  }

  function buildCoachHtml(session) {
    const first = getCoachFirst(session);
    const last = getCoachLast(session);
    const avatar = getCoachAvatar(session);
    return `<div class="ks-session__coach">${avatarHtml(avatar, first, last)}${coachNameHtml(first, last)}</div>`;
  }

  function avatarHtml(avatar, first, last) {
    if (!avatar) return "";
    return `<img class="ks-coach__avatar" src="${esc(avatar)}" alt="${esc(`${first} ${last}`.trim())}">`;
  }

  function coachNameHtml(first, last) {
    return `<div class="ks-coach__name"><span class="ks-coach__first">${esc(first)}</span><span class="ks-coach__last">${esc(last)}</span></div>`;
  }

  function buildSessionInfoHtml(title, time, ageHtml) {
    return `<div class="ks-session__left"><div class="ks-session__row"><strong>${title}</strong></div><div class="ks-session__row">${esc(time)}</div><div class="ks-session__row">${ageHtml}</div></div>`;
  }

  function buildPowertrainingSession(session, index, offer, count) {
    const title = buildSessionTitle(session, offer, count);
    const info = buildSessionInfoHtml(
      title,
      formatTime(session),
      formatAgeHtml(session),
    );
    return `<div class="ks-session ks-session--selectable" data-session-index="${index}">${info}${buildCoachHtml(session)}${priceOnlyHtml(session)}</div>`;
  }

  function priceOnlyHtml(session) {
    return `<div class="ks-session__actions"><span class="ks-session__price">${esc(formatPrice(session))}</span></div>`;
  }

  function buildBookableSession(session, offer, count, nextBase) {
    const title = buildSessionTitle(session, offer, count);
    const info = buildSessionInfoHtml(
      title,
      formatTime(session),
      formatAgeHtml(session),
    );
    const href = bookHref(nextBase, session, offer);
    return `<div class="ks-session ks-session--selectable" data-book-href="${esc(href)}">${info}${buildCoachHtml(session)}${actionsHtml(href, session)}</div>`;
  }

  function actionsHtml(href, session) {
    return `<div class="ks-session__actions"><span class="ks-session__price">${esc(formatPrice(session))}</span><a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">${i18nSpan("offersDialog.actions.continue", "Weiter")}</a></div>`;
  }

  function buildSessionsHtml(nextBase, sessions, offer, isPowertraining) {
    const list = Array.isArray(sessions) ? sessions : [];
    return list
      .map((session, index) =>
        buildSessionByType(
          session,
          index,
          offer,
          list,
          nextBase,
          isPowertraining,
        ),
      )
      .join("");
  }

  function buildSessionByType(
    session,
    index,
    offer,
    list,
    nextBase,
    isPowertraining,
  ) {
    if (isPowertraining) {
      return buildPowertrainingSession(session, index, offer, list.length);
    }
    return buildBookableSession(session, offer, list.length, nextBase);
  }

  function buildCloseButton(closeURL) {
    const closeIcon = getIcon("close.svg") || closeURL;
    if (closeIcon) return closeIconButton(closeIcon);
    return closeTextButton();
  }

  function closeIconButton(icon) {
    return `<button type="button" class="ks-offer-modal__close" data-offer-close aria-label="Schließen" ${i18nAttr("offersDialog.actions.close")}><img src="${esc(icon)}" alt="" aria-hidden="true" width="24" height="24"></button>`;
  }

  function closeTextButton() {
    return `<button type="button" class="ks-offer-modal__close" data-offer-close aria-label="Schließen" ${i18nAttr("offersDialog.actions.close")}>✕</button>`;
  }

  function buildNonTrialHtml(nextBase, offer) {
    const href = bookHref(nextBase, offer, offer);
    return `<div class="ks-session ks-session--simple ks-session--selectable" data-book-href="${esc(href)}"><div class="ks-session__info">${i18nSpan("offersDialog.messages.requestBasedProgram", "Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.")}</div>${singleActionHtml(href)}</div>`;
  }

  function singleActionHtml(href) {
    return `<div class="ks-session__actions"><a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">${i18nSpan("offersDialog.actions.continue", "Weiter")}</a></div>`;
  }

  function buildPowertrainingFooter() {
    return `<div class="ks-offer__footer"><button type="button" class="ks-btn ks-btn--dark ks-offer__continue" data-pt-continue>${i18nSpan("offersDialog.actions.continue", "Weiter")}</button></div>`;
  }

  function attachHandlers(modal, overlay, panel) {
    const handlers = {
      onPanel: handlePanelClick,
      onOverlay: close,
      onEsc: (event) => event.key === "Escape" && close(),
    };

    panel.addEventListener("click", handlers.onPanel);
    overlay.addEventListener("click", handlers.onOverlay);
    document.addEventListener("keydown", handlers.onEsc);
    modal.__ksHandlers = handlers;
  }

  function detachHandlers(modal) {
    const handlers = modal?.__ksHandlers;
    if (!handlers) return;
    getPanel(modal)?.removeEventListener("click", handlers.onPanel);
    getOverlay(modal)?.removeEventListener("click", handlers.onOverlay);
    document.removeEventListener("keydown", handlers.onEsc);
    modal.__ksHandlers = null;
  }

  function handlePanelClick(event) {
    if (event.target.closest("[data-offer-close]")) return close();
    if (LAST.isPowertraining) return handlePowertrainingClick(event);
    const book = event.target.closest("[data-book-href]");
    if (book) return openBookFromElement(event, book);
    event.stopPropagation();
  }

  function openBookFromElement(event, element) {
    event.preventDefault();
    const url =
      element.getAttribute("data-book-href") ||
      element.getAttribute("href") ||
      "";
    close();
    window.BookDialog.open(url);
  }

  function handlePowertrainingClick(event) {
    const row = event.target.closest(".ks-session--selectable");
    if (row && row.hasAttribute("data-session-index")) {
      return togglePowertrainingRow(row);
    }
    const button = event.target.closest("[data-pt-continue]");
    if (button) return continuePowertraining();
    event.stopPropagation();
  }

  function togglePowertrainingRow(row) {
    const index = Number(row.getAttribute("data-session-index"));
    if (Number.isNaN(index)) return;
    const selectedIndex = LAST.selected.indexOf(index);
    if (selectedIndex === -1) return selectPowertrainingRow(row, index);
    unselectPowertrainingRow(row, selectedIndex);
  }

  function selectPowertrainingRow(row, index) {
    LAST.selected.push(index);
    row.classList.add("ks-session--selected");
  }

  function unselectPowertrainingRow(row, index) {
    LAST.selected.splice(index, 1);
    row.classList.remove("ks-session--selected");
  }

  function continuePowertraining() {
    if (!canContinuePowertraining()) return;
    const url = buildPowertrainingUrl();
    if (!url) return;
    close();
    window.BookDialog.open(url);
  }

  function canContinuePowertraining() {
    return (
      Array.isArray(LAST.sessions) &&
      LAST.sessions.length &&
      LAST.selected.length
    );
  }

  function buildPowertrainingUrl() {
    const base = getPowertrainingBase();
    const id = getPowertrainingBaseId();
    if (!id) return "";
    const url = `${base}/book?offerId=${encodeURIComponent(id)}&embed=1`;
    return appendPowertrainingMeta(url);
  }

  function getPowertrainingBase() {
    return (LAST.nextBase || getNextBase() || "http://localhost:3000").replace(
      /\/$/,
      "",
    );
  }

  function getPowertrainingBaseId() {
    return (
      (LAST.offer && LAST.offer._id) ||
      (LAST.sessions[0] && LAST.sessions[0]._id) ||
      ""
    );
  }

  function appendPowertrainingMeta(url) {
    const query = buildHolidayQuery(LAST.offer || {});
    const preview = buildPowertrainingPreviewQuery();
    const meta = buildPowertrainingMeta();
    const metaQuery = buildPowertrainingMetaQuery(meta);
    return `${url}${query}${preview}${metaQuery}`;
  }

  function buildPowertrainingMetaQuery(meta) {
    if (!meta.length) return "";
    return `&ptmeta=${encodeURIComponent(JSON.stringify(meta))}`;
  }

  function buildPowertrainingPreviewQuery() {
    const session = getFirstSelectedPowertrainingSession();
    if (!session) return "";
    return buildPreviewQuery(session, LAST.offer || {});
  }

  function getFirstSelectedPowertrainingSession() {
    const index = LAST.selected[0];
    if (index == null) return null;
    return LAST.sessions[index] || null;
  }

  function buildPowertrainingMeta() {
    return LAST.selected.map(powertrainingMetaItem).filter(Boolean);
  }

  function powertrainingMetaItem(index) {
    const session = LAST.sessions[index];
    if (!session) return null;
    return {
      id: session._id || "",
      day: "",
      dateFrom: metaDateFrom(session),
      dateTo: metaDateTo(session),
      timeFrom: session.timeFrom || "",
      timeTo: session.timeTo || "",
      price: session.price,
    };
  }

  function metaDateFrom(session) {
    return (
      session.dateFrom || session.holidayDateFrom || session.holidayFrom || ""
    );
  }

  function metaDateTo(session) {
    return session.dateTo || session.holidayDateTo || session.holidayTo || "";
  }

  function setLastState(offer, sessions, opts, nextBase, isPowertraining) {
    LAST.offer = offer;
    LAST.sessions = sessions;
    LAST.opts = opts;
    LAST.isPowertraining = isPowertraining;
    LAST.selected = [];
    LAST.nextBase = nextBase;
  }

  function renderPanel(panel, html) {
    panel.innerHTML = html;
    translateDynamicContent(panel);
  }

  function buildDialogHtml(data) {
    return `${dialogHeadHtml(data)}<div class="ks-offer-dialog__body"><div class="ks-offer__sessions">${data.sessionsHtml}</div>${data.footerHtml}</div>`;
  }

  function dialogHeadHtml(data) {
    return `<div class="ks-offer-dialog__head"><div class="ks-offer-dialog__head-main"><h3 id="ksOfferTitle" class="ks-dir__m-title">${esc(data.name)}</h3><p class="ks-dir__m-addr">${esc(data.addr)}</p>${googleLinkHtml(data.googleHref, data.directionsIcon)}</div><div class="ks-offer-dialog__head-actions">${data.closeBtn}</div></div>`;
  }

  function googleLinkHtml(href, icon) {
    return `<p class="ks-offer__google"><a href="${esc(href)}" target="_blank" rel="noopener">${directionIconHtml(icon)}${i18nSpan("offersDialog.links.googleDirections", "Anfahrt mit Google")}</a></p>`;
  }

  function directionIconHtml(icon) {
    return icon
      ? `<img src="${esc(icon)}" alt="" aria-hidden="true" width="20" height="20">`
      : "";
  }

  function open(offer, sessions, opts = {}) {
    const modal = getModal();
    if (!modal || !offer) return;
    const overlay = getOverlay(modal);
    const panel = getPanel(modal);
    if (!overlay || !panel) return;
    openWithParts(modal, overlay, panel, offer, sessions, opts);
  }

  function openWithParts(modal, overlay, panel, offer, sessions, opts) {
    const data = prepareDialogData(offer, sessions, opts);
    setLastState(offer, data.list, opts, data.nextBase, data.isPowertraining);
    detachHandlers(modal);
    renderPanel(panel, buildDialogHtml(data));
    modal.hidden = false;
    lockBody();
    attachHandlers(modal, overlay, panel);
  }

  function prepareDialogData(offer, sessions, opts) {
    const list = sessions && sessions.length ? sessions : [offer];
    const nextBase =
      opts.nextBase || getRoot()?.dataset?.next || "http://localhost:3000";
    return buildDialogData(offer, list, opts, nextBase);
  }

  function buildDialogData(offer, list, opts, nextBase) {
    const { name, addr } = nameAddr(offer);
    const isPowertraining = isPowertrainingOffer(offer);
    return {
      name,
      addr,
      nextBase,
      list,
      isPowertraining,
      closeBtn: buildCloseButton(
        opts.closeIcon || getRoot()?.dataset?.closeIcon || "",
      ),
      googleHref: googleMapsHref(offer),
      directionsIcon: getIcon("directions.svg"),
      sessionsHtml: buildDialogSessions(offer, list, nextBase, isPowertraining),
      footerHtml: isPowertraining ? buildPowertrainingFooter() : "",
    };
  }

  function isPowertrainingOffer(offer) {
    return (
      String(offer.category || "").toLowerCase() === "holiday" &&
      String(offer.sub_type || "").toLowerCase() === "powertraining"
    );
  }

  function buildDialogSessions(offer, list, nextBase, isPowertraining) {
    if (isNonTrialProgram(offer)) return buildNonTrialHtml(nextBase, offer);
    return buildSessionsHtml(nextBase, list, offer, isPowertraining);
  }

  function close() {
    const modal = getModal();
    if (!modal) return;
    modal.hidden = true;
    detachHandlers(modal);
    unlockBody();
  }

  window.KSOffersDialog = { open, close, __last: LAST };
})();

// (function () {
//   "use strict";

//   const LOCK_ATTR = "data-ks-modal-lock";
//   const $ = (selector, root = document) => root.querySelector(selector);

//   const DAY_ALIASES = {
//     m: "Mo",
//     mo: "Mo",
//     montag: "Mo",
//     monday: "Mo",
//     mon: "Mo",
//     di: "Di",
//     dienstag: "Di",
//     tuesday: "Di",
//     tue: "Di",
//     mi: "Mi",
//     mittwoch: "Mi",
//     wednesday: "Mi",
//     wed: "Mi",
//     do: "Do",
//     donnerstag: "Do",
//     thursday: "Do",
//     thu: "Do",
//     fr: "Fr",
//     freitag: "Fr",
//     friday: "Fr",
//     fri: "Fr",
//     sa: "Sa",
//     samstag: "Sa",
//     saturday: "Sa",
//     sat: "Sa",
//     so: "So",
//     sonntag: "So",
//     sunday: "So",
//   };

//   const DAY_LONG = {
//     Mo: "Montag",
//     Di: "Dienstag",
//     Mi: "Mittwoch",
//     Do: "Donnerstag",
//     Fr: "Freitag",
//     Sa: "Samstag",
//     So: "Sonntag",
//   };

//   const COACH_PATHS = [
//     ["coach", "image"],
//     ["coach", "photo"],
//     ["coach", "avatar"],
//     ["provider", "coachImage"],
//     ["provider", "coach_image"],
//     ["owner", "avatarUrl"],
//     ["owner", "coachImage"],
//     ["creator", "avatarUrl"],
//     ["user", "avatarUrl"],
//   ];

//   const LAST = {
//     offer: null,
//     sessions: null,
//     opts: null,
//     isPowertraining: false,
//     selected: [],
//     nextBase: "",
//   };

//   function esc(value) {
//     return String(value ?? "").replace(/[&<>"']/g, replaceHtml);
//   }

//   function replaceHtml(match) {
//     return {
//       "&": "&amp;",
//       "<": "&lt;",
//       ">": "&gt;",
//       '"': "&quot;",
//       "'": "&#39;",
//     }[match];
//   }

//   function pick(object, path) {
//     return path.reduce(getPathValue, object) ?? null;
//   }

//   function getPathValue(current, key) {
//     return current && current[key] != null ? current[key] : null;
//   }

//   function getRoot() {
//     return document.getElementById("ksDir");
//   }

//   function getModal() {
//     return document.getElementById("ksOfferModal");
//   }

//   function getPanel(modal) {
//     return $(".ks-offer-modal__panel", modal);
//   }

//   function getOverlay(modal) {
//     return $(".ks-offer-modal__overlay", modal);
//   }

//   function getNextBase() {
//     const base = (getRoot()?.dataset?.next || "").trim();
//     return base ? base.replace(/\/+$/, "") : "";
//   }

//   function getIconBase() {
//     const base = getRoot()?.dataset?.dialogIconBase || "";
//     return base ? String(base).replace(/\/?$/, "/") : "";
//   }

//   function getIcon(name) {
//     const base = getIconBase();
//     return base ? `${base}${name}` : "";
//   }

//   function i18nSpan(key, fallback) {
//     return `<span data-i18n="${esc(key)}">${esc(fallback)}</span>`;
//   }

//   function i18nAttr(key) {
//     return `data-i18n="${esc(key)}" data-i18n-attr="aria-label"`;
//   }

//   function translateDynamicContent(root) {
//     document.dispatchEvent(
//       new CustomEvent("ks:i18n:content-added", { detail: { root } }),
//     );
//   }

//   function lockBody() {
//     if (!document.body.hasAttribute(LOCK_ATTR)) setBodyLock();
//     document.body.classList.add("ks-modal-open");
//   }

//   function setBodyLock() {
//     document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
//     document.body.style.overflow = "hidden";
//   }

//   function unlockBody() {
//     if (document.body.hasAttribute(LOCK_ATTR)) restoreBodyLock();
//     document.body.classList.remove("ks-modal-open");
//   }

//   function restoreBodyLock() {
//     document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
//     document.body.removeAttribute(LOCK_ATTR);
//   }

//   function normDay(value) {
//     return value
//       ? DAY_ALIASES[String(value).trim().toLowerCase()] || value
//       : "";
//   }

//   function dayLong(value) {
//     const code = normDay(value);
//     return code ? DAY_LONG[code] || code : "";
//   }

//   function isWeeklyOffer(offer) {
//     return String(offer?.category || "").toLowerCase() === "weekly";
//   }

//   function isHolidayOffer(offer) {
//     const category = String(offer?.category || "").toLowerCase();
//     return category === "holiday" || category === "holidayprograms";
//   }

//   function getOfferDay(session, offer, count) {
//     if (session?.day) return dayLong(session.day);
//     const sessionDay = Array.isArray(session?.days) ? session.days[0] : "";
//     if (sessionDay) return dayLong(sessionDay);
//     if (count === 1 && offer?.day) return dayLong(offer.day);
//     return getSingleOfferDay(offer, count);
//   }

//   function getSingleOfferDay(offer, count) {
//     const offerDay = Array.isArray(offer?.days) ? offer.days[0] : "";
//     return count === 1 && offerDay ? dayLong(offerDay) : "";
//   }

//   function formatScheduleTitle(session, offer, count) {
//     return getOfferDay(session, offer, count) || "—";
//   }

//   function nameAddr(offer) {
//     const name =
//       offer.clubName ||
//       offer.club ||
//       offer.provider ||
//       offer.title ||
//       offer.type ||
//       "Standort";
//     const lineOne = offer.address || offer.street || "";
//     const lineTwo = [offer.zip || offer.postalCode || "", offer.city || ""]
//       .filter(Boolean)
//       .join(", ");
//     return { name, addr: buildAddress(lineOne, lineTwo, offer) };
//   }

//   function buildAddress(lineOne, lineTwo, offer) {
//     if (lineOne && lineTwo) return `${lineOne} - ${lineTwo}`;
//     return lineOne || lineTwo || offer.location || "";
//   }

//   function isNonTrialProgram(offer) {
//     const key = String(offer?.sub_type || offer?.type || "").trim();
//     return (
//       key === "RentACoach_Generic" ||
//       key === "ClubProgram_Generic" ||
//       key === "CoachEducation"
//     );
//   }

//   function parseCoord(value) {
//     if (value == null) return NaN;
//     const number = Number(String(value).trim().replace(",", "."));
//     return Number.isFinite(number) ? number : NaN;
//   }

//   function isLat(value) {
//     return Number.isFinite(value) && value >= -90 && value <= 90;
//   }

//   function isLng(value) {
//     return Number.isFinite(value) && value >= -180 && value <= 180;
//   }

//   function latLngOf(offer) {
//     const lat = parseCoord(offer.lat ?? offer.latitude);
//     const lng = parseCoord(
//       offer.lng ?? offer.lon ?? offer.long ?? offer.longitude,
//     );
//     if (isLat(lat) && isLng(lng)) return [lat, lng];
//     return nestedLatLngOf(offer);
//   }

//   function nestedLatLngOf(offer) {
//     const coords = getCoordinateSource(offer);
//     if (!coords || typeof coords !== "object") return null;
//     const lat = parseCoord(coords.lat ?? coords.latitude);
//     const lng = parseCoord(
//       coords.lng ?? coords.lon ?? coords.long ?? coords.longitude,
//     );
//     return isLat(lat) && isLng(lng) ? [lat, lng] : null;
//   }

//   function getCoordinateSource(offer) {
//     return (
//       offer.coords ||
//       offer.coord ||
//       offer.position ||
//       offer.geo ||
//       offer.gps ||
//       offer.map ||
//       offer.center ||
//       offer.centerPoint ||
//       offer.point ||
//       offer.location
//     );
//   }

//   function googleMapsHref(offer) {
//     const latLng = latLngOf(offer);
//     if (latLng) return googleCoordHref(latLng);
//     const { addr } = nameAddr(offer);
//     return googleQueryHref(addr || offer.location || "");
//   }

//   function googleCoordHref(latLng) {
//     return googleQueryHref(`${latLng[0]},${latLng[1]}`);
//   }

//   function googleQueryHref(query) {
//     return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(query)}`;
//   }

//   function formatAgeHtml(session) {
//     const from = Number(session.ageFrom ?? "");
//     const to = Number(session.ageTo ?? "");
//     if (Number.isFinite(from) && Number.isFinite(to))
//       return ageRangeHtml(from, to);
//     if (Number.isFinite(from)) return ageFromHtml(from);
//     return "—";
//   }

//   function ageRangeHtml(from, to) {
//     return `${esc(`${from} - ${to}`)} ${i18nSpan("offersDialog.labels.yearsOld", "Jährige")}`;
//   }

//   function ageFromHtml(from) {
//     return `${esc(`${from}+`)} ${i18nSpan("offersDialog.labels.yearsOld", "Jährige")}`;
//   }

//   function formatTime(session) {
//     const from = String(session.timeFrom || "").trim();
//     const to = String(session.timeTo || "").trim();
//     return from && to ? `${from} - ${to}` : from || to || "—";
//   }

//   function formatPrice(session) {
//     return Number.isFinite(+session.price)
//       ? `${(+session.price).toFixed(2)}€`
//       : session.priceText || "—";
//   }

//   function formatDateDE(value) {
//     if (!value) return "";
//     const date = new Date(value);
//     if (Number.isNaN(date.getTime())) return String(value);
//     return date.toLocaleDateString("de-DE", {
//       day: "2-digit",
//       month: "2-digit",
//       year: "numeric",
//     });
//   }

//   function formatRangeDE(from, to) {
//     if (!from && !to) return "";
//     if (from && to) return `${formatDateDE(from)} - ${formatDateDE(to)}`;
//     return from ? formatDateDE(from) : formatDateDE(to);
//   }

//   function getHolidayTitle(offer, session) {
//     const label = getHolidayLabel(offer, session);
//     const range = formatRangeDE(
//       getHolidayFrom(offer, session),
//       getHolidayTo(offer, session),
//     );
//     const rangePart = range ? ` (${range})` : "";
//     return (label + rangePart).trim() || range;
//   }

//   function getHolidayLabel(offer, session) {
//     return (
//       session.holidayWeekLabel ||
//       session.holidayLabel ||
//       session.holidayWeek ||
//       offer.holidayWeekLabel ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       ""
//     );
//   }

//   function getHolidayFrom(offer, session) {
//     return (
//       session.dateFrom ||
//       session.holidayDateFrom ||
//       session.holidayFrom ||
//       offer.dateFrom ||
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       ""
//     );
//   }

//   function getHolidayTo(offer, session) {
//     return (
//       session.dateTo ||
//       session.holidayDateTo ||
//       session.holidayTo ||
//       offer.dateTo ||
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       ""
//     );
//   }

//   function buildHolidayQuery(offer) {
//     const params = buildHolidayParams(offer || {});
//     return params.length ? `&${params.join("&")}` : "";
//   }

//   function buildHolidayParams(offer) {
//     const params = [];
//     if (getHolidayQueryLabel(offer))
//       params.push(
//         `holidayLabel=${encodeURIComponent(getHolidayQueryLabel(offer))}`,
//       );
//     if (getHolidayQueryFrom(offer))
//       params.push(
//         `holidayFrom=${encodeURIComponent(getHolidayQueryFrom(offer))}`,
//       );
//     if (getHolidayQueryTo(offer))
//       params.push(`holidayTo=${encodeURIComponent(getHolidayQueryTo(offer))}`);
//     return params;
//   }

//   function getHolidayQueryLabel(offer) {
//     return (
//       offer.holidayWeekName ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       offer.holidayName ||
//       offer.holiday ||
//       ""
//     );
//   }

//   function getHolidayQueryFrom(offer) {
//     return (
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       offer.dateFrom ||
//       offer.startDate ||
//       offer.start ||
//       ""
//     );
//   }

//   function getHolidayQueryTo(offer) {
//     return (
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       offer.dateTo ||
//       offer.endDate ||
//       offer.end ||
//       ""
//     );
//   }

//   function bookHref(base, session, offer) {
//     const id = session && session._id ? String(session._id) : "";
//     if (!id) return "#";
//     const cleanBase =
//       base && base.trim()
//         ? base.trim().replace(/\/$/, "")
//         : "http://localhost:3000";
//     return buildBookUrl(cleanBase, id, offer);
//   }

//   function buildBookUrl(base, id, offer) {
//     let url = `${base}/book?offerId=${encodeURIComponent(id)}&embed=1`;
//     if (isHolidayOffer(offer)) url += buildHolidayQuery(offer || {});
//     return url;
//   }

//   function getCoachFull(offer) {
//     return (
//       offer.coachName ||
//       [offer.coachFirst, offer.coachLast].filter(Boolean).join(" ") ||
//       offer.coach ||
//       ""
//     ).trim();
//   }

//   function splitName(full) {
//     const parts = String(full).trim().split(/\s+/).filter(Boolean);
//     if (!parts.length) return { first: "—", last: "" };
//     if (parts.length === 1) return { first: parts[0], last: "" };
//     return { first: parts[0], last: parts.slice(1).join(" ") };
//   }

//   function getCoachFirst(offer) {
//     return splitName(getCoachFull(offer)).first;
//   }

//   function getCoachLast(offer) {
//     return splitName(getCoachFull(offer)).last;
//   }

//   function normalizeCoachSrc(src) {
//     if (!src) return "";
//     if (/^https?:\/\//i.test(src)) return src;
//     if (src.startsWith("/api/uploads/coach/")) return withNextBase(src);
//     if (/^\/?uploads\/coach\//i.test(src))
//       return withNextBase(src.startsWith("/") ? `/api${src}` : `/api/${src}`);
//     if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src))
//       return withNextBase(`/api/uploads/coach/${src}`);
//     return src;
//   }

//   function withNextBase(path) {
//     const next = getNextBase();
//     return next ? `${next}${path}` : path;
//   }

//   function getCoachAvatar(offer) {
//     const direct = getDirectCoachAvatar(offer) || getNestedCoachAvatar(offer);
//     return normalizeCoachSrc(direct || getRoot()?.dataset?.coachph || "");
//   }

//   function getDirectCoachAvatar(offer) {
//     return (
//       offer.coachImage ||
//       offer.coachPhoto ||
//       offer.coachAvatar ||
//       offer.coachPic ||
//       offer.coachImg ||
//       offer.coach_image ||
//       offer.coach_photo ||
//       offer.coach_avatar ||
//       offer.coach_pic ||
//       offer.coach_img
//     );
//   }

//   function getNestedCoachAvatar(offer) {
//     for (const path of COACH_PATHS) {
//       const value = pick(offer, path);
//       if (value) return value;
//     }

//     return "";
//   }

//   function buildSessionTitle(session, offer, count) {
//     if (isHolidayOffer(offer))
//       return esc(getHolidayTitle(offer || {}, session) || "—");
//     const title = formatScheduleTitle(session, offer, count);
//     if (isWeeklyOffer(offer) && title !== "—") return regularCourseTitle(title);
//     return esc(title);
//   }

//   function regularCourseTitle(title) {
//     return `${i18nSpan("offersDialog.labels.regularCourseTime", "Reguläre Kurszeit")}: ${esc(title)}`;
//   }

//   function buildCoachHtml(session) {
//     const first = getCoachFirst(session);
//     const last = getCoachLast(session);
//     const avatar = getCoachAvatar(session);
//     return `<div class="ks-session__coach">${avatarHtml(avatar, first, last)}${coachNameHtml(first, last)}</div>`;
//   }

//   function avatarHtml(avatar, first, last) {
//     if (!avatar) return "";
//     return `<img class="ks-coach__avatar" src="${esc(avatar)}" alt="${esc(`${first} ${last}`.trim())}">`;
//   }

//   function coachNameHtml(first, last) {
//     return `<div class="ks-coach__name"><span class="ks-coach__first">${esc(first)}</span><span class="ks-coach__last">${esc(last)}</span></div>`;
//   }

//   function buildSessionInfoHtml(title, time, ageHtml) {
//     return `<div class="ks-session__left"><div class="ks-session__row"><strong>${title}</strong></div><div class="ks-session__row">${esc(time)}</div><div class="ks-session__row">${ageHtml}</div></div>`;
//   }

//   function buildPowertrainingSession(session, index, offer, count) {
//     const title = buildSessionTitle(session, offer, count);
//     const info = buildSessionInfoHtml(
//       title,
//       formatTime(session),
//       formatAgeHtml(session),
//     );
//     return `<div class="ks-session ks-session--selectable" data-session-index="${index}">${info}${buildCoachHtml(session)}${priceOnlyHtml(session)}</div>`;
//   }

//   function priceOnlyHtml(session) {
//     return `<div class="ks-session__actions"><span class="ks-session__price">${esc(formatPrice(session))}</span></div>`;
//   }

//   function buildBookableSession(session, offer, count, nextBase) {
//     const title = buildSessionTitle(session, offer, count);
//     const info = buildSessionInfoHtml(
//       title,
//       formatTime(session),
//       formatAgeHtml(session),
//     );
//     const href = bookHref(nextBase, session, offer);
//     return `<div class="ks-session ks-session--selectable" data-book-href="${esc(href)}">${info}${buildCoachHtml(session)}${actionsHtml(href, session)}</div>`;
//   }

//   function actionsHtml(href, session) {
//     return `<div class="ks-session__actions"><span class="ks-session__price">${esc(formatPrice(session))}</span><a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">${i18nSpan("offersDialog.actions.continue", "Weiter")}</a></div>`;
//   }

//   function buildSessionsHtml(nextBase, sessions, offer, isPowertraining) {
//     const list = Array.isArray(sessions) ? sessions : [];
//     return list
//       .map((session, index) =>
//         buildSessionByType(
//           session,
//           index,
//           offer,
//           list,
//           nextBase,
//           isPowertraining,
//         ),
//       )
//       .join("");
//   }

//   function buildSessionByType(
//     session,
//     index,
//     offer,
//     list,
//     nextBase,
//     isPowertraining,
//   ) {
//     if (isPowertraining)
//       return buildPowertrainingSession(session, index, offer, list.length);
//     return buildBookableSession(session, offer, list.length, nextBase);
//   }

//   function buildCloseButton(closeURL) {
//     const closeIcon = getIcon("close.svg") || closeURL;
//     if (closeIcon) return closeIconButton(closeIcon);
//     return closeTextButton();
//   }

//   function closeIconButton(icon) {
//     return `<button type="button" class="ks-offer-modal__close" data-offer-close aria-label="Schließen" ${i18nAttr("offersDialog.actions.close")}><img src="${esc(icon)}" alt="" aria-hidden="true" width="24" height="24"></button>`;
//   }

//   function closeTextButton() {
//     return `<button type="button" class="ks-offer-modal__close" data-offer-close aria-label="Schließen" ${i18nAttr("offersDialog.actions.close")}>✕</button>`;
//   }

//   function buildNonTrialHtml(nextBase, offer) {
//     const href = bookHref(nextBase, offer, offer);
//     return `<div class="ks-session ks-session--simple ks-session--selectable" data-book-href="${esc(href)}"><div class="ks-session__info">${i18nSpan("offersDialog.messages.requestBasedProgram", "Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.")}</div>${singleActionHtml(href)}</div>`;
//   }

//   function singleActionHtml(href) {
//     return `<div class="ks-session__actions"><a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(href)}" data-book-href="${esc(href)}">${i18nSpan("offersDialog.actions.continue", "Weiter")}</a></div>`;
//   }

//   function buildPowertrainingFooter() {
//     return `<div class="ks-offer__footer"><button type="button" class="ks-btn ks-btn--dark ks-offer__continue" data-pt-continue>${i18nSpan("offersDialog.actions.continue", "Weiter")}</button></div>`;
//   }

//   function attachHandlers(modal, overlay, panel) {
//     const handlers = {
//       onPanel: handlePanelClick,
//       onOverlay: close,
//       onEsc: (event) => event.key === "Escape" && close(),
//     };

//     panel.addEventListener("click", handlers.onPanel);
//     overlay.addEventListener("click", handlers.onOverlay);
//     document.addEventListener("keydown", handlers.onEsc);
//     modal.__ksHandlers = handlers;
//   }

//   function detachHandlers(modal) {
//     const handlers = modal?.__ksHandlers;
//     if (!handlers) return;
//     getPanel(modal)?.removeEventListener("click", handlers.onPanel);
//     getOverlay(modal)?.removeEventListener("click", handlers.onOverlay);
//     document.removeEventListener("keydown", handlers.onEsc);
//     modal.__ksHandlers = null;
//   }

//   function handlePanelClick(event) {
//     if (event.target.closest("[data-offer-close]")) return close();
//     if (LAST.isPowertraining) return handlePowertrainingClick(event);
//     const book = event.target.closest("[data-book-href]");
//     if (book) return openBookFromElement(event, book);
//     event.stopPropagation();
//   }

//   function openBookFromElement(event, element) {
//     event.preventDefault();
//     const url =
//       element.getAttribute("data-book-href") ||
//       element.getAttribute("href") ||
//       "";
//     close();
//     window.BookDialog.open(url);
//   }

//   function handlePowertrainingClick(event) {
//     const row = event.target.closest(".ks-session--selectable");
//     if (row && row.hasAttribute("data-session-index"))
//       return togglePowertrainingRow(row);
//     const button = event.target.closest("[data-pt-continue]");
//     if (button) return continuePowertraining();
//     event.stopPropagation();
//   }

//   function togglePowertrainingRow(row) {
//     const index = Number(row.getAttribute("data-session-index"));
//     if (Number.isNaN(index)) return;
//     const selectedIndex = LAST.selected.indexOf(index);
//     if (selectedIndex === -1) return selectPowertrainingRow(row, index);
//     unselectPowertrainingRow(row, selectedIndex);
//   }

//   function selectPowertrainingRow(row, index) {
//     LAST.selected.push(index);
//     row.classList.add("ks-session--selected");
//   }

//   function unselectPowertrainingRow(row, index) {
//     LAST.selected.splice(index, 1);
//     row.classList.remove("ks-session--selected");
//   }

//   function continuePowertraining() {
//     if (!canContinuePowertraining()) return;
//     const url = buildPowertrainingUrl();
//     if (!url) return;
//     close();
//     window.BookDialog.open(url);
//   }

//   function canContinuePowertraining() {
//     return (
//       Array.isArray(LAST.sessions) &&
//       LAST.sessions.length &&
//       LAST.selected.length
//     );
//   }

//   function buildPowertrainingUrl() {
//     const base = (
//       LAST.nextBase ||
//       getNextBase() ||
//       "http://localhost:3000"
//     ).replace(/\/$/, "");
//     const id = getPowertrainingBaseId();
//     if (!id) return "";
//     return appendPowertrainingMeta(
//       `${base}/book?offerId=${encodeURIComponent(id)}&embed=1`,
//     );
//   }

//   function getPowertrainingBaseId() {
//     return (
//       (LAST.offer && LAST.offer._id) ||
//       (LAST.sessions[0] && LAST.sessions[0]._id) ||
//       ""
//     );
//   }

//   function appendPowertrainingMeta(url) {
//     const query = buildHolidayQuery(LAST.offer || {});
//     const meta = buildPowertrainingMeta();
//     return meta.length
//       ? `${url}${query}&ptmeta=${encodeURIComponent(JSON.stringify(meta))}`
//       : `${url}${query}`;
//   }

//   function buildPowertrainingMeta() {
//     return LAST.selected.map(powertrainingMetaItem).filter(Boolean);
//   }

//   function powertrainingMetaItem(index) {
//     const session = LAST.sessions[index];
//     if (!session) return null;
//     return {
//       id: session._id || "",
//       day: "",
//       dateFrom: metaDateFrom(session),
//       dateTo: metaDateTo(session),
//       timeFrom: session.timeFrom || "",
//       timeTo: session.timeTo || "",
//       price: session.price,
//     };
//   }

//   function metaDateFrom(session) {
//     return (
//       session.dateFrom || session.holidayDateFrom || session.holidayFrom || ""
//     );
//   }

//   function metaDateTo(session) {
//     return session.dateTo || session.holidayDateTo || session.holidayTo || "";
//   }

//   function setLastState(offer, sessions, opts, nextBase, isPowertraining) {
//     LAST.offer = offer;
//     LAST.sessions = sessions;
//     LAST.opts = opts;
//     LAST.isPowertraining = isPowertraining;
//     LAST.selected = [];
//     LAST.nextBase = nextBase;
//   }

//   function renderPanel(panel, html) {
//     panel.innerHTML = html;
//     translateDynamicContent(panel);
//   }

//   function buildDialogHtml(data) {
//     return `${dialogHeadHtml(data)}<div class="ks-offer-dialog__body"><div class="ks-offer__sessions">${data.sessionsHtml}</div>${data.footerHtml}</div>`;
//   }

//   function dialogHeadHtml(data) {
//     return `<div class="ks-offer-dialog__head"><div class="ks-offer-dialog__head-main"><h3 id="ksOfferTitle" class="ks-dir__m-title">${esc(data.name)}</h3><p class="ks-dir__m-addr">${esc(data.addr)}</p>${googleLinkHtml(data.googleHref, data.directionsIcon)}</div><div class="ks-offer-dialog__head-actions">${data.closeBtn}</div></div>`;
//   }

//   function googleLinkHtml(href, icon) {
//     return `<p class="ks-offer__google"><a href="${esc(href)}" target="_blank" rel="noopener">${directionIconHtml(icon)}${i18nSpan("offersDialog.links.googleDirections", "Anfahrt mit Google")}</a></p>`;
//   }

//   function directionIconHtml(icon) {
//     return icon
//       ? `<img src="${esc(icon)}" alt="" aria-hidden="true" width="20" height="20">`
//       : "";
//   }

//   function open(offer, sessions, opts = {}) {
//     const modal = getModal();
//     if (!modal || !offer) return;
//     const overlay = getOverlay(modal);
//     const panel = getPanel(modal);
//     if (!overlay || !panel) return;
//     openWithParts(modal, overlay, panel, offer, sessions, opts);
//   }

//   function openWithParts(modal, overlay, panel, offer, sessions, opts) {
//     const data = prepareDialogData(offer, sessions, opts);
//     setLastState(offer, data.list, opts, data.nextBase, data.isPowertraining);
//     detachHandlers(modal);
//     renderPanel(panel, buildDialogHtml(data));
//     modal.hidden = false;
//     lockBody();
//     attachHandlers(modal, overlay, panel);
//   }

//   function prepareDialogData(offer, sessions, opts) {
//     const list = sessions && sessions.length ? sessions : [offer];
//     const nextBase =
//       opts.nextBase || getRoot()?.dataset?.next || "http://localhost:3000";
//     return buildDialogData(offer, list, opts, nextBase);
//   }

//   function buildDialogData(offer, list, opts, nextBase) {
//     const { name, addr } = nameAddr(offer);
//     const isPowertraining = isPowertrainingOffer(offer);
//     return {
//       name,
//       addr,
//       nextBase,
//       list,
//       isPowertraining,
//       closeBtn: buildCloseButton(
//         opts.closeIcon || getRoot()?.dataset?.closeIcon || "",
//       ),
//       googleHref: googleMapsHref(offer),
//       directionsIcon: getIcon("directions.svg"),
//       sessionsHtml: buildDialogSessions(offer, list, nextBase, isPowertraining),
//       footerHtml: isPowertraining ? buildPowertrainingFooter() : "",
//     };
//   }

//   function isPowertrainingOffer(offer) {
//     return (
//       String(offer.category || "").toLowerCase() === "holiday" &&
//       String(offer.sub_type || "").toLowerCase() === "powertraining"
//     );
//   }

//   function buildDialogSessions(offer, list, nextBase, isPowertraining) {
//     if (isNonTrialProgram(offer)) return buildNonTrialHtml(nextBase, offer);
//     return buildSessionsHtml(nextBase, list, offer, isPowertraining);
//   }

//   function close() {
//     const modal = getModal();
//     if (!modal) return;
//     modal.hidden = true;
//     detachHandlers(modal);
//     unlockBody();
//   }

//   window.KSOffersDialog = { open, close, __last: LAST };
// })();

// (function () {
//   "use strict";

//   const $ = (selector, context = document) => context.querySelector(selector);

//   const esc = (value) =>
//     String(value ?? "").replace(
//       /[&<>"']/g,
//       (match) =>
//         ({
//           "&": "&amp;",
//           "<": "&lt;",
//           ">": "&gt;",
//           '"': "&quot;",
//           "'": "&#39;",
//         })[match],
//     );

//   const pick = (object, path) => {
//     try {
//       return (
//         path.reduce((current, key) => {
//           return current && current[key] != null ? current[key] : null;
//         }, object) ?? null
//       );
//     } catch {
//       return null;
//     }
//   };

//   const DAY_ALIASES = {
//     m: "Mo",
//     mo: "Mo",
//     montag: "Mo",
//     monday: "Mo",
//     mon: "Mo",
//     di: "Di",
//     dienstag: "Di",
//     tuesday: "Di",
//     tue: "Di",
//     mi: "Mi",
//     mittwoch: "Mi",
//     wednesday: "Mi",
//     wed: "Mi",
//     do: "Do",
//     donnerstag: "Do",
//     thursday: "Do",
//     thu: "Do",
//     fr: "Fr",
//     freitag: "Fr",
//     friday: "Fr",
//     fri: "Fr",
//     sa: "Sa",
//     samstag: "Sa",
//     saturday: "Sa",
//     sat: "Sa",
//     so: "So",
//     sonntag: "So",
//     sunday: "So",
//   };

//   const DAY_LONG = {
//     Mo: "Montag",
//     Di: "Dienstag",
//     Mi: "Mittwoch",
//     Do: "Donnerstag",
//     Fr: "Freitag",
//     Sa: "Samstag",
//     So: "Sonntag",
//   };

//   const LOCK_ATTR = "data-ks-modal-lock";

//   const LAST = {
//     offer: null,
//     sessions: null,
//     opts: null,
//     isPowertraining: false,
//     selected: [],
//     nextBase: "",
//   };

//   const normDay = (value) => {
//     return value
//       ? DAY_ALIASES[String(value).trim().toLowerCase()] || value
//       : "";
//   };

//   const dayLong = (value) => {
//     const code = normDay(value);
//     return code ? DAY_LONG[code] || code : "";
//   };

//   const isWeeklyOffer = (offer) =>
//     String(offer?.category || "").toLowerCase() === "weekly";

//   function getOfferDay(session, offer, sessionCount) {
//     if (session?.day) return dayLong(session.day);

//     const sessionDay = Array.isArray(session?.days) ? session.days[0] : "";
//     if (sessionDay) return dayLong(sessionDay);

//     if (sessionCount === 1 && offer?.day) return dayLong(offer.day);

//     const offerDay = Array.isArray(offer?.days) ? offer.days[0] : "";
//     return sessionCount === 1 && offerDay ? dayLong(offerDay) : "";
//   }

//   function formatScheduleTitle(session, offer, sessionCount) {
//     const day = getOfferDay(session, offer, sessionCount);
//     return day || "—";
//   }

//   function i18nSpan(key, fallback) {
//     return `<span data-i18n="${esc(key)}">${esc(fallback)}</span>`;
//   }

//   function i18nAttr(key) {
//     return `data-i18n="${esc(key)}" data-i18n-attr="aria-label"`;
//   }

//   function getRoot() {
//     return document.getElementById("ksDir");
//   }

//   function getNextBase() {
//     const root = getRoot();
//     const base = (root?.dataset?.next || "").trim();
//     return base ? base.replace(/\/+$/, "") : "";
//   }

//   function getDialogIconBase() {
//     const root = getRoot();
//     const base = root?.dataset?.dialogIconBase || root?.dataset?.dialogiconbase;
//     return base ? String(base).replace(/\/?$/, "/") : "";
//   }

//   function getDialogIcon(name) {
//     const base = getDialogIconBase();
//     return base ? `${base}${name}` : "";
//   }

//   function translateDynamicContent(root) {
//     document.dispatchEvent(
//       new CustomEvent("ks:i18n:content-added", {
//         detail: { root },
//       }),
//     );
//   }

//   function nameAddr(offer) {
//     const name =
//       offer.clubName ||
//       offer.club ||
//       offer.provider ||
//       offer.title ||
//       offer.type ||
//       "Standort";

//     const lineOne = offer.address || offer.street || "";
//     const lineTwo = [offer.zip || offer.postalCode || "", offer.city || ""]
//       .filter(Boolean)
//       .join(", ");

//     const address =
//       lineOne && lineTwo
//         ? `${lineOne} - ${lineTwo}`
//         : lineOne || lineTwo || offer.location || "";

//     return { name, addr: address };
//   }

//   function isNonTrialProgram(offer) {
//     const key = String(offer?.sub_type || offer?.type || "").trim();

//     return (
//       key === "RentACoach_Generic" ||
//       key === "ClubProgram_Generic" ||
//       key === "CoachEducation"
//     );
//   }

//   const isLat = (value) =>
//     Number.isFinite(value) && value >= -90 && value <= 90;

//   const isLng = (value) =>
//     Number.isFinite(value) && value >= -180 && value <= 180;

//   function parseCoord(value) {
//     if (value == null) return NaN;
//     const number = Number(String(value).trim().replace(",", "."));
//     return Number.isFinite(number) ? number : NaN;
//   }

//   function latLngOf(offer) {
//     const lat = parseCoord(offer.lat ?? offer.latitude);
//     const lng = parseCoord(
//       offer.lng ?? offer.lon ?? offer.long ?? offer.longitude,
//     );

//     if (isLat(lat) && isLng(lng)) return [lat, lng];

//     const coords =
//       offer.coords ||
//       offer.coord ||
//       offer.position ||
//       offer.geo ||
//       offer.gps ||
//       offer.map ||
//       offer.center ||
//       offer.centerPoint ||
//       offer.point ||
//       offer.location;

//     if (!coords || typeof coords !== "object") return null;

//     const nestedLat = parseCoord(coords.lat ?? coords.latitude);
//     const nestedLng = parseCoord(
//       coords.lng ?? coords.lon ?? coords.long ?? coords.longitude,
//     );

//     return isLat(nestedLat) && isLng(nestedLng) ? [nestedLat, nestedLng] : null;
//   }

//   function googleMapsHref(offer) {
//     const latLng = latLngOf(offer);

//     if (latLng) {
//       return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
//         `${latLng[0]},${latLng[1]}`,
//       )}`;
//     }

//     const { addr } = nameAddr(offer);

//     return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
//       addr || offer.location || "",
//     )}`;
//   }

//   function formatAgeHtml(session) {
//     const from = Number(session.ageFrom ?? "");
//     const to = Number(session.ageTo ?? "");

//     if (Number.isFinite(from) && Number.isFinite(to)) {
//       return `${esc(`${from} - ${to}`)} ${i18nSpan(
//         "offersDialog.labels.yearsOld",
//         "Jährige",
//       )}`;
//     }

//     if (Number.isFinite(from)) {
//       return `${esc(`${from}+`)} ${i18nSpan(
//         "offersDialog.labels.yearsOld",
//         "Jährige",
//       )}`;
//     }

//     return "—";
//   }

//   function formatTime(session) {
//     const from = String(session.timeFrom || "").trim();
//     const to = String(session.timeTo || "").trim();
//     return from && to ? `${from} - ${to}` : from || to || "—";
//   }

//   function formatPrice(session) {
//     return Number.isFinite(+session.price)
//       ? `${(+session.price).toFixed(2)}€`
//       : session.priceText || "—";
//   }

//   function formatDateDE(value) {
//     if (!value) return "";
//     const date = new Date(value);
//     if (isNaN(date.getTime())) return String(value);

//     return date.toLocaleDateString("de-DE", {
//       day: "2-digit",
//       month: "2-digit",
//       year: "numeric",
//     });
//   }

//   function formatRangeDE(from, to) {
//     if (!from && !to) return "";
//     if (from && to) return `${formatDateDE(from)} - ${formatDateDE(to)}`;
//     return from ? formatDateDE(from) : formatDateDE(to);
//   }

//   function getHolidayTitle(offer, session) {
//     const label =
//       session.holidayWeekLabel ||
//       session.holidayLabel ||
//       session.holidayWeek ||
//       offer.holidayWeekLabel ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       "";

//     const from =
//       session.dateFrom ||
//       session.holidayDateFrom ||
//       session.holidayFrom ||
//       offer.dateFrom ||
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       "";

//     const to =
//       session.dateTo ||
//       session.holidayDateTo ||
//       session.holidayTo ||
//       offer.dateTo ||
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       "";

//     const range = formatRangeDE(from, to);
//     const rangePart = range ? ` (${range})` : "";
//     return (label + rangePart).trim() || range;
//   }

//   function buildHolidayQuery(offer) {
//     if (!offer) return "";

//     const label =
//       offer.holidayWeekName ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       offer.holidayName ||
//       offer.holiday ||
//       "";

//     const from =
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       offer.dateFrom ||
//       offer.startDate ||
//       offer.start ||
//       "";

//     const to =
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       offer.dateTo ||
//       offer.endDate ||
//       offer.end ||
//       "";

//     const params = [];
//     if (label) params.push(`holidayLabel=${encodeURIComponent(label)}`);
//     if (from) params.push(`holidayFrom=${encodeURIComponent(from)}`);
//     if (to) params.push(`holidayTo=${encodeURIComponent(to)}`);

//     return params.length ? `&${params.join("&")}` : "";
//   }

//   function bookHref(base, session, offer) {
//     const id = session && session._id ? String(session._id) : "";
//     const cleanBase =
//       base && base.trim()
//         ? base.trim().replace(/\/$/, "")
//         : "http://localhost:3000";

//     if (!id) return "#";

//     let url = `${cleanBase}/book?offerId=${encodeURIComponent(id)}&embed=1`;
//     const category = String(offer?.category || "").toLowerCase();

//     if (category === "holiday" || category === "holidayprograms") {
//       url += buildHolidayQuery(offer || {});
//     }

//     return url;
//   }

//   function getCoachFull(offer) {
//     return (
//       offer.coachName ||
//       [offer.coachFirst, offer.coachLast].filter(Boolean).join(" ") ||
//       offer.coach ||
//       ""
//     ).trim();
//   }

//   function splitName(full) {
//     const parts = String(full).trim().split(/\s+/).filter(Boolean);

//     if (!parts.length) return { first: "—", last: "" };
//     if (parts.length === 1) return { first: parts[0], last: "" };

//     return {
//       first: parts[0],
//       last: parts.slice(1).join(" "),
//     };
//   }

//   const getCoachFirst = (offer) => splitName(getCoachFull(offer)).first;
//   const getCoachLast = (offer) => splitName(getCoachFull(offer)).last;

//   function normalizeCoachSrc(src) {
//     if (!src) return "";
//     if (/^https?:\/\//i.test(src)) return src;

//     const next = getNextBase();

//     if (src.startsWith("/api/uploads/coach/")) {
//       return next ? `${next}${src}` : src;
//     }

//     if (/^\/?uploads\/coach\//i.test(src)) {
//       const path = src.startsWith("/") ? `/api${src}` : `/api/${src}`;
//       return next ? `${next}${path}` : path;
//     }

//     if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src)) {
//       const path = `/api/uploads/coach/${src}`;
//       return next ? `${next}${path}` : path;
//     }

//     return src;
//   }

//   function getCoachAvatar(offer) {
//     const root = getRoot();
//     const fallback = root?.dataset?.coachph || "";

//     const direct =
//       offer.coachImage ||
//       offer.coachPhoto ||
//       offer.coachAvatar ||
//       offer.coachPic ||
//       offer.coachImg ||
//       offer.coach_image ||
//       offer.coach_photo ||
//       offer.coach_avatar ||
//       offer.coach_pic ||
//       offer.coach_img ||
//       pick(offer, ["coach", "image"]) ||
//       pick(offer, ["coach", "photo"]) ||
//       pick(offer, ["coach", "avatar"]) ||
//       pick(offer, ["provider", "coachImage"]) ||
//       pick(offer, ["provider", "coach_image"]) ||
//       pick(offer, ["owner", "avatarUrl"]) ||
//       pick(offer, ["owner", "coachImage"]) ||
//       pick(offer, ["creator", "avatarUrl"]) ||
//       pick(offer, ["user", "avatarUrl"]);

//     return normalizeCoachSrc(direct || fallback || "");
//   }

//   function lockBody() {
//     if (!document.body.hasAttribute(LOCK_ATTR)) {
//       document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
//       document.body.style.overflow = "hidden";
//     }

//     document.body.classList.add("ks-modal-open");
//   }

//   function unlockBody() {
//     if (document.body.hasAttribute(LOCK_ATTR)) {
//       document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
//       document.body.removeAttribute(LOCK_ATTR);
//     }

//     document.body.classList.remove("ks-modal-open");
//   }

//   function buildSessionTitle(session, offer, sessionCount, isHoliday) {
//     if (isHoliday) {
//       return esc(getHolidayTitle(offer || {}, session) || "—");
//     }

//     const title = formatScheduleTitle(session, offer, sessionCount);

//     if (isWeeklyOffer(offer) && title !== "—") {
//       return `${i18nSpan(
//         "offersDialog.labels.regularCourseTime",
//         "Reguläre Kurszeit",
//       )}: ${esc(title)}`;
//     }

//     return esc(title);
//   }

//   function buildCoachHtml(session) {
//     const firstName = getCoachFirst(session);
//     const lastName = getCoachLast(session);
//     const avatar = getCoachAvatar(session);

//     return `
//       <div class="ks-session__coach">
//         ${
//           avatar
//             ? `<img class="ks-coach__avatar" src="${esc(avatar)}" alt="${esc(
//                 `${firstName} ${lastName}`.trim(),
//               )}">`
//             : ""
//         }
//         <div class="ks-coach__name">
//           <span class="ks-coach__first">${esc(firstName)}</span>
//           <span class="ks-coach__last">${esc(lastName)}</span>
//         </div>
//       </div>`;
//   }

//   function buildSessionInfoHtml(title, time, ageHtml) {
//     return `
//       <div class="ks-session__left">
//         <div class="ks-session__row"><strong>${title}</strong></div>
//         <div class="ks-session__row">${esc(time)}</div>
//         <div class="ks-session__row">${ageHtml}</div>
//       </div>`;
//   }

//   function buildPowertrainingSession(
//     session,
//     index,
//     offer,
//     sessionCount,
//     isHoliday,
//   ) {
//     const title = buildSessionTitle(session, offer, sessionCount, isHoliday);
//     const info = buildSessionInfoHtml(
//       title,
//       formatTime(session),
//       formatAgeHtml(session),
//     );
//     const coach = buildCoachHtml(session);
//     const price = formatPrice(session);

//     return `
//       <div class="ks-session ks-session--selectable" data-session-index="${index}">
//         ${info}
//         ${coach}
//         <div class="ks-session__actions">
//           <span class="ks-session__price">${esc(price)}</span>
//         </div>
//       </div>`;
//   }

//   function buildBookableSession(
//     session,
//     offer,
//     sessionCount,
//     nextBase,
//     isHoliday,
//   ) {
//     const title = buildSessionTitle(session, offer, sessionCount, isHoliday);
//     const info = buildSessionInfoHtml(
//       title,
//       formatTime(session),
//       formatAgeHtml(session),
//     );
//     const coach = buildCoachHtml(session);
//     const href = bookHref(nextBase, session, offer);
//     const price = formatPrice(session);

//     return `
//       <div class="ks-session ks-session--selectable" data-book-href="${esc(href)}">
//         ${info}
//         ${coach}
//         <div class="ks-session__actions">
//           <span class="ks-session__price">${esc(price)}</span>
//           <a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(
//             href,
//           )}" data-book-href="${esc(href)}">
//             ${i18nSpan("offersDialog.actions.continue", "Weiter")}
//           </a>
//         </div>
//       </div>`;
//   }

//   function buildSessionsHtml(nextBase, sessions, offer, isPowertraining) {
//     const list = Array.isArray(sessions) ? sessions : [];
//     const category = String(offer?.category || "").toLowerCase();
//     const isHoliday = category === "holiday" || category === "holidayprograms";

//     return list
//       .map((session, index) => {
//         if (isPowertraining) {
//           return buildPowertrainingSession(
//             session,
//             index,
//             offer,
//             list.length,
//             isHoliday,
//           );
//         }

//         return buildBookableSession(
//           session,
//           offer,
//           list.length,
//           nextBase,
//           isHoliday,
//         );
//       })
//       .join("");
//   }

//   function buildCloseButton(closeURL) {
//     const closeIcon = getDialogIcon("close.svg") || closeURL;

//     if (closeIcon) {
//       return `
//         <button
//           type="button"
//           class="ks-dir__close ks-offer-dialog__close"
//           data-close
//           aria-label="Schließen"
//           ${i18nAttr("offersDialog.actions.close")}
//         >
//           <img src="${esc(closeIcon)}" alt="" aria-hidden="true" width="24" height="24">
//         </button>`;
//     }

//     return `
//       <button
//         type="button"
//         class="ks-dir__close ks-offer-dialog__close"
//         data-close
//         aria-label="Schließen"
//         ${i18nAttr("offersDialog.actions.close")}
//       >✕</button>`;
//   }

//   function buildNonTrialHtml(nextBase, offer) {
//     const href = bookHref(nextBase, offer, offer);

//     return `
//       <div class="ks-session ks-session--simple ks-session--selectable" data-book-href="${esc(
//         href,
//       )}">
//         <div class="ks-session__info">
//           ${i18nSpan(
//             "offersDialog.messages.requestBasedProgram",
//             "Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.",
//           )}
//         </div>
//         <div class="ks-session__actions">
//           <a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(
//             href,
//           )}" data-book-href="${esc(href)}">
//             ${i18nSpan("offersDialog.actions.continue", "Weiter")}
//           </a>
//         </div>
//       </div>`;
//   }

//   function buildPowertrainingFooter() {
//     return `
//       <div class="ks-offer__footer">
//         <button type="button" class="ks-btn ks-btn--dark ks-offer__continue" data-pt-continue>
//           ${i18nSpan("offersDialog.actions.continue", "Weiter")}
//         </button>
//       </div>`;
//   }

//   function attachHandlers(modal, overlay) {
//     const panel = $(".ks-dir__panel", modal);

//     const panelOnClick = (event) => {
//       if (event.target.closest("[data-close]")) {
//         close();
//         return;
//       }

//       if (LAST.isPowertraining) {
//         handlePowertrainingClick(event);
//         return;
//       }

//       const book = event.target.closest("[data-book-href]");

//       if (book) {
//         event.preventDefault();
//         openBookFromElement(book);
//         return;
//       }

//       event.stopPropagation();
//     };

//     const onOverlayClick = () => close();

//     const onEsc = (event) => {
//       if (event.key === "Escape") close();
//     };

//     panel && panel.addEventListener("click", panelOnClick);
//     overlay && overlay.addEventListener("click", onOverlayClick);
//     document.addEventListener("keydown", onEsc);

//     modal.__ksHandlers = { panelOnClick, onOverlayClick, onEsc };
//   }

//   function detachHandlers(modal) {
//     const handlers = modal && modal.__ksHandlers;
//     if (!handlers) return;

//     const panel = $(".ks-dir__panel", modal);
//     const overlay = $(".ks-dir__overlay", modal);

//     panel && panel.removeEventListener("click", handlers.panelOnClick);
//     overlay && overlay.removeEventListener("click", handlers.onOverlayClick);
//     document.removeEventListener("keydown", handlers.onEsc);

//     modal.__ksHandlers = null;
//   }

//   function openBookFromElement(element) {
//     const url =
//       element.getAttribute("data-book-href") ||
//       element.getAttribute("href") ||
//       "";

//     const root = getRoot();
//     const icon = root?.dataset?.closeIcon || root?.dataset?.closeicon || "";

//     close();
//     BookDialog.open(url, { closeIcon: icon });
//   }

//   function handlePowertrainingClick(event) {
//     const row = event.target.closest(".ks-session--selectable");

//     if (row && row.hasAttribute("data-session-index")) {
//       togglePowertrainingRow(row);
//       return;
//     }

//     const continueButton = event.target.closest("[data-pt-continue]");

//     if (continueButton) {
//       continuePowertraining();
//       return;
//     }

//     event.stopPropagation();
//   }

//   function togglePowertrainingRow(row) {
//     const index = Number(row.getAttribute("data-session-index"));
//     if (Number.isNaN(index)) return;

//     const selectedIndex = LAST.selected.indexOf(index);

//     if (selectedIndex === -1) {
//       LAST.selected.push(index);
//       row.classList.add("ks-session--selected");
//       return;
//     }

//     LAST.selected.splice(selectedIndex, 1);
//     row.classList.remove("ks-session--selected");
//   }

//   function continuePowertraining() {
//     if (
//       !Array.isArray(LAST.sessions) ||
//       !LAST.sessions.length ||
//       !LAST.selected.length
//     ) {
//       return;
//     }

//     const base = (LAST.nextBase || getNextBase() || "").replace(/\/$/, "");
//     const baseOfferId =
//       (LAST.offer && LAST.offer._id) ||
//       (LAST.sessions[0] && LAST.sessions[0]._id) ||
//       "";

//     if (!baseOfferId) return;

//     const meta = buildPowertrainingMeta();
//     let url = `${base || "http://localhost:3000"}/book?offerId=${encodeURIComponent(
//       baseOfferId,
//     )}&embed=1`;

//     const holidayQuery = buildHolidayQuery(LAST.offer || {});
//     if (holidayQuery) url += holidayQuery;

//     if (meta.length) {
//       try {
//         url += `&ptmeta=${encodeURIComponent(JSON.stringify(meta))}`;
//       } catch {}
//     }

//     const root = getRoot();
//     const icon = root?.dataset?.closeIcon || root?.dataset?.closeicon || "";

//     close();
//     BookDialog.open(url, { closeIcon: icon });
//   }

//   function buildPowertrainingMeta() {
//     const meta = [];

//     LAST.selected.forEach((index) => {
//       const session = LAST.sessions[index];
//       if (!session) return;

//       meta.push({
//         id: session._id || "",
//         day: "",
//         dateFrom:
//           session.dateFrom ||
//           session.holidayDateFrom ||
//           session.holidayFrom ||
//           "",
//         dateTo:
//           session.dateTo || session.holidayDateTo || session.holidayTo || "",
//         timeFrom: session.timeFrom || "",
//         timeTo: session.timeTo || "",
//         price: session.price,
//       });
//     });

//     return meta;
//   }

//   function open(offer, sessions, opts = {}) {
//     const modal = $("#ksOfferModal");
//     if (!modal || !offer) return;

//     let overlay = $(".ks-dir__overlay", modal);
//     let panel = $(".ks-dir__panel", modal);

//     if (!overlay) {
//       overlay = document.createElement("div");
//       overlay.className = "ks-dir__overlay";
//       modal.appendChild(overlay);
//     }

//     if (!panel) {
//       panel = document.createElement("div");
//       panel.className = "ks-dir__panel";
//       modal.appendChild(panel);
//     }

//     const root = getRoot();
//     const closeURL =
//       opts.closeIcon ||
//       root?.dataset?.closeIcon ||
//       root?.dataset?.closeicon ||
//       "";

//     const nextBase =
//       opts.nextBase || root?.dataset?.next || "http://localhost:3000";

//     const { name, addr } = nameAddr(offer);
//     const googleHref = googleMapsHref(offer);
//     const list = sessions && sessions.length ? sessions : [offer];
//     const category = String(offer.category || "").toLowerCase();

//     const isPowertraining =
//       category === "holiday" &&
//       String(offer.sub_type || "").toLowerCase() === "powertraining";

//     LAST.offer = offer;
//     LAST.sessions = list;
//     LAST.opts = opts;
//     LAST.isPowertraining = isPowertraining;
//     LAST.selected = [];
//     LAST.nextBase = nextBase;

//     const sessionsHtml = isNonTrialProgram(offer)
//       ? buildNonTrialHtml(nextBase, offer)
//       : buildSessionsHtml(nextBase, list, offer, isPowertraining);

//     const footerHtml = isPowertraining ? buildPowertrainingFooter() : "";
//     const closeBtn = buildCloseButton(closeURL);
//     const directionsIcon = getDialogIcon("directions.svg");

//     panel.innerHTML = `
//       <div class="ks-offer-dialog__head">
//         <div class="ks-offer-dialog__head-main">
//           <h3 id="ksOfferTitle" class="ks-dir__m-title">${esc(name)}</h3>
//           <p class="ks-dir__m-addr">${esc(addr)}</p>
//           <p class="ks-offer__google">
//             <a href="${esc(googleHref)}" target="_blank" rel="noopener">
//               ${
//                 directionsIcon
//                   ? `<img src="${esc(
//                       directionsIcon,
//                     )}" alt="" aria-hidden="true" width="20" height="20">`
//                   : ""
//               }
//               ${i18nSpan("offersDialog.links.googleDirections", "Anfahrt mit Google")}
//             </a>
//           </p>
//         </div>
//         <div class="ks-offer-dialog__head-actions">
//           ${closeBtn}
//         </div>
//       </div>

//       <div class="ks-offer-dialog__body">
//         <div class="ks-offer__sessions">${sessionsHtml}</div>
//         ${footerHtml}
//       </div>
//     `;

//     modal.hidden = false;
//     lockBody();
//     attachHandlers(modal, overlay);
//     translateDynamicContent(panel);
//   }

//   function close() {
//     const modal = $("#ksOfferModal");
//     if (!modal) return;

//     modal.hidden = true;
//     detachHandlers(modal);
//     unlockBody();
//   }

//   window.KSOffersDialog = { open, close, __last: LAST };
// })();

// (function () {
//   "use strict";

//   const $ = (sel, ctx = document) => ctx.querySelector(sel);
//   const esc = (s) =>
//     String(s).replace(
//       /[&<>"']/g,
//       (m) =>
//         ({
//           "&": "&amp;",
//           "<": "&lt;",
//           ">": "&gt;",
//           '"': "&quot;",
//           "'": "&#39;",
//         })[m],
//     );
//   const pick = (o, path) => {
//     try {
//       return (
//         path.reduce((a, k) => (a && a[k] != null ? a[k] : null), o) ?? null
//       );
//     } catch {
//       return null;
//     }
//   };

//   const DAY_ALIASES = {
//     m: "Mo",
//     mo: "Mo",
//     montag: "Mo",
//     monday: "Mo",
//     mon: "Mo",
//     di: "Di",
//     dienstag: "Di",
//     tuesday: "Di",
//     tue: "Di",
//     mi: "Mi",
//     mittwoch: "Mi",
//     wednesday: "Mi",
//     wed: "Mi",
//     do: "Do",
//     donnerstag: "Do",
//     thursday: "Do",
//     thu: "Do",
//     fr: "Fr",
//     freitag: "Fr",
//     friday: "Fr",
//     fri: "Fr",
//     sa: "Sa",
//     samstag: "Sa",
//     saturday: "Sa",
//     sat: "Sa",
//     so: "So",
//     sonntag: "So",
//     sunday: "So",
//   };
//   // const DAY_LONG = {
//   //   Mo: "Montag",
//   //   Di: "Dienstag",
//   //   Mi: "Mittwoch",
//   //   Do: "Donnerstag",
//   //   Fr: "Freitag",
//   //   Sa: "Samstag",
//   //   So: "Sonntag",
//   // };

//   // const normDay = (v) =>
//   //   v ? DAY_ALIASES[String(v).trim().toLowerCase()] || v : "";
//   // const dayLongPlural = (c) => (DAY_LONG[c] || c) + (c ? "s" : "");

//   const DAY_LONG = {
//     Mo: "Montag",
//     Di: "Dienstag",
//     Mi: "Mittwoch",
//     Do: "Donnerstag",
//     Fr: "Freitag",
//     Sa: "Samstag",
//     So: "Sonntag",
//   };

//   const normDay = (v) =>
//     v ? DAY_ALIASES[String(v).trim().toLowerCase()] || v : "";

//   const dayLong = (value) => {
//     const code = normDay(value);
//     return code ? DAY_LONG[code] || code : "";
//   };

//   // const getOfferDay = (session, offer) => {
//   //   if (session?.day) return dayLong(session.day);
//   //   if (offer?.day) return dayLong(offer.day);

//   //   const sessionDay = Array.isArray(session?.days) ? session.days[0] : "";
//   //   const offerDay = Array.isArray(offer?.days) ? offer.days[0] : "";
//   //   return dayLong(sessionDay || offerDay);
//   // };

//   // const isWeeklyOffer = (offer) =>
//   //   String(offer?.category || "").toLowerCase() === "weekly";

//   // const isIndividualOffer = (offer) =>
//   //   String(offer?.category || "").toLowerCase() === "individual";

//   // const formatScheduleTitle = (session, offer) => {
//   //   const day = getOfferDay(session, offer);
//   //   if (!day) return "—";
//   //   if (isWeeklyOffer(offer)) return `Jeden ${day}`;
//   //   if (isIndividualOffer(offer)) return day;
//   //   return day;
//   // };

//   const getOfferDay = (session, offer, sessionCount) => {
//     if (session?.day) return dayLong(session.day);

//     const sessionDay = Array.isArray(session?.days) ? session.days[0] : "";
//     if (sessionDay) return dayLong(sessionDay);

//     if (sessionCount === 1 && offer?.day) return dayLong(offer.day);

//     const offerDay = Array.isArray(offer?.days) ? offer.days[0] : "";
//     if (sessionCount === 1 && offerDay) return dayLong(offerDay);

//     return "";
//   };

//   const isWeeklyOffer = (offer) =>
//     String(offer?.category || "").toLowerCase() === "weekly";

//   const isIndividualOffer = (offer) =>
//     String(offer?.category || "").toLowerCase() === "individual";

//   // const formatScheduleTitle = (session, offer, sessionCount) => {
//   //   const day = getOfferDay(session, offer, sessionCount);
//   //   if (!day) return "—";
//   //   if (isWeeklyOffer(offer)) return `Jeden ${day}`;
//   //   if (isIndividualOffer(offer)) return day;
//   //   return day;
//   // };

//   const formatScheduleTitle = (session, offer, sessionCount) => {
//     const day = getOfferDay(session, offer, sessionCount);
//     if (!day) return "—";
//     return day;
//   };

//   const nameAddr = (o) => {
//     const name =
//       o.clubName || o.club || o.provider || o.title || o.type || "Standort";
//     const l1 = o.address || o.street || "";
//     const l2 = [o.zip || o.postalCode || "", o.city || ""]
//       .filter(Boolean)
//       .join(", ");
//     const addr = l1 && l2 ? `${l1} - ${l2}` : l1 || l2 || o.location || "";
//     return { name, addr };
//   };

//   function isNonTrialProgram(offer) {
//     if (!offer) return false;
//     const key = (offer.sub_type || offer.type || "").trim();
//     return (
//       key === "RentACoach_Generic" ||
//       key === "ClubProgram_Generic" ||
//       key === "CoachEducation"
//     );
//   }

//   const isLat = (n) => Number.isFinite(n) && n >= -90 && n <= 90;
//   const isLng = (n) => Number.isFinite(n) && n >= -180 && n <= 180;
//   const parseCoord = (v) => {
//     if (v == null) return NaN;
//     const n = Number(String(v).trim().replace(",", "."));
//     return Number.isFinite(n) ? n : NaN;
//   };

//   function latLngOf(o) {
//     const lat = parseCoord(o.lat ?? o.latitude);
//     const lng = parseCoord(o.lng ?? o.lon ?? o.long ?? o.longitude);
//     if (isLat(lat) && isLng(lng)) return [lat, lng];

//     const c =
//       o.coords ||
//       o.coord ||
//       o.position ||
//       o.geo ||
//       o.gps ||
//       o.map ||
//       o.center ||
//       o.centerPoint ||
//       o.point ||
//       o.location;

//     if (c && typeof c === "object") {
//       const la = parseCoord(c.lat ?? c.latitude);
//       const lo = parseCoord(c.lng ?? c.lon ?? c.long ?? c.longitude);
//       if (isLat(la) && isLng(lo)) return [la, lo];
//     }

//     return null;
//   }

//   const googleMapsHref = (o) => {
//     const ll = latLngOf(o);
//     if (ll) {
//       return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
//         ll[0] + "," + ll[1],
//       )}`;
//     }

//     const { addr } = nameAddr(o);
//     return `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(
//       addr || o.location || "",
//     )}`;
//   };

//   const formatAge = (s) => {
//     const f = Number(s.ageFrom ?? "");
//     const t = Number(s.ageTo ?? "");
//     if (Number.isFinite(f) && Number.isFinite(t)) return `${f} - ${t} Jährige`;
//     if (Number.isFinite(f)) return `${f}+ Jährige`;
//     return "—";
//   };

//   const formatTime = (s) => {
//     const a = String(s.timeFrom || "").trim();
//     const b = String(s.timeTo || "").trim();
//     return a && b ? `${a} - ${b}` : a || b || "—";
//   };

//   const formatPrice = (s) =>
//     Number.isFinite(+s.price)
//       ? `${(+s.price).toFixed(2)}€`
//       : s.priceText || "—";

//   function formatDateDE(v) {
//     if (!v) return "";
//     const d = new Date(v);
//     if (isNaN(d.getTime())) return String(v);
//     return d.toLocaleDateString("de-DE", {
//       day: "2-digit",
//       month: "2-digit",
//       year: "numeric",
//     });
//   }

//   function formatRangeDE(from, to) {
//     const hasFrom = !!from;
//     const hasTo = !!to;
//     if (!hasFrom && !hasTo) return "";
//     if (hasFrom && hasTo) return `${formatDateDE(from)} - ${formatDateDE(to)}`;
//     if (hasFrom) return formatDateDE(from);
//     return formatDateDE(to);
//   }

//   function getHolidayTitle(offer, session) {
//     const label =
//       session.holidayWeekLabel ||
//       session.holidayLabel ||
//       session.holidayWeek ||
//       offer.holidayWeekLabel ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       "";

//     const from =
//       session.dateFrom ||
//       session.holidayDateFrom ||
//       session.holidayFrom ||
//       offer.dateFrom ||
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       "";

//     const to =
//       session.dateTo ||
//       session.holidayDateTo ||
//       session.holidayTo ||
//       offer.dateTo ||
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       "";

//     const range = formatRangeDE(from, to);
//     const rangePart = range ? ` (${range})` : "";
//     const combined = (label + rangePart).trim();
//     return combined || range;
//   }

//   function buildHolidayQuery(offer) {
//     if (!offer) return "";

//     const label =
//       offer.holidayWeekName ||
//       offer.holidayLabel ||
//       offer.holidayWeek ||
//       offer.holiday_name ||
//       offer.holidayName ||
//       offer.holiday ||
//       "";

//     const from =
//       offer.holidayDateFrom ||
//       offer.holidayFrom ||
//       offer.dateFrom ||
//       offer.startDate ||
//       offer.start ||
//       "";

//     const to =
//       offer.holidayDateTo ||
//       offer.holidayTo ||
//       offer.dateTo ||
//       offer.endDate ||
//       offer.end ||
//       "";

//     const params = [];
//     if (label) params.push(`holidayLabel=${encodeURIComponent(label)}`);
//     if (from) params.push(`holidayFrom=${encodeURIComponent(from)}`);
//     if (to) params.push(`holidayTo=${encodeURIComponent(to)}`);

//     return params.length ? `&${params.join("&")}` : "";
//   }

//   const bookHref = (base, s, offer) => {
//     const id = s && s._id ? String(s._id) : "";
//     const b =
//       base && base.trim()
//         ? base.trim().replace(/\/$/, "")
//         : "http://localhost:3000";
//     if (!id) return "#";

//     let url = `${b}/book?offerId=${encodeURIComponent(id)}&embed=1`;

//     const cat = String(offer?.category || "").toLowerCase();
//     if (cat === "holiday" || cat === "holidayprograms") {
//       url += buildHolidayQuery(offer || {});
//     }

//     return url;
//   };

//   function getCoachFull(o) {
//     return (
//       o.coachName ||
//       [o.coachFirst, o.coachLast].filter(Boolean).join(" ") ||
//       o.coach ||
//       ""
//     ).trim();
//   }

//   function splitName(full) {
//     const parts = String(full).trim().split(/\s+/).filter(Boolean);
//     if (!parts.length) return { first: "—", last: "" };
//     if (parts.length === 1) return { first: parts[0], last: "" };
//     return { first: parts[0], last: parts.slice(1).join(" ") };
//   }

//   function getCoachFirst(o) {
//     return splitName(getCoachFull(o)).first;
//   }

//   function getCoachLast(o) {
//     return splitName(getCoachFull(o)).last;
//   }

//   function getNextBase() {
//     const root = document.getElementById("ksDir");
//     const base = (root?.dataset?.next || "").trim();
//     return base ? base.replace(/\/+$/, "") : "";
//   }

//   function getDialogIconBase() {
//     const root = document.getElementById("ksDir");
//     const base = root?.dataset?.dialogIconBase || root?.dataset?.dialogiconbase;
//     return base ? String(base).replace(/\/?$/, "/") : "";
//   }

//   function getDialogIcon(name) {
//     const base = getDialogIconBase();
//     return base ? `${base}${name}` : "";
//   }

//   function i18nText(key, fallback) {
//     return `<span data-i18n="${esc(key)}">${esc(fallback)}</span>`;
//   }

//   function i18nAttr(key) {
//     return `data-i18n="${esc(key)}" data-i18n-attr="aria-label"`;
//   }

//   function formatAgeHtml(session) {
//     const from = Number(session.ageFrom ?? "");
//     const to = Number(session.ageTo ?? "");

//     if (Number.isFinite(from) && Number.isFinite(to)) {
//       return `${esc(`${from} - ${to}`)} ${i18nText("offersDialog.labels.yearsOld", "Jährige")}`;
//     }

//     if (Number.isFinite(from)) {
//       return `${esc(`${from}+`)} ${i18nText("offersDialog.labels.yearsOld", "Jährige")}`;
//     }

//     return "—";
//   }

//   function translateDynamicDialog(panel) {
//     document.dispatchEvent(
//       new CustomEvent("ks:i18n:content-added", {
//         detail: { root: panel },
//       }),
//     );
//   }

//   function normalizeCoachSrc(src) {
//     if (!src) return "";
//     if (/^https?:\/\//i.test(src)) return src;

//     const next = getNextBase();

//     if (src.startsWith("/api/uploads/coach/")) {
//       return next ? `${next}${src}` : src;
//     }

//     if (/^\/?uploads\/coach\//i.test(src)) {
//       const p = src.startsWith("/") ? `/api${src}` : `/api/${src}`;
//       return next ? `${next}${p}` : p;
//     }

//     if (/^[\w.\-]+\.(png|jpe?g|webp|gif)$/i.test(src)) {
//       const p = `/api/uploads/coach/${src}`;
//       return next ? `${next}${p}` : p;
//     }

//     return src;
//   }

//   function getCoachAvatar(o) {
//     const root = document.getElementById("ksDir");
//     const ph = root?.dataset?.coachph || "";
//     const direct =
//       o.coachImage ||
//       o.coachPhoto ||
//       o.coachAvatar ||
//       o.coachPic ||
//       o.coachImg ||
//       o.coach_image ||
//       o.coach_photo ||
//       o.coach_avatar ||
//       o.coach_pic ||
//       o.coach_img ||
//       pick(o, ["coach", "image"]) ||
//       pick(o, ["coach", "photo"]) ||
//       pick(o, ["coach", "avatar"]) ||
//       pick(o, ["provider", "coachImage"]) ||
//       pick(o, ["provider", "coach_image"]) ||
//       pick(o, ["owner", "avatarUrl"]) ||
//       pick(o, ["owner", "coachImage"]) ||
//       pick(o, ["creator", "avatarUrl"]) ||
//       pick(o, ["user", "avatarUrl"]);

//     return normalizeCoachSrc(direct || ph || "");
//   }

//   const LOCK_ATTR = "data-ks-modal-lock";

//   const lockBody = () => {
//     if (!document.body.hasAttribute(LOCK_ATTR)) {
//       document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
//       document.body.style.overflow = "hidden";
//     }
//     document.body.classList.add("ks-modal-open");
//   };

//   const unlockBody = () => {
//     if (document.body.hasAttribute(LOCK_ATTR)) {
//       document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
//       document.body.removeAttribute(LOCK_ATTR);
//     }
//     document.body.classList.remove("ks-modal-open");
//   };

//   function buildSessionsHtml(nextBase, sessions, offer, isPowertraining) {
//     const list = Array.isArray(sessions) ? sessions : [];
//     const cat = String(offer?.category || "").toLowerCase();
//     const isHoliday = cat === "holiday" || cat === "holidayprograms";

//     return list
//       .map((s, idx) => {
//         // const dayCode = normDay(
//         //   Array.isArray(s.days) && s.days.length ? s.days[0] : "",
//         // );
//         // const weekdayShort = dayCode || "";
//         // const weekdayLong = weekdayShort ? dayLongPlural(weekdayShort) : "";

//         // const time = formatTime(s);

//         const scheduleTitle = formatScheduleTitle(s, offer, list.length);
//         const time = formatTime(s);
//         const age = formatAge(s);
//         const fName = getCoachFirst(s);
//         const lName = getCoachLast(s);
//         const img = getCoachAvatar(s);
//         const href = bookHref(nextBase, s, offer);
//         const price = formatPrice(s);

//         let topLine = "";
//         let middleLine = "";
//         const isPower = isPowertraining;

//         // if (isHoliday) {
//         //   const holidayTitle = getHolidayTitle(offer || {}, s);
//         //   topLine = holidayTitle || "—";
//         //   middleLine = time || "—";
//         // } else {
//         //   topLine = weekdayLong || "—";
//         //   middleLine = time || "—";
//         // }

//         if (isHoliday) {
//           const holidayTitle = getHolidayTitle(offer || {}, s);
//           topLine = holidayTitle || "—";
//           middleLine = time || "—";
//         } else if (isWeeklyOffer(offer)) {
//           topLine = scheduleTitle ? `Reguläre Kurszeit: ${scheduleTitle}` : "—";
//           middleLine = time || "—";
//         } else {
//           topLine = scheduleTitle || "—";
//           middleLine = time || "—";
//         }

//         // if (isHoliday) {
//         //   const holidayTitle = getHolidayTitle(offer || {}, s);
//         //   topLine = holidayTitle || "—";
//         //   middleLine = time || "—";
//         // } else {
//         //   topLine = scheduleTitle || "—";
//         //   middleLine = time || "—";
//         // }

//         if (isPower) {
//           return `
//           <div class="ks-session ks-session--selectable" data-session-index="${idx}">
//             <div class="ks-session__left">
//               <div class="ks-session__row"><strong>${esc(topLine)}</strong></div>
//               <div class="ks-session__row">${esc(middleLine)}</div>
//               <div class="ks-session__row">${esc(age)}</div>
//             </div>

//             <div class="ks-session__coach">
//               ${
//                 img
//                   ? `<img class="ks-coach__avatar" src="${esc(img)}" alt="${esc(
//                       fName,
//                     )} ${esc(lName)}">`
//                   : ""
//               }
//               <div class="ks-coach__name">
//                 <span class="ks-coach__first">${esc(fName)}</span>
//                 <span class="ks-coach__last">${esc(lName)}</span>
//               </div>
//             </div>

//             <div class="ks-session__actions">
//               <span class="ks-session__price">${esc(price)}</span>
//             </div>
//           </div>`;
//         }

//         const btnLabel = "Weiter";

//         return `
//         <div class="ks-session ks-session--selectable" data-book-href="${esc(
//           href,
//         )}">
//           <div class="ks-session__left">
//             <div class="ks-session__row"><strong>${esc(topLine)}</strong></div>
//             <div class="ks-session__row">${esc(middleLine)}</div>
//             <div class="ks-session__row">${esc(age)}</div>
//           </div>

//           <div class="ks-session__coach">
//             ${
//               img
//                 ? `<img class="ks-coach__avatar" src="${esc(img)}" alt="${esc(
//                     fName,
//                   )} ${esc(lName)}">`
//                 : ""
//             }
//             <div class="ks-coach__name">
//               <span class="ks-coach__first">${esc(fName)}</span>
//               <span class="ks-coach__last">${esc(lName)}</span>
//             </div>
//           </div>

//           <div class="ks-session__actions">
//             <a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(
//               href,
//             )}" data-book-href="${esc(href)}">${esc(btnLabel)}</a>
//             <span class="ks-session__price">${esc(price)}</span>
//           </div>
//         </div>`;
//       })
//       .join("");
//   }

//   const LAST = {
//     offer: null,
//     sessions: null,
//     opts: null,
//     isPowertraining: false,
//     selected: [],
//     nextBase: "",
//   };

//   function attachHandlers(modal, overlay) {
//     const panel = $(".ks-dir__panel", modal);

//     const panelOnClick = (e) => {
//       if (e.target.closest("[data-close]")) {
//         close();
//         return;
//       }

//       if (LAST.isPowertraining) {
//         const row = e.target.closest(".ks-session--selectable");
//         if (row && row.hasAttribute("data-session-index")) {
//           const idx = Number(row.getAttribute("data-session-index"));
//           if (!Number.isNaN(idx)) {
//             const i = LAST.selected.indexOf(idx);
//             if (i === -1) {
//               LAST.selected.push(idx);
//               row.classList.add("ks-session--selected");
//             } else {
//               LAST.selected.splice(i, 1);
//               row.classList.remove("ks-session--selected");
//             }
//           }
//           return;
//         }

//         const cont = e.target.closest("[data-pt-continue]");
//         if (cont) {
//           if (
//             !Array.isArray(LAST.sessions) ||
//             !LAST.sessions.length ||
//             !LAST.selected.length
//           ) {
//             return;
//           }

//           const base = (LAST.nextBase || getNextBase() || "").replace(
//             /\/$/,
//             "",
//           );
//           const baseOfferId =
//             (LAST.offer && LAST.offer._id) ||
//             (LAST.sessions[0] && LAST.sessions[0]._id) ||
//             "";

//           if (!baseOfferId) return;

//           const ptMeta = [];

//           LAST.selected.forEach((i) => {
//             const s = LAST.sessions[i];
//             if (!s) return;

//             ptMeta.push({
//               id: s._id || "",
//               day: "",
//               dateFrom: s.dateFrom || s.holidayDateFrom || s.holidayFrom || "",
//               dateTo: s.dateTo || s.holidayDateTo || s.holidayTo || "",
//               timeFrom: s.timeFrom || "",
//               timeTo: s.timeTo || "",
//               price: s.price,
//             });
//           });

//           let url = `${
//             base || "http://localhost:3000"
//           }/book?offerId=${encodeURIComponent(baseOfferId)}&embed=1`;

//           const holidayQuery = buildHolidayQuery(LAST.offer || {});
//           if (holidayQuery) {
//             url += holidayQuery;
//           }

//           if (ptMeta.length) {
//             try {
//               url += `&ptmeta=${encodeURIComponent(JSON.stringify(ptMeta))}`;
//             } catch {}
//           }

//           const root = $("#ksDir");
//           const icon =
//             root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
//           close();
//           BookDialog.open(url, { closeIcon: icon });
//           return;
//         }

//         e.stopPropagation();
//         return;
//       }

//       const book = e.target.closest("[data-book-href]");
//       if (book) {
//         e.preventDefault();
//         const url =
//           book.getAttribute("data-book-href") ||
//           book.getAttribute("href") ||
//           "";
//         const root = $("#ksDir");
//         const icon = root?.dataset?.closeIcon || root?.dataset?.closeicon || "";
//         close();
//         BookDialog.open(url, { closeIcon: icon });
//         return;
//       }

//       e.stopPropagation();
//     };

//     panel && panel.addEventListener("click", panelOnClick);

//     const onOverlayClick = () => close();
//     overlay && overlay.addEventListener("click", onOverlayClick);

//     const onEsc = (e) => {
//       if (e.key === "Escape") close();
//     };
//     document.addEventListener("keydown", onEsc);

//     modal.__ksHandlers = { panelOnClick, onOverlayClick, onEsc };
//   }

//   function detachHandlers(modal) {
//     const h = modal && modal.__ksHandlers;
//     if (!h) return;
//     const panel = $(".ks-dir__panel", modal);
//     const overlay = $(".ks-dir__overlay", modal);
//     panel && panel.removeEventListener("click", h.panelOnClick);
//     overlay && overlay.removeEventListener("click", h.onOverlayClick);
//     document.removeEventListener("keydown", h.onEsc);
//     modal.__ksHandlers = null;
//   }

//   function open(offer, sessions, opts = {}) {
//     const modal = $("#ksOfferModal");
//     if (!modal || !offer) return;

//     let overlay = $(".ks-dir__overlay", modal);
//     let panel = $(".ks-dir__panel", modal);

//     if (!overlay) {
//       overlay = document.createElement("div");
//       overlay.className = "ks-dir__overlay";
//       modal.appendChild(overlay);
//     }

//     if (!panel) {
//       panel = document.createElement("div");
//       panel.className = "ks-dir__panel";
//       modal.appendChild(panel);
//     }

//     const root = $("#ksDir");
//     const closeURL =
//       opts.closeIcon ||
//       root?.dataset?.closeIcon ||
//       root?.dataset?.closeicon ||
//       "";
//     const nextBase =
//       opts.nextBase || root?.dataset?.next || "http://localhost:3000";

//     const { name, addr } = nameAddr(offer);
//     const gHref = googleMapsHref(offer);
//     const list = sessions && sessions.length ? sessions : [offer];

//     const closeBtn = closeURL
//       ? `<button type="button" class="ks-dir__close" data-close aria-label="Schließen"><img src="${esc(
//           closeURL,
//         )}" alt="Schließen" width="14" height="14"></button>`
//       : `<button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>`;

//     const nonTrial = isNonTrialProgram(offer);

//     const cat = String(offer.category || "").toLowerCase();
//     const isPowertraining =
//       cat === "holiday" &&
//       String(offer.sub_type || "").toLowerCase() === "powertraining";

//     LAST.offer = offer;
//     LAST.sessions = list;
//     LAST.opts = opts;
//     LAST.isPowertraining = isPowertraining;
//     LAST.selected = [];
//     LAST.nextBase = nextBase;

//     const sublineHtml = "";
//     const btnLabel = "Weiter";

//     let sessionsHtml;
//     if (nonTrial) {
//       const href = bookHref(nextBase, offer, offer);
//       sessionsHtml = `
//         <div class="ks-session ks-session--simple ks-session--selectable" data-book-href="${esc(
//           href,
//         )}">
//           <div class="ks-session__info">
//             Dieses Programm ist anfragebasiert. Klicke auf „Weiter“.
//           </div>
//           <div class="ks-session__actions">
//             <a class="ks-btn ks-btn--dark ks-session__btn" href="${esc(
//               href,
//             )}" data-book-href="${esc(href)}">${esc(btnLabel)}</a>
//           </div>
//         </div>`;
//     } else {
//       sessionsHtml = buildSessionsHtml(nextBase, list, offer, isPowertraining);
//     }

//     const footerHtml = isPowertraining
//       ? `<div class="ks-offer__footer">
//            <button type="button" class="ks-btn ks-btn--dark ks-offer__continue" data-pt-continue>Weiter</button>
//          </div>`
//       : "";

//     panel.innerHTML = `
//       ${closeBtn}
//       <h3 class="ks-dir__m-title">${esc(name)}</h3>
//       ${sublineHtml}
//       <p class="ks-dir__m-addr">${esc(addr)}</p>
//       <p class="ks-offer__google">
//         <a href="${esc(gHref)}" target="_blank" rel="noopener">
//           Anfahrt mit Google
//         </a>
//       </p>
//       <div class="ks-offer__sessions">${sessionsHtml}</div>
//       ${footerHtml}
//     `;

//     modal.hidden = false;
//     lockBody();
//     attachHandlers(modal, overlay);
//   }

//   function close() {
//     const modal = $("#ksOfferModal");
//     if (!modal) return;
//     modal.hidden = true;
//     detachHandlers(modal);
//     unlockBody();
//   }

//   window.KSOffersDialog = { open, close, __last: LAST };
// })();
