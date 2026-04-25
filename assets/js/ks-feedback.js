(function () {
  const root = document.querySelector("[data-feedback-root]");

  if (!root) return;

  const tabs = Array.from(root.querySelectorAll("[data-feedback-filter]"));
  const slides = Array.from(root.querySelectorAll("[data-feedback-slide]"));
  const prevButtons = Array.from(root.querySelectorAll("[data-feedback-prev]"));
  const nextButtons = Array.from(root.querySelectorAll("[data-feedback-next]"));
  const currentNodes = Array.from(
    root.querySelectorAll("[data-feedback-current]"),
  );
  const totalNodes = Array.from(root.querySelectorAll("[data-feedback-total]"));
  const progressNodes = Array.from(
    root.querySelectorAll("[data-feedback-progress]"),
  );

  if (!slides.length) return;

  let activeIndex = slides.findIndex((slide) =>
    slide.classList.contains("is-active"),
  );

  if (activeIndex < 0) {
    activeIndex = 0;
  }

  let activeFilter = tabs.find((tab) => tab.classList.contains("is-active"))
    ?.dataset.feedbackFilter;

  if (!activeFilter && tabs[0]) {
    activeFilter = tabs[0].dataset.feedbackFilter;
  }

  function filteredSlides() {
    if (!activeFilter) return slides;

    return slides.filter((slide) => {
      return slide.dataset.feedbackLabel === activeFilter;
    });
  }

  function padNumber(value) {
    return String(value).padStart(2, "0");
  }

  function setTabs() {
    tabs.forEach((tab) => {
      const isActive = tab.dataset.feedbackFilter === activeFilter;

      tab.classList.toggle("is-active", isActive);
      tab.setAttribute("aria-selected", String(isActive));
      tab.setAttribute("tabindex", isActive ? "0" : "-1");
    });
  }

  function setProgress(activePosition, total) {
    progressNodes.forEach((node, index) => {
      node.hidden = index >= total;
      node.classList.toggle("is-active", index === activePosition);
    });
  }

  function setCounter(activePosition, total) {
    currentNodes.forEach((node) => {
      node.textContent = padNumber(activePosition + 1);
    });

    totalNodes.forEach((node) => {
      node.textContent = padNumber(total);
    });
  }

  function setSlides(nextIndex) {
    slides.forEach((slide, index) => {
      const isActive = index === nextIndex;

      slide.classList.toggle("is-active", isActive);
      slide.setAttribute("aria-hidden", String(!isActive));
    });
  }

  function activePositionInFiltered(list) {
    const position = list.findIndex((slide) => {
      return Number(slide.dataset.feedbackIndex) === activeIndex;
    });

    return Math.max(position, 0);
  }

  function render(nextIndex) {
    activeIndex = nextIndex;

    const list = filteredSlides();
    const position = activePositionInFiltered(list);

    setTabs();
    setSlides(activeIndex);
    setCounter(position, list.length);
    setProgress(position, list.length);
  }

  function activateByPosition(position) {
    const list = filteredSlides();

    if (!list.length) return;

    const safePosition = (position + list.length) % list.length;
    const nextIndex = Number(list[safePosition].dataset.feedbackIndex);

    render(nextIndex);
  }

  function activateFilter(filter) {
    activeFilter = filter;

    const list = filteredSlides();
    const firstIndex = list.length ? Number(list[0].dataset.feedbackIndex) : 0;

    render(firstIndex);
  }

  function move(step) {
    const list = filteredSlides();
    const currentPosition = activePositionInFiltered(list);

    activateByPosition(currentPosition + step);
  }

  tabs.forEach((tab, index) => {
    tab.addEventListener("click", () => {
      activateFilter(tab.dataset.feedbackFilter);
    });

    tab.addEventListener("keydown", (event) => {
      if (!["ArrowRight", "ArrowLeft", "Home", "End"].includes(event.key)) {
        return;
      }

      event.preventDefault();

      if (event.key === "Home") {
        tabs[0].focus();
        activateFilter(tabs[0].dataset.feedbackFilter);
        return;
      }

      if (event.key === "End") {
        const lastTab = tabs[tabs.length - 1];

        lastTab.focus();
        activateFilter(lastTab.dataset.feedbackFilter);
        return;
      }

      const direction = event.key === "ArrowRight" ? 1 : -1;
      const nextIndex = (index + direction + tabs.length) % tabs.length;
      const nextTab = tabs[nextIndex];

      nextTab.focus();
      activateFilter(nextTab.dataset.feedbackFilter);
    });
  });

  prevButtons.forEach((button) => {
    button.addEventListener("click", () => {
      move(-1);
    });
  });

  nextButtons.forEach((button) => {
    button.addEventListener("click", () => {
      move(1);
    });
  });

  render(activeIndex);
})();

// (function () {
//   const fb = document.getElementById('feedback');
//   if (!fb) return;

//   const tabs   = Array.from(fb.querySelectorAll('.ks-fb-tab'));
//   const slides = Array.from(fb.querySelectorAll('.ks-fb-slide'));

//   const keyToIndex = {};
//   slides.forEach((s, i) => keyToIndex[s.getAttribute('data-key')] = i);

//   let active = slides.find(s => s.classList.contains('is-active')) || slides[0];
//   slides.forEach(s => { if (s !== active) s.classList.remove('is-active'); });
//   let activeIdx = keyToIndex[active.getAttribute('data-key')] ?? 0;

//   const markBootDone = () => fb.classList.add('fb-boot-done');
//   if (document.readyState === 'loading'){
//     document.addEventListener('DOMContentLoaded', markBootDone, { once: true });
//   } else {
//     markBootDone();
//   }

//   function animateContentIn(slide, dir){
//     const content = slide && slide.querySelector('.ks-fb-content');
//     if (!content) return;

//     fb.querySelectorAll('.ks-fb-content').forEach(el => {
//       if (el !== content) el.classList.remove('is-prep','is-anim','dir-down','dir-up');
//     });

//     content.classList.remove('is-anim','is-prep','dir-down','dir-up');
//     content.classList.add('is-prep', dir === 'up' ? 'dir-up' : 'dir-down');

//     void content.getBoundingClientRect();
//     requestAnimationFrame(() => {
//       content.classList.add('is-anim');
//       content.classList.remove('is-prep');
//     });
//   }

//   const boot = () => animateContentIn(active, 'down');
//   if (document.readyState === 'loading'){
//     document.addEventListener('DOMContentLoaded', boot, { once: true });
//   } else {
//     boot();
//   }

//   function activate(key){
//     const next = slides.find(s => s.getAttribute('data-key') === key);
//     if (!next || next === active) return;

//     const nextIdx = keyToIndex[key] ?? 0;
//     const dir = (nextIdx > activeIdx) ? 'down' : 'up';

//     next.classList.add('is-active');
//     animateContentIn(next, dir);

//     tabs.forEach(t => {
//       const isAct = t.getAttribute('data-key') === key;
//       t.classList.toggle('is-active', isAct);
//       t.setAttribute('aria-selected', String(isAct));
//       t.setAttribute('tabindex', isAct ? '0' : '-1');
//     });

//     if (active) active.classList.remove('is-active');
//     active = next;
//     activeIdx = nextIdx;
//   }

//   const tablist = fb.querySelector('.ks-fb-tabs');
//   if (tablist) tablist.setAttribute('role','tablist');
//   tabs.forEach((t) => {
//     const isAct = t.classList.contains('is-active');
//     t.setAttribute('role','tab');
//     t.setAttribute('aria-selected', String(isAct));
//     t.setAttribute('tabindex', isAct ? '0' : '-1');
//   });

//   tabs.forEach((t, i) => {
//     t.addEventListener('click', () => activate(t.getAttribute('data-key')));

//     t.addEventListener('keydown', (e) => {
//       if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
//         e.preventDefault();
//         const dir = e.key === 'ArrowRight' ? 1 : -1;
//         const n = (i + dir + tabs.length) % tabs.length;
//         tabs[n].focus();
//         activate(tabs[n].getAttribute('data-key'));
//       }
//       if (e.key === 'Home'){
//         e.preventDefault();
//         tabs[0].focus();
//         activate(tabs[0].getAttribute('data-key'));
//       }
//       if (e.key === 'End'){
//         e.preventDefault();
//         const last = tabs[tabs.length-1];
//         last.focus();
//         activate(last.getAttribute('data-key'));
//       }
//     });
//   });

//   function openFromHash(){
//     const k = location.hash.slice(1);
//     if (!k) return;
//     const btn = tabs.find(t => t.getAttribute('data-key') === k);
//     if (btn) activate(k);
//   }
//   window.addEventListener('hashchange', openFromHash);
//   openFromHash();
// })();
