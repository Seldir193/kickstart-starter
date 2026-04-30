// assets/js/ks-dropdown.js

(function () {
  "use strict";

  function $(sel, ctx) {
    return (ctx || document).querySelector(sel);
  }
  function $all(sel, ctx) {
    return Array.from((ctx || document).querySelectorAll(sel));
  }

  function getMaxRows(dd) {
    var v = dd.getAttribute("data-max-rows");
    var n = parseInt(v || "", 10);
    return Number.isFinite(n) && n > 0 ? n : 5;
  }

  function shouldSubmit(dd) {
    return dd.getAttribute("data-submit") === "1";
  }

  function getNativeSelect(dd) {
    var selRef = dd.getAttribute("data-select");
    if (selRef) {
      var s = $(selRef, document);
      if (s) return s;
    }

    return (
      $("select", dd.parentElement) ||
      dd.closest("form")?.querySelector("select")
    );
  }

  function syncLabelFromSelect(dd, nativeSel) {
    var label = $(".ks-dd__label", dd);
    if (!label || !nativeSel) return;
    var opt = nativeSel.selectedOptions && nativeSel.selectedOptions[0];
    label.textContent = opt ? opt.textContent : "Bitte auswählen …";
  }

  function setPanelMaxHeight(dd, panel) {
    var inner = getPanelInner(panel);
    if (!inner) return;

    var rows = getMaxRows(dd);
    var rowH = 44;

    var first = inner.firstElementChild;
    if (first) {
      var h = first.getBoundingClientRect().height;
      if (h > 0) rowH = Math.round(h);
    } else {
      var cssVar = getComputedStyle(inner).getPropertyValue("--row-h");
      var parsed = parseFloat(cssVar);
      if (!isNaN(parsed) && parsed > 0) rowH = parsed;
    }

    inner.style.maxHeight = rowH * rows + "px";
    inner.style.overflowY = "auto";
    inner.style.overflowX = "hidden";
  }
  function ensurePanelInner(panel) {
    if (!panel) return null;

    var existing = panel.querySelector(".ks-dd__inner");
    if (existing) return existing;

    var inner = document.createElement("div");
    inner.className = "ks-dd__inner";

    while (panel.firstChild) {
      inner.appendChild(panel.firstChild);
    }

    panel.appendChild(inner);
    return inner;
  }

  function getPanelInner(panel) {
    return ensurePanelInner(panel);
  }

  function buildPanelFromSelect(dd, nativeSel, panel) {
    panel.innerHTML = "";
    var inner = ensurePanelInner(panel);
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
      inner.appendChild(item);
    });

    setPanelMaxHeight(dd, panel);
  }

  function ensureSelectedState(nativeSel, panel) {
    if (!nativeSel || !panel) return;
    var current = nativeSel.value;

    $all(".ks-dd__option", panel).forEach(function (x) {
      x.removeAttribute("aria-selected");
      var v =
        x.getAttribute("data-value") || x.dataset.value || x.dataset.phone;
      if (v === current) x.setAttribute("aria-selected", "true");
    });
  }

  function focusSelectedOrFirst(panel) {
    var inner = getPanelInner(panel) || panel;
    var sel = inner.querySelector('.ks-dd__option[aria-selected="true"]');
    var first = inner.querySelector(".ks-dd__option");
    var target = sel || first;
    if (!target) return;

    try {
      target.focus({ preventScroll: true });
    } catch (e) {}

    var r = target.getBoundingClientRect();
    var pr = inner.getBoundingClientRect();

    if (r.top < pr.top) {
      inner.scrollTop -= pr.top - r.top;
    } else if (r.bottom > pr.bottom) {
      inner.scrollTop += r.bottom - pr.bottom;
    }
  }

  function closeDD(dd, focusBtn) {
    var btn = $(".ks-dd__btn", dd);
    var panel = $(".ks-dd__panel", dd);

    dd.classList.remove("is-open");
    dd.setAttribute("aria-expanded", "false");
    if (btn) btn.setAttribute("aria-expanded", "false");

    if (panel) {
      animateDropdownInnerClose(panel, function () {
        panel.setAttribute("hidden", "");
      });
    }

    if (dd.__onOutsidePointerDown) {
      document.removeEventListener(
        "pointerdown",
        dd.__onOutsidePointerDown,
        true,
      );
      dd.__onOutsidePointerDown = null;
    }

    if (focusBtn !== false) {
      try {
        btn && btn.focus({ preventScroll: true });
      } catch (e) {}
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
      panel.style.visibility = "hidden";
      panel.removeAttribute("hidden");

      requestAnimationFrame(function () {
        ensurePanelInner(panel);
        setPanelMaxHeight(dd, panel);
        if (nativeSel) ensureSelectedState(nativeSel, panel);

        panel.style.visibility = "";

        animateDropdownInnerOpen(panel, dd, function () {
          focusSelectedOrFirst(panel);
        });
      });
    }

    if (dd.__onOutsidePointerDown) {
      document.removeEventListener(
        "pointerdown",
        dd.__onOutsidePointerDown,
        true,
      );
      dd.__onOutsidePointerDown = null;
    }

    dd.__onOutsidePointerDown = function (e) {
      if (!dd.contains(e.target)) closeDD(dd, false);
    };

    setTimeout(function () {
      document.addEventListener("pointerdown", dd.__onOutsidePointerDown, true);
    }, 0);

    document.addEventListener(
      "keydown",
      function (e) {
        if (e.key === "Escape") closeDD(dd, true);
      },
      { once: true },
    );
  }

  function bindOne(dd) {
    if (dd.dataset.ksDdBound === "1") return;
    dd.dataset.ksDdBound = "1";

    var btn = $(".ks-dd__btn", dd);
    var panel = $(".ks-dd__panel", dd);
    var nativeSel = getNativeSelect(dd);

    if (nativeSel) syncLabelFromSelect(dd, nativeSel);

    btn?.addEventListener("pointerdown", function (e) {
      e.stopPropagation();
    });
    panel?.addEventListener("pointerdown", function (e) {
      e.stopPropagation();
    });

    btn?.addEventListener("click", function (e) {
      e.preventDefault();
      e.stopPropagation();
      dd.classList.contains("is-open") ? closeDD(dd, true) : openDD(dd);
    });

    panel?.addEventListener("click", function (e) {
      var item = e.target.closest(".ks-dd__option");
      if (!item) return;

      var val =
        item.getAttribute("data-value") ??
        item.dataset.value ??
        item.dataset.phone;
      if (!val) return;

      $all(".ks-dd__option", panel).forEach(function (x) {
        x.removeAttribute("aria-selected");
      });
      item.setAttribute("aria-selected", "true");

      if (nativeSel) {
        nativeSel.value = val;
        nativeSel.dispatchEvent(new Event("change", { bubbles: true }));
        syncLabelFromSelect(dd, nativeSel);
      } else {
        var label = $(".ks-dd__label", dd);
        if (label)
          label.textContent =
            item.dataset.label || item.textContent || "Bitte auswählen …";
      }

      if (shouldSubmit(dd)) {
        var form = dd.closest("form");
        closeDD(dd, false);

        if (form) {
          setTimeout(function () {
            if (form.requestSubmit) form.requestSubmit();
            else form.submit();
          }, 0);
        }
        return;
      }

      closeDD(dd, true);
    });

    panel?.addEventListener("keydown", function (e) {
      var items = $all(".ks-dd__option", panel);
      var cur = document.activeElement;
      var i = items.indexOf(cur);

      if (e.key === "ArrowDown") {
        e.preventDefault();
        (items[i + 1] || items[0])?.focus();
      }
      if (e.key === "ArrowUp") {
        e.preventDefault();
        (items[i - 1] || items[items.length - 1])?.focus();
      }
      if (e.key === "Enter") {
        e.preventDefault();
        cur?.click();
      }
      if (e.key === "Escape") {
        e.preventDefault();
        closeDD(dd, true);
      }
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

  function animateDropdownInnerOpen(panel, dd, done) {
    var inner = getPanelInner(panel);
    if (!inner) {
      if (done) done();
      return;
    }

    setPanelMaxHeight(dd, panel);

    if (window.KSDropdownMotion && window.KSDropdownMotion.innerOpen) {
      window.KSDropdownMotion.innerOpen(inner, {
        duration: 320,
        easing: "cubic-bezier(0.22, 1, 0.36, 1)",
        afterOpen: function () {
          setPanelMaxHeight(dd, panel);
          if (done) done();
        },
      });
      return;
    }

    if (done) done();
  }

  function animateDropdownInnerClose(panel, done) {
    var inner = getPanelInner(panel);
    if (!inner) {
      if (done) done();
      return;
    }

    if (window.KSDropdownMotion && window.KSDropdownMotion.innerClose) {
      window.KSDropdownMotion.innerClose(inner, {
        duration: 240,
        easing: "ease-out",
        afterClose: function () {
          if (done) done();
        },
      });
      return;
    }

    if (done) done();
  }
})();
