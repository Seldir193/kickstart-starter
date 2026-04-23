(function () {
  "use strict";

  function stopCurrentAnimation(el) {
    if (!el || !el.getAnimations) return;
    el.getAnimations().forEach(function (animation) {
      animation.cancel();
    });
  }

  function resetInlineState(el) {
    if (!el) return;
    el.style.height = "";
    el.style.opacity = "";
    el.style.transform = "";
    el.style.overflow = "";
    el.style.willChange = "";
  }

  function showElement(el, hiddenAttr) {
    if (!el) return;
    if (hiddenAttr) el.removeAttribute("hidden");
    else el.style.display = "block";
  }

  function hideElement(el, hiddenAttr) {
    if (!el) return;
    if (hiddenAttr) el.setAttribute("hidden", "");
    else el.style.display = "none";
  }

  function animateOpen(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};
    var useHiddenAttr = config.useHiddenAttr === true;

    stopCurrentAnimation(el);
    showElement(el, useHiddenAttr);

    el.style.overflow = "hidden";
    el.style.willChange = "height, opacity, transform";

    var endHeight = el.scrollHeight;

    return el
      .animate(
        [
          { height: "0px", opacity: 0, transform: "translateY(-8px)" },
          { height: endHeight + "px", opacity: 1, transform: "translateY(0)" },
        ],
        {
          duration: config.duration || 420,
          easing: config.easing || "cubic-bezier(0.22, 1, 0.36, 1)",
          fill: "both",
        },
      )
      .finished.finally(function () {
        resetInlineState(el);
      });
  }

  function animateClose(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};
    var useHiddenAttr = config.useHiddenAttr === true;

    stopCurrentAnimation(el);

    el.style.overflow = "hidden";
    el.style.willChange = "height, opacity, transform";

    var startHeight = el.scrollHeight;

    return el
      .animate(
        [
          {
            height: startHeight + "px",
            opacity: 1,
            transform: "translateY(0)",
          },
          { height: "0px", opacity: 0, transform: "translateY(-8px)" },
        ],
        {
          duration: config.duration || 340,
          easing: config.easing || "ease-out",
          fill: "both",
        },
      )
      .finished.finally(function () {
        resetInlineState(el);
        hideElement(el, useHiddenAttr);
      });
  }

  function fadeOpen(el, duration) {
    if (!el) return Promise.resolve();

    stopCurrentAnimation(el);
    el.style.display = "block";

    return el
      .animate([{ opacity: 0 }, { opacity: 1 }], {
        duration: duration || 260,
        easing: "ease-out",
        fill: "both",
      })
      .finished.finally(function () {
        el.style.opacity = "";
      });
  }

  function fadeClose(el, duration) {
    if (!el) return Promise.resolve();

    stopCurrentAnimation(el);

    return el
      .animate([{ opacity: 1 }, { opacity: 0 }], {
        duration: duration || 220,
        easing: "ease-out",
        fill: "both",
      })
      .finished.finally(function () {
        el.style.opacity = "";
        el.style.display = "none";
      });
  }

  function innerOpen(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};
    stopCurrentAnimation(el);

    var targetHeight = el.scrollHeight;

    el.style.height = "0px";
    el.style.opacity = "0";
    el.style.overflow = "hidden";
    el.style.willChange = "height, opacity";

    return new Promise(function (resolve) {
      requestAnimationFrame(function () {
        el.animate(
          [
            { height: "0px", opacity: 0 },
            { height: targetHeight + "px", opacity: 1 },
          ],
          {
            duration: config.duration || 320,
            easing: config.easing || "cubic-bezier(0.22, 1, 0.36, 1)",
            fill: "forwards",
          },
        ).finished.finally(function () {
          resetInlineState(el);
          if (typeof config.afterOpen === "function") {
            config.afterOpen();
          }
          resolve();
        });
      });
    });
  }

  function innerClose(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};
    stopCurrentAnimation(el);

    var startHeight = el.scrollHeight;

    el.style.height = startHeight + "px";
    el.style.opacity = "1";
    el.style.overflow = "hidden";
    el.style.willChange = "height, opacity";

    return new Promise(function (resolve) {
      requestAnimationFrame(function () {
        el.animate(
          [
            { height: startHeight + "px", opacity: 1 },
            { height: "0px", opacity: 0 },
          ],
          {
            duration: config.duration || 240,
            easing: config.easing || "ease-out",
            fill: "forwards",
          },
        ).finished.finally(function () {
          resetInlineState(el);
          if (typeof config.afterClose === "function") {
            config.afterClose();
          }
          resolve();
        });
      });
    });
  }

  function panelOpen(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};

    stopCurrentAnimation(el);

    el.style.visibility = "visible";
    el.style.pointerEvents = "auto";
    el.style.opacity = "0";
    el.style.willChange = "opacity";

    return el
      .animate([{ opacity: 0 }, { opacity: 1 }], {
        duration: config.duration || 240,
        easing: config.easing || "ease-out",
        fill: "both",
      })
      .finished.finally(function () {
        el.style.opacity = "";
        el.style.willChange = "";
      });
  }

  function panelClose(el, options) {
    if (!el) return Promise.resolve();

    var config = options || {};

    stopCurrentAnimation(el);
    el.style.willChange = "opacity";

    return el
      .animate([{ opacity: 1 }, { opacity: 0 }], {
        duration: config.duration || 180,
        easing: config.easing || "ease-out",
        fill: "both",
      })
      .finished.finally(function () {
        el.style.opacity = "0";
        el.style.visibility = "hidden";
        el.style.pointerEvents = "none";
        el.style.willChange = "";
      });
  }

  window.KSDropdownMotion = {
    animateOpen: animateOpen,
    animateClose: animateClose,
    fadeOpen: fadeOpen,
    fadeClose: fadeClose,
    innerOpen: innerOpen,
    innerClose: innerClose,
    panelOpen: panelOpen,
    panelClose: panelClose,
  };
})();

// (function () {
//   "use strict";

//   function stopCurrentAnimation(el) {
//     if (!el || !el.getAnimations) return;
//     el.getAnimations().forEach(function (animation) {
//       animation.cancel();
//     });
//   }

//   function resetInlineState(el) {
//     if (!el) return;
//     el.style.height = "";
//     el.style.opacity = "";
//     el.style.transform = "";
//     el.style.overflow = "";
//     el.style.willChange = "";
//   }

//   function showElement(el, hiddenAttr) {
//     if (!el) return;
//     if (hiddenAttr) el.removeAttribute("hidden");
//     else el.style.display = "block";
//   }

//   function hideElement(el, hiddenAttr) {
//     if (!el) return;
//     if (hiddenAttr) el.setAttribute("hidden", "");
//     else el.style.display = "none";
//   }

//   function animateOpen(el, options) {
//     if (!el) return Promise.resolve();

//     var config = options || {};
//     var useHiddenAttr = config.useHiddenAttr === true;

//     stopCurrentAnimation(el);
//     showElement(el, useHiddenAttr);

//     el.style.overflow = "hidden";
//     el.style.willChange = "height, opacity, transform";

//     var endHeight = el.scrollHeight;

//     return el
//       .animate(
//         [
//           { height: "0px", opacity: 0, transform: "translateY(-8px)" },
//           { height: endHeight + "px", opacity: 1, transform: "translateY(0)" },
//         ],
//         {
//           duration: config.duration || 420,
//           easing: config.easing || "cubic-bezier(0.22, 1, 0.36, 1)",
//           fill: "both",
//         },
//       )
//       .finished.finally(function () {
//         resetInlineState(el);
//       });
//   }

//   function animateClose(el, options) {
//     if (!el) return Promise.resolve();

//     var config = options || {};
//     var useHiddenAttr = config.useHiddenAttr === true;

//     stopCurrentAnimation(el);

//     el.style.overflow = "hidden";
//     el.style.willChange = "height, opacity, transform";

//     var startHeight = el.scrollHeight;

//     return el
//       .animate(
//         [
//           {
//             height: startHeight + "px",
//             opacity: 1,
//             transform: "translateY(0)",
//           },
//           { height: "0px", opacity: 0, transform: "translateY(-8px)" },
//         ],
//         {
//           duration: config.duration || 340,
//           easing: config.easing || "ease-out",
//           fill: "both",
//         },
//       )
//       .finished.finally(function () {
//         resetInlineState(el);
//         hideElement(el, useHiddenAttr);
//       });
//   }

//   function fadeOpen(el, duration) {
//     if (!el) return Promise.resolve();

//     stopCurrentAnimation(el);
//     el.style.display = "block";

//     return el
//       .animate([{ opacity: 0 }, { opacity: 1 }], {
//         duration: duration || 260,
//         easing: "ease-out",
//         fill: "both",
//       })
//       .finished.finally(function () {
//         el.style.opacity = "";
//       });
//   }

//   function fadeClose(el, duration) {
//     if (!el) return Promise.resolve();

//     stopCurrentAnimation(el);

//     return el
//       .animate([{ opacity: 1 }, { opacity: 0 }], {
//         duration: duration || 220,
//         easing: "ease-out",
//         fill: "both",
//       })
//       .finished.finally(function () {
//         el.style.opacity = "";
//         el.style.display = "none";
//       });
//   }

//   function panelOpen(el, options) {
//     if (!el) return Promise.resolve();

//     var config = options || {};

//     stopCurrentAnimation(el);

//     el.style.visibility = "visible";
//     el.style.pointerEvents = "auto";
//     el.style.opacity = "0";
//     el.style.willChange = "opacity";

//     return el
//       .animate([{ opacity: 0 }, { opacity: 1 }], {
//         duration: config.duration || 240,
//         easing: config.easing || "ease-out",
//         fill: "both",
//       })
//       .finished.finally(function () {
//         el.style.opacity = "";
//         el.style.willChange = "";
//       });
//   }

//   function panelClose(el, options) {
//     if (!el) return Promise.resolve();

//     var config = options || {};

//     stopCurrentAnimation(el);

//     el.style.willChange = "opacity";

//     return el
//       .animate([{ opacity: 1 }, { opacity: 0 }], {
//         duration: config.duration || 180,
//         easing: config.easing || "ease-out",
//         fill: "both",
//       })
//       .finished.finally(function () {
//         el.style.opacity = "0";
//         el.style.visibility = "hidden";
//         el.style.pointerEvents = "none";
//         el.style.willChange = "";
//       });
//   }

//   window.KSDropdownMotion = {
//     animateOpen: animateOpen,
//     animateClose: animateClose,
//     fadeOpen: fadeOpen,
//     fadeClose: fadeClose,
//     panelOpen: panelOpen,
//     panelClose: panelClose,
//   };
// })();
