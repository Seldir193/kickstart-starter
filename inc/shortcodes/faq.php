<?php

if (!function_exists('ks_get_current_i18n_language')) {
  function ks_get_current_i18n_language(): string {
    $allowed = ['de', 'en', 'tr'];

    if (!empty($_COOKIE['wpFrontendLng'])) {
      $cookie_lang = strtolower((string) $_COOKIE['wpFrontendLng']);
      if (in_array($cookie_lang, $allowed, true)) {
        return $cookie_lang;
      }
    }

    $header = strtolower((string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? ''));
    foreach ($allowed as $lang) {
      if (strpos($header, $lang) === 0 || strpos($header, $lang . '-') !== false) {
        return $lang;
      }
    }

    return 'de';
  }
}

if (!function_exists('ks_get_i18n_scope_data')) {
  function ks_get_i18n_scope_data(string $scope, ?string $lang = null): array {
    static $cache = [];

    $lang = $lang ?: ks_get_current_i18n_language();
    $cache_key = $scope . '|' . $lang;

    if (isset($cache[$cache_key])) {
      return $cache[$cache_key];
    }

    $theme_dir = get_stylesheet_directory();
    $file = $theme_dir . '/assets/i18n/' . $lang . '/' . $scope . '.' . $lang . '.json';

    if (!file_exists($file)) {
      $cache[$cache_key] = [];
      return [];
    }

    $json = file_get_contents($file);
    $data = json_decode((string) $json, true);

    if (!is_array($data)) {
      $cache[$cache_key] = [];
      return [];
    }

    $cache[$cache_key] = $data;
    return $data;
  }
}

if (!function_exists('ks_get_i18n_value')) {
  function ks_get_i18n_value(array $data, array $path) {
    $value = $data;

    foreach ($path as $segment) {
      if (!is_array($value) || !array_key_exists($segment, $value)) {
        return null;
      }
      $value = $value[$segment];
    }

    return $value;
  }
}

if (!function_exists('ks_get_faq_slug')) {
  function ks_get_faq_slug(?string $key): string {
    $slug = sanitize_title((string) $key);
    return str_replace('-', '_', $slug);
  }
}

if (!function_exists('ks_normalize_faq_items')) {
  function ks_normalize_faq_items($items): array {
    if (!is_array($items)) {
      return [];
    }

    $normalized = [];

    foreach ($items as $item) {
      if (!is_array($item)) {
        continue;
      }

      $question = (string) ($item['question'] ?? '');
      $answer = (string) ($item['answer'] ?? '');

      if ($question === '' || $answer === '') {
        continue;
      }

      $normalized[] = [$question, $answer];
    }

    return $normalized;
  }
}

if (!function_exists('ks_get_faq_items')) {
  function ks_get_faq_items(string $context, ?string $key = null): array {
    $lang = ks_get_current_i18n_language();

    if ($context === 'home') {
      $path = ['home', 'faq', 'items'];
      $items = ks_get_i18n_value(ks_get_i18n_scope_data('home', $lang), $path);

      if (!is_array($items) && $lang !== 'de') {
        $items = ks_get_i18n_value(ks_get_i18n_scope_data('home', 'de'), $path);
      }

      return ks_normalize_faq_items($items);
    }

    if ($context === 'franchise') {
      $path = ['franchise', 'faq', 'items'];
      $items = ks_get_i18n_value(ks_get_i18n_scope_data('franchise', $lang), $path);

      if (!is_array($items) && $lang !== 'de') {
        $items = ks_get_i18n_value(ks_get_i18n_scope_data('franchise', 'de'), $path);
      }

      return ks_normalize_faq_items($items);
    }

    if ($context === 'offers') {
      $slug = ks_get_faq_slug($key);
      $path = ['offers', 'faq', $slug, 'items'];
      $items = ks_get_i18n_value(ks_get_i18n_scope_data('offers', $lang), $path);

      if (!is_array($items) && $lang !== 'de') {
        $items = ks_get_i18n_value(ks_get_i18n_scope_data('offers', 'de'), $path);
      }

      return ks_normalize_faq_items($items);
    }

    return [];
  }
}

if (!function_exists('ks_render_faq_section')) {
  function ks_render_faq_section(array $items, array $args = []): string {
    if (empty($items)) {
      return '';
    }

    $defaults = [
      'section_id'             => 'faq',
      'wrapper_class'          => 'container ks-home-faq',
      'title'                  => 'Häufige Fragen',
      'kicker'                 => 'Gut zu wissen',
      'watermark'              => 'FAQ',
      'title_i18n'             => '',
      'kicker_i18n'            => '',
      'items_i18n_prefix'      => '',
      'use_video'              => false,
      'video_embed'            => '',
      'image_src'              => '',
      'image_class'            => 'fr-faq__image',
      'side_card_enabled'      => true,
      'side_card_kicker'       => 'Fragen offen?',
      'side_card_title'        => 'Wir helfen dir gerne weiter',
      'side_card_text'         => 'Wenn noch etwas unklar ist, melde dich direkt bei uns.',
      'side_card_button'       => 'Kontakt aufnehmen',
      'side_card_href'         => '#kontakt',
      'side_card_kicker_i18n'  => '',
      'side_card_title_i18n'   => '',
      'side_card_text_i18n'    => '',
      'side_card_button_i18n'  => '',
    ];

    $args = array_merge($defaults, $args);

    ob_start();
    ?>
    <section id="<?php echo esc_attr($args['section_id']); ?>" class="ks-sec ks-py-56">
      <div class="<?php echo esc_attr($args['wrapper_class']); ?>">
       


<div
  class="ks-title-wrap"
  data-bgword="FAQ"
  data-i18n="home.faq.watermark"
  data-i18n-attr="data-bgword"
>
          <div
            class="ks-kicker"
            <?php if ($args['kicker_i18n'] !== ''): ?>
              data-i18n="<?php echo esc_attr($args['kicker_i18n']); ?>"
            <?php endif; ?>
          >
            <?php echo esc_html($args['kicker']); ?>
          </div>

          <h2
            class="ks-dir__title"
            <?php if ($args['title_i18n'] !== ''): ?>
              data-i18n="<?php echo esc_attr($args['title_i18n']); ?>"
            <?php endif; ?>
          >
            <?php echo esc_html($args['title']); ?>
          </h2>
        </div>

        <div class="ks-faq-section__acc">
          <?php foreach ($items as $index => $item): ?>
            <?php
              $question = $item[0] ?? '';
              $answer = $item[1] ?? '';
              $q_i18n = $args['items_i18n_prefix'] !== ''
                ? $args['items_i18n_prefix'] . '.items.' . $index . '.question'
                : '';
              $a_i18n = $args['items_i18n_prefix'] !== ''
                ? $args['items_i18n_prefix'] . '.items.' . $index . '.answer'
                : '';
            ?>
            <details class="ks-acc"<?php echo $index === 0 ? ' open data-open-first="true"' : ''; ?>>
              <summary<?php echo $q_i18n !== '' ? ' data-i18n="' . esc_attr($q_i18n) . '"' : ''; ?>>
                <?php echo esc_html($question); ?>
              </summary>

              <div
                class="ks-acc__body"
                <?php echo $a_i18n !== '' ? ' data-i18n="' . esc_attr($a_i18n) . '"' : ''; ?>
              >
                <?php echo esc_html($answer); ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>

        <?php if (!empty($args['side_card_enabled'])): ?>
          <aside class="ks-faq-section__side">
            <div class="ks-faq-section__card">
              <div
                class="ks-kicker ks-faq-section__card-kicker"
                <?php if ($args['side_card_kicker_i18n'] !== ''): ?>
                  data-i18n="<?php echo esc_attr($args['side_card_kicker_i18n']); ?>"
                <?php endif; ?>
              >
                <?php echo esc_html($args['side_card_kicker']); ?>
              </div>

              <h3
                class="ks-faq-section__card-title"
                <?php if ($args['side_card_title_i18n'] !== ''): ?>
                  data-i18n="<?php echo esc_attr($args['side_card_title_i18n']); ?>"
                <?php endif; ?>
              >
                <?php echo esc_html($args['side_card_title']); ?>
              </h3>

              <p
                class="ks-faq-section__card-text"
                <?php if ($args['side_card_text_i18n'] !== ''): ?>
                  data-i18n="<?php echo esc_attr($args['side_card_text_i18n']); ?>"
                <?php endif; ?>
              >
                <?php echo esc_html($args['side_card_text']); ?>
              </p>

              <a
                class="ks-btn"
                href="<?php echo esc_url($args['side_card_href']); ?>"
                <?php if ($args['side_card_button_i18n'] !== ''): ?>
                  data-i18n="<?php echo esc_attr($args['side_card_button_i18n']); ?>"
                <?php endif; ?>
              >
                <?php echo esc_html($args['side_card_button']); ?>
              </a>
            </div>
          </aside>
        <?php elseif (!empty($args['use_video'])): ?>
          <figure class="ks-dir-faq__media">
            <div class="ks-vid ratio">
              <?php echo $args['video_embed']; ?>
            </div>
          </figure>
        <?php elseif (!empty($args['image_src'])): ?>
          <figure class="<?php echo esc_attr($args['image_class']); ?>">
            <img
              src="<?php echo esc_url($args['image_src']); ?>"
              alt=""
              loading="lazy"
              decoding="async"
            >
          </figure>
        <?php endif; ?>
      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}