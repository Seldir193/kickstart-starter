<?php
// inc/partials/home/program-cta.php
defined('ABSPATH') || exit;
?>

<section id="program-cta" class="ks-sec ks-py-56 ks-cta-strip" aria-label="Programm schnell buchen">
  <div class="container ks-cta-grid">
   

       <div class="ks-cta-text">
       <div class="ks-title-wrap" data-bgword="BUCHEN">
    <div class="ks-kicker">Schnell buchen</div>
    <h2 class="ks-dir__title">Dein Programm auswählen</h2>
  </div>
      <p>Wähle ein Angebot und starte direkt mit der Anmeldung.</p>
              </div>

      <?php
        $programs = [
          'Foerdertraining'  => 'Fördertraining',
          'PersonalTraining' => 'Einzeltraining',
          'Camp'             => 'Fußballcamp',
          'Kindergarten'     => 'Kindergarten',
        ];
      ?>
     <form id="ksProgramForm" class="ks-cta-form" action="<?php echo esc_url($offers); ?>" method="get">
  <!-- natives Select bleibt fürs Submit, wird vollständig versteckt -->


<label class="screen-reader-text" for="ksProgramSelect">Programm</label>
<select id="ksProgramSelect" name="type" class="ks-select" hidden>
  <option value="" selected disabled>Bitte auswählen …</option>
  <?php foreach ($programs as $val => $label): ?>
    <option value="<?php echo esc_attr($val); ?>"><?php echo esc_html($label); ?></option>
  <?php endforeach; ?>
</select>



  <!-- einzig sichtbares Dropdown (Custom UI wie beim Trainer) -->
  <div class="ks-dd" id="ks-dd-program" data-select="#ksProgramSelect"
  data-max-rows="2"
  data-submit="1" aria-expanded="false">
    <button type="button" class="ks-dd__btn" aria-haspopup="listbox" aria-expanded="false">
      <span class="ks-dd__label">Bitte wählen…</span>
      <span class="ks-dd__caret" aria-hidden="true">
  
   <img src="<?php echo esc_url( get_stylesheet_directory_uri() . '/assets/img/offers/select-caret.svg' ); ?>" alt="">
</span>
    </button>
    <div class="ks-dd__panel" role="listbox" tabindex="-1"></div>
  </div>
</form>

    </div>
  </div>
</section>
