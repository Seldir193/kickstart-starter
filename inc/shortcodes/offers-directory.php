

<?php

/* -------------------------------------------------------
 * [ks_offers_directory] – Hero + Filter + Map + Liste + Modal + Brandbar + FAQ (pro Kurs)
 * -----------------------------------------------------*/
add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {

    if (function_exists('ks_enqueue_feedback_assets')) {
    ks_enqueue_feedback_assets();
  }



    /* ==== Theme-Pfade ==== */
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
$select_icon = $theme_uri . '/assets/img/offers/select-caret.svg';







add_action('wp_enqueue_scripts', function () {
  $theme_dir = get_stylesheet_directory();
  $theme_uri = get_stylesheet_directory_uri();

  $dd_hover_css = $theme_dir . '/assets/css/dropdown-hover.css';
  if (!file_exists($dd_hover_css)) return;

  // global laden (leichteste Lösung)
  wp_enqueue_style(
    'ks-dropdown-hover',
    $theme_uri . '/assets/css/dropdown-hover.css',
    ['kickstart-style'],
    filemtime($dd_hover_css)
  );
}, 20);



   
    /* ==== CSS wie auf der Startseite laden ==== */

    // ks-utils.css
    $utils_abs = $theme_dir . '/assets/css/ks-utils.css';
    if (file_exists($utils_abs) && !wp_style_is('ks-utils', 'enqueued')) {
      wp_enqueue_style(
        'ks-utils',
        $theme_uri . '/assets/css/ks-utils.css',
        ['kickstart-style'],
        filemtime($utils_abs)
      );
    }

    // ks-home.css (enthält FAQ-Styles: .ks-acc, .ks-home-faq, etc.)
    $home_abs = $theme_dir . '/assets/css/ks-home.css';
    if (file_exists($home_abs) && !wp_style_is('ks-home', 'enqueued')) {
      wp_enqueue_style(
        'ks-home',
        $theme_uri . '/assets/css/ks-home.css',
        ['kickstart-style', 'ks-utils'],
        filemtime($home_abs)
      );
    }

    // OPTIONAL: eigenes Directory-CSS
    $dir_abs = $theme_dir . '/assets/css/ks-dir.css';
    if (file_exists($dir_abs) && !wp_style_is('ks-dir', 'enqueued')) {
      wp_enqueue_style(
        'ks-dir',
        $theme_uri . '/assets/css/ks-dir.css',
        ['ks-home'],
        filemtime($dir_abs)
      );
    }

    // FAQ-Bild rechts setzen (wie in home.php)
    if (wp_style_is('ks-home', 'enqueued')) {
      $faq_img = $theme_uri . '/assets/img/home/mfs.png';
      wp_add_inline_style(
        'ks-home',
        ".ks-home-faq__image{--faq-img:url('{$faq_img}')}"
      );
    }

    /* ==== bisheriger Code ==== */

    $media     = $theme_uri . '/assets/img/mfs.png';

    $type      = isset($_GET['type']) ? sanitize_text_field( wp_unslash($_GET['type']) ) : '';
    $city      = isset($_GET['city']) ? sanitize_text_field( wp_unslash($_GET['city']) ) : '';
    $category  = isset($_GET['category']) ? sanitize_text_field( wp_unslash($_GET['category']) ) : '';
    $sub_type  = isset($_GET['sub_type']) ? sanitize_text_field( wp_unslash($_GET['sub_type']) ) : '';

    $mapTitles = [
      'Kindergarten'                 => 'Fußballkindergarten',
      'Foerdertraining'              => 'Fördertraining',
      'PersonalTraining'             => 'Individualtraining',
      'AthleticTraining'             => 'Power Training',
      'Camp'                         => 'Holiday Programs',
      'Torwarttraining'              => 'Torwarttraining',
      'Foerdertraining_Athletik'     => 'Fördertraining · Athletik',
      'Einzeltraining_Athletik'      => 'Einzeltraining · Athletik',
      'Einzeltraining_Torwart'       => 'Einzeltraining · Torwart',
      'RentACoach_Generic'           => 'Rent-a-Coach',
      'ClubProgram_Generic'          => 'Club Program',
      'CoachEducation'               => 'Coach Education',
    ];

    $mapWatermarks = [
      'Kindergarten'             => 'KINDERGARTEN',
      'Foerdertraining'          => 'FÖRDERTRAINING',
      'PersonalTraining'         => 'EINZELTRAINING',
      'AthleticTraining'         => 'POWERTRAINING',
      'PowerTraining'            => 'POWERTRAINING',
      'Powertraining'            => 'POWERTRAINING',
      'Camp'                     => 'CAMP',
      'Torwarttraining'          => 'TORWART',
      'Foerdertraining_Athletik' => 'ATHLETIK',
      'Einzeltraining_Athletik'  => 'ATHLETIK',
      'Einzeltraining_Torwart'   => 'TORWART',
      'RentACoach_Generic'       => 'RENT A COACH',
      'ClubProgram_Generic'      => 'CLUB PROGRAM',
      'CoachEducation'           => 'COACH EDUCATION',
      'Generic'                  => 'PROGRAMME',
    ];

    $headingKey = $sub_type ?: $type;
    $heading    = $mapTitles[$headingKey] ?? 'Powertraining';

    // Normalize Kurs-Schlüssel für dyn. Logik
    $normalize = function (string $k = null) {
      if (!$k) return 'Generic';
      switch ($k) {
        case 'Foerdertraining_Athletik': return 'Foerdertraining';
        case 'Einzeltraining_Athletik':  return 'PersonalTraining';
        case 'Einzeltraining_Torwart':   return 'Torwarttraining';
        case 'RentACoach_Generic':       return 'RentACoach';
        case 'ClubProgram_Generic':      return 'ClubProgram';
        default: return $k;
      }
    };
    $courseKey = $normalize($headingKey);

       // =====================================================
    // FAQ-KEY für diesen Kurs bestimmen (für ks_get_faq_items)
    // =====================================================
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

    // Für diese Kursarten KEINE Filter anzeigen
    $noFilterCourses = ['RentACoach', 'ClubProgram', 'CoachEducation'];
    $showFilters = !in_array($courseKey, $noFilterCourses, true);

    // Holiday-Programm? (Camp & Powertraining)
    $catLower        = strtolower($category);
    $isHolidayCourse =
      $catLower === 'holiday' ||
      $catLower === 'holidayprograms' ||
      in_array($courseKey, ['Camp', 'AthleticTraining', 'Powertraining'], true);

    // Schnupper-Kicker nur bei Weekly Courses
    $showKicker = in_array($courseKey, [
      'Foerdertraining',
      'Kindergarten',
      'Torwarttraining',
      'Foerdertraining_Athletik',
    ], true);
    $kickerClass = $showKicker
      ? 'ks-dir__kicker'
      : 'ks-dir__kicker ks-dir__kicker--hidden';

    $watermark = $mapWatermarks[$courseKey] ?? $heading;

    $hero_url = get_the_post_thumbnail_url(null, 'full');
    if (!$hero_url) {
      $hero_url = $theme_uri . '/assets/img/mfs.png';
    }

    // Altersbereich initial serverseitig (optional)
    $api_base = ks_api_base();
    $query    = ['limit' => '200'];
    if ($type !== '')     $query['type']     = $type;
    if ($city !== '')     $query['city']     = $city;
    if ($category !== '') $query['category'] = $category;
    if ($sub_type !== '') $query['sub_type'] = $sub_type;

    $url = add_query_arg($query, $api_base . '/api/offers');

    // 1) Dynamisch aus den Angeboten
    $ageMin = null; $ageMax = null;
    $res = wp_remote_get($url, ['timeout'=>10, 'headers'=>['Accept'=>'application/json']]);
    if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
      $data  = json_decode(wp_remote_retrieve_body($res), true);
      $items = [];
      if (isset($data['items']) && is_array($data['items'])) $items = $data['items'];
      elseif (is_array($data)) $items = $data;

      foreach ($items as $o) {
        if (isset($o['ageFrom']) && is_numeric($o['ageFrom'])) {
          $ageMin = is_null($ageMin) ? (int)$o['ageFrom'] : min($ageMin, (int)$o['ageFrom']);
        }
        if (isset($o['ageTo']) && is_numeric($o['ageTo'])) {
          $ageMax = is_null($ageMax) ? (int)$o['ageTo'] : max($ageMax, (int)$o['ageTo']);
        }
      }
    }

    // 2) Standard-Fallback aus den Daten
    $ageText = ($ageMin !== null && $ageMax !== null)
      ? ($ageMin . '–' . $ageMax . ' Jahre')
      : 'alle Altersstufen';

    if ($headingKey === 'Einzeltraining_Torwart') {
      $ageText = '6–25 Jahre';
    } else {
      // 3) HARTE Bereiche pro Kurs – GLEICH wie im JS
      switch ($courseKey) {
        // Weekly Courses
        case 'Kindergarten':
          $ageText = '4–6 Jahre';
          break;

        case 'Foerdertraining':
        case 'Foerdertraining_Athletik':
        case 'Torwarttraining':
        case 'GoalkeeperTraining':
          $ageText = '7–17 Jahre';
          break;

        // Holiday Programs
        case 'Camp':
          $ageText = '6–13 Jahre';
          break;

        case 'AthleticTraining':
        case 'Powertraining':
        case 'AthletikTraining':
          $ageText = '7–17 Jahre';
          break;

        // Individual Courses
        case 'PersonalTraining':
        case 'Einzeltraining_Athletik':
        case 'Einzeltraining_Torwart':
          $ageText = '6–25 Jahre';
          break;

        // Coach Education
        case 'CoachEducation':
          $ageText = 'alle Altersstufen';
          break;
      }
    }

    ob_start(); ?>
<div id="ksDir"

     class="ks-dir"
     data-api="<?php echo esc_attr($api_base); ?>"
     data-next="<?php echo esc_attr($next_base); ?>"
     data-type="<?php echo esc_attr($type); ?>"
     data-category="<?php echo esc_attr($category); ?>"
     data-subtype="<?php echo esc_attr($sub_type); ?>"
     data-city="<?php echo esc_attr($city); ?>"
     data-close-icon="<?php echo esc_url( $theme_uri . '/assets/img/close.png' ); ?>"
     data-coachph="<?php echo esc_url( $theme_uri . '/assets/img/avatar.png' ); ?>">

  <!-- HERO -->
  <div class="ks-dir__hero"
       data-watermark="<?php echo esc_attr($watermark); ?>"
       style="--hero-img:url('<?php echo esc_url($hero_url); ?>')">
    <div class="ks-dir__hero-inner">
      <div class="ks-dir__crumb">Home <span class="sep">/</span> <?php echo esc_html($heading); ?></div>
      <h1 class="ks-dir__hero-title"><?php echo esc_html($heading); ?></h1>
    </div>
  </div>

  <!-- Intro -->
  <header class="ks-dir__intro ks-py-56">
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
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
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
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
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
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
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
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
      </span>
    </div>
  </label>

  <label class="ks-field ks-field--with-icon">
    <span>Alter</span>
    <div class="ks-field__control ks-field__control--select">
      <select id="ksFilterAge">
        <option value="">Alle</option>
        <?php for ($i = 3; $i <= 18; $i++): ?>
          <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
        <?php endfor; ?>
      </select>
      <span class="ks-field__icon" aria-hidden="true">
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
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
        <img src="<?php echo esc_url( $select_icon ); ?>" alt="">
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

  <!-- 2-Spalten: Map | Liste -->
  <div class="ks-dir__layout ks-py-56">
    <div class="ks-dir__map"><div id="ksMap" class="ks-map"></div></div>
    <div class="ks-dir__listwrap" aria-live="polite">
      <ul id="ksDirList" class="ks-dir__list"></ul>
    </div>
  </div>

  <!-- Booking Modal (iframe) -->
  <div id="ksBookModal" class="ks-dir__modal" hidden>
    <div class="ks-dir__overlay" data-close></div>
    <div class="ks-dir__panel" role="dialog" aria-modal="true" aria-label="Buchung">
      <button type="button" class="ks-dir__close" data-close aria-label="Schließen">
        <?php
          $close = $theme_uri . '/assets/img/close.png';
          echo '<img src="' . esc_url($close) . '" alt="Schließen" width="14" height="14">';
        ?>
      </button>
      <iframe class="ks-book__frame"
              src=""
              title="Buchung"
              loading="lazy"
              referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
  </div>

  <!-- Offer Modal (wird von JS aktuell nicht mehr genutzt, aber gelassen) -->
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
</div> <!-- /#ksDir -->

<?php echo do_shortcode('[ks_brandbar]'); ?>

<?php
 

    // Kurs-bezogene FAQ-Items über zentralen Helper laden
  $faq_items = $faqKey
    ? ks_get_faq_items('offers', $faqKey)
    : [];


  // Video wie bisher
  $faq_video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');
  if (!$faq_video_embed) {
    $faq_video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';
  }

  if (!empty($faq_items)) {
    echo ks_render_faq_section($faq_items, [
      'section_id'    => 'dir-faq',                             // eigene ID
      'wrapper_class' => 'container ks-home-faq ks-dir-faq__grid',
      'title'         => 'Häufig gestellte Fragen',
      'kicker'        => 'FAQ',
      'watermark'     => 'FAQ',                                 // immer „FAQ“
      'use_video'     => true,
      'video_embed'   => $faq_video_embed,
    ]);
  }
?>













<!-- Kontakt-Bereich unter den FAQ -->
<section id="kontakt" class="ks-sec ks-py-56 ks-bg-dark ks-text-light">
  <div class="container container--1100 ks-text-center">
    <div class="ks-kicker ks-text-accent">Kontakt</div>
    <h2 class="ks-dir__title ks-text-light">Hast du Fragen?</h2>
    <p>Bei Interesse kannst du uns folgendermaßen erreichen:</p>

     <?php $icon_base = get_stylesheet_directory_uri() . '/assets/img/offers/'; ?>
    <div class="ks-grid-3 ks-mt-28 ks-contact-cards">
      <div class="ks-text-center">
        <a class="ks-contact-iconwrap" href="tel:+4917643203362" aria-label="Anrufen">
          <span class="ks-contact-icon"
                style="--icon:url('<?php echo esc_url($icon_base . 'phone.png'); ?>')"></span>
        </a>
        <div class="ks-fw-700 ks-mb-16">Ruf uns an:</div>
        <div>
          <a class="ks-link-light" href="tel:+4917643203362">+49 (176) 43 20 33 62</a>
        </div>
      </div>

      <div class="ks-text-center">
        <a class="ks-contact-iconwrap" href="mailto:fussballschule@selcuk-kocyigit.de" aria-label="E-Mail schreiben">
          <span class="ks-contact-icon"
                style="--icon:url('<?php echo esc_url($icon_base . 'mail.png'); ?>')"></span>
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
          <span class="ks-contact-icon"
                style="--icon:url('<?php echo esc_url($icon_base . 'clock.png'); ?>')"></span>
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

// ---------------------------------------------------------
// Programm-Textblock unterhalb "Hast du Fragen?"
// Steuert Text je nach Kurs (type / sub_type)
// ---------------------------------------------------------

$courseKey = '';
if (!empty($sub_type)) {
  $courseKey = $sub_type;
} elseif (!empty($type)) {
  $courseKey = $type;
}

$program_title   = '';
$program_age     = '';
$program_text    = [];
$program_bullets = [];

// Wir vereinheitlichen Key ein bisschen fürs Lookup
$normKey = strtolower(trim($courseKey));

// Spezialfälle aus der API / Mongo normalisieren
if ($normKey === 'rentacoach_generic') {
  $normKey = 'rentacoach';
} elseif ($normKey === 'clubprogram_generic') {
  $normKey = 'clubprogram';
}

// Mapping für Schreibvarianten
$aliasMap = [
  'fördertraining'          => 'foerdertraining',
  'fördertraining_athletik' => 'foerdertraining_athletik',
  'athletiktraining'        => 'foerdertraining_athletik',
];

if (isset($aliasMap[$normKey])) {
  $normKey = $aliasMap[$normKey];
}

// Programmtexte aus externer Datei laden
$prog_file = $theme_dir . '/inc/shortcodes/offer-program-texts.php';
$programs  = [];

if (file_exists($prog_file)) {
  $data = include $prog_file;
  if (is_array($data)) {
    $programs = $data;
  }
}

if (!empty($programs[$normKey]) && is_array($programs[$normKey])) {
  $cfg = $programs[$normKey];

  // Falls manche Keys null sind (Alias), auf Zielkey mappen
  if ($cfg === null) {
    // Alias-Ziel suchen
    foreach ($programs as $key => $val) {
      if ($val !== null && $key === $aliasMap[$normKey]) {
        $cfg = $val;
        break;
      }
    }
  }

  if (is_array($cfg)) {
    $program_title   = $cfg['title']   ?? '';
    $program_age     = $cfg['age']     ?? '';
    $program_text    = $cfg['text']    ?? [];
    $program_bullets = $cfg['bullets'] ?? [];
  }
}

if ($program_title) : ?>

  <section class="ks-sec ks-py-48 ks-program-text ks-program-text--full">
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
          <?php foreach ($program_text as $p): ?>
            <p><?php echo esc_html($p); ?></p>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($program_bullets)): ?>
          <ul class="ks-list-plus">
            <?php foreach ($program_bullets as $b): ?>
              <li>
                <span class="ks-list-plus__icon" aria-hidden="true"></span>
                <span><?php echo esc_html($b); ?></span>
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
  ?>




<?php

    return ob_get_clean();
  });
});



























