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

      <section id="fr-intro" class="ks-sec ks-py-48 ks-home-about">
  <div class="container ks-home-about__grid">
    <div class="ks-home-about__content">
      <div class="ks-kicker" data-i18n="franchise.intro.kicker">
        Shared success
      </div>

      <h2 class="ks-dir__title ks-dir__title--split">
        <span class="ks-dir__title-line" data-i18n="franchise.intro.titleLine1">
          Franchise with the
        </span>
        <span class="ks-dir__title-line" data-i18n="franchise.intro.titleLine2">
          Dortmund Football School
        </span>
      </h2>

      <p class="ks-home-about__lead" data-i18n="franchise.intro.lead">
        The franchise model of Dortmund Football School gives you the opportunity to combine your passion for football with a profitable business concept built on practical experience and a clear football philosophy.
      </p>

      <div class="ks-home-about__points">
        <div class="ks-home-about__point">
          <strong data-i18n="franchise.intro.point1Title">Proven model</strong>
          <span data-i18n="franchise.intro.point1Text">Benefit from a structured concept, practical experience and a strong foundation for sustainable growth.</span>
        </div>
        <div class="ks-home-about__point">
          <strong data-i18n="franchise.intro.point2Title">Strong support</strong>
          <span data-i18n="franchise.intro.point2Text">Use training know-how, marketing support and ongoing guidance for your local development.</span>
        </div>
      </div>

      <div class="ks-home-about__actions">
        <a
          href="#fr-worldwide"
          class="ks-btn js-scroll"
          data-i18n="franchise.intro.button"
        >Learn more</a>
      </div>
    </div>

    <div class="ks-home-about__media">
      <div class="ks-home-about__badge" data-i18n="franchise.intro.mediaBadge">
        Insight into our franchise model
      </div>

      <div class="ks-vid ratio"><?php echo $video_embed; ?></div>

      <p class="ks-home-about__caption" data-i18n="franchise.intro.mediaCaption">
        Discover how our concept combines football know-how, structure and long-term partnership.
      </p>

      <div
        class="ks-home-about__chips"
        data-i18n-attr="aria-label"
        data-i18n="franchise.intro.chipsLabel"
        aria-label="Franchise highlights"
      >
        <span class="ks-home-about__chip" data-i18n="franchise.intro.chip1">Proven concept</span>
        <span class="ks-home-about__chip" data-i18n="franchise.intro.chip2">Marketing support</span>
        <span class="ks-home-about__chip" data-i18n="franchise.intro.chip3">Long-term partnership</span>
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
      <div class="ks-kicker" data-i18n="franchise.benefits.kicker">WOFÜR WIR STEHEN</div>
      <h2 class="ks-dir__title" data-i18n="franchise.benefits.title">Franchise Vorteile</h2>
    </div>

    <div class="ks-fr-benefits">
      <article class="ks-fr-benefit">
        <div class="ks-fr-benefit__icon-wrap">
          <img
            src="<?php echo esc_url($theme_uri . '/assets/img/franchise/cup.png'); ?>"
            alt=""
            loading="lazy"
          >
        </div>
        <h3 class="ks-fr-benefit__title">
          <span data-i18n="franchise.benefits.items.model.titleLine1">BEWÄHRTES</span>
          <span data-i18n="franchise.benefits.items.model.titleLine2">KONZEPT</span>
        </h3>
        <p data-i18n="franchise.benefits.items.model.text">
          Ein klares Franchise-System mit starker Basis für nachhaltiges Wachstum.
        </p>
      </article>

      <article class="ks-fr-benefit">
        <div class="ks-fr-benefit__icon-wrap">
          <img
            src="<?php echo esc_url($theme_uri . '/assets/img/franchise/handshake.png'); ?>"
            alt=""
            loading="lazy"
          >
        </div>
        <h3 class="ks-fr-benefit__title">
          <span data-i18n="franchise.benefits.items.support.titleLine1">STARKE</span>
          <span data-i18n="franchise.benefits.items.support.titleLine2">BEGLEITUNG</span>
        </h3>
        <p data-i18n="franchise.benefits.items.support.text">
          Profitiere von Know-how, Marketing-Support und persönlicher Unterstützung.
        </p>
      </article>

      <article class="ks-fr-benefit">
        <div class="ks-fr-benefit__icon-wrap">
          <img
            src="<?php echo esc_url($theme_uri . '/assets/img/franchise/wachs.png'); ?>"
            alt=""
            loading="lazy"
          >
        </div>
        <h3 class="ks-fr-benefit__title">
          <span data-i18n="franchise.benefits.items.market.titleLine1">SCHNELLER</span>
          <span data-i18n="franchise.benefits.items.market.titleLine2">START</span>
        </h3>
        <p data-i18n="franchise.benefits.items.market.text">
          Starte mit Struktur, Marke und klaren Prozessen in deine Region.
        </p>
      </article>

      <article class="ks-fr-benefit">
        <div class="ks-fr-benefit__icon-wrap">
          <img
            src="<?php echo esc_url($theme_uri . '/assets/img/franchise/partnership.svg'); ?>"
            alt=""
            loading="lazy"
          >
        </div>
        <h3 class="ks-fr-benefit__title">
          <span data-i18n="franchise.benefits.items.partner.titleLine1">LANGFRISTIGE</span>
          <span data-i18n="franchise.benefits.items.partner.titleLine2">PARTNERSCHAFT</span>
        </h3>
        <p data-i18n="franchise.benefits.items.partner.text">
          Entwickle deinen Standort mit Austausch, Standards und Zusammenarbeit.
        </p>
      </article>
    </div>
  </div>
</section>



      <?php
      
      $fr_faq_items = function_exists('ks_get_faq_items')
        ? ks_get_faq_items('franchise')
        : [];

      if (!empty($fr_faq_items)) {
        

// echo ks_render_faq_section($fr_faq_items, [
//   'section_id'         => 'fr-faq',
//   'wrapper_class'      => 'container fr-faq',
//   'title'              => 'Fragen zur Partnerschaft',
//   'kicker'             => 'Gut zu wissen',
//   'watermark'          => 'FAQ',
//   'side_card_enabled'  => true,
//   'side_card_kicker'   => 'Interesse geweckt?',
//   'side_card_title'    => 'Lass uns ins Gespräch kommen',
//   'side_card_text'     => 'Wenn du mehr über eine Partnerschaft erfahren möchtest, begleiten wir dich gerne im nächsten Schritt.',
//   'side_card_button'   => 'Kontakt aufnehmen',
//   'side_card_href'     => '#kontakt',
//   'use_video'          => false,
//   'image_src'          => '',
//   'image_class'        => 'fr-faq__image',
// ]);

echo ks_render_faq_section($fr_faq_items, [
  'section_id'              => 'fr-faq',
  'wrapper_class'           => 'container fr-faq',
  'title'                   => 'Fragen zur Partnerschaft',
  'kicker'                  => 'Gut zu wissen',
  'watermark'               => 'FAQ',
  'title_i18n'              => 'franchise.faq.title',
  'kicker_i18n'             => 'franchise.faq.kicker',
  'items_i18n_prefix'       => 'franchise.faq',
  'side_card_enabled'       => true,
  'side_card_kicker'        => 'Interesse geweckt?',
  'side_card_title'         => 'Lass uns ins Gespräch kommen',
  'side_card_text'          => 'Wenn du mehr über eine Partnerschaft erfahren möchtest, begleiten wir dich gerne im nächsten Schritt.',
  'side_card_button'        => 'Kontakt aufnehmen',
  'side_card_href'          => '#kontakt',
  'side_card_kicker_i18n'   => 'franchise.faq.sideCard.kicker',
  'side_card_title_i18n'    => 'franchise.faq.sideCard.title',
  'side_card_text_i18n'     => 'franchise.faq.sideCard.text',
  'side_card_button_i18n'   => 'franchise.faq.sideCard.button',
  'use_video'               => false,
  'image_src'               => '',
  'image_class'             => 'fr-faq__image',
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









