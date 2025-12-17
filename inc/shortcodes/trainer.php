<?php
/**
 * inc/shortcodes/trainer.php
 *
 * Shortcode: [ks_trainer_profile]
 * - Lädt einen Trainer (per ?c=slug) aus dem Next.js-Proxy (/api/coaches/:slug)
 * - Zeigt Hero, Bild und Info-Tabelle
 * - Links: Custom-Dropdown (50px hoch, max. 5 Einträge sichtbar, Rest scrollbar)
 * - Verwendet ein verstecktes <select> fürs Formular-Submit; UI ist ein eigenes Dropdown
 */

$theme_dir = get_stylesheet_directory();
$theme_uri = get_stylesheet_directory_uri();

$dd_hover_css = $theme_dir . '/assets/css/dropdown-hover.css';
if (file_exists($dd_hover_css) && !wp_style_is('ks-dropdown-hover', 'enqueued')) {
  wp_enqueue_style(
    'ks-dropdown-hover',
    $theme_uri . '/assets/css/dropdown-hover.css',
    ['kickstart-style'],
    filemtime($dd_hover_css)
  );
}


/* ==== Next.js Basis-URL (Proxy) ==== */
if (!function_exists('ks_next_base')) {
  function ks_next_base(): string {
    $opt = get_option('ks_next_base');
    if (!empty($opt)) return rtrim((string) $opt, '/');
    return 'http://localhost:3000';
  }
}

/* ==== Bild-URL robust normalisieren ==== */
/* Erlaubt http/https, data:image/* oder relative Pfade (an NEXT-Base anhängen) */
if (!function_exists('ks_normalize_next_img')) {
  function ks_normalize_next_img(?string $u): string {
    $u = trim((string) $u);
    if ($u === '') return '';
    if (preg_match('~^(https?://|data:image/)~i', $u)) return $u;
    // führendes "admin/" entfernen
    $u = preg_replace('#^/?admin/#i', '', $u);
    // an Next-Base hängen
    $base = rtrim(ks_next_base(), '/');
    if ($base) {
      if ($u[0] !== '/') $u = '/' . $u;
      return $base . $u;
    }
    return $u;
  }
}

/* ==== Shortcode registrieren ==== */
if (!function_exists('ks_register_trainer_shortcode')) {
  function ks_register_trainer_shortcode() {

    add_shortcode('ks_trainer_profile', function () {

      /* --- akt. Trainer aus ?c= (Slug) --- */
      $slug = isset($_GET['c']) ? sanitize_title(wp_unslash($_GET['c'])) : '';
      if (!$slug) return '<p>Kein Trainer ausgewählt.</p>';

      $next_base = rtrim(ks_next_base(), '/');

      /* --- 1) Aktuellen Trainer laden --- */
      $coach_url = $next_base . '/api/coaches/' . rawurlencode($slug);
      $res = wp_remote_get($coach_url, [
        'timeout' => 12,
        'headers' => ['Accept' => 'application/json'],
      ]);
      if (is_wp_error($res) || wp_remote_retrieve_response_code($res) !== 200) {
        $code = is_wp_error($res) ? 0 : wp_remote_retrieve_response_code($res);
        return '<p>Trainer nicht gefunden.<br>'
             . 'URL: ' . esc_html($coach_url) . '<br>'
             . 'Status: ' . esc_html($code) . '</p>';
      }
      $coach = json_decode(wp_remote_retrieve_body($res), true);
      if (!$coach || !is_array($coach)) {
        return '<p>Trainer nicht gefunden. (Ungültige Antwort)</p>';
      }


      


      $full = trim(($coach['name'] ?? '') ?: trim(($coach['firstName'] ?? '') . ' ' . ($coach['lastName'] ?? '')));      
      if ($full === '') $full = 'Trainer';
      $img  = ks_normalize_next_img($coach['photoUrl'] ?? '');

      $rows = [
        'Position'           => $coach['position']   ?? '',
        'Abschluss'          => $coach['degree']     ?? '',
        'Bei der MFS seit'   => $coach['since']      ?? '',
        'DFB Lizenz'         => $coach['dfbLicense'] ?? '',
        'MFS Lizenz'         => $coach['mfsLicense'] ?? '',
        'Lieblingsverein'    => $coach['favClub']    ?? '',
        'Lieblingstrainer'   => $coach['favCoach']   ?? '',
        'Lieblingstrick'     => $coach['favTrick']   ?? '',
      ];

      /* --- 2) Alle Trainer für Dropdown laden --- */
      $all_coaches = [];
      foreach ([
        $next_base . '/api/coaches?limit=200',
        $next_base . '/api/admin/coaches?limit=200',
      ] as $list_url) {
        $r = wp_remote_get($list_url, ['timeout' => 10, 'headers' => ['Accept' => 'application/json']]);
        if (!is_wp_error($r) && wp_remote_retrieve_response_code($r) === 200) {
          $json = json_decode(wp_remote_retrieve_body($r), true);
          if (isset($json['items']) && is_array($json['items'])) { $all_coaches = $json['items']; break; }
          if (is_array($json)) { $all_coaches = $json; break; }
        }
      }

      /* Ziel-URL dieser Seite (ohne Query) fürs Formular */
      $action_url = get_permalink();

      /* === Assets: kleines JS für Custom-Dropdown === */
      // Eindeutige IDs, falls Shortcode mehrfach verwendet wird
      $uid = uniqid('ks-trn-');
      $form_id   = 'ksTrainerSelectForm-' . $uid;
      $select_id = 'ks-trainer-select-'   . $uid;
      $dd_id     = 'ks-dd-trainer-'       . $uid;

    


      

      /* --- HTML ausgeben --- */
      ob_start(); ?>
      <!-- HERO (Full-Bleed + mittiges Wasserzeichen) -->
      <section class="ks-trainer-hero" data-watermark="TRAINER">
        <div class="container">
          <div class="ks-kicker">
            
             <a href="http://localhost/wordpress/">Home</a> • Trainer
          </div>
          <h1><?php echo esc_html($full); ?></h1>
        </div>
      </section>

      <!-- Inhalt: 3-spaltig (Dropdown links, Foto mittig, Tabelle rechts) -->
      <section class="ks-trainer-wrap">
        <div class="container">
          <div class="ks-trainer-grid">

            <!-- Spalte 1: Dropdown -->
            <div class="ks-trainer-col ks-trainer-col--left">
              <?php if (!empty($all_coaches)): ?>
                <form id="<?php echo esc_attr($form_id); ?>" class="ks-trainer-select" action="<?php echo esc_url($action_url); ?>" method="get">
                  <label class="screen-reader-text" for="<?php echo esc_attr($select_id); ?>">Trainer wählen</label>

                  <!-- echtes Select (unsichtbar, bleibt fürs Submit) -->
                  <select id="<?php echo esc_attr($select_id); ?>" name="c" class="ks-select">
                    <?php foreach ($all_coaches as $c):
                      $first = $c['firstName'] ?? ''; $last = $c['lastName'] ?? '';
                      $name  = trim(($c['name'] ?? '') ?: trim("$first $last")) ?: 'Trainer';
                      $s     = isset($c['slug']) && $c['slug'] !== '' ? $c['slug'] : sanitize_title($name);
                    ?>
                      <option value="<?php echo esc_attr($s); ?>" <?php selected($s, $slug); ?>>
                        <?php echo esc_html($name); ?>
                      </option>
                    <?php endforeach; ?>
                  </select>

                  <!-- Custom Dropdown UI -->
                  <div class="ks-dd" id="<?php echo esc_attr($dd_id); ?>" aria-expanded="false" data-submit="1" data-max-rows="5">
                    <button type="button" class="ks-dd__btn" aria-haspopup="listbox" aria-expanded="false">
                      <span class="ks-dd__label"><?php echo esc_html($full); ?></span>
                      <span class="ks-dd__caret" aria-hidden="true">
                        <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/offers/select-caret.svg' ); ?>" alt="">
</span>
                    </button>
                    <div class="ks-dd__panel" role="listbox" tabindex="-1"></div>
                  </div>
                </form>
              <?php endif; ?>
            </div>

            <!-- Spalte 2: Foto (mittig, fix ~290px breit, höhengleich) -->
            <div class="ks-trainer-col ks-trainer-col--photo">
              <div class="ks-trainer-photo">
                <?php if ($img): ?>
                  <img
                    src="<?php echo esc_attr($img); ?>"
                    alt="<?php echo esc_attr($full); ?>"
                    loading="lazy"
                    decoding="async">
                <?php endif; ?>
              </div>
            </div>

            <!-- Spalte 3: Tabelle (rechts) -->
            <div class="ks-trainer-col ks-trainer-col--right">
              <table class="ks-trainer-table">
                <thead>
                  <tr>
                    <th>NAME</th>
                    <th><?php echo esc_html($full); ?></th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($rows as $label => $val): if (!$val) continue; ?>
                    <tr>
                      <th><?php echo esc_html($label); ?></th>
                      <td><?php echo esc_html($val); ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

          </div>
        </div>
      </section>
      <?php
      return ob_get_clean();
    });
  }

  add_action('init', 'ks_register_trainer_shortcode');
}















