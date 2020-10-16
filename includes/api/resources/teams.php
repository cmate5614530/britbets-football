<?php

class BBF_Api_Teams extends BBF_Api_ResourceAbstract {
  use BBF_Api_InitTrait;
  use BBF_Api_FindAllTrait;
  use BBF_Api_FindTrait {
    find as findTrait;
  }



  /**
   * Get team stats
   * @param array $params
   * @return mixed
   */
  public function get_stats($id, $params = array()) {
    $this->set_route('get_stats', 'stats/{id}');
    $this->set_route_params(array(
      'id' => $id
    ));
    return $this->findTrait(null, $params, 'get_stats');
  }


}
