<?php

if (!function_exists('ks_shared_contact_t')) {
  function ks_shared_contact_t($key, $fallback) {
    return function_exists('ks_t') ? ks_t($key, $fallback, 'common') : $fallback;
  }
}

if (!function_exists('ks_enqueue_contact_section_assets')) {
  function ks_enqueue_contact_section_assets() {
    $theme_dir = get_stylesheet_directory();
    $theme_uri = get_stylesheet_directory_uri();
    $file = $theme_dir . '/assets/css/ks-contact-section.css';

    if (!file_exists($file) || wp_style_is('ks-contact-section', 'enqueued')) {
      return;
    }

    wp_enqueue_style(
      'ks-contact-section',
      $theme_uri . '/assets/css/ks-contact-section.css',
      ['kickstart-style', 'ks-utils'],
      filemtime($file)
    );
  }
}

if (!function_exists('ks_print_shared_contact_section')) {
  function ks_print_shared_contact_section($spacing_class = 'ks-py-32') {
    ks_enqueue_contact_section_assets();
    $theme_uri = get_stylesheet_directory_uri();

    ?>
    <section id="kontakt" class="ks-sec <?php echo esc_attr($spacing_class); ?> ks-contact-section ks-text-light">
      <div class="container container--1100">
        <div class="ks-contact-section__grid">
          <?php ks_print_shared_contact_content(); ?>
          <?php ks_print_shared_contact_panel($theme_uri); ?>
        </div>
      </div>
    </section>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_content')) {
  function ks_print_shared_contact_content() {
    ?>
    <div class="ks-contact-section__content">
      <div class="ks-kicker" data-i18n="common.contact.kicker">
        <?php echo esc_html(ks_shared_contact_t('common.contact.kicker', 'Kontakt')); ?>
      </div>

      <h2 class="ks-dir__title ks-dir__title--split ks-contact-section__title">
        <span class="ks-dir__title-line" data-i18n="common.contact.title">
          <?php echo esc_html(ks_shared_contact_t('common.contact.title', 'Hast du Fragen?')); ?>
        </span>
      </h2>

      <p class="ks-contact-section__text" data-i18n="common.contact.text">
        <?php echo esc_html(ks_shared_contact_t('common.contact.text', 'Bei Interesse kannst du uns jederzeit kontaktieren. Wir melden uns schnellstmöglich zurück.')); ?>
      </p>

      <?php ks_print_shared_contact_button(); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_button')) {
  function ks_print_shared_contact_button() {
    ?>
    <a class="ks-btn ks-btn--dark ks-contact-section__button" href="mailto:fussballschule@selcuk-kocyigit.de" data-i18n="common.contact.button">
      <?php echo esc_html(ks_shared_contact_t('common.contact.button', 'Nachricht schreiben')); ?>
    </a>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_panel')) {
  function ks_print_shared_contact_panel($theme_uri) {
    ?>
    <div class="ks-contact-section__panel">
      <?php foreach (ks_get_shared_contact_rows($theme_uri) as $row): ?>
        <?php ks_print_shared_contact_row($row); ?>
      <?php endforeach; ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_get_shared_contact_rows')) {
  function ks_get_shared_contact_rows($theme_uri) {
    return [
      ks_get_shared_contact_row('tel:+4917643203362', 'phone.png', 'common.contact.phoneTitle', 'Ruf uns an:', '+49 (176) 43 20 33 62', 'common.contact.phoneAria', 'Anrufen', $theme_uri),
      ks_get_shared_contact_row('mailto:fussballschule@selcuk-kocyigit.de', 'mail.png', 'common.contact.mailTitle', 'Schreib uns:', 'fussballschule@selcuk-kocyigit.de', 'common.contact.mailAria', 'E-Mail schreiben', $theme_uri),
      ks_get_shared_contact_row('', 'clock.png', 'common.contact.hoursTitle', 'Telefonzeiten:', 'Mo.–Fr. 09:00–20:00 Uhr', 'common.contact.hoursAria', 'Telefonzeiten ansehen', $theme_uri),
    ];
  }
}

if (!function_exists('ks_get_shared_contact_row')) {
  function ks_get_shared_contact_row($url, $icon, $title_key, $title, $text, $label_key, $label, $theme_uri) {
    return [
      'url' => $url,
      'icon' => $theme_uri . '/assets/img/offers/' . $icon,
      'title_key' => $title_key,
      'title' => $title,
      'text' => $text,
      'label_key' => $label_key,
      'label' => $label,
    ];
  }
}

if (!function_exists('ks_print_shared_contact_row')) {
  function ks_print_shared_contact_row($row) {
    ?>
    <div class="ks-contact-section__row">
      <?php ks_print_shared_contact_icon($row); ?>
      <?php ks_print_shared_contact_row_body($row); ?>
    </div>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_icon')) {
  function ks_print_shared_contact_icon($row) {
    ?>
    <span class="ks-contact-section__icon" aria-hidden="true">
      <img src="<?php echo esc_url($row['icon']); ?>" alt="" loading="lazy" decoding="async">
    </span>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_row_body')) {
  function ks_print_shared_contact_row_body($row) {
    ?>
    <span class="ks-contact-section__body">
      <strong class="ks-contact-section__label" data-i18n="<?php echo esc_attr($row['title_key']); ?>">
        <?php echo esc_html(ks_shared_contact_t($row['title_key'], $row['title'])); ?>
      </strong>

      <?php ks_print_shared_contact_value($row); ?>
    </span>
    <?php
  }
}

if (!function_exists('ks_print_shared_contact_value')) {
  function ks_print_shared_contact_value($row) {
    if (empty($row['url'])) {
      echo '<span class="ks-contact-section__value">' . esc_html($row['text']) . '</span>';
      return;
    }

    ?>
    <a
      class="ks-contact-section__value"
      href="<?php echo esc_url($row['url']); ?>"
      aria-label="<?php echo esc_attr(ks_shared_contact_t($row['label_key'], $row['label'])); ?>"
      data-i18n="<?php echo esc_attr($row['label_key']); ?>"
      data-i18n-attr="aria-label"
    >
      <?php echo esc_html($row['text']); ?>
    </a>
    <?php
  }
}