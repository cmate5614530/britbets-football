<?php

class BBF_Api_Activate extends BBF_Api_ResourceAbstract {
  use BBF_Api_FindTrait {
    find as findTrait;
  }

  /**
   * Get an event by country and league
   * @param string $country
   * @param string $league
   * @param string $event
   * @param array $params
   * @return mixed
   */
  public function type($type, $id, $params = array()) {
    $this->set_route('activate_item', 'activate/{type}/{id}');
    $this->set_route_params(array(
      'type' => $type,
      'id' => $id
    ));
    return $this->findTrait(null, $params, 'activate_item');
  }
}
