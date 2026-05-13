

<?php

$team_items = array_values(is_array($coaches ?? null) ? $coaches : []);
$fallback_src = $fallback_img ?: ($theme_uri . '/assets/img/avatar.png');
$team_arrow_icon = $theme_uri . '/assets/img/team/arrow_right_alt.svg';

$team_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'home') : $fallback;
};

$team_fact_icons = [
  $theme_uri . '/assets/img/team/trophy.svg',
  $theme_uri . '/assets/img/team/license.svg',
  $theme_uri . '/assets/img/team/group.svg',
];


$build_team_item = function (array $coach) use ($trainer_url, $fallback_src) {
  $first = isset($coach['firstName']) ? trim((string) $coach['firstName']) : '';
  $last = isset($coach['lastName']) ? trim((string) $coach['lastName']) : '';
  $name = trim((string) (($coach['name'] ?? '') ?: trim($first . ' ' . $last)));
  $name = $name !== '' ? $name : 'Trainer';

  $slug = isset($coach['slug']) && $coach['slug'] !== ''
    ? (string) $coach['slug']
    : sanitize_title($name);

  $photo = isset($coach['photoUrl']) ? trim((string) $coach['photoUrl']) : '';
  $image = $photo !== '' ? ks_normalize_next_img($photo) : '';
  $image = $image !== '' ? $image : $fallback_src;

  $role = isset($coach['position']) ? trim((string) $coach['position']) : '';
  $role = $role !== '' ? $role : 'Trainer';

  return [
    'name' => $name,
    'slug' => $slug,
    'img' => $image,
    'role' => $role,
    'href' => add_query_arg('c', rawurlencode($slug), $trainer_url),
  ];
};

$team_items = array_map($build_team_item, $team_items);
$featured = !empty($team_items) ? $team_items[0] : null;
$side_items = array_slice($team_items, 1, 5);
?>

<section id="team" class="ks-sec ks-py-56 ks-bg-white">
  <div class="container container--1400">
    <div
      id="ksHomeTeam"
      class="ks-home-team"
      data-featured-slug="<?php echo esc_attr($featured['slug'] ?? ''); ?>"
    >
      <script type="application/json" class="ks-home-team__data"><?php echo wp_json_encode($team_items, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>

      <div
        class="ks-title-wrap ks-title-wrap--team ks-watermark ks-watermark--center ks-watermark--team"
        data-bgword="<?php echo esc_attr($team_t('home.team.watermark', 'DFS')); ?>"
        data-i18n="home.team.watermark"
        data-i18n-attr="data-bgword"
      >
        <div class="ks-kicker" data-i18n="home.team.kicker">
          <?php echo esc_html($team_t('home.team.kicker', 'Unsere Trainer')); ?>
        </div>

        <h2 class="ks-dir__title ks-home-team__title" data-i18n="home.team.title">
          <?php echo esc_html($team_t('home.team.title', 'Lerne unser Trainerteam kennen')); ?>
        </h2>

        <p class="ks-home-team__lead" data-i18n="home.team.lead">
          <?php echo esc_html($team_t('home.team.lead', 'Erfahrene Coaches. Echte Leidenschaft. Eine gemeinsame Mission: das Potenzial jedes Spielers zu entfalten – auf und neben dem Platz.')); ?>
        </p>
      </div>

      <?php if ($featured): ?>
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
              <div class="ks-home-team__eyebrow" data-team-featured-role>
                <?php echo esc_html($featured['role']); ?>
              </div>

              <h3 class="ks-home-team__panel-title">
                <a data-team-featured-link href="<?php echo esc_url($featured['href']); ?>">
                  <span data-team-featured-name><?php echo esc_html($featured['name']); ?></span>
                </a>
              </h3>

              <ul class="ks-home-team__facts">
                <li class="ks-home-team__fact">
                  <img
                    class="ks-home-team__fact-icon"
                    src="<?php echo esc_url($team_fact_icons[0]); ?>"
                    alt=""
                    aria-hidden="true"
                  />
                  <span class="ks-home-team__fact-copy">
                    <strong data-i18n="home.team.fact1Title">
                      <?php echo esc_html($team_t('home.team.fact1Title', 'Individuelle Entwicklung')); ?>
                    </strong>
                    <span data-i18n="home.team.fact1Text">
                      <?php echo esc_html($team_t('home.team.fact1Text', 'Gezielte Förderung mit Blick auf Technik, Haltung und Spielverständnis.')); ?>
                    </span>
                  </span>
                </li>

                <li class="ks-home-team__fact">
                  <img
                    class="ks-home-team__fact-icon"
                    src="<?php echo esc_url($team_fact_icons[1]); ?>"
                    alt=""
                    aria-hidden="true"
                  />
                  <span class="ks-home-team__fact-copy">
                    <strong data-i18n="home.team.fact2Title">
                      <?php echo esc_html($team_t('home.team.fact2Title', 'Strukturiertes Coaching')); ?>
                    </strong>
                    <span data-i18n="home.team.fact2Text">
                      <?php echo esc_html($team_t('home.team.fact2Text', 'Klare Inhalte, moderne Trainingsmethoden und nachvollziehbare Abläufe.')); ?>
                    </span>
                  </span>
                </li>

                <li class="ks-home-team__fact">
                  <img
                    class="ks-home-team__fact-icon"
                    src="<?php echo esc_url($team_fact_icons[2]); ?>"
                    alt=""
                    aria-hidden="true"
                  />
                  <span class="ks-home-team__fact-copy">
                    <strong data-i18n="home.team.fact3Title">
                      <?php echo esc_html($team_t('home.team.fact3Title', 'Praxisnahe Förderung')); ?>
                    </strong>
                    <span data-i18n="home.team.fact3Text">
                      <?php echo esc_html($team_t('home.team.fact3Text', 'Begleitung nah am Spieler – auf und neben dem Platz.')); ?>
                    </span>
                  </span>
                </li>
              </ul>

              <a class="ks-btn ks-home-team__cta" data-team-featured-link href="<?php echo esc_url($featured['href']); ?>">
                <span data-i18n="home.team.cta">
                  <?php echo esc_html($team_t('home.team.cta', 'Profil ansehen')); ?>
                </span>
                <img
                  class="ks-home-team__cta-icon"
                  src="<?php echo esc_url($team_arrow_icon); ?>"
                  alt=""
                  aria-hidden="true"
                />
              </a>
            </div>
          </article>

          <div class="ks-home-team__side-shell">
            <button
              type="button"
              class="ks-home-team__side-nav ks-home-team__side-nav--up"
              aria-label="<?php echo esc_attr($team_t('home.team.navUp', 'Vorheriger Trainer')); ?>"
              data-i18n="home.team.navUp"
              data-i18n-attr="aria-label"
            >
              <img
                class="ks-home-team__side-nav-icon"
                src="<?php echo esc_url($team_arrow_icon); ?>"
                alt=""
                aria-hidden="true"
              />
            </button>

            <div class="ks-home-team__side">
              <?php foreach ($side_items as $item): ?>
                <button
                  type="button"
                  class="ks-home-team__side-card"
                  data-team-side-card
                >
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
                    <span class="ks-home-team__side-role" data-team-side-role>
                      <?php echo esc_html($item['role']); ?>
                    </span>

                    <span class="ks-home-team__side-name" data-team-side-name>
                      <?php echo esc_html($item['name']); ?>
                    </span>

                    <span class="ks-home-team__side-linkline">
                      <span class="ks-home-team__side-linktext" data-i18n="home.team.more">
                        <?php echo esc_html($team_t('home.team.more', 'Mehr erfahren')); ?>
                      </span>
                      <img
                        class="ks-home-team__mini-arrow"
                        src="<?php echo esc_url($team_arrow_icon); ?>"
                        alt=""
                        aria-hidden="true"
                      />
                    </span>
                  </span>
                </button>
              <?php endforeach; ?>
            </div>

            <button
              type="button"
              class="ks-home-team__side-nav ks-home-team__side-nav--down"
              aria-label="<?php echo esc_attr($team_t('home.team.navDown', 'Nächster Trainer')); ?>"
              data-i18n="home.team.navDown"
              data-i18n-attr="aria-label"
            >
              <img
                class="ks-home-team__side-nav-icon"
                src="<?php echo esc_url($team_arrow_icon); ?>"
                alt=""
                aria-hidden="true"
              />
            </button>
          </div>
        </div>
      <?php else: ?>
        <div class="ks-home-team__empty" data-i18n="home.team.empty">
          <?php echo esc_html($team_t('home.team.empty', 'Keine Trainer gefunden.')); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>











