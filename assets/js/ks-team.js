(function(){
  var root = document.getElementById('ksTeamCarousel');
  if (!root) return;
  var track = root.querySelector('.ks-team__track');
  if (!track) return;

  var prev = document.querySelector('.ks-team__nav--prev');
  var next = document.querySelector('.ks-team__nav--next');
  var index = 0;

  function perView(){
    var w = window.innerWidth;
    if (w <= 560)  return 1;
    if (w <= 900)  return 2;
    if (w <= 1200) return 3;
    return 4;
  }

  function maxIndex(){
    var n = track.querySelectorAll('.ks-team__card').length;
    return Math.max(0, n - perView());
  }

  function update(){
    var firstCard = track.querySelector('.ks-team__card');
    if (!firstCard) return;
    var gap = parseFloat(getComputedStyle(track).gap) || 16;
    var cardWidth = firstCard.getBoundingClientRect().width + gap;
    var max = maxIndex();
    if (index > max) index = max;
    track.style.transform = 'translateX(' + (-index * cardWidth) + 'px)';
  }

  function go(dir){
    index += dir;
    if (index < 0) index = 0;
    var max = maxIndex();
    if (index > max) index = max;
    update();
  }

  window.addEventListener('resize', update, { passive: true });

  if (prev) prev.addEventListener('click', function(){ go(-1); });
  if (next) next.addEventListener('click', function(){ go(1); });

  // Start
  update();
})();
