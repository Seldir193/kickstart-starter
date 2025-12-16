<?php
// inc/shortcodes/brandbar.php
// Shortcode [ks_brandbar] – Partner-Leiste (Puma, DFS Berater, Teamstolz, DFS Player)

if (!function_exists('ks_register_brandbar_shortcode')) {
  function ks_register_brandbar_shortcode() {

    add_shortcode('ks_brandbar', function () {
      $theme_uri = get_stylesheet_directory_uri();

      // Falls du extra CSS brauchst, könntest du hier ks-home.css oder eine eigene Datei enqueuen.
      // Beispiel (nur wenn nötig):
      // $home_css = get_stylesheet_directory() . '/assets/css/ks-home.css';
      // if (file_exists($home_css) && !wp_style_is('ks-home', 'enqueued')) {
      //   wp_enqueue_style(
      //     'ks-home',
      //     $theme_uri . '/assets/css/ks-home.css',
      //     ['kickstart-style', 'ks-utils'],
      //     filemtime($home_css)
      //   );
      // }

      ob_start();
      ?>
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
      <?php
      return ob_get_clean();
    });

  }

  add_action('init', 'ks_register_brandbar_shortcode');
}
