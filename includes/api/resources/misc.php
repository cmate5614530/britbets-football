<?php

class BBF_Api_Misc extends BBF_Api_ResourceAbstract {
  use BBF_Api_InitTrait;

  /**
   * Get the last update
   * @param string $country
   * @param string $league
   * @param string $event
   * @param array $params
   * @return mixed
   */
  public function last_update($params = array()) {
    self::$response = bbfootball()->api()->get('last_update', $params);
    return self::$response;
  }

}
