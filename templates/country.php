<?php
/**
 * Display a country
 */

// We need a $country variable
if(!isset($country, $atts)) {
  return;
}
?>
<div class="bbfootball-country-holder">
  <?php
    if(isset($country->leagues)) {
      foreach($country->leagues as $league) {
          bbfootball_template('league.php', array(
              'league' => $league,
              'atts' => $atts
          ));
      }
  }
  ?>
</div>
<?php
