<?php
/**
 * Display a league
 */

// We need a $league variable
if(!isset($league, $atts)) {
  return;
}

$unique = uniqid();
?>
<div class="bbfootball-league-holder">
  <?php if(isset($atts['show_title']) && $atts['show_title']) { ?>
  <header>
    <h3><a href="<?php echo bbfootball_slug("{$league->country->slug}/{$league->slug}"); ?>"><?php echo $league->name; ?></a></h3>
    <button data-toggle="<?php echo $unique; ?>-league-events <?php echo $unique; ?>-league-toggle">
      <span class="dashicons dashicons-arrow-up-alt2" data-toggler="dashicons-arrow-up-alt2 dashicons-arrow-down-alt2" id="<?php echo $unique; ?>-league-toggle"></span>
    </button>
  </header>
  <?php } ?>
  <div id="<?php echo $unique; ?>-league-events" data-toggler="hide">
  <?php
    if(isset($league->events)) {
      // Sort the events by date

      $dates = array();
      foreach($league->events as $event) {
          //print_r($event) ;
        $date = DateTime::createFromFormat('d-m-Y', $event->date);
        if($date) {
          $dates[$date->format('U')][] = $event;
        }
      }
      ksort($dates);

      foreach($dates as $date => $events) {
        // Add the slug to the event
        $events = array_map(function($event) use($league) {
          $event->slug = "{$league->country->slug}/{$league->slug}/{$event->slug}";
          return $event;
        }, $events);

        $date = DateTime::createFromFormat('U', $date);
        echo "<h4>{$date->format('jS F Y')}</h4>";
        bbfootball_template('events.php', array(
          'events' => $events
        ));
      }

    }
  ?>
  </div>
</div>
