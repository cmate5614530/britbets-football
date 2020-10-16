<?php
if(!class_exists('BBF_Admin')) {


  class BBF_Shortcodes {
    /**
     * Holds the current instance of the class
     * @var null
     */
    private static $instance = null;

    /**
     * Return the current instance
     * @return BBF_Shortcodes
     */
    public static function get_instance() {
      if(is_null(self::$instance)) {
        self::$instance = new self;
      }
      return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
      add_shortcode('odds', array($this, 'odds'));
      add_shortcode('livedata', array($this, 'livedata'));
    }

  /**
   * Output the [livedata] shortcode
   * @param array $att
   * @author Bright
   * @date 2020-08-16
   */
    public function  livedata($att){
        $att = shortcode_atts(array(
            'livedata'=>false
        ), $att, 'livedata');
        $att['livedata'] = $this->string_to_bool($att['livedata']);
        ob_start();

        if($att['livedata']){
            bbfootball_template('livedata.php', array(
                'livedata'=>$att['livedata']
            ));
        }
        return ob_get_clean();
    }
    /**
     * Output the [odds] shortcode
     * @param array $atts
     */
    public function odds($atts) {
      $atts = shortcode_atts(array(
        'country' => '',
        'league' => '',
        'event' => '',
        'ajax' => false,
        'upcoming' => false,
        'show_title' => true,
        'event_summary' => 0,
        'last_update' => false,
        'team' => ''
      ), $atts, 'odds');

      // Ensure the ajax/upcoming attributes are boolean
      $atts['ajax'] = $this->string_to_bool($atts['ajax']);
      $atts['upcoming'] = $this->string_to_bool($atts['upcoming']);
      $atts['show_title'] = $this->string_to_bool($atts['show_title']);
      $atts['last_update'] = $this->string_to_bool($atts['last_update']);
      // If we're already doing ajax - make the ajax attribute false
      // if(wp_doing_ajax()) {
      //   $atts['ajax'] = false;
      // }

      ob_start();

      // Ajax shortcodes stop here
      if($atts['ajax']) {
        echo bbfootball_ajax_elemet(array(
          'attributes' => $atts
        ));

        return ob_get_clean();
      }
      else {
        if(!wp_doing_ajax()) {
          bbfootball_template('odds-format-switcher.php');
        }
      }

      // If it's a team
      if($atts['team']) {
        $team = $team = bbfootball()->api()->teams()->find($atts['team'], array(
          'events' => true,
          'leagues' => true,
          'countries' => true
        ));
        if(!$team) {
          do_action('bbfootball_shortcode_invalid_team');
          return ob_get_clean();
        }

        bbfootball_template('team.php', array(
          'team' => $team,
          'atts' => $atts
        ));
        return ob_get_clean();
      }

      // If we're getting the upcoming events - get them here
      if($atts['upcoming']) {

        $events = bbfootball()->api()->events()->find_todays(array(
          'league' => true,
          'country' => true
        ));
        if(!$events) {
          do_action('bbfootball_shortcode_no_events');
          return ob_get_clean();
        }
        // We found some events
        // Load them in the template
        bbfootball_template('events.php', array(
          'events' => $events
        ));
        return ob_get_clean();
      }

//      //bright start-live data
//      if($atts['livedata']) {
//          bbfootball_template('livedata.php');
//          return ob_get_clean();
//      }
//      //bright end-live data

      // Event summary
      if($atts['event_summary'] > 0) {
        // Get the event summary
        $event = bbfootball()->api()->events()->find($atts['event_summary'], array(
          'odds' => true,
          'league' => true,
          'country' => true,
          'best-odds' => true
        ));
        if(!$event || !isset($event->odds)) {
          do_action('bbfootball_shortcode_no_odds', $atts);
          return ob_get_clean();
        }
        bbfootball_template('events-odds.php', array(
          'event' => $event
        ));
        return ob_get_clean();
      }

      if($atts['last_update']) {
        $update = bbfootball()->api()->get('last-update');
        if($update) {
          echo "Odds were last updated: {$update}";
        }
        return ob_get_clean();
      }

      // If there's no country - stop here.
      if(!$atts['country']) {
        do_action('bbfootball_shortcode_no_country');
        return ob_get_clean();
      }
      // If there's no league - we can validate this country
      if(!$atts['league']) {
        $country = bbfootball()->api()->countries()->find($atts['country'], array(
          'leagues' => true,
//          'hide_empty' => true
        ));
        if(!$country) {
          do_action('bbfootball_shortcode_invalid_country', $atts);
          return ob_get_clean();
        }
        // Display the country in the template
        // It's an array and we only need the first element
        bbfootball_template('country.php', array(
          'country' => $country,
          'atts' => $atts
        ));

        return ob_get_clean();
      }
      // A league is set
      // Check for an event
      if($atts['event']) {
        // Get the event
        $event = bbfootball()->api()->events()->find_in_country_league($atts['country'], $atts['league'], $atts['event'], array(
          'odds' => true
        ));
        if(!$event) {
          do_action();
        }
        else {
          bbfootball_template('event-markets.php', array(
            'event' => $event,
            'atts' => $atts
          ));
        }
        return ob_get_clean();
      }
      // Try and get the events for this league
      $league = bbfootball()->api()->leagues()->find_in_country($atts['league'], $atts['country'], array(
        'events' => true,
        'country' => true,
        'odds' => true,
        'teams' => true
      ));

      if(!$league) {
        do_action('bbfootball_shortcode_invalid_league', $atts);
        return ob_get_clean();
      }

      // Show the league in the template
      // We only want the first entry
      bbfootball_template('league.php', array(
        'league' => $league,
        'atts' => $atts
      ));
      return ob_get_clean();

    }


    /**
     * convert a string to a bool
     * @param string $string
     * @return bool
     */
    public function string_to_bool($string) {
      return (is_bool($string) && $string) || in_array($string, array(1, '1', 'true', 'yes'), true);
    }

  }
}
