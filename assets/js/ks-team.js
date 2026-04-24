(function () {
  var root = document.getElementById("ksHomeTeam");
  if (!root) return;

  var dataNode = root.querySelector(".ks-home-team__data");
  if (!dataNode) return;

  var coaches = getCoaches(dataNode);
  if (!coaches.length) return;

  var featuredImage = root.querySelector("[data-team-featured-image]");
  var featuredRole = root.querySelector("[data-team-featured-role]");
  var featuredName = root.querySelector("[data-team-featured-name]");
  var featuredLinks = root.querySelectorAll("[data-team-featured-link]");
  var sideCards = root.querySelectorAll("[data-team-side-card]");
  var prev = root.querySelector(".ks-home-team__side-nav--up");
  var next = root.querySelector(".ks-home-team__side-nav--down");
  var featuredSlug = root.getAttribute("data-featured-slug") || "";
  var index = findFeaturedIndex(featuredSlug);

  function getCoaches(node) {
    try {
      var parsedCoaches = JSON.parse(node.textContent || "[]");
      return Array.isArray(parsedCoaches) ? parsedCoaches : [];
    } catch (error) {
      return [];
    }
  }

  function findFeaturedIndex(slug) {
    var i;

    if (!slug) return 0;

    for (i = 0; i < coaches.length; i += 1) {
      if (coaches[i].slug === slug) return i;
    }

    return 0;
  }

  function wrapIndex(value) {
    var total = coaches.length;
    return ((value % total) + total) % total;
  }

  function getImageUrl(coach) {
    if (!coach || typeof coach.img !== "string") return "";
    return coach.img.trim();
  }

  function setImage(image, coach) {
    var imageUrl = getImageUrl(coach);

    if (!image || !imageUrl) return;

    image.src = imageUrl;
    image.alt = coach.name || "Trainer";
  }

  function setFeaturedLinks(coach) {
    var i;

    for (i = 0; i < featuredLinks.length; i += 1) {
      featuredLinks[i].setAttribute("href", coach.href);
    }
  }

  function setFeatured(coach) {
    setImage(featuredImage, coach);

    if (featuredRole) {
      featuredRole.textContent = coach.role || "Trainer";
    }

    if (featuredName) {
      featuredName.textContent = coach.name || "Trainer";
    }

    setFeaturedLinks(coach);
  }

  function setSideCard(card, coach) {
    var image = card.querySelector("[data-team-side-image]");
    var role = card.querySelector("[data-team-side-role]");
    var name = card.querySelector("[data-team-side-name]");

    setImage(image, coach);

    if (role) {
      role.textContent = coach.role || "Trainer";
    }

    if (name) {
      name.textContent = coach.name || "Trainer";
    }
  }

  function renderSide() {
    var i;

    for (i = 0; i < sideCards.length; i += 1) {
      setSideCard(sideCards[i], coaches[wrapIndex(index + i + 1)]);
      // setSideCard(sideCards[i], coaches[wrapIndex(index + i)]);
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

  function bindSideCards() {
    var i;

    for (i = 0; i < sideCards.length; i += 1) {
      bindSideCard(sideCards[i], i);
    }
  }

  function bindSideCard(card, cardIndex) {
    card.addEventListener("click", function () {
      index = wrapIndex(index + cardIndex + 1);
      //index = wrapIndex(index + cardIndex);
      render();
    });
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

  bindSideCards();

  render();
})();
