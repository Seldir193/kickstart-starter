<?php
/**
 * Zentrale FAQ-Helfer
 */

/**
 * Lädt FAQ-Items je nach Kontext.
 *
 * @param string      $context 'offers' | 'home' | 'franchise'
 * @param string|null $key     für offers (z.B. 'Foerdertraining', 'Camp', ...)
 * @return array      Liste von [frage, antwort]
 */
if (!function_exists('ks_get_faq_items')) {
  function ks_get_faq_items(string $context, ?string $key = null): array {
    $theme_dir = get_stylesheet_directory();

    switch ($context) {

      /* ===========================================================
       * 1) OFFERS – FAQ-Texte aus inc/shortcodes/faq-texts.de.php
       *    Struktur:
       *    return [
       *      'Foerdertraining' => [
       *        ['Frage', 'Antwort'],
       *        ...
       *      ],
       *      'Camp' => [ ... ],
       *    ];
       * =========================================================== */
      case 'offers':
        $faq_texts = [];
        $file = $theme_dir . '/inc/shortcodes/faq-texts.de.php';

        if (file_exists($file)) {
          $data = include $file;

          // neue Variante: Datei gibt Array zurück
          if (is_array($data)) {
            $faq_texts = $data;
          }
          // alte Variante: Datei könnte $faq_texts setzen
        }

        if ($key !== null && isset($faq_texts[$key]) && is_array($faq_texts[$key])) {
          return $faq_texts[$key];
        }

        return [];

      /* ===========================================================
       * 2) HOME – FAQ-Texte aus inc/shortcodes/faq-texts-home.de.php
       *    akzeptiert:
       *    a) return [[frage, antwort], ...]
       *    b) return ['home' => [...]]
       *    c) $faq_home = [...]
       * =========================================================== */
      case 'home':
        $file = $theme_dir . '/inc/shortcodes/faq-texts-home.de.php';

        if (file_exists($file)) {
          $faq_home = [];
          $data = include $file;

          // Datei gibt ein Array zurück
          if (is_array($data)) {

            // b) Verschachtelt: ['home' => [...]]
            if (isset($data['home']) && is_array($data['home'])) {
              return $data['home'];
            }

            // a) Direkt eine Liste: [[frage, antwort], ...]
            $keys = array_keys($data);
            $is_list = ($keys === range(0, count($data) - 1));

            if ($is_list) {
              return $data;
            }
          }

          // c) Fallback: Datei setzt $faq_home
          if (isset($faq_home) && is_array($faq_home) && !empty($faq_home)) {
            return $faq_home;
          }
        }
        return [];

      /* ===========================================================
       * 3) FRANCHISE – FAQ-Texte aus inc/shortcodes/faq-texts-franchise.de.php
       *    akzeptiert:
       *    a) return [[frage, antwort], ...]
       *    b) return ['franchise' => [...]]
       *    c) $faq_franchi = [...]
       * =========================================================== */
      case 'franchise':
        $file = $theme_dir . '/inc/shortcodes/faq-texts-franchise.de.php';

        if (file_exists($file)) {
          $faq_franchi = [];
          $data = include $file;

          // Datei gibt ein Array zurück
          if (is_array($data)) {

            // b) Verschachtelt: ['franchise' => [...]]
            if (isset($data['franchise']) && is_array($data['franchise'])) {
              return $data['franchise'];
            }

            // a) Direkt eine Liste: [[frage, antwort], ...]
            $keys = array_keys($data);
            $is_list = ($keys === range(0, count($data) - 1));

            if ($is_list) {
              return $data;
            }
          }

          // c) Fallback: Datei setzt $faq_franchi
          if (isset($faq_franchi) && is_array($faq_franchi) && !empty($faq_franchi)) {
            return $faq_franchi;
          }
        }
        return [];
    }

    return [];
  }
}

/**
 * Rendert die komplette FAQ-Sektion.
 * $items: [
 *   ['Frage 1', 'Antwort 1'],
 *   ['Frage 2', 'Antwort 2'],
 * ]
 */
if (!function_exists('ks_render_faq_section')) {
  function ks_render_faq_section(array $items, array $args = []): string {
    if (empty($items)) {
      return '';
    }

    $theme_uri = get_stylesheet_directory_uri();

    $defaults = [
      'section_id'    => 'faq',
      'wrapper_class' => 'container ks-home-faq',
      'title'         => 'Häufig gestellte Fragen',
      'kicker'        => 'FAQ',
      'watermark'     => 'FAQ',
      'use_video'     => false,
      'video_embed'   => '',
      'image_src'     => '',               // optionales Bild
      'image_class'   => 'fr-faq__image',  // Standard-Klasse fürs Bild
    ];

    $args = array_merge($defaults, $args);

    ob_start(); ?>
    <section id="<?php echo esc_attr($args['section_id']); ?>"
      class="ks-sec ks-py-56"
      style="--acc-plus:url('<?php echo esc_url($theme_uri . '/assets/img/home/plus.png'); ?>');
             --acc-minus:url('<?php echo esc_url($theme_uri . '/assets/img/home/minus.png'); ?>');">

      <div class="<?php echo esc_attr($args['wrapper_class']); ?>">

        <div class="ks-title-wrap" data-bgword="<?php echo esc_attr($args['watermark']); ?>">
          <div class="ks-kicker"><?php echo esc_html($args['kicker']); ?></div>
          <h2 class="ks-dir__title"><?php echo esc_html($args['title']); ?></h2>
        </div>

        <!-- Linke Spalte: Akkordeon -->
        <div>
          <?php foreach ($items as $index => $item):
            $question = $item[0] ?? '';
            $answer   = $item[1] ?? '';
          ?>
            <details class="ks-acc" <?php echo $index === 0 ? 'open' : ''; ?>>
              <summary><?php echo esc_html($question); ?></summary>
              <div class="ks-acc__body">
                <?php echo nl2br( esc_html($answer) ); ?>
              </div>
            </details>
          <?php endforeach; ?>
        </div>

        <!-- Rechte Spalte: Video ODER Bild -->
        <?php if (!empty($args['use_video'])): ?>
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
              decoding="async">
          </figure>
        <?php endif; ?>

      </div>
    </section>
    <?php
    return ob_get_clean();
  }
}











