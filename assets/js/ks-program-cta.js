(function () {
  var form  = document.getElementById('ksProgramForm');
  if (!form) return;

  var nativeSel = document.getElementById('ksProgramSelect');
  var dd        = document.getElementById('ks-dd-program');
  if (!nativeSel || !dd) return;

  var btn   = dd.querySelector('.ks-dd__btn');
  var label = dd.querySelector('.ks-dd__label');
  var panel = dd.querySelector('.ks-dd__panel');

  // <<< NEU: globales Limit für sichtbare Zeilen
  var MAX_VISIBLE_ROWS = 2;

  function syncLabel() {
    var opt = nativeSel.selectedOptions[0];
    label.textContent = opt ? opt.textContent : 'Bitte auswählen …';
  }

  function buildPanel() {
    panel.innerHTML = '';

    Array.from(nativeSel.options).forEach(function (opt) {
      var isPlaceholder = opt.disabled || opt.value === '';
      if (isPlaceholder) return;

      var item = document.createElement('div');
      item.className = 'ks-dd__option';
      item.setAttribute('role', 'option');
      item.setAttribute('data-value', opt.value);
      item.setAttribute('tabindex', '-1');
      if (opt.selected) item.setAttribute('aria-selected', 'true');
      item.textContent = opt.textContent;
      panel.appendChild(item);
    });

    // <<< NEU: Höhe auf genau 2 Zeilen begrenzen + Scrollbar aktivieren
    // Versuche zuerst echte Reihenhöhe zu messen, fallback 40px.
    var rowH = 40;
    var firstRow = panel.firstElementChild;
    if (firstRow) {
      var h = firstRow.getBoundingClientRect().height;
      if (h > 0) rowH = Math.round(h);
    } else {
      // wenn noch nichts drin, nehmen wir die CSS-Variable --row-h, falls vorhanden
      var cssVar = getComputedStyle(panel).getPropertyValue('--row-h');
      var parsed = parseFloat(cssVar);
      if (!isNaN(parsed) && parsed > 0) rowH = parsed;
    }
    panel.style.maxHeight = (rowH * MAX_VISIBLE_ROWS) + 'px';
    panel.style.overflowY = 'auto';
  }

  function openDD() {
    buildPanel();
    dd.setAttribute('aria-expanded', 'true');
    btn.setAttribute('aria-expanded', 'true');

    var first = panel.querySelector('.ks-dd__option');
    if (first) {
      first.focus({ preventScroll: false });
      panel.scrollTop = 0; // immer oben starten
    }

    setTimeout(function () {
      document.addEventListener('click', onDocClick, { once: true });
    }, 0);
    document.addEventListener('keydown', onEscKey, { once: true });
  }

  function closeDD() {
    dd.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-expanded', 'false');
    btn.focus();
  }

  function onDocClick(e) {
    if (!dd.contains(e.target)) closeDD();
    else setTimeout(function () {
      document.addEventListener('click', onDocClick, { once: true });
    }, 0);
  }

  function onEscKey(e) {
    if (e.key === 'Escape') closeDD();
  }

  btn.addEventListener('click', function (e) {
    e.stopPropagation();
    dd.getAttribute('aria-expanded') === 'true' ? closeDD() : openDD();
  });

  panel.addEventListener('click', function (e) {
    var item = e.target.closest('.ks-dd__option');
    if (!item) return;
    var val = item.getAttribute('data-value');
    if (!val) return;

    nativeSel.value = val;
    syncLabel();
    closeDD();
    form.submit(); // → /angebote/?type=<val>
  });

  panel.addEventListener('keydown', function (e) {
    var items = Array.from(panel.querySelectorAll('.ks-dd__option'));
    var cur   = document.activeElement;
    var i     = items.indexOf(cur);

    if (e.key === 'ArrowDown') { e.preventDefault(); (items[i + 1] || items[0])?.focus(); }
    if (e.key === 'ArrowUp')   { e.preventDefault(); (items[i - 1] || items[items.length - 1])?.focus(); }
    if (e.key === 'Enter')     { e.preventDefault(); cur?.click(); }
    if (e.key === 'Escape')    { e.preventDefault(); closeDD(); }
  });

  // Initial
  syncLabel();
})();













