<?php

if (!function_exists('ks_register_franchise_locations_rest')) {
  function ks_register_franchise_locations_rest() {
    add_action('rest_api_init', function () {
      register_rest_route('ks/v1', '/franchise-locations', [
        'methods'  => 'GET',
        'permission_callback' => '__return_true',
        'callback' => function () {
          $api = defined('KS_FRANCHISE_LOCATIONS_API') ? KS_FRANCHISE_LOCATIONS_API : '';
          if (!$api) {
            return new WP_REST_Response([
              'ok' => false,
              'error' => 'KS_FRANCHISE_LOCATIONS_API ist nicht gesetzt.'
            ], 500);
          }

          $nocache = isset($_GET['nocache']) && $_GET['nocache'] == '1';

         
          $ttl = 20; 

         
          $cache_key = 'ks_fr_locations_v2_' . md5($api);

          if (!$nocache && $ttl > 0) {
            $cached = get_transient($cache_key);
            if ($cached) return new WP_REST_Response($cached, 200);
          }

          $res = wp_remote_get($api, [
            'timeout' => 12,
            'headers' => ['Accept' => 'application/json'],
          ]);

          if (is_wp_error($res)) {
            return new WP_REST_Response([
              'ok' => false,
              'error' => $res->get_error_message()
            ], 502);
          }

          $code = wp_remote_retrieve_response_code($res);
          $body = wp_remote_retrieve_body($res);
          $json = json_decode($body, true);

          if ($code < 200 || $code >= 300 || !is_array($json)) {
            return new WP_REST_Response([
              'ok' => false,
              'error' => 'Ungültige API Antwort.',
              'status' => $code
            ], 502);
          }

          
          $items = [];
          if (isset($json['items']) && is_array($json['items'])) {
            $items = $json['items'];
          } elseif (array_keys($json) === range(0, count($json) - 1)) {
            $items = $json;
          } else {
            $items = [];
          }

         
          $items = array_values(array_filter($items, function ($it) {
            $status = isset($it['status']) ? (string)$it['status'] : '';
            $published = isset($it['published']) ? (bool)$it['published'] : false;
         

            if ($status !== 'approved') return false;
            if ($published !== true) return false;
            

            return true;
          }));

          $payload = ['ok' => true, 'items' => $items];

          if (!$nocache && $ttl > 0) {
            set_transient($cache_key, $payload, $ttl);
          }

          return new WP_REST_Response($payload, 200);
        },
      ]);
    });
  }

  ks_register_franchise_locations_rest();
}


if (!function_exists('ks_register_franchise_shortcode')) {
  function ks_register_franchise_shortcode() {

    add_shortcode('ks_franchise', function ($atts = []) {
      $theme_dir = get_stylesheet_directory();
      $theme_uri = get_stylesheet_directory_uri();

     
      $hero = get_the_post_thumbnail_url(null, 'full');
      if (!$hero) {
        $hero = $theme_uri . '/assets/img/mfs.png';
      }

      
      $atts = shortcode_atts([
        'video' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
      ], $atts, 'ks_franchise');

      
      $video_embed = wp_oembed_get(esc_url($atts['video']));
      if (!$video_embed) {
        $yt = preg_replace('~.*(?:v=|be/)([^&?]+).*~', '$1', (string)$atts['video']);
        $video_embed = '<iframe class="ks-vid-embed" src="https://www.youtube.com/embed/' . esc_attr($yt) . '" allowfullscreen loading="lazy"></iframe>';
      }

      
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

      $fr_abs = $theme_dir . '/assets/css/ks-franchise.css';
      if (file_exists($fr_abs) && !wp_style_is('ks-franchise', 'enqueued')) {
        wp_enqueue_style(
          'ks-franchise',
          $theme_uri . '/assets/css/ks-franchise.css',
          ['kickstart-style', 'ks-utils', 'ks-home'],
          filemtime($fr_abs)
        );
      }

    
      $inline_handle = wp_style_is('ks-franchise', 'enqueued')
        ? 'ks-franchise'
        : (wp_style_is('ks-home', 'enqueued') ? 'ks-home' : 'kickstart-style');

      wp_add_inline_style(
        $inline_handle,
        '#fr-hero{--hero-img:url("' . esc_url($hero) . '")}'
      );

      ob_start(); ?>

      
      <section id="fr-hero"
               class="ks-dir__hero ks-sec"
               data-watermark="FRANCHISE">
        <div class="ks-dir__hero-inner">
          <div class="ks-dir__crumb">
            <a class="ks-dir__crumb-home" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span class="sep">/</span>
            Franchise
          </div>

          <h1 class="ks-dir__hero-title">Franchise</h1>
         
        </div>
      </section>

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
              <?php echo $video_embed; ?>
            </div>
            <div class="ks-right-below">
              <p><strong>Warum DFS?</strong> Erprobtes Konzept, Trainings-Know-how, Marketing-Support und laufende Beratung.</p>
            </div>
          </div>
        </div>
      </section>

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
              <li><span class="ks-list-plus__icon" aria-hidden="true"></span><span>25 Jahre Erfahrung</span></li>
              <li><span class="ks-list-plus__icon" aria-hidden="true"></span><span>13 Partner &gt; 10 Trainer</span></li>
              <li><span class="ks-list-plus__icon" aria-hidden="true"></span><span>&gt; 700 Kinder &amp; 10 Partnervereine</span></li>
              <li><span class="ks-list-plus__icon" aria-hidden="true"></span><span>Wöchentliche Trainerfortbildungen</span></li>
              <li><span class="ks-list-plus__icon" aria-hidden="true"></span><span>Streamingportal mit &gt; 1000 Videos</span></li>
            </ul>
          </div>
        </div>
      </section>


<section id="fr-worldwide-map" class="ks-sec ks-py-48 ks-bg-world">
  <div class="container container--1100">
    <div class="ks-kicker">UNSERE STANDORTE UND FRANCHISES</div>
    <h2 class="ks-dir__title ks-mb-8">DFS WORLDWIDE</h2>
    <p class="fr-worldwide__sub">Klicke auf ein Land oder bewege die Maus – dann siehst du alle Standorte.</p>

    <div
      class="fr-worldwide__wrap"
      data-fr-worldmap
      data-svg="<?php echo esc_url($theme_uri . '/assets/img/franchise/world.svg'); ?>"
      data-api="<?php echo esc_url(rest_url('ks/v1/franchise-locations')); ?>"
    >
      <div class="fr-worldwide__loading" data-fr-loading>Standorte laden…</div>
      <div class="fr-worldwide__stage" data-fr-stage></div>
      <div class="fr-worldwide__tooltip" data-fr-tooltip aria-hidden="true"></div>
    </div>
  </div>
</section>

      
        <section id="fr-benefits" class="ks-sec ks-py-48 ks-section--soft ks-wm-top-80">
        <div class="container container--1100">

          <div class="ks-title-wrap" data-bgword="VORTEILE">
            <div class="ks-kicker">WOFÜR WIR STEHEN</div>
            <h2 class="ks-dir__title">Franchise Vorteile</h2>
          </div>

          <div class="ks-benefits">
            <div class="ks-benefit">
              <img class="ks-benefit__icon"
                   src="<?php echo esc_url($theme_uri . '/assets/img/franchise/cup.png'); ?>"
                   alt="Bewährtes Geschäftsmodell" loading="lazy">
              <h3>Bewährtes Geschäftsmodell</h3>
              <p>Franchise-Partner nutzen ein erprobtes Konzept, das bereits erfolgreich ist.</p>
            </div>

            <div class="ks-benefit">
              <img class="ks-benefit__icon"
                   src="<?php echo esc_url($theme_uri . '/assets/img/franchise/handshake.png'); ?>"
                   alt="Umfassende Unterstützung" loading="lazy">
              <h3>Umfassende Unterstützung</h3>
              <p>Schulungen, Marketingressourcen und laufende Beratung fördern den Erfolg der Partner.</p>
            </div>

            <div class="ks-benefit">
              <img class="ks-benefit__icon"
                   src="<?php echo esc_url($theme_uri . '/assets/img/franchise/wachs.png'); ?>"
                   alt="Schneller Marktzugang" loading="lazy">
              <h3>Schneller Marktzugang</h3>
              <p>Von einer etablierten Marke und einem bestehenden Kundenstamm profitieren.</p>
            </div>
          </div>

        </div>
      </section>

      <?php
      
      $fr_faq_items = function_exists('ks_get_faq_items')
        ? ks_get_faq_items('franchise')
        : [];

      if (!empty($fr_faq_items)) {
        // $fr_image = $theme_uri . '/assets/img/franchise/mfs.png';

        // echo ks_render_faq_section($fr_faq_items, [
        //   'section_id'    => 'fr-faq',
        //   'wrapper_class' => 'container fr-faq',
        //   'title'         => 'Häufig gestellte Fragen',
        //   'kicker'        => 'FAQ',
        //   'watermark'     => 'FAQ',
        //   'use_video'     => false,
        //   'image_src'     => $fr_image,
        //   'image_class'   => 'fr-faq__image',
        // ]);

echo ks_render_faq_section($fr_faq_items, [
  'section_id'         => 'fr-faq',
  'wrapper_class'      => 'container fr-faq',
  'title'              => 'Fragen zur Partnerschaft',
  'kicker'             => 'Gut zu wissen',
  'watermark'          => 'FAQ',
  'side_card_enabled'  => true,
  'side_card_kicker'   => 'Interesse geweckt?',
  'side_card_title'    => 'Lass uns ins Gespräch kommen',
  'side_card_text'     => 'Wenn du mehr über eine Partnerschaft erfahren möchtest, begleiten wir dich gerne im nächsten Schritt.',
  'side_card_button'   => 'Kontakt aufnehmen',
  'side_card_href'     => '#kontakt',
  'use_video'          => false,
  'image_src'          => '',
  'image_class'        => 'fr-faq__image',
]);
      }
      ?>
<?php
get_template_part('inc/partials/shared/contact-form', null, [
  'ks_contact' => [
    'show_map' => false, 
    'map_url'  => '',

    'kicker'   => 'KONTAKT',
    'title'    => 'Hast Du Fragen?',
    'bgword'   => '', 

    'brand'    => 'Dortmunder Fussball Schule',
    'subtitle' => 'Unser Office-Team ist täglich von 09:00 – 12:00 Uhr für Dich da und beantwortet gerne alle Deine Fragen.',

    'address_line1' => 'Hochfelder Straße 33',
    'address_line2' => '47226 Duisburg',
    'phone'         => '0176 43203362',
    'email'         => 'fussballschule@selcuk-kocyigit.de',
  ],
]);
?>
      <?php
      return ob_get_clean();
    });
  }

  add_action('init', 'ks_register_franchise_shortcode');
}









