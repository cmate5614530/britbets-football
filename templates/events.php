<?php
/**
 * Display a list of events
 */

// We need a $events variable
if(!isset($events) || !is_array($events)) {
  return;
}
?>
<div class="bbfootball-events-list">
  <?php foreach($events as $event) { ?>
  <div class="bbfootball-event-summary">
    <div class="event-info">
      <h4>
        <?php
        // We only show the link if the event has odds
        // If not we show links to the teams
        if(isset($event->odds)) {
          $slug = bbfootball_slug($event->slug);
          echo "<a href=\"{$slug}\">{$event->name}</a>";
        }
        else {
//            bright start make team name as hyperlinked. adjusted 2020-08-18
          if(!isset($event->teams)) {
            //echo $event->name;
              $remove_slash_array = explode("/", $event->slug);
              $removed_slash = array_pop($remove_slash_array);
              $team_array = explode("-vs-", $removed_slash);
              $team_name_array = explode(" vs ",$event->name );
              ?>
              <a href="<?php echo bbfootball_slug("team/{$team_array[0]}"); ?>"><?php echo $team_name_array[0]; ?></a> vs <a href="<?php echo bbfootball_slug("team/{$team_array[1]}"); ?>"><?php echo $team_name_array[1]; ?></a>

              <?php
          }
          else {
        ?>
        <a href="<?php echo bbfootball_slug("team/{$event->teams[0]->slug}"); ?>"><?php echo $event->teams[0]->name; ?></a> vs <a href="<?php echo bbfootball_slug("team/{$event->teams[1]->slug}"); ?>"><?php echo $event->teams[1]->name; ?></a>
        <?php
          }
        }
//        bright end making team name hyperlinked
        ?>
      </h4>
      <ul class="event-meta">
        <li class="date">
          <?php
            if($date = DateTime::createFromFormat('d-m-Y', $event->date)) {
              echo $date->format('jS F');
            }
            else {
              echo $event->date;
            }
          ?>
        </li>
        <li class="time"><?php echo $event->time; ?></li>
        <?php if(isset($event->country)) { ?>
        <li><a href="<?php echo bbfootball_slug($event->country->slug); ?>"><?php echo $event->country->name; ?></a></li>
        <?php } ?>
        <?php if(isset($event->league)) { ?>
        <li><a href="<?php echo bbfootball_slug("{$event->country->slug}/{$event->league->slug}"); ?>"><?php echo $event->league->name; ?></a></li>
        <?php } ?>
      </ul>
    </div>
    <div class="event-odds">
      <?php
        if(isset($event->odds)) {
          bbfootball_template('events-odds.php', array(
            'event' => $event
          ));
        }
        else {
          // If we're already doing AJAX - we can't do it again so just display the template
          if(wp_doing_ajax()) {
            do_action('bbfootball_shortcode_no_odds');
          }
          else {
            echo do_shortcode("[odds event_summary=\"{$event->id}\" ajax=\"true\"]");
          }
        }
      ?>
    </div>
  </div>
  <?php } ?>
</div>
