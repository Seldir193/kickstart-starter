<?php
$offers_url = ks_offers_url();

function ks_offers_link_with($base, $params) {
  return esc_url(add_query_arg($params, $base));
}
?>

<div class="ks-programs__backdrop" aria-hidden="true"></div>

<div
  class="ks-programs__panel"
  role="menu"
  aria-label="Programs"
  data-i18n-attr="aria-label"
  data-i18n="mega.label"
>
  <div class="ks-programs__row">
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading" data-i18n="mega.holiday.heading">
        Holiday Programs
      </h4>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.holiday.camps"
        href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Camp']); ?>"
      >
        Camps (Indoor/Outdoor)
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.holiday.power"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Holiday', 'sub_type' => 'Powertraining']); ?>"
      >
        Power Training
      </a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading" data-i18n="mega.weekly.heading">
        Weekly Courses
      </h4>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.weekly.development"
        href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Foerdertraining']); ?>"
      >
        Foerdertraining
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.weekly.kindergarten"
        href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Kindergarten']); ?>"
      >
        Soccer Kindergarten
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.weekly.goalkeeper"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Torwarttraining']); ?>"
      >
        Goalkeeper Training
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.weekly.athletik"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Foerdertraining_Athletik']); ?>"
      >
        Development Training · Athletik
      </a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading" data-i18n="mega.individual.heading">
        Individual Courses
      </h4>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.individual.basic"
        href="<?php echo ks_offers_link_with($offers_url, ['type' => 'PersonalTraining']); ?>"
      >
        1:1 Training
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.individual.athletik"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Athletik']); ?>"
      >
        1:1 Training Athletik
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.individual.goalkeeper"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Torwart']); ?>"
      >
        1:1 Training Torwart
      </a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading" data-i18n="mega.club.heading">
        Club Programs
      </h4>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.club.rent"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'RentACoach', 'sub_type' => 'RentACoach_Generic']); ?>"
      >
        Rent-a-Coach
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.club.camps"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'ClubProgram_Generic']); ?>"
      >
        Training Camps
      </a>

      <a
        class="ks-programs__link"
        role="menuitem"
        data-i18n="mega.club.education"
        href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'CoachEducation']); ?>"
      >
        Coach Education
      </a>
    </div>
  </div>
</div>


















   <?php
/* $offers_url = ks_offers_url(); 

function ks_offers_link_with($base, $params) {
 
  return esc_url( add_query_arg( $params, $base ) );
} */
?>  

<!-- <div class="ks-programs__backdrop" aria-hidden="true"></div>

<div class="ks-programs__panel" role="menu" aria-label="Programs">
  <div class="ks-programs__row">
    
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Holiday Programs</h4>
      







      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Camp']); ?>">
         Camps (Indoor/Outdoor)
      </a>
     
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Holiday', 'sub_type' => 'Powertraining']); ?>">
         Power Training
      </a>
    </div>

    
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Weekly Courses</h4>
      
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Foerdertraining']); ?>">
         Foerdertraining
      </a>
      <!
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'Kindergarten']); ?>">
         Soccer Kindergarten
      </a>
    
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Torwarttraining']); ?>">
         Goalkeeper Training
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Weekly', 'sub_type' => 'Foerdertraining_Athletik']); ?>">
         Development Training · Athletik
      </a>
    </div>

   
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Individual Courses</h4>
      
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['type' => 'PersonalTraining']); ?>">
         1:1 Training
      </a>
     
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Athletik']); ?>">
         1:1 Training Athletik
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'Individual', 'sub_type' => 'Einzeltraining_Torwart']); ?>">
         1:1 Training Torwart
      </a>
    </div>

    
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Club Programs</h4>
      
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'RentACoach', 'sub_type' => 'RentACoach_Generic']); ?>">
         Rent-a-Coach
      </a>
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'ClubProgram_Generic']); ?>">
         Training Camps
      </a>
      
      <a class="ks-programs__link" role="menuitem"
         href="<?php echo ks_offers_link_with($offers_url, ['category' => 'ClubPrograms', 'sub_type' => 'CoachEducation']); ?>">
         Coach Education
      </a>










    </div>
  </div>
</div>
 -->

 

































