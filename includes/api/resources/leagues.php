<?php

class BBF_Api_Leagues extends BBF_Api_ResourceAbstract {
  use BBF_Api_InitTrait;
  use BBF_Api_FindAllTrait;
  use BBF_Api_FindTrait {
    find as findTrait;
  }

  /**
   * Get a league by a country
   * @param string $league
   * @param string $country
   * @param array $params
   * @return mixed
   */
  public function find_in_country($league, $country, $params = array()) {
    $this->set_route('league_in_country', 'leagues/{country}/{league}');
    $this->set_route_params(array(
      'league' => $league,
      'country' => $country
    ));
    return $this->findTrait(null, $params, 'league_in_country');
  }
}
