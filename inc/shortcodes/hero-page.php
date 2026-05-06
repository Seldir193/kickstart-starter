<?php

$hero_page_i18n = get_stylesheet_directory() . '/inc/shortcodes/hero-page-i18n.php';

if (file_exists($hero_page_i18n)) {
  require_once $hero_page_i18n;
}

if (!function_exists('ks_register_page_hero_shortcode')) {
  function ks_register_page_hero_shortcode() {
    add_shortcode('ks_hero_page', 'ks_render_page_hero_shortcode');
  }
}

if (!function_exists('ks_render_page_hero_shortcode')) {
  function ks_render_page_hero_shortcode($atts = [], $content = '') {
    $data = ks_get_page_hero_data($atts);

    return ks_get_page_hero_markup($data) . ks_get_page_hero_content($content);
  }
}

if (!function_exists('ks_get_page_hero_data')) {
  function ks_get_page_hero_data($atts) {
    $data = shortcode_atts(ks_get_page_hero_defaults(), $atts, 'ks_hero_page');
    $data['image'] = ks_get_page_hero_image($data['image']);
    $data['features'] = ks_should_show_page_hero_features($data['features']);

    return function_exists('ks_apply_page_hero_i18n')
      ? ks_apply_page_hero_i18n($data)
      : $data;
  }
}

if (!function_exists('ks_get_page_hero_defaults')) {
  function ks_get_page_hero_defaults() {
    return [
      'title' => get_the_title(),
      'subtitle' => '',
      'breadcrumb' => 'Home',
      'watermark' => '',
      'image' => '',
      'image_alt' => '',
      'variant' => '',
      'features' => '1',
      'eyebrow' => 'Mehr als Fussball',
      'primary_label' => 'Unsere Philosophie',
      'primary_href' => '#philosophie',
      'secondary_label' => 'Trainerteam ansehen',
      'secondary_href' => '#team',
      'title_i18n' => '',
      'subtitle_i18n' => '',
      'breadcrumb_i18n' => '',
      'watermark_i18n' => '',
      'eyebrow_i18n' => 'pageHero.eyebrow',
      'primary_i18n' => 'pageHero.actions.primary',
      'secondary_i18n' => 'pageHero.actions.team',
    ];
  }
}

if (!function_exists('ks_should_show_page_hero_features')) {
  function ks_should_show_page_hero_features($value) {
    return !in_array(strtolower((string) $value), ['0', 'false', 'no'], true);
  }
}

if (!function_exists('ks_get_page_hero_image')) {
  function ks_get_page_hero_image($image) {
    if ($image !== '') {
      return $image;
    }

    return ks_get_featured_page_hero_image();
  }
}

if (!function_exists('ks_get_featured_page_hero_image')) {
  function ks_get_featured_page_hero_image() {
    $image = get_the_post_thumbnail_url(null, 'full');

    return $image ?: get_stylesheet_directory_uri() . '/assets/img/hero/page-hero-default.png';
  }
}

if (!function_exists('ks_get_page_hero_class')) {
  function ks_get_page_hero_class($variant) {
    $classes = ['ks-page-hero'];

    if ($variant !== '') {
      $classes[] = 'ks-page-hero--' . sanitize_html_class($variant);
    }

    return implode(' ', $classes);
  }
}

if (!function_exists('ks_get_page_hero_markup')) {
  function ks_get_page_hero_markup($data) {
    ob_start();
    ks_print_page_hero_markup($data);

    return ob_get_clean();
  }
}

if (!function_exists('ks_print_page_hero_markup')) {
  function ks_print_page_hero_markup($data) {
    ?>
    <section class="<?php echo esc_attr(ks_get_page_hero_class($data['variant'])); ?>">
      <div class="container ks-page-hero__inner">
        <?php ks_print_page_hero_content($data); ?>
        <?php ks_print_page_hero_media($data); ?>
      </div>

      <?php ks_print_page_hero_features($data['features'], $data['variant']); ?>
    </section>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_i18n_attr')) {
  function ks_print_page_hero_i18n_attr($key, $attr = '') {
    if ($key === '') {
      return;
    }

    echo ' data-i18n="' . esc_attr($key) . '"';

    if ($attr !== '') {
      echo ' data-i18n-attr="' . esc_attr($attr) . '"';
    }
  }
}

if (!function_exists('ks_page_hero_get_text')) {
  function ks_page_hero_get_text($key, $fallback) {
    return function_exists('ks_page_hero_translate')
      ? ks_page_hero_translate($key, $fallback)
      : $fallback;
  }
}

if (!function_exists('ks_print_page_hero_content')) {
  function ks_print_page_hero_content($data) {
    ?>
    <div class="ks-page-hero__content">
      <?php ks_print_page_hero_crumb($data); ?>
      <?php ks_print_page_hero_eyebrow($data); ?>

      <h1 class="ks-page-hero__title" <?php ks_print_page_hero_i18n_attr($data['title_i18n']); ?>>
        <?php echo esc_html($data['title']); ?>
      </h1>

      <?php ks_print_page_hero_subtitle($data); ?>
      <?php ks_print_page_hero_actions($data); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_crumb')) {
  function ks_print_page_hero_crumb($data) {
    ?>
    <p class="ks-page-hero__crumb">
      <a class="ks-page-hero__crumb-link" href="<?php echo esc_url(home_url('/')); ?>" <?php ks_print_page_hero_i18n_attr($data['breadcrumb_i18n']); ?>>
        <?php echo esc_html($data['breadcrumb']); ?>
      </a>

      <span class="ks-page-hero__crumb-separator">/</span>

      <strong class="ks-page-hero__crumb-current" <?php ks_print_page_hero_i18n_attr($data['title_i18n']); ?>>
        <?php echo esc_html($data['title']); ?>
      </strong>
    </p>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_eyebrow')) {
  function ks_print_page_hero_eyebrow($data) {
    if ($data['eyebrow'] === '') {
      return;
    }
    ?>
    <p class="ks-kicker ks-page-hero__eyebrow" <?php ks_print_page_hero_i18n_attr($data['eyebrow_i18n']); ?>>
      <?php echo esc_html($data['eyebrow']); ?>
    </p>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_subtitle')) {
  function ks_print_page_hero_subtitle($data) {
    if ($data['subtitle'] === '') {
      return;
    }
    ?>
    <p class="ks-page-hero__subtitle" <?php ks_print_page_hero_i18n_attr($data['subtitle_i18n']); ?>>
      <?php echo esc_html($data['subtitle']); ?>
    </p>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_actions')) {
  function ks_print_page_hero_actions($data) {
    if ($data['primary_label'] === '' && $data['secondary_label'] === '') {
      return;
    }
    ?>
    <div class="ks-page-hero__actions">
      <?php ks_print_page_hero_button($data, 'primary'); ?>
      <?php ks_print_page_hero_button($data, 'secondary'); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_button')) {
  function ks_print_page_hero_button($data, $type) {
    $label = $data[$type . '_label'];
    $href = $data[$type . '_href'];

    if ($label === '' || $href === '') {
      return;
    }

    ks_print_page_hero_button_markup($label, $href, $type, $data[$type . '_i18n']);
  }
}

if (!function_exists('ks_print_page_hero_button_markup')) {
  function ks_print_page_hero_button_markup($label, $href, $type, $i18n) {
    $button_class = $type === 'primary' ? 'ks-btn ks-btn--dark' : 'ks-btn';
    ?>
    <a class="<?php echo esc_attr($button_class); ?>" href="<?php echo esc_url($href); ?>">
      <span <?php ks_print_page_hero_i18n_attr($i18n); ?>>
        <?php echo esc_html($label); ?>
      </span>
    </a>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_media')) {
  function ks_print_page_hero_media($data) {
    if ($data['image'] === '') {
      return;
    }
    ?>
    <div class="ks-page-hero__media" aria-hidden="true">
      <div class="ks-page-hero__image-card">
        <img
          class="ks-page-hero__image"
          src="<?php echo esc_url($data['image']); ?>"
          alt="<?php echo esc_attr($data['image_alt']); ?>"
          loading="eager"
          decoding="async"
        >
      </div>

      <?php ks_print_page_hero_floating_cards($data['features'], $data['variant']); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_floating_cards')) {
  function ks_print_page_hero_floating_cards($show_features, $variant) {
    if (!$show_features) {
      return;
    }

    foreach (ks_get_page_hero_floating_cards($variant) as $card) {
      ks_print_page_hero_floating_card($card);
    }
  }
}

if (!function_exists('ks_print_page_hero_floating_card')) {
  function ks_print_page_hero_floating_card($card) {
    ?>
    <article class="<?php echo esc_attr($card['class']); ?>">
      <img src="<?php echo esc_url($card['icon']); ?>" alt="" aria-hidden="true">

      <strong data-i18n="<?php echo esc_attr($card['title_i18n']); ?>">
        <?php echo esc_html(ks_page_hero_get_text($card['title_i18n'], $card['title'])); ?>
      </strong>

      <span data-i18n="<?php echo esc_attr($card['text_i18n']); ?>">
        <?php echo esc_html(ks_page_hero_get_text($card['text_i18n'], $card['text'])); ?>
      </span>
    </article>
    <?php
  }
}

if (!function_exists('ks_get_page_hero_floating_cards')) {
  function ks_get_page_hero_floating_cards($variant = '') {
    if ($variant === 'franchise') {
      return ks_get_page_hero_franchise_floating_cards();
    }

    return ks_get_page_hero_default_floating_cards();
  }
}

if (!function_exists('ks_get_page_hero_default_floating_cards')) {
  function ks_get_page_hero_default_floating_cards() {
    $base_uri = get_stylesheet_directory_uri() . '/assets/img/hero';

    return [
      ks_get_page_hero_floating_card($base_uri, 'trophy', 'Ganzheitliche Förderung', 'Sportlich. Sozial. Persönlich.', 'dark'),
      ks_get_page_hero_floating_card($base_uri, 'shield', 'Werte, die bleiben', 'Respekt, Teamgeist, Fairness.', 'light'),
    ];
  }
}

if (!function_exists('ks_get_page_hero_franchise_floating_cards')) {
  function ks_get_page_hero_franchise_floating_cards() {
    $base_uri = get_stylesheet_directory_uri() . '/assets/img/hero';

    return [
      ks_get_page_hero_franchise_floating_card($base_uri, 'model', 'trophy', 'Bewährtes Konzept', 'Strukturierte Grundlage für deinen Standort.', 'dark'),
      ks_get_page_hero_franchise_floating_card($base_uri, 'partner', 'shield', 'Langfristige Partnerschaft', 'Gemeinsam wachsen mit klarer Perspektive.', 'light'),
    ];
  }
}

if (!function_exists('ks_get_page_hero_franchise_floating_card')) {
  function ks_get_page_hero_franchise_floating_card($base_uri, $key, $icon, $title, $text, $variant) {
    return [
      'icon' => $base_uri . '/' . $icon . '.svg',
      'title' => $title,
      'text' => $text,
      'class' => 'ks-page-hero__float-card ks-page-hero__float-card--' . $variant,
      'title_i18n' => 'pageHero.franchiseFeatures.' . $key . '.title',
      'text_i18n' => 'pageHero.franchiseFeatures.' . $key . '.text',
    ];
  }
}

if (!function_exists('ks_get_page_hero_floating_card')) {
  function ks_get_page_hero_floating_card($base_uri, $key, $title, $text, $variant) {
    return [
      'icon' => $base_uri . '/' . $key . '.svg',
      'title' => $title,
      'text' => $text,
      'class' => 'ks-page-hero__float-card ks-page-hero__float-card--' . $variant,
      'title_i18n' => 'pageHero.features.' . $key . '.title',
      'text_i18n' => 'pageHero.features.' . $key . '.text',
    ];
  }
}

if (!function_exists('ks_print_page_hero_features')) {
  function ks_print_page_hero_features($show_features, $variant) {
    if (!$show_features) {
      return;
    }
    ?>
    <div class="ks-page-hero__features">
      <?php foreach (ks_get_page_hero_features($variant) as $feature): ?>
        <?php ks_print_page_hero_feature($feature); ?>
      <?php endforeach; ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_feature')) {
  function ks_print_page_hero_feature($feature) {
    ?>
    <article class="ks-page-hero__feature">
      <img src="<?php echo esc_url($feature['icon']); ?>" alt="" aria-hidden="true">

      <div>
        <strong data-i18n="<?php echo esc_attr($feature['title_i18n']); ?>">
          <?php echo esc_html(ks_page_hero_get_text($feature['title_i18n'], $feature['title'])); ?>
        </strong>

        <span data-i18n="<?php echo esc_attr($feature['text_i18n']); ?>">
          <?php echo esc_html(ks_page_hero_get_text($feature['text_i18n'], $feature['text'])); ?>
        </span>
      </div>
    </article>
    <?php
  }
}

if (!function_exists('ks_get_page_hero_features')) {
  function ks_get_page_hero_features($variant = '') {
    if ($variant === 'franchise') {
      return ks_get_page_hero_franchise_features();
    }

    return ks_get_page_hero_default_features();
  }
}

if (!function_exists('ks_get_page_hero_default_features')) {
  function ks_get_page_hero_default_features() {
    $base_uri = get_stylesheet_directory_uri() . '/assets/img/hero';

    return [
      ks_get_page_hero_feature($base_uri, 'trophy', 'Ganzheitliche Förderung', 'Sportlich. Sozial. Persönlich.'),
      ks_get_page_hero_feature($base_uri, 'trainer', 'Erfahrene Trainer', 'Lizenzierte Experten mit Herz.'),
      ks_get_page_hero_feature($base_uri, 'location', 'Regional aktiv', 'Training an starken Standorten.'),
      ks_get_page_hero_feature($base_uri, 'shield', 'Werte, die bleiben', 'Respekt, Teamgeist, Fairness.'),
    ];
  }
}

if (!function_exists('ks_get_page_hero_franchise_features')) {
  function ks_get_page_hero_franchise_features() {
    $base_uri = get_stylesheet_directory_uri() . '/assets/img/hero';

    return [
      ks_get_page_hero_franchise_feature($base_uri, 'model', 'trophy', 'Bewährtes Konzept', 'Strukturierte Grundlage für deinen Standort.'),
      ks_get_page_hero_franchise_feature($base_uri, 'support', 'trainer', 'Starke Begleitung', 'Know-how, Austausch und klare Standards.'),
      ks_get_page_hero_franchise_feature($base_uri, 'start', 'location', 'Schneller Start', 'Mit Marke, Prozessen und Unterstützung.'),
      ks_get_page_hero_franchise_feature($base_uri, 'partner', 'shield', 'Langfristige Partnerschaft', 'Gemeinsam wachsen mit klarer Perspektive.'),
    ];
  }
}

if (!function_exists('ks_get_page_hero_feature')) {
  function ks_get_page_hero_feature($base_uri, $key, $title, $text) {
    return [
      'icon' => $base_uri . '/' . $key . '.svg',
      'title' => $title,
      'text' => $text,
      'title_i18n' => 'pageHero.features.' . $key . '.title',
      'text_i18n' => 'pageHero.features.' . $key . '.text',
    ];
  }
}

if (!function_exists('ks_get_page_hero_franchise_feature')) {
  function ks_get_page_hero_franchise_feature($base_uri, $key, $icon, $title, $text) {
    return [
      'icon' => $base_uri . '/' . $icon . '.svg',
      'title' => $title,
      'text' => $text,
      'title_i18n' => 'pageHero.franchiseFeatures.' . $key . '.title',
      'text_i18n' => 'pageHero.franchiseFeatures.' . $key . '.text',
    ];
  }
}

if (!function_exists('ks_get_page_hero_content')) {
  function ks_get_page_hero_content($content) {
    if (trim($content) === '') {
      return '';
    }

    return '<div class="container site-section">' . do_shortcode($content) . '</div>';
  }
}

ks_register_page_hero_shortcode();










