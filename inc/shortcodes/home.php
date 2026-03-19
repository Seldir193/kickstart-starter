<?php
// inc/shortcodes/home.php
// Rendert die komplette Startseite via [ks_home]
// Attribute:
//   show_news="1|0"   -> News-Bereich ein/aus
//   portal_url="URL"  -> Ziel-Link für „MEHR INFOS“ im Video-Portal



if (!function_exists('ks_register_home_shortcode')) {
  function ks_register_home_shortcode() {

    add_shortcode('ks_home', function ($atts = []) {
      $theme_dir = get_stylesheet_directory();
      $theme_uri = get_stylesheet_directory_uri();

      /* ==== CSS laden ==== */
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
      if (file_exists($home_abs)) {
        wp_enqueue_style(
          'ks-home',
          $theme_uri . '/assets/css/ks-home.css',
          ['kickstart-style', 'ks-utils'],
          filemtime($home_abs)
        );
      }






// $dd_hover_css = $theme_dir . '/assets/css/dropdown-hover.css';
// if (file_exists($dd_hover_css)) {
//   wp_enqueue_style(
//     'ks-dropdown-hover',
//     $theme_uri . '/assets/css/dropdown-hover.css',
//     ['kickstart-style', 'ks-utils', 'ks-home'],
//     filemtime($dd_hover_css)
//   );
// }










$videos_css = $theme_dir . '/assets/css/ks-videos.css';
if (file_exists($videos_css)) {
  wp_enqueue_style(
    'ks-videos',
    $theme_uri . '/assets/css/ks-videos.css',
    ['ks-home'],                         // NACH ks-home.css
    filemtime($videos_css)
  );
}





      /* ==== Feedback CSS/JS laden (global) ==== */
      if (function_exists('ks_enqueue_feedback_assets')) {
        ks_enqueue_feedback_assets();
      }


$wa_css = $theme_dir . '/assets/css/ks-wa.css';
if (file_exists($wa_css)) {
  wp_enqueue_style('ks-wa', $theme_uri . '/assets/css/ks-wa.css', [], filemtime($wa_css));
}




// HERO: Autoplay & Animation
$hero_css = $theme_dir . '/assets/css/ks-hero-anim.css';
if (file_exists($hero_css)) {
  wp_enqueue_style(
    'ks-hero-anim',
    $theme_uri . '/assets/css/ks-hero-anim.css',
    ['ks-home'], // nach Grundlayout
    filemtime($hero_css)
  );
}
$hero_js  = $theme_dir . '/assets/js/ks-hero.js';
if (file_exists($hero_js)) {
  wp_enqueue_script(
    'ks-hero',
    $theme_uri . '/assets/js/ks-hero.js',
    [], // keine Abhängigkeiten
    filemtime($hero_js),
    true
  );
}



      /* ==== Shortcode-Attribute ==== */
      $atts = shortcode_atts([
        'show_news'  => '1',
        'portal_url' => home_url('/mfscoach-video-portal/'),
      ], $atts, 'ks_home');
      $show_news  = ($atts['show_news'] !== '0');
      $portal_url = esc_url($atts['portal_url']);

      /* ==== Links / Fallbacks ==== */
      $offers = function_exists('ks_offers_url') ? ks_offers_url() : home_url('/angebote/');
      $about_page = get_page_by_path('about');
      $about_url  = $about_page ? get_permalink($about_page->ID) : home_url('/index.php/about/');

      /* ==== Assets + Inline-Variablen fürs CSS ==== */
      $ball_img      = $theme_uri . '/assets/img/home/mfs.png';
      $portal_bg     = $theme_uri . '/assets/img/home/mfs.png';
      $portal_laptop = $theme_uri . '/assets/img/home/mfs.png';

     
    

      wp_add_inline_style(
        'ks-home',
        ".ks-home-faq__image{--faq-img:url('{$ball_img}')}" .
        ".ks-home-portal{--portal-bg:url('{$portal_bg}')}" .
        ".ks-home-portal__media{--portal-img:url('{$portal_laptop}')}"  
     
      );


      // Platzhalter-Icons – gern ersetzen
      $icon1 = $theme_uri . '/assets/img/home/dfs-home-1.png';
      $icon2 = $theme_uri . '/assets/img/home/dfs-home-4.png';
      $icon3 = $theme_uri . '/assets/img/home/dfs-home-5.png';
      $werte_target = home_url('/');

      /* ==== Video (oEmbed) ==== */
      $video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');
      if (!$video_embed) $video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';

      /* ==== Buchungs-URLs ==== */
      $book_urls = [
        'camp'    => add_query_arg(['type'=>'Camp'],             $offers),
        'foerder' => add_query_arg(['type'=>'Foerdertraining'],  $offers),
        'kita'    => add_query_arg(['type'=>'Kindergarten'],     $offers),
        'einzel'  => add_query_arg(['type'=>'PersonalTraining'], $offers),
      ];

      /* ==== Hero-Slides (Tabs) ==== */
      $fallback_img = $theme_uri . '/assets/img/home/mfs.png';
      $slides = [
        [
          'key' => 'camp', 'label' => 'CAMPS', 'num' => '01',
          'title' => 'Fussballcamps',
          'lead'  => 'Ein 3- bis 5-tägiges Fußballprogramm mit Tricks, Koordination, Torschüssen, Wettkämpfen und einem Abschlussturnier – ideal, um Technik und Spaß zu verbinden.',
          'watermark' => 'CAMPS',
          'img' => $fallback_img,
          'book' => $book_urls['camp'],
        ],
        [
          'key' => 'foerder', 'label' => 'TRAINING', 'num' => '02',
          'title' => 'Fördertraining',
          'lead'  => 'Verbessere dein Spiel durch unser wöchentliches Fördertraining! Dich erwarten Tricks, Schusstechniken, Koordination und tolle Abschlussspiele.',
          'watermark' => 'FÖRDERTRAINING',
          'img' => $fallback_img,
          'book' => $book_urls['foerder'],
        ],
        [
          'key' => 'kita', 'label' => 'KINDERGARTEN', 'num' => '03',
          'title' => 'Kindergarten',
          'lead'  => 'Bewegung, Koordination und Freude am Ball — spielerisch und altersgerecht im Kindergarten.',
          'watermark' => 'KINDERGARTEN',
          'img' => $fallback_img,
          'book' => $book_urls['kita'],
        ],
        [
          'key' => 'einzel', 'label' => 'EINZELTRAINING', 'num' => '04',
          'title' => 'Einzeltraining',
          'lead'  => '1-zu-1 Coaching: individuell, effizient und zielgerichtet — Technik, Torschuss, Athletik.',
          'watermark' => 'EINZELTRAINING',
          'img' => $fallback_img,
          'book' => $book_urls['einzel'],
        ],
      ];

  
 $coaches      = ks_get_coaches(48);
$trainer_url  = ks_get_trainer_url();
ks_enqueue_team_assets();


      /* ==== Markup ==== */
      ob_start(); ?>

      <!-- 1) HERO mit Tabs -->
      <section id="home-hero" class="ks-home-hero ks-sec" data-watermark="<?php echo esc_attr($slides[0]['watermark']); ?>">
        
                               <nav class="ks-hero-tabs" aria-label="Hero Auswahl">
          <?php foreach ($slides as $i => $s): ?>
            <button type="button"
              class="ks-hero-tab<?php echo $i===0 ? ' is-active' : ''; ?>"
              data-key="<?php echo esc_attr($s['key']); ?>"
              data-watermark="<?php echo esc_attr($s['watermark']); ?>">
              <span class="ks-hero-tab__label"><?php echo esc_html($s['label']); ?></span>
              <span class="ks-hero-tab__num"><?php echo esc_html($s['num']); ?></span>
            </button>
          <?php endforeach; ?>
        </nav>
         
        <div class="ks-hero-slides">
    

          <?php foreach ($slides as $i => $s): ?>
            <div class="ks-hero-slide<?php echo $i===0 ? ' is-active' : ''; ?>"
                 data-key="<?php echo esc_attr($s['key']); ?>"
                 data-watermark="<?php echo esc_attr($s['watermark']); ?>">
              <div class="container ks-home-grid">

      

                <div class="ks-home-hero__left">
                  <div class="ks-kicker">DORTMUNDER FUSSBALL SCHULE</div>
                  <h1 class="ks-home-hero__title"><?php echo esc_html($s['title']); ?></h1>
                  <p class="ks-home-hero__lead"><?php echo esc_html($s['lead']); ?></p>
                  <div class="ks-home-hero__actions">
                    <a class="ks-btn ks-btn--dark" href="#wer-wir-sind">MEHR LESEN</a>
                    <a class="ks-btn" href="<?php echo esc_url($s['book']); ?>">JETZT BUCHEN</a>
                  </div>
                </div>
                <div class="ks-home-hero__right">
                  <img class="ks-home-hero__img" src="<?php echo esc_url($s['img']); ?>" alt="">
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <!-- 2) Wer wir sind -->
      <section id="wer-wir-sind" class="ks-sec ks-py-48 ">
        <div class="container ks-split">
          <div>
            <div class="ks-kicker">Wer wir sind</div>
            <h2 class="ks-dir__title">Die Dortmunder Fussball Schule</h2>
            <p>Wir sind eine vereinsergänzende Institution, die Vereine in ihrer täglichen Arbeit mit Kindern, Jugendlichen
              und Erwachsenen unterstützt. Mit unserer ganzheitlichen Ausbildungsphilosophie, unserer außergewöhnlichen
              Trainingsorganisation und Trainingseffizienz begeistern wir jährlich über 700 Kinder.</p>
            <p>Unser Ziel ist es stets mit Spaß und Freude jeden Einzelnen maximal zu fördern – von den Kleinsten bis zu
              ambitionierten Talenten. Neben sportlichem Know-how ist es uns ein großes Anliegen, soziale Werte zu vermitteln.</p>
            <p class="ks-mt-16">
              <a class="ks-btn " href="<?php echo esc_url($about_url); ?>">MEHR</a>
            </p>
          </div>
          <div class="ks-vid ratio"><?php echo $video_embed; ?></div>
      
        </div>
      </section>

      <!-- 3) Unsere Werte -->
      <section id="werte"  class="ks-sec ks-py-56 ks-home-values">
        <div class="container" >
          
          <div class="ks-title-wrap" data-bgword="WERTE">
  <div class="ks-kicker">WOFÜR WIR STEHEN</div>
  <h2 class="ks-dir__title">Unsere Werte</h2>
</div>


          <div class="ks-values">
  <a class="ks-value" href="<?php echo esc_url($werte_target); ?>">

  <div class="icon-wrap">

    <img src="<?php echo esc_url($icon1); ?>" alt="" loading="lazy">
          </div>
    <h3>Spass &amp; Freude</h3>
    <p>Der Spaß am Fußball steht bei uns an erster Stelle!</p>
  </a>

  <a class="ks-value" href="<?php echo esc_url($werte_target); ?>">
    <div class="icon-wrap">
    <img src="<?php echo esc_url($icon2); ?>" alt="" loading="lazy">
          </div>
    <h3>Sportliches Know-how</h3>
    <p>Regelmäßige Schulungen für unsere Trainer*innen.</p>
  </a>

  <a class="ks-value" href="<?php echo esc_url($werte_target); ?>">
    <div class="icon-wrap">
    <img src="<?php echo esc_url($icon3); ?>" alt="" loading="lazy">
          </div>
    <h3>Vorbilder</h3>
    <p>Unser Handeln prägt die Spieler*innen nachhaltig.</p>
  </a>
</div>

      
    
        </div>
      </section>

   <?php
  // FAQ für Startseite (Texte kommen aus inc/faq-texts-home.de.php)
  $faq_items = ks_get_faq_items('home');

  if (!empty($faq_items)) {
    // Video wie bisher
    $faq_video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');
    if (!$faq_video_embed) {
      $faq_video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';
    }

    echo ks_render_faq_section($faq_items, [
      'section_id'    => 'faq',                    // ID bleibt #faq
      'wrapper_class' => 'container ks-home-faq',  // Layout wie bisher
      'title'         => 'Häufig gestellte Fragen',
      'kicker'        => 'FAQ',
      'watermark'     => 'FAQ',                    // großes „FAQ“ im Hintergrund
      'use_video'     => true,
      'video_embed'   => $faq_video_embed,
    ]);
  }
?>

     



 <!-- 4.5) MFSCoach Video-Portal -->
      <section id="coach-portal" class="ks-sec ks-home-portal" aria-label="MFSCoach Video Portal">
        <div class="container ks-home-portal__grid">
          <div class="ks-home-portal__text">
            <div class="ks-eyebrow">Training &amp; Taktik für Trainer aller Altersklassen</div>
            <h2 class="ks-home-portal__title"><span>DFSCOACH</span> VIDEO PORTAL</h2>
            <p>Die DFS Trainingsphilosophie in über 1000 Videos. Folge unserer Grundausbildung
               oder erstelle eigene Trainingspläne.</p>
            <p class="ks-home-portal__actions">
              <a class="ks-btn ks-btn--light" href="<?php echo $portal_url; ?>">MEHR INFOS</a>
            </p>
          </div>

          <div class="ks-home-portal__media" role="presentation" aria-hidden="true"></div>
        </div>
      </section>







<?php
include $theme_dir . '/inc/partials/shared/team-section.php';
?>



    




      <?php
      // Globale Feedback-Section (selbe Logik/Optik)
      if (function_exists('ks_render_feedback_section')) {
        echo ks_render_feedback_section();
      }
      ?>
















<?php echo do_shortcode('[ks_brandbar]'); ?>


 












<?php if ($show_news): ?>

<section id="news" class="ks-sec ks-py-48">
  <div class="container">
    

    <div class="ks-title-wrap" data-bgword="NEWS">
  <div class="ks-kicker">Aktuelles</div>
  <h2 class="ks-dir__title">Neuigkeiten</h2>
</div>

  </div>

  <?php echo do_shortcode('[ks_news_latest limit="3" thumbs="0"]'); ?>
</section>
<?php endif; ?>



  


      














<?php

$program_cta_partial = $theme_dir . '/inc/partials/home/program-cta.php';
if (file_exists($program_cta_partial)) {
  include $program_cta_partial;
}

?>



<?php

echo do_shortcode('[ks_whatsapp_locations]');

?>

<?php
      


      return ob_get_clean();
    }
  
  
  
  
  );

  }

  add_action('init', 'ks_register_home_shortcode');
}














