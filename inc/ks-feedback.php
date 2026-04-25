<?php
// inc/ks-feedback.php

if (!function_exists('ks_enqueue_feedback_assets')) {
  function ks_enqueue_feedback_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    $fb_css = $theme_dir . '/assets/css/ks-feedback.css';

    if (file_exists($fb_css)) {
      wp_enqueue_style(
        'ks-feedback',
        $theme_uri . '/assets/css/ks-feedback.css',
        ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
        filemtime($fb_css)
      );
    }

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

// if (!function_exists('ks_feedback_icon')) {
//   function ks_feedback_icon(string $type): string {
//     $icons = [
//       'Eltern' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-8 0v2"></path><circle cx="12" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>',
//       'Spieler' => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="7" r="4"></circle><path d="M5.5 21a6.5 6.5 0 0 1 13 0"></path></svg>',
//       'Trainer' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 10h1"></path><path d="M14 10h1"></path><path d="M8 14h8"></path><path d="M7 4h10l2 5v9a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V9l2-5Z"></path></svg>',
//       'Partner' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m7 11 2-2a3 3 0 0 1 4 0l4 4"></path><path d="m14 14 1 1a3 3 0 0 0 4 0l2-2"></path><path d="m3 13 2 2a3 3 0 0 0 4 0l1-1"></path><path d="M2 7h5l3 3"></path><path d="M22 7h-5l-3 3"></path></svg>',
//     ];

//     return $icons[$type] ?? $icons['Eltern'];
//   }
// }

if (!function_exists('ks_feedback_icon_src')) {
  function ks_feedback_icon_src(string $type): string {
    $theme_uri = get_stylesheet_directory_uri();

    $icons = [
      'Eltern' => 'parents.svg',
      'Spieler' => 'players.svg',
      'Trainer' => 'coaches.svg',
      'Partner' => 'partners.svg',
    ];

    $file = $icons[$type] ?? $icons['Eltern'];

    return $theme_uri . '/assets/img/feedback/' . $file;
  }
}

if (!function_exists('ks_feedback_category_key')) {
  function ks_feedback_category_key(string $label): string {
    $keys = [
      'Eltern' => 'feedback.tabs.parents',
      'Spieler' => 'feedback.tabs.players',
      'Trainer' => 'feedback.tabs.coaches',
      'Partner' => 'feedback.tabs.partners',
    ];

    return $keys[$label] ?? 'feedback.tabs.parents';
  }
}

if (!function_exists('ks_feedback_categories')) {
  function ks_feedback_categories(array $feedbacks): array {
    $ordered = ['Eltern', 'Spieler', 'Trainer', 'Partner'];
    $labels = array_unique(array_filter(array_column($feedbacks, 'label')));
    $categories = [];

    foreach ($ordered as $label) {
      if (in_array($label, $labels, true)) {
        $categories[] = $label;
      }
    }

    foreach ($labels as $label) {
      if (!in_array($label, $categories, true)) {
        $categories[] = $label;
      }
    }

    return $categories;
  }
}

if (!function_exists('ks_render_feedback_section')) {
  function ks_render_feedback_section() {
    ks_enqueue_feedback_assets();

    $theme_uri = get_stylesheet_directory_uri();
    $fb_img = $theme_uri . '/assets/img/home/mfs.png';
    $feedback_arrow_icon = $theme_uri . '/assets/img/team/arrow_right_alt.svg';

    $feedbacks = [
      [
        'img' => $fb_img,
        'quote' => 'Ihr Einsatz, Ihre Energie, verbunden mit zielorientiertem Coaching ist fast unbezahlbar für die Jugendlichen.',
        'author' => 'Christian Gross',
        'meta' => 'Schweizer Profitrainer (Tottenham Hotspur, VfB Stuttgart, Young Boys Bern)',
        'label' => 'Trainer',
        'quote_key' => 'feedback.items.1.quote',
        'author_key' => 'feedback.items.1.author',
        'meta_key' => 'feedback.items.1.meta',
      ],
      [
        'img' => $fb_img,
        'quote' => 'Die Trainingsqualität und die Organisation sind außergewöhnlich, jedes Detail stimmt.',
        'author' => 'Muster Coach',
        'meta' => 'U17-Trainer, NRW',
        'label' => 'Trainer',
        'quote_key' => 'feedback.items.2.quote',
        'author_key' => 'feedback.items.2.author',
        'meta_key' => 'feedback.items.2.meta',
      ],
      [
        'img' => $fb_img,
        'quote' => 'Fachlich top und menschlich nah – so macht Förderung Sinn.',
        'author' => 'Elternstimme',
        'meta' => 'Dortmund',
        'label' => 'Eltern',
        'quote_key' => 'feedback.items.3.quote',
        'author_key' => 'feedback.items.3.author',
        'meta_key' => 'feedback.items.3.meta',
      ],
      [
        'img' => $fb_img,
        'quote' => 'Kindgerecht, motivierend, professionell – klare Empfehlung.',
        'author' => 'Vereinsvorstand',
        'meta' => 'Partnerverein',
        'label' => 'Partner',
        'quote_key' => 'feedback.items.4.quote',
        'author_key' => 'feedback.items.4.author',
        'meta_key' => 'feedback.items.4.meta',
      ],
    ];

    $categories = ks_feedback_categories($feedbacks);

    ob_start();
    ?>

    <section
      id="feedback"
      class="ks-sec ks-feedback"
      data-feedback-root
      aria-label="Feedbacks"
      data-i18n="feedback.aria.section"
      data-i18n-attr="aria-label"
    >
      <div class="container container--1400">
        

        <div
  class="ks-title-wrap ks-feedback__head"
  data-bgword="STIMMEN"
  data-i18n="feedback.watermark"
  data-i18n-attr="data-bgword"
>
  <div class="ks-kicker" data-i18n="feedback.kicker">Feedback</div>
 

  <h2 class="ks-dir__title ks-feedback__title" data-i18n="feedback.title">Echte Stimmen aus der DFS</h2>
  <p class="ks-feedback__lead" data-i18n="feedback.lead">
    Erfahrungen, die uns antreiben. Entwicklung, die bleibt.
  </p>
</div>

        <div
          class="ks-feedback__tabs"
          role="tablist"
          aria-label="Feedback Kategorien"
          data-i18n="feedback.aria.tabs"
          data-i18n-attr="aria-label"
        >
          <?php foreach ($categories as $index => $category): ?>
            <button
              type="button"
              class="ks-feedback__tab<?php echo $index === 0 ? ' is-active' : ''; ?>"
              data-feedback-filter="<?php echo esc_attr($category); ?>"
              aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
              role="tab"
            >
             

              <span class="ks-feedback__tab-icon">
  <img
    src="<?php echo esc_url(ks_feedback_icon_src($category)); ?>"
    alt=""
    aria-hidden="true"
    loading="lazy"
    decoding="async"
  />
</span>
              <span data-i18n="<?php echo esc_attr(ks_feedback_category_key($category)); ?>">
                <?php echo esc_html($category); ?>
              </span>
            </button>
          <?php endforeach; ?>
        </div>

        <div class="ks-feedback__frame">
          <?php foreach ($feedbacks as $index => $item): ?>
            <article
              class="ks-feedback__slide<?php echo $index === 0 ? ' is-active' : ''; ?>"
              data-feedback-slide
              data-feedback-label="<?php echo esc_attr($item['label']); ?>"
              data-feedback-index="<?php echo esc_attr((string) $index); ?>"
              aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>"
            >
              <div class="ks-feedback__media">
                <span class="ks-feedback__side-label" data-i18n="feedback.sideLabel">
                  Dortmunder Fussball Schule
                </span>
                <img
                  class="ks-feedback__image"
                  src="<?php echo esc_url($item['img']); ?>"
                  alt=""
                  loading="lazy"
                  decoding="async"
                />
              </div>

              <div class="ks-feedback__content">
                <div class="ks-feedback__quote-mark" aria-hidden="true">“</div>

                <blockquote class="ks-feedback__quote" data-i18n="<?php echo esc_attr($item['quote_key']); ?>">
                  <?php echo esc_html($item['quote']); ?>
                </blockquote>

                <div class="ks-feedback__author">
                  <span class="ks-feedback__line" aria-hidden="true"></span>
                  <strong data-i18n="<?php echo esc_attr($item['author_key']); ?>">
                    <?php echo esc_html(mb_strtoupper($item['author'])); ?>
                  </strong>
                  <span data-i18n="<?php echo esc_attr($item['meta_key']); ?>">
                    <?php echo esc_html($item['meta']); ?>
                  </span>
                </div>

                <div
                  class="ks-feedback__controls"
                  aria-label="Feedback Navigation"
                  data-i18n="feedback.aria.controls"
                  data-i18n-attr="aria-label"
                >
               

                  <button
  type="button"
  class="ks-feedback__nav ks-feedback__nav--prev"
  data-feedback-prev
  aria-label="Vorheriges Feedback"
  data-i18n="feedback.aria.prev"
  data-i18n-attr="aria-label"
>
  <img
    class="ks-feedback__nav-icon"
    src="<?php echo esc_url($feedback_arrow_icon); ?>"
    alt=""
    aria-hidden="true"
  />
</button>

                  <div class="ks-feedback__count" aria-live="polite">
                    <span data-feedback-current>01</span>
                    <span>/</span>
                    <span data-feedback-total><?php echo esc_html(sprintf('%02d', count($feedbacks))); ?></span>
                  </div>

                  <div class="ks-feedback__progress" aria-hidden="true">
                    <?php foreach ($feedbacks as $bar_index => $_): ?>
                      <span class="<?php echo $bar_index === 0 ? 'is-active' : ''; ?>" data-feedback-progress></span>
                    <?php endforeach; ?>
                  </div>

                  <button
                    type="button"
                    class="ks-feedback__nav ks-feedback__nav--next"
                    data-feedback-next
                    aria-label="Nächstes Feedback"
                    data-i18n="feedback.aria.next"
                    data-i18n-attr="aria-label"
                  >
                   

                                    <img
  class="ks-feedback__nav-icon"
  src="<?php echo esc_url($feedback_arrow_icon); ?>"
  alt=""
  aria-hidden="true"
/>
                  </button>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="ks-feedback__foot">
          <span></span>
          <p data-i18n="feedback.footer.left">Fussball ist Entwicklung.</p>
          <i aria-hidden="true"></i>
          <p data-i18n="feedback.footer.right">Wir geben den Rahmen.</p>
          <span></span>
        </div>
      </div>
    </section>

    <?php
    return ob_get_clean();
  }
}











