// assets/js/book-dialog.js
(function () {
  "use strict";

  /* ========== tiny helpers (nur für diesen Dialog) ========== */
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const esc = (s) =>
    String(s).replace(
      /[&<>"']/g,
      (m) =>
        ({
          "&": "&amp;",
          "<": "&lt;",
          ">": "&gt;",
          '"': "&quot;",
          "'": "&#39;",
        }[m])
    );

  /* ========== body lock (eigenständig, aber gleich wie im Offers-Dialog) ========== */
  const LOCK_ATTR = "data-ks-modal-lock";
  const lockBody = () => {
    if (!document.body.hasAttribute(LOCK_ATTR)) {
      document.body.setAttribute(LOCK_ATTR, document.body.style.overflow || "");
      document.body.style.overflow = "hidden";
    }
    document.body.classList.add("ks-modal-open");
  };

  const unlockBody = () => {
    if (document.body.hasAttribute(LOCK_ATTR)) {
      document.body.style.overflow = document.body.getAttribute(LOCK_ATTR);
      document.body.removeAttribute(LOCK_ATTR);
    }
    document.body.classList.remove("ks-modal-open");
  };

  /* ========== BOOKING DIALOG (iframe) ========== */
  const BookDialog = (() => {
    function ensure() {
      let modal = $("#ksBookModal");

      if (!modal) {
        // Neu erstellen
        modal = document.createElement("div");
        modal.id = "ksBookModal";
        modal.className = "ks-book-modal";
        modal.hidden = true;

        modal.innerHTML = `
          <div class="ks-book-modal__overlay" data-close></div>
          <div class="ks-book-modal__panel"
               role="dialog"
               aria-modal="true"
               aria-label="Buchung">

            <button type="button"
                    class="ks-book-back"
                    data-back
                    aria-label="Zurück"
                    title="Zurück">
              <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <path d="M15 18l-6-6 6-6"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"></path>
              </svg>
            </button>

            <button type="button"
                    class="ks-dir__close"
                    data-close
                    aria-label="Schließen">✕</button>

            <iframe class="ks-book-modal__frame ks-book__frame"
                    src=""
                    title="Buchung"
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"></iframe>
          </div>`;
        document.body.appendChild(modal);
      } else {
        // Bestehendes Markup "aufrüsten"
        modal.classList.add("ks-book-modal");

        let overlay =
          $(".ks-book-modal__overlay", modal) || $(".ks-dir__overlay", modal);
        if (!overlay) {
          overlay = document.createElement("div");
          overlay.className = "ks-book-modal__overlay";
          overlay.setAttribute("data-close", "");
          modal.prepend(overlay);
        } else {
          overlay.classList.add("ks-book-modal__overlay");
        }

        let panel =
          $(".ks-book-modal__panel", modal) || $(".ks-dir__panel", modal);
        if (!panel) {
          panel = document.createElement("div");
          panel.className = "ks-book-modal__panel";
          modal.appendChild(panel);
        } else {
          panel.classList.add("ks-book-modal__panel");
        }

        let frame =
          $(".ks-book-modal__frame", modal) || $(".ks-book__frame", modal);
        if (!frame) {
          frame = document.createElement("iframe");
          frame.className = "ks-book-modal__frame ks-book__frame";
          frame.title = "Buchung";
          frame.loading = "lazy";
          frame.referrerPolicy = "no-referrer-when-downgrade";
          panel.appendChild(frame);
        } else {
          frame.classList.add("ks-book-modal__frame");
        }

        if (!$(".ks-dir__close", modal)) {
          const btn = document.createElement("button");
          btn.type = "button";
          btn.className = "ks-dir__close";
          btn.setAttribute("data-close", "");
          btn.setAttribute("aria-label", "Schließen");
          btn.textContent = "✕";
          panel.prepend(btn);
        }

        if (!$(".ks-book-back", modal)) {
          const back = document.createElement("button");
          back.type = "button";
          back.className = "ks-book-back";
          back.setAttribute("data-back", "");
          back.setAttribute("aria-label", "Zurück");
          back.setAttribute("title", "Zurück");
          back.innerHTML = `
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
              <path d="M15 18l-6-6 6-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"></path>
            </svg>`;
          panel.prepend(back);
        }
      }

      return modal;
    }

    function open(url, opts = {}) {
      const modal = ensure();

      const overlay =
        $(".ks-book-modal__overlay", modal) || $(".ks-dir__overlay", modal);
      const panel =
        $(".ks-book-modal__panel", modal) || $(".ks-dir__panel", modal);
      const frame =
        $(".ks-book-modal__frame", modal) || $(".ks-book__frame", modal);
      const closeBtn = $(".ks-dir__close", modal);

      // Zustand vom vorherigen Öffnen zurücksetzen
      if (panel) {
        panel.classList.remove("ks-ready", "ks-prep");
        panel.style.maxHeight = "";
      }
      if (frame) {
        frame.style.height = "0px";
      }
      if (modal) {
        delete modal.dataset.ksHeightApplied;
      }

      // Close-Icon setzen
      const root = $("#ksDir");
      const icon =
        opts.closeIcon ||
        root?.dataset?.closeIcon ||
        root?.dataset?.closeicon ||
        "";

      if (icon && closeBtn) {
        closeBtn.innerHTML = `<img src="${esc(
          icon
        )}" alt="Schließen" width="14" height="14">`;
      } else if (closeBtn) {
        closeBtn.textContent = "✕";
      }

      // iFrame-URL setzen
      if (frame) {
        frame.src = url || "#";
      }

      // Modal sichtbar machen (Overlay + Panel)
      modal.hidden = false;
      if (panel) {
        panel.classList.add("ks-prep");
        requestAnimationFrame(() => {
          panel.classList.remove("ks-prep");
        });
      }

      lockBody();

      // Falls wir bereits eine gute Höhe gespeichert haben → direkt nutzen
      if (panel && frame) {
        let cached = 0;
        try {
          cached = Number(
            window.localStorage.getItem("ksBookLastHeight") || "0"
          );
        } catch (err) {
          cached = 0;
        }

        if (Number.isFinite(cached) && cached > 0) {
          const viewport =
            window.innerHeight || document.documentElement.clientHeight || 600;

          // Obergrenze abhängig vom Viewport
          const maxPanelHeight = Math.max(viewport - 160, 460);
          const frameHeight = Math.min(cached, maxPanelHeight);

          panel.style.maxHeight = maxPanelHeight + "px";
          frame.style.height = frameHeight + "px";

          panel.classList.add("ks-ready");
          modal.dataset.ksHeightApplied = "1";
        }
      }

      const reopenOffersIfPossible = () => {
        if (
          window.KSOffersDialog &&
          window.KSOffersDialog.__last &&
          window.KSOffersDialog.__last.offer
        ) {
          const { offer, sessions, opts } = window.KSOffersDialog.__last;
          window.KSOffersDialog.open(offer, sessions, opts || {});
        }
      };

      // Schließen-Logik
      const doClose = () => {
        const overlayNow =
          $(".ks-book-modal__overlay", modal) || $(".ks-dir__overlay", modal);
        const panelNow =
          $(".ks-book-modal__panel", modal) || $(".ks-dir__panel", modal);
        const frameNow =
          $(".ks-book-modal__frame", modal) || $(".ks-book__frame", modal);

        modal.hidden = true;

        if (panelNow) {
          panelNow.classList.remove("ks-ready", "ks-prep");
          panelNow.style.maxHeight = "";
        }
        if (frameNow) {
          frameNow.style.height = "0px";
          frameNow.src = "about:blank";
        }

        delete modal.dataset.ksHeightApplied;

        const handlers = modal.__ksBookHandlers;
        if (handlers) {
          overlayNow &&
            overlayNow.removeEventListener("click", handlers.onOverlay);
          modal.removeEventListener("click", handlers.onAny);
          document.removeEventListener("keydown", handlers.onEsc);
          modal.__ksBookHandlers = null;
        }

        unlockBody();
      };

      const onOverlay = () => doClose();
      const onAny = (e) => {
        if (e.target.closest("[data-back]")) {
          doClose();
          reopenOffersIfPossible();
          return;
        }
        if (e.target.closest("[data-close]")) {
          doClose();
        }
      };
      const onEsc = (e) => {
        if (e.key === "Escape") {
          doClose();
        }
      };

      overlay && overlay.addEventListener("click", onOverlay);
      modal.addEventListener("click", onAny);
      document.addEventListener("keydown", onEsc);

      modal.__ksBookHandlers = { onOverlay, onAny, onEsc };
    }

    function close() {
      const modal = $("#ksBookModal");
      if (!modal || modal.hidden) return;

      const overlay =
        $(".ks-book-modal__overlay", modal) || $(".ks-dir__overlay", modal);
      const panel =
        $(".ks-book-modal__panel", modal) || $(".ks-dir__panel", modal);
      const frame =
        $(".ks-book-modal__frame", modal) || $(".ks-book__frame", modal);

      modal.hidden = true;

      if (panel) {
        panel.classList.remove("ks-ready", "ks-prep");
        panel.style.maxHeight = "";
      }
      if (frame) {
        frame.style.height = "0px";
        frame.src = "about:blank";
      }

      delete modal.dataset.ksHeightApplied;

      const handlers = modal.__ksBookHandlers;
      if (handlers) {
        overlay && overlay.removeEventListener("click", handlers.onOverlay);
        modal.removeEventListener("click", handlers.onAny);
        document.removeEventListener("keydown", handlers.onEsc);
        modal.__ksBookHandlers = null;
      }

      unlockBody();
    }

    return { open, close };
  })();

  // global verfügbar machen
  window.BookDialog = BookDialog;

  /* ========== BACK & HEIGHT from embedded booking (Next) ========== */
  window.addEventListener(
    "message",
    (e) => {
      const d = e && e.data;
      if (!d || !d.type) return;

      /* ---- dynamische Höhe aus Next.js Booking ---- */
      if (d.type === "KS_BOOKING_HEIGHT") {
        const modal = $("#ksBookModal");
        if (!modal || modal.hidden) return;

        // Nur das ERSTE Height-Event pro Öffnen verwenden
        if (modal.dataset.ksHeightApplied === "1") return;

        const panel =
          $(".ks-book-modal__panel", modal) || $(".ks-dir__panel", modal);
        const frame =
          $(".ks-book-modal__frame", modal) || $(".ks-book__frame", modal);

        if (!panel || !frame) return;
        if (!frame.src || frame.src === "about:blank") return;

        let contentHeight = Number(d.height);
        if (!Number.isFinite(contentHeight) || contentHeight <= 0) return;

        const viewport =
          window.innerHeight || document.documentElement.clientHeight || 600;

        const maxPanelHeight = Math.max(viewport - 160, 460);
        const frameHeight = Math.min(contentHeight, maxPanelHeight);

        panel.style.maxHeight = maxPanelHeight + "px";
        frame.style.height = frameHeight + "px";

        // Höhe für zukünftige Öffnungen merken
        try {
          window.localStorage.setItem(
            "ksBookLastHeight",
            String(contentHeight)
          );
        } catch (err) {}

        panel.classList.add("ks-ready");
        modal.dataset.ksHeightApplied = "1";

        return;
      }

      /* --- Zurück / Schließen vom iFrame --- */
      if (d.type !== "KS_BOOKING_BACK" && d.type !== "KS_BOOKING_CLOSE") {
        return;
      }

      BookDialog.close();

      if (
        d.type === "KS_BOOKING_BACK" &&
        window.KSOffersDialog &&
        window.KSOffersDialog.__last &&
        window.KSOffersDialog.__last.offer
      ) {
        const { offer, sessions, opts } = window.KSOffersDialog.__last;
        window.KSOffersDialog.open(offer, sessions, opts || {});
      }
    },
    false
  );
})();
