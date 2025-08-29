// @ts-nocheck
(function () {
  'use strict';

  function qs(root, sel) { return (root || document).querySelector(sel); }

  document.addEventListener('DOMContentLoaded', function () {
    // Toggle-Ziel: <li class="ks-programs-toggle"><a>PROGRAMS</a></li>
    var toggle =
      qs(document, '.menu .ks-programs-toggle > a') ||
      qs(document, '.menu .ks-programs-toggle');

    // Wrapper: <div class="ks-programs" data-mega>…</div>
    var wrap = qs(document, '[data-mega].ks-programs');

    if (!toggle || !wrap) return;

    var backdrop = qs(wrap, '.ks-programs__backdrop');
    var panel    = qs(wrap, '.ks-programs__panel');

    // ARIA setup
    toggle.setAttribute('role', 'button');
    toggle.setAttribute('aria-expanded', 'false');
    if (panel) {
      var pid = 'mega-programs-panel';
      panel.id = pid;
      toggle.setAttribute('aria-controls', pid);
    }

    var isOpen = false;

    function openPanel() {
      if (isOpen) return;
      isOpen = true;
      wrap.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');

      // Fokus ins Panel (erstes fokussierbares Element)
      if (panel) {
        var first = panel.querySelector('a,button,input,select,textarea,[tabindex]');
        if (first && first.focus) {
          try { first.focus({ preventScroll: true }); } catch (e) { /* noop */ }
        }
      }
    }

    function closePanel() {
      if (!isOpen) return;
      isOpen = false;
      wrap.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
    }

    function togglePanel(ev) {
      if (ev && ev.preventDefault) ev.preventDefault();
      if (isOpen) closePanel(); else openPanel();
    }

    // Klick auf den Menü-Button
    toggle.addEventListener('click', togglePanel);

    // Klick auf Backdrop schließt
    if (backdrop) {
      backdrop.addEventListener('click', closePanel);
    }

    // Outside-Click schließt
    document.addEventListener('pointerdown', function (e) {
      var t = e.target;
      if (!t) return;
      if (wrap.contains(t)) return;   // Klick IN der Mega
      if (toggle.contains(t)) return; // Klick am Button
      closePanel();
    });

    // ESC schließt
    document.addEventListener('keydown', function (e) {
      if (e && e.key === 'Escape') closePanel();
    });

    // Link-Klicks im Panel schließen ebenfalls
    if (panel) {
      panel.addEventListener('click', function (e) {
        var a = (e && e.target && e.target.closest) ? e.target.closest('a') : null;
        if (a) closePanel();
      });
    }
  });
})();
















