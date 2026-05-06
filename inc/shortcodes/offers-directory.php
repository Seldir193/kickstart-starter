

<?php

add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {
    if (function_exists('ks_enqueue_feedback_assets')) {
      ks_enqueue_feedback_assets();
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $select_icon = $theme_uri . '/assets/img/offers/select-caret.svg';

    add_action('wp_enqueue_scripts', function () {
      $theme_dir = get_stylesheet_directory();
      $theme_uri = get_stylesheet_directory_uri();
      $dd_hover_css = $theme_dir . '/assets/css/dropdown-hover.css';

      if (!file_exists($dd_hover_css)) {
        return;
      }

      wp_enqueue_style(
        'ks-dropdown-hover',
        $theme_uri . '/assets/css/dropdown-hover.css',
        ['kickstart-style'],
        filemtime($dd_hover_css)
      );
    }, 20);

    $utils_abs = $theme_dir . '/assets/css/ks-utils.css';

    if (file_exists($utils_abs) && !wp_style_is('ks-utils', 'enqueued')) {
      wp_enqueue_style(
        'ks-utils',
        $theme_uri . '/assets/css/ks-utils.css',
        ['kickstart-style'],
        filemtime($utils_abs)
      );
    }

    $home_abs = $theme_dir . '/assets/css/ks-home.css';

    if (file_exists($home_abs) && !wp_style_is('ks-home', 'enqueued')) {
      wp_enqueue_style(
        'ks-home',
        $theme_uri . '/assets/css/ks-home.css',
        ['kickstart-style', 'ks-utils'],
        filemtime($home_abs)
      );
    }

    $dir_abs = $theme_dir . '/assets/css/ks-dir.css';

    if (file_exists($dir_abs) && !wp_style_is('ks-dir', 'enqueued')) {
      wp_enqueue_style(
        'ks-dir',
        $theme_uri . '/assets/css/ks-dir.css',
        ['ks-home'],
        filemtime($dir_abs)
      );
    }

    $dir_hover_abs = $theme_dir . '/assets/css/offers-directory-list-hover.css';

    if (file_exists($dir_hover_abs) && !wp_style_is('ks-dir-list-hover', 'enqueued')) {
      wp_enqueue_style(
        'ks-dir-list-hover',
        $theme_uri . '/assets/css/offers-directory-list-hover.css',
        ['ks-dir'],
        filemtime($dir_hover_abs)
      );
    }

    if (wp_style_is('ks-home', 'enqueued')) {
      $faq_img = $theme_uri . '/assets/img/home/mfs.png';

      wp_add_inline_style(
        'ks-home',
        ".ks-home-faq__image{--faq-img:url('{$faq_img}')}"
      );
    }

    $type = isset($_GET['type'])
      ? sanitize_text_field(wp_unslash($_GET['type']))
      : '';

    $city = isset($_GET['city'])
      ? sanitize_text_field(wp_unslash($_GET['city']))
      : '';

    $category = isset($_GET['category'])
      ? sanitize_text_field(wp_unslash($_GET['category']))
      : '';

    $sub_type = isset($_GET['sub_type'])
      ? sanitize_text_field(wp_unslash($_GET['sub_type']))
      : '';

    $mapTitles = [
      'Kindergarten' => 'Fußballkindergarten',
      'Foerdertraining' => 'Fördertraining',
      'PersonalTraining' => 'Individualtraining',
      'AthleticTraining' => 'Power Training',
      'PowerTraining' => 'Power Training',
      'Powertraining' => 'Power Training',
      'Camp' => 'Holiday Programs',
      'Torwarttraining' => 'Torwarttraining',
      'Foerdertraining_Athletik' => 'Fördertraining · Athletik',
      'Einzeltraining_Athletik' => 'Einzeltraining · Athletik',
      'Einzeltraining_Torwart' => 'Einzeltraining · Torwart',
      'RentACoach_Generic' => 'Rent-a-Coach',
      'ClubProgram_Generic' => 'Club Program',
      'CoachEducation' => 'Coach Education',
    ];

    $headingKey = $sub_type ?: $type;
    $heading = $mapTitles[$headingKey] ?? 'Powertraining';

    $normalize = function (string $key = null) {
      if (!$key) {
        return 'Generic';
      }

      switch ($key) {
        case 'Foerdertraining_Athletik':
          return 'Foerdertraining';
        case 'Einzeltraining_Athletik':
          return 'PersonalTraining';
        case 'Einzeltraining_Torwart':
          return 'Torwarttraining';
        case 'RentACoach_Generic':
          return 'RentACoach';
        case 'ClubProgram_Generic':
          return 'ClubProgram';
        default:
          return $key;
      }
    };

    $courseKey = $normalize($headingKey);
    $faqKey = $headingKey ?: $courseKey;

    switch ($headingKey) {
      case 'AthleticTraining':
      case 'AthletikTraining':
      case 'PowerTraining':
      case 'Powertraining':
        $faqKey = 'Powertraining';
        break;
      case 'Einzeltraining_Torwart':
        $faqKey = 'Torwarttraining';
        break;
      case 'RentACoach_Generic':
        $faqKey = 'RentACoach';
        break;
      case 'Einzeltraining_Athletik':
      case 'einzeltraining_athletik':
        $faqKey = 'Einzeltraining_Athletik';
        break;
      case 'ClubProgram_Generic':
        $faqKey = 'Trainingscamp';
        break;
      default:
        $faqKey = $courseKey ?: $faqKey;
        break;
    }

    $next_base = ks_next_base();
    $noFilterCourses = ['RentACoach', 'ClubProgram', 'CoachEducation'];
    $showFilters = !in_array($courseKey, $noFilterCourses, true);

    $catLower = strtolower($category);
    $isHolidayCourse =
      $catLower === 'holiday' ||
      $catLower === 'holidayprograms' ||
      in_array($courseKey, ['Camp', 'AthleticTraining', 'Powertraining'], true);

    $showKicker = in_array($courseKey, [
      'Foerdertraining',
      'Kindergarten',
      'Torwarttraining',
      'Foerdertraining_Athletik',
    ], true);

    $kickerClass = $showKicker
      ? 'ks-dir__kicker'
      : 'ks-dir__kicker ks-dir__kicker--hidden';

    $hero_i18n_key = $headingKey ?: $courseKey ?: 'Generic';
    $title_i18n = 'offersHero.titles.' . $hero_i18n_key;

    $api_base = ks_api_base();
    $query = ['limit' => '200'];

    if ($type !== '') {
      $query['type'] = $type;
    }

    if ($city !== '') {
      $query['city'] = $city;
    }

    if ($category !== '') {
      $query['category'] = $category;
    }

    if ($sub_type !== '') {
      $query['sub_type'] = $sub_type;
    }

    $url = add_query_arg($query, $api_base . '/api/offers');
    $ageMin = null;
    $ageMax = null;

    $res = wp_remote_get($url, [
      'timeout' => 10,
      'headers' => ['Accept' => 'application/json'],
    ]);

    if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
      $data = json_decode(wp_remote_retrieve_body($res), true);
      $items = [];

      if (isset($data['items']) && is_array($data['items'])) {
        $items = $data['items'];
      } elseif (is_array($data)) {
        $items = $data;
      }

      foreach ($items as $offer) {
        if (isset($offer['ageFrom']) && is_numeric($offer['ageFrom'])) {
          $ageMin = is_null($ageMin)
            ? (int) $offer['ageFrom']
            : min($ageMin, (int) $offer['ageFrom']);
        }

        if (isset($offer['ageTo']) && is_numeric($offer['ageTo'])) {
          $ageMax = is_null($ageMax)
            ? (int) $offer['ageTo']
            : max($ageMax, (int) $offer['ageTo']);
        }
      }
    }

    $ageText = ($ageMin !== null && $ageMax !== null)
      ? ($ageMin . '–' . $ageMax . ' Jahre')
      : 'alle Altersstufen';

    if ($headingKey === 'Einzeltraining_Torwart') {
      $ageText = '6–25 Jahre';
    } else {
      switch ($courseKey) {
        case 'Kindergarten':
          $ageText = '4–6 Jahre';
          break;
        case 'Foerdertraining':
        case 'Foerdertraining_Athletik':
        case 'Torwarttraining':
        case 'GoalkeeperTraining':
          $ageText = '7–17 Jahre';
          break;
        case 'Camp':
          $ageText = '6–13 Jahre';
          break;
        case 'AthleticTraining':
        case 'Powertraining':
        case 'AthletikTraining':
          $ageText = '7–17 Jahre';
          break;
        case 'PersonalTraining':
        case 'Einzeltraining_Athletik':
        case 'Einzeltraining_Torwart':
          $ageText = '6–25 Jahre';
          break;
        case 'CoachEducation':
          $ageText = 'alle Altersstufen';
          break;
      }
    }

    ob_start(); 

$page_hero_image = esc_url($theme_uri . '/assets/img/hero/mfs.png');

// echo do_shortcode(
//   '[ks_hero_page title="' . esc_attr($heading) .
//   '" subtitle="Finde das passende Training und buche dein kostenfreies Schnuppertraining direkt online." breadcrumb="Home" image="' .
//   $page_hero_image .
//   '" variant="offers" features="0" eyebrow="Kurse" primary_label="Jetzt buchen" primary_href="#angebote-buchen" secondary_label="Häufige Fragen" secondary_href="#dir-faq" title_i18n="' .
//   esc_attr($title_i18n) .
//   '" subtitle_i18n="offersHero.subtitle" eyebrow_i18n="offersHero.eyebrow" primary_i18n="offersHero.actions.booking" secondary_i18n="offersHero.actions.faq"]'
// );

echo do_shortcode(
  '[ks_hero_page title="' . esc_attr($heading) .
  '" subtitle="Finde das passende Training und buche dein kostenfreies Schnuppertraining direkt online." breadcrumb="Home" image="' .
  $page_hero_image .
  '" variant="offers" features="0" eyebrow="Kurse" primary_label="Jetzt buchen" primary_href="#angebote-buchen" secondary_label="Häufige Fragen" secondary_href="#dir-faq" title_i18n="' .
  esc_attr($title_i18n) .
  '" subtitle_i18n="offersHero.subtitle" eyebrow_i18n="offersHero.eyebrow" primary_i18n="offersHero.actions.booking" secondary_i18n="offersHero.actions.faq"]'
);
?>

<div id="ksDir"
     class="ks-dir"
     data-api="<?php echo esc_attr($api_base); ?>"
     data-next="<?php echo esc_attr($next_base); ?>"
     data-type="<?php echo esc_attr($type); ?>"
     data-category="<?php echo esc_attr($category); ?>"
     data-subtype="<?php echo esc_attr($sub_type); ?>"
     data-city="<?php echo esc_attr($city); ?>"
     data-close-icon="<?php echo esc_url($theme_uri . '/assets/img/close.png'); ?>"
     data-coachph="<?php echo esc_url($theme_uri . '/assets/img/avatar.png'); ?>">

  

  <header id="angebote-buchen" class="ks-dir__intro ks-py-56">
    <p class="<?php echo esc_attr($kickerClass); ?>">
      Hier kannst du dein kostenfreies Schnuppertraining ganz einfach buchen
    </p>

    <h2 class="ks-dir__title">
      Unsere Angebote (<span data-age-title><?php echo esc_html($ageText); ?></span>)
    </h2>
  </header>

  <?php if ($showFilters): ?>
    <script>document.documentElement.classList.add('ks-js');</script>

    <?php if ($isHolidayCourse): ?>
      <form class="ks-dir__filters" data-filters>
        <label class="ks-field ks-field--with-icon">
          <span>Ferienzeit</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterHolidaySeason">
              <option value="">Alle Ferienzeiten</option>
              <option value="oster">Ostern</option>
              <option value="pfingst">Pfingsten</option>
              <option value="sommer">Sommer</option>
              <option value="herbst">Herbst</option>
              <option value="winter">Winter</option>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span>Zeitraum</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterHolidayWeek">
              <option value="">Alle Zeiträume</option>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span>Standort</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterLoc">
              <option value="">Alle Standorte</option>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>
      </form>

      <div class="ks-dir__meta">
        <strong><span data-count-offers>0</span> Angebote</strong>
        &nbsp;&bull;&nbsp;
        <strong><span data-count-locations>0</span> Standorte</strong>
      </div>
    <?php else: ?>
      <form class="ks-dir__filters" data-filters>
        <label class="ks-field ks-field--with-icon">
          <span>Tag</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterDay">
              <option value="">Alle Tage</option>
              <option value="Mo">Mo</option>
              <option value="Di">Di</option>
              <option value="Mi">Mi</option>
              <option value="Do">Do</option>
              <option value="Fr">Fr</option>
              <option value="Sa">Sa</option>
              <option value="So">So</option>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span>Alter</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterAge">
              <option value="">Alle</option>
              <?php for ($i = 3; $i <= 18; $i++): ?>
                <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
              <?php endfor; ?>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span>Standort</span>
          <div class="ks-field__control ks-field__control--select">
            <select id="ksFilterLoc">
              <option value="">Alle Standorte</option>
            </select>
            <span class="ks-field__icon" aria-hidden="true">
              <img src="<?php echo esc_url($select_icon); ?>" alt="">
            </span>
          </div>
        </label>
      </form>

      <div class="ks-dir__meta">
        <strong><span data-count-offers>0</span> Angebote</strong>
        &nbsp;&bull;&nbsp;
        <strong><span data-count-locations>0</span> Standorte</strong>
      </div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="ks-dir__layout ks-py-56">
    <div class="ks-dir__map">
      <div id="ksMap" class="ks-map"></div>
    </div>
    <div class="ks-dir__listwrap" aria-live="polite">
      <ul id="ksDirList" class="ks-dir__list"></ul>
    </div>
  </div>

  <div id="ksBookModal" class="ks-dir__modal" hidden>
    <div class="ks-dir__overlay" data-close></div>
    <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-label="Buchung">
      <button type="button" class="ks-dir__close" data-close aria-label="Schließen">
        <img src="<?php echo esc_url($theme_uri . '/assets/img/close.png'); ?>" alt="Schließen" width="14" height="14">
      </button>
      <iframe class="ks-book__frame"
              src=""
              title="Buchung"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

  <div id="ksOfferModal" class="ks-dir__modal" hidden>
    <div class="ks-dir__overlay" data-close></div>
    <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-labelledby="ksOfferTitle">
      <button type="button" class="ks-dir__close" data-close aria-label="Schließen">✕</button>
      <h3 id="ksOfferTitle" class="ks-dir__m-title">Standort</h3>
      <p class="ks-dir__m-addr" data-address></p>
      <p class="ks-dir__m-meta">
        <span>Tag: <b data-days>-</b></span> ·
        <span>Uhrzeit: <b data-time>-</b></span> ·
        <span>Alter: <b data-age>-</b></span>
      </p>
      <p class="ks-dir__m-coach" data-coach></p>
      <p class="ks-dir__m-price"><b data-price></b></p>
      <div class="ks-dir__m-actions">
        <a class="btn btn-primary" data-select target="_blank" rel="noopener">Auswählen</a>
      </div>
    </div>
  </div>
</div>

<?php echo do_shortcode('[ks_partner_network]'); ?>

<?php
$faq_items = $faqKey
  ? ks_get_faq_items('offers', $faqKey)
  : [];

$faq_video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');

if (!$faq_video_embed) {
  $faq_video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';
}

if (!empty($faq_items)) {
  echo ks_render_faq_section($faq_items, [
    'section_id' => 'dir-faq',
    'wrapper_class' => 'container ks-home-faq ks-dir-faq__grid',
    'title' => 'Wichtige Fragen zum Angebot',
    'kicker' => 'Gut zu wissen',
    'watermark' => 'FAQ',
    'title_i18n' => 'offers.faq.common.title',
    'kicker_i18n' => 'offers.faq.common.kicker',
    'items_i18n_prefix' => 'offers.faq.' . ks_get_faq_slug($faqKey),
    'side_card_enabled' => true,
    'side_card_kicker' => 'Noch unsicher?',
    'side_card_title' => 'Wir beraten dich gerne',
    'side_card_text' => 'Wenn du Fragen zum Ablauf, zum passenden Format oder zur Buchung hast, unterstützen wir dich gerne persönlich.',
    'side_card_button' => 'Zum Kontakt',
    'side_card_href' => '#kontakt',
    'side_card_kicker_i18n' => 'offers.faq.common.sideCard.kicker',
    'side_card_title_i18n' => 'offers.faq.common.sideCard.title',
    'side_card_text_i18n' => 'offers.faq.common.sideCard.text',
    'side_card_button_i18n' => 'offers.faq.common.sideCard.button',
  ]);
}
?>

<section id="kontakt" class="ks-sec ks-py-56 ks-bg-dark ks-text-light">
  <div class="container container--1100 ks-text-center">
    <div class="ks-kicker ks-text-accent">Kontakt</div>
    <h2 class="ks-dir__title ks-text-light">Hast du Fragen?</h2>
    <p>Bei Interesse kannst du uns folgendermaßen erreichen:</p>

    <?php $icon_base = get_stylesheet_directory_uri() . '/assets/img/offers/'; ?>

    <div class="ks-grid-3 ks-mt-28 ks-contact-cards">
      <div class="ks-text-center">
        <a class="ks-contact-iconwrap" href="tel:+4917643203362" aria-label="Anrufen">
          <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'phone.png'); ?>')"></span>
        </a>
        <div class="ks-fw-700 ks-mb-16">Ruf uns an:</div>
        <div>
          <a class="ks-link-light" href="tel:+4917643203362">+49 (176) 43 20 33 62</a>
        </div>
      </div>

      <div class="ks-text-center">
        <a class="ks-contact-iconwrap" href="mailto:fussballschule@selcuk-kocyigit.de" aria-label="E-Mail schreiben">
          <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'mail.png'); ?>')"></span>
        </a>
        <div class="ks-fw-700 ks-mb-16">Schreib uns:</div>
        <div>
          <a class="ks-link-light" href="mailto:fussballschule@selcuk-kocyigit.de">
            fussballschule@selcuk-kocyigit.de
          </a>
        </div>
      </div>

      <div class="ks-text-center">
        <a class="ks-contact-iconwrap" href="#ksDir" aria-label="Nach oben scrollen">
          <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'clock.png'); ?>')"></span>
        </a>
        <div class="ks-fw-700 ks-mb-16">Telefonzeiten:</div>
        <div>
          <a class="ks-link-light" href="#dir-faq">Mo.–Fr. 09:00–20:00 Uhr</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
$courseKey = '';

if (!empty($sub_type)) {
  $courseKey = $sub_type;
} elseif (!empty($type)) {
  $courseKey = $type;
}

$program_title = '';
$program_age = '';
$program_text = [];
$program_bullets = [];
$normKey = strtolower(trim($courseKey));

if ($normKey === 'rentacoach_generic') {
  $normKey = 'rentacoach';
} elseif ($normKey === 'clubprogram_generic') {
  $normKey = 'clubprogram';
}

$aliasMap = [
  'fördertraining' => 'foerdertraining',
  'fördertraining_athletik' => 'foerdertraining_athletik',
  'athletiktraining' => 'foerdertraining_athletik',
];

if (isset($aliasMap[$normKey])) {
  $normKey = $aliasMap[$normKey];
}

$prog_file = $theme_dir . '/inc/shortcodes/offer-program-texts.php';
$programs = [];

if (file_exists($prog_file)) {
  $data = include $prog_file;

  if (is_array($data)) {
    $programs = $data;
  }
}

if (!empty($programs[$normKey]) && is_array($programs[$normKey])) {
  $cfg = $programs[$normKey];

  if ($cfg === null) {
    foreach ($programs as $key => $val) {
      if ($val !== null && isset($aliasMap[$normKey]) && $key === $aliasMap[$normKey]) {
        $cfg = $val;
        break;
      }
    }
  }

  if (is_array($cfg)) {
    $program_title = $cfg['title'] ?? '';
    $program_age = $cfg['age'] ?? '';
    $program_text = $cfg['text'] ?? [];
    $program_bullets = $cfg['bullets'] ?? [];
  }
}

if ($program_title) : ?>
  <section id="kursdetails" class="ks-sec ks-py-48 ks-program-text ks-program-text--full">
    <div class="container container--1100">
      <?php if ($program_age): ?>
        <div class="ks-kicker">
          <?php echo esc_html($program_age); ?>
        </div>
      <?php endif; ?>

      <h2 class="ks-dir__title">
        <?php echo esc_html($program_title); ?>
      </h2>

      <div class="ks-grid-12-8">
        <div>
          <?php foreach ($program_text as $paragraph): ?>
            <p><?php echo esc_html($paragraph); ?></p>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($program_bullets)): ?>
          <ul class="ks-list-plus">
            <?php foreach ($program_bullets as $bullet): ?>
              <li>
                <span class="ks-list-plus__icon" aria-hidden="true"></span>
                <span><?php echo esc_html($bullet); ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php
if (function_exists('ks_render_feedback_section')) {
  echo ks_render_feedback_section();
}

return ob_get_clean();
  });
});






































































