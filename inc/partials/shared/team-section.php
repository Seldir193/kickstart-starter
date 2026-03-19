<?php
/**
 * Shared Team Section (Markup 1:1 wie Home)
 *
 * Erwartet VOR dem Include folgende Variablen:
 * - $theme_uri (string)
 * - $coaches (array)
 * - $trainer_url (string)
 * - $fallback_img (string)
 *
 * Benötigt außerdem die Funktion ks_normalize_next_img() (kommt aus team-helpers.php).
 */


?>

<section id="team" class="ks-sec ks-py-56 ks-bg-white">
  <div class="container container--1400">

    <div class="ks-title-wrap" data-bgword="TEAM">
      <div class="ks-kicker">Wir sind für dich da</div>
      <h2 class="ks-dir__title">Unser Team</h2>
    </div>

    <div class="ks-team-wrap">
      <button class="ks-team__nav ks-team__nav--prev" aria-label="Zurück">
        <img src="<?php echo esc_url($theme_uri . '/assets/img/home/left.png'); ?>" alt="" width="28" height="28" />
      </button>

      <div id="ksTeamCarousel" class="ks-team">
        <ul class="ks-team__track" aria-live="polite">
          <?php if (!empty($coaches)): ?>
            <?php foreach ($coaches as $c):
              $first = isset($c['firstName']) ? $c['firstName'] : '';
              $last  = isset($c['lastName'])  ? $c['lastName']  : '';
              $full  = trim(($c['name'] ?? '') ?: trim($first . ' ' . $last));
              if ($full === '') $full = 'Trainer';

              $slug = isset($c['slug']) && $c['slug'] !== '' ? $c['slug'] : sanitize_title($full);

              $rawImg = isset($c['photoUrl']) ? $c['photoUrl'] : '';
              $img    = $rawImg ? ks_normalize_next_img($rawImg) : '';
              if ($img === '') $img = $fallback_img;
              $fallback_img = $theme_uri . '/assets/img/avatar.png';


              $role = isset($c['position']) && $c['position'] ? $c['position'] : 'Trainer';

              $href = add_query_arg('c', rawurlencode($slug), $trainer_url);
            ?>
              <li class="ks-team__card">
                <a href="<?php echo esc_url($href); ?>">
                  <img
                    class="ks-team__img"
                    src="<?php echo esc_attr($img); ?>"
                    alt="<?php echo esc_attr($full); ?>"
                    loading="lazy"
                    decoding="async" />
                </a>
                <div class="ks-team__meta">
                  <div class="ks-team__role"><?php echo esc_html($role); ?></div>
                  <div class="ks-team__name">
                    <a href="<?php echo esc_url($href); ?>"><?php echo esc_html($full); ?></a>
                  </div>
                </div>
              </li>
            <?php endforeach; ?>
          <?php else: ?>
            <li class="ks-team__card">
              <div class="ks-team__meta">
                <div class="ks-team__name">Keine Trainer gefunden.</div>
              </div>
            </li>
          <?php endif; ?>
        </ul>
      </div>

      <button class="ks-team__nav ks-team__nav--next" aria-label="Weiter">
        <img src="<?php echo esc_url($theme_uri . '/assets/img/home/right.png'); ?>" alt="" width="28" height="28" />
      </button>
    </div>
  </div>
</section>
