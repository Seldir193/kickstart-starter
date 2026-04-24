(function () {
  var root = document.getElementById("ksHomeTeam");
  if (!root) return;

  var dataNode = root.querySelector(".ks-home-team__data");
  if (!dataNode) return;

  var coaches = [];
  try {
    coaches = JSON.parse(dataNode.textContent || "[]");
  } catch (error) {
    coaches = [];
  }

  if (!Array.isArray(coaches) || !coaches.length) return;

  var featuredImage = root.querySelector("[data-team-featured-image]");
  var featuredRole = root.querySelector("[data-team-featured-role]");
  var featuredName = root.querySelector("[data-team-featured-name]");
  var featuredLinks = root.querySelectorAll("[data-team-featured-link]");
  var sideCards = root.querySelectorAll("[data-team-side-card]");
  var prev = root.querySelector(".ks-home-team__side-nav--up");
  var next = root.querySelector(".ks-home-team__side-nav--down");
  var index = 0;

  function wrapIndex(value) {
    var total = coaches.length;
    return ((value % total) + total) % total;
  }

  function setFeatured(coach) {
    var i;

    if (featuredImage) {
      featuredImage.src = coach.img;
      featuredImage.alt = coach.name;
    }

    if (featuredRole) {
      featuredRole.textContent = coach.role || "Trainer";
    }

    if (featuredName) {
      featuredName.textContent = coach.name;
    }

    for (i = 0; i < featuredLinks.length; i += 1) {
      featuredLinks[i].setAttribute("href", coach.href);
    }
  }

  function setSideCard(card, coach) {
    var image = card.querySelector("[data-team-side-image]");
    var role = card.querySelector("[data-team-side-role]");
    var name = card.querySelector("[data-team-side-name]");

    card.setAttribute("href", coach.href);

    if (image) {
      image.src = coach.img;
      image.alt = coach.name;
    }

    if (role) {
      role.textContent = coach.role || "Trainer";
    }

    if (name) {
      name.textContent = coach.name;
    }
  }

  function renderSide() {
    var i;

    for (i = 0; i < sideCards.length; i += 1) {
      setSideCard(sideCards[i], coaches[wrapIndex(index + i + 1)]);
    }
  }

  function render() {
    setFeatured(coaches[index]);
    renderSide();
  }

  function move(step) {
    index = wrapIndex(index + step);
    render();
  }

  if (prev) {
    prev.addEventListener("click", function () {
      move(-1);
    });
  }

  if (next) {
    next.addEventListener("click", function () {
      move(1);
    });
  }

  if (coaches.length <= 1) {
    if (prev) prev.hidden = true;
    if (next) next.hidden = true;
  }

  render();
})();
// (function () {
//   var root = document.getElementById("ksTeamCarousel");
//   if (!root) return;
//   var track = root.querySelector(".ks-team__track");
//   if (!track) return;

//   var prev = document.querySelector(".ks-team__nav--prev");
//   var next = document.querySelector(".ks-team__nav--next");
//   var index = 0;

//   function perView() {
//     var w = window.innerWidth;
//     if (w <= 560) return 1;
//     if (w <= 900) return 2;
//     if (w <= 1200) return 3;
//     return 4;
//   }

//   function maxIndex() {
//     var n = track.querySelectorAll(".ks-team__card").length;
//     return Math.max(0, n - perView());
//   }

//   function update() {
//     var firstCard = track.querySelector(".ks-team__card");
//     if (!firstCard) return;
//     var gap = parseFloat(getComputedStyle(track).gap) || 16;
//     var cardWidth = firstCard.getBoundingClientRect().width + gap;
//     var max = maxIndex();
//     if (index > max) index = max;
//     track.style.transform = "translateX(" + -index * cardWidth + "px)";
//   }

//   function go(dir) {
//     index += dir;
//     if (index < 0) index = 0;
//     var max = maxIndex();
//     if (index > max) index = max;
//     update();
//   }

//   window.addEventListener("resize", update, { passive: true });

//   if (prev)
//     prev.addEventListener("click", function () {
//       go(-1);
//     });
//   if (next)
//     next.addEventListener("click", function () {
//       go(1);
//     });

//   // Start
//   update();

// })();
