// assets/js/ks-whatsapp-locations.js
(function () {
  "use strict";

  const MAX_VISIBLE_ROWS = 2;

  const qs = (sel, ctx) => (ctx || document).querySelector(sel);
  const qsa = (sel, ctx) => Array.from((ctx || document).querySelectorAll(sel));

  function setDisabled(btn, disabled) {
    if (!btn) return;
    btn.disabled = !!disabled;
    btn.setAttribute("aria-disabled", disabled ? "true" : "false");
  }

  function setMaxVisibleRows(panel, rows) {
    if (!panel) return;

    // Panel muss sichtbar sein, sonst ist height = 0
    const first = panel.querySelector(".ks-dd__option");
    let rowH = 44;

    if (first) {
      const h = first.getBoundingClientRect().height;
      if (h > 0) rowH = Math.round(h);
    }

    panel.style.maxHeight = String(rowH * rows) + "px";
    panel.style.overflowY = "auto";
  }

  function syncSelected(panel, selectedPhone) {
    if (!panel) return null;

    const opts = qsa(".ks-dd__option", panel);
    opts.forEach((o) => o.removeAttribute("aria-selected"));

    if (!selectedPhone) return null;

    const match =
      opts.find((o) => (o.dataset.phone || "") === selectedPhone) || null;

    if (match) match.setAttribute("aria-selected", "true");
    return match;
  }

  function scrollIntoViewInPanel(panel, el) {
    if (!panel || !el) return;

    const r = el.getBoundingClientRect();
    const pr = panel.getBoundingClientRect();

    if (r.top < pr.top) panel.scrollTop -= (pr.top - r.top);
    if (r.bottom > pr.bottom) panel.scrollTop += (r.bottom - pr.bottom);
  }

  function init(form) {
    const sel = qs('select[name="wa_location"]', form);
    const actionBtn = qs(".ks-wa-btn", form);

    const dd = qs(".ks-dd", form);
    const openBtn = dd ? qs(".ks-dd__btn", dd) : null;
    const panel = dd ? qs(".ks-dd__panel", dd) : null;
    const labelEl = dd ? qs(".ks-dd__label", dd) : null;

    if (!sel || !actionBtn || !dd || !openBtn || !panel || !labelEl) return;

    // Text & Campaign aus data-Attributen (wie wir es machen wollten)
    const text = form.getAttribute("data-wa-text") || "";
    const campaign = form.getAttribute("data-wa-campaign") || "whatsapp_locations";

    // Initial disabled
    setDisabled(actionBtn, true);

    function syncButtonState() {
      setDisabled(actionBtn, !sel.value);
    }

    // Wenn Select Wert hat -> Button aktiv
    sel.addEventListener("change", syncButtonState);
    syncButtonState();

    // --- WICHTIG: wenn Dropdown aufgeht -> max 2 rows + selected dunkel setzen + dahin scrollen
    // (ks-dropdown.js macht open/close, wir reagieren nur)
    function onOpenTick() {
      // erst nach dem Öffnen messen (Panel sichtbar)
      requestAnimationFrame(() => {
        setMaxVisibleRows(panel, MAX_VISIBLE_ROWS);

        const match = syncSelected(panel, sel.value);
        if (match) {
          // optional: Fokus auf selected (damit Keyboard direkt dort startet)
          try { match.focus({ preventScroll: true }); } catch (e) {}
          scrollIntoViewInPanel(panel, match);
        }
      });
    }

    // Triggern wenn Button geklickt wird (ks-dropdown.js toggelt dann)
    openBtn.addEventListener("click", () => {
      // wenn gleich geöffnet -> tick
      setTimeout(() => {
        const expanded = dd.getAttribute("aria-expanded") === "true";
        if (expanded) onOpenTick();
      }, 0);
    });

    // Fallback: wenn globaler JS NICHT change dispatcht oder aria-selected nicht setzt
    // -> beim Klick im Panel minimal nachziehen
    panel.addEventListener("click", (e) => {
      const opt = e.target.closest(".ks-dd__option");
      if (!opt) return;

      const phone = opt.dataset.phone || "";
      if (!phone) return;

      // 1) Selected dunkel (global CSS greift über [aria-selected="true"])
      qsa(".ks-dd__option", panel).forEach((x) => x.removeAttribute("aria-selected"));
      opt.setAttribute("aria-selected", "true");

      // 2) Select sync
      sel.value = phone;
      sel.dispatchEvent(new Event("change", { bubbles: true }));

      // 3) Label sync (damit immer korrekt)
      const lbl = opt.dataset.label || opt.textContent || "";
      labelEl.textContent = lbl;

      // 4) Fokus auf gewähltes Element (für “dunkel” + saubere Tastatur)
      try { opt.focus({ preventScroll: true }); } catch (e) {}
    });

    // WhatsApp öffnen
    actionBtn.addEventListener("click", () => {
      if (actionBtn.disabled) return;

      const phone = sel.value || "";
      if (!phone) return;

      const slug = sel.selectedOptions?.[0]?.dataset?.slug || "standort";

      const msg = encodeURIComponent(text);
      const utm =
        "utm_source=website&utm_medium=cta&utm_campaign=" +
        encodeURIComponent(campaign + "_" + slug);

      window.open("https://wa.me/" + phone + "?text=" + msg + "&" + utm, "_blank");
    });
  }

  document.addEventListener("DOMContentLoaded", () => {
    qsa('form.ks-wa-form[data-ks-wa="1"]').forEach(init);
  });
})();







