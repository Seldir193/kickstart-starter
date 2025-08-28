

















(function () {
  function ready(fn){document.readyState==='loading'?document.addEventListener('DOMContentLoaded',fn):fn();}

  ready(function () {
    const toggle = document.querySelector('.ks-programs-toggle');                 // <a>
    const wrap   = document.querySelector('[data-mega].ks-programs');            // <li>
    if (!toggle || !wrap) return;

    const panel    = wrap.querySelector('.ks-programs__panel');
    const backdrop = wrap.querySelector('.ks-programs__backdrop');

    // ARIA
    toggle.setAttribute('role', 'button');
    toggle.setAttribute('aria-expanded', 'false');
    if (panel) {
      const pid = panel.id || 'mega-programs-panel';
      panel.id = pid;
      toggle.setAttribute('aria-controls', pid);
      panel.setAttribute('aria-hidden', 'true');
    }
    backdrop && backdrop.setAttribute('aria-hidden', 'true');

    const isOpen = () => wrap.classList.contains('is-open');

    function openPanel() {
      wrap.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      panel && panel.setAttribute('aria-hidden', 'false');
      backdrop && backdrop.setAttribute('aria-hidden', 'false');
      armOutsideClose();
    }

    function closePanel() {
      wrap.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      panel && panel.setAttribute('aria-hidden', 'true');
      backdrop && backdrop.setAttribute('aria-hidden', 'true');
      disarmOutsideClose();
    }

    function togglePanel(e) {
      e.preventDefault();
      isOpen() ? closePanel() : openPanel();
    }

    // Toggle
    toggle.addEventListener('click', togglePanel);
    toggle.addEventListener('keydown', (e) => {
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); togglePanel(e); }
      if (e.key === 'Escape') closePanel();
    });

    // Backdrop closes
    backdrop && backdrop.addEventListener('click', closePanel);

    // Outside click (capture) — closes unless inside toggle or inside panel
    let outsideHandler = null;
    function armOutsideClose() {
      setTimeout(() => {
        if (outsideHandler) return;
        outsideHandler = (e) => {
          if (!isOpen()) return;
          const t = e.target instanceof Element ? e.target : null;
          if (t && (t.closest('.ks-programs-toggle') || t.closest('.ks-programs__panel'))) return;
          closePanel();
        };
        document.addEventListener('pointerdown', outsideHandler, true);
      }, 0);
    }
    function disarmOutsideClose() {
      if (!outsideHandler) return;
      document.removeEventListener('pointerdown', outsideHandler, true);
      outsideHandler = null;
    }

    // ESC closes
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closePanel(); });

    // Optional: close on scroll
    window.addEventListener('scroll', () => { if (isOpen()) closePanel(); }, { passive: true });
  });
})();
