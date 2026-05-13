<?php
defined('ABSPATH') || exit;

$program_cta_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'program-cta') : $fallback;
};

$programs = [
  'Foerdertraining' => $program_cta_t('programCta.programs.Foerdertraining', 'Fördertraining'),
  'PersonalTraining' => $program_cta_t('programCta.programs.PersonalTraining', 'Einzeltraining'),
  'Camp' => $program_cta_t('programCta.programs.Camp', 'Fußballcamp'),
  'Kindergarten' => $program_cta_t('programCta.programs.Kindergarten', 'Kindergarten'),
];
?>

<section
  id="program-cta"
  class="ks-sec ks-py-56 ks-cta-strip"
  aria-label="<?php echo esc_attr($program_cta_t('programCta.ariaLabel', 'Programm schnell buchen')); ?>"
  data-i18n-attr="aria-label"
  data-i18n="programCta.ariaLabel"
>
  <div class="container ks-cta-grid">
    <div class="ks-cta-text">
      <div
        class="ks-title-wrap ks-watermark ks-watermark--center ks-watermark--cta"
        data-bgword="<?php echo esc_attr($program_cta_t('programCta.watermark', 'BUCHEN')); ?>"
        data-i18n="programCta.watermark"
        data-i18n-attr="data-bgword"
      >
        <div class="ks-kicker" data-i18n="programCta.kicker">
          <?php echo esc_html($program_cta_t('programCta.kicker', 'Schnell buchen')); ?>
        </div>

        <h2 class="ks-dir__title" data-i18n="programCta.title">
          <?php echo esc_html($program_cta_t('programCta.title', 'Dein Programm auswählen')); ?>
        </h2>
      </div>

      <p data-i18n="programCta.lead">
        <?php echo esc_html($program_cta_t('programCta.lead', 'Wähle ein Angebot und starte direkt mit der Anmeldung.')); ?>
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
        <?php echo esc_html($program_cta_t('programCta.selectLabel', 'Programm')); ?>
      </label>

      <select id="ksProgramSelect" name="type" class="ks-select" hidden>
        <option value="" selected disabled data-i18n="programCta.placeholder">
          <?php echo esc_html($program_cta_t('programCta.placeholder', 'Bitte auswählen …')); ?>
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
            <?php echo esc_html($program_cta_t('programCta.placeholder', 'Bitte auswählen …')); ?>
          </span>

          <span class="ks-dd__caret" aria-hidden="true">
            <img
              src="<?php echo esc_url(get_stylesheet_directory_uri() . '/assets/img/offers/select-caret.svg'); ?>"
              alt=""
            >
          </span>
        </button>

        <div class="ks-dd__panel" role="listbox" tabindex="-1"></div>
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
          <?php echo esc_html($program_cta_t('programCta.hint', 'Du kannst dein Programm später jederzeit ändern.')); ?>
        </span>
      </div>
    </form>
  </div>
</section>



