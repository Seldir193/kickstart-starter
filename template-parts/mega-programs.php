

<?php
$offers_url = ks_offers_url(); // nutzt deinen Helper

function ks_offers_link_with($base, $params) {
  // kleine Helper-Funktion für sauberes add_query_arg
  return esc_url( add_query_arg( $params, $base ) );
}
?>

<div class="ks-programs__backdrop" aria-hidden="true"></div>

<div class="ks-programs__panel" role="menu" aria-label="Programs">
  <div class="ks-programs__row">
    <!-- Holiday Programs -->
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Holiday Programs</h4>
      <!-- Bestehend über type (kompatibel) -->







      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Camp']); ?>">
         Camps (Indoor/Outdoor)
      </a>
      <!-- NEU: Powertraining über category/sub_type -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Holiday', 'sub_type' => 'Powertraining']); ?>">
         Power Training
      </a>
    </div>

    <!-- Weekly Courses -->
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Weekly Courses</h4>
      <!-- Bestehend: Fördertraining über type (läuft weiter) -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Foerdertraining']); ?>">
         Foerdertraining
      </a>
      <!-- Kindergarten über type (bestehend) -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Kindergarten']); ?>">
         Soccer Kindergarten
      </a>
      <!-- NEU: Torwart + Foerdertraining_Athletik über category/sub_type -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Torwarttraining']); ?>">
         Goalkeeper Training
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Foerdertraining_Athletik']); ?>">
         Development Training · Athletik
      </a>
    </div>

    <!-- Individual Courses -->
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Individual Courses</h4>
      <!-- Bestehend: PersonalTraining (bleibt) -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'PersonalTraining']); ?>">
         1:1 Training
      </a>
      <!-- NEU: Einzeltraining-Athletik / Torwart über category/sub_type -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Athletik']); ?>">
         1:1 Training Athletik
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Torwart']); ?>">
         1:1 Training Torwart
      </a>
    </div>

    <!-- Club Programs -->
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Club Programs</h4>
      <!-- NEU: echte Filter statt nackte Base-URL -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'RentACoach', 'sub_type' => 'RentACoach_Generic']); ?>">
         Rent-a-Coach
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'ClubProgram_Generic']); ?>">
         Training Camps
      </a>
      <!-- Kann später eigenes sub_type bekommen; vorerst Base-URL belassen oder z.B.: -->
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'CoachEducation']); ?>">
         Coach Education
      </a>










    </div>
  </div>
</div>




































