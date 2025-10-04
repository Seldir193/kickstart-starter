<?php
$about_url = ks_about_url();
?>
<div class="ks-programs__backdrop" aria-hidden="true"></div>
<div class="ks-programs__panel" role="menu" aria-label="Über uns">
  <div class="ks-programs__row">
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Über uns</h4>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#wer-wir-sind'); ?>">Wer wir sind</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#mfs'); ?>">Die Dortmunder Fussball Schule</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#team'); ?>">Unser Team</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#philosophie'); ?>">Unsere Philosophie</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#kontakt'); ?>">Hast du Fragen?</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#ziele'); ?>">Unsere Ziele</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url($about_url . '#standorte'); ?>">Unsere Standorte</a>
    </div>
  </div>
</div>
