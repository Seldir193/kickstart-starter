document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-membership-cancellation-form]");
  if (!form || !window.ksMembershipCancellation) return;

  const result = form.querySelector("[data-membership-cancellation-result]");
  const requestedWrap = form.querySelector("[data-requested-end-date-wrap]");
  const requestedDate = form.querySelector('input[name="requestedEndDate"]');
  const radios = form.querySelectorAll('input[name="terminationMode"]');

  function setResult(type, message, endDate = "") {
    if (!result) return;
    result.className = `membership-cancellation__result membership-cancellation__result--${type}`;
    result.classList.remove("membership-cancellation__result--hidden");
    result.innerHTML = endDate
      ? `<div>${message}</div><div class="membership-cancellation__end-date">Vertragsende: ${endDate}</div>`
      : `<div>${message}</div>`;
  }

  function formatDate(value) {
    if (!value) return "";
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return "";
    return new Intl.DateTimeFormat("de-DE").format(date);
  }

  function toggleRequestedDate() {
    const mode = form.querySelector(
      'input[name="terminationMode"]:checked',
    )?.value;
    const show = mode === "requested";

    requestedWrap?.classList.toggle(
      "membership-cancellation__field--hidden",
      !show,
    );
    if (requestedDate) {
      requestedDate.required = show;
      if (!show) requestedDate.value = "";
    }
  }

  radios.forEach((radio) => {
    radio.addEventListener("change", toggleRequestedDate);
  });

  toggleRequestedDate();

  form.addEventListener("submit", async (event) => {
    event.preventDefault();

    const submitButton = form.querySelector('button[type="submit"]');
    const formData = new FormData(form);

    const payload = {
      parentEmail: String(formData.get("parentEmail") || "").trim(),
      referenceNo: String(formData.get("referenceNo") || "").trim(),
      childFirstName: String(formData.get("childFirstName") || "").trim(),
      childLastName: String(formData.get("childLastName") || "").trim(),
      childBirthDate: String(formData.get("childBirthDate") || "").trim(),
      terminationMode: String(formData.get("terminationMode") || "").trim(),
      requestedEndDate: String(formData.get("requestedEndDate") || "").trim(),
    };

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "Wird verarbeitet ...";
    }

    setResult("loading", ksMembershipCancellation.labels.loading);

    try {
      const response = await fetch(ksMembershipCancellation.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      const message =
        data?.message || data?.error || ksMembershipCancellation.labels.error;

      if (!response.ok || !data?.ok) {
        setResult(
          "error",
          message,
          formatDate(data?.cancelEffectiveAt || data?.earliestEndDate),
        );
        return;
      }

      form.reset();
      toggleRequestedDate();
      setResult("success", message, formatDate(data?.cancelEffectiveAt));
    } catch {
      setResult("error", "Serverfehler bei der Kündigung.");
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = "Mitgliedschaft kündigen";
      }
    }
  });
});
