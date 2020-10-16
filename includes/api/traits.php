<?php

// Init Trait used to enable the others
trait BBF_Api_InitTrait {
  /**
   * This method allows functions with the same name as a variable
   * $api->country can be $api->country()
   * @param string $name
   * @param mixed $args
   * @return mixed
   */
  public function __call($name, $args) {
    $endpoints = $this::get_endpoints();
    if(isset($endpoints[$name])) {
      $class = $endpoints[$name];
      return new $class();
    }
    return false;
  }
}

// Find a single item

trait BBF_Api_FindTrait {
  /**
   * Load all of this rosourse
   * @param null $id
   * @param array $params
   * @param string $route
   * @return mixed
   */
  public function find($id, $params = array(), $route = __FUNCTION__) {
    $route = $this->get_route($route, array(
      'id' => $id
    ));
    if(!$route) {
      if(!isset($this->resource_name)) {
        $this->resource_name = $this->get_resource_name_from_class();
      }

      if(empty($id)) {
        return false;
      }

      $route = $this->resource_name . '/' . $id;
      $this->set_route(__FUNCTION__, $route);
    }
    self::$response = bbfootball()->api()->get($route, $params);
    return self::$response;
  }
}


// Find all Trait
trait BBF_Api_FindAllTrait {
  /**
   * Load all of this rosourse
   * @param array $params
   * @param string $route
   * @return mixed
   */
  public function find_all($params = array(), $route = __FUNCTION__) {
    $params = array_merge($params, self::$params);
    $route = $this->get_route($route, $params);
    if(!$route) {
      if(!isset($this->resource_name)) {
        $this->resource_name = $this->get_resource_name_from_class();
      }
      $route = $this->resource_name;
      $this->set_route(__FUNCTION__, $route);
    }

    self::$response = bbfootball()->api()->get($route, $params);
    return self::$response;
  }
}
