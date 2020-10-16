<?php
/**
 * Actions that can be replaced
 */

add_action('bbfootball_shortcode_invalid_country', 'bbfootball_shortcode_invalid_country');
add_action('bbfootball_shortcode_no_events', 'bbfootball_shortcode_no_events');
add_action('bbfootball_shortcode_no_country', 'bbfootball_shortcode_no_country');
add_action('bbfootball_shortcode_invalid_league', 'bbfootball_shortcode_invalid_league');
add_action('bbfootball_invalid_event', 'bbfootball_invalid_event');
add_action('bbfootball_shortcode_no_odds', 'bbfootball_shortcode_no_odds');

/**
 * This is triggered when the shortcode is displaying an invalid country
 * @param array $atts
 */
function bbfootball_shortcode_invalid_country($atts) {
  echo "The country is invalid.";
}

/**
 * The is triggered when the shortcode has no events
 * @param array $atts
 */
function bbfootball_shortcode_no_events($atts) {
  echo "There are no events.";
}

/**
 * This is triggered when the shortcode has no country
 * @param array $atts
 */
function bbfootball_shortcode_no_country($atts) {
  echo "No country is found.";
}

/**
 * This is triggered when the shortcode has an invalid league
 * @param array $atts
 */
function bbfootball_shortcode_invalid_league($atts) {
  // If we're in ajax - we'll remove the element
  if(wp_doing_ajax() && !isset($atts['league'])) {
    echo "__remove__";
  }
  else {
    $league = bbfootball()->api()->leagues()->find_in_country($atts['league'], $atts['country'], array(
      'events' => true,
      'country' => true,
      'teams' => true
    ));
    if($league) {
      bbfootball_template('league.php', array(
        'league' => $league,
        'atts' => array(
          'show_title' => true
        )
      ));
    }

    return;
  }

}

/**
 * This is triggered when the event is invalid
 */
function bbfootball_invalid_event() {
  var_dump(bbfootball_slug(''));
  exit;
}

/**
 * This is triggered when the shortcode displays n event with no odds
 */
function bbfootball_shortcode_no_odds() {
?>
<div class="bbfootball-no-odds">
  <span>1</span>
  <span>X</span>
  <span>2</span>
  <span></span>
  <p>No odds available. Check back soon.</p>
</div>
<?php
}
