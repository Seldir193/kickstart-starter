document.addEventListener("DOMContentLoaded", () => {
  const form = document.querySelector("[data-withdrawal-form]");
  if (!form || !window.ksWithdrawal) return;

  const result = form.querySelector("[data-withdrawal-result]");

  function setResult(type, message) {
    if (!result) return;
    result.className = `withdrawal__result withdrawal__result--${type}`;
    result.classList.remove("withdrawal__result--hidden");
    result.innerHTML = `<div>${message}</div>`;
  }

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
      reason: "Widerruf innerhalb von 14 Tagen",
    };

    if (submitButton) {
      submitButton.disabled = true;
      submitButton.textContent = "Wird verarbeitet ...";
    }

    setResult("loading", ksWithdrawal.labels.loading);

    try {
      const response = await fetch(ksWithdrawal.endpoint, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      const data = await response.json();
      const message = data?.message || data?.error || ksWithdrawal.labels.error;

      if (!response.ok || !data?.ok) {
        setResult("error", message);
        return;
      }

      form.reset();
      setResult("success", message);
    } catch {
      setResult("error", "Serverfehler beim Widerruf.");
    } finally {
      if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = "Vertrag widerrufen";
      }
    }
  });
});
