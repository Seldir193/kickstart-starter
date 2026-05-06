<?php

if (!function_exists('ks_enqueue_feedback_assets')) {
  function ks_enqueue_feedback_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();

    ks_enqueue_feedback_css($theme_dir, $theme_uri);
    ks_enqueue_feedback_js($theme_dir, $theme_uri);
  }
}

if (!function_exists('ks_enqueue_feedback_css')) {
  function ks_enqueue_feedback_css(string $theme_dir, string $theme_uri): void {
    $fb_css = $theme_dir . '/assets/css/ks-feedback.css';

    if (!file_exists($fb_css)) {
      return;
    }

    wp_enqueue_style(
      'ks-feedback',
      $theme_uri . '/assets/css/ks-feedback.css',
      ['kickstart-style', 'ks-utils', 'ks-base', 'ks-layout', 'ks-components'],
      filemtime($fb_css)
    );
  }
}

if (!function_exists('ks_enqueue_feedback_js')) {
  function ks_enqueue_feedback_js(string $theme_dir, string $theme_uri): void {
    $fb_js = $theme_dir . '/assets/js/ks-feedback.js';

    if (!file_exists($fb_js)) {
      return;
    }

    wp_enqueue_script(
      'ks-feedback',
      $theme_uri . '/assets/js/ks-feedback.js',
      [],
      filemtime($fb_js),
      true
    );
  }
}

if (!function_exists('ks_feedback_text')) {
  function ks_feedback_text(string $key, string $fallback): string {
    return function_exists('ks_t') ? ks_t($key, $fallback, 'feedback') : $fallback;
  }
}

if (!function_exists('ks_feedback_api_lang')) {
  function ks_feedback_api_lang(): string {
    if (function_exists('ks_i18n_get_current_language')) {
      return ks_i18n_get_current_language();
    }

    $locale = function_exists('determine_locale') ? determine_locale() : get_locale();

    if (str_starts_with($locale, 'tr')) {
      return 'tr';
    }

    if (str_starts_with($locale, 'en')) {
      return 'en';
    }

    return 'de';
  }
}

if (!function_exists('ks_feedback_api_url')) {
  function ks_feedback_api_url(): string {
    $base_url = 'http://localhost:5000/api/feedbacks';
    $url = add_query_arg('lang', ks_feedback_api_lang(), $base_url);

    return apply_filters('ks_feedback_api_url', $url);
  }
}

if (!function_exists('ks_fetch_feedbacks_from_api')) {
  function ks_fetch_feedbacks_from_api(): array {
    $response = wp_remote_get(ks_feedback_api_url(), ['timeout' => 8]);

    if (is_wp_error($response)) {
      return [];
    }

    return ks_parse_feedback_response($response);
  }
}

if (!function_exists('ks_parse_feedback_response')) {
  function ks_parse_feedback_response(array $response): array {
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data) || empty($data['ok'])) {
      return [];
    }

    return ks_normalize_feedbacks($data['items'] ?? []);
  }
}

if (!function_exists('ks_normalize_feedbacks')) {
  function ks_normalize_feedbacks($items): array {
    if (!is_array($items)) {
      return [];
    }

    $feedbacks = array_map('ks_normalize_feedback_item', $items);

    return array_values(array_filter($feedbacks));
  }
}

if (!function_exists('ks_normalize_feedback_item')) {
  function ks_normalize_feedback_item($item): array {
    if (!is_array($item)) {
      return [];
    }

    return [
      'id' => sanitize_text_field((string) ($item['id'] ?? $item['_id'] ?? '')),
      'category' => sanitize_text_field((string) ($item['category'] ?? 'parents')),
      'label' => ks_feedback_label((string) ($item['label'] ?? $item['category'] ?? 'parents')),
      'img' => esc_url_raw((string) ($item['img'] ?? $item['imageUrl'] ?? '')),
      'quote' => sanitize_textarea_field((string) ($item['quote'] ?? '')),
      'author' => sanitize_text_field((string) ($item['author'] ?? '')),
      'meta' => sanitize_text_field((string) ($item['meta'] ?? '')),
      'sortOrder' => (int) ($item['sortOrder'] ?? 100),
    ];
  }
}

if (!function_exists('ks_feedback_label')) {
  function ks_feedback_label(string $value): string {
    $labels = [
      'parents' => 'Eltern',
      'players' => 'Spieler',
      'coaches' => 'Trainer',
      'partners' => 'Partner',
      'Eltern' => 'Eltern',
      'Spieler' => 'Spieler',
      'Trainer' => 'Trainer',
      'Partner' => 'Partner',
    ];

    return $labels[$value] ?? 'Eltern';
  }
}

if (!function_exists('ks_feedback_icon_src')) {
  function ks_feedback_icon_src(string $type): string {
    $icons = [
      'Eltern' => 'parents.svg',
      'Spieler' => 'players.svg',
      'Trainer' => 'coaches.svg',
      'Partner' => 'partners.svg',
    ];

    $file = $icons[$type] ?? $icons['Eltern'];

    return get_stylesheet_directory_uri() . '/assets/img/feedback/' . $file;
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

if (!function_exists('ks_feedback_category_text')) {
  function ks_feedback_category_text(string $label): string {
    return ks_feedback_text(ks_feedback_category_key($label), $label);
  }
}



if (!function_exists('ks_feedback_categories')) {
  function ks_feedback_categories(array $feedbacks): array {
    $ordered = ['Eltern', 'Spieler', 'Trainer', 'Partner'];
    $labels = array_unique(array_filter(array_column($feedbacks, 'label')));

    return ks_append_custom_feedback_categories($ordered, $labels);
  }
}

if (!function_exists('ks_sort_feedback_categories')) {
  function ks_sort_feedback_categories(array $ordered, array $labels): array {
    $categories = [];

    foreach ($ordered as $label) {
      if (in_array($label, $labels, true)) {
        $categories[] = $label;
      }
    }

    return ks_append_custom_feedback_categories($categories, $labels);
  }
}

if (!function_exists('ks_append_custom_feedback_categories')) {
  function ks_append_custom_feedback_categories(array $categories, array $labels): array {
    foreach ($labels as $label) {
      if (!in_array($label, $categories, true)) {
        $categories[] = $label;
      }
    }

    return $categories;
  }
}

if (!function_exists('ks_feedback_image_src')) {
  function ks_feedback_image_src(array $item): string {
    if (!empty($item['img'])) {
      return $item['img'];
    }

    return get_stylesheet_directory_uri() . '/assets/img/home/mfs.png';
  }
}

if (!function_exists('ks_feedback_initial_active_index')) {
  function ks_feedback_initial_active_index(array $feedbacks, string $category): int {
    foreach ($feedbacks as $index => $item) {
      if (($item['label'] ?? '') === $category) {
        return (int) $index;
      }
    }

    return 0;
  }
}

if (!function_exists('ks_render_feedback_section')) {
  function ks_render_feedback_section() {
    ks_enqueue_feedback_assets();

    $feedbacks = ks_fetch_feedbacks_from_api();

    if (empty($feedbacks)) {
      return '';
    }

    $categories = ks_feedback_categories($feedbacks);
    $feedback_arrow_icon = get_stylesheet_directory_uri() . '/assets/img/team/arrow_right_alt.svg';
    $initial_category = $categories[0] ?? '';
    $initial_active_index = ks_feedback_initial_active_index($feedbacks, $initial_category);

    ob_start();
    ?>

    <section
      id="feedback"
      class="ks-sec ks-feedback"
      data-feedback-root
      aria-label="<?php echo esc_attr(ks_feedback_text('feedback.aria.section', 'Feedbacks')); ?>"
      data-i18n="feedback.aria.section"
      data-i18n-attr="aria-label"
    >
      <div class="container container--1400">
        <div
          class="ks-title-wrap ks-feedback__head"
          data-bgword="<?php echo esc_attr(ks_feedback_text('feedback.watermark', 'STIMMEN')); ?>"
          data-i18n="feedback.watermark"
          data-i18n-attr="data-bgword"
        >
          <div class="ks-kicker" data-i18n="feedback.kicker">
            <?php echo esc_html(ks_feedback_text('feedback.kicker', 'Feedback')); ?>
          </div>

          <h2 class="ks-dir__title ks-feedback__title" data-i18n="feedback.title">
            <?php echo esc_html(ks_feedback_text('feedback.title', 'Echte Stimmen aus der DFS')); ?>
          </h2>

          <p class="ks-feedback__lead" data-i18n="feedback.lead">
            <?php echo esc_html(ks_feedback_text('feedback.lead', 'Erfahrungen, die uns antreiben. Entwicklung, die bleibt.')); ?>
          </p>
        </div>

        <div
          class="ks-feedback__tabs"
          role="tablist"
          aria-label="<?php echo esc_attr(ks_feedback_text('feedback.aria.tabs', 'Feedback Kategorien')); ?>"
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
                <?php echo esc_html(ks_feedback_category_text($category)); ?>
              </span>
            </button>
          <?php endforeach; ?>
        </div>

        <div class="ks-feedback__frame">
          <?php foreach ($feedbacks as $index => $item): ?>
            <?php $is_active_slide = $index === $initial_active_index; ?>

            <article
              class="ks-feedback__slide<?php echo $is_active_slide ? ' is-active' : ''; ?>"
              data-feedback-slide
              data-feedback-label="<?php echo esc_attr($item['label']); ?>"
              data-feedback-index="<?php echo esc_attr((string) $index); ?>"
              aria-hidden="<?php echo $is_active_slide ? 'false' : 'true'; ?>"
            >
              <div class="ks-feedback__media">
                <span class="ks-feedback__side-label" data-i18n="feedback.sideLabel">
                  <?php echo esc_html(ks_feedback_text('feedback.sideLabel', 'Dortmunder Fussball Schule')); ?>
                </span>

                <img
                  class="ks-feedback__image"
                  src="<?php echo esc_url(ks_feedback_image_src($item)); ?>"
                  alt=""
                  loading="lazy"
                  decoding="async"
                />
              </div>

              <div class="ks-feedback__content">
                <div class="ks-feedback__quote-mark" aria-hidden="true">“</div>

                <blockquote class="ks-feedback__quote">
                  <?php echo esc_html($item['quote']); ?>
                </blockquote>

                <div class="ks-feedback__author">
                  <span class="ks-feedback__line" aria-hidden="true"></span>

                  <strong>
                    <?php echo esc_html(mb_strtoupper($item['author'])); ?>
                  </strong>

                  <span>
                    <?php echo esc_html($item['meta']); ?>
                  </span>
                </div>

                <div
                  class="ks-feedback__controls"
                  aria-label="<?php echo esc_attr(ks_feedback_text('feedback.aria.controls', 'Feedback Navigation')); ?>"
                  data-i18n="feedback.aria.controls"
                  data-i18n-attr="aria-label"
                >
                  <button
                    type="button"
                    class="ks-feedback__nav ks-feedback__nav--prev"
                    data-feedback-prev
                    aria-label="<?php echo esc_attr(ks_feedback_text('feedback.aria.prev', 'Vorheriges Feedback')); ?>"
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
                    aria-label="<?php echo esc_attr(ks_feedback_text('feedback.aria.next', 'Nächstes Feedback')); ?>"
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

          <p data-i18n="feedback.footer.left">
            <?php echo esc_html(ks_feedback_text('feedback.footer.left', 'Fussball ist Entwicklung.')); ?>
          </p>

          <i aria-hidden="true"></i>

          <p data-i18n="feedback.footer.right">
            <?php echo esc_html(ks_feedback_text('feedback.footer.right', 'Wir geben den Rahmen.')); ?>
          </p>

          <span></span>
        </div>
      </div>
    </section>

    <?php
    return ob_get_clean();
  }
}




























