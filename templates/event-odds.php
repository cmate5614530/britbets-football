<?php
/**
 * Display odds on small screens
 */

// We need an $event variable
if(!isset($odds)) {
  return;
}

?>
<div class="event-odds">
  <header>
    <h2><?php echo $odds->market->name; ?></h2>
      <script>

      </script>
  </header>
  <?php bbfootball_template('odds-format-switcher.php'); ?>
  <div class="hide-for-large">
    <?php bbfootball_template('event-odds-small.php', array(
      'odds' => $odds
    )); ?>
  </div>

  <?php $bookies = get_posts(array(
    'post_type' => 'bbf_bookie',
    'posts_per_page' => -1,
    'post_status' => 'publish'
  ));
  ?>


  <div class="show-for-large">
    <table class="event-odds-table">
      <thead>
        <th class="name-column"></th>
        <?php foreach($bookies as $bookie) { ?>
        <th class="odds-column"><?php echo bbfootball_bookie($bookie); ?></th>
        <?php } ?>
      </thead>
      <tbody>
        <?php foreach($odds->outcomes as $outcome => $outcome_odds) { ?>
        <tr>
          <th class="name-column"><?php echo $outcome; ?></th>
          <?php foreach($bookies as $bookie) { ?>
          <td class="odds-column">
            <?php
              foreach($outcome_odds as $odds_obj) {
                if($odds_obj->bookie->name == $bookie->post_title) {
                  echo bbfootball_bookie($bookie, array(
                    'show_image' => false,
                    'odds' => array(
                      'decimal' => $odds_obj->price,
                      'fractional' => bbfootball_decimal_to_fractional($odds_obj->price)
                    )
                  ));
                }
              }
            ?>
          </td>
          <?php } ?>
        </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<!--    bright start 2020-08-18-->
    <div class="show-for-mobile">
        <table class="event-odds-table">
            <thead>
            <th class="odds-column"></th>
            <?php foreach($odds->outcomes as $outcome => $outcome_odds) { ?>
                <th class="odds-column"><?php echo $outcome; ?></th>
            <?php } ?>
            </thead>
            <tbody>
            <?php foreach($bookies as $bookie) { ?>
                <tr>
                    <th class="odds-column"><?php echo bbfootball_bookie($bookie); ?></th>
                    <?php foreach($odds->outcomes as $outcome => $outcome_odds) { ?>
                        <td class="odds-column">
                            <?php
                            foreach($outcome_odds as $odds_obj) {
                                if($odds_obj->bookie->name == $bookie->post_title) {
                                    echo bbfootball_bookie($bookie, array(
                                        'show_image' => false,
                                        'odds' => array(
                                            'decimal' => $odds_obj->price,
                                            'fractional' => bbfootball_decimal_to_fractional($odds_obj->price)
                                        )
                                    ));
                                }
                            }
                            ?>
                        </td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
<!--    bright end 2020-08-18-->
</div>
