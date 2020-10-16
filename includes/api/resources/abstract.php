<?php

abstract class BBF_Api_ResourceAbstract {
  /**
   * Holds the resource name
   * @var string
   */
  protected $resource_name;
  /**
   * Array of available routes
   * @var array
   */
  protected $routes;
  /**
   * Params for the route
   * @var array
   */
  protected $route_params = array();
  /**
   * The response from the route
   * @var mixed
   */
  protected static $response;
  /**
   * Default params
   * @var array
   */
  protected static $params = array();

  /**
   * Class constructor
   */
  public function __construct() {
    if(!isset($this->resource_name)) {
      $this->resource_name = $this->get_resource_name_from_class();
    }
  }

  /**
   * Try and get the resource name from the class
   * @return string
   */
  protected function get_resource_name_from_class() {
    $classname = get_class($this);
    $classname = str_replace('bbf_api_', '', strtolower($classname));
    return $classname;
  }

  /**
   * Return the resources endpoints
   * @return array
   */
  public static function get_endpoints() {
    return array();
  }

  /**
   * Add a Route
   * @param string $name
   * @param string $route
   */
  public function set_route($name, $route) {
    $this->routes[$name] = $route;
  }

  /**
   * Returns a route
   * @param string $name
   * @param array $params
   * @return mixed
   */
  public function get_route($route, $params = array()) {
    if(!isset($this->routes[$route])) {
      return false;
    }

    $route = $this->routes[$route];
    $replacements = array_merge($params, $this->get_route_params());
    foreach($replacements as $key => $val) {
      if(is_scalar($val)) {
        $route = str_replace('{' . $key . '}', $val, $route);
      }
    }
    return $route;
  }

  /**
   * Return the route params
   * @return array
   */
  public function get_route_params() {
    return $this->route_params;
  }

  /**
   * Set the Route params
   * @param array $params
   */
  public function set_route_params($params = array()) {
    $this->route_params = $params;
  }
}
