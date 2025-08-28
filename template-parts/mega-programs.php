





<?php
$offers_url = ks_offers_url(); // nutzt den robusten Helper
?>

<div class="ks-programs__backdrop" aria-hidden="true"></div>

<div class="ks-programs__panel" role="menu" aria-label="Programs">
  <div class="ks-programs__row">
    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Holiday Programs</h4>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','Camp', $offers_url) ); ?>">Camps (Indoor/Outdoor)</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','AthleticTraining', $offers_url) ); ?>">Power Training</a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Weekly Courses</h4>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','Foerdertraining', $offers_url) ); ?>">Foerdertraining</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','Kindergarten', $offers_url) ); ?>">Soccer Kindergarten</a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Individual Courses</h4>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','PersonalTraining', $offers_url) ); ?>">Personal Training</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( add_query_arg('type','PersonalTraining', $offers_url) ); ?>">Personal Training Pro</a>
    </div>

    <div class="ks-programs__col">
      <h4 class="ks-programs__heading">Club Programs</h4>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( $offers_url ); ?>">Rent-a-Coach</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( $offers_url ); ?>">Training Camps</a>
      <a class="ks-programs__link" role="menuitem" href="<?php echo esc_url( $offers_url ); ?>">Coach Education</a>
    </div>
  </div>
</div>
