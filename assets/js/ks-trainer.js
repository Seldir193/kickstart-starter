(function () {
  "use strict";

  function getViewport(button) {
    var picker = button.closest("[data-trainer-picker]");
    if (!picker) return null;
    return picker.querySelector("[data-trainer-picker-viewport]");
  }

  function getScrollStep(viewport, direction) {
    var width = viewport.clientWidth || 0;
    return Math.max(180, Math.round(width * 0.8)) * direction;
  }

  function scrollTrainerList(button) {
    var viewport = getViewport(button);
    var direction = parseInt(button.dataset.trainerScroll || "0", 10);
    if (!viewport || !direction) return;
    viewport.scrollBy({
      left: getScrollStep(viewport, direction),
      behavior: "smooth",
    });
  }

  function bindButton(button) {
    if (button.dataset.trainerScrollBound === "1") return;
    button.dataset.trainerScrollBound = "1";
    button.addEventListener("click", function () {
      scrollTrainerList(button);
    });
  }

  function init() {
    document.querySelectorAll("[data-trainer-scroll]").forEach(bindButton);
  }

  document.addEventListener("DOMContentLoaded", init);
})();
