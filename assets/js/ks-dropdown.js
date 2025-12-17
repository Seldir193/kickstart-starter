// assets/js/ks-dropdown.js
(function () {
  "use strict";

  function $(sel, ctx) { return (ctx || document).querySelector(sel); }
  function $all(sel, ctx) { return Array.from((ctx || document).querySelectorAll(sel)); }

  function getMaxRows(dd) {
    var v = dd.getAttribute("data-max-rows");
    var n = parseInt(v || "", 10);
    return Number.isFinite(n) && n > 0 ? n : 5;
  }

  function shouldSubmit(dd) {
    return dd.getAttribute("data-submit") === "1";
  }

  // ✅ NEU: eindeutiges Select pro Dropdown (data-select="#id")
  function getNativeSelect(dd) {
    var selRef = dd.getAttribute("data-select");
    if (selRef) {
      var s = $(selRef, document);
      if (s) return s;
    }
    // fallback: select neben dropdown oder im form
    return $("select", dd.parentElement) || dd.closest("form")?.querySelector("select");
  }

  function syncLabelFromSelect(dd, nativeSel) {
    var label = $(".ks-dd__label", dd);
    if (!label || !nativeSel) return;
    var opt = nativeSel.selectedOptions && nativeSel.selectedOptions[0];
    label.textContent = opt ? opt.textContent : "Bitte auswählen …";
  }

  function setPanelMaxHeight(dd, panel) {
    var rows = getMaxRows(dd);
    var rowH = 44;

    var first = panel.firstElementChild;
    if (first) {
      var h = first.getBoundingClientRect().height;
      if (h > 0) rowH = Math.round(h);
    } else {
      var cssVar = getComputedStyle(panel).getPropertyValue("--row-h");
      var parsed = parseFloat(cssVar);
      if (!isNaN(parsed) && parsed > 0) rowH = parsed;
    }

    panel.style.maxHeight = (rowH * rows) + "px";
    panel.style.overflowY = "auto";
    panel.style.overflowX = "hidden";
  }

  function buildPanelFromSelect(dd, nativeSel, panel) {
    panel.innerHTML = "";
    var current = nativeSel.value;

    Array.from(nativeSel.options).forEach(function (opt) {
      if (opt.disabled || opt.value === "") return;

      var item = document.createElement("div");
      item.className = "ks-dd__option";
      item.setAttribute("role", "option");
      item.setAttribute("tabindex", "-1");
      item.setAttribute("data-value", opt.value);
      item.textContent = opt.textContent;

      if (opt.value === current) item.setAttribute("aria-selected", "true");
      panel.appendChild(item);
    });

    setPanelMaxHeight(dd, panel);
  }

  function ensureSelectedState(nativeSel, panel) {
    if (!nativeSel || !panel) return;
    var current = nativeSel.value;

    $all(".ks-dd__option", panel).forEach(function (x) {
      x.removeAttribute("aria-selected");
      var v = x.getAttribute("data-value") || x.dataset.value || x.dataset.phone;
      if (v === current) x.setAttribute("aria-selected", "true");
    });
  }

  function focusSelectedOrFirst(panel) {
  var sel = panel.querySelector('.ks-dd__option[aria-selected="true"]');
  var first = panel.querySelector(".ks-dd__option");
  var target = sel || first;
  if (!target) return;

  // Fokus setzen ohne Page-Scroll
  try { target.focus({ preventScroll: true }); } catch (e) {}

  // Nur IM Panel scrollen (kein Springen der Seite)
  var r = target.getBoundingClientRect();
  var pr = panel.getBoundingClientRect();

  if (r.top < pr.top) {
    panel.scrollTop -= (pr.top - r.top);
  } else if (r.bottom > pr.bottom) {
    panel.scrollTop += (r.bottom - pr.bottom);
  }
}



  function closeDD(dd, focusBtn) {
    var btn = $(".ks-dd__btn", dd);
    var panel = $(".ks-dd__panel", dd);

    dd.classList.remove("is-open");
    dd.setAttribute("aria-expanded", "false");
    if (btn) btn.setAttribute("aria-expanded", "false");
    if (panel) panel.setAttribute("hidden", "");

    if (dd.__onOutsidePointerDown) {
      document.removeEventListener("pointerdown", dd.__onOutsidePointerDown, true);
      dd.__onOutsidePointerDown = null;
    }

    if (focusBtn !== false) {
      try { btn && btn.focus({ preventScroll: true }); } catch (e) {}
    }
  }

  function closeAllDropdowns(exceptDd) {
    $all(".ks-dd.is-open").forEach(function (dd) {
      if (exceptDd && dd === exceptDd) return;
      closeDD(dd, false);
    });
  }

  function openDD(dd) {
    var btn = $(".ks-dd__btn", dd);
    var panel = $(".ks-dd__panel", dd);
    var nativeSel = getNativeSelect(dd);

    closeAllDropdowns(dd);

    // Panel bauen, wenn leer (Programs/Trainer). WA hat oft schon Options im HTML.
    if (panel && nativeSel && panel.children.length === 0) {
      buildPanelFromSelect(dd, nativeSel, panel);
    }

    if (panel) {
      setPanelMaxHeight(dd, panel);
      ensureSelectedState(nativeSel, panel);
    }
    if (nativeSel) syncLabelFromSelect(dd, nativeSel);

    dd.classList.add("is-open");
dd.setAttribute("aria-expanded", "true");
if (btn) btn.setAttribute("aria-expanded", "true");

if (panel) {
  // Erst sichtbar machen, aber noch "unsichtbar" rendern -> kein Flackern
  panel.style.visibility = "hidden";
  panel.removeAttribute("hidden");

  requestAnimationFrame(function () {
    // jetzt sind Maße stabil
    setPanelMaxHeight(dd, panel);
    if (nativeSel) ensureSelectedState(dd, nativeSel, panel);

    panel.style.visibility = "";
    focusSelectedOrFirst(panel);
  });
}



    // ✅ NEU: outside pointerdown erst "nach dem Klick" binden (verhindert 2-Klick Bugs)
    if (dd.__onOutsidePointerDown) {
      document.removeEventListener("pointerdown", dd.__onOutsidePointerDown, true);
      dd.__onOutsidePointerDown = null;
    }

    dd.__onOutsidePointerDown = function (e) {
      if (!dd.contains(e.target)) closeDD(dd, false);
    };

    setTimeout(function () {
      document.addEventListener("pointerdown", dd.__onOutsidePointerDown, true);
    }, 0);

    // ESC (once)
    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape") closeDD(dd, true);
    }, { once: true });
  }

  function bindOne(dd) {
    if (dd.dataset.ksDdBound === "1") return;
    dd.dataset.ksDdBound = "1";

    var btn = $(".ks-dd__btn", dd);
    var panel = $(".ks-dd__panel", dd);
    var nativeSel = getNativeSelect(dd);

    if (nativeSel) syncLabelFromSelect(dd, nativeSel);

    // ✅ NEU: pointerdown stoppen (damit globale capture-listener nix "frisst")
    btn?.addEventListener("pointerdown", function (e) { e.stopPropagation(); });
    panel?.addEventListener("pointerdown", function (e) { e.stopPropagation(); });

    btn?.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      dd.classList.contains("is-open") ? closeDD(dd, true) : openDD(dd);
    });

    panel?.addEventListener("click", function (e) {
      var item = e.target.closest(".ks-dd__option");
      if (!item) return;

      var val = item.getAttribute("data-value") ?? item.dataset.value ?? item.dataset.phone;
      if (!val) return;

      // aria-selected
      $all(".ks-dd__option", panel).forEach(function (x) { x.removeAttribute("aria-selected"); });
      item.setAttribute("aria-selected", "true");

      if (nativeSel) {
        nativeSel.value = val;
        nativeSel.dispatchEvent(new Event("change", { bubbles: true }));
        syncLabelFromSelect(dd, nativeSel);
      } else {
        var label = $(".ks-dd__label", dd);
        if (label) label.textContent = item.dataset.label || item.textContent || "Bitte auswählen …";
      }

      closeDD(dd, true);

      if (shouldSubmit(dd)) {
        var form = dd.closest("form");
        if (form) form.submit();
      }
    });

    panel?.addEventListener("keydown", function (e) {
      var items = $all(".ks-dd__option", panel);
      var cur = document.activeElement;
      var i = items.indexOf(cur);

      if (e.key === "ArrowDown") { e.preventDefault(); (items[i + 1] || items[0])?.focus(); }
      if (e.key === "ArrowUp") { e.preventDefault(); (items[i - 1] || items[items.length - 1])?.focus(); }
      if (e.key === "Enter") { e.preventDefault(); cur?.click(); }
      if (e.key === "Escape") { e.preventDefault(); closeDD(dd, true); }
    });

    nativeSel?.addEventListener("change", function () {
      syncLabelFromSelect(dd, nativeSel);
      ensureSelectedState(nativeSel, panel);
    });
  }

  function init(root) {
    $all(".ks-dd", root || document).forEach(bindOne);
  }

  document.addEventListener("DOMContentLoaded", function () {
    init(document);
  });

  window.KSDropdown = { init: init };
})();














