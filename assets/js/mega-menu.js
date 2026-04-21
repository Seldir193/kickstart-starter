//@ts-nocheck
(function () {
  "use strict";

  function qs(root, sel) {
    return (root || document).querySelector(sel);
  }

  function findToggle() {
    return (
      qs(document, ".menu .ks-programs-toggle > a") ||
      qs(document, ".menu .ks-programs-toggle")
    );
  }

  function setPanelAria(toggle, panel) {
    toggle.setAttribute("role", "button");
    toggle.setAttribute("aria-expanded", "false");
    if (!panel) return;
    panel.id = "mega-programs-panel";
    toggle.setAttribute("aria-controls", panel.id);
  }

  function focusFirst(panel) {
    if (!panel) return;
    var first = panel.querySelector(
      "a,button,input,select,textarea,[tabindex]",
    );
    if (!first || !first.focus) return;
    try {
      first.focus({ preventScroll: true });
    } catch (e) {}
  }

  // function openPanel(state) {
  //   if (state.isOpen) return;
  //   state.isOpen = true;
  //   state.wrap.classList.add("is-open");
  //   state.toggle.setAttribute("aria-expanded", "true");
  //   focusFirst(state.panel);
  // }

  // function closePanel(state) {
  //   if (!state.isOpen) return;
  //   state.isOpen = false;
  //   state.wrap.classList.remove("is-open");
  //   state.toggle.setAttribute("aria-expanded", "false");
  // }

  // function togglePanel(state, event) {
  //   if (event && event.preventDefault) event.preventDefault();
  //   if (state.isOpen) closePanel(state);
  //   else openPanel(state);
  // }

  function openPanel(state, moveFocus) {
    if (state.isOpen) return;
    state.isOpen = true;
    state.wrap.classList.add("is-open");
    state.toggle.setAttribute("aria-expanded", "true");
    if (moveFocus) focusFirst(state.panel);
  }

  function closePanel(state) {
    if (!state.isOpen) return;
    state.isOpen = false;
    state.wrap.classList.remove("is-open");
    state.toggle.setAttribute("aria-expanded", "false");
  }

  function togglePanel(state, event, moveFocus) {
    if (event && event.preventDefault) event.preventDefault();
    if (state.isOpen) closePanel(state);
    else openPanel(state, moveFocus);
  }

  function clickedOutside(state, event) {
    var target = event.target;
    if (!target) return false;
    if (state.wrap.contains(target)) return false;
    return !state.toggle.contains(target);
  }

  function handlePanelClick(state, event) {
    var link =
      event && event.target && event.target.closest
        ? event.target.closest("a")
        : null;
    if (link) closePanel(state);
  }

  function bindEvents(state) {
    // state.toggle.addEventListener("click", function (event) {
    //   togglePanel(state, event);
    // });

    state.toggle.addEventListener("click", function (event) {
      togglePanel(state, event, false);
    });

    state.toggle.addEventListener("keydown", function (event) {
      if (!event) return;
      if (event.key === "Enter" || event.key === " ") {
        togglePanel(state, event, true);
      }
      if (event.key === "ArrowDown" && !state.isOpen) {
        openPanel(state, true);
        event.preventDefault();
      }
    });

    if (state.backdrop) {
      state.backdrop.addEventListener("click", function () {
        closePanel(state);
      });
    }

    document.addEventListener("pointerdown", function (event) {
      if (clickedOutside(state, event)) closePanel(state);
    });

    document.addEventListener("keydown", function (event) {
      if (event && event.key === "Escape") closePanel(state);
    });

    if (state.panel) {
      state.panel.addEventListener("click", function (event) {
        handlePanelClick(state, event);
      });
    }
  }

  function createState(toggle, wrap) {
    return {
      isOpen: false,
      toggle: toggle,
      wrap: wrap,
      backdrop: qs(wrap, ".ks-programs__backdrop"),
      panel: qs(wrap, ".ks-programs__panel"),
    };
  }

  function initMegaMenu() {
    var toggle = findToggle();
    var wrap = qs(document, "[data-mega].ks-programs");
    if (!toggle || !wrap) return;
    var state = createState(toggle, wrap);
    setPanelAria(state.toggle, state.panel);
    bindEvents(state);
  }

  document.addEventListener("DOMContentLoaded", initMegaMenu);
})();
