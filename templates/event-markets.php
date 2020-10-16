<?php
foreach($event->markets as $market_name => $market) {
  $id = sanitize_title($market_name);
  echo "<a id=\"{$id}\" name=\"{$id}\"></a>";
?>
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
