









/* KS Feedback Tabs */
(function () {
  var fb = document.getElementById('feedback');
  if (!fb) return;

  var tabs   = fb.querySelectorAll('.ks-fb-tab');
  var slides = fb.querySelectorAll('.ks-fb-slide');

  function activate(key) {
    slides.forEach(function (s) { s.classList.toggle('is-active', s.dataset.key === key); });
    tabs.forEach(function (t) { t.classList.toggle('is-active', t.dataset.key === key); });
  }

  tabs.forEach(function (t) {
    t.addEventListener('click', function () { activate(t.dataset.key); });
    t.addEventListener('keydown', function (e) {
      // optional: Pfeiltasten-Navigation
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        var idx = Array.prototype.indexOf.call(tabs, t);
        var next = e.key === 'ArrowRight' ? (idx + 1) % tabs.length : (idx - 1 + tabs.length) % tabs.length;
        tabs[next].focus();
        activate(tabs[next].dataset.key);
      }
    });
  });
})();