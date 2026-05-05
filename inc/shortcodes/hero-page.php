<?php

if (!function_exists('ks_register_page_hero_shortcode')) {
  function ks_register_page_hero_shortcode() {
    add_shortcode('ks_hero_page', 'ks_render_page_hero_shortcode');
  }
}

if (!function_exists('ks_render_page_hero_shortcode')) {
  function ks_render_page_hero_shortcode($atts = [], $content = '') {
    $data = ks_get_page_hero_data($atts);
    $hero = ks_get_page_hero_markup($data);
    $body = ks_get_page_hero_content($content);

    return $hero . $body;
  }
}

if (!function_exists('ks_get_page_hero_data')) {
  function ks_get_page_hero_data($atts) {
    $data = shortcode_atts(ks_get_page_hero_defaults(), $atts, 'ks_hero_page');
    $data['image'] = ks_get_page_hero_image($data['image']);
    $data['features'] = ks_should_show_page_hero_features($data['features']);

    return $data;
  }
}

if (!function_exists('ks_get_page_hero_defaults')) {
  function ks_get_page_hero_defaults() {
    return [
      'title' => get_the_title(),
      'subtitle' => '',
      'breadcrumb' => 'Home',
      'watermark' => get_the_title(),
      'image' => '',
      'image_alt' => '',
      'variant' => '',
      'features' => '1',
      'title_i18n' => '',
      'subtitle_i18n' => '',
      'breadcrumb_i18n' => '',
      'watermark_i18n' => '',
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
    <section
      class="<?php echo esc_attr(ks_get_page_hero_class($data['variant'])); ?>"
      data-bgword="<?php echo esc_attr($data['watermark']); ?>"
      <?php ks_print_page_hero_i18n_attr($data['watermark_i18n'], 'data-bgword'); ?>
    >
      <div class="ks-page-hero__inner">
        <?php ks_print_page_hero_content($data); ?>
        <?php ks_print_page_hero_media($data); ?>
      </div>
      <?php ks_print_page_hero_features($data['features']); ?>
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

if (!function_exists('ks_print_page_hero_content')) {
  function ks_print_page_hero_content($data) {
    ?>
    <div class="ks-page-hero__content">
      <?php ks_print_page_hero_crumb($data); ?>
      <h1 class="ks-page-hero__title" <?php ks_print_page_hero_i18n_attr($data['title_i18n']); ?>>
        <?php echo esc_html($data['title']); ?>
      </h1>
      <?php ks_print_page_hero_subtitle($data); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_crumb')) {
  function ks_print_page_hero_crumb($data) {
    ?>
    <p class="ks-page-hero__crumb">
      <span <?php ks_print_page_hero_i18n_attr($data['breadcrumb_i18n']); ?>>
        <?php echo esc_html($data['breadcrumb']); ?>
      </span>
      <span>/</span>
      <strong <?php ks_print_page_hero_i18n_attr($data['title_i18n']); ?>>
        <?php echo esc_html($data['title']); ?>
      </strong>
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

if (!function_exists('ks_print_page_hero_media')) {
  function ks_print_page_hero_media($data) {
    if ($data['image'] === '') {
      return;
    }
    ?>
    <div class="ks-page-hero__media" aria-hidden="true">
      <img
        class="ks-page-hero__image"
        src="<?php echo esc_url($data['image']); ?>"
        alt="<?php echo esc_attr($data['image_alt']); ?>"
        loading="eager"
        decoding="async"
      >
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_features')) {
  function ks_print_page_hero_features($show_features) {
    if (!$show_features) {
      return;
    }
    ?>
    <div class="ks-page-hero__features">
      <?php foreach (ks_get_page_hero_features() as $feature): ?>
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
          <?php echo esc_html($feature['title']); ?>
        </strong>
        <span data-i18n="<?php echo esc_attr($feature['text_i18n']); ?>">
          <?php echo esc_html($feature['text']); ?>
        </span>
      </div>
    </article>
    <?php
  }
}

if (!function_exists('ks_get_page_hero_features')) {
  function ks_get_page_hero_features() {
    $base_uri = get_stylesheet_directory_uri() . '/assets/img/hero';

    return [
      ks_get_page_hero_feature($base_uri, 'trophy', 'Ganzheitliche Förderung', 'Sportlich. Sozial. Persönlich.'),
      ks_get_page_hero_feature($base_uri, 'trainer', 'Erfahrene Trainer', 'Lizenzierte Experten mit Herz.'),
      ks_get_page_hero_feature($base_uri, 'location', 'Regional aktiv', 'Training an starken Standorten.'),
      ks_get_page_hero_feature($base_uri, 'shield', 'Werte, die bleiben', 'Respekt, Teamgeist, Fairness.'),
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

if (!function_exists('ks_get_page_hero_content')) {
  function ks_get_page_hero_content($content) {
    if (trim($content) === '') {
      return '';
    }

    return '<div class="container site-section">' . do_shortcode($content) . '</div>';
  }
}

ks_register_page_hero_shortcode();