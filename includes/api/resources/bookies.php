<?php

class BBF_Api_Bookies extends BBF_Api_ResourceAbstract {
  use BBF_Api_InitTrait;
  use BBF_Api_FindAllTrait;
  use BBF_Api_FindTrait;


  public function activate($id, $params = array()) {
    $this->set_route('activate_item', 'bookies/{id}/activate');
    $this->set_route_params(array(
      'id' => $id
    ));
    return $this->find(null, $params, 'activate_item');
  }


  public function deactivate($id, $params = array()) {
    $this->set_route('deactivate_item', 'bookies/{id}/deactivate');
    $this->set_route_params(array(
      'id' => $id
    ));
    return $this->find(null, $params, 'deactivate_item');
  }


}
