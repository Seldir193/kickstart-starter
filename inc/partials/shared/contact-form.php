<?php
/**
 * Shared Partial: Kontakt (Map optional + Form)
 * Pfad MUSS bleiben:
 * inc/partials/shared/contact-form.php
 *
 * Erwartet optional:
 * - get_template_part(..., ..., ['ks_contact' => [ ... ]])
 * oder eine Variable $ks_contact im Scope.
 *
 * Wichtig: KEIN Watermark (wird hier nicht ausgegeben).
 */

$incoming = [];

// 1) Bevorzugt: args aus get_template_part(..., $args)
if (isset($args) && is_array($args) && isset($args['ks_contact']) && is_array($args['ks_contact'])) {
  $incoming = $args['ks_contact'];
}

// 2) Fallback: $ks_contact aus Scope
if (isset($ks_contact) && is_array($ks_contact)) {
  $incoming = array_merge($incoming, $ks_contact);
}

$defaults = [
  'show_map' => false,
  'map_url'  => '',

  'kicker'   => 'KONTAKT',
  'title'    => 'Hast Du Fragen?',

  'brand'    => 'Dortmunder Fussball Schule',
  'subtitle' => 'Unser Office-Team ist täglich von 09:00 – 12:00 Uhr für Dich da und beantwortet gerne alle Deine Fragen.',

  'address_line1' => 'Hochfelder Straße 33',
  'address_line2' => '47226 Duisburg',
  'phone'         => '0176 43203362',
  'email'         => 'fussballschule@selcuk-kocyigit.de',
];

$data = array_merge($defaults, $incoming);

$theme_uri = get_stylesheet_directory_uri();

// Icons
$icon_mail  = $theme_uri . '/assets/img/offers/mail.png';
$icon_phone = $theme_uri . '/assets/img/offers/phone.png';
$icon_loc   = $theme_uri . '/assets/img/offers/location.png';

// Links
$tel_href  = 'tel:' . preg_replace('/\s+/', '', (string) $data['phone']);
$mail_href = 'mailto:' . sanitize_email((string) $data['email']);

$maps_query = rawurlencode(trim((string)$data['address_line1'] . ', ' . (string)$data['address_line2']));
$maps_href  = 'https://www.google.com/maps?q=' . $maps_query;

// Section classes
$section_classes = 'ks-sec ks-py-56';
if (!empty($data['show_map'])) {
  $section_classes .= ' ks-contact--has-map';
}
?>

<section id="kontakt" class="<?php echo esc_attr($section_classes); ?>">

  <?php if (!empty($data['show_map'])): ?>
    <div class="ks-contact-mapfull" aria-label="Karte">
      <?php if (!empty($data['map_url'])): ?>
        <iframe
          class="ks-contact-mapfull__iframe"
          src="<?php echo esc_url($data['map_url']); ?>"
          loading="lazy"
          referrerpolicy="no-referrer-when-downgrade"
          allowfullscreen
          title="Kontakt – Karte"></iframe>
      <?php else: ?>
        <div class="ks-contact-mapfull__fallback">
          Bitte Google Maps Embed URL setzen.
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="container">

    <div class="ks-title-wrap">
      <div class="ks-kicker"><?php echo esc_html($data['kicker']); ?></div>
      <h2 class="ks-dir__title"><?php echo esc_html($data['title']); ?></h2>

      <p class="ks-text-center ks-mb-16 ks-contact-lead">
        <strong><?php echo esc_html($data['brand']); ?></strong><br>
        <?php echo esc_html($data['subtitle']); ?>
      </p>
    </div>

    <!-- ✅ Fallback (nur wenn JS aus ist): Redirect zeigt diese Box -->
    <?php if (isset($_GET['sent']) && $_GET['sent'] === '1') : ?>
      <div class="ok" data-auto-hide="1">Vielen Dank! Ihre Nachricht wurde gesendet.</div>
    <?php endif; ?>

    <div class="ks-contact-grid">

      <!-- LEFT -->
      <div class="ks-contact-infoBox">
        <div class="box">
          <div class="ks-contact-infoHead">
            <h3 class="ks-contact-infoTitle"><?php echo esc_html($data['brand']); ?></h3>
          </div>

          <div class="ks-contact-infoRows">
            <a class="ks-contact-infoRow" href="<?php echo esc_url($maps_href); ?>" target="_blank" rel="noopener">
              <span class="ks-contact-ic" style="--icon:url('<?php echo esc_url($icon_loc); ?>')" aria-hidden="true"></span>
              <span class="ks-contact-infoText">
                <strong>Adresse</strong>
                <span><?php echo esc_html($data['address_line1']); ?></span>
                <span><?php echo esc_html($data['address_line2']); ?></span>
              </span>
            </a>

            <a class="ks-contact-infoRow" href="<?php echo esc_url($tel_href); ?>">
              <span class="ks-contact-ic" style="--icon:url('<?php echo esc_url($icon_phone); ?>')" aria-hidden="true"></span>
              <span class="ks-contact-infoText">
                <strong>Telefon</strong>
                <span><?php echo esc_html($data['phone']); ?></span>
              </span>
            </a>

            <a class="ks-contact-infoRow" href="<?php echo esc_url($mail_href); ?>">
              <span class="ks-contact-ic" style="--icon:url('<?php echo esc_url($icon_mail); ?>')" aria-hidden="true"></span>
              <span class="ks-contact-infoText">
                <strong>E-Mail</strong>
                <span><?php echo esc_html($data['email']); ?></span>
              </span>
            </a>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="ks-contact-formCard">
        <form
          action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
          method="post"
          class="form"
          id="ks-contact-form"
          novalidate
          data-ks-contact-form="1"
          data-ajax-url="<?php echo esc_url(admin_url('admin-ajax.php')); ?>"
          data-ajax-action="ks_contact_send">

          <!-- ✅ Fallback für No-JS (admin-post) -->
          <input type="hidden" name="action" value="send_contact_form">
          <?php wp_nonce_field('contact_form_nonce', 'contact_form_nonce_field'); ?>

          <div class="field">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required autocomplete="name">
            <div class="ks-field-error" aria-live="polite"></div>
          </div>

          <div class="field">
            <label for="email">E-Mail *</label>
            <input type="email" id="email" name="email" required autocomplete="email">
            <div class="ks-field-error" aria-live="polite"></div>
          </div>

          <div class="field">
            <label for="message">Nachricht *</label>
            <textarea id="message" name="message" rows="6" required></textarea>
            <div class="ks-field-error" aria-live="polite"></div>
          </div>

          <!-- ✅ Profi-Meldung: IM Formular, direkt über dem Button -->
          <div id="ks-form-alert-success" class="ok" hidden aria-live="polite"></div>
          <div id="ks-form-alert-error" class="ks-field-error" hidden aria-live="polite"></div>

          <button type="submit" class="ks-btn">Nachricht senden</button>
        </form>
      </div>

    </div>
  </div>
</section>
