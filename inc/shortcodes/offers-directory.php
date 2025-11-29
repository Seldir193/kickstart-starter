<?php

/* -------------------------------------------------------
 * [ks_offers_directory] – Hero + Filter + Map + Liste + Modal + Brandbar + (FAQ, Kontakt, Programmbeschreibung)
 * -----------------------------------------------------*/
add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {

    $media     = get_stylesheet_directory_uri() . '/assets/img/mfs.png';
    $theme_uri = get_stylesheet_directory_uri();

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

    // Normalize Kurs-Schlüssel für dynamische Texte
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

    // Für diese Kursarten KEINE Filter anzeigen
    $noFilterCourses = ['RentACoach', 'ClubProgram', 'CoachEducation'];
    $showFilters = !in_array($courseKey, $noFilterCourses, true);

    // Holiday-Programm? (Camp & Powertraining)
    $catLower        = strtolower($category);
    $isHolidayCourse =
      $catLower === 'holiday' ||
      $catLower === 'holidayprograms' ||
      in_array($courseKey, ['Camp', 'AthleticTraining', 'Powertraining'], true);

    // Schnupper-Kicker nur bei Weekly Courses anzeigen
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
      $hero_url = get_stylesheet_directory_uri() . '/assets/img/mfs.png';
    }

    // Altersbereich initial serverseitig (optional)
    $api_base = ks_api_base();
    $query    = ['limit' => '200'];
    if ($type !== '')     $query['type']     = $type;
    if ($city !== '')     $query['city']     = $city;
    if ($category !== '') $query['category'] = $category;
    if ($sub_type !== '') $query['sub_type'] = $sub_type;

    $url = add_query_arg($query, $api_base . '/api/offers');

    // 1) Dynamisch aus den Angeboten (Fallback vorbereiten)
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

    $next_base = ks_next_base();
    /* ===== Kurs-Texte (dyn. für FAQ & Programmbeschreibung) ===== */
    $texts = [
      'Kindergarten' => [
        'faq' => [
          ['Wo, wann und wie lange findet der Fußballkindergarten statt?', 'Die Einheiten finden wöchentlich in unseren Partnervereinen oder Soccerhallen statt und dauern jeweils ca. 60 Minuten.'],
          ['Was kostet der Fußballkindergarten?', 'Die Gebühr variiert je nach Standort. Den genauen Preis siehst du im jeweiligen Angebot.'],
          ['Dürfen Eltern zusehen?', 'Ja, gerne. Bitte bleibt außerhalb des Übungsfeldes, damit die Kinder fokussiert bleiben.'],
          ['Welche Ausrüstung benötige ich?', 'Bequeme Sportsachen, Sportschuhe (Halle: Hallenschuhe), Trinkflasche. Bälle stellen wir bereit.'],
          ['Wie sehen die Anmelde- und Kündigungsbedingungen aus?', 'Monatlich kündbar gemäß unseren AGB. Details im Buchungsprozess.'],
          ['Was passiert an Feiertagen und in Schulferien?', 'In der Regel pausieren die Einheiten. Info erhaltet ihr rechtzeitig per E-Mail.'],
        ],
        'about' => [
          'eyebrow' => 'Für 3–6-Jährige',
          'body'    => 'Der Fußballkindergarten (FuKiGa) vermittelt spielerisch Bewegung, Koordination und erste fußballspezifische Techniken – immer mit Spaß und in kleinen Gruppen.',
          'bullets' => [
            'Spaß an Bewegung und Freude am Fußball wecken',
            'Koordinative Bewegungsabläufe stärken',
            'Selbstvertrauen und Sozialverhalten fördern',
            'Altersgerechte, abwechslungsreiche Übungen',
          ],
        ],
      ],
      'Foerdertraining' => [
        'faq' => [
          ['Für wen ist das Fördertraining?', 'Für Spieler*innen aller Vereine, die an Technik, Torschuss, 1-gegen-1 und Spielverständnis arbeiten möchten.'],
          ['Wie oft findet das Training statt?', 'Wöchentlich, 60–90 Minuten je nach Standort.'],
          ['Kann ich ein Probetraining machen?', 'Ja – buche einfach ein kostenfreies Schnuppertraining im Angebot.'],
          ['Welche Ausrüstung brauche ich?', 'Fußballschuhe, Schienbeinschoner, Sportkleidung, Trinkflasche.'],
          ['Wie lange läuft die Mitgliedschaft?', 'Monatlich kündbar – Details im Buchungsprozess/AGB.'],
          ['Gibt es Ferien-/Feiertagspausen?', 'Ja, in der Regel pausieren wir. Du bekommst rechtzeitig eine Info.'],
        ],
        'about' => [
          'eyebrow' => 'Für 6–14-Jährige',
          'body'    => 'Im Fördertraining verbessern wir systematisch Technik, Koordination und Spielverständnis. Klare Lernziele, kleine Gruppen, moderne Trainingsformen.',
          'bullets' => [
            'Technik: Ballan- & Mitnahme, Dribbling, Passen',
            'Torschuss & 1-gegen-1 Offensiv/Defensiv',
            'Koordination & Athletik altersgerecht',
            'Kleine Gruppen, hohe Wiederholungszahlen',
          ],
        ],
      ],
      'PersonalTraining' => [
        'faq' => [
          ['Was ist Einzeltraining?', '1-zu-1 oder 1-zu-2 Coaching – individuell, effizient und zielgerichtet.'],
          ['Wie lange dauert eine Einheit?', 'Meist 60 Minuten; Umfang nach Absprache.'],
          ['Wo findet das Training statt?', 'Auf einem unserer Standorte oder nach Vereinbarung.'],
          ['Welche Ziele kann ich erreichen?', 'Technik verfeinern, Torschuss verbessern, Schnelligkeit/Koordination steigern.'],
          ['Kann ich Termine flexibel buchen?', 'Ja. Wähle im Angebot einen Termin oder kontaktiere uns.'],
          ['Gibt es Pakete?', 'Ja, Pakete mit Preisvorteil sind möglich – Details im Angebot.'],
        ],
        'about' => [
          'eyebrow' => 'Individuelles Coaching',
          'body'    => 'Im Einzeltraining arbeiten wir an deinen persönlichen Zielen. Hohe Wiederholungszahl, direkte Korrektur, messbare Fortschritte.',
          'bullets' => [
            'Individueller Trainingsplan',
            'Technik, Abschluss, 1-gegen-1',
            'Koordination & Athletik nach Bedarf',
            'Sichtbare Fortschritte durch Feedback',
          ],
        ],
      ],
      'Torwarttraining' => [
        'faq' => [
          ['Für wen ist das Torwarttraining?', 'Für Keeper aller Leistungsstufen – Grundlagen bis leistungsorientiert.'],
          ['Welche Inhalte werden trainiert?', 'Grundstellung, Fangen/Lenken, Fall-/Sprungtechniken, 1-gegen-1, Spieleröffnung.'],
          ['Benötige ich spezielle Ausrüstung?', 'TW-Handschuhe, ggf. lange Kleidung.'],
          ['Wie oft/Wie lange?', 'Wöchentlich 60–90 Minuten, je nach Standort.'],
          ['Probetraining möglich?', 'Ja, über das Schnuppertraining im Angebot.'],
          ['Ferien/Feiertage?', 'In der Regel Pause; Info per E-Mail.'],
        ],
        'about' => [
          'eyebrow' => 'Für Torhüter*innen',
          'body'    => 'Strukturiertes Keeper-Training – technische Basis, Mut im 1-gegen-1 und moderne Spieleröffnung.',
          'bullets' => [
            'Grundtechniken & Fangtechniken',
            '1-gegen-1 & Raumverteidigung',
            'Sprung-/Falltechniken',
            'Spieleröffnung & Kommunikation',
          ],
        ],
      ],
      'AthleticTraining' => [
        'faq' => [
          ['Was ist Power/Athletiktraining?', 'Athletik, Schnelligkeit, Stabilität & Verletzungsprophylaxe – fußballspezifisch.'],
          ['Wer kann teilnehmen?', 'Spieler*innen aller Positionen; Inhalte werden altersgerecht angepasst.'],
          ['Wie oft/Wie lange?', 'Wöchentlich 60 Minuten (Standortabhängig).'],
          ['Brauche ich Equipment?', 'Sportkleidung, ggf. Handtuch/Trinkflasche – Geräte stellen wir.'],
          ['Probetraining?', 'Ja, als Schnuppertraining im Angebot.'],
          ['Pausen?', 'Feiertage/Ferien i. d. R. Pause – Info per E-Mail.'],
        ],
        'about' => [
          'eyebrow' => 'Schnelligkeit & Stabilität',
          'body'    => 'Mehr Power auf dem Platz: Schnelligkeit, Stabilität und Beweglichkeit – verletzungspräventiv und altersgerecht.',
          'bullets' => [
            'Schnelligkeit & Antritt',
            'Core-Stability & Balance',
            'Beweglichkeit & Mobilität',
            'Verletzungsprophylaxe',
          ],
        ],
      ],
      'Camp' => [
        'faq' => [
          ['Wie lange dauert ein Camp?', '3–5 Tage mit vielseitigem Trainingsprogramm.'],
          ['Was ist inklusive?', 'Training, Wettkämpfe, Abschlussturnier – je nach Camp zusätzlich Trikot/Verpflegung.'],
          ['Welche Altersklassen?', 'Standortabhängig; meist 6–14 Jahre.'],
          ['Betreuungszeiten?', 'Tagesprogramm; Details im jeweiligen Angebot.'],
          ['Kann man sich mit Freund*innen anmelden?', 'Ja, sehr gern – im Anmeldeformular vermerken.'],
          ['Was, wenn es regnet?', 'Wir trainieren wetterangepasst – Infos vor Ort.'],
        ],
        'about' => [
          'eyebrow' => 'Ferien-Highlights',
          'body'    => 'Unsere Feriencamps verbinden intensives Training mit viel Spaß, Teamwettkämpfen und einem Abschlussturnier.',
          'bullets' => [
            'Technik & Spielformen',
            'Wettkämpfe & Turniere',
            'Teamgeist & Fairplay',
            'Unvergessliche Erlebnisse',
          ],
        ],
      ],
      // Fallbacks
      'RentACoach' => [
        'faq' => [
          ['Wie funktioniert Rent-a-Coach?', 'Wir kommen zu eurem Verein/Team – Inhalte & Termine nach Absprache.'],
          ['Welche Inhalte?', 'Von Technik bis Athletik – je nach Zielsetzung.'],
          ['Kosten?', 'Individuell je Umfang/Anfahrt.'],
          ['Terminabsprache?', 'Einfach Kontakt aufnehmen.'],
          ['Ausrüstung?', 'Wir bringen Material mit – Platz/ Halle erforderlich.'],
          ['Rechnung/AGB?', 'Erhaltet ihr digital per E-Mail.'],
        ],
        'about' => [
          'eyebrow' => 'Für Vereine & Teams',
          'body'    => 'Wir unterstützen euch direkt vor Ort – mit strukturierten Einheiten und klaren Lernzielen.',
          'bullets' => ['Individuelle Trainingsziele','Flexible Terminplanung','Erfahrene Coaches','Messbare Fortschritte'],
        ],
      ],
      'ClubProgram' => [
        'faq' => [
          ['Was ist das Club Program?', 'Vereinsbegleitende Ausbildung mit klaren Modulen und Coach-Fortbildungen.'],
          ['Ablauf?', 'Analyse, Konzept, regelmäßige Einheiten, Evaluation.'],
          ['Kosten?', 'Je nach Paket/Umfang.'],
          ['Bindung?', 'Vertraglich geregelt – transparent & flexibel.'],
          ['Trainerausbildung?', 'Ja, interne Fortbildungen möglich.'],
          ['Kontakt?', 'Meldet euch – wir planen individuell.'],
        ],
        'about' => [
          'eyebrow' => 'Vereinsentwicklung',
          'body'    => 'Strukturiertes Programm zur nachhaltigen Spieler- und Trainerentwicklung in eurem Club.',
          'bullets' => ['Modulare Ausbildung','Trainer-Workshops','Spielerentwicklung','Nachhaltige Strukturen'],
        ],
      ],
      'CoachEducation' => [
        'faq' => [
          ['Für wen ist Coach Education?', 'Für Trainer*innen aller Altersklassen.'],
          ['Formate?', 'Workshops, Praxis-Clinics, Online-Module.'],
          ['Inhalte?', 'Methodik, Trainingsplanung, Technik/Taktik, Athletik.'],
          ['Zertifikat?', 'Ja, je nach Format.'],
          ['Voraussetzungen?', 'Motivation & Offenheit – sonst keine.'],
          ['Anmeldung?', 'Über unsere Angebote/Termine.'],
        ],
        'about' => [
          'eyebrow' => 'Für Trainer*innen',
          'body'    => 'Praxisnahes Know-how für modernes, effizientes Coaching – sofort umsetzbar.',
          'bullets' => ['Methodik & Didaktik','Trainingsplanung','Technik/Taktik','Athletik & Prävention'],
        ],
      ],
      'Generic' => [
        'faq' => [
          ['Wer kann teilnehmen?', 'Alle interessierten Spieler*innen – Inhalte werden dem Niveau angepasst.'],
          ['Wie oft/Wie lange?', 'Je nach Standort/Format 60–90 Minuten.'],
          ['Probetraining?', 'Ja – im Angebot auswählbar.'],
          ['Ausrüstung?', 'Sportkleidung, Schuhe, Trinkflasche.'],
          ['Laufzeit/Kündigung?', 'Transparent gemäß AGB.'],
          ['Feiertage/Ferien?', 'In der Regel Pause – Info per E-Mail.'],
        ],
        'about' => [
          'eyebrow' => 'Training & Entwicklung',
          'body'    => 'Strukturiertes Training mit klaren Lernzielen, hohem Spaßfaktor und nachhaltiger Entwicklung.',
          'bullets' => ['Technik & Koordination','Spielformen','Teamgeist','Nachhaltiges Lernen'],
        ],
      ],
    ];
    
        
    $course = $texts[$courseKey] ?? $texts['Generic'];

    ob_start(); ?>
<div id="ksDir"
     class="ks-dir"
     data-api="<?php echo esc_attr($api_base); ?>"
     data-next="<?php echo esc_attr($next_base); ?>"
     data-type="<?php echo esc_attr($type); ?>"
     data-category="<?php echo esc_attr($category); ?>"
     data-subtype="<?php echo esc_attr($sub_type); ?>"
     data-city="<?php echo esc_attr($city); ?>"
     data-close-icon="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/close.png' ); ?>"
     data-coachph="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/avatar.png' ); ?>">

  <!-- HERO -->
  <div class="ks-dir__hero" data-watermark="<?php echo esc_attr($watermark); ?>" 
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

  <?php if ($isHolidayCourse): ?>
    <!-- Holiday-Filter: Ferienzeit / Zeitraum / Standort (für Camp & Powertraining) -->
    <form class="ks-dir__filters" data-filters>
      <label class="ks-field">
        <span>Ferienzeit</span>
        <select id="ksFilterHolidaySeason">
          <option value="">Alle Ferienzeiten</option>
          <option value="oster">Ostern</option>
          <option value="pfingst">Pfingsten</option>
          <option value="sommer">Sommer</option>
          <option value="herbst">Herbst</option>
          <option value="winter">Winter</option>
        </select>
      </label>

      <label class="ks-field">
        <span>Zeitraum</span>
        <select id="ksFilterHolidayWeek">
          <option value="">Alle Zeiträume</option>
        </select>
      </label>

      <label class="ks-field">
        <span>Standort</span>
        <select id="ksFilterLoc">
          <option value="">Alle Standorte</option>
        </select>
      </label>
    </form>

    <div class="ks-dir__meta">
      <strong><span data-count-offers>0</span> Angebote</strong>
      &nbsp;&bull;&nbsp;
      <strong><span data-count-locations>0</span> Standorte</strong>
    </div>

  <?php else: ?>
    <!-- Standard-Filter: Tag / Alter / Standort (alle anderen Programme) -->
    <form class="ks-dir__filters" data-filters>
      <label class="ks-field">
        <span>Tag</span>
        <select id="ksFilterDay">
          <option value="">Alle Tage</option>
          <option value="Mo">Mo</option><option value="Di">Di</option><option value="Mi">Mi</option>
          <option value="Do">Do</option><option value="Fr">Fr</option><option value="Sa">Sa</option><option value="So">So</option>
        </select>
      </label>

      <label class="ks-field">
        <span>Alter</span>
        <select id="ksFilterAge">
          <option value="">Alle</option>
          <?php for ($i=3; $i<=18; $i++): ?>
            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
          <?php endfor; ?>
        </select>
      </label>

      <label class="ks-field">
        <span>Standort</span>
        <select id="ksFilterLoc">
          <option value="">Alle Standorte</option>
        </select>
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
          $close = get_stylesheet_directory_uri() . '/assets/img/close.png';
          echo '<img src="' . esc_url($close) . '" alt="Schließen" width="14" height="14">';
        ?>
      </button>
      <iframe class="ks-book__frame" src="" title="Buchung" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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

<!-- Brandbar, FAQ, Kontakt, Programmbeschreibung – alles wie gehabt -->
<?php
    // … hier bleibt dein kompletter Brandbar/FAQ/Kontakt/Programm-Code unverändert …
    return ob_get_clean();
  });
});
