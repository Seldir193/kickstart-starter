(function () {
  function ready(fn) {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", fn);
      return;
    }
    fn();
  }

  function safeJson(res) {
    return res
      .text()
      .then(function (t) {
        try {
          return JSON.parse(t);
        } catch (e) {
          return { ok: false, error: t || "Unbekannte Serverantwort." };
        }
      })
      .then(function (j) {
        if (j === 0 || j === "0")
          return {
            ok: false,
            error: "AJAX-Handler nicht registriert (wp_ajax_...).",
            raw: j,
          };
        return j;
      });
  }

  ready(function () {
    var scope = document.querySelector("#kontakt");
    if (!scope) return;

    var form = scope.querySelector("form[data-ks-contact-form='1']");
    if (!form) return;

    var successBox = form.querySelector("#ks-form-alert-success");
    var errorBox = form.querySelector("#ks-form-alert-error");
    var submitBtn = form.querySelector("button[type='submit']");

    function showSuccess(msg) {
      if (!successBox) return;
      successBox.textContent = msg || "";
      successBox.hidden = !msg;
      if (errorBox) errorBox.hidden = true;
    }

    function showError(msg) {
      if (!errorBox) return;
      errorBox.textContent = msg || "";
      errorBox.hidden = !msg;
      if (successBox) successBox.hidden = true;
    }

    // ✅ Server-Fallback Box (.ok mit ?sent=1) in Profi-Box übernehmen + URL reinigen
    var okBox = scope.querySelector(".ok[data-auto-hide='1']");
    if (okBox) {
      var msg = (okBox.textContent || "").trim();
      if (msg) showSuccess(msg);

      if (okBox.parentNode) okBox.parentNode.removeChild(okBox);

      try {
        var url = new URL(window.location.href);
        url.searchParams.delete("sent");
        window.history.replaceState({}, "", url);
      } catch (e) {}

      window.setTimeout(function () {
        showSuccess("");
      }, 6000);
    }

    // Validation (Warnings unter Inputs)
    var rules = [
      { id: "name", requiredMsg: "Dies ist ein Pflichtfeld." },
      {
        id: "email",
        requiredMsg: "Dies ist ein Pflichtfeld.",
        invalidMsg: "Die eingegebene E-Mail-Adresse ist ungültig.",
      },
      { id: "message", requiredMsg: "Dies ist ein Pflichtfeld." },
    ];

    function getField(input) {
      return input ? input.closest(".field") : null;
    }

    function getErrorEl(input) {
      var field = getField(input);
      if (!field) return null;
      return field.querySelector(".ks-field-error");
    }

    function setError(input, msg) {
      var err = getErrorEl(input);
      if (err) err.textContent = msg || "";

      if (msg) {
        input.classList.add("is-invalid");
        input.setAttribute("aria-invalid", "true");
      } else {
        input.classList.remove("is-invalid");
        input.removeAttribute("aria-invalid");
      }
    }

    function validateOne(input, conf) {
      if (!input) return true;

      if (input.validity && input.validity.valueMissing) {
        setError(input, conf.requiredMsg);
        return false;
      }

      if (
        input.type === "email" &&
        input.validity &&
        input.validity.typeMismatch
      ) {
        setError(input, conf.invalidMsg || conf.requiredMsg);
        return false;
      }

      setError(input, "");
      return true;
    }

    function validateAll() {
      var ok = true;

      rules.forEach(function (conf) {
        var input = form.querySelector("#" + conf.id);
        if (!validateOne(input, conf)) ok = false;
      });

      return ok;
    }

    rules.forEach(function (conf) {
      var input = form.querySelector("#" + conf.id);
      if (!input) return;

      function live() {
        if (!form.classList.contains("was-validated")) return;
        validateOne(input, conf);
      }

      input.addEventListener("input", live);
      input.addEventListener("blur", live);
    });

    // ✅ Submit: Validieren + AJAX senden (kein Reload, kein Scroll)
    form.addEventListener("submit", function (e) {
      form.classList.add("was-validated");
      showError("");
      showSuccess("");

      if (!validateAll()) {
        e.preventDefault();
        e.stopPropagation();
        var first = form.querySelector(".is-invalid");
        if (first) first.focus();
        return;
      }

      var ajaxUrl = form.getAttribute("data-ajax-url");
      var ajaxAction = form.getAttribute("data-ajax-action");

      // Wenn AJAX nicht konfiguriert ist → normaler Submit (Fallback)
      if (!ajaxUrl || !ajaxAction) return;

      // ✅ Kein Reload → kein Scroll
      e.preventDefault();

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.setAttribute("aria-busy", "true");
      }

      showSuccess("Nachricht wird gesendet…");

      var fd = new FormData(form);
      // wichtig: action für admin-ajax überschreiben
      fd.set("action", ajaxAction);

      fetch(ajaxUrl, {
        method: "POST",
        body: fd,
        credentials: "same-origin",
      })
        .then(function (res) {
          return safeJson(res).then(function (json) {
            return { status: res.status, json: json };
          });
        })
        .then(function (pack) {
          var json = pack.json || {};
          if (!json.ok) {
            showSuccess("");
            showError(
              json.error || "Senden fehlgeschlagen. Bitte erneut versuchen."
            );
            return;
          }

          showError("");
          showSuccess(
            json.message || "Vielen Dank! Ihre Nachricht wurde gesendet."
          );
          form.reset();
          form.classList.remove("was-validated");

          window.setTimeout(function () {
            showSuccess("");
          }, 6000);
        })
        .catch(function () {
          showSuccess("");
          showError("Senden fehlgeschlagen. Bitte erneut versuchen.");
        })
        .finally(function () {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.removeAttribute("aria-busy");
          }
        });
    });
  });
})();
