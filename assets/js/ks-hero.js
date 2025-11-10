/* KS Home Hero – Autoplay 6s + sanfte Richtungsanimationen + WM-Sequential */
(function () {
  var hero = document.getElementById('home-hero');
  if (!hero) return;

  var tabs   = Array.from(hero.querySelectorAll('.ks-hero-tab'));
  var slides = Array.from(hero.querySelectorAll('.ks-hero-slide'));

  var idx = slides.findIndex(function(s){ return s.classList.contains('is-active'); });
  if (idx < 0) idx = 0;

  var TIMER = null;
  var DURATION = 6000; // 6 Sekunden zwischen den Slides

  // Staffelung (behalten aus deiner Version)
  var WM_ENTER_DELAY = 0;   // ms bis Wasserzeichen startet
  var TEXT_DELAY     = 180; // ms nach WM-Start -> Text
  var IMG_DELAY      = 300; // ms nach WM-Start -> Bild

  // Zeitungseffekt (du nutzt diese Werte)
  var afterStart = 120;     // ms nach dem ersten beginnt die Kette
  var step       = 126;     // ms Abstand je Buchstabe

  function setWatermark(i){
    var a = slides[i];
    if (!a) return;
    var wm = a.getAttribute('data-watermark') || '';
    hero.setAttribute('data-watermark', wm);
  }

  function activate(nextIdx, userTriggered){
    if (nextIdx === idx) return;

    slides.forEach(function(s,i){ s.classList.toggle('is-active', i===nextIdx); });
    tabs.forEach(function(t,i){
      var act = i===nextIdx;
      t.classList.toggle('is-active', act);
      // ARIA kleinigkeit
      t.setAttribute('aria-selected', String(act));
      t.setAttribute('tabindex', act ? '0' : '-1');
    });

    setWatermark(nextIdx);
    prepAnim(slides[nextIdx]);
    idx = nextIdx;

    if (userTriggered) restart(); // nach Userinput Timer neu starten
  }

  function next(){ activate((idx+1) % slides.length, false); }

  function start(){
    if (TIMER) return;
    TIMER = setInterval(next, DURATION);
  }
  function stop(){
    if (!TIMER) return;
    clearInterval(TIMER);
    TIMER = null;
  }
  function restart(){ stop(); start(); }

  // Click & Keyboard
  tabs.forEach(function(t,i){
    t.setAttribute('role','tab');
    if (!t.hasAttribute('tabindex')) t.setAttribute('tabindex', i===idx ? '0' : '-1');
    if (!t.hasAttribute('aria-selected')) t.setAttribute('aria-selected', String(i===idx));

    t.addEventListener('click', function(){ activate(i, true); });
    t.addEventListener('keydown', function(e){
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft'){
        e.preventDefault();
        var n = (i + (e.key==='ArrowRight'?1:-1) + tabs.length) % tabs.length;
        activate(n, true);
        tabs[n].focus();
      }
      if (e.key === 'Home'){
        e.preventDefault(); activate(0, true); tabs[0].focus();
      }
      if (e.key === 'End'){
        e.preventDefault(); activate(tabs.length-1, true); tabs[tabs.length-1].focus();
      }
    });
  });
  var tablist = hero.querySelector('.ks-hero-tabs');
  if (tablist) tablist.setAttribute('role','tablist');

  // Autoplay pausieren bei Hover/Focus
  hero.addEventListener('mouseenter', stop);
  hero.addEventListener('mouseleave', start);
  hero.addEventListener('focusin',  stop);
  hero.addEventListener('focusout', start);

  // Sichtbarkeit: nur abspielen, wenn sichtbar
  if ('IntersectionObserver' in window){
    var io = new IntersectionObserver(function(entries){
      entries.forEach(function(e){
        if (e.target !== hero) return;
        if (e.isIntersecting && e.intersectionRatio > 0.3){
          start();
        } else {
          stop();
        }
      });
    }, { threshold: [0, 0.3, 0.6, 1] });
    io.observe(hero);
  }

  // Boot
  prepAnim(slides[idx]);
  setWatermark(idx);
  start();

  // ===== Wasserzeichen als Spans aufbauen + animieren =====================

  function ensureWatermarkRoot(){
    hero.classList.add('wm-ready'); // ::after ausblenden (CSS)
    var wm = hero.querySelector('.ks-hero-wm');
    if (!wm){
      wm = document.createElement('div');
      wm.className = 'ks-hero-wm';
      wm.setAttribute('aria-hidden', 'true');
      hero.appendChild(wm);
    }
    return wm;
  }

  function buildWatermark(text){
    var wm = ensureWatermarkRoot();
    // Nur neu bauen, wenn Text sich geändert hat
    if (wm.getAttribute('data-text') !== text){
      wm.setAttribute('data-text', text);
      wm.classList.remove('enter');
      wm.innerHTML = '';
      for (var i = 0; i < text.length; i++){
        var ch = text[i] === ' ' ? '\u00A0' : text[i];
        var sp = document.createElement('span');
        sp.textContent = ch;
        wm.appendChild(sp);
      }
    }
    return wm;
  }

  function clearWatermarkDelays(){
    var wm = hero.querySelector('.ks-hero-wm');
    if (!wm) return;
    wm.querySelectorAll('span').forEach(function(sp){
      sp.style.transitionDelay = '';
    });
  }

  function scheduleClearDelays(){
    var wm = hero.querySelector('.ks-hero-wm');
    if (!wm) return;
    var n = wm.querySelectorAll('span').length || 1;
    // Gesamtdauer der Staffelung + Transition (0.9s) + kleiner Puffer
    var total = afterStart + step * Math.max(0, n - 1) + 900 + 80;
    clearTimeout(scheduleClearDelays._t);
    scheduleClearDelays._t = setTimeout(clearWatermarkDelays, total);
  }

  function animateWatermark(text){
    var wm = buildWatermark(text);
    var spans = Array.from(wm.querySelectorAll('span'));

    var firstDelay = 0; // erster Buchstabe sofort
    spans.forEach(function(sp, i){
      var d = (i === 0) ? firstDelay : afterStart + (i - 1) * step;
      sp.style.transitionDelay = d + 'ms';
    });

    // Animation triggern
    wm.classList.remove('enter');
    void wm.offsetWidth; // Reflow
    wm.classList.add('enter');

    scheduleClearDelays();
  }

  // ===== Slide-Animation orchestrieren ====================================

  function prepAnim(slide){
    var img    = slide.querySelector('.ks-home-hero__img');
    var text   = slide.querySelector('.ks-home-hero__left');
    var wmText = (slide.getAttribute('data-watermark') || '').toString();

    // Reset für Bild/Text (damit jede Slide neu animiert)
    if (img)  img.classList.remove('enter');
    if (text) text.classList.remove('enter');

    // 1) Wasserzeichen: Buchstabe-für-Buchstabe
    setTimeout(function(){
      animateWatermark(wmText);  // startet sofort (erste Glyphe), Rest gestaffelt
    }, WM_ENTER_DELAY);

    // 2) Text kurz danach
    setTimeout(function(){
      if (!text) return;
      text.classList.remove('enter');
      void text.offsetWidth;
      text.classList.add('enter');
    }, WM_ENTER_DELAY + TEXT_DELAY);

    // 3) Bild kurz nach Text
    setTimeout(function(){
      if (!img) return;
      img.classList.remove('enter');
      void img.offsetWidth;
      img.classList.add('enter');
    }, WM_ENTER_DELAY + IMG_DELAY);
  }
})();


















