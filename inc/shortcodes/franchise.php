



<?php



/* -------------------------------------------------------
 * [ks_franchise] – Hero + Intro (Text|Video) + MEHR-Buttons
 * -----------------------------------------------------*/
add_shortcode('ks_franchise', function ($atts = []) {
  $theme_uri = get_stylesheet_directory_uri();

  // Hero (Fallback)
  $hero = get_the_post_thumbnail_url(null, 'full');
  if (!$hero) $hero = $theme_uri . '/assets/img/mfs.png';

  // Video-URL optional
  $atts = shortcode_atts([
    'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
  ], $atts, 'ks_franchise');

  // oEmbed oder Fallback (ohne Inline-Styles)
  $video_embed = wp_oembed_get(esc_url($atts['video']));
  if (!$video_embed) {
    $yt = preg_replace('~.*(?:v=|be/)([^&?]+).*~', '$1', $atts['video']);
    $video_embed = '<iframe class="ks-vid-embed" src="https://www.youtube.com/embed/' . esc_attr($yt) . '" allowfullscreen loading="lazy"></iframe>';
  }

  /* CSS laden (Utilities + Franchise) */
  $utils_path = get_stylesheet_directory() . '/assets/css/ks-utils.css';
  $utils_uri  = $theme_uri . '/assets/css/ks-utils.css';
  wp_register_style('ks-utils', $utils_uri, ['kickstart-style'], file_exists($utils_path)?filemtime($utils_path):null);
  wp_enqueue_style('ks-utils');

  $css_path = get_stylesheet_directory() . '/assets/css/ks-franchise.css';
  $css_uri  = $theme_uri . '/assets/css/ks-franchise.css';
  wp_register_style('ks-franchise', $css_uri, ['kickstart-style','ks-utils'], file_exists($css_path)?filemtime($css_path):null);
  wp_enqueue_style('ks-franchise');

  // Hero-Bild via CSS-Variable auf #fr-hero
  wp_add_inline_style('ks-franchise', '#fr-hero{--hero-img:url("'.esc_url($hero).'")}');












  ob_start(); ?>
  <!-- HERO -->
  <section class="ks-dir__hero ks-sec" id="fr-hero">
    <div class="ks-dir__hero-inner">
      <div class="ks-dir__crumb">Home <span class="sep">/</span> Franchise</div>
      <h1 class="ks-dir__hero-title">Franchise</h1>
      <p class="ks-dir__kicker">Franchising an der Dortmunder Fussball Schule</p>
    </div>
  </section>

  <!-- INTRO: Text links | Video rechts -->
  <section class="ks-sec ks-py-56" id="fr-intro">
    <div class="ks-split">
      <div class="ks-split__left">
        <div class="ks-kicker">Gemeinsam zum Erfolg</div>
        <h2>Franchising an der Dortmunder Fussball Schule</h2>
        <p>Das Franchising-Modell der Dortmunder Fussball Schule (DFS) bietet dir eine einzigartige Gelegenheit,
           deine Leidenschaft für Fußball mit einer profitablen Geschäftsmöglichkeit zu verbinden. Durch die
           Zusammenarbeit mit der DFS profitierst du von einem bewährten Geschäftsmodell, das auf jahrelanger
           Erfahrung in der professionellen Fußballausbildung basiert.</p>
        <p>Franchising bei der DFS bedeutet, Teil einer dynamischen Community zu sein. Du lernst Best Practices,
           profitierst vom Netzwerk und schaffst eine positive Lernumgebung für Kinder und Jugendliche.</p>
        <p>Werde Teil der Dortmunder Fussball Schule und inspiriere die nächste Generation – gemeinsam zum Erfolg!</p>

        <p class="ks-mt-16">
          <a href="#fr-worldwide" class="ks-btn js-scroll">MEHR</a>
        </p>
      </div>

      <div class="ks-split__right">
        <div class="ks-vid">
          <?php echo $video_embed; // iframe ohne inline style ?>
        </div>
        <div class="ks-right-below">
          <p><strong>Warum DFS?</strong> Erprobtes Konzept, Trainings-Know-how, Marketing-Support und laufende Beratung.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Die Münchner Fussball Schule (heller Block) -->
  <section id="fr-worldwide" class="ks-sec ks-py-48 ks-bg-white">
    <div class="container container--1100">
      <div class="ks-kicker">25 Jahre Fußballerfahrung</div>
      <h2 class="ks-dir__title ks-mb-16">Die Dortmunder Fussball Schule</h2>

      <div class="ks-grid-12-8">
        <div>
          <p>Die Dortmunder Fussball Schule wurde 2025 gegründet. Inzwischen arbeiten über 10 Trainer für mehr als
             10 Vereine im Großraum NRW &amp; Dortmund. Unsere Angebote sind vereinsunabhängig – mit ganzheitlicher
             Ausbildungsphilosophie.</p>
          <p>Gemeinsam fördern wir die Entwicklung junger Talente – seit über 25 Jahren.</p>
        </div>
        <ul class="ks-list-plus">
          <li>25 Jahre Erfahrung</li>
          <li>13 Partner &gt; 10 Trainer</li>
          <li>&gt; 700 Kinder &amp; 10 Partnervereine</li>
          <li>Wöchentliche Trainerfortbildungen</li>
          <li>Streamingportal mit &gt; 1000 Videos</li>
        </ul>
      </div>
    </div>
  </section>



  <!-- Vorteile -->
  <section id="fr-benefits" class="ks-sec ks-py-48 ks-section--soft">
    <div class="container container--1100">

      <div class="ks-title-wrap" data-bgword="WERTE">
        <h2 class="ks-dir__title">Franchise Vorteile</h2>
      </div>

      <div class="ks-benefits">
        <div class="ks-benefit">
          <img class="ks-benefit__icon"
               src="<?php echo esc_url($theme_uri . '/assets/img/franchise/mfs.png'); ?>"
               alt="Bewährtes Geschäftsmodell" loading="lazy">
          <h3>Bewährtes Geschäftsmodell</h3>
          <p>Franchise-Partner nutzen ein erprobtes Konzept, das bereits erfolgreich ist.</p>
        </div>

        <div class="ks-benefit">
          <img class="ks-benefit__icon"
               src="<?php echo esc_url($theme_uri . '/assets/img/franchise/mfs.png'); ?>"
               alt="Umfassende Unterstützung" loading="lazy">
          <h3>Umfassende Unterstützung</h3>
          <p>Schulungen, Marketingressourcen und laufende Beratung fördern den Erfolg der Partner.</p>
        </div>

        <div class="ks-benefit">
          <img class="ks-benefit__icon"
               src="<?php echo esc_url($theme_uri . '/assets/img/franchise/mfs.png'); ?>"
               alt="Schneller Marktzugang" loading="lazy">
          <h3>Schneller Marktzugang</h3>
          <p>Von einer etablierten Marke und einem bestehenden Kundenstamm profitieren.</p>
        </div>
      </div>

    </div>
  </section>



















<!-- FAQ -->
<?php $theme_uri = get_stylesheet_directory_uri(); ?>
<section id="fr-faq"
  class="ks-sec ks-py-56"
  style="
    --acc-plus:url('<?php echo $theme_uri; ?>/assets/img/home/plus.png');
    --acc-minus:url('<?php echo $theme_uri; ?>/assets/img/home/minus.png');
  ">

  <div class="container fr-faq">

    <!-- Titelblock zentriert + Watermark -->
    <div class="ks-title-wrap" data-bgword="FAQ">
      <div class="ks-kicker">FAQ</div>
      <h2 class="ks-dir__title">Häufig gestellte Fragen</h2>
    </div>

    <!-- Linke Spalte: Akkordeon -->
    <div class="fr-faq__left">
      <details class="ks-acc" open>
        <summary>Wie hoch sind die Startkosten für ein DFS-Franchise?</summary>
        <div class="ks-acc__body">Die Höhe hängt vom Standort ab; eine detaillierte Aufstellung erhältst du im persönlichen Austausch.</div>
      </details>

      <details class="ks-acc">
        <summary>Welche Qualifikationen sind erforderlich?</summary>
        <div class="ks-acc__body">Trainererfahrung ist hilfreich; wichtig sind Zuverlässigkeit, Begeisterung &amp; Organisation.</div>
      </details>

      <details class="ks-acc">
        <summary>Welche Unterstützung bietet die DFS?</summary>
        <div class="ks-acc__body">Schulungen, Materialien, Best Practices &amp; Austausch mit Partnern.</div>
      </details>

      <details class="ks-acc">
        <summary>Wie erhalte ich weitere Informationen?</summary>
        <div class="ks-acc__body">Nutze das Kontaktformular – wir melden uns zeitnah.</div>
      </details>
    </div>

    <!-- Rechte Spalte: MFS-Logo / Bild -->
    <figure class="fr-faq__image">
      <img
        src="<?php echo esc_url($theme_uri . '/assets/img/franchise/mfs.png'); ?>"
        alt="MFS Logo" loading="lazy" decoding="async">
    </figure>

  </div>
</section>





  <?php
  return ob_get_clean();
});









