(function () {
  var hero = document.getElementById("home-hero");
  if (!hero) return;

  var tabs = Array.from(hero.querySelectorAll(".ks-hero-tab"));
  var mediaItems = Array.from(
    hero.querySelectorAll(".ks-home-hero__media-item"),
  );
  var titleTarget = hero.querySelector(".ks-home-hero__course-title");
  var textTarget = hero.querySelector(".ks-home-hero__course-text");
  var linkTarget = hero.querySelector(".ks-home-hero__course-link");
  var tablist = hero.querySelector(".ks-hero-tabs");

  if (!tabs.length || !mediaItems.length) return;

  var AUTO_DELAY = 6000;
  var USER_DELAY = 10000;
  var timer = null;
  var observer = null;
  var currentIndex = 0;
  var isFocused = false;
  var isVisible = true;
  var isTabHovered = false;

  function getKey(tab) {
    return tab.getAttribute("data-key") || "";
  }

  function setTabState(activeKey) {
    tabs.forEach(function (tab) {
      var isActive = getKey(tab) === activeKey;
      tab.classList.toggle("is-active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
      tab.setAttribute("tabindex", isActive ? "0" : "-1");

      if (!isActive) return;

      if (titleTarget) {
        titleTarget.textContent = tab.getAttribute("data-title") || "";
      }

      if (textTarget) {
        textTarget.textContent = tab.getAttribute("data-text") || "";
      }

      if (linkTarget) {
        linkTarget.setAttribute("href", tab.getAttribute("data-link") || "#");
      }
    });
  }

  function setMediaState(activeKey) {
    mediaItems.forEach(function (item) {
      var isActive = item.getAttribute("data-key") === activeKey;
      item.classList.toggle("is-active", isActive);
    });
  }

  function activateByIndex(index) {
    currentIndex = index;
    var activeKey = getKey(tabs[currentIndex]);
    setTabState(activeKey);
    setMediaState(activeKey);
  }

  function stopTimer() {
    if (!timer) return;
    clearTimeout(timer);
    timer = null;
  }

  // function canAutoplay() {
  //   return !isFocused && isVisible;
  // }

  function canAutoplay() {
    return !isFocused && !isTabHovered && isVisible;
  }

  function scheduleNext(delay) {
    stopTimer();
    if (!canAutoplay()) return;

    timer = setTimeout(function () {
      var next = (currentIndex + 1) % tabs.length;
      activateByIndex(next);
      scheduleNext(AUTO_DELAY);
    }, delay);
  }

  function restartAuto(delay) {
    scheduleNext(delay || AUTO_DELAY);
  }

  function activateFromUser(index) {
    activateByIndex(index);
    restartAuto(USER_DELAY);
  }

  function setupTabs() {
    if (tablist) {
      tablist.setAttribute("role", "tablist");
    }

    tabs.forEach(function (tab, index) {
      tab.setAttribute("role", "tab");
      tab.setAttribute("aria-selected", String(index === 0));
      tab.setAttribute("tabindex", index === 0 ? "0" : "-1");

      tab.addEventListener("click", function () {
        activateFromUser(index);
        tab.blur();
        isFocused = false;
      });

      tab.addEventListener("keydown", function (event) {
        var target = index;

        if (event.key === "ArrowRight") {
          target = (index + 1) % tabs.length;
        }

        if (event.key === "ArrowLeft") {
          target = (index - 1 + tabs.length) % tabs.length;
        }

        if (event.key === "Home") {
          target = 0;
        }

        if (event.key === "End") {
          target = tabs.length - 1;
        }

        if (target === index && event.key !== "Home" && event.key !== "End") {
          return;
        }

        event.preventDefault();
        tabs[target].focus();
        activateFromUser(target);
      });

      tab.addEventListener("mouseenter", function () {
        if (tab.classList.contains("is-active")) {
          isTabHovered = true;
          stopTimer();
        }
      });

      tab.addEventListener("mouseleave", function () {
        if (tab.classList.contains("is-active")) {
          isTabHovered = false;
          restartAuto(AUTO_DELAY);
        }
      });
    });
  }

  function setupFocusEvents() {
    hero.addEventListener("focusin", function () {
      isFocused = true;
      stopTimer();
    });

    hero.addEventListener("focusout", function () {
      isFocused = false;
      restartAuto(AUTO_DELAY);
    });
  }

  function setupVisibilityObserver() {
    if (!("IntersectionObserver" in window)) return;

    observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.target !== hero) return;

          isVisible = entry.isIntersecting && entry.intersectionRatio > 0.3;

          if (!isVisible) {
            stopTimer();
            return;
          }

          restartAuto(AUTO_DELAY);
        });
      },
      { threshold: [0, 0.3, 0.6, 1] },
    );

    observer.observe(hero);
  }

  function initStartIndex() {
    var activeIndex = tabs.findIndex(function (tab) {
      return tab.classList.contains("is-active");
    });

    currentIndex = activeIndex >= 0 ? activeIndex : 0;
    activateByIndex(currentIndex);
  }

  setupTabs();
  setupFocusEvents();
  setupVisibilityObserver();
  initStartIndex();
  restartAuto(AUTO_DELAY);
})();
// (function () {
//   var hero = document.getElementById("home-hero");
//   if (!hero) return;

//   var tabs = Array.from(hero.querySelectorAll(".ks-hero-tab"));
//   var mediaItems = Array.from(
//     hero.querySelectorAll(".ks-home-hero__media-item"),
//   );
//   var titleTarget = hero.querySelector(".ks-home-hero__course-title");
//   var textTarget = hero.querySelector(".ks-home-hero__course-text");
//   var linkTarget = hero.querySelector(".ks-home-hero__course-link");

//   if (!tabs.length || !mediaItems.length) return;

//   function activate(key) {
//     tabs.forEach(function (tab) {
//       var active = tab.getAttribute("data-key") === key;
//       tab.classList.toggle("is-active", active);
//       tab.setAttribute("aria-selected", String(active));
//       tab.setAttribute("tabindex", active ? "0" : "-1");

//       if (!active) return;

//       if (titleTarget)
//         titleTarget.textContent = tab.getAttribute("data-title") || "";
//       if (textTarget)
//         textTarget.textContent = tab.getAttribute("data-text") || "";
//       if (linkTarget)
//         linkTarget.setAttribute("href", tab.getAttribute("data-link") || "#");
//     });

//     mediaItems.forEach(function (item) {
//       var active = item.getAttribute("data-key") === key;
//       item.classList.toggle("is-active", active);
//     });
//   }

//   tabs.forEach(function (tab, index) {
//     tab.addEventListener("click", function () {
//       activate(tab.getAttribute("data-key"));
//     });

//     tab.addEventListener("keydown", function (event) {
//       var target = index;

//       if (event.key === "ArrowRight") target = (index + 1) % tabs.length;
//       if (event.key === "ArrowLeft")
//         target = (index - 1 + tabs.length) % tabs.length;
//       if (event.key === "Home") target = 0;
//       if (event.key === "End") target = tabs.length - 1;

//       if (target === index && event.key !== "Home" && event.key !== "End")
//         return;

//       event.preventDefault();
//       tabs[target].focus();
//       activate(tabs[target].getAttribute("data-key"));
//     });
//   });

//   var tablist = hero.querySelector(".ks-hero-tabs");
//   if (tablist) tablist.setAttribute("role", "tablist");

//   tabs.forEach(function (tab, index) {
//     tab.setAttribute("role", "tab");
//     tab.setAttribute("aria-selected", String(index === 0));
//     tab.setAttribute("tabindex", index === 0 ? "0" : "-1");
//   });

//   activate(tabs[0].getAttribute("data-key"));
// })();

//alt
// {
//   (function () {
//     var hero = document.getElementById("home-hero");
//     if (!hero) return;

//     var tabs = Array.from(hero.querySelectorAll(".ks-hero-tab"));
//     var slides = Array.from(hero.querySelectorAll(".ks-hero-slide"));

//     var idx = slides.findIndex(function (s) {
//       return s.classList.contains("is-active");
//     });
//     if (idx < 0) idx = 0;

//     var TIMER = null;
//     var DURATION = 6000;

//     var WM_ENTER_DELAY = 0;
//     var TEXT_DELAY = 180;
//     var IMG_DELAY = 300;

//     var afterStart = 120;
//     var step = 126;

//     function setWatermark(i) {
//       var a = slides[i];
//       if (!a) return;
//       var wm = a.getAttribute("data-watermark") || "";
//       hero.setAttribute("data-watermark", wm);
//     }

//     function activate(nextIdx, userTriggered) {
//       if (nextIdx === idx) return;

//       slides.forEach(function (s, i) {
//         s.classList.toggle("is-active", i === nextIdx);
//       });
//       tabs.forEach(function (t, i) {
//         var act = i === nextIdx;
//         t.classList.toggle("is-active", act);

//         t.setAttribute("aria-selected", String(act));
//         t.setAttribute("tabindex", act ? "0" : "-1");
//       });

//       setWatermark(nextIdx);
//       prepAnim(slides[nextIdx]);
//       idx = nextIdx;

//       if (userTriggered) restart();
//     }

//     function next() {
//       activate((idx + 1) % slides.length, false);
//     }

//     function start() {
//       if (TIMER) return;
//       TIMER = setInterval(next, DURATION);
//     }
//     function stop() {
//       if (!TIMER) return;
//       clearInterval(TIMER);
//       TIMER = null;
//     }
//     function restart() {
//       stop();
//       start();
//     }

//     tabs.forEach(function (t, i) {
//       t.setAttribute("role", "tab");
//       if (!t.hasAttribute("tabindex"))
//         t.setAttribute("tabindex", i === idx ? "0" : "-1");
//       if (!t.hasAttribute("aria-selected"))
//         t.setAttribute("aria-selected", String(i === idx));

//       t.addEventListener("click", function () {
//         activate(i, true);
//       });
//       t.addEventListener("keydown", function (e) {
//         if (e.key === "ArrowRight" || e.key === "ArrowLeft") {
//           e.preventDefault();
//           var n =
//             (i + (e.key === "ArrowRight" ? 1 : -1) + tabs.length) % tabs.length;
//           activate(n, true);
//           tabs[n].focus();
//         }
//         if (e.key === "Home") {
//           e.preventDefault();
//           activate(0, true);
//           tabs[0].focus();
//         }
//         if (e.key === "End") {
//           e.preventDefault();
//           activate(tabs.length - 1, true);
//           tabs[tabs.length - 1].focus();
//         }
//       });
//     });
//     var tablist = hero.querySelector(".ks-hero-tabs");
//     if (tablist) tablist.setAttribute("role", "tablist");

//     hero.addEventListener("mouseenter", stop);
//     hero.addEventListener("mouseleave", start);
//     hero.addEventListener("focusin", stop);
//     hero.addEventListener("focusout", start);

//     if ("IntersectionObserver" in window) {
//       var io = new IntersectionObserver(
//         function (entries) {
//           entries.forEach(function (e) {
//             if (e.target !== hero) return;
//             if (e.isIntersecting && e.intersectionRatio > 0.3) {
//               start();
//             } else {
//               stop();
//             }
//           });
//         },
//         { threshold: [0, 0.3, 0.6, 1] },
//       );
//       io.observe(hero);
//     }

//     // Boot
//     prepAnim(slides[idx]);
//     setWatermark(idx);
//     start();

//     function ensureWatermarkRoot() {
//       hero.classList.add("wm-ready");
//       var wm = hero.querySelector(".ks-hero-wm");
//       if (!wm) {
//         wm = document.createElement("div");
//         wm.className = "ks-hero-wm";
//         wm.setAttribute("aria-hidden", "true");
//         hero.appendChild(wm);
//       }
//       return wm;
//     }

//     function buildWatermark(text) {
//       var wm = ensureWatermarkRoot();

//       if (wm.getAttribute("data-text") !== text) {
//         wm.setAttribute("data-text", text);
//         wm.classList.remove("enter");
//         wm.innerHTML = "";
//         for (var i = 0; i < text.length; i++) {
//           var ch = text[i] === " " ? "\u00A0" : text[i];
//           var sp = document.createElement("span");
//           sp.textContent = ch;
//           wm.appendChild(sp);
//         }
//       }
//       return wm;
//     }

//     function clearWatermarkDelays() {
//       var wm = hero.querySelector(".ks-hero-wm");
//       if (!wm) return;
//       wm.querySelectorAll("span").forEach(function (sp) {
//         sp.style.transitionDelay = "";
//       });
//     }

//     function scheduleClearDelays() {
//       var wm = hero.querySelector(".ks-hero-wm");
//       if (!wm) return;
//       var n = wm.querySelectorAll("span").length || 1;

//       var total = afterStart + step * Math.max(0, n - 1) + 900 + 80;
//       clearTimeout(scheduleClearDelays._t);
//       scheduleClearDelays._t = setTimeout(clearWatermarkDelays, total);
//     }

//     function animateWatermark(text) {
//       var wm = buildWatermark(text);
//       var spans = Array.from(wm.querySelectorAll("span"));

//       var firstDelay = 0;
//       spans.forEach(function (sp, i) {
//         var d = i === 0 ? firstDelay : afterStart + (i - 1) * step;
//         sp.style.transitionDelay = d + "ms";
//       });

//       wm.classList.remove("enter");
//       void wm.offsetWidth;
//       wm.classList.add("enter");

//       scheduleClearDelays();
//     }

//     function prepAnim(slide) {
//       var img = slide.querySelector(".ks-home-hero__img");
//       var text = slide.querySelector(".ks-home-hero__left");
//       var wmText = (slide.getAttribute("data-watermark") || "").toString();

//       if (img) img.classList.remove("enter");
//       if (text) text.classList.remove("enter");

//       setTimeout(function () {
//         animateWatermark(wmText);
//       }, WM_ENTER_DELAY);

//       setTimeout(function () {
//         if (!text) return;
//         text.classList.remove("enter");
//         void text.offsetWidth;
//         text.classList.add("enter");
//       }, WM_ENTER_DELAY + TEXT_DELAY);

//       setTimeout(function () {
//         if (!img) return;
//         img.classList.remove("enter");
//         void img.offsetWidth;
//         img.classList.add("enter");
//       }, WM_ENTER_DELAY + IMG_DELAY);
//     }
//   })();
// }
