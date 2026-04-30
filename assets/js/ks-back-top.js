(function () {
  "use strict";

  function initBackTop() {
    var button = document.querySelector(".ks-back-top");
    if (!button) return;

    function syncVisibility() {
      button.classList.toggle("is-visible", window.scrollY > 420);
    }

    button.addEventListener("click", function () {
      window.scrollTo({
        top: 0,
        behavior: "smooth",
      });
    });

    window.addEventListener("scroll", syncVisibility, { passive: true });
    syncVisibility();
  }

  document.addEventListener("DOMContentLoaded", initBackTop);
})();
