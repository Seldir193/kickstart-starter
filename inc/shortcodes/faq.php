<?php

if (!function_exists('ks_register_faq_shortcode')) {
  function ks_register_faq_shortcode() {

    add_shortcode('ks_faq', function ($atts = []) {
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
      if (file_exists($home_abs) && !wp_style_is('ks-home', 'enqueued')) {
        wp_enqueue_style(
          'ks-home',
          $theme_uri . '/assets/css/ks-home.css',
          ['kickstart-style', 'ks-utils'],
          filemtime($home_abs)
        );
      }

      $dir_abs = $theme_dir . '/assets/css/ks-dir.css';
      if (file_exists($dir_abs) && !wp_style_is('ks-dir', 'enqueued')) {
        wp_enqueue_style(
          'ks-dir',
          $theme_uri . '/assets/css/ks-dir.css',
          ['ks-home'],
          filemtime($dir_abs)
        );
      }

      $faq_css_abs = $theme_dir . '/assets/css/ks-faq.css';
      if (file_exists($faq_css_abs) && !wp_style_is('ks-faq', 'enqueued')) {
        wp_enqueue_style(
          'ks-faq',
          $theme_uri . '/assets/css/ks-faq.css',
          ['ks-dir', 'ks-home', 'ks-utils'],
          filemtime($faq_css_abs)
        );
      }

      $atts = shortcode_atts([
        'title'     => 'FAQ',
        'watermark' => 'FAQ',
        'kicker'    => 'FAQ',
      ], $atts, 'ks_faq');

      $title = trim((string) $atts['title']);
      $kicker = trim((string) $atts['kicker']);
      $watermark = trim((string) $atts['watermark']);

      $texts_file = $theme_dir . '/inc/shortcodes/faq-texts-faq.de.php';
      if (!file_exists($texts_file)) {
        return '';
      }

      $faq_items = include $texts_file;
      if (!is_array($faq_items) || empty($faq_items)) {
        return '';
      }

      $hero_url = get_the_post_thumbnail_url(null, 'full');
      if (!$hero_url) {
        $hero_url = $theme_uri . '/assets/img/home/mfs.png';
      }

      ob_start(); ?>

      <div id="faq-page-hero"
           class="ks-dir__hero"
           data-watermark="<?php echo esc_attr($watermark ?: 'FAQ'); ?>"
           style="--hero-img:url('<?php echo esc_url($hero_url); ?>')">
        <div class="ks-dir__hero-inner">
          <div class="ks-dir__crumb">
            <a class="ks-dir__crumb-home" href="<?php echo esc_url(home_url('/')); ?>">Home</a>
            <span class="sep">/</span>
            <?php echo esc_html($title ?: 'FAQ'); ?>
          </div>
          <h1 class="ks-dir__hero-title"><?php echo esc_html($title ?: 'FAQ'); ?></h1>
        </div>
      </div>

      <section id="faq" class="ks-sec ks-py-56">
        <div class="container ks-home-faq ks-faq-page--full">
          <div class="ks-title-wrap" data-bgword="FAQ">
            <div class="ks-kicker"><?php echo esc_html($kicker ?: 'FAQ'); ?></div>
            <h2 class="ks-dir__title">Häufig gestellte Fragen</h2>
          </div>

          <div class="ks-faq-page__acc">
            <?php foreach ($faq_items as $it):
              $q = is_array($it) ? (string) ($it['q'] ?? '') : '';
              $a = is_array($it) ? ($it['a'] ?? '') : '';
              if ($q === '') continue; ?>
              <details class="ks-acc">
                <summary><?php echo esc_html($q); ?></summary>
                <div class="ks-acc__body">
                  <?php echo wp_kses_post((string) $a); ?>
                </div>
              </details>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <?php
      return ob_get_clean();
    });

  }

  add_action('init', 'ks_register_faq_shortcode');
}



