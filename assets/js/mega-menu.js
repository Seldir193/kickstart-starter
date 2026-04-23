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

  // function openPanel(state, moveFocus) {
  //   if (state.isOpen) return;
  //   state.isOpen = true;
  //   state.wrap.classList.add("is-open");
  //   state.toggle.setAttribute("aria-expanded", "true");
  //   if (moveFocus) focusFirst(state.panel);
  // }

  // function closePanel(state) {
  //   if (!state.isOpen) return;
  //   state.isOpen = false;
  //   state.wrap.classList.remove("is-open");
  //   state.toggle.setAttribute("aria-expanded", "false");
  // }

  function animateRowOpen(panel, done) {
    if (!panel) return;
    var row = panel.querySelector(".ks-programs__row");
    if (!row) {
      if (done) done();
      return;
    }

    row.getAnimations().forEach(function (a) {
      a.cancel();
    });

    row.style.height = "0px";
    row.style.opacity = "0";
    row.style.overflow = "hidden";

    var targetHeight = row.scrollHeight;

    requestAnimationFrame(function () {
      row
        .animate(
          [
            { height: "0px", opacity: 0 },
            { height: targetHeight + "px", opacity: 1 },
          ],
          {
            duration: 360,
            easing: "cubic-bezier(0.22, 1, 0.36, 1)",
            fill: "forwards",
          },
        )
        .finished.finally(function () {
          row.style.height = "";
          row.style.opacity = "";
          row.style.overflow = "";
          if (done) done();
        });
    });
  }

  function animateRowClose(panel, done) {
    if (!panel) return;
    var row = panel.querySelector(".ks-programs__row");
    if (!row) {
      if (done) done();
      return;
    }

    row.getAnimations().forEach(function (a) {
      a.cancel();
    });

    var startHeight = row.scrollHeight;

    row.style.height = startHeight + "px";
    row.style.opacity = "1";
    row.style.overflow = "hidden";

    requestAnimationFrame(function () {
      row
        .animate(
          [
            { height: startHeight + "px", opacity: 1 },
            { height: "0px", opacity: 0 },
          ],
          {
            duration: 260,
            easing: "ease-out",
            fill: "forwards",
          },
        )
        .finished.finally(function () {
          row.style.height = "0px";
          row.style.opacity = "0";
          row.style.overflow = "hidden";
          if (done) done();
        });
    });
  }

  function openPanel(state, moveFocus) {
    if (state.isOpen) return;
    state.isOpen = true;
    state.wrap.classList.add("is-open");
    state.toggle.setAttribute("aria-expanded", "true");

    if (state.backdrop && window.KSDropdownMotion) {
      window.KSDropdownMotion.fadeOpen(state.backdrop, 220);
    }

    animateRowOpen(state.panel, function () {
      if (moveFocus) focusFirst(state.panel);
    });
  }

  function closePanel(state) {
    if (!state.isOpen) return;
    state.isOpen = false;
    state.toggle.setAttribute("aria-expanded", "false");

    if (state.backdrop && window.KSDropdownMotion) {
      window.KSDropdownMotion.fadeClose(state.backdrop, 180);
    }

    animateRowClose(state.panel, function () {
      state.wrap.classList.remove("is-open");
    });
  }
  // function openPanel(state, moveFocus) {
  //   if (state.isOpen) return;
  //   state.isOpen = true;
  //   state.wrap.classList.add("is-open");
  //   state.toggle.setAttribute("aria-expanded", "true");

  //   if (window.KSDropdownMotion) {
  //     window.KSDropdownMotion.fadeOpen(state.backdrop, 220);

  //     state.panel.style.opacity = "0";

  //     requestAnimationFrame(function () {
  //       state.panel
  //         .animate([{ opacity: 0 }, { opacity: 1 }], {
  //           duration: 260,
  //           easing: "ease-out",
  //           fill: "both",
  //         })
  //         .finished.finally(function () {
  //           state.panel.style.opacity = "";
  //           if (moveFocus) focusFirst(state.panel);
  //         });
  //     });
  //     return;
  //   }

  //   if (moveFocus) focusFirst(state.panel);
  // }

  // function closePanel(state) {
  //   if (!state.isOpen) return;
  //   state.isOpen = false;
  //   state.toggle.setAttribute("aria-expanded", "false");

  //   if (window.KSDropdownMotion) {
  //     Promise.all([
  //       window.KSDropdownMotion.fadeClose(state.backdrop, 180),
  //       state.panel.animate([{ opacity: 1 }, { opacity: 0 }], {
  //         duration: 180,
  //         easing: "ease-out",
  //         fill: "both",
  //       }).finished,
  //     ]).finally(function () {
  //       state.panel.style.opacity = "";
  //       state.wrap.classList.remove("is-open");
  //     });
  //     return;
  //   }

  //   state.wrap.classList.remove("is-open");
  // }

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
