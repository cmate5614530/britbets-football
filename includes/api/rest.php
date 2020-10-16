<?php

class BBF_Api_Rest {
  /**
   * Holds the WPDB object
   * @var null
   */
  protected $db = null;

  /**
   * Class constructor
   */
   public function __construct() {
     $this->setup_db();
     $this->namespace = 'britbets';
     add_action('rest_api_init', array($this, 'register_routes'));
   }

   /**
    * Set up the connection to the odds database
    */
  private function setup_db() {
    // $this->db = new wpdb('root', 'root', 'britbets-sportmonks2', '127.0.0.1');
    $this->db = new wpdb('sraudncw_britbets', 'mxAdBK_,2)q7', 'sraudncw_britbets_odds_feed', 'localhost');
    $this->db->show_errors();
  }

  /**
   * Register the custom REST Routes
   */
  public function register_routes() {
    // Route to collect the countries
    register_rest_route(BBF_SLUG, '/countries(?:/(?P<country>[a-zA-Z0-9-]+))?', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_countries'),
      'args' => array(
        'country'
      )
    ));

    // Route to collect bookies
    register_rest_route(BBF_SLUG, '/bookies(?:/(?P<bookie>[a-zA-Z0-9-]+))?', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_bookies'),
      'args' => array(
        'bookie'
      )
    ));

    // Route to collect teams
    register_rest_route(BBF_SLUG, '/teams(?:/(?P<team>[a-zA-Z0-9-]+))?', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_teams'),
      'args' => array(
        'team'
      )
    ));
    // Route to collect leagues
    register_rest_route(BBF_SLUG, '/leagues', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_leagues')
    ));
    // Route to collect leagues
    register_rest_route(BBF_SLUG, '/leagues/(?P<country>[a-zA-Z0-9-]+)(?:/(?P<league>[a-zA-Z0-9-]+))?', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_leagues'),
      'args' => array(
        'country',
        'league'
      )
    ));
    // Route to collect a single event
    register_rest_route(BBF_SLUG, '/event/(?P<country>[a-zA-Z0-9-]+)/(?P<league>[a-zA-Z0-9-]+)/(?P<event>[a-zA-Z0-9-]+)', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_event'),
      'args' => array(
        'country',
        'league',
        'event'
      )
    ));
    // Route to collect todays events
    register_rest_route(BBF_SLUG, '/events/today', array(
      'methods' => 'GET',
      'callback' => array($this, 'get_todays_events')
    ));
    // Route to validate a single event
    register_rest_route(BBF_SLUG, '/validate-event/(?P<country>[a-zA-Z0-9-]+)/(?P<league>[a-zA-Z0-9-]+)/(?P<event>[a-zA-Z0-9-]+)', array(
      'methods' => 'GET',
      'callback' => array($this, 'validate_event'),
      'args' => array(
        'country',
        'league',
        'event'
      )
    ));
  }

  /**
   * Return an array of countries from the database
   * @param WP_REST_Request $request
   * @return array
   */
  public function get_countries($request) {
    $params = $this->get_params();

    // To keep things easier to update
    // We'll have multiple queries here - depending on the params
    if(isset($params['leagues']) && $params['leagues']) {
      // If we're hiding empty leagues (leagues with no fixtures)
      if(isset($params['hide_empty']) && $params['hide_empty']) {
        $time = time();
        $sql = "SELECT
                  country.name as country_name,
                  league.name as league_name
                FROM countries as country
                LEFT JOIN
                  leagues as league ON
                    league.country = country.id
                    LEFT JOIN
                      events as event ON
                        event.league = league.id
                        JOIN
                          odds as odds ON
                            odds.event = event.id AND
                            odds.market = 1
                WHERE
                  1=1 AND
                  event.ts > {$time} ";
        // If there's a name - extend the query
        if($request['country']) {
          $sql .= " AND country.name = \"{$request['country']}\" ";
        }
      }
      else {
        // just get the leagues with the country
        $sql = "SELECT
                  country.name as country_name,
                  league.name as league_name
                FROM countries as country
                LEFT JOIN
                  leagues as league ON
                    league.country = country.id
                WHERE
                  1=1 ";
        // If there's a name - extend the query
        if($request['country']) {
          $sql .= " AND country.name = \"{$request['country']}\" ";
        }
      }
    }
    else {
      // There's no leagues parameter - so it's a basic query
      // Maybe with the country name
      $sql = "SELECT
                country.name as country_name
              FROM
                countries as country
              WHERE
                1=1";

      // If there's a name - extend the query
      if($request['country']) {
        $sql .= " AND country.name = \"{$request['country']}\" ";
      }
    }

    // Order them
    $sql .= " ORDER BY country.name ";

    $results = $this->db->get_results($sql);
    if(!$results) {
      rest_ensure_response(false);
    }
    // Empty array to hold the countries
    $countries = array();
    foreach($results as $result) {
      if($result->country_name == "") {
        continue;
      }
      $countries[$result->country_name] = array(
        'name' => $result->country_name
      );
    }

    // If we're showing the leagues add them here
    if(isset($params['leagues']) && $params['leagues']) {
      foreach($results as $result) {
        if(!$result->league_name) {
          continue;
        }

        $league = array(
          'name' => $result->league_name,
        );

        if(isset($countries[$result->country_name])) {
          $countries[$result->country_name]['leagues'][$league['name']] = $league;
        }
      }

      if(isset($params['leagues']) && $params['leagues']) {
        foreach($countries as $key => $val) {
          if(isset($val['leagues'])) {
            $countries[$key]['leagues'] = array_values($val['leagues']);
          }
          else {
            if(isset($params['hide_empty']) && $params['hide_empty']) {
              unset($countries[$key]);
            }
          }

        }
      }
    }
    // Remove the array keys
    $countries = array_values($countries);
    return rest_ensure_response($countries);
  }

  /**
   * Return an array of bookies from the database
   * @param WP_REST_Request $request
   * @return array
   */
  public function get_bookies($request) {
    $params = $this->get_params();
    $sql = "SELECT
              name
            FROM
              bookies
            WHERE 1=1 ";

    if($request['bookie']) {
      $sql .= "AND name = \"{$request['bookie']}\" ";
    }

    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response(false);
    }
    return rest_ensure_response($results);
  }

  /**
   * Return an array of teams from the database
   * @param WP_REST_Request $request
   * @return array
   */
  public function get_teams($request) {
    $params = $this->get_params();
    $request['team'] = str_replace('-', ' ', $request['team']);
    $sql = "SELECT
              team.name as team_name,
              team.id as team_id,
              league.name as league_name,
              event.home_team as event_home_team,
              event.away_team as event_away_team,
              event.ts as event_ts,
              extra_team.name as event_extra_team,
              country.name as country_name,
              bookie.name as odds_bookie,
              odds.price as odds_price,
              odds.outcome as odds_outcome
            FROM
              teams as team
              LEFT JOIN
                events as event on
                  event.home_team = team.id OR
                  event.away_team = team.id
                  JOIN leagues as league ON
                    league.id = event.league
                    JOIN countries as country ON
                      country.id = league.country
                    LEFT JOIN teams as extra_team ON
                      extra_team.id != team.id AND
                      (extra_team.id = event.home_team OR extra_team.id = event.away_team)
                      LEFT JOIN odds as odds ON
                        odds.event = event.id AND
                        odds.market = 1
                        LEFT JOIN bookies as bookie ON
                          bookie.id = odds.bookie
            WHERE 1=1 ";

    if($request['team']) {
      $sql .= "AND team.name = \"{$request['team']}\" ";
    }

    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response(false);
    }
    $teams = array();
    foreach($results as $result) {
      $team = array(
        'name' => $result->team_name
      );

      $teams[$team['name']] = $team;
    }

    foreach($results as $result) {

      if($result->event_home_team == $result->team_id) {
        $event_name = "{$result->team_name} vs {$result->event_extra_team}";
      }
      else {
        $event_name = "{$result->event_extra_team} vs {$result->team_name}";
      }

      $date = DateTime::createFromFormat('U', $result->event_ts);
      $event = array(
        'name' => $event_name,
        'ts' => $result->event_ts,
        'date' => $date->format('d-m-Y'),
        'time' => $date->format('H:i'),
        'league' => array(
          'name' => $result->league_name
        ),
        'country' => array(
          'name' => $result->country_name,
        )
      );

      $teams[$result->team_name]['events'][$event_name] = $event;
    }

    foreach($results as $result) {
      if(!$result->odds_bookie) {
        continue;
      }
      if($result->event_home_team == $result->team_id) {
        $event_name = "{$result->team_name} vs {$result->event_extra_team}";
      }
      else {
        $event_name = "{$result->event_extra_team} vs {$result->team_name}";
      }

      $odds = array(
        'bookie' => $result->odds_bookie,
        'decimal_odds' => $result->odds_price
      );

      // We only want the best odds
      if(isset($teams[$result->team_name]['events'][$event_name]['odds'][$result->odds_outcome])) {
        if($teams[$result->team_name]['events'][$event_name]['odds'][$result->odds_outcome] > $odds['decimal_odds']) {
          $teams[$result->team_name]['events'][$event_name]['odds'][$result->odds_outcome] = $odds;
        }
      }
      else {
        $teams[$result->team_name]['events'][$event_name]['odds'][$result->odds_outcome] = $odds;
      }
    }


    $teams = array_values($teams);
    return rest_ensure_response($teams);
  }

  /**
   * Return an array of leagues from the database
   * @param WP_REST_Request $request
   * @return array
   */
  public function get_leagues($request) {
    $params = $this->get_params();
    $sql = "SELECT
              league.id as league_id,
              league.name as league_name,
              country.name as country_name,
              country.id as country_id ";
    // Are we showing the events?
    if(isset($params['events']) && $params['events']) {
      $sql .= ", event.ts as event_ts,
               event.id as event_id,
               home_team.name as event_home_team,
               away_team.name as event_away_team ";

      // Are we getting the odds
      if(isset($params['odds']) && $params['odds']) {
        $sql .= ", bookie.name as odds_bookie,
                odds.price as odds_price,
                odds.outcome as odds_outcome ";
      }
    }

    // Carry on the SQL
    $sql .= "FROM
              leagues as league
              LEFT JOIN
                countries as country ON
                  country.id = league.country ";

    // Are we showing the events?
    if(isset($params['events']) && $params['events']) {
      $sql .= "LEFT JOIN
                  events as event ON
                    event.league = league.id
                    JOIN
                      teams as home_team ON
                        home_team.id = event.home_team
                        JOIN
                          teams as away_team ON
                            away_team.id = event.away_team
                LEFT JOIN
                    odds as odds ON
                      odds.event = event.id AND
                      odds.market = 1
                      JOIN
                        bookies as bookie ON
                          bookie.id = odds.bookie ";
    }

    // Carry on the SQL
    $sql .= "WHERE 1=1 ";
    if($request['country']) {
      $sql .= "AND country.name = \"{$request['country']}\" ";
    }

    if($request['league']) {
      $league_name = str_replace('-', ' ', $request['league']);
      $sql .= "AND league.name = \"{$league_name}\" ";
    }

    if(isset($params['events']) && $params['events']) {
      $time = time();
      $sql .= "AND event.ts > {$time} ORDER BY event.ts ";
    }

    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response(false);
    }

    // Empty array to hold the leagues
    $leagues = array();
    foreach($results as $result) {
      $league = array(
        'id' => $result->league_id,
        'name' => $result->league_name,
        'country' => array(
          'id' => $result->country_id,
          'name' => $result->country_name,
        )
      );

      $flag = ABSPATH . "/flags/{$result->country_id}.svg";
      if(file_exists($flag)) {
        $league['country']['flag'] = esc_url(home_url("flags/{$result->country_id}.svg"));
      }

      $leagues["{$result->country_name}{$result->league_name}"] = $league;
    }

    // If we're adding the events
    if(isset($params['events']) && $params['events']) {
      foreach($results as $result) {
        $date = DateTime::createFromFormat('U', $result->event_ts);
        $event = array(
          'id' => $result->event_id,
          'name' => "{$result->event_home_team} vs {$result->event_away_team}",
          'ts' => $result->event_ts,
          'date' => $date->format('d-m-Y'),
          'time' => $date->format('H:i'),
          'league' => array(
            'id' => $result->league_id,
            'name' => $result->league_name
          ),
          'country' => array(
            'id' => $result->country_id,
            'name' => $result->country_name,
          )
        );

        $leagues["{$result->country_name}{$result->league_name}"]['events'][$event['name']] = $event;
      }

      // Are we adding the odds?
      if(isset($params['odds']) && $params['odds']) {
        foreach($results as $result) {
          $name = "{$result->event_home_team} vs {$result->event_away_team}";
          $odds = array(
            'bookie' => $result->odds_bookie,
            'decimal_odds' => $result->odds_price
          );

          // We only want the best odds
          if(isset($leagues["{$result->country_name}{$result->league_name}"]['events'][$name]['odds'][$result->odds_outcome])) {
            if($leagues["{$result->country_name}{$result->league_name}"]['events'][$name]['odds'][$result->odds_outcome] > $odds['decimal_odds']) {
              $leagues["{$result->country_name}{$result->league_name}"]['events'][$name]['odds'][$result->odds_outcome] = $odds;
            }
          }
          else {
            $leagues["{$result->country_name}{$result->league_name}"]['events'][$name]['odds'][$result->odds_outcome] = $odds;
          }
        }
      }

    }


    // Remove the keys
    if(isset($params['events']) && $params['events']) {
      $leagues = array_map(function($league) {
        $league['events'] = array_values($league['events']);
        return $league;
      }, $leagues);
    }


    return rest_ensure_response(array_values($leagues));
  }

  /**
   * Validate an event
   * @param WP_REST_Request $request
   * @return array
   */
  public function validate_event($request) {
    $params = $this->get_params();
    // Get the team names
    $team_names = explode('-vs-', $request['event']);
    $request['country'] = str_replace('-', ' ', $request['country']);
    $request['league'] = str_replace('-', ' ', $request['league']);
    $sql = "SELECT
              league.name as league_name,
              country.name as country_name,
              country.id as country_id,
              event.ts as event_ts,
              home_team.name as event_home_team,
              away_team.name as event_away_team
            FROM
              leagues as league
              LEFT JOIN
                countries as country on
                  country.id = league.country
                  LEFT JOIN
                    events as event ON
                      event.league = league.id
                      LEFT JOIN
                        teams as home_team ON
                          home_team.id = event.home_team
                      LEFT JOIN
                        teams as away_team ON
                          away_team.id = event.away_team
                  WHERE
                league.name = \"{$request['league']}\" AND
                country.name = \"{$request['country']}\" AND
                home_team.slug = \"{$team_names[0]}\" AND
                away_team.slug = \"{$team_names[1]}\" ";

    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response($sql);
    }

    $event_name = "{$results[0]->event_home_team} vs {$results[0]->event_away_team}";
    $event_date = DateTime::createFromFormat('U', $results[0]->event_ts);
    $event = array(
      'name' => $event_name,
      'ts' => $results[0]->event_ts,
      'home_team' => $results[0]->event_home_team,
      'away_team' => $results[0]->event_away_team,
      'date' => $event_date->format('d-m-Y'),
      'time' => $event_date->format('H:i'),
      'league' => array(
        'name' => $results[0]->league_name
      ),
      'country' => array(
        'name' => $results[0]->country_name
      )
    );

    return $event;
  }

  /**
   * Return a single event from the database
   * @param WP_REST_Request $request
   * @return array
   */
  public function get_event($request) {
    $params = $this->get_params();
    // Get the team names
    $team_names = explode('-vs-', $request['event']);

    $request['country'] = str_replace('-', ' ', $request['country']);
    $request['league'] = str_replace('-', ' ', $request['league']);

    $sql = "SELECT
              league.name as league_name,
              country.name as country_name,
              country.id as country_id,
              event.ts as event_ts,
              home_team.name as event_home_team,
              away_team.name as event_away_team ";
    // Are we getting the odds too?
    if(isset($params['odds']) && $params['odds']) {
      $sql .= ", odds.price as odds_price,
                odds.outcome as odds_outcome,
                bookie.name as odds_bookie,
                market.name as odds_market ";
    }
    $sql .= "FROM
              leagues as league
              LEFT JOIN
                countries as country on
                  country.id = league.country
                  LEFT JOIN
                    events as event ON
                      event.league = league.id
                      LEFT JOIN
                        teams as home_team ON
                          home_team.id = event.home_team
                      LEFT JOIN
                        teams as away_team ON
                          away_team.id = event.away_team ";
      // Are we getting the odds too?
      if(isset($params['odds']) && $params['odds']) {
        $sql .= "LEFT JOIN
                  odds as odds ON
                    odds.event = event.id
                    JOIN
                      bookies as bookie ON
                        bookie.id = odds.bookie
                        LEFT JOIN
                          markets as market ON
                            market.id = odds.market ";
      }
      $sql .= "WHERE
                league.name = \"{$request['league']}\" AND
                country.name = \"{$request['country']}\" AND
                home_team.slug = \"{$team_names[0]}\" AND
                away_team.slug = \"{$team_names[1]}\" ";
    if(isset($params['odds']) && $params['odds']) {
      $sql .= " ORDER BY market.id ";
    }
    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response(false);
    }

    $event_name = "{$results[0]->event_home_team} vs {$results[0]->event_away_team}";
    $event_date = DateTime::createFromFormat('U', $results[0]->event_ts);
    $event = array(
      'name' => $event_name,
      'ts' => $results[0]->event_ts,
      'home_team' => $results[0]->event_home_team,
      'away_team' => $results[0]->event_away_team,
      'date' => $event_date->format('d-m-Y'),
      'time' => $event_date->format('H:i'),
      'league' => array(
        'name' => $results[0]->league_name
      ),
      'country' => array(
        'name' => $results[0]->country_name
      )
    );

    $flag = ABSPATH . "/flags/{$results[0]->country_id}.svg";
    if(file_exists($flag)) {
      $event['country']['flag'] = esc_url(home_url("flags/{$results[0]->country_id}.svg"));
    }

    // Are we showing the odds?
    if(isset($params['odds']) && $params['odds']) {
      foreach($results as $result) {
        $event['bookies'][$result->odds_bookie] = $result->odds_bookie;
        $odds = array(
          'bookie' => $result->odds_bookie,
          'decimal_odds' => $result->odds_price,
        );

        $event['markets'][$result->odds_market]['odds'][$result->odds_outcome][$result->odds_bookie] = $odds;
        $event['markets'][$result->odds_market]['bookies'][] = $result->odds_bookie;
        $event['markets'][$result->odds_market]['bookies'] = array_values(array_unique($event['markets'][$result->odds_market]['bookies']));
      }

      $event['bookies'] = array_values(array_unique($event['bookies']));
    }

    return rest_ensure_response($event);
  }

  /**
   * Return an array of events for today
   * @param WP_REST_Request $request
   */
  public function get_todays_events($request) {
    $time = time();
    $day_time = strtotime('+1 day', $time);

    //$date = new DateTime();
    //$date->setTime(00, 00);
    //$time = $date->getTimestamp();

    //$date1= new DateTime('tomorrow + 1day');
    //$date1->setTime(23,59);
    //$day_time = $date1->getTimestamp();

    $sql = "SELECT
              league.name as league_name,
              country.name as country_name,
              country.id as country_id,
              event.ts as event_ts,
              home_team.name as event_home_team,
              away_team.name as event_away_team,
              odds.price as odds_price,
              odds.outcome as odds_outcome,
              bookie.name as odds_bookie,
              market.name as odds_market
            FROM
              leagues as league
              LEFT JOIN
                countries as country on
                  country.id = league.country
                  LEFT JOIN
                    events as event ON
                      event.league = league.id
                      LEFT JOIN
                        teams as home_team ON
                          home_team.id = event.home_team
                      LEFT JOIN
                        teams as away_team ON
                          away_team.id = event.away_team
                      LEFT JOIN
                        odds as odds ON
                          odds.event = event.id
                          JOIN
                            bookies as bookie ON
                              bookie.id = odds.bookie
                              LEFT JOIN
                                markets as market ON
                                  market.id = odds.market
              WHERE
                event.ts > {$time} AND
                event.ts < {$day_time} AND
                market.id = 1";
    $results = $this->db->get_results($sql);
    if(!$results) {
      return rest_ensure_response(false);
    }
    // Todays date
    $date = date('d-m-Y');
    $events = array();
    foreach($results as $result) {

      $event_name = "{$result->event_home_team} vs {$result->event_away_team}";
      $event_date = DateTime::createFromFormat('U', $result->event_ts);
      if($event_date->format('d-m-Y') !== $date) {
        continue;
      }
      $event = array(
        'name' => $event_name,
        'ts' => $result->event_ts,
        'home_team' => $result->event_home_team,
        'away_team' => $result->event_away_team,
        'date' => $event_date->format('d-m-Y'),
        'time' => $event_date->format('H:i'),
        'league' => array(
          'name' => $result->league_name
        ),
        'country' => array(
          'name' => $result->country_name
        )
      );

      $events[$event['name']] = $event;
    }

    foreach($results as $result) {
      $event_date = DateTime::createFromFormat('U', $result->event_ts);
      if($event_date->format('d-m-Y') !== $date) {
        continue;
      }

      $name = "{$result->event_home_team} vs {$result->event_away_team}";
      $odds = array(
        'bookie' => $result->odds_bookie,
        'decimal_odds' => $result->odds_price
      );

      // We only want the best odds
      if(isset($events[$name]['odds'][$result->odds_outcome])) {
        if($events[$name]['odds'][$result->odds_outcome] > $odds['decimal_odds']) {
          $events[$name]['odds'][$result->odds_outcome] = $odds;
        }
      }
      else {
        $events[$name]['odds'][$result->odds_outcome] = $odds;
      }
    }


    return rest_ensure_response(array_values($events));
  }

  /**
   * Get the current GET params and convert the bool values
   * @return array
   */
  private function get_params() {
    return array_filter(array_map(function($param) {
      if(in_array($param, array('1', 'true', 'yes'))) {
        return true;
      }
      else if(in_array($param, array('0', 'false', 'no'))) {
        return false;
      }

      return $param;
    }, $_GET));
  }

}
