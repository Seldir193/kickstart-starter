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
    $atts = shortcode_atts(ks_get_page_hero_defaults(), $atts, 'ks_hero_page');
    $atts['image'] = ks_get_page_hero_image($atts['image']);

    return $atts;
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
    ];
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
    >
      <div class="ks-page-hero__inner">
        <?php ks_print_page_hero_content($data); ?>
        <?php ks_print_page_hero_media($data); ?>
      </div>
    </section>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_content')) {
  function ks_print_page_hero_content($data) {
    ?>
    <div class="ks-page-hero__content">
      <p class="ks-page-hero__crumb">
        <?php echo esc_html($data['breadcrumb']); ?>
        <span>/</span>
        <?php echo esc_html($data['title']); ?>
      </p>
      <h1 class="ks-page-hero__title"><?php echo esc_html($data['title']); ?></h1>
      <?php ks_print_page_hero_subtitle($data['subtitle']); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_subtitle')) {
  function ks_print_page_hero_subtitle($subtitle) {
    if ($subtitle === '') {
      return;
    }
    ?>
    <p class="ks-page-hero__subtitle"><?php echo esc_html($subtitle); ?></p>
    <?php
  }
}

if (!function_exists('ks_print_page_hero_media')) {
  function ks_print_page_hero_media($data) {
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

if (!function_exists('ks_get_page_hero_content')) {
  function ks_get_page_hero_content($content) {
    if (trim($content) === '') {
      return '';
    }

    return '<div class="container site-section">' . do_shortcode($content) . '</div>';
  }
}

ks_register_page_hero_shortcode();





