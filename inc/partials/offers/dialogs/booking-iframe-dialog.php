















<?php
$theme_uri = get_stylesheet_directory_uri();
$dialog_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'dialog') : $fallback;
};

$dialog_icon_base = $theme_uri . '/assets/img/dialog/';
$close_icon = $dialog_icon_base . 'close.svg';
$back_icon = $dialog_icon_base . 'back.svg';
?>

<div id="ksBookModal" class="ks-book-modal" hidden>
  <div class="ks-book-modal__overlay" data-book-close></div>

  <div
    class="ks-book-modal__panel"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ksBookTitle"
    aria-label="<?php echo esc_attr($dialog_t('bookingDialog.label', 'Buchung')); ?>"
    data-i18n="bookingDialog.label"
    data-i18n-attr="aria-label"
  >
    <div class="ks-book-dialog__head">
      <button
        type="button"
        class="ks-book-modal__back"
        data-book-back
        aria-label="<?php echo esc_attr($dialog_t('bookingDialog.actions.back', 'Zurück')); ?>"
        title="<?php echo esc_attr($dialog_t('bookingDialog.actions.back', 'Zurück')); ?>"
      >
        <img src="<?php echo esc_url($back_icon); ?>" alt="" aria-hidden="true" width="24" height="24">
      </button>

      <div class="ks-book-dialog__head-main">
        <h3 id="ksBookTitle" class="ks-dir__m-title ks-book-modal__title"></h3>
        <p class="ks-dir__m-addr ks-book-modal__product"></p>
        <p class="ks-book-dialog__meta ks-book-modal__meta"></p>
      </div>

      <button
        type="button"
        class="ks-book-modal__close"
        data-book-close
        aria-label="<?php echo esc_attr($dialog_t('bookingDialog.actions.close', 'Schließen')); ?>"
        title="<?php echo esc_attr($dialog_t('bookingDialog.actions.close', 'Schließen')); ?>"
      >
        <img src="<?php echo esc_url($close_icon); ?>" alt="" aria-hidden="true" width="24" height="24">
      </button>
    </div>

    <iframe
      class="ks-book-modal__frame"
      src="about:blank"
      title="<?php echo esc_attr($dialog_t('bookingDialog.label', 'Buchung')); ?>"
      loading="eager"
      referrerpolicy="no-referrer-when-downgrade"
    ></iframe>
  </div>
</div>