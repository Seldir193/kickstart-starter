(function () {
  "use strict";

  function animateOpen(body) {
    if (!body || !body.animate) return;

    body.style.overflow = "hidden";
    body.style.opacity = "1";

    var endHeight = body.scrollHeight;

    body.animate(
      [
        { height: "0px", opacity: 0 },
        { height: endHeight + "px", opacity: 1 },
      ],
      {
        duration: 520,
        easing: "cubic-bezier(0.22, 1, 0.36, 1)",
        fill: "both",
      },
    ).onfinish = function () {
      body.style.height = "";
      body.style.opacity = "";
      body.style.overflow = "";
    };
  }

  function animateClose(body, onDone) {
    if (!body || !body.animate) {
      if (onDone) onDone();
      return;
    }

    var startHeight = body.scrollHeight;

    body.style.overflow = "hidden";
    body.style.height = startHeight + "px";
    body.style.opacity = "1";

    body.animate(
      [
        { height: startHeight + "px", opacity: 1 },
        { height: "0px", opacity: 0 },
      ],
      // {
      //   duration: 420,
      //   easing: "ease-out",
      //   fill: "both",
      // },
      {
        duration: 180,
        easing: "cubic-bezier(0.4, 0, 1, 1)",
        fill: "both",
      },
    ).onfinish = function () {
      body.style.height = "";
      body.style.opacity = "";
      body.style.overflow = "";
      if (onDone) onDone();
    };
  }

  function closeDetails(details) {
    details.removeAttribute("open");
  }

  function bindAccordion(details) {
    var summary = details.querySelector("summary");
    var body = details.querySelector(".ks-acc__body");
    if (!summary || !body) return;

    if (details.hasAttribute("data-accordion-bound")) return;
    details.setAttribute("data-accordion-bound", "true");

    summary.addEventListener("click", function (event) {
      event.preventDefault();

      // if (details.hasAttribute("open")) {
      //   animateClose(body, function () {
      //     closeDetails(details);
      //   });
      //   return;
      // }

      // details.setAttribute("open", "open");
      // requestAnimationFrame(function () {
      //   animateOpen(body);
      // });
      if (details.hasAttribute("open")) {
        details.classList.add("is-closing");
        details.classList.remove("is-opening");

        animateClose(body, function () {
          closeDetails(details);
          details.classList.remove("is-closing");
        });

        return;
      }

      details.classList.add("is-opening");
      details.classList.remove("is-closing");
      details.setAttribute("open", "open");

      requestAnimationFrame(function () {
        animateOpen(body);
      });
    });
  }

  function initAccordions() {
    var items = document.querySelectorAll(".ks-acc");
    if (!items.length) return;

    items.forEach(function (item) {
      bindAccordion(item);
    });

    setTimeout(function () {
      items.forEach(function (item) {
        if (!item.hasAttribute("data-open-first")) return;
        var body = item.querySelector(".ks-acc__body");
        animateOpen(body);
      });
    }, 120);
  }

  document.addEventListener("DOMContentLoaded", initAccordions);
})();
