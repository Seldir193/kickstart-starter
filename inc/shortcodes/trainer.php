<?php

$theme_dir = get_stylesheet_directory();
$theme_uri = get_stylesheet_directory_uri();

$trainer_css = $theme_dir . '/assets/css/ks-trainer.css';
$trainer_js = $theme_dir . '/assets/js/ks-trainer.js';

if (file_exists($trainer_css) && !wp_style_is('ks-trainer', 'enqueued')) {
  wp_enqueue_style(
    'ks-trainer',
    $theme_uri . '/assets/css/ks-trainer.css',
    ['kickstart-style'],
    filemtime($trainer_css)
  );
}

if (file_exists($trainer_js) && !wp_script_is('ks-trainer', 'enqueued')) {
  wp_enqueue_script(
    'ks-trainer',
    $theme_uri . '/assets/js/ks-trainer.js',
    [],
    filemtime($trainer_js),
    true
  );
}

if (!function_exists('ks_next_base')) {
  function ks_next_base(): string {
    $option = get_option('ks_next_base');
    if (!empty($option)) return rtrim((string) $option, '/');
    return 'http://localhost:3000';
  }
}

if (!function_exists('ks_build_next_image_url')) {
  function ks_build_next_image_url(string $url): string {
    $base = rtrim(ks_next_base(), '/');
    if (!$base) return $url;
    if ($url[0] !== '/') $url = '/' . $url;
    return $base . $url;
  }
}

if (!function_exists('ks_normalize_next_img')) {
  function ks_normalize_next_img(?string $url): string {
    $url = trim((string) $url);
    if ($url === '') return '';
    if (preg_match('~^(https?://|data:image/)~i', $url)) return $url;
    $url = preg_replace('#^/?admin/#i', '', $url);
    return ks_build_next_image_url($url);
  }
}

if (!function_exists('ks_pick_coach_img')) {
  function ks_pick_coach_img(array $coach, array $keys): string {
    foreach ($keys as $key) {
      if (empty($coach[$key]) || !is_string($coach[$key])) continue;
      $url = ks_normalize_next_img($coach[$key]);
      if ($url !== '') return $url;
    }

    return '';
  }
}

if (!function_exists('ks_trainer_get_name')) {
  function ks_trainer_get_name(array $coach): string {
    $first = $coach['firstName'] ?? '';
    $last = $coach['lastName'] ?? '';
    $name = trim(($coach['name'] ?? '') ?: trim("$first $last"));
    return $name !== '' ? $name : 'Trainer';
  }
}

if (!function_exists('ks_trainer_get_slug')) {
  function ks_trainer_get_slug(array $coach): string {
    $name = ks_trainer_get_name($coach);
    if (!empty($coach['slug'])) return sanitize_title($coach['slug']);
    return sanitize_title($name);
  }
}

if (!function_exists('ks_trainer_get_photo')) {
  function ks_trainer_get_photo(array $coach, string $theme_uri): string {
    $photo = ks_pick_coach_img($coach, ['photoUrl', 'imageUrl', 'avatarUrl']);
    if ($photo !== '') return $photo;
    return $theme_uri . '/assets/img/avatar.png';
  }
}

if (!function_exists('ks_trainer_get_current_slug')) {
  function ks_trainer_get_current_slug(): string {
    if (!isset($_GET['c'])) return '';
    return sanitize_title(wp_unslash($_GET['c']));
  }
}

if (!function_exists('ks_trainer_parse_response')) {
  function ks_trainer_parse_response($response): array {
    if (is_wp_error($response)) return ['ok' => false, 'code' => 0, 'data' => null];
    $code = wp_remote_retrieve_response_code($response);
    $body = json_decode(wp_remote_retrieve_body($response), true);
    return ['ok' => $code === 200, 'code' => $code, 'data' => $body];
  }
}

if (!function_exists('ks_trainer_fetch_json')) {
  function ks_trainer_fetch_json(string $url, int $timeout): array {
    $response = wp_remote_get($url, [
      'timeout' => $timeout,
      'headers' => ['Accept' => 'application/json'],
    ]);

    return ks_trainer_parse_response($response);
  }
}

if (!function_exists('ks_trainer_get_current_coach')) {
  function ks_trainer_get_current_coach(string $next_base, string $slug): array {
    $url = $next_base . '/api/coaches/' . rawurlencode($slug);
    $result = ks_trainer_fetch_json($url, 12);

    if (!$result['ok'] || !is_array($result['data'])) {
      return ['coach' => null, 'url' => $url, 'code' => $result['code']];
    }

    return ['coach' => $result['data'], 'url' => $url, 'code' => 200];
  }
}

if (!function_exists('ks_trainer_get_list_urls')) {
  function ks_trainer_get_list_urls(string $next_base): array {
    return [
      $next_base . '/api/coaches?limit=200',
      $next_base . '/api/admin/coaches?limit=200',
    ];
  }
}

if (!function_exists('ks_trainer_get_coach_list_from_url')) {
  function ks_trainer_get_coach_list_from_url(string $url): array {
    $result = ks_trainer_fetch_json($url, 10);
    if (!$result['ok'] || !is_array($result['data'])) return [];
    if (isset($result['data']['items'])) return (array) $result['data']['items'];
    return $result['data'];
  }
}

if (!function_exists('ks_trainer_get_all_coaches')) {
  function ks_trainer_get_all_coaches(string $next_base): array {
    foreach (ks_trainer_get_list_urls($next_base) as $url) {
      $items = ks_trainer_get_coach_list_from_url($url);
      if (!empty($items)) return $items;
    }

    return [];
  }
}

if (!function_exists('ks_trainer_get_rows')) {
  function ks_trainer_get_rows(array $coach): array {
    return [
      'Position' => $coach['position'] ?? '',
      'Abschluss' => $coach['degree'] ?? '',
      'Bei der DFS seit' => $coach['since'] ?? '',
      'DFB Lizenz' => $coach['dfbLicense'] ?? '',
      'DFS Lizenz' => $coach['mfsLicense'] ?? '',
      'Lieblingsverein' => $coach['favClub'] ?? '',
      'Lieblingstrainer' => $coach['favCoach'] ?? '',
      'Lieblingstrick' => $coach['favTrick'] ?? '',
    ];
  }
}

if (!function_exists('ks_trainer_get_icons')) {
  function ks_trainer_get_icons(): array {
    return [
      'Position' => 'icon-position.svg',
      'Abschluss' => 'icon-degree.svg',
      'Bei der DFS seit' => 'icon-calendar.svg',
      'DFB Lizenz' => 'icon-license.svg',
      'DFS Lizenz' => 'icon-shield.svg',
      'Lieblingsverein' => 'icon-club.svg',
      'Lieblingstrainer' => 'icon-coach.svg',
      'Lieblingstrick' => 'icon-skill.svg',
    ];
  }
}

if (!function_exists('ks_trainer_get_label_i18n')) {
  function ks_trainer_get_label_i18n(string $label): string {
    $keys = [
      'Position' => 'coaches.labels.position',
      'Abschluss' => 'coaches.labels.degree',
      'Bei der DFS seit' => 'coaches.labels.since',
      'DFB Lizenz' => 'coaches.labels.dfbLicense',
      'DFS Lizenz' => 'coaches.labels.dfsLicense',
      'Lieblingsverein' => 'coaches.labels.favClub',
      'Lieblingstrainer' => 'coaches.labels.favCoach',
      'Lieblingstrick' => 'coaches.labels.favTrick',
    ];

    return $keys[$label] ?? '';
  }
}

if (!function_exists('ks_trainer_render_error')) {
  function ks_trainer_render_error(string $message): string {
    return '<p>' . esc_html($message) . '</p>';
  }
}

if (!function_exists('ks_trainer_render_not_found')) {
  function ks_trainer_render_not_found(array $coach_result): string {
    return '<p data-i18n="coaches.notFound">Trainer nicht gefunden.</p><p>URL: '
      . esc_html($coach_result['url'])
      . '<br>Status: '
      . esc_html($coach_result['code'])
      . '</p>';
  }
}

if (!function_exists('ks_trainer_get_profile_text')) {
  function ks_trainer_get_profile_text(): string {
    return 'Unsere Trainer verbinden klare Methodik, Erfahrung und moderne Fußballentwicklung mit einer individuellen Betreuung auf dem Platz.';
  }
}

if (!function_exists('ks_trainer_get_hero_text')) {
  function ks_trainer_get_hero_text(): string {
    return 'Lerne den Menschen hinter dem Training kennen. Qualifikation, Haltung und sportliche Schwerpunkte auf einen Blick.';
  }
}

if (!function_exists('ks_trainer_get_hero_title')) {
  function ks_trainer_get_hero_title(array $coach): string {
    $position = trim((string) ($coach['position'] ?? ''));
    return $position !== '' ? $position : 'Trainerprofil';
  }
}

if (!function_exists('ks_trainer_get_visible_rows')) {
  function ks_trainer_get_visible_rows(array $rows): array {
    return array_filter($rows, function ($value) {
      return trim((string) $value) !== '';
    });
  }
}

if (!function_exists('ks_trainer_build_view_data')) {
  function ks_trainer_build_view_data(array $coach, array $all_coaches, string $slug): array {
    $theme_uri = get_stylesheet_directory_uri();
    $rows = ks_trainer_get_rows($coach);
    return ks_trainer_merge_view_data($coach, $all_coaches, $slug, $theme_uri, $rows);
  }
}

if (!function_exists('ks_trainer_merge_view_data')) {
  function ks_trainer_merge_view_data(array $coach, array $all_coaches, string $slug, string $theme_uri, array $rows): array {
    return [
      'coach' => $coach,
      'all_coaches' => $all_coaches,
      'slug' => $slug,
      'theme_uri' => $theme_uri,
      'action_url' => get_permalink(),
      'full' => ks_trainer_get_name($coach),
      'profile_img' => ks_trainer_get_photo($coach, $theme_uri),
      'hero_title' => ks_trainer_get_hero_title($coach),
      'hero_text' => ks_trainer_get_hero_text(),
      'profile_text' => ks_trainer_get_profile_text(),
      'visible_rows' => ks_trainer_get_visible_rows($rows),
      'icons' => ks_trainer_get_icons(),
      'icon_base' => $theme_uri . '/assets/img/team/',
    ];
  }
}

if (!function_exists('ks_trainer_render_hero')) {
  function ks_trainer_render_hero(array $data): void { ?>
    <section class="ks-page-hero ks-page-hero--trainer">
      <div class="container ks-page-hero__inner">
        <?php ks_trainer_render_hero_content($data); ?>
        <?php ks_trainer_render_hero_media($data); ?>
      </div>
    </section>
  <?php }
}

if (!function_exists('ks_trainer_render_hero_content')) {
  function ks_trainer_render_hero_content(array $data): void { ?>
    <div class="ks-page-hero__content">
      <p class="ks-kicker ks-page-hero__eyebrow" data-i18n="coaches.hero.kicker">Trainer</p>

      <h1 class="ks-page-hero__title">
        <?php echo esc_html($data['hero_title']); ?>
      </h1>

      <p class="ks-page-hero__subtitle" data-i18n="coaches.hero.subtitle">
        <?php echo esc_html($data['hero_text']); ?>
      </p>

      <div class="ks-page-hero__actions">
        <a class="ks-btn ks-btn--dark" href="#trainer-profile" data-i18n="coaches.hero.profileButton">Profil ansehen</a>
        <a class="ks-btn" href="<?php echo esc_url(home_url('/kontakt/')); ?>" data-i18n="coaches.hero.contactButton">Kontakt aufnehmen</a>
      </div>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_hero_media')) {
  function ks_trainer_render_hero_media(array $data): void { ?>
    <div class="ks-page-hero__media" aria-hidden="true">
      <div class="ks-page-hero__image-card">
        <img
          class="ks-page-hero__image"
          src="<?php echo esc_attr($data['profile_img']); ?>"
          alt=""
          loading="eager"
          decoding="async"
        >
      </div>

      <article class="ks-page-hero__float-card ks-page-hero__float-card--dark">
        <strong data-i18n="coaches.hero.teamLabel">DFS Team</strong>
        <span><?php echo esc_html($data['hero_title']); ?></span>
      </article>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_picker')) {
  function ks_trainer_render_picker(array $data): void {
    if (empty($data['all_coaches'])) return; ?>

    <div
      class="ks-trainer-picker"
      aria-label="Trainer auswählen"
      data-i18n="coaches.picker.ariaLabel"
      data-i18n-attr="aria-label"
      data-trainer-picker
    >
      <div class="ks-trainer-picker__title">
        <span data-i18n="coaches.picker.title">Trainer auswählen</span>
      </div>

      <?php ks_trainer_render_picker_nav('prev', 'Trainerliste nach links scrollen', 'coaches.picker.prev'); ?>

      <div class="ks-trainer-picker__viewport" data-trainer-picker-viewport>
        <div class="ks-trainer-picker__track">
          <?php foreach ($data['all_coaches'] as $coach) ks_trainer_render_picker_card($coach, $data); ?>
        </div>
      </div>

      <?php ks_trainer_render_picker_nav('next', 'Trainerliste nach rechts scrollen', 'coaches.picker.next'); ?>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_picker_nav')) {
  function ks_trainer_render_picker_nav(string $direction, string $label, string $i18n_key): void {
    $is_prev = $direction === 'prev';
    $class = $is_prev ? ' ks-trainer-picker__arrow--prev' : '';
    $step = $is_prev ? '-1' : '1'; ?>

    <button
      class="ks-trainer-picker__arrow<?php echo esc_attr($class); ?>"
      type="button"
      data-trainer-scroll="<?php echo esc_attr($step); ?>"
      aria-label="<?php echo esc_attr($label); ?>"
      data-i18n="<?php echo esc_attr($i18n_key); ?>"
      data-i18n-attr="aria-label"
    >
      <img src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/team/arrow_right_alt.svg'); ?>" alt="">
    </button>
  <?php }
}

if (!function_exists('ks_trainer_render_picker_card')) {
  function ks_trainer_render_picker_card(array $coach, array $data): void {
    $name = ks_trainer_get_name($coach);
    $slug = ks_trainer_get_slug($coach);
    $photo = ks_trainer_get_photo($coach, $data['theme_uri']);
    $active = $slug === $data['slug']; ?>

    <a class="ks-trainer-picker__card<?php echo $active ? ' is-active' : ''; ?>" href="<?php echo esc_url(add_query_arg('c', $slug, $data['action_url'])); ?>">
      <span class="ks-trainer-picker__avatar">
        <img src="<?php echo esc_attr($photo); ?>" alt="">
      </span>
      <span class="ks-trainer-picker__name"><?php echo esc_html($name); ?></span>
    </a>
  <?php }
}

if (!function_exists('ks_trainer_render_profile')) {
  function ks_trainer_render_profile(array $data): void { ?>
    <section id="trainer-profile" class="ks-trainer-profile">
      <div class="ks-trainer-profile__inner">
        <div class="ks-trainer-profile__top">
          <span class="ks-kicker" data-i18n="coaches.profile.kicker">Trainerteam</span>
          <h2 class="ks-trainer-profile__title" data-i18n="coaches.profile.title">Dein Trainer im Überblick</h2>
          <?php ks_trainer_render_picker($data); ?>
        </div>
        <?php ks_trainer_render_profile_grid($data); ?>
      </div>
    </section>
  <?php }
}

if (!function_exists('ks_trainer_render_profile_grid')) {
  function ks_trainer_render_profile_grid(array $data): void { ?>
    <div class="ks-trainer-profile__grid">
      <?php ks_trainer_render_main_card($data); ?>
      <?php ks_trainer_render_facts($data); ?>
      <?php ks_trainer_render_dna($data); ?>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_main_card')) {
  function ks_trainer_render_main_card(array $data): void { ?>
    <article class="ks-trainer-card">
      <div class="ks-trainer-card__image">
        <img src="<?php echo esc_attr($data['profile_img']); ?>" alt="<?php echo esc_attr($data['full']); ?>" loading="lazy" decoding="async">
      </div>
      <div class="ks-trainer-card__body">
        <span data-i18n="coaches.card.currentProfile">Aktuelles Profil</span>
        <h3><?php echo esc_html($data['full']); ?></h3>
        <p data-i18n="coaches.card.profileText"><?php echo esc_html($data['profile_text']); ?></p>
      </div>
    </article>
  <?php }
}

if (!function_exists('ks_trainer_render_facts')) {
  function ks_trainer_render_facts(array $data): void {
    $primary_rows = array_slice($data['visible_rows'], 0, 4, true); ?>

    <div class="ks-trainer-facts">
      <?php foreach ($primary_rows as $label => $value) ks_trainer_render_fact($label, $value, $data); ?>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_fact')) {
  function ks_trainer_render_fact(string $label, string $value, array $data): void {
    $icon = $data['icons'][$label] ?? 'icon-target.svg';
    $i18n_key = ks_trainer_get_label_i18n($label); ?>

    <article class="ks-trainer-fact">
      <span class="ks-trainer-fact__icon">
        <img src="<?php echo esc_url($data['icon_base'] . $icon); ?>" alt="">
      </span>
      <span class="ks-trainer-fact__label"<?php echo $i18n_key ? ' data-i18n="' . esc_attr($i18n_key) . '"' : ''; ?>><?php echo esc_html($label); ?></span>
      <strong><?php echo esc_html($value); ?></strong>
    </article>
  <?php }
}

if (!function_exists('ks_trainer_render_dna')) {
  function ks_trainer_render_dna(array $data): void {
    $secondary_rows = array_slice($data['visible_rows'], 4, null, true);
    if (empty($secondary_rows)) return; ?>

    <div class="ks-trainer-dna">
      <div class="ks-trainer-dna__head">
        <span class="ks-kicker" data-i18n="coaches.dna.kicker">Trainer DNA</span>
        <h3 data-i18n="coaches.dna.title">Persönliches Profil</h3>
      </div>
      <div class="ks-trainer-dna__list">
        <?php foreach ($secondary_rows as $label => $value) ks_trainer_render_dna_item($label, $value, $data); ?>
      </div>
    </div>
  <?php }
}

if (!function_exists('ks_trainer_render_dna_item')) {
  function ks_trainer_render_dna_item(string $label, string $value, array $data): void {
    $icon = $data['icons'][$label] ?? 'icon-target.svg';
    $i18n_key = ks_trainer_get_label_i18n($label); ?>

    <div class="ks-trainer-dna__item">
      <img src="<?php echo esc_url($data['icon_base'] . $icon); ?>" alt="">
      <span<?php echo $i18n_key ? ' data-i18n="' . esc_attr($i18n_key) . '"' : ''; ?>><?php echo esc_html($label); ?></span>
      <strong><?php echo esc_html($value); ?></strong>
    </div>
  <?php }
}

if (!function_exists('ks_render_trainer_profile_shortcode')) {
  function ks_render_trainer_profile_shortcode(): string {
    $slug = ks_trainer_get_current_slug();
    if (!$slug) return ks_trainer_render_error('Kein Trainer ausgewählt.');

    $next_base = rtrim(ks_next_base(), '/');
    $coach_result = ks_trainer_get_current_coach($next_base, $slug);

    if (!$coach_result['coach']) return ks_trainer_render_not_found($coach_result);

    $all_coaches = ks_trainer_get_all_coaches($next_base);
    $data = ks_trainer_build_view_data($coach_result['coach'], $all_coaches, $slug);

    ob_start();
    ks_trainer_render_hero($data);
    ks_trainer_render_profile($data);
    return ob_get_clean();
  }
}

if (!function_exists('ks_register_trainer_shortcode')) {
  function ks_register_trainer_shortcode(): void {
    add_shortcode('ks_trainer_profile', 'ks_render_trainer_profile_shortcode');
  }

  add_action('init', 'ks_register_trainer_shortcode');
}






