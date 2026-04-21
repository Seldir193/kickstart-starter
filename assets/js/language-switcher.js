// @ts-nocheck
(function () {
  "use strict";

  function qs(root, sel) {
    return (root || document).querySelector(sel);
  }

  function qsa(root, sel) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function normalizeLanguage(value, fallback) {
    var current = String(value || "").toLowerCase();
    if (current.indexOf("de") === 0) return "de";
    if (current.indexOf("en") === 0) return "en";
    if (current.indexOf("tr") === 0) return "tr";
    return fallback;
  }

  function normalizePath(path) {
    return String(path || "").replace(/\/+$/, "") || "/";
  }

  function getHeaderPathMap() {
    return {
      "/": "home",
      "/about": "about",
      "/jobs": "jobs",
      "/news": "news",
      "/franchise": "franchise",
      "/shop": "shop",
      "/contact": "contact",
    };
  }

  function getLinkPath(anchor) {
    try {
      return normalizePath(
        new URL(anchor.href, window.location.origin).pathname,
      );
    } catch (e) {
      return "/";
    }
  }

  function getTextKey(text) {
    var value = String(text || "")
      .trim()
      .toLowerCase();
    if (value === "home" || value === "start") return "home";
    if (value === "about" || value === "über uns") return "about";
    if (value === "programs" || value === "programme") return "programs";
    if (value === "jobs") return "jobs";
    if (value === "news") return "news";
    if (value === "franchise") return "franchise";
    if (value === "shop") return "shop";
    if (value === "contact" || value === "kontakt") return "contact";
    if (value === "iletişim") return "contact";
    return "";
  }

  function detectHeaderKey(anchor) {
    var item = anchor.closest("li");
    if (item && item.classList.contains("ks-programs-toggle")) {
      return "programs";
    }
    var map = getHeaderPathMap();
    var path = getLinkPath(anchor);
    if (map[path]) return map[path];
    return getTextKey(anchor.textContent);
  }

  function buildJsonUrl(base, language) {
    return (
      base.replace(/\/+$/, "") +
      "/" +
      language +
      "/header." +
      language +
      ".json"
    );
  }

  function createState(switcher) {
    return {
      isOpen: false,
      cache: {},
      switcher: switcher,
      trigger: qs(switcher, ".language-switcher__trigger"),
      label: qs(switcher, ".language-switcher__label"),
      menu: qs(switcher, ".language-switcher__menu"),
      items: qsa(switcher, ".language-switcher__item"),
      navLinks: qsa(document, ".main-nav .menu > li > a"),
      i18nNodes: qsa(document, "[data-i18n]"),
      i18nAttrNodes: qsa(document, "[data-i18n-attr][data-i18n]"),
      fallback: switcher.getAttribute("data-fallback-language") || "en",
      base: switcher.getAttribute("data-i18n-base") || "",
    };
  }

  function isValidState(state) {
    if (!state.trigger || !state.label) return false;
    if (!state.menu || !state.items.length) return false;
    return !!state.base;
  }

  function setNavKeys(state) {
    state.navLinks.forEach(function (link) {
      link.setAttribute("data-i18n-key", detectHeaderKey(link));
    });
  }

  function openMenu(state) {
    state.isOpen = true;
    state.menu.hidden = false;
    state.trigger.setAttribute("aria-expanded", "true");
  }

  function closeMenu(state) {
    state.isOpen = false;
    state.menu.hidden = true;
    state.trigger.setAttribute("aria-expanded", "false");
  }

  function toggleMenu(state) {
    if (state.isOpen) closeMenu(state);
    else openMenu(state);
  }

  function setItemState(item, language, data) {
    var itemLanguage = item.getAttribute("data-language") || "";
    var isActive = itemLanguage === language;
    item.classList.toggle("is-active", isActive);
    item.setAttribute("aria-checked", String(isActive));
    if (data && data.language && data.language[itemLanguage]) {
      item.textContent = data.language[itemLanguage];
    }
  }

  function setActiveLanguage(state, language, data) {
    state.items.forEach(function (item) {
      setItemState(item, language, data);
    });
    if (data && data.language && data.language[language]) {
      state.label.textContent = data.language[language];
    }
  }

  function setTriggerLabel(state, data) {
    if (data.language && data.language.label) {
      state.trigger.setAttribute("aria-label", data.language.label);
    }
  }

  function setDocumentLanguage(language) {
    document.documentElement.lang = language;
  }

  function translateNavLink(link, data) {
    var key = link.getAttribute("data-i18n-key") || "";
    if (!key || !data.nav || !data.nav[key]) return;
    link.textContent = data.nav[key];
  }

  function getNestedValue(data, key) {
    return String(key || "")
      .split(".")
      .reduce(function (result, part) {
        if (!result || typeof result !== "object") return null;
        if (!(part in result)) return null;
        return result[part];
      }, data);
  }

  function translateNodeText(node, data) {
    var key = node.getAttribute("data-i18n") || "";
    if (!key) return;
    if (node.hasAttribute("data-i18n-attr")) return;
    var value = getNestedValue(data, key);
    if (typeof value !== "string") return;
    node.textContent = value;
  }

  function translateNodeAttr(node, data) {
    var key = node.getAttribute("data-i18n") || "";
    var attr = node.getAttribute("data-i18n-attr") || "";
    if (!key || !attr) return;
    var value = getNestedValue(data, key);
    if (typeof value !== "string") return;
    node.setAttribute(attr, value);
  }

  function applyGenericTranslations(state, data) {
    state.i18nNodes.forEach(function (node) {
      translateNodeText(node, data);
    });
    state.i18nAttrNodes.forEach(function (node) {
      translateNodeAttr(node, data);
    });
  }

  function applyHeaderTranslations(state, language, data) {
    if (!data) return;
    setDocumentLanguage(language);
    setTriggerLabel(state, data);
    setActiveLanguage(state, language, data);
    state.navLinks.forEach(function (link) {
      translateNavLink(link, data);
    });
    applyGenericTranslations(state, data);
  }

  function getCachedLanguage(state, language) {
    return state.cache[language] || null;
  }

  function storeLanguageData(state, language, data) {
    state.cache[language] = data;
  }

  function fetchLanguageData(state, language) {
    var url = buildJsonUrl(state.base, language);
    return fetch(url, { cache: "no-store" }).then(function (response) {
      if (!response.ok) throw new Error("Failed to load " + url);
      return response.json();
    });
  }

  function loadFallback(state, language) {
    if (language === state.fallback) return Promise.resolve(false);
    return loadLanguage(state, state.fallback);
  }

  function applyCachedLanguage(state, language, data) {
    applyHeaderTranslations(state, language, data);
    return Promise.resolve(true);
  }

  function fetchAndApplyLanguage(state, language) {
    return fetchLanguageData(state, language)
      .then(function (data) {
        storeLanguageData(state, language, data);
        applyHeaderTranslations(state, language, data);
        return true;
      })
      .catch(function () {
        return loadFallback(state, language);
      });
  }

  function loadLanguage(state, language) {
    var nextLanguage = normalizeLanguage(language, state.fallback);
    var cached = getCachedLanguage(state, nextLanguage);
    if (cached) return applyCachedLanguage(state, nextLanguage, cached);
    return fetchAndApplyLanguage(state, nextLanguage);
  }

  function getSavedLanguage() {
    try {
      return localStorage.getItem("wpFrontendLng") || "";
    } catch (e) {
      return "";
    }
  }

  function getBrowserLanguage() {
    return navigator.language || navigator.userLanguage || "";
  }

  function getInitialLanguage(state) {
    var saved = getSavedLanguage();
    if (saved) return normalizeLanguage(saved, state.fallback);
    return normalizeLanguage(getBrowserLanguage(), state.fallback);
  }

  function persistLanguage(language) {
    try {
      localStorage.setItem("wpFrontendLng", language);
    } catch (e) {}
  }

  function changeLanguage(state, language) {
    var nextLanguage = normalizeLanguage(language, state.fallback);
    persistLanguage(nextLanguage);
    loadLanguage(state, nextLanguage);
    closeMenu(state);
  }

  function clickedInsideSwitcher(state, event) {
    var target = event.target;
    if (!target) return false;
    return state.switcher.contains(target);
  }

  function bindTrigger(state) {
    state.trigger.addEventListener("click", function (event) {
      event.preventDefault();
      event.stopPropagation();
      toggleMenu(state);
    });
  }

  function bindItems(state) {
    state.items.forEach(function (item) {
      item.addEventListener("click", function (event) {
        event.preventDefault();
        event.stopPropagation();
        changeLanguage(
          state,
          item.getAttribute("data-language") || state.fallback,
        );
      });
    });
  }

  function bindOutsideClick(state) {
    document.addEventListener("pointerdown", function (event) {
      if (!clickedInsideSwitcher(state, event)) closeMenu(state);
    });
  }

  function bindEscape(state) {
    document.addEventListener("keydown", function (event) {
      if (event.key === "Escape") closeMenu(state);
    });
  }

  function bindEvents(state) {
    bindTrigger(state);
    bindItems(state);
    bindOutsideClick(state);
    bindEscape(state);
  }

  function initLanguageSwitcher() {
    var switcher = qs(document, ".language-switcher");
    if (!switcher) return;
    var state = createState(switcher);
    if (!isValidState(state)) return;
    setNavKeys(state);
    closeMenu(state);
    bindEvents(state);
    loadLanguage(state, getInitialLanguage(state));
  }

  document.addEventListener("DOMContentLoaded", initLanguageSwitcher);
})();
