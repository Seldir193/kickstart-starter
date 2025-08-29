(function () {
  document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.KS_OFFERS_GATE || {};
    var root = document.getElementById('ksGate');
    if (!root) return;

    var type = cfg.type || '';
    var api  = cfg.apiBase || '';
    var url  = cfg.offersUrl || location.pathname;

    var modal = root.querySelector('[data-modal]');
    var form  = root.querySelector('[data-form]');
    var citySelect = root.querySelector('[data-city]');

    // Helper: read current ?city
    var params = new URLSearchParams(location.search);
    var haveCity = !!params.get('city');

    // Open modal on first load if city is missing
    function openModal() { if (modal) modal.hidden = false; }
    function closeModal() { if (modal) modal.hidden = true; }

    if (!haveCity) openModal();

    // Populate city list from API (unique locations)
    function populateCities(items) {
      var seen = new Set();
      items.forEach(function (o) {
        var loc = (o && o.location) ? String(o.location).trim() : '';
        if (!loc) return;
        var key = loc.toLowerCase();
        if (seen.has(key)) return;
        seen.add(key);

        var opt = document.createElement('option');
        opt.value = loc;
        opt.textContent = loc;
        citySelect.appendChild(opt);
      });
    }

    // Fetch offers for type (or all) to extract cities
    (async function () {
      try {
        var q = new URL(api.replace(/\/$/, '') + '/api/offers');
        if (type) q.searchParams.set('type', type);
        var res = await fetch(q.toString(), { headers: { 'Accept': 'application/json' } });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        var data = await res.json();
        var items = Array.isArray(data?.items) ? data.items : (Array.isArray(data) ? data : []);
        populateCities(items);
      } catch (e) {
        // fallback: keep select empty
      }
    })();

    // Submit -> redirect to offers page with ?type & ?city
    if (form) {
      form.addEventListener('submit', function (e) {
        e.preventDefault();
        var chosen = citySelect && citySelect.value ? citySelect.value.trim() : '';
        if (!chosen) return;

        var next = new URL(url, location.origin);
        if (type) next.searchParams.set('type', type);
        next.searchParams.set('city', chosen);
        // navigate -> WP will render the filtered list
        window.location.href = next.toString();
      });
    }

    // Close handlers
    root.addEventListener('click', function (e) {
      if (e.target.matches('[data-close]')) {
        // Allow closing, user can reopen by reloading or choosing later
        closeModal();
      }
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeModal();
    });
  });
})();
