<?php

$team_items = [];

$is_valid_team_image = function (?string $url): bool {
  $value = strtolower(trim((string) $url));
  if ($value === '') return false;
  if (strpos($value, 'powertraining') !== false) return false;
  if (strpos($value, 'camp') !== false) return false;
  if (strpos($value, 'banner') !== false) return false;
  if (strpos($value, 'poster') !== false) return false;
  if (strpos($value, 'flyer') !== false) return false;
  return true;
};

foreach ($coaches as $coach) {
  $first = isset($coach['firstName']) ? trim((string) $coach['firstName']) : '';
  $last = isset($coach['lastName']) ? trim((string) $coach['lastName']) : '';
  $name = trim((string) ($coach['name'] ?? ''));
  $name = $name !== '' ? $name : trim($first . ' ' . $last);
  $name = $name !== '' ? $name : 'Trainer';

  $slug = !empty($coach['slug']) ? (string) $coach['slug'] : sanitize_title($name);
  $raw_img = isset($coach['photoUrl']) ? (string) $coach['photoUrl'] : '';
$img = ($raw_img !== '' && $is_valid_team_image($raw_img)) ? ks_normalize_next_img($raw_img) : '';
$img = $img !== '' ? $img : $fallback_img;
  $role = !empty($coach['position']) ? (string) $coach['position'] : 'Trainer';
  $href = add_query_arg('c', rawurlencode($slug), $trainer_url);

  $team_items[] = [
    'name' => $name,
    'role' => $role,
    'img' => $img,
    'href' => $href,
  ];
}

if (empty($team_items)) {
  return;
}

$featured = null;
$side_items = [];

foreach ($team_items as $item) {
  if ($featured === null && $item['img'] !== $fallback_img) {
    $featured = $item;
    continue;
  }
  $side_items[] = $item;
}

if ($featured === null) {
  $featured = $team_items[0];
  $side_items = array_slice($team_items, 1);
}

$side_items = array_slice($side_items, 0, 3);
?>

<section id="team" class="ks-sec ks-py-56 ks-bg-white">
  <div class="container container--1400">
    <div id="ksHomeTeam" class="ks-home-team">
      <script type="application/json" class="ks-home-team__data"><?php echo wp_json_encode($team_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

      <div
        class="ks-title-wrap ks-title-wrap--team"
        data-bgword="DFS"
        data-i18n="home.team.watermark"
        data-i18n-attr="data-bgword"
      >
        <div class="ks-kicker" data-i18n="home.team.kicker">Unsere Trainer</div>
        <h2 class="ks-dir__title ks-home-team__title" data-i18n="home.team.title">Lerne unser Trainerteam kennen</h2>
        <p class="ks-home-team__lead" data-i18n="home.team.lead">
          Erfahrene Coaches. Echte Leidenschaft. Eine gemeinsame Mission: das Potenzial jedes Spielers zu entfalten – auf und neben dem Platz.
        </p>
      </div>

      <div class="ks-home-team__layout">
        <article class="ks-home-team__featured-content">
          <a class="ks-home-team__featured-media" data-team-featured-link href="<?php echo esc_url($featured['href']); ?>">
            <img
              class="ks-home-team__featured-image"
              data-team-featured-image
              src="<?php echo esc_url($featured['img']); ?>"
              alt="<?php echo esc_attr($featured['name']); ?>"
              loading="lazy"
              decoding="async"
            />
          </a>

          <div class="ks-home-team__featured-panel">
            <div class="ks-home-team__eyebrow" data-team-featured-role><?php echo esc_html($featured['role']); ?></div>

            <h3 class="ks-home-team__panel-title">
              <a data-team-featured-link href="<?php echo esc_url($featured['href']); ?>">
                <span data-team-featured-name><?php echo esc_html($featured['name']); ?></span>
              </a>
            </h3>

            <ul class="ks-home-team__facts">
              <li class="ks-home-team__fact">
                <strong data-i18n="home.team.fact1Title">Individuelle Entwicklung</strong>
                <span data-i18n="home.team.fact1Text">Gezielte Förderung mit Blick auf Technik, Haltung und Spielverständnis.</span>
              </li>
              <li class="ks-home-team__fact">
                <strong data-i18n="home.team.fact2Title">Strukturiertes Coaching</strong>
                <span data-i18n="home.team.fact2Text">Klare Inhalte, moderne Trainingsmethoden und nachvollziehbare Abläufe.</span>
              </li>
              <li class="ks-home-team__fact">
                <strong data-i18n="home.team.fact3Title">Praxisnahe Förderung</strong>
                <span data-i18n="home.team.fact3Text">Begleitung nah am Spieler – auf und neben dem Platz.</span>
              </li>
            </ul>

            <a class="ks-btn ks-home-team__cta" data-team-featured-link href="<?php echo esc_url($featured['href']); ?>">
              <span data-i18n="home.team.cta">Profil ansehen</span>
              <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M5 12h13"></path>
                <path d="M13 6l6 6-6 6"></path>
              </svg>
            </a>
          </div>
        </article>

        <div class="ks-home-team__side-shell">
          <button
            type="button"
            class="ks-home-team__side-nav ks-home-team__side-nav--up"
            aria-label="Vorheriger Trainer"
            data-i18n="home.team.navUp"
            data-i18n-attr="aria-label"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 18V6"></path>
              <path d="M6 12l6-6 6 6"></path>
            </svg>
          </button>

          <div class="ks-home-team__side">
            <?php foreach ($side_items as $item): ?>
              <a class="ks-home-team__side-card" data-team-side-card href="<?php echo esc_url($item['href']); ?>">
                <span class="ks-home-team__side-media">
                  <img
                    class="ks-home-team__side-image"
                    data-team-side-image
                    src="<?php echo esc_url($item['img']); ?>"
                    alt="<?php echo esc_attr($item['name']); ?>"
                    loading="lazy"
                    decoding="async"
                  />
                </span>

                <span class="ks-home-team__side-body">
                  <span class="ks-home-team__side-role" data-team-side-role><?php echo esc_html($item['role']); ?></span>
                  <span class="ks-home-team__side-name" data-team-side-name><?php echo esc_html($item['name']); ?></span>

                  <span class="ks-home-team__side-linkline">
                    <span class="ks-home-team__side-linktext" data-i18n="home.team.more">Mehr erfahren</span>
                    <svg class="ks-home-team__mini-arrow" viewBox="0 0 24 24" aria-hidden="true">
                      <path d="M5 12h13"></path>
                      <path d="M13 6l6 6-6 6"></path>
                    </svg>
                  </span>
                </span>
              </a>
            <?php endforeach; ?>
          </div>

          <button
            type="button"
            class="ks-home-team__side-nav ks-home-team__side-nav--down"
            aria-label="Nächster Trainer"
            data-i18n="home.team.navDown"
            data-i18n-attr="aria-label"
          >
            <svg viewBox="0 0 24 24" aria-hidden="true">
              <path d="M12 6v12"></path>
              <path d="M6 12l6 6 6-6"></path>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>
</section>














