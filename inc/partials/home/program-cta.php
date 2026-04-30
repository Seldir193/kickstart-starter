<?php
defined('ABSPATH') || exit;

$programs = [
  'Foerdertraining' => 'Fördertraining',
  'PersonalTraining' => 'Einzeltraining',
  'Camp' => 'Fußballcamp',
  'Kindergarten' => 'Kindergarten',
];
?>

<section
  id="program-cta"
  class="ks-sec ks-py-56 ks-cta-strip"
  aria-label="Programm schnell buchen"
  data-i18n-attr="aria-label"
  data-i18n="programCta.ariaLabel"
>
  <div class="container ks-cta-grid">
    <div class="ks-cta-text">
      <div class="ks-title-wrap" data-bgword="BUCHEN" data-i18n="programCta.watermark"
  data-i18n-attr="data-bgword">
        <div class="ks-kicker" data-i18n="programCta.kicker">
          Schnell buchen
        </div>

        <h2 class="ks-dir__title" data-i18n="programCta.title">
          Dein Programm auswählen
        </h2>
      </div>

      <p data-i18n="programCta.lead">
        Wähle ein Angebot und starte direkt mit der Anmeldung.
      </p>
    </div>

    <form
      id="ksProgramForm"
      class="ks-cta-form"
      action="<?php echo esc_url($offers); ?>"
      method="get"
    >
      <label
        class="screen-reader-text"
        for="ksProgramSelect"
        data-i18n="programCta.selectLabel"
      >
        Programm
      </label>

      <select id="ksProgramSelect" name="type" class="ks-select" hidden>
        <option value="" selected disabled data-i18n="programCta.placeholder">
          Bitte auswählen …
        </option>

        <?php foreach ($programs as $value => $label): ?>
          <option value="<?php echo esc_attr($value); ?>">
            <?php echo esc_html($label); ?>
          </option>
        <?php endforeach; ?>
      </select>

      <div
        class="ks-dd"
        id="ks-dd-program"
        data-select="#ksProgramSelect"
        data-max-rows="2"
        data-submit="1"
        aria-expanded="false"
      >
        <button
          type="button"
          class="ks-dd__btn"
          aria-haspopup="listbox"
          aria-expanded="false"
        >
          <span class="ks-dd__label" data-i18n="programCta.placeholder">
            Bitte auswählen …
          </span>

          <span class="ks-dd__caret" aria-hidden="true">
            <img
              src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/offers/select-caret.svg'); ?>"
              alt=""
            >
          </span>
        </button>

        <div class="ks-dd__panel" role="listbox" tabindex="-1"></div> 
         <!-- <div class="ks-dd__panel" role="listbox" tabindex="-1">
  <div class="ks-dd__inner"></div>
</div> -->
      </div>

     <div class="ks-cta-note">
 <span class="ks-cta-note__icon" aria-hidden="true">
  <img
    src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/home/info-circle.svg'); ?>"
    alt=""
    loading="lazy"
  >
</span>

  <span class="ks-cta-note__text" data-i18n="programCta.hint">
    Du kannst dein Programm später jederzeit ändern.
  </span>
</div>
    </form>
  </div>
</section>






