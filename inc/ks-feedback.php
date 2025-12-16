<?php
// inc/ks-feedback.php

if (!function_exists('ks_enqueue_feedback_assets')) {
  function ks_enqueue_feedback_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    // CSS
    $fb_css = $theme_dir . '/assets/css/ks-feedback.css';
    if (file_exists($fb_css)) {
      wp_enqueue_style(
        'ks-feedback',
        $theme_uri . '/assets/css/ks-feedback.css',
        ['ks-utils'],
        filemtime($fb_css)
      );
    }

    // JS
    $fb_js = $theme_dir . '/assets/js/ks-feedback.js';
    if (file_exists($fb_js)) {
      wp_enqueue_script(
        'ks-feedback',
        $theme_uri . '/assets/js/ks-feedback.js',
        [],
        filemtime($fb_js),
        true
      );
    }
  }
}

if (!function_exists('ks_render_feedback_section')) {
  function ks_render_feedback_section() {
    $theme_uri = get_stylesheet_directory_uri();
    $fb_img    = $theme_uri . '/assets/img/home/mfs.png'; // wie in home.php

    $feedbacks = [
      [
        'img'    => $fb_img,
        'quote'  => 'Ihr Einsatz, Ihre Energie, verbunden mit zielorientiertem Coaching ist fast unbezahlbar für die Jugendlichen.',
        'author' => 'Christian Gross',
        'meta'   => 'Schweizer Profitrainer (Tottenham Hotspur, VfB Stuttgart, Young Boys Bern)',
        'label'  => 'Trainer',
      ],
      [
        'img'    => $fb_img,
        'quote'  => 'Die Trainingsqualität und die Organisation sind außergewöhnlich, jedes Detail stimmt.',
        'author' => 'Muster Coach',
        'meta'   => 'U17-Trainer, NRW',
        'label'  => 'Trainer',
      ],
      [
        'img'    => $fb_img,
        'quote'  => 'Fachlich top und menschlich nah – so macht Förderung Sinn.',
        'author' => 'Elternstimme',
        'meta'   => 'Dortmund',
        'label'  => 'Eltern',
      ],
      [
        'img'    => $fb_img,
        'quote'  => 'Kindgerecht, motivierend, professionell – klare Empfehlung.',
        'author' => 'Vereinsvorstand',
        'meta'   => 'Partnerverein',
        'label'  => 'Partner',
      ],
    ];

    ob_start();
    ?>
    <section id="feedback" class="ks-sec ks-py-56 ks-fb" data-watermark="FEEDBACK" aria-label="Feedbacks">
      <nav class="ks-fb-tabs" aria-label="Feedback Auswahl">
        <?php foreach ($feedbacks as $i => $f): ?>
          <button type="button"
                  class="ks-fb-tab<?php echo $i===0 ? ' is-active' : ''; ?>"
                  data-key="fb-<?php echo $i; ?>">
            <span class="ks-fb-tab__label"><?php echo esc_html($f['label'] ?? 'Feedback'); ?></span>
            <span class="ks-fb-tab__num"><?php echo sprintf('%02d', $i+1); ?></span>
          </button>
        <?php endforeach; ?>
      </nav>

      <div class="container ks-fb-grid">
        <?php foreach ($feedbacks as $i => $f): ?>
          <article class="ks-fb-slide <?php echo $i===0 ? ' is-active' : ''; ?>"
                   data-key="fb-<?php echo $i; ?>">

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
    <?php
    return ob_get_clean();
  }
}
