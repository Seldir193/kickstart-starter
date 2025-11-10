/* KS Feedback – Boot-Animation bei Reload + Richtungslogik + ARIA */
(function () {
  const fb = document.getElementById('feedback');
  if (!fb) return;

  const tabs   = Array.from(fb.querySelectorAll('.ks-fb-tab'));
  const slides = Array.from(fb.querySelectorAll('.ks-fb-slide'));

  // data-key -> Index
  const keyToIndex = {};
  slides.forEach((s, i) => keyToIndex[s.getAttribute('data-key')] = i);

  // aktive Slide (oder erste)
  let active = slides.find(s => s.classList.contains('is-active')) || slides[0];
  slides.forEach(s => { if (s !== active) s.classList.remove('is-active'); });
  let activeIdx = keyToIndex[active.getAttribute('data-key')] ?? 0;

  // ► Boot: CSS-Keyframe läuft sofort. DOM-Ready markiert Ende (falls du fb-boot-done nutzt)
  const markBootDone = () => fb.classList.add('fb-boot-done');
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', markBootDone, { once: true });
  } else {
    markBootDone();
  }

  // Nur den Text animieren – dir: 'down' (von unten) | 'up' (von oben)
  function animateContentIn(slide, dir){
    const content = slide && slide.querySelector('.ks-fb-content');
    if (!content) return;

    // andere Inhalte zurücksetzen
    fb.querySelectorAll('.ks-fb-content').forEach(el => {
      if (el !== content) el.classList.remove('is-prep','is-anim','dir-down','dir-up');
    });

    // Start je Richtung
    content.classList.remove('is-anim','is-prep','dir-down','dir-up');
    content.classList.add('is-prep', dir === 'up' ? 'dir-up' : 'dir-down');

    // Reflow → Ziel aktivieren
    void content.getBoundingClientRect();
    requestAnimationFrame(() => {
      content.classList.add('is-anim');
      content.classList.remove('is-prep');
    });
  }

  // Beim Neuladen: 01 sofort von unten in die Mitte
  const boot = () => animateContentIn(active, 'down');
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', boot, { once: true });
  } else {
    boot();
  }

  // Wechsel-Logik: vorwärts=unten, zurück=oben
  function activate(key){
    const next = slides.find(s => s.getAttribute('data-key') === key);
    if (!next || next === active) return;

    const nextIdx = keyToIndex[key] ?? 0;
    const dir = (nextIdx > activeIdx) ? 'down' : 'up';

    next.classList.add('is-active');     // Bild direkt zeigen
    animateContentIn(next, dir);         // nur Text animieren

    // Tabs-Status + ARIA updaten
    tabs.forEach(t => {
      const isAct = t.getAttribute('data-key') === key;
      t.classList.toggle('is-active', isAct);
      t.setAttribute('aria-selected', String(isAct));
      t.setAttribute('tabindex', isAct ? '0' : '-1');
    });

    if (active) active.classList.remove('is-active');
    active = next;
    activeIdx = nextIdx;
  }

  // ARIA-Grundsetup
  const tablist = fb.querySelector('.ks-fb-tabs');
  if (tablist) tablist.setAttribute('role','tablist');
  tabs.forEach((t) => {
    const isAct = t.classList.contains('is-active');
    t.setAttribute('role','tab');
    t.setAttribute('aria-selected', String(isAct));
    t.setAttribute('tabindex', isAct ? '0' : '-1');
  });

  // Click & Keyboard (Pfeile + Home/End)
  tabs.forEach((t, i) => {
    t.addEventListener('click', () => activate(t.getAttribute('data-key')));

    t.addEventListener('keydown', (e) => {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        const dir = e.key === 'ArrowRight' ? 1 : -1;
        const n = (i + dir + tabs.length) % tabs.length;
        tabs[n].focus();
        activate(tabs[n].getAttribute('data-key'));
      }
      if (e.key === 'Home'){
        e.preventDefault();
        tabs[0].focus();
        activate(tabs[0].getAttribute('data-key'));
      }
      if (e.key === 'End'){
        e.preventDefault();
        const last = tabs[tabs.length-1];
        last.focus();
        activate(last.getAttribute('data-key'));
      }
    });
  });

  // Optional: Hash-Deeplink (#fb-02)
  function openFromHash(){
    const k = location.hash.slice(1);
    if (!k) return;
    const btn = tabs.find(t => t.getAttribute('data-key') === k);
    if (btn) activate(k);
  }
  window.addEventListener('hashchange', openFromHash);
  openFromHash();
})();




































