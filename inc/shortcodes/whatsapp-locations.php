

<?php
// [ks_whatsapp_locations mode="auto" text="..." campaign="whatsapp_locations" btn_class="ks-wa-btn" select_class="ks-wa-select" placeholder="Standort wählen…"]
// - mode=auto  : 1 Standort => Button, >=2 Standorte => Dropdown + Button
// - mode=list  : Liste mit Buttons (wie bisher)
// - mode=dropdown : immer Dropdown + Button

if (!function_exists('ks_register_whatsapp_locations_shortcode')) {
  function ks_register_whatsapp_locations_shortcode() {

    add_shortcode('ks_whatsapp_locations', function ($atts = []) {
      $a = shortcode_atts([
        'mode'         => 'auto',                    // auto | list | dropdown
        'text'         => 'Hallo DFS, ich habe eine Frage zu euren Programmen.',
        'campaign'     => 'whatsapp_locations',
        'btn_class'    => 'ks-wa-btn',               // Button-Klasse
        'select_class' => 'ks-wa-select',            // Select-Klasse (kannst du in ks-wa.css stylen)
        'placeholder'  => 'Standort wählen…',
      ], $atts, 'ks_whatsapp_locations');

      // ---- Standorte pflegen (oder später via ACF Options ersetzen) ----
      $locations = [
        [ 'label' => 'Dortmund', 'phone' => '4917643203362' ],
        [ 'label' => 'Köln',     'phone' => '491714324324' ],
        [ 'label' => 'Duisburg',     'phone' => '491714324388' ],
        // weitere Standorte …
      ];

      // kompaktes WhatsApp-Icon als Inline-SVG (übernimmt currentColor)
      $wa_icon = '<svg aria-hidden="true" width="18" height="18" viewBox="0 0 256 256" xmlns="http://www.w3.org/2000/svg" focusable="false"><path fill="currentColor" d="M128 20C69 20 20 66.7 20 124.2c0 21.9 6.3 42.5 17.3 59.9L20 236l54-16c15.6 8.5 33.4 13.4 54 13.4 59 0 108-46.7 108-104.2C236 66.7 187 20 128 20zm0 187.8c-17.7 0-34.1-4.7-48-13.5l-3.4-2.1-32 9.3 9.5-30.6-2.2-3.5c-10.2-16-15.6-34.3-15.6-53.5 0-52 44-94.3 98-94.3s98 42.3 98 94.3-44 94.3-98 94.3zm55.6-70.5c-3-1.5-17.5-8.7-20.2-9.7-2.7-1-4.7-1.5-6.7 1.5s-7.7 9.7-9.5 11.7c-1.7 2-3.5 2.2-6.5.7-3-1.5-12.5-4.6-23.8-14.7-8.8-7.8-14.7-17.5-16.4-20.5-1.7-3-.2-4.5 1.3-6 1.3-1.3 3-3.5 4.5-5.2s2-3 3-5c1-2 .5-3.7 0-5.2-.5-1.5-6.7-16-9.2-21.9-2.4-5.8-4.8-5-6.7-5h-5.7c-2 0-5.2.7-8 3.7-2.7 3-10.5 10.3-10.5 25s10.7 29 12.2 31c1.5 2 21 32.5 51 45.5 30 13 30 8.7 35.5 8.2 5.5-.5 17.5-7.2 20-14.2 2.5-7 2.5-13 1.7-14.2-.8-1.2-2.8-2-5.8-3.5z"/></svg>';

      // ---- Säubern & validieren ----
      $clean = [];
      foreach ($locations as $loc) {
        $label = trim((string)($loc['label'] ?? ''));
        $phone = preg_replace('~\D+~', '', (string)($loc['phone'] ?? ''));
        if ($label !== '' && $phone !== '') {
          $clean[] = ['label'=>$label, 'phone'=>$phone];
        }
      }
      if (!$clean) return '';

      // ---- Modus bestimmen ----
      $count = count($clean);
      $mode  = $a['mode'];
      if ($mode === 'auto') {
        $mode = ($count <= 1) ? 'single' : 'dropdown';
      } elseif ($mode === 'list') {
        $mode = 'list';
      } elseif ($mode === 'dropdown') {
        $mode = 'dropdown';
      }

      ob_start();





      // ---------- SINGLE: Ein Standort => nur 1 Button ----------
      if ($mode === 'single') {
        $label    = $clean[0]['label'];
        $phone    = $clean[0]['phone'];
        $slug     = sanitize_title($label);
        $campaign = $a['campaign'] ? $a['campaign'].'_'.$slug : 'whatsapp_'.$slug;

        $href = sprintf(
          'https://wa.me/%s?text=%s&utm_source=website&utm_medium=cta&utm_campaign=%s',
          $phone,
          rawurlencode($a['text']),
          rawurlencode($campaign)
        ); ?>
        <section class="ks-sec ks-wa-strip ks-wa-single" aria-label="<?php echo esc_attr(sprintf('WhatsApp Kontakt %s', $label)); ?>">
          <div class="container ks-wa-grid">
            <p class="ks-wa-text">
              Fragen? <strong>Schreib uns auf WhatsApp.</strong>
              <span class="ks-wa-meta">Standort: <?php echo esc_html($label); ?></span>
            </p>
            <a class="<?php echo esc_attr($a['btn_class']); ?>"
               href="<?php echo esc_url($href); ?>"
               target="_blank" rel="nofollow noopener"
               aria-label="<?php echo esc_attr(sprintf('WhatsApp öffnen – %s', $label)); ?>">
              <span class="ks-wa-icon" aria-hidden="true"></span>
              WhatsApp öffnen
            </a>
          </div>
        </section>
        <?php
        return ob_get_clean();
      }

      // ---------- LIST: (falls du die alte Listen-Ansicht brauchst) ----------
      if ($mode === 'list') { ?>
        <section class="ks-sec ks-wa-strip ks-wa-multi" aria-label="WhatsApp Kontakt je Standort">
          <div class="container ks-wa-grid">
            <p class="ks-wa-text">
              Fragen? <strong>Wähle deinen Standort & schreib uns auf WhatsApp.</strong>
            </p>
            <ul class="ks-wa-list" role="list">
              <?php foreach ($clean as $loc):
                $label    = $loc['label'];
                $phone    = $loc['phone'];
                $slug     = sanitize_title($label);
                $campaign = $a['campaign'] ? $a['campaign'].'_'.$slug : 'whatsapp_'.$slug;
                $href     = sprintf(
                  'https://wa.me/%s?text=%s&utm_source=website&utm_medium=cta&utm_campaign=%s',
                  $phone,
                  rawurlencode($a['text']),
                  rawurlencode($campaign)
                ); ?>
                <li class="ks-wa-item">
                  <a class="<?php echo esc_attr($a['btn_class']); ?>"
                     href="<?php echo esc_url($href); ?>"
                     target="_blank" rel="nofollow noopener"
                     aria-label="<?php echo esc_attr('WhatsApp öffnen – '.$label); ?>">
                    <span class="ks-wa-icon" aria-hidden="true"></span>
                    <?php echo esc_html($label); ?> – WhatsApp öffnen
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </section>
        <?php
        return ob_get_clean();
      }

      // ---------- DROPDOWN: Custom-Dropdown + Button ----------
      $uid = 'wa_'.wp_generate_uuid4();
      ?>
      <section class="ks-sec ks-wa-strip ks-wa-dropdown" aria-label="WhatsApp Kontakt je Standort">
        <div class="container ks-wa-inner">
          <p class="ks-wa-text">
            Fragen? <strong>Wähle deinen Standort & schreib uns auf WhatsApp.</strong>
          </p>

          <form id="<?php echo esc_attr($uid); ?>_form" class="ks-wa-form"  data-ks-wa="1"
  data-wa-text="<?php echo esc_attr($a['text']); ?>"
  data-wa-campaign="<?php echo esc_attr($a['campaign']); ?>" onsubmit="return false;">
            <!-- natives Select nur für Screenreader/Form-Sync, UNSICHTBAR -->
            <label for="<?php echo esc_attr($uid); ?>_select" class="screen-reader-only">Standort</label>
            <select id="<?php echo esc_attr($uid); ?>_select" name="wa_location" class="screen-reader-only" tabindex="-1" aria-hidden="true">
              <option value="" selected disabled><?php echo esc_html($a['placeholder'] ?? 'Standort wählen…'); ?></option>
              <?php foreach ($clean as $loc): ?>
                <option value="<?php echo esc_attr($loc['phone']); ?>" data-slug="<?php echo esc_attr(sanitize_title($loc['label'])); ?>">
                  <?php echo esc_html($loc['label']); ?>
                </option>
              <?php endforeach; ?>
            </select>

            <!-- sichtbares Custom-Dropdown (vereinheitlicht auf .ks-dd) -->
            <div class="ks-dd" id="<?php echo esc_attr($uid); ?>_dd"  aria-expanded="false" data-submit="0" data-max-rows="2">
              <button type="button" class="ks-dd__btn" aria-haspopup="listbox" aria-expanded="false" aria-controls="<?php echo esc_attr($uid); ?>_panel">
                <span class="ks-dd__label"><?php echo esc_html($a['placeholder'] ?? 'Standort wählen…'); ?></span>
                <span class="ks-dd__caret" aria-hidden="true">
                <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/offers/select-caret.svg' ); ?>" alt="">
                </span>
              </button>
              <div class="ks-dd__panel" id="<?php echo esc_attr($uid); ?>_panel" role="listbox" tabindex="-1" hidden>
                <?php foreach ($clean as $loc): ?>
                  <div class="ks-dd__option"
                       role="option"
                       tabindex="-1"
                       data-phone="<?php echo esc_attr($loc['phone']); ?>"
                       data-slug="<?php echo esc_attr(sanitize_title($loc['label'])); ?>"
                       data-label="<?php echo esc_attr($loc['label']); ?>">
                    <?php echo esc_html($loc['label']); ?>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>

            <button type="button" id="<?php echo esc_attr($uid); ?>_btn" class="<?php echo esc_attr($a['btn_class']); ?>" aria-disabled="true" disabled>
              <span class="ks-wa-ico" aria-hidden="true"><?php echo $wa_icon; ?></span>
              WhatsApp öffnen
            </button>
          </form>
        </div>
      </section>

  

      <?php

      return ob_get_clean();
    });
  }
  add_action('init', 'ks_register_whatsapp_locations_shortcode');
}





