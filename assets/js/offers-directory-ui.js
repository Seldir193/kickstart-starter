(function () {
  "use strict";

  let onOutsidePointerDown = null;

  function enhanceFilterSelects(root) {
    if (!root) return;

    const selects = Array.from(root.querySelectorAll("[data-filters] select"));
    if (!selects.length) return;

    const closeAll = () => {
      root.querySelectorAll(".ks-dir-dd.is-open").forEach((dd) => {
        dd.classList.remove("is-open");
        dd.setAttribute("aria-expanded", "false");
        const btn = dd.querySelector(".ks-dir-dd__btn");
        if (btn) btn.setAttribute("aria-expanded", "false");
        const panel = dd.querySelector(".ks-dir-dd__panel");
        if (panel) panel.innerHTML = "";
      });
    };

    selects.forEach((nativeSel) => {
      if (nativeSel.dataset.enhanced === "1") return;
      nativeSel.dataset.enhanced = "1";

      const wrapLabel = nativeSel.closest("label.ks-field");
      const control = wrapLabel?.querySelector(".ks-field__control--select");
      if (!wrapLabel || !control) return;

      const iconImg = control.querySelector(".ks-field__icon img");
      const caretSrc = iconImg?.getAttribute("src") || "";

      nativeSel.classList.add("ks-dir-native-select");
      nativeSel.tabIndex = -1;
      nativeSel.setAttribute("aria-hidden", "true");

      const dd = document.createElement("div");
      dd.className = "ks-dir-dd";
      dd.setAttribute("aria-expanded", "false");

      dd.innerHTML = `
        <button type="button" class="ks-dir-dd__btn" aria-expanded="false">
          <span class="ks-dir-dd__label"></span>
          <span class="ks-dir-dd__caret" aria-hidden="true">
            ${caretSrc ? `<img src="${caretSrc}" alt="">` : ""}
          </span>
        </button>
        <div class="ks-dir-dd__panel" role="listbox"></div>
      `;

      control.classList.add("is-enhanced");
      control.innerHTML = "";
      control.appendChild(dd);
      control.appendChild(nativeSel);

      const btn = dd.querySelector(".ks-dir-dd__btn");
      const label = dd.querySelector(".ks-dir-dd__label");
      const panel = dd.querySelector(".ks-dir-dd__panel");

      function syncLabel() {
        const opt = nativeSel.selectedOptions?.[0];
        label.textContent = opt ? opt.textContent : "Bitte auswählen …";
      }

      function buildPanel() {
        panel.innerHTML = "";
        Array.from(nativeSel.options).forEach((opt) => {
          const item = document.createElement("div");
          item.className = "ks-dir-dd__option";
          item.setAttribute("role", "option");
          item.setAttribute("tabindex", "-1");
          item.setAttribute("data-value", opt.value);
          item.textContent = opt.textContent;
          if (opt.selected) item.setAttribute("aria-selected", "true");
          panel.appendChild(item);
        });

        const sel = panel.querySelector(
          '.ks-dir-dd__option[aria-selected="true"]'
        );
        const first = panel.querySelector(".ks-dir-dd__option");
        (sel || first)?.focus({ preventScroll: true });
      }

      function closeDD() {
        dd.classList.remove("is-open");
        dd.setAttribute("aria-expanded", "false");
        btn?.setAttribute("aria-expanded", "false");
        panel.innerHTML = "";

        if (onOutsidePointerDown) {
          document.removeEventListener(
            "pointerdown",
            onOutsidePointerDown,
            true
          );
          onOutsidePointerDown = null;
        }

        try {
          btn?.focus({ preventScroll: true });
        } catch {}
      }

      function openDD() {
        closeAll();
        buildPanel();
        dd.classList.add("is-open");
        dd.setAttribute("aria-expanded", "true");
        btn?.setAttribute("aria-expanded", "true");

        onOutsidePointerDown = (e) => {
          if (!dd.contains(e.target)) closeDD();
        };

        document.addEventListener("pointerdown", onOutsidePointerDown, true);

        document.addEventListener(
          "keydown",
          (e) => {
            if (e.key === "Escape") closeDD();
          },
          { once: true }
        );
      }

      btn?.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        dd.classList.contains("is-open") ? closeDD() : openDD();
      });

      panel.addEventListener("click", (e) => {
        const item = e.target.closest(".ks-dir-dd__option");
        if (!item) return;

        nativeSel.value = item.getAttribute("data-value") ?? "";
        syncLabel();

        panel
          .querySelectorAll(".ks-dir-dd__option")
          .forEach((x) => x.removeAttribute("aria-selected"));
        item.setAttribute("aria-selected", "true");

        nativeSel.dispatchEvent(new Event("change", { bubbles: true }));
        closeDD();
      });

      nativeSel.addEventListener("change", () => {
        syncLabel();
        if (dd.classList.contains("is-open")) buildPanel();
      });

      syncLabel();
    });

    // erst nach dem Enhancen sichtbar machen
    root.classList.add("is-ready");

    // global outside closer (1x)
    if (!root.dataset.ddOutsideBound) {
      root.dataset.ddOutsideBound = "1";
      document.addEventListener("pointerdown", (e) => {
        if (!root.contains(e.target)) closeAll();
      });
    }
  }

  window.KSOffersDirectoryUI = { enhanceFilterSelects };
})();
