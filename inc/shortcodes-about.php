<?php

// ==============================
// Shortcode: [ks_about] – zentral mit ks-utils.css
// ==============================
if (!function_exists('ks_register_about_shortcode')) {
  function ks_register_about_shortcode() {
    add_shortcode('ks_about', function () {
      $theme_uri = get_stylesheet_directory_uri();
      $hero = get_the_post_thumbnail_url(null, 'full');
      if (!$hero) {
        $hero = $theme_uri . '/assets/img/mfs.png';
      }

      // globales Plus-Icon für alle Listen auf dieser Seite
      //$plus_icon = $theme_uri . '/assets/img/home/plus.svg';

      $handle = 'ks-utils';
      if (!wp_style_is($handle, 'enqueued')) {
        $utils_path = get_stylesheet_directory() . '/assets/css/ks-utils.css';
        if (file_exists($utils_path)) {
          wp_enqueue_style(
            $handle,
            $theme_uri . '/assets/css/ks-utils.css',
            ['kickstart-style'],
            filemtime($utils_path)
          );
        } else {
          // Fallback: hänge die Variable ans Haupt-Stylesheet
          $handle = 'kickstart-style';
        }
      }

      // Hero-Bild per CSS-Variable setzen (kein inline style am HTML)
      wp_add_inline_style(
        $handle,
        '#about-hero{--hero-img:url("'.esc_url($hero).'")}'
      );

      ob_start(); ?>
      <!-- 1) Wer wir sind -->
      <section id="about-hero" class="ks-dir__hero ks-sec">
        <div class="ks-dir__hero-inner">
          <div class="ks-dir__crumb">Home <span class="sep">/</span> Über uns</div>
          <h1 class="ks-dir__hero-title">Über uns</h1>
        </div>
      </section>

      <!-- 2) Die MFS -->
      <section id="mfs" class="ks-sec ks-py-48">
        <div class="container container--1100">
          <div class="ks-kicker">25 Jahre Fußballerfahrung</div>
          <h2 class="ks-dir__title ks-mb-16">Die Dortmunder Fussball Schule</h2>

          <div class="ks-grid-12-8">
            <div>
              <p>Die Dortmunder Fussball Schule wurde 2025 gegründet. Inzwischen ist unser Unternehmen gewachsen –
                mit über 10 Trainer*innen und Kooperationen mit mehr als 90 Vereinen im Großraum NRW &amp; Dortmund.
                Unsere Angebote sind vereinsunabhängig und folgen einer ganzheitlichen Ausbildungsphilosophie.</p>
              <p>Wir begleiten Kinder und Jugendliche sportlich und persönlich – Jahr für Jahr.</p>
            </div>

            <ul class="ks-list-plus">
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>25 Jahre Erfahrung</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>10 Partner und &gt; 10 Trainer</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>&gt; 7000 Kinder &amp; 10 Partnervereine</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Wöchentliche Trainerfortbildungen</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Streamingportal mit &gt; 1000 Videos</span>
  </li>
</ul>


            
          </div>
        </div>
      </section>

      <!-- 3) Team -->
      <section id="team" class="ks-sec ks-py-48 ks-bg-white">
        <div class="container container--1400">
          <h2 class="ks-dir__title">Unser Team</h2>
          <div id="ksTeamCarousel" class="ks-team"></div>
        </div>
      </section>

      <!-- 4) Philosophie -->
      <section id="philosophie" class="ks-sec ks-py-48">
        <div class="container container--1100">
          <div class="ks-kicker">Wofür wir stehen</div>
          <h2 class="ks-dir__title">Unsere Philosophie</h2>

          <div class="ks-grid-12-8">
            <div>
              <p>Wir lehren das Fußballspielen mit Fokus auf Freude, Entwicklung und Charakterbildung.
                 Ausbildung geht bei uns vor Ergebnisdenken – wir fördern nachhaltig und altersgerecht.</p>
            </div>
            
            <ul class="ks-list-plus">
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Spaß, Freude und Ausbildung vor Ergebnis</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>&gt; 250 Tricks, Ballannahmen und Schusstechniken</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Komplexes altersgerechtes Athletiktraining</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Hohe Trainingseffizienz durch kleine Gruppen</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Perfekte Trainingsstruktur</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Individual-, Gruppen- und Mannschaftstaktik im Detail</span>
  </li>
</ul>



            
          </div>
        </div>
      </section>

      <!-- 5) Kontakt (mit Icon-Buttons wie im Directory) -->
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
              <a class="ks-contact-iconwrap" href="#about-hero" aria-label="Nach oben scrollen">
                <span class="ks-contact-icon" style="--icon:url('<?php echo esc_url($icon_base . 'clock.png'); ?>')"></span>
              </a>
              <div class="ks-fw-700 ks-mb-16">Telefonzeiten:</div>
              <div><a class="ks-link-light" href="#about-hero">Mo.–Fr. 09:00–20:00 Uhr</a></div>
            </div>
          </div>
        </div>
      </section>

      <!-- 6) Ziele -->
      <section id="ziele" class="ks-sec ks-py-48">
        <div class="container container--1100">
          <div class="ks-kicker">Unsere Philosophie</div>
          <h2 class="ks-dir__title">Unsere Ziele</h2>

          <div class="ks-grid-12-8">
            <div>
              <p>Im Mittelpunkt stehen Spaß und Freude am Fußball – das ist die Basis für Leistung und Erfolg.
                 Wir fördern soziale Kompetenz, bieten qualitativ hochwertiges Training und achten auf
                 sportwissenschaftliche Kriterien.</p>
            </div>

            <ul class="ks-list-plus">
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Möglichst vielen Menschen bestmögliches Training ermöglichen</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Vereine inhaltlich &amp; wirtschaftlich unterstützen</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Stetige Verbesserung und Weiterentwicklung unserer Philosophie</span>
  </li>
  <li>
    <span class="ks-list-plus__icon" aria-hidden="true"></span>
    <span>Unsere Philosophie in andere Städte &amp; Länder bringen</span>
  </li>
</ul>



            
          </div>
        </div>
      </section>

      <!-- 7) Standorte -->
      <section id="standorte" class="ks-sec ks-py-32 ks-bg-deep ks-text-light ks-standorte">
        <div class="container container--1200">
          <h2 class="ks-dir__title ks-text-light">Unsere Standorte</h2>
          <p class="ks-mt-16">
            <a href="<?php echo esc_url(home_url('/standorte')); ?>" class="ks-btn">Zu den Standorten</a>
          </p>
        </div>
      </section>
      <?php
      return ob_get_clean();
    });
  }
  add_action('init', 'ks_register_about_shortcode');
}







