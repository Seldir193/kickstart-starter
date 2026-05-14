<?php

if (!function_exists('ks_get_offer_program_icon')) {
  function ks_get_offer_program_icon($index) {
    $icons = [
      'trophy.svg',
      'license.svg',
      'group.svg',
      'academy.svg',
      'video.svg',
    ];

    $file = $icons[$index % count($icons)];

    return get_stylesheet_directory_uri() . '/assets/img/team/' . $file;
  }
}

if (!function_exists('ks_get_offer_program_detail')) {
  function ks_get_offer_program_detail($norm_key) {
    if (!function_exists('ks_i18n_load_scope')) {
      return [];
    }

    $data = ks_i18n_load_scope('offers');
    $detail = ks_i18n_get_nested_value($data, 'offers.programDetails.' . $norm_key);

    return is_array($detail) ? $detail : [];
  }
}

if (!function_exists('ks_get_offer_program_texts')) {
  function ks_get_offer_program_texts($norm_key) {
    $detail = ks_get_offer_program_detail($norm_key);

    return [
      'title' => is_string($detail['title'] ?? null) ? $detail['title'] : '',
      'age' => is_string($detail['age'] ?? null) ? $detail['age'] : '',
      'text' => is_array($detail['text'] ?? null) ? $detail['text'] : [],
      'bullets' => is_array($detail['bullets'] ?? null) ? $detail['bullets'] : [],
    ];
  }
}

add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {
    if (function_exists('ks_enqueue_feedback_assets')) {
      ks_enqueue_feedback_assets();
    }

    if (function_exists('ks_enqueue_info_section_assets')) {
      ks_enqueue_info_section_assets();
    }

    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $select_icon = $theme_uri . '/assets/img/offers/caret-down.svg';
    $calendar_icon = $theme_uri . '/assets/img/offers/calendar-day.svg';
    $age_icon = $theme_uri . '/assets/img/offers/age-user.svg';
    $location_icon = $theme_uri . '/assets/img/offers/location-pin.svg';
    $reset_icon = $theme_uri . '/assets/img/offers/reset-refresh.svg';
    $support_icon = $theme_uri . '/assets/img/offers/support-headset.svg';
    $phone_icon = $theme_uri . '/assets/img/offers/phone.svg';
    $mail_icon = $theme_uri . '/assets/img/offers/mail.svg';
    $route_icon = $theme_uri . '/assets/img/offers/route-navigation.svg';
    $arrow_icon = $theme_uri . '/assets/img/team/arrow_right_alt.svg';
    $dialog_icon_base = $theme_uri . '/assets/img/dialog/';
    $dialog_close_icon = $dialog_icon_base . 'close.svg';

    $offers_directory_t = function ($key, $fallback) {
      return function_exists('ks_t') ? ks_t($key, $fallback, 'offers-directory') : $fallback;
    };

    $dialog_t = function ($key, $fallback) {
      return function_exists('ks_t') ? ks_t($key, $fallback, 'dialog') : $fallback;
    };

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
      ? 'ks-kicker ks-dir__kicker'
      : 'ks-kicker ks-dir__kicker ks-dir__kicker--hidden';

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

    echo do_shortcode(
      '[ks_hero_page title="' . esc_attr($heading) .
      '" subtitle="Finde das passende Training und buche dein kostenfreies Schnuppertraining direkt online." breadcrumb="Home" image="' .
      $page_hero_image .
      '" variant="offers" features="0" eyebrow="Kurse" primary_label="Jetzt buchen" primary_href="#angebote-buchen" secondary_label="Häufige Fragen" secondary_href="#dir-faq" title_i18n="' .
      esc_attr($title_i18n) .
      '" subtitle_i18n="offersHero.subtitle" eyebrow_i18n="offersHero.eyebrow" primary_i18n="offersHero.actions.booking" secondary_i18n="offersHero.actions.faq"]'
    );
?>

<div
  id="ksDir"
  class="ks-dir"
  data-api="<?php echo esc_attr($api_base); ?>"
  data-next="<?php echo esc_attr($next_base); ?>"
  data-type="<?php echo esc_attr($type); ?>"
  data-category="<?php echo esc_attr($category); ?>"
  data-subtype="<?php echo esc_attr($sub_type); ?>"
  data-city="<?php echo esc_attr($city); ?>"
  data-close-icon="<?php echo esc_url($dialog_close_icon); ?>"
  data-dialog-icon-base="<?php echo esc_url($dialog_icon_base); ?>"
  data-coachph="<?php echo esc_url($theme_uri . '/assets/img/avatar.png'); ?>"
>
  <span class="ks-sr-only" data-i18n="offersDialog.actions.close">
    <?php echo esc_html($dialog_t('offersDialog.actions.close', 'Schließen')); ?>
  </span>

  <section
    id="angebote-buchen"
    class="ks-dir__hero ks-py-56 ks-section-accent"
  >
    <div
      class="ks-dir__hero-content ks-dir__hero-content--wordmark"
      data-wordmark="<?php echo esc_attr($offers_directory_t('offersDirectory.intro.watermark', 'ANGEBOTE')); ?>"
      data-i18n="offersDirectory.intro.watermark"
      data-i18n-attr="data-wordmark"
    >
      <p class="<?php echo esc_attr($kickerClass); ?>">
        <span data-i18n="offersDirectory.finder.kicker">
          <?php echo esc_html($offers_directory_t('offersDirectory.finder.kicker', 'Angebote & Standorte')); ?>
        </span>
      </p>

      <h2 class="ks-dir__title" data-i18n="offersDirectory.finder.title">
        <?php echo esc_html($offers_directory_t('offersDirectory.finder.title', 'Wähle dein Training aus.')); ?>
      </h2>

      <div class="ks-dir__stats" aria-label="DFS Vorteile">
        <span class="ks-dir__stat">
          <img src="<?php echo esc_url($location_icon); ?>" alt="" loading="lazy" decoding="async">
          <strong>300+</strong>
          <small data-i18n="offersDirectory.stats.locations">
            <?php echo esc_html($offers_directory_t('offersDirectory.stats.locations', 'Trainingsstandorte')); ?>
          </small>
        </span>

        <span class="ks-dir__stat">
          <img src="<?php echo esc_url($age_icon); ?>" alt="" loading="lazy" decoding="async">
          <strong><?php echo esc_html(str_replace(' Jahre', '', $ageText)); ?></strong>
          <small data-i18n="offersDirectory.stats.age">
            <?php echo esc_html($offers_directory_t('offersDirectory.stats.age', 'Jahre')); ?>
          </small>
        </span>

        <span class="ks-dir__stat">
          <img src="<?php echo esc_url($reset_icon); ?>" alt="" loading="lazy" decoding="async">
          <strong data-i18n="offersDirectory.stats.qualityTitle">
            <?php echo esc_html($offers_directory_t('offersDirectory.stats.qualityTitle', 'Geprüfte')); ?>
          </strong>
          <small data-i18n="offersDirectory.stats.qualityText">
            <?php echo esc_html($offers_directory_t('offersDirectory.stats.qualityText', 'DFS-Qualität')); ?>
          </small>
        </span>
      </div>
    </div>

    <div class="ks-dir__hero-map">
      <div class="ks-dir__map">
        <button type="button" class="ks-dir__nearby">
          <img src="<?php echo esc_url($route_icon); ?>" alt="" loading="lazy" decoding="async">
          <span data-i18n="offersDirectory.map.nearby">
            <?php echo esc_html($offers_directory_t('offersDirectory.map.nearby', 'In meiner Nähe')); ?>
          </span>
        </button>

        <div id="ksMap" class="ks-map"></div>
      </div>
    </div>
  </section>

  <?php if ($showFilters): ?>
    <script>document.documentElement.classList.add('ks-js');</script>

    <?php if ($isHolidayCourse): ?>
      <form class="ks-dir__filters" data-filters>
        <label class="ks-field ks-field--with-icon">
          <span data-i18n="offersDirectory.filters.holidaySeason">Ferienzeit</span>
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($calendar_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterHolidaySeason">
                <option value="" data-i18n="offersDirectory.filters.allHolidaySeasons">Alle Ferienzeiten</option>
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
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span data-i18n="offersDirectory.filters.holidayWeek">Zeitraum</span>
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($calendar_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterHolidayWeek">
                <option value="" data-i18n="offersDirectory.filters.allHolidayWeeks">Alle Zeiträume</option>
              </select>
              <span class="ks-field__icon" aria-hidden="true">
                <img src="<?php echo esc_url($select_icon); ?>" alt="">
              </span>
            </div>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <span data-i18n="offersDirectory.filters.location">Standort</span>
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($location_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterLoc">
                <option value="" data-i18n="offersDirectory.filters.allLocations">Alle Standorte</option>
              </select>
              <span class="ks-field__icon" aria-hidden="true">
                <img src="<?php echo esc_url($select_icon); ?>" alt="">
              </span>
            </div>
          </div>
        </label>
      </form>
    <?php else: ?>
      <form class="ks-dir__filters" data-filters>
        <label class="ks-field ks-field--with-icon">
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($calendar_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterDay">
                <option value="" data-i18n="offersDirectory.filters.allDays">Alle Tage</option>
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
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($age_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterAge">
                <option value="" data-i18n="offersDirectory.filters.allAges">Alle Altersgruppen</option>
                <?php for ($i = 3; $i <= 18; $i++): ?>
                  <option value="<?php echo esc_attr($i); ?>"><?php echo esc_html($i); ?></option>
                <?php endfor; ?>
              </select>
              <span class="ks-field__icon" aria-hidden="true">
                <img src="<?php echo esc_url($select_icon); ?>" alt="">
              </span>
            </div>
          </div>
        </label>

        <label class="ks-field ks-field--with-icon">
          <div class="ks-field__shell">
            <span class="ks-field__lead-icon" aria-hidden="true">
              <img src="<?php echo esc_url($location_icon); ?>" alt="">
            </span>
            <div class="ks-field__control ks-field__control--select">
              <select id="ksFilterLoc">
                <option value="" data-i18n="offersDirectory.filters.allLocations">Alle Standorte</option>
              </select>
              <span class="ks-field__icon" aria-hidden="true">
                <img src="<?php echo esc_url($select_icon); ?>" alt="">
              </span>
            </div>
          </div>
        </label>

        <button type="button" class="ks-dir__reset" data-reset-filters>
          <img src="<?php echo esc_url($reset_icon); ?>" alt="" loading="lazy" decoding="async">
          <span data-i18n="offersDirectory.filters.reset">Filter zurücksetzen</span>
        </button>
      </form>
    <?php endif; ?>

    <div class="ks-dir__meta">
      <strong>
        <span data-count-offers>0</span>
        <span data-i18n="offersDirectory.meta.offers">Angebote</span>
      </strong>
      <span aria-hidden="true">•</span>
      <strong>
        <span data-count-locations>0</span>
        <span data-i18n="offersDirectory.meta.locations">Standorte</span>
      </strong>
    </div>
  <?php endif; ?>

  <div class="ks-dir__results ks-py-48">
    <aside class="ks-dir__support">
      <span class="ks-dir__support-icon" aria-hidden="true">
        <img src="<?php echo esc_url($support_icon); ?>" alt="" loading="lazy" decoding="async">
      </span>

      <div class="ks-dir__support-body">
        <h3 data-i18n="offersDirectory.support.title">Wir sind für dich da</h3>
        <p data-i18n="offersDirectory.support.text">
          Persönliche Beratung zu Programmen und Standorten.
        </p>

        <a href="tel:+498912345678" class="ks-dir__support-link">
          <img src="<?php echo esc_url($phone_icon); ?>" alt="" loading="lazy" decoding="async">
          <span>089 123 456 78</span>
        </a>

        <a href="mailto:beratung@dfs-fussballschule.de" class="ks-dir__support-link">
          <img src="<?php echo esc_url($mail_icon); ?>" alt="" loading="lazy" decoding="async">
          <span>beratung@dfs-fussballschule.de</span>
        </a>

        <a href="#kontakt" class="ks-dir__support-cta">
          <span data-i18n="offersDirectory.support.cta">Zur Beratung</span>
          <img src="<?php echo esc_url($arrow_icon); ?>" alt="" loading="lazy" decoding="async">
        </a>
      </div>
    </aside>

    <section class="ks-dir__results-main" aria-live="polite">
      <div class="ks-dir__listwrap">
        <ul id="ksDirList" class="ks-dir__list"></ul>
      </div>
    </section>
  </div>
</div>

<?php get_template_part('inc/partials/offers/dialogs/offer-sessions-dialog'); ?>
<?php get_template_part('inc/partials/offers/dialogs/booking-iframe-dialog'); ?>

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

<?php
if (function_exists('ks_print_shared_contact_section')) {
  ks_print_shared_contact_section('ks-py-32');
}
?>

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
  'rentacoach_generic' => 'rentacoach',
  'clubprogram_generic' => 'clubprogram',
  'coacheducation' => 'coacheducation',
  'coach_education' => 'coacheducation',
  'personaltraining' => 'personaltraining',
  'einzeltraining_athletik' => 'einzeltraining_athletik',
  'einzeltraining_torwart' => 'einzeltraining_torwart',
];

if (isset($aliasMap[$normKey])) {
  $normKey = $aliasMap[$normKey];
}

$program_data = ks_get_offer_program_texts($normKey);

$program_title = $program_data['title'];
$program_age = $program_data['age'];
$program_text = $program_data['text'];
$program_bullets = $program_data['bullets'];
$program_i18n_base = 'offers.programDetails.' . $normKey;

if ($program_title) : ?>
  <section id="kursdetails" class="ks-sec ks-py-48 ks-program-text ks-program-text--full ks-info-section">
    <div class="container container--1100">
      <div class="ks-info-section__grid">
        <div class="ks-info-section__content">
          <?php if ($program_age): ?>
            <div class="ks-kicker" data-i18n="<?php echo esc_attr($program_i18n_base . '.age'); ?>">
              <?php echo esc_html($program_age); ?>
            </div>
          <?php endif; ?>

          <h2 class="ks-dir__title ks-mb-16" data-i18n="<?php echo esc_attr($program_i18n_base . '.title'); ?>">
            <?php echo esc_html($program_title); ?>
          </h2>

          <div class="ks-info-section__copy">
            <?php foreach ($program_text as $index => $paragraph): ?>
              <p data-i18n="<?php echo esc_attr($program_i18n_base . '.text.' . $index); ?>">
                <?php echo esc_html($paragraph); ?>
              </p>
            <?php endforeach; ?>
          </div>
        </div>

        <?php if (!empty($program_bullets)): ?>
          <ul class="ks-info-facts" role="list">
            <?php foreach ($program_bullets as $index => $bullet): ?>
              <li class="ks-info-fact">
                <span class="ks-info-fact__icon" aria-hidden="true">
                  <img
                    src="<?php echo esc_url(ks_get_offer_program_icon($index)); ?>"
                    alt=""
                    loading="lazy"
                    decoding="async"
                  >
                </span>

                <span class="ks-info-fact__body">
                  <strong
                    class="ks-info-fact__title"
                    data-i18n="<?php echo esc_attr($program_i18n_base . '.bullets.' . $index); ?>"
                  >
                    <?php echo esc_html($bullet); ?>
                  </strong>
                </span>
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










