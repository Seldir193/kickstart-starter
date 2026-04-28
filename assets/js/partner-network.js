(function () {
  const sliders = Array.from(
    document.querySelectorAll("[data-partner-slider]"),
  );

  if (!sliders.length) return;

  sliders.forEach(initPartnerSlider);

  function initPartnerSlider(slider) {
    const state = getPartnerSliderState(slider);

    if (!state.track || !state.prevButton || !state.nextButton) return;
    if (!state.items.length) return;

    bindPartnerSliderEvents(state);
    updatePartnerSlider(state);
  }

  function getPartnerSliderState(slider) {
    const track = slider.querySelector("[data-partner-track]");

    return {
      slider,
      track,
      prevButton: slider.querySelector("[data-partner-prev]"),
      nextButton: slider.querySelector("[data-partner-next]"),
      items: Array.from(track ? track.children : []),
      activeIndex: 0,
      resizeFrame: 0,
    };
  }

  function bindPartnerSliderEvents(state) {
    state.prevButton.addEventListener("click", () =>
      movePartnerSlider(state, -1),
    );
    state.nextButton.addEventListener("click", () =>
      movePartnerSlider(state, 1),
    );

    window.addEventListener("resize", () => {
      cancelAnimationFrame(state.resizeFrame);
      state.resizeFrame = requestAnimationFrame(() =>
        updatePartnerSlider(state),
      );
    });
  }

  function movePartnerSlider(state, step) {
    state.activeIndex += step;
    updatePartnerSlider(state);
  }

  function updatePartnerSlider(state) {
    const maxIndex = getPartnerSliderMaxIndex(state);
    const offset = getPartnerSliderOffset(state);

    state.activeIndex = clampPartnerIndex(state.activeIndex, maxIndex);
    state.track.style.transform = `translate3d(${-state.activeIndex * offset}px, 0, 0)`;
    updatePartnerSliderButtons(state, maxIndex);
  }

  function getPartnerSliderMaxIndex(state) {
    return Math.max(state.items.length - getPartnerVisibleCount(state), 0);
  }

  function getPartnerVisibleCount(state) {
    const value = getComputedStyle(state.slider).getPropertyValue(
      "--partner-network-visible",
    );
    const parsed = parseInt(value, 10);

    return Number.isNaN(parsed) ? 5 : parsed;
  }

  function getPartnerSliderOffset(state) {
    const item = state.items[0];

    if (!item) return 0;

    return item.getBoundingClientRect().width + getPartnerSliderGap(state);
  }

  function getPartnerSliderGap(state) {
    const styles = getComputedStyle(state.track);
    const value = parseFloat(styles.columnGap || styles.gap || "0");

    return Number.isNaN(value) ? 0 : value;
  }

  function clampPartnerIndex(index, maxIndex) {
    if (index < 0) return 0;
    if (index > maxIndex) return maxIndex;

    return index;
  }

  function updatePartnerSliderButtons(state, maxIndex) {
    const hasNavigation = maxIndex > 0;

    state.prevButton.hidden = !hasNavigation;
    state.nextButton.hidden = !hasNavigation;
    state.prevButton.disabled = state.activeIndex <= 0;
    state.nextButton.disabled = state.activeIndex >= maxIndex;
  }
})();
