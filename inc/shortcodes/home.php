<?php

if (!function_exists('ks_register_home_shortcode')) {
  function ks_register_home_shortcode() {
    add_shortcode('ks_home', function ($atts = []) {
      $theme_dir = get_stylesheet_directory();
      $theme_uri = get_stylesheet_directory_uri();

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
          ['kickstart-style', 'ks-utils', 'ks-watermark'],
          filemtime($home_abs)
        );
      }

      $hero_split_abs = $theme_dir . '/assets/css/ks-home-hero.css';

      if (file_exists($hero_split_abs)) {
        wp_enqueue_style(
          'ks-home-hero',
          $theme_uri . '/assets/css/ks-home-hero.css',
          ['ks-home'],
          filemtime($hero_split_abs)
        );
      }

      $videos_css = $theme_dir . '/assets/css/ks-videos.css';

      if (file_exists($videos_css)) {
        wp_enqueue_style(
          'ks-videos',
          $theme_uri . '/assets/css/ks-videos.css',
          ['ks-home'],
          filemtime($videos_css)
        );
      }

      if (function_exists('ks_enqueue_feedback_assets')) {
        ks_enqueue_feedback_assets();
      }

      $wa_css = $theme_dir . '/assets/css/ks-wa.css';

      if (file_exists($wa_css)) {
        wp_enqueue_style(
          'ks-wa',
          $theme_uri . '/assets/css/ks-wa.css',
          [],
          filemtime($wa_css)
        );
      }

      $hero_css = $theme_dir . '/assets/css/ks-hero-anim.css';

      if (file_exists($hero_css)) {
        wp_enqueue_style(
          'ks-hero-anim',
          $theme_uri . '/assets/css/ks-hero-anim.css',
          ['ks-home'],
          filemtime($hero_css)
        );
      }

      $hero_js = $theme_dir . '/assets/js/ks-hero.js';

      if (file_exists($hero_js)) {
        wp_enqueue_script(
          'ks-hero',
          $theme_uri . '/assets/js/ks-hero.js',
          [],
          filemtime($hero_js),
          true
        );
      }

      $atts = shortcode_atts([
        'show_news' => '1',
        'portal_url' => home_url('/mfscoach-video-portal/'),
      ], $atts, 'ks_home');

      $show_news = ($atts['show_news'] !== '0');
      $portal_url = esc_url($atts['portal_url']);

      $offers = function_exists('ks_offers_url')
        ? ks_offers_url()
        : home_url('/angebote/');

      $about_page = get_page_by_path('about');
      $about_url = $about_page
        ? get_permalink($about_page->ID)
        : home_url('/index.php/about/');

      $home_t = function ($key, $fallback) {
        return function_exists('ks_t') ? ks_t($key, $fallback, 'home') : $fallback;
      };

      $news_t = function ($key, $fallback) {
        return function_exists('ks_t') ? ks_t($key, $fallback, 'news') : $fallback;
      };

      $ball_img = $theme_uri . '/assets/img/home/mfs.png';
      $portal_bg = $theme_uri . '/assets/img/home/mfs.png';
      $portal_laptop = $theme_uri . '/assets/img/home/mfs.png';

      wp_add_inline_style(
        'ks-home',
        ".ks-home-faq__image{--faq-img:url('{$ball_img}')}" .
        ".ks-home-portal{--portal-bg:url('{$portal_bg}')}" .
        ".ks-home-portal__media{--portal-img:url('{$portal_laptop}')}"
      );

      $icon1 = $theme_uri . '/assets/img/home/dfs-home-1.png';
      $icon2 = $theme_uri . '/assets/img/home/dfs-home-4.png';
      $icon3 = $theme_uri . '/assets/img/home/dfs-home-5.png';
      $icon4 = $theme_uri . '/assets/img/home/individual-development.svg';
      $werte_target = home_url('/');

      $video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');

      if (!$video_embed) {
        $video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';
      }

      $book_urls = [
        'camp' => add_query_arg(['type' => 'Camp'], $offers),
        'foerder' => add_query_arg(['type' => 'Foerdertraining'], $offers),
        'kita' => add_query_arg(['type' => 'Kindergarten'], $offers),
        'einzel' => add_query_arg(['type' => 'PersonalTraining'], $offers),
      ];

      $slides = [
        [
          'key' => 'camp',
          'label' => $home_t('home.hero.slides.camp.label', 'CAMPS'),
          'num' => '01',
          'title' => $home_t('home.hero.slides.camp.title', 'Fussballcamps'),
          'lead' => $home_t('home.hero.slides.camp.lead', 'Ferienprogramme mit Technik, Spielformen und Teamgefühl.'),
          'detail' => $home_t('home.hero.slides.camp.detail', 'Ein 3- bis 5-tägiges Fußballprogramm mit Tricks, Koordination, Torschüssen, Wettkämpfen und einem Abschlussturnier – ideal, um Technik und Spaß zu verbinden.'),
          'img' => $theme_uri . '/assets/img/home/left.png',
          'book' => $book_urls['camp'],
        ],
        [
          'key' => 'foerder',
          'label' => $home_t('home.hero.slides.foerder.label', 'TRAINING'),
          'num' => '02',
          'title' => $home_t('home.hero.slides.foerder.title', 'Fördertraining'),
          'lead' => $home_t('home.hero.slides.foerder.lead', 'Zusatztraining für Technik, Koordination und Spielverständnis.'),
          'detail' => $home_t('home.hero.slides.foerder.detail', 'Verbessere dein Spiel durch unser wöchentliches Fördertraining. Dich erwarten Tricks, Schusstechniken, Koordination und tolle Abschlussspiele.'),
          'img' => $theme_uri . '/assets/img/home/mfs.png',
          'book' => $book_urls['foerder'],
        ],
        [
          'key' => 'kita',
          'label' => $home_t('home.hero.slides.kita.label', 'KINDERGARTEN'),
          'num' => '03',
          'title' => $home_t('home.hero.slides.kita.title', 'Kindergarten'),
          'lead' => $home_t('home.hero.slides.kita.lead', 'Spielerische Bewegung und erste Ballerfahrungen für Kinder.'),
          'detail' => $home_t('home.hero.slides.kita.detail', 'Bewegung, Koordination und Freude am Ball – spielerisch und altersgerecht im Kindergarten mit viel Aktivität und Begeisterung.'),
          'img' => $theme_uri . '/assets/img/home/dfs-home-1.png',
          'book' => $book_urls['kita'],
        ],
        [
          'key' => 'einzel',
          'label' => $home_t('home.hero.slides.einzel.label', 'EINZELTRAINING'),
          'num' => '04',
          'title' => $home_t('home.hero.slides.einzel.title', 'Einzeltraining'),
          'lead' => $home_t('home.hero.slides.einzel.lead', 'Individuelle Einheiten mit persönlichem Fokus und klaren Zielen.'),
          'detail' => $home_t('home.hero.slides.einzel.detail', '1-zu-1 Coaching: individuell, effizient und zielgerichtet – Technik, Torschuss, Athletik und persönliche Entwicklung.'),
          'img' => $theme_uri . '/assets/img/home/dfs-home-4.png',
          'book' => $book_urls['einzel'],
        ],
      ];

      $coaches = ks_get_coaches(48);
      $trainer_url = ks_get_trainer_url();
      ks_enqueue_team_assets();

      $first_slide = $slides[0];

      ob_start();
      ?>

<section id="home-hero" class="ks-home-hero ks-sec">
  <div class="container ks-home-hero__shell">
    <div class="ks-home-hero__intro">
      <div class="ks-kicker" data-i18n="home.hero.kicker">
        <?php echo esc_html($home_t('home.hero.kicker', 'Dortmund Football School')); ?>
      </div>

      <h1 class="ks-home-hero__brand-title" data-i18n="home.hero.title">
        <?php echo esc_html($home_t('home.hero.title', 'Build football skills. Strengthen children.')); ?>
      </h1>

      <p class="ks-home-hero__brand-lead" data-i18n="home.hero.lead">
        <?php echo esc_html($home_t('home.hero.lead', 'Modern training programs for camps, development training, kindergarten and individual training – with clear structure, joy of the game and personal growth.')); ?>
      </p>

      <div class="ks-home-hero__brand-actions">
        <a class="ks-btn ks-btn--dark" href="#wer-wir-sind" data-i18n="home.hero.primaryButton">
          <?php echo esc_html($home_t('home.hero.primaryButton', 'Learn more')); ?>
        </a>

        <a class="ks-btn" href="<?php echo esc_url($offers); ?>" data-i18n="home.hero.secondaryButton">
          <?php echo esc_html($home_t('home.hero.secondaryButton', 'Explore programs')); ?>
        </a>
      </div>
    </div>

    <div class="ks-home-hero__side">
      <nav class="ks-hero-tabs" aria-label="Program selection">
        <?php foreach ($slides as $i => $s): ?>
          <button
            type="button"
            class="ks-hero-tab<?php echo $i === 0 ? ' is-active' : ''; ?>"
            data-key="<?php echo esc_attr($s['key']); ?>"
            data-title="<?php echo esc_attr($s['title']); ?>"
            data-text="<?php echo esc_attr($s['detail']); ?>"
            data-link="<?php echo esc_url($s['book']); ?>"
            aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
          >
            <span class="ks-hero-tab__label"><?php echo esc_html($s['label']); ?></span>
          </button>
        <?php endforeach; ?>
      </nav>

      <div class="ks-home-hero__media">
        <div class="ks-home-hero__overlay">
          <div class="ks-kicker" data-i18n="home.hero.activeOffer">
            <?php echo esc_html($home_t('home.hero.activeOffer', 'Active offer')); ?>
          </div>

          <h2 class="ks-home-hero__course-title">
            <?php echo esc_html($first_slide['title']); ?>
          </h2>

          <p class="ks-home-hero__course-text">
            <?php echo esc_html($first_slide['detail']); ?>
          </p>

          <div class="ks-home-hero__course-actions">
            <a
              class="ks-btn ks-btn--dark ks-home-hero__course-link"
              href="<?php echo esc_url($first_slide['book']); ?>"
              data-i18n="home.hero.bookButton"
            >
              <?php echo esc_html($home_t('home.hero.bookButton', 'Book now')); ?>
            </a>
          </div>
        </div>

        <?php foreach ($slides as $i => $s): ?>
          <div
            class="ks-home-hero__media-item<?php echo $i === 0 ? ' is-active' : ''; ?>"
            data-key="<?php echo esc_attr($s['key']); ?>"
          >
            <img
              class="ks-home-hero__img"
              src="<?php echo esc_url($s['img']); ?>"
              alt="<?php echo esc_attr($s['title']); ?>"
            >
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section id="wer-wir-sind" class="ks-sec ks-py-48 ks-home-about">
  <div class="container ks-home-about__grid">
    <div class="ks-home-about__content">
      <div class="ks-kicker" data-i18n="home.aboutPreview.kicker">
        <?php echo esc_html($home_t('home.aboutPreview.kicker', 'Our Philosophy')); ?>
      </div>

      <h2 class="ks-dir__title ks-dir__title--split">
        <span class="ks-dir__title-line" data-i18n="home.aboutPreview.titleLine1">
          <?php echo esc_html($home_t('home.aboutPreview.titleLine1', 'Dortmund')); ?>
        </span>

        <span class="ks-dir__title-line" data-i18n="home.aboutPreview.titleLine2">
          <?php echo esc_html($home_t('home.aboutPreview.titleLine2', 'Football School')); ?>
        </span>
      </h2>

      <p class="ks-home-about__lead" data-i18n="home.aboutPreview.lead">
        <?php echo esc_html($home_t('home.aboutPreview.lead', 'We support children, young people, and clubs with a clear training approach that combines athletic development, enjoyment of the game, and a positive learning environment.')); ?>
      </p>

      <div class="ks-home-about__points">
        <div class="ks-home-about__point">
          <strong data-i18n="home.aboutPreview.point1Title">
            <?php echo esc_html($home_t('home.aboutPreview.point1Title', 'Structured')); ?>
          </strong>

          <span data-i18n="home.aboutPreview.point1Text">
            <?php echo esc_html($home_t('home.aboutPreview.point1Text', 'Clear content, modern training organization, and easy-to-understand processes.')); ?>
          </span>
        </div>

        <div class="ks-home-about__point">
          <strong data-i18n="home.aboutPreview.point2Title">
            <?php echo esc_html($home_t('home.aboutPreview.point2Title', 'Individual')); ?>
          </strong>

          <span data-i18n="home.aboutPreview.point2Text">
            <?php echo esc_html($home_t('home.aboutPreview.point2Text', 'Support tailored to age, level, and development.')); ?>
          </span>
        </div>
      </div>

      <div class="ks-home-about__actions">
        <a
          class="ks-btn"
          href="<?php echo esc_url($about_url); ?>"
          data-i18n="home.aboutPreview.button"
        >
          <?php echo esc_html($home_t('home.aboutPreview.button', 'Learn more')); ?>
        </a>
      </div>
    </div>

    <div class="ks-home-about__media">
      <div class="ks-home-about__badge" data-i18n="home.aboutPreview.mediaBadge">
        <?php echo esc_html($home_t('home.aboutPreview.mediaBadge', 'Insight into our work')); ?>
      </div>

      <div class="ks-vid ratio"><?php echo $video_embed; ?></div>

      <p class="ks-home-about__caption" data-i18n="home.aboutPreview.mediaCaption">
        <?php echo esc_html($home_t('home.aboutPreview.mediaCaption', 'Insights into our work with children, young people, and clubs.')); ?>
      </p>

      <div
        class="ks-home-about__chips"
        aria-label="<?php echo esc_attr($home_t('home.aboutPreview.chipsLabel', 'Focus areas')); ?>"
        data-i18n-attr="aria-label"
        data-i18n="home.aboutPreview.chipsLabel"
      >
        <span class="ks-home-about__chip" data-i18n="home.aboutPreview.chip1">
          <?php echo esc_html($home_t('home.aboutPreview.chip1', 'Children & Young People')); ?>
        </span>

        <span class="ks-home-about__chip" data-i18n="home.aboutPreview.chip2">
          <?php echo esc_html($home_t('home.aboutPreview.chip2', 'Individual Support')); ?>
        </span>

        <span class="ks-home-about__chip" data-i18n="home.aboutPreview.chip3">
          <?php echo esc_html($home_t('home.aboutPreview.chip3', 'Clubs & Teams')); ?>
        </span>
      </div>
    </div>
  </div>
</section>

<section id="werte" class="ks-sec ks-py-56 ks-home-values">
  <div class="container">
    <div class="ks-title-wrap ks-home-values__title-wrap ks-section-accent ks-section-accent--center">
      <div class="ks-kicker" data-i18n="home.values.kicker">
        <?php echo esc_html($home_t('home.values.kicker', 'Our mindset')); ?>
      </div>

      <h2 class="ks-dir__title" data-i18n="home.values.title">
        <?php echo esc_html($home_t('home.values.title', 'What defines us')); ?>
      </h2>
    </div>

    <div class="ks-values ks-home-values__grid">
      <a
        class="ks-value ks-home-values__card"
        href="<?php echo esc_url($werte_target); ?>"
      >
        <div class="icon-wrap ks-home-values__icon-wrap">
          <img src="<?php echo esc_url($icon1); ?>" alt="" loading="lazy">
        </div>

        <h3 data-i18n="home.values.card1Title">
          <?php echo esc_html($home_t('home.values.card1Title', 'Joy of the game')); ?>
        </h3>

        <p data-i18n="home.values.card1Text">
          <?php echo esc_html($home_t('home.values.card1Text', 'Football should inspire and motivate.')); ?>
        </p>
      </a>

      <a
        class="ks-value ks-home-values__card"
        href="<?php echo esc_url($werte_target); ?>"
      >
        <div class="icon-wrap ks-home-values__icon-wrap">
          <img src="<?php echo esc_url($icon2); ?>" alt="" loading="lazy">
        </div>

        <h3 data-i18n="home.values.card2Title">
          <?php echo esc_html($home_t('home.values.card2Title', 'Professional quality')); ?>
        </h3>

        <p data-i18n="home.values.card2Text">
          <?php echo esc_html($home_t('home.values.card2Text', 'We work with structure and quality.')); ?>
        </p>
      </a>

      <a
        class="ks-value ks-home-values__card"
        href="<?php echo esc_url($werte_target); ?>"
      >
        <div class="icon-wrap ks-home-values__icon-wrap">
          <img src="<?php echo esc_url($icon3); ?>" alt="" loading="lazy">
        </div>

        <h3 data-i18n="home.values.card3Title">
          <?php echo esc_html($home_t('home.values.card3Title', 'Strong values')); ?>
        </h3>

        <p data-i18n="home.values.card3Text">
          <?php echo esc_html($home_t('home.values.card3Text', 'We strengthen children in sport and life.')); ?>
        </p>
      </a>

      <a
        class="ks-value ks-home-values__card"
        href="<?php echo esc_url($werte_target); ?>"
      >
        <div class="icon-wrap ks-home-values__icon-wrap">
          <img src="<?php echo esc_url($icon4); ?>" alt="" loading="lazy">
        </div>

        <h3 data-i18n="home.values.card4Title">
          <?php echo esc_html($home_t('home.values.card4Title', 'Targeted support')); ?>
        </h3>

        <p data-i18n="home.values.card4Text">
          <?php echo esc_html($home_t('home.values.card4Text', 'Every child is supported appropriately.')); ?>
        </p>
      </a>
    </div>
  </div>
</section>

<?php
$faq_items = ks_get_faq_items('home');

if (!empty($faq_items)) {
  $faq_video_embed = wp_oembed_get('https://www.youtube.com/watch?v=KEWP2dELhrY');

  if (!$faq_video_embed) {
    $faq_video_embed = '<div class="ks-vid-ph" aria-hidden="true"></div>';
  }

  echo ks_render_faq_section($faq_items, [
    'section_id' => 'faq',
    'wrapper_class' => 'container ks-home-faq',
    'title' => $home_t('home.faq.title', 'Häufige Fragen zur Fußballschule'),
    'kicker' => $home_t('home.faq.kicker', 'Gut zu wissen'),
    'title_i18n' => 'home.faq.title',
    'kicker_i18n' => 'home.faq.kicker',
    'items_i18n_prefix' => 'home.faq',
    'side_card_enabled' => true,
    'side_card_kicker' => $home_t('home.faq.sideCard.kicker', 'Fragen offen?'),
    'side_card_title' => $home_t('home.faq.sideCard.title', 'Wir helfen dir gerne weiter'),
    'side_card_text' => $home_t('home.faq.sideCard.text', 'Wenn du Fragen zu Trainingsformaten, Anmeldung oder Abläufen hast, kannst du dich jederzeit direkt bei uns melden.'),
    'side_card_button' => $home_t('home.faq.sideCard.button', 'Kontakt aufnehmen'),
    'side_card_href' => esc_url(home_url('/#whatsapp-locations')),
    'side_card_kicker_i18n' => 'home.faq.sideCard.kicker',
    'side_card_title_i18n' => 'home.faq.sideCard.title',
    'side_card_text_i18n' => 'home.faq.sideCard.text',
    'side_card_button_i18n' => 'home.faq.sideCard.button',
  ]);
}
?>

<section
  id="coach-portal"
  class="ks-sec ks-home-portal"
  aria-label="<?php echo esc_attr($home_t('home.portal.ariaLabel', 'DFS Trainingsbibliothek')); ?>"
  data-i18n-attr="aria-label"
  data-i18n="home.portal.ariaLabel"
>
  <div class="ks-home-portal__bg" aria-hidden="true">
    <img
      class="ks-home-portal__bg-image"
      src="<?php echo esc_url($portal_bg); ?>"
      alt=""
    >
  </div>

  <div class="container ks-home-portal__grid">
    <div class="ks-home-portal__text">
      <div class="ks-eyebrow" data-i18n="home.portal.eyebrow">
        <?php echo esc_html($home_t('home.portal.eyebrow', 'Digitale Inhalte für Trainer, Teams und ambitionierte Spieler')); ?>
      </div>

      <h2 class="ks-home-portal__title">
        <span data-i18n="home.portal.titlePrefix">
          <?php echo esc_html($home_t('home.portal.titlePrefix', 'DFSCOACH')); ?>
        </span>

        <span data-i18n="home.portal.titleSuffix">
          <?php echo esc_html($home_t('home.portal.titleSuffix', 'Trainingsbibliothek')); ?>
        </span>
      </h2>

      <p data-i18n="home.portal.text">
        <?php echo esc_html($home_t('home.portal.text', 'Greife auf Übungen, Trainingsideen und strukturierte Inhalte zu. Nutze die Bibliothek zur Vorbereitung deiner Einheiten und entwickle deine Trainingsarbeit gezielt weiter.')); ?>
      </p>

      <p class="ks-home-portal__actions">
        <a class="ks-btn ks-btn--light" href="<?php echo esc_url($portal_url); ?>" data-i18n="home.portal.button">
          <?php echo esc_html($home_t('home.portal.button', 'Mehr erfahren')); ?>
        </a>
      </p>
    </div>

    <div class="ks-home-portal__media" role="presentation" aria-hidden="true"></div>
  </div>
</section>

<?php include $theme_dir . '/inc/partials/shared/team-section.php'; ?>

<?php
if (function_exists('ks_render_feedback_section')) {
  echo ks_render_feedback_section();
}
?>

<?php echo do_shortcode('[ks_partner_network]'); ?>

<?php if ($show_news): ?>
  <?php
    $news_archive_url = function_exists('ks_news_archive_url')
      ? ks_news_archive_url()
      : home_url('/new/');
  ?>

  <section id="news" class="ks-sec ks-home-news">
    <div class="container ks-home-news__shell">
      <div class="ks-home-news__intro">
        <div class="ks-kicker" data-i18n="news.kicker">
          <?php echo esc_html($news_t('news.kicker', 'Aktuelles')); ?>
        </div>

        <h2
          class="ks-dir__title ks-home-news__title"
          data-i18n="news.title"
        >
          <?php echo esc_html($news_t('news.title', 'Neues aus der Fussballschule')); ?>
        </h2>

        <p class="ks-home-news__lead" data-i18n="news.lead">
          <?php echo esc_html($news_t('news.lead', 'Spannende Einblicke, aktuelle Entwicklungen und Geschichten aus unserem Alltag auf und neben dem Platz.')); ?>
        </p>

        <a
          class="ks-btn ks-home-news__archive-link"
          href="<?php echo esc_url($news_archive_url); ?>"
        >
          <span data-i18n="news.archiveButton">
            <?php echo esc_html($news_t('news.archiveButton', 'Alle News ansehen')); ?>
          </span>

          <img
            class="ks-home-news__archive-icon"
            src="<?php echo esc_url($theme_uri . '/assets/img/team/arrow_right_alt.svg'); ?>"
            alt=""
            loading="lazy"
          >
        </a>
      </div>

      <div class="ks-home-news__list">
        <?php echo do_shortcode('[ks_news_latest limit="3" thumbs="1"]'); ?>
      </div>
    </div>
  </section>
<?php endif; ?>

<?php
$program_cta_partial = $theme_dir . '/inc/partials/home/program-cta.php';

if (file_exists($program_cta_partial)) {
  include $program_cta_partial;
}
?>

<?php echo do_shortcode('[ks_whatsapp_locations]'); ?>

<?php
      return ob_get_clean();
    });
  }

  add_action('init', 'ks_register_home_shortcode');
}

















