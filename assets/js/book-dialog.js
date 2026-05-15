(function () {
  "use strict";

  const LOCK_ATTR = "data-ks-modal-lock";
  const $ = (selector, root = document) => root.querySelector(selector);

  const readyUrls = new Set();
  const readyCallbacks = new Map();

  function getRoot() {
    return document.getElementById("ksDir");
  }

  function getIconBase() {
    const base = getRoot()?.dataset?.dialogIconBase || "";
    return base ? String(base).replace(/\/?$/, "/") : "";
  }

  function getIcon(name) {
    const base = getIconBase();
    return base ? `${base}${name}` : "";
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

  function getModalParts() {
    const modal = $("#ksBookModal");
    if (!modal) return null;

    return {
      modal,
      overlay: $(".ks-book-modal__overlay", modal),
      panel: $(".ks-book-modal__panel", modal),
      frame: $(".ks-book-modal__frame", modal),
    };
  }

  function setIcon(button, iconName) {
    const icon = getIcon(iconName);
    if (!button || !icon) return;

    const image = button.querySelector("img");
    if (image) image.src = icon;
  }

  function prepareIcons(modal) {
    setIcon($(".ks-book-modal__close", modal), "close.svg");
    setIcon($(".ks-book-modal__back", modal), "back.svg");
  }

  function resetPanel(panel, frame) {
    if (panel) resetPanelState(panel);
    if (frame) frame.style.height = "";
  }

  function resetPanelState(panel) {
    panel.classList.remove("ks-book-modal__panel--ready");
    panel.style.maxHeight = "";
  }

  function clearFrame(frame) {
    if (!frame) return;
    frame.style.height = "";
    frame.removeAttribute("src");
  }

  function getViewportHeight() {
    return window.innerHeight || document.documentElement.clientHeight || 600;
  }

  function getMaxPanelHeight() {
    return Math.max(getViewportHeight() - 48, 460);
  }

  function getHeaderHeight(parts) {
    const header = $(".ks-book-dialog__head", parts.modal);
    return header ? Math.ceil(header.getBoundingClientRect().height) : 0;
  }

  function getFrameMaxHeight(parts) {
    const panelHeight = getMaxPanelHeight();
    const headerHeight = getHeaderHeight(parts);
    return Math.max(panelHeight - headerHeight, 320);
  }

  // function applyInitialFrameHeight(parts) {
  //   const panelHeight = getMaxPanelHeight();
  //   const frameHeight = getFrameMaxHeight(parts);

  //   parts.panel.style.maxHeight = `${panelHeight}px`;
  //   parts.frame.style.height = `${frameHeight}px`;
  //   parts.panel.classList.add("ks-book-modal__panel--ready");
  // }

  function applyInitialFrameHeight(parts) {
    const panelHeight = getMaxPanelHeight();
    const frameHeight = getFrameMaxHeight(parts);

    parts.panel.style.maxHeight = `${panelHeight}px`;
    parts.frame.style.height = `${frameHeight}px`;
    parts.panel.classList.add("ks-book-modal__panel--ready");
  }

  function applyFrameHeight(parts, height) {
    const panelHeight = getMaxPanelHeight();
    const frameMaxHeight = getFrameMaxHeight(parts);
    const frameHeight = Math.min(height, frameMaxHeight);

    parts.panel.style.maxHeight = `${panelHeight}px`;
    parts.frame.style.height = `${frameHeight}px`;
    parts.panel.classList.add("ks-book-modal__panel--ready");
  }

  function getCachedHeight() {
    try {
      return Number(window.localStorage.getItem("ksBookLastHeight") || "0");
    } catch {
      return 0;
    }
  }

  function saveHeight(height) {
    try {
      window.localStorage.setItem("ksBookLastHeight", String(height));
    } catch {}
  }

  function applyCachedHeight(parts) {
    const cached = getCachedHeight();

    if (!Number.isFinite(cached) || cached <= 0) {
      applyInitialFrameHeight(parts);
      return;
    }

    applyFrameHeight(parts, cached);
  }

  function reopenOffersDialog() {
    const dialog = window.KSOffersDialog;
    const last = dialog?.__last;

    if (!dialog || !last?.offer) return;
    dialog.open(last.offer, last.sessions, last.opts || {});
  }

  function removeHandlers(parts) {
    const handlers = parts.modal.__ksBookHandlers;
    if (!handlers) return;

    parts.overlay?.removeEventListener("click", handlers.onOverlay);
    parts.modal.removeEventListener("click", handlers.onClick);
    document.removeEventListener("keydown", handlers.onEsc);
    parts.modal.__ksBookHandlers = null;
  }

  function close() {
    const parts = getModalParts();
    if (!parts || parts.modal.hidden) return;

    parts.modal.hidden = true;
    resetPanel(parts.panel, parts.frame);
    clearFrame(parts.frame);
    updateHeader(parts, "");
    delete parts.modal.dataset.ksHeightApplied;
    removeHandlers(parts);
    unlockBody();
  }

  function handleClick(event) {
    if (event.target.closest("[data-book-back]")) {
      close();
      reopenOffersDialog();
      return;
    }

    if (event.target.closest("[data-book-close]")) close();
  }

  function bindHandlers(parts) {
    const handlers = {
      onOverlay: close,
      onClick: handleClick,
      onEsc: (event) => event.key === "Escape" && close(),
    };

    parts.overlay?.addEventListener("click", handlers.onOverlay);
    parts.modal.addEventListener("click", handlers.onClick);
    document.addEventListener("keydown", handlers.onEsc);
    parts.modal.__ksBookHandlers = handlers;
  }

  function normalizeDialogUrl(url) {
    try {
      return new URL(url || "", window.location.href).href;
    } catch {
      return String(url || "");
    }
  }

  function isReady(url) {
    return readyUrls.has(normalizeDialogUrl(url));
  }

  function runReadyCallbacks(url) {
    const key = normalizeDialogUrl(url);
    const callbacks = readyCallbacks.get(key) || [];
    readyCallbacks.delete(key);
    callbacks.forEach((callback) => callback());
  }

  function whenReady(url, callback) {
    const key = normalizeDialogUrl(url);
    if (readyUrls.has(key)) return callback();

    const callbacks = readyCallbacks.get(key) || [];
    callbacks.push(callback);
    readyCallbacks.set(key, callbacks);
    preload(url);
  }

  // function preload(url) {
  //   const parts = getModalParts();
  //   if (!parts || !parts.frame || !url) return;

  //   prepareIcons(parts.modal);
  //   updateHeader(parts, url);

  //   if (parts.frame.getAttribute("src") === url) return;

  //   parts.frame.style.height = "";
  //   parts.frame.setAttribute("src", url);
  // }

  function preload(url) {
    const parts = getModalParts();
    if (!parts || !parts.frame || !url) return;

    const nextUrl = normalizeDialogUrl(url);
    const currentUrl = normalizeDialogUrl(parts.frame.getAttribute("src"));

    prepareIcons(parts.modal);
    updateHeader(parts, url);

    if (currentUrl === nextUrl) return;

    readyUrls.delete(nextUrl);
    parts.frame.style.height = "";
    parts.frame.setAttribute("src", url);
  }

  function open(url) {
    const parts = getModalParts();
    if (!parts || !parts.frame || !parts.panel) return;

    removeHandlers(parts);
    resetPanel(parts.panel, parts.frame);
    delete parts.modal.dataset.ksHeightApplied;
    prepareIcons(parts.modal);
    updateHeader(parts, url || "");
    parts.modal.hidden = false;
    lockBody();
    applyCachedHeight(parts);

    const currentUrl = normalizeDialogUrl(parts.frame.getAttribute("src"));
    const nextUrl = normalizeDialogUrl(url);

    if (currentUrl !== nextUrl) {
      readyUrls.delete(nextUrl);
      parts.frame.setAttribute("src", url || "#");
    }

    // if (parts.frame.getAttribute("src") !== url) {
    //   parts.frame.setAttribute("src", url || "#");
    // }

    bindHandlers(parts);
  }

  function handleHeightMessage(data) {
    const parts = getModalParts();
    if (!parts || parts.modal.hidden) return;
    if (!parts.panel || !parts.frame) return;
    if (!parts.frame.src) return;

    const height = Number(data.height);
    if (!Number.isFinite(height) || height <= 0) return;

    applyFrameHeight(parts, height);
    saveHeight(height);
    parts.modal.dataset.ksHeightApplied = "1";
  }

  // function handleReadyMessage() {
  //   const parts = getModalParts();
  //   if (!parts || parts.modal.hidden) return;
  // }

  function handleReadyMessage(event) {
    const parts = getModalParts();
    if (!parts || !parts.frame) return;
    if (event?.source !== parts.frame.contentWindow) return;

    const url = parts.frame.getAttribute("src") || "";
    const key = normalizeDialogUrl(url);

    readyUrls.add(key);
    runReadyCallbacks(key);
  }

  function handleFrameMessage(event) {
    const data = event?.data;
    if (!data?.type) return;

    // if (data.type === "KS_BOOKING_READY") {
    //   handleReadyMessage();
    //   return;
    // }

    if (data.type === "KS_BOOKING_READY") {
      handleReadyMessage(event);
      return;
    }

    if (data.type === "KS_BOOKING_HEIGHT") {
      handleHeightMessage(data);
      return;
    }

    if (data.type === "KS_BOOKING_CLOSE") close();
    if (data.type === "KS_BOOKING_BACK") handleBackMessage();
  }

  function handleBackMessage() {
    close();
    reopenOffersDialog();
  }

  function getUrlParam(url, key) {
    try {
      return new URL(url, window.location.href).searchParams.get(key) || "";
    } catch {
      return "";
    }
  }

  function setText(element, value) {
    if (!element) return;
    element.textContent = value || "";
    element.hidden = !value;
  }

  function updateHeader(parts, url) {
    const heading = getUrlParam(url, "previewHeading");
    const title = getUrlParam(url, "previewTitle");
    const meta = getUrlParam(url, "previewMeta");

    setText($(".ks-book-modal__title", parts.modal), heading);
    setText($(".ks-book-modal__product", parts.modal), title);
    setText($(".ks-book-modal__meta", parts.modal), meta);
  }

  // window.BookDialog = { open, close, preload };
  window.BookDialog = { open, close, preload, isReady, whenReady };
  window.addEventListener("message", handleFrameMessage, false);
})();

// (function () {
//   "use strict";

//   const LOCK_ATTR = "data-ks-modal-lock";
//   const $ = (selector, root = document) => root.querySelector(selector);

//   function getRoot() {
//     return document.getElementById("ksDir");
//   }

//   function getIconBase() {
//     const base = getRoot()?.dataset?.dialogIconBase || "";
//     return base ? String(base).replace(/\/?$/, "/") : "";
//   }

//   function getIcon(name) {
//     const base = getIconBase();
//     return base ? `${base}${name}` : "";
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

//   function getModalParts() {
//     const modal = $("#ksBookModal");
//     if (!modal) return null;

//     return {
//       modal,
//       overlay: $(".ks-book-modal__overlay", modal),
//       panel: $(".ks-book-modal__panel", modal),
//       frame: $(".ks-book-modal__frame", modal),
//     };
//   }

//   function setIcon(button, iconName) {
//     const icon = getIcon(iconName);
//     if (!button || !icon) return;

//     const image = button.querySelector("img");
//     if (image) image.src = icon;
//   }

//   function prepareIcons(modal) {
//     setIcon($(".ks-book-modal__close", modal), "close.svg");
//     setIcon($(".ks-book-modal__back", modal), "back.svg");
//   }

//   function setLoadingState(parts, isLoading) {
//     if (!parts?.modal) return;
//     parts.modal.classList.toggle("ks-book-modal--loading", isLoading);
//   }

//   function resetPanel(panel, frame) {
//     if (panel) resetPanelState(panel);
//     if (frame) frame.style.height = "";
//   }

//   function resetPanelState(panel) {
//     panel.classList.remove("ks-book-modal__panel--ready");
//     panel.style.maxHeight = "";
//   }

//   function clearFrame(frame) {
//     if (!frame) return;
//     frame.style.height = "";
//     frame.removeAttribute("src");
//   }

//   function getViewportHeight() {
//     return window.innerHeight || document.documentElement.clientHeight || 600;
//   }

//   function getMaxPanelHeight() {
//     return Math.max(getViewportHeight() - 48, 460);
//   }

//   function getHeaderHeight(parts) {
//     const header = $(".ks-book-dialog__head", parts.modal);
//     return header ? Math.ceil(header.getBoundingClientRect().height) : 0;
//   }

//   function getFrameMaxHeight(parts) {
//     const panelHeight = getMaxPanelHeight();
//     const headerHeight = getHeaderHeight(parts);
//     return Math.max(panelHeight - headerHeight, 320);
//   }

//   function applyInitialFrameHeight(parts) {
//     const panelHeight = getMaxPanelHeight();
//     const frameHeight = getFrameMaxHeight(parts);

//     parts.panel.style.maxHeight = `${panelHeight}px`;
//     parts.frame.style.height = `${frameHeight}px`;
//     parts.panel.classList.add("ks-book-modal__panel--ready");
//   }

//   function applyFrameHeight(parts, height) {
//     const panelHeight = getMaxPanelHeight();
//     const frameMaxHeight = getFrameMaxHeight(parts);
//     const frameHeight = Math.min(height, frameMaxHeight);

//     parts.panel.style.maxHeight = `${panelHeight}px`;
//     parts.frame.style.height = `${frameHeight}px`;
//     parts.panel.classList.add("ks-book-modal__panel--ready");
//   }

//   function getCachedHeight() {
//     try {
//       return Number(window.localStorage.getItem("ksBookLastHeight") || "0");
//     } catch {
//       return 0;
//     }
//   }

//   function saveHeight(height) {
//     try {
//       window.localStorage.setItem("ksBookLastHeight", String(height));
//     } catch {}
//   }

//   function applyCachedHeight(parts) {
//     const cached = getCachedHeight();

//     if (!Number.isFinite(cached) || cached <= 0) {
//       applyInitialFrameHeight(parts);
//       return;
//     }

//     applyFrameHeight(parts, cached);
//   }

//   function replaceFrame(parts) {
//     if (!parts.frame) return parts;

//     const freshFrame = parts.frame.cloneNode(false);
//     freshFrame.removeAttribute("src");
//     freshFrame.style.height = "";

//     parts.frame.replaceWith(freshFrame);
//     parts.frame = freshFrame;

//     return parts;
//   }

//   function reopenOffersDialog() {
//     const dialog = window.KSOffersDialog;
//     const last = dialog?.__last;

//     if (!dialog || !last?.offer) return;
//     dialog.open(last.offer, last.sessions, last.opts || {});
//   }

//   function removeHandlers(parts) {
//     const handlers = parts.modal.__ksBookHandlers;
//     if (!handlers) return;

//     parts.overlay?.removeEventListener("click", handlers.onOverlay);
//     parts.modal.removeEventListener("click", handlers.onClick);
//     document.removeEventListener("keydown", handlers.onEsc);
//     parts.modal.__ksBookHandlers = null;
//   }

//   function close() {
//     const parts = getModalParts();
//     if (!parts || parts.modal.hidden) return;

//     parts.modal.hidden = true;
//     setLoadingState(parts, false);
//     resetPanel(parts.panel, parts.frame);
//     clearFrame(parts.frame);
//     updateHeader(parts, "");
//     delete parts.modal.dataset.ksHeightApplied;
//     removeHandlers(parts);
//     unlockBody();
//   }

//   function handleClick(event) {
//     if (event.target.closest("[data-book-back]")) {
//       close();
//       reopenOffersDialog();
//       return;
//     }

//     if (event.target.closest("[data-book-close]")) close();
//   }

//   function bindHandlers(parts) {
//     const handlers = {
//       onOverlay: close,
//       onClick: handleClick,
//       onEsc: (event) => event.key === "Escape" && close(),
//     };

//     parts.overlay?.addEventListener("click", handlers.onOverlay);
//     parts.modal.addEventListener("click", handlers.onClick);
//     document.addEventListener("keydown", handlers.onEsc);
//     parts.modal.__ksBookHandlers = handlers;
//   }

//   function open(url) {
//     let parts = getModalParts();
//     if (!parts || !parts.frame || !parts.panel) return;

//     removeHandlers(parts);
//     parts = replaceFrame(parts);
//     resetPanel(parts.panel, parts.frame);
//     delete parts.modal.dataset.ksHeightApplied;
//     prepareIcons(parts.modal);
//     updateHeader(parts, url || "");
//     parts.modal.hidden = false;
//     lockBody();
//     applyCachedHeight(parts);
//     // setLoadingState(parts, true);
//     parts.frame.src = url || "#";
//     bindHandlers(parts);
//   }

//   function handleHeightMessage(data) {
//     const parts = getModalParts();
//     if (!parts || parts.modal.hidden) return;
//     if (!parts.panel || !parts.frame) return;
//     if (!parts.frame.src) return;

//     const height = Number(data.height);
//     if (!Number.isFinite(height) || height <= 0) return;

//     applyFrameHeight(parts, height);
//     saveHeight(height);
//     parts.modal.dataset.ksHeightApplied = "1";
//   }

//   function handleReadyMessage() {
//     const parts = getModalParts();
//     if (!parts || parts.modal.hidden) return;
//   }

//   function handleFrameMessage(event) {
//     const data = event?.data;
//     if (!data?.type) return;

//     if (data.type === "KS_BOOKING_READY") {
//       handleReadyMessage();
//       return;
//     }

//     if (data.type === "KS_BOOKING_HEIGHT") {
//       handleHeightMessage(data);
//       return;
//     }

//     if (data.type === "KS_BOOKING_CLOSE") close();
//     if (data.type === "KS_BOOKING_BACK") handleBackMessage();
//   }

//   function handleBackMessage() {
//     close();
//     reopenOffersDialog();
//   }

//   function getUrlParam(url, key) {
//     try {
//       return new URL(url, window.location.href).searchParams.get(key) || "";
//     } catch {
//       return "";
//     }
//   }

//   function setText(element, value) {
//     if (!element) return;
//     element.textContent = value || "";
//     element.hidden = !value;
//   }

//   function updateHeader(parts, url) {
//     const heading = getUrlParam(url, "previewHeading");
//     const title = getUrlParam(url, "previewTitle");
//     const meta = getUrlParam(url, "previewMeta");

//     setText($(".ks-book-modal__title", parts.modal), heading);
//     setText($(".ks-book-modal__product", parts.modal), title);
//     setText($(".ks-book-modal__meta", parts.modal), meta);
//   }

//   window.BookDialog = { open, close };
//   window.addEventListener("message", handleFrameMessage, false);
// })();
