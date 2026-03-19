<?php
"use strict";

function ks_membership_cancellation_enqueue_assets() {
  $dir = get_stylesheet_directory();
  $uri = get_stylesheet_directory_uri();

  $css = $dir . '/assets/css/membership-cancellation.css';
  $js = $dir . '/assets/js/membership-cancellation.js';

  if (file_exists($css)) {
    wp_enqueue_style(
      'ks-membership-cancellation',
      $uri . '/assets/css/membership-cancellation.css',
      [],
      filemtime($css)
    );
  }

  if (file_exists($js)) {
    wp_enqueue_script(
      'ks-membership-cancellation',
      $uri . '/assets/js/membership-cancellation.js',
      [],
      filemtime($js),
      true
    );

    wp_localize_script(
      'ks-membership-cancellation',
      'ksMembershipCancellation',
      [
        'endpoint' => 'http://localhost:5000/api/payments/stripe/cancel-subscription-request',
        'labels' => [
          'loading' => 'Kündigung wird verarbeitet ...',
          'success' => 'Kündigung erfolgreich vorgemerkt.',
          'error' => 'Die Kündigung konnte nicht verarbeitet werden.',
        ],
      ]
    );
  }
}

function ks_membership_cancellation_form_shortcode() {
  ks_membership_cancellation_enqueue_assets();

  ob_start();
  ?>
  <section class="membership-cancellation">
    <div class="membership-cancellation__card">
      <div class="membership-cancellation__badge">Service</div>

      <h2 class="membership-cancellation__title">Mitgliedschaft kündigen</h2>

      <p class="membership-cancellation__intro">
        Gib bitte die Daten des Kindes und die E-Mail-Adresse des Elternteils ein,
        damit wir die passende Mitgliedschaft finden und deine Kündigung direkt verarbeiten können.
      </p>

      <form class="membership-cancellation__form" data-membership-cancellation-form>
        <div class="membership-cancellation__grid">
          <div class="membership-cancellation__field">
            <label class="membership-cancellation__label" for="mc-parent-email">
              E-Mail des Elternteils
            </label>
            <input
              class="membership-cancellation__input"
              id="mc-parent-email"
              name="parentEmail"
              type="email"
              required
              autocomplete="email"
            />
          </div>

          <div class="membership-cancellation__field">
  <label class="membership-cancellation__label" for="mc-reference-no">
    Rechnungsnummer / Buchungsnummer
  </label>
  <input
    class="membership-cancellation__input"
    id="mc-reference-no"
    name="referenceNo"
    type="text"
    required
    placeholder="z. B. FO-26-0241 oder KS-1916BB"
  />
  <p class="membership-cancellation__hint">
    Die Referenz findest du in deiner Rechnung oder Teilnahmebestätigung.
  </p>
</div>

          <div class="membership-cancellation__field">
            <label class="membership-cancellation__label" for="mc-child-first-name">
              Vorname des Kindes
            </label>
            <input
              class="membership-cancellation__input"
              id="mc-child-first-name"
              name="childFirstName"
              type="text"
              required
            />
          </div>

          <div class="membership-cancellation__field">
            <label class="membership-cancellation__label" for="mc-child-last-name">
              Nachname des Kindes
            </label>
            <input
              class="membership-cancellation__input"
              id="mc-child-last-name"
              name="childLastName"
              type="text"
              required
            />
          </div>

          <div class="membership-cancellation__field">
            <label class="membership-cancellation__label" for="mc-child-birth-date">
              Geburtsdatum des Kindes
            </label>
            <input
              class="membership-cancellation__input"
              id="mc-child-birth-date"
              name="childBirthDate"
              type="date"
              required
            />
          </div>
        </div>

        <fieldset class="membership-cancellation__choices">
          <legend class="membership-cancellation__legend">Kündigungswunsch</legend>

          <label class="membership-cancellation__choice">
            <input
              type="radio"
              name="terminationMode"
              value="earliest"
              checked
            />
            <span>Zum nächstmöglichen Termin</span>
          </label>

          <label class="membership-cancellation__choice">
            <input
              type="radio"
              name="terminationMode"
              value="requested"
            />
            <span>Zu einem späteren Monatsende</span>
          </label>
        </fieldset>

        <div
          class="membership-cancellation__field membership-cancellation__field--hidden"
          data-requested-end-date-wrap
        >
          <label class="membership-cancellation__label" for="mc-requested-end-date">
            Gewünschtes Kündigungsdatum
          </label>
          <input
            class="membership-cancellation__input"
            id="mc-requested-end-date"
            name="requestedEndDate"
            type="date"
          />
          <p class="membership-cancellation__hint">
            Es sind nur zulässige Monatsenden möglich.
          </p>
        </div>

        <div class="membership-cancellation__actions">
          <button class="membership-cancellation__button" type="submit">
            Mitgliedschaft kündigen
          </button>
        </div>

        <div
          class="membership-cancellation__result membership-cancellation__result--hidden"
          data-membership-cancellation-result
        ></div>
      </form>
    </div>
  </section>
  <?php
  return ob_get_clean();
}

add_shortcode(
  'membership_cancellation_form',
  'ks_membership_cancellation_form_shortcode'
);