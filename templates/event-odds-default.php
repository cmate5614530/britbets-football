<?php
/**
 * Display odds on large screens
 */

// We need an $market variable
if(!isset($market)) {
  return;
}

if(isset($market_name)) { ?>
<h3><?php echo $market_name; ?></h3>
<table class="">
  <thead>
    <th></th>
    <?php foreach($market->bookies as $bookie) { ?>
    <th><?php echo bbfootball_bookie($bookie); ?></th>
    <?php } ?>
  </thead>
  <tbody>
    <?php foreach($market->odds as $outcome => $outcome_odds) { ?>
    <tr>
      <th><?php echo $outcome; ?></th>
      <?php foreach($market->bookies as $bookie) { ?>
      <th>
        <?php
        foreach($outcome_odds as $odds) {
          if($odds->bookie == $bookie) {
            echo bbfootball_bookie($bookie, array(
              'text' => $odds->decimal_odds,
              'show_image' => false
            ));
          }
        }
        ?>
      </th>
      <?php } ?>
    </tr>
    <?php } ?>
  </tbody>
</table>
<?php
}
