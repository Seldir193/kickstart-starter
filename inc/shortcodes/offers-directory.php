



<?php








/* -------------------------------------------------------
 * [ks_offers_directory] – Hero + Filter + Map + Liste + Modal + Brandbar + (FAQ, Kontakt, Programmbeschreibung)
 * -----------------------------------------------------*/
add_action('init', function () {
  add_shortcode('ks_offers_directory', function () {
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
    $headingKey = $sub_type ?: $type;
    $heading    = $mapTitles[$headingKey] ?? 'Programme';

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

    $ageMin = null; $ageMax = null;
    $res = wp_remote_get($url, ['timeout'=>10, 'headers'=>['Accept'=>'application/json']]);
    if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
      $data  = json_decode(wp_remote_retrieve_body($res), true);
      $items = [];
      if (isset($data['items']) && is_array($data['items'])) $items = $data['items'];
      elseif (is_array($data)) $items = $data;

      foreach ($items as $o) {
        if (isset($o['ageFrom']) && is_numeric($o['ageFrom'])) $ageMin = is_null($ageMin) ? (int)$o['ageFrom'] : min($ageMin, (int)$o['ageFrom']);
        if (isset($o['ageTo'])   && is_numeric($o['ageTo']))   $ageMax = is_null($ageMax) ? (int)$o['ageTo']   : max($ageMax, (int)$o['ageTo']);
      }
    }
    $ageText = ($ageMin !== null && $ageMax !== null) ? ($ageMin . '–' . $ageMax . ' Jahre') : 'alle Altersstufen';


 


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

  <!-- HERO (Bild via CSS-Variable, kein CSS in PHP) -->
  <div class="ks-dir__hero" style="--hero-img:url('<?php echo esc_url($hero_url); ?>')">
    <div class="ks-dir__hero-inner">
      <div class="ks-dir__crumb">Home <span class="sep">/</span> <?php echo esc_html($heading); ?></div>
      <h1 class="ks-dir__hero-title"><?php echo esc_html($heading); ?></h1>
    </div>
  </div>

  <!-- Intro -->
  <header class="ks-dir__intro">
    <p class="ks-dir__kicker">Hier kannst du dein kostenfreies Schnuppertraining ganz einfach buchen</p>
    <h2 class="ks-dir__title">
      Unsere Angebote (<span data-age-title><?php echo esc_html($ageText); ?></span>)
    </h2>
  </header>

  <!-- Filter -->
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

  <!-- Zähler -->
  <div class="ks-dir__meta">
    <strong><span data-count-offers>0</span> Angebote</strong>
    &nbsp;&bull;&nbsp;
    <strong><span data-count-locations>0</span> Standorte</strong>
  </div>

  <!-- 2-Spalten: Map | Liste -->
  <div class="ks-dir__layout">
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

  <!-- Offer Modal -->
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

<?php
  // Partner-Brandbar Markup (nur Markup; Styles in offers-directory.css)
  $brand_base = get_stylesheet_directory_uri() . '/assets/img/';

  $brands = [
    [ 'src' => $brand_base . 'mfs.png', 'label' => 'Bodosee Sportlo' ],
    [ 'src' => $brand_base . 'mfs.png', 'label' => 'Puma' ],
    [ 'src' => $brand_base . 'mfs.png', 'label' => 'DFS Berater' ],
    [ 'src' => $brand_base . 'mfs.png', 'label' => 'Teamstolz' ],
    [ 'src' => $brand_base . 'mfs.png', 'label' => 'DFS Player' ],
  ];
?>

<section id="brandbar" class="ks-sec ks-brandbar" aria-label="Partner &amp; Marken">
  <div class="container">
    <ul class="ks-brandbar__list" role="list">
      <?php foreach ($brands as $b): ?>
        <li class="ks-brandbar__item">
          <img
            src="<?php echo esc_url($b['src']); ?>"
            alt="" aria-hidden="true"
            loading="lazy" decoding="async">
          <span class="ks-brandbar__label"><?php echo esc_html($b['label']); ?></span>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>





<?php
  // ===== Sektion 1: FAQ (dynamisch nach Kurs) =====
  $media = get_stylesheet_directory_uri() . '/assets/img/mfs.png';
  $theme_uri = get_stylesheet_directory_uri();
?>
<section id="dir-faq"
  class="ks-sec ks-py-56"
  
  aria-label="Häufig gestellte Fragen"
  style="--acc-plus:url('<?php echo $theme_uri; ?>/assets/img/home/plus.png');
         --acc-minus:url('<?php echo $theme_uri; ?>/assets/img/home/minus.png');">





     <div class="container ks-dir-faq__grid">
    <!-- ZENTRIERTER TITELBLOCK (spannt über beide Spalten) -->
    <div class="ks-title-wrap" data-bgword="FAQ">
      <div class="ks-kicker">FAQ</div>
      <h2 class="ks-dir__title">Häufig gestellte Fragen</h2>
    </div>

      <div class="ks-dir-faq__left">
        
      

      <div class="ks-accs">
        <?php foreach ($course['faq'] as $qa): ?>
          <details class="ks-acc">
         
            <summary class="ks-acc__q"><?php echo esc_html($qa[0]); ?></summary>
            <div class="ks-acc__body"><?php echo esc_html($qa[1]); ?></div>
          </details>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="ks-dir-faq__right">
      <figure class="ks-dir-faq__media">
        <img src="<?php echo esc_url($media); ?>" alt="" loading="lazy">
        <button class="ks-dir-faq__play" type="button" aria-label="Video abspielen">▶</button>
      </figure>
    </div>
  </div>
</section>



<!-- 5) Kontakt -->
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
    <div><a class="ks-link-light" href="tel:+4917643203362">+49 (176) 43 20 33 62</a></div>
  </div>

  <div class="ks-text-center">
    <a class="ks-contact-iconwrap" href="mailto:fussballschule@selcuk-kocyigit.de" aria-label="E-Mail schreiben">
      <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'mail.png'); ?>')"></span>
    </a>
    <div class="ks-fw-700 ks-mb-16">Schreib uns:</div>
    <div><a class="ks-link-light" href="mailto:fussballschule@selcuk-kocyigit.de">fussballschule@selcuk-kocyigit.de</a></div>
  </div>

  

<div class="ks-text-center">
  <a class="ks-contact-iconwrap" href="#ksDir" aria-label="Nach oben scrollen">
    <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'clock.png'); ?>')"></span>
  </a>
  <div class="ks-fw-700 ks-mb-16">Telefonzeiten:</div>
  <div><a class="ks-link-light" href="#ksDir">Mo.–Fr. 09:00–20:00 Uhr</a></div>
</div>
        











  </div>
</section>


<?php
  // ===== Sektion 3: Programmbeschreibung (dynamisch nach Kurs) =====
  $eyebrow = $course['about']['eyebrow'] ?? '';
  $bodyTxt = $course['about']['body'] ?? '';
  $bullets = $course['about']['bullets'] ?? [];
?>
<section id="dir-program" class="ks-sec ks-dir-program" aria-label="Programm">
  <div class="container ks-dir-program__grid">
    <div class="ks-dir-program__left">
      <?php if ($eyebrow): ?><div class="ks-eyebrow"><?php echo esc_html($eyebrow); ?></div><?php endif; ?>
      <h2 class="ks-dir-program__title"><?php echo esc_html($heading); ?></h2>
      <p class="ks-dir-program__body"><?php echo esc_html($bodyTxt); ?></p>
    </div>
    <div class="ks-dir-program__right">
      <ul class="ks-pluslist">
        <?php foreach ($bullets as $li): ?>
          <li><span class="ks-plus">+</span> <?php echo esc_html($li); ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>

<?php
    return ob_get_clean();
  });
});












