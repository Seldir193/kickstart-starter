<?php
$dialog_t = function ($key, $fallback) {
  return function_exists('ks_t') ? ks_t($key, $fallback, 'dialog') : $fallback;
};
?>

<div id="ksOfferModal" class="ks-offer-modal" hidden>
  <div class="ks-offer-modal__overlay" data-offer-close></div>

  <div
    class="ks-offer-modal__panel"
    role="dialog"
    aria-modal="true"
    aria-labelledby="ksOfferTitle"
  >
    <span class="ks-sr-only" data-i18n="offersDialog.actions.close">
      <?php echo esc_html($dialog_t('offersDialog.actions.close', 'Schließen')); ?>
    </span>
  </div>
</div>