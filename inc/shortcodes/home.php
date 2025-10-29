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


// JS: Program-CTA
$cta_js = $theme_dir . '/assets/js/ks-program-cta.js';
if (file_exists($cta_js)) {
  wp_enqueue_script(
    'ks-program-cta',
    $theme_uri . '/assets/js/ks-program-cta.js',
    [],
    filemtime($cta_js),
    true
  );
}




      /* ==== Feedback CSS/JS laden ==== */
$fb_css = $theme_dir . '/assets/css/ks-feedback.css';
if (file_exists($fb_css)) {
  wp_enqueue_style('ks-feedback', $theme_uri . '/assets/css/ks-feedback.css', ['ks-utils'], filemtime($fb_css));
}
$fb_js = $theme_dir . '/assets/js/ks-feedback.js';
if (file_exists($fb_js)) {
  wp_enqueue_script('ks-feedback', $theme_uri . '/assets/js/ks-feedback.js', [], filemtime($fb_js), true);
}


$wa_css = $theme_dir . '/assets/css/ks-wa.css';
if (file_exists($wa_css)) {
  wp_enqueue_style('ks-wa', $theme_uri . '/assets/css/ks-wa.css', [], filemtime($wa_css));
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
      $icon1 = $theme_uri . '/assets/img/home/mfs.png';
      $icon2 = $theme_uri . '/assets/img/home/mfs.png';
      $icon3 = $theme_uri . '/assets/img/home/mfs.png';

      /* ==== Video (oEmbed) ==== */
      $video_embed = wp_oembed_get('https://www.youtube.com/watch?v=8ZcmTl_1ER8');
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

      /* ==== JS für Tabs (Footer) ==== */
      wp_register_script('ks-home-hero', false, [], null, true);
      wp_enqueue_script('ks-home-hero');
      wp_add_inline_script(
        'ks-home-hero',
        "(function(){
          var hero=document.getElementById('home-hero');
          if(!hero) return;
          var tabs=hero.querySelectorAll('.ks-hero-tab');
          var slides=hero.querySelectorAll('.ks-hero-slide');
          function act(k){
            slides.forEach(function(s){ s.classList.toggle('is-active', s.dataset.key===k); });
            tabs.forEach(function(t){ t.classList.toggle('is-active', t.dataset.key===k); });
            var a=hero.querySelector('.ks-hero-slide.is-active');
            if(a){ hero.setAttribute('data-watermark', a.dataset.watermark||''); }
          }
          tabs.forEach(function(t){ t.addEventListener('click', function(){ act(t.dataset.key); }); });
        })();"
      );

      /* ==== TEAM Daten serverseitig laden ==== */
      $next_base = function_exists('ks_next_base') ? ks_next_base() : '';
      // Kandidaten: öffentliche API, dann Admin-API
      $team_api_candidates = [];
      if ($next_base) {
        $team_api_candidates[] = trailingslashit($next_base) . 'api/coaches?limit=48';
        $team_api_candidates[] = trailingslashit($next_base) . 'api/admin/coaches?limit=48';
      }
      $coaches = [];
      foreach ($team_api_candidates as $api) {
        $res = wp_remote_get($api, ['timeout'=>10, 'headers'=>['Accept'=>'application/json']]);
        if (!is_wp_error($res) && wp_remote_retrieve_response_code($res) === 200) {
          $json = json_decode(wp_remote_retrieve_body($res), true);
          if (isset($json['items']) && is_array($json['items'])) {
            $coaches = $json['items'];
            break;
          } elseif (is_array($json)) {
            $coaches = $json;
            break;
          }
        }
      }

      /* ==== Team-Karussell-JS (separate Datei, optional) ==== */
      $team_js = $theme_dir . '/assets/js/ks-team.js';
      if (file_exists($team_js)) {
        wp_enqueue_script('ks-team', $theme_uri . '/assets/js/ks-team.js', [], filemtime($team_js), true);
      }

      // Helper zum Normalisieren der Bild-URL (http/https ODER data:image/ erlauben)
      if (!function_exists('ks_normalize_next_img')) {
        function ks_normalize_next_img($u) {
          $u = trim((string)$u);
          if ($u === '') return '';

          // Absolut (http/https) ODER Base64-Data-URL → direkt zurückgeben
          if (preg_match('~^(https?://|data:image/)~i', $u)) {
            return $u;
          }

          // führendes "admin/" entfernen
          $u = preg_replace('#^/?admin/#i', '', $u);

          // relative Pfade an NEXT-Base anhängen
          $base = function_exists('ks_next_base') ? rtrim(ks_next_base(), '/') : '';
          if ($base) {
            if ($u[0] !== '/') $u = '/'.$u;
            return $base.$u;
          }
          return $u;
        }
      }

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
              <a class="ks-btn ks-btn--ghost" href="<?php echo esc_url($about_url); ?>">MEHR</a>
            </p>
          </div>
          <div class="ks-vid"><?php echo $video_embed; ?></div>
        </div>
      </section>

      <!-- 3) Unsere Werte -->
      <section id="werte" class="ks-sec ks-py-56 ks-home-values">
        <div class="container">
          <div class="ks-kicker">WOFÜR WIR STEHEN</div>
          <h2 class="ks-dir__title">Unsere Werte</h2>

          <div class="ks-values">
            <div class="ks-value">
              <img src="<?php echo esc_url($icon1); ?>" alt="" loading="lazy">
              <h3>Spass &amp; Freude</h3>
              <p>Der Spaß am Fußball steht bei uns an erster Stelle!</p>
            </div>
            <div class="ks-value">
              <img src="<?php echo esc_url($icon2); ?>" alt="" loading="lazy">
              <h3>Sportliches Know-how</h3>
              <p>Regelmäßige Schulungen für unsere Trainer*innen.</p>
            </div>
            <div class="ks-value">
              <img src="<?php echo esc_url($icon3); ?>" alt="" loading="lazy">
              <h3>Vorbilder</h3>
              <p>Unser Handeln prägt die Spieler*innen nachhaltig.</p>
            </div>
          </div>
        </div>
      </section>

      <!-- 4) FAQ -->
      <?php $theme_uri_local = $theme_uri; ?>
      <section id="faq"
        class="ks-sec ks-py-56"
        style="--acc-plus:url('<?php echo $theme_uri_local; ?>/assets/img/home/plus.png');
               --acc-minus:url('<?php echo $theme_uri_local; ?>/assets/img/home/minus.png');">

        <div class="container ks-home-faq">
          <div class="ks-title-wrap" data-bgword="FAQ">
            <div class="ks-kicker">FAQ</div>
            <h2 class="ks-dir__title">Häufig gestellte Fragen</h2>
          </div>

          <div>
            <details open class="ks-acc">
              <summary>Wo finden eure Trainingsangebote statt?</summary>
              <div class="ks-acc__body">Unsere Trainings finden auf den Sportanlagen unserer Partnervereine und in den Soccerhallen in und um NRW statt.</div>
            </details>

            <details class="ks-acc">
              <summary>Wer kann teilnehmen?</summary>
              <div class="ks-acc__body">Kinder, Jugendliche und Erwachsene – wir haben passende Gruppen für alle Altersstufen.</div>
            </details>

            <details class="ks-acc">
              <summary>Wie bekomme ich die neuesten Informationen?</summary>
              <div class="ks-acc__body">Abonniere unseren Newsletter oder folge uns auf Social Media.</div>
            </details>

            <details class="ks-acc">
              <summary>Wie kann ich Mitglied werden?</summary>
              <div class="ks-acc__body">Buche ein Schnuppertraining – wir erklären dir alles weitere vor Ort.</div>
            </details>
          </div>

          <div class="ks-home-faq__image" aria-hidden="true"></div>
        </div>
      </section>

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

      <!-- 5) Team -->
      <section id="team" class="ks-sec ks-py-56 ks-bg-white">
        <div class="container container--1400">
          <div class="ks-kicker">Wir sind für dich da</div>
          <h2 class="ks-dir__title">Unser Team</h2>

          <div class="ks-team-wrap">
            <button class="ks-team__nav ks-team__nav--prev" aria-label="Zurück">
              <img src="<?php echo esc_url($theme_uri . '/assets/img/home/left.png'); ?>" alt="" width="28" height="28" />
            </button>

            <div id="ksTeamCarousel" class="ks-team">

              <?php
                // Trainer-Seite finden (erst per Slug, dann per Shortcode), Fallback /trainer/
                $trainer_url = '';
                $page_by_path = get_page_by_path('trainer');
                if ($page_by_path) {
                  $trainer_url = get_permalink($page_by_path->ID);
                }
                if (!$trainer_url) {
                  $pages = get_posts([
                    'post_type'      => 'page',
                    's'              => '[ks_trainer_profile]',
                    'posts_per_page' => 1,
                  ]);
                  if (!empty($pages)) {
                    $trainer_url = get_permalink($pages[0]->ID);
                  }
                }
                if (!$trainer_url) {
                  $trainer_url = home_url('/trainer/');
                }
              ?>

              <ul class="ks-team__track" aria-live="polite">
                <?php if (!empty($coaches)): ?>
                  <?php foreach ($coaches as $c):
                    $first = isset($c['firstName']) ? $c['firstName'] : '';
                    $last  = isset($c['lastName'])  ? $c['lastName']  : '';
                    $full  = trim(($c['name'] ?? '') ?: trim($first . ' ' . $last));
                    if ($full === '') $full = 'Trainer';

                    $slug = isset($c['slug']) && $c['slug'] !== '' ? $c['slug'] : sanitize_title($full);

                    // Bild-URL robust normalisieren (http/https ODER data:image/)
                    $rawImg = isset($c['photoUrl']) ? $c['photoUrl'] : '';
                    $img    = $rawImg ? ks_normalize_next_img($rawImg) : '';
                    if ($img === '') $img = $fallback_img;

                    $role = isset($c['position']) && $c['position'] ? $c['position'] : 'Trainer';

                    // Link auf Trainer-Seite mit Query ?c=<slug>
                    $href = add_query_arg('c', rawurlencode($slug), $trainer_url);
                  ?>
                    <li class="ks-team__card">
                      <a href="<?php echo esc_url($href); ?>">
                        <img
                          class="ks-team__img"
                          src="<?php echo esc_attr($img); ?>"
                          alt="<?php echo esc_attr($full); ?>"
                          loading="lazy"
                          decoding="async" />
                      </a>
                      <div class="ks-team__meta">
                        <div class="ks-team__role"><?php echo esc_html($role); ?></div>
                        <div class="ks-team__name"><a href="<?php echo esc_url($href); ?>"><?php echo esc_html($full); ?></a></div>
                      </div>
                    </li>
                  <?php endforeach; ?>
                <?php else: ?>
                  <li class="ks-team__card">
                    <div class="ks-team__meta">
                      <div class="ks-team__name">Keine Trainer gefunden.</div>
                    </div>
                  </li>
                <?php endif; ?>
              </ul>
            </div>

            <button class="ks-team__nav ks-team__nav--next" aria-label="Weiter">
              <img src="<?php echo esc_url($theme_uri . '/assets/img/home/right.png'); ?>" alt="" width="28" height="28" />
            </button>
          </div>
        </div>
      </section>












        <!-- 2.5) Feedback -->
<?php
  $fb_img = $theme_uri . '/assets/img/home/mfs.png'; // Platzhalter
  $feedbacks = [
    [
      'img'    => $fb_img,
      'quote'  => 'Ihr Einsatz, Ihre Energie, verbunden mit zielorientiertem Coaching ist fast unbezahlbar für die Jugendlichen.',
      'author' => 'Christian Gross',
      'meta'   => 'Schweizer Profitrainer (Tottenham Hotspur, VfB Stuttgart, Young Boys Bern)',
    ],
    [
      'img'    => $fb_img,
      'quote'  => 'Die Trainingsqualität und die Organisation sind außergewöhnlich jedes Detail stimmt.',
      'author' => 'Muster Coach',
      'meta'   => 'U17-Trainer, NRW',
    ],
    [
      'img'    => $fb_img,
      'quote'  => 'Fachlich top und menschlich nah so macht Förderung Sinn.',
      'author' => 'Elternstimme',
      'meta'   => 'Dortmund',
    ],
    [
      'img'    => $fb_img,
      'quote'  => 'Kindgerecht, motivierend, professionell klare Empfehlung.',
      'author' => 'Vereinsvorstand',
      'meta'   => 'Partnerverein',
    ],
  ];
?>






<section id="feedback" class="ks-sec ks-py-56 ks-fb" data-watermark="FEEDBACK" aria-label="Feedbacks">
  <nav class="ks-fb-tabs" aria-label="Feedback Auswahl">
    <?php foreach ($feedbacks as $i => $f): ?>
      <button type="button" class="ks-fb-tab<?php echo $i===0 ? ' is-active' : ''; ?>" data-key="fb-<?php echo $i; ?>">
        <span class="ks-fb-tab__label">Feedback</span>
        <span class="ks-fb-tab__num"><?php echo sprintf('%02d', $i+1); ?></span>
      </button>
    <?php endforeach; ?>
  </nav>


  

  <div class="container ks-fb-grid">

 
    
    <?php foreach ($feedbacks as $i => $f): ?>
      <article class="ks-fb-slide<?php echo $i===0 ? ' is-active' : ''; ?>" data-key="fb-<?php echo $i; ?>">
      
 
      <div class="ks-fb-media">
          <img src="<?php echo esc_url($f['img']); ?>" alt="" loading="lazy" decoding="async">
        </div>
  

        <div class="ks-fb-content">
          

          
  <div class="ks-fb-quote">
    <img
      class="quote-icon"
      src="<?php echo get_template_directory_uri(); ?>/assets/img/feedback/quote.svg"
      alt="" aria-hidden="true"
      loading="lazy" decoding="async"
    >
    <?php echo esc_html($f['quote']); ?>
  </div>

  


          <div class="ks-fb-author">
            <strong><?php echo esc_html(mb_strtoupper($f['author'])); ?></strong>
            <div class="ks-fb-meta"><?php echo esc_html($f['meta']); ?></div>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </div>



  
</section>



































      <?php if ($show_news): ?>
      <!-- 6) Neuigkeiten -->
      <section id="news" class="ks-sec ks-py-48">
        <div class="container">
          <div class="ks-kicker">Aktuelles</div>
          <h2 class="ks-dir__title">Neuigkeiten</h2>

          <div class="ks-news">
            <?php
            $q = new WP_Query([
              'posts_per_page'       => 3,
              'ignore_sticky_posts'  => true,
              'post_status'          => 'publish',
            ]);

            if ($q->have_posts()):
              while ($q->have_posts()): $q->the_post(); ?>
                <article class="ks-news__item">
                  <?php if (has_post_thumbnail()): ?>
                    <a href="<?php the_permalink(); ?>" class="ks-news__thumb"><?php the_post_thumbnail('medium_large'); ?></a>
                  <?php endif; ?>
                  <div class="ks-news__meta"><?php echo esc_html( get_the_date('d.m.Y') ); ?></div>
                  <h3 class="ks-news__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                  <p class="ks-news__excerpt"><?php echo esc_html( wp_trim_words(get_the_excerpt(), 22) ); ?></p>
                  <p><a class="ks-btn ks-btn--ghost" href="<?php the_permalink(); ?>">MEHR LESEN</a></p>
                </article>
              <?php endwhile;
              wp_reset_postdata();
            else: ?>
              <p>Keine Beiträge gefunden.</p>
            <?php endif; ?>
          </div>
        </div>
      </section>

      <!-- Brandbar -->
      <section id="brandbar" class="ks-sec ks-brandbar" aria-label="Partner & Marken">
        <div class="container">
          <ul class="ks-brandbar__list" role="list">
            <?php
              $brands = [
                [ 'src' => $theme_uri . '/assets/img/brands/bodosee-sportlo.svg', 'label' => 'Bodosee Sportlo' ],
                [ 'src' => $theme_uri . '/assets/img/home/mfs.png',               'label' => 'Puma' ],
                [ 'src' => $theme_uri . '/assets/img/brands/dfsberater.svg',      'label' => 'DFS Berater' ],
                [ 'src' => $theme_uri . '/assets/img/brands/teamstolz.svg',       'label' => 'Teamstolz' ],
                [ 'src' => $theme_uri . '/assets/img/brands/dfsplayer.svg',       'label' => 'DFS Player' ],
              ];
              foreach ($brands as $b):
                $src   = esc_url($b['src']);
                $label = esc_html($b['label']);
            ?>
              <li class="ks-brandbar__item">
                <img src="<?php echo $src; ?>" alt="" loading="lazy" decoding="async" aria-hidden="true">
                <span class="ks-brandbar__label"><?php echo $label; ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
      </section>
      <?php endif; ?>

      














<section id="program-cta" class="ks-sec ks-py-56 ks-cta-strip" aria-label="Programm schnell buchen">
  <div class="container ks-cta-grid">
   

       <div class="ks-cta-text">
      <div class="ks-eyebrow">Schnell buchen</div>
      <h2 class="ks-dir__title">Dein Programm auswählen</h2>
      <p>Wähle ein Angebot und starte direkt mit der Anmeldung.</p>
              </div>

      <?php
        $programs = [
          'Foerdertraining'  => 'Fördertraining',
          'PersonalTraining' => 'Einzeltraining',
          'Camp'             => 'Fußballcamp',
          'Kindergarten'     => 'Kindergarten',
        ];
      ?>
     <form id="ksProgramForm" class="ks-cta-form" action="<?php echo esc_url($offers); ?>" method="get">
  <!-- natives Select bleibt fürs Submit, wird vollständig versteckt -->


<label class="screen-reader-text" for="ksProgramSelect">Programm</label>
<select id="ksProgramSelect" name="type" class="ks-select" hidden>
  <option value="" selected disabled>Bitte auswählen …</option>
  <?php foreach ($programs as $val => $label): ?>
    <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($label); ?></option>
  <?php endforeach; ?>
</select>



  <!-- einzig sichtbares Dropdown (Custom UI wie beim Trainer) -->
  <div class="ks-dd" id="ks-dd-program" aria-expanded="false">
    <button type="button" class="ks-dd__btn" aria-haspopup="listbox" aria-expanded="false">
      <span class="ks-dd__label">Bitte wählen…</span>
      <span class="ks-dd__caret" aria-hidden="true"></span>
    </button>
    <div class="ks-dd__panel" role="listbox" tabindex="-1"></div>
  </div>
</form>

    </div>
  </div>
</section>









<?php

echo do_shortcode('[ks_whatsapp_locations]');

?>

<?php
      


      return ob_get_clean();
    });

  }

  add_action('init', 'ks_register_home_shortcode');
}














