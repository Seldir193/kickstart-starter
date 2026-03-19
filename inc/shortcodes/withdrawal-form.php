<?php
"use strict";

function ks_withdrawal_enqueue_assets() {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();

  $css = $dir . '/assets/css/withdrawal.css';
  $js = $dir . '/assets/js/withdrawal.js';

  if (file_exists($css)) {
    wp_enqueue_style(
      'ks-withdrawal',
      $uri . '/assets/css/withdrawal.css',
      [],
      filemtime($css)
    );
  }

  if (file_exists($js)) {
    wp_enqueue_script(
      'ks-withdrawal',
      $uri . '/assets/js/withdrawal.js',
      [],
      filemtime($js),
      true
    );

    wp_localize_script(
      'ks-withdrawal',
      'ksWithdrawal',
      [
        'endpoint' => 'http://localhost:5000/api/payments/stripe/revoke-request',
        'labels' => [
          'loading' => 'Widerruf wird verarbeitet ...',
          'success' => 'Der Widerruf wurde erfolgreich verarbeitet.',
          'error' => 'Der Widerruf konnte nicht verarbeitet werden.',
        ],
      ]
    );
  }
}

function ks_withdrawal_form_shortcode() {
  ks_withdrawal_enqueue_assets();

  ob_start();
  ?>
  <section class="withdrawal">
    <div class="withdrawal__card">
      <div class="withdrawal__badge">Service</div>

      <h2 class="withdrawal__title">Vertrag widerrufen</h2>

      <p class="withdrawal__intro">
        Gib bitte die Daten des Kindes und die E-Mail-Adresse des Elternteils ein,
        damit wir die passende Buchung finden und deinen Widerruf innerhalb der
        gesetzlichen Frist direkt verarbeiten können.
      </p>

      <form class="withdrawal__form" data-withdrawal-form>
        <div class="withdrawal__grid">
          <div class="withdrawal__field">
            <label class="withdrawal__label" for="wd-parent-email">
              E-Mail des Elternteils
            </label>
            <input
              class="withdrawal__input"
              id="wd-parent-email"
              name="parentEmail"
              type="email"
              required
              autocomplete="email"
            />
          </div>

          <div class="withdrawal__field">
  <label class="withdrawal__label" for="wd-reference-no">
    Rechnungsnummer / Buchungsnummer
  </label>
  <input
    class="withdrawal__input"
    id="wd-reference-no"
    name="referenceNo"
    type="text"
    required
    placeholder="z. B. CA-26-0076 oder KS-AD2F2C"
  />
  <p class="withdrawal__hint">
    Die Referenz findest du in deiner Rechnung oder Teilnahmebestätigung.
  </p>
</div>

          <div class="withdrawal__field">
            <label class="withdrawal__label" for="wd-child-first-name">
              Vorname des Kindes
            </label>
            <input
              class="withdrawal__input"
              id="wd-child-first-name"
              name="childFirstName"
              type="text"
              required
            />
          </div>

          <div class="withdrawal__field">
            <label class="withdrawal__label" for="wd-child-last-name">
              Nachname des Kindes
            </label>
            <input
              class="withdrawal__input"
              id="wd-child-last-name"
              name="childLastName"
              type="text"
              required
            />
          </div>

          <div class="withdrawal__field">
            <label class="withdrawal__label" for="wd-child-birth-date">
              Geburtsdatum des Kindes
            </label>
            <input
              class="withdrawal__input"
              id="wd-child-birth-date"
              name="childBirthDate"
              type="date"
              required
            />
          </div>
        </div>

        <div class="withdrawal__actions">
          <button class="withdrawal__button" type="submit">
            Vertrag widerrufen
          </button>
        </div>

        <div
          class="withdrawal__result withdrawal__result--hidden"
          data-withdrawal-result
        ></div>
      </form>
    </div>
  </section>
  <?php
  return ob_get_clean();
}

add_shortcode(
  'withdrawal_form',
  'ks_withdrawal_form_shortcode'
);