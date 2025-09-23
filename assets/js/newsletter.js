(function () {
  const API = (window.KS_NEWS && KS_NEWS.api) || 'http://127.0.0.1:5000/api/public/newsletter';
  const form = document.getElementById('ksNewsletterForm');
  if (!form) return;

  const emailInput = form.querySelector('input[name="email"]');
  const honeypot   = form.querySelector('input[name="website"]'); // hidden
  const button     = form.querySelector('button[type="submit"]');
  const msgBox     = document.getElementById('ksNewsletterMsg');

  let hideTimer = null;
  function showMsg(text, ok) {
    if (!msgBox) return;
    msgBox.textContent = text;
    msgBox.className = 'ks-news-msg ' + (ok ? 'is-ok' : 'is-err');
    msgBox.style.display = 'block';
    if (hideTimer) clearTimeout(hideTimer);
    hideTimer = setTimeout(() => { msgBox.style.display = 'none'; }, 5000); // ← 5 Sekunden
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const email = (emailInput.value || '').trim();
    if (!email) {
      showMsg('Bitte E-Mail eingeben.', false);
      return;
    }

    // Button sperren
    const prev = button.textContent;
    button.disabled = true;
    button.textContent = 'Senden…';

    try {
      const r = await fetch(API, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          email,
          website: honeypot ? honeypot.value : '' // Honeypot
        })
      });
      const data = await r.json().catch(() => ({}));

      if (!r.ok) {
        showMsg('Leider fehlgeschlagen. Bitte später erneut versuchen.', false);
      } else {
        // Bei DOI sagen wir „Bitte E-Mail bestätigen“
        showMsg('Fast geschafft! Bitte bestätige deine E-Mail in der soeben gesendeten Nachricht.', true);
        form.reset();
      }
    } catch {
      showMsg('Verbindungsfehler. Bitte später erneut versuchen.', false);
    } finally {
      button.disabled = false;
      button.textContent = prev;
    }
  });

  // Optional: Status aus Query (nach Redirects)
  const p = new URLSearchParams(location.search);
  const status = p.get('newsletter');
  if (status === 'confirmed')       showMsg('Danke! Deine Anmeldung wurde bestätigt.', true);
  else if (status === 'invalid')    showMsg('Bestätigungslink ungültig oder abgelaufen.', false);
  else if (status === 'unsubscribed') showMsg('Du wurdest vom Newsletter abgemeldet.', true);
})();









