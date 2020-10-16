<?php

class BBF_Api_Events extends BBF_Api_ResourceAbstract {
  use BBF_Api_InitTrait;
  use BBF_Api_FindAllTrait;
  use BBF_Api_FindTrait {
    find as findTrait;
  }
    /**
     * Holds the resource name
     * @var string
     */
    protected $resource_name = 'event';

  /**
   * Get an event by country and league
   * @param string $country
   * @param string $league
   * @param string $event
   * @param array $params
   * @return mixed
   */
  public function find_in_country_league($country, $league, $event, $params = array()) {
    $this->set_route('in_country_league', 'event/{country}/{league}/{event}');
    $this->set_route_params(array(
      'league' => $league,
      'country' => $country,
      'event' => $event
    ));
    return $this->findTrait(null, $params, 'in_country_league');
  }

  /**
   * Get todays games
   * @param array $params
   * @return mixed
   */
  public function find_todays($params = array()) {
    $this->set_route('todays_games', 'events/today');
    return $this->findTrait(null, $params, 'todays_games');
  }

  /**
   * Validate an event
   */
  public function validate($country, $league, $event, $params = array()) {
    $this->set_route('validate', 'validate-event/{country}/{league}/{event}');
    $this->set_route_params(array(
      'league' => $league,
      'country' => $country,
      'event' => $event
    ));
    return $this->findTrait(null, $params, 'validate');
  }
}
