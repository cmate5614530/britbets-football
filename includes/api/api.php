<?php
if(!class_exists('BBF_Api')) {

  require __DIR__ . '/traits.php';
  require __DIR__ . '/resources/abstract.php';
  require __DIR__ . '/resources/leagues.php';
  require __DIR__ . '/resources/countries.php';
  require __DIR__ . '/resources/events.php';
  require __DIR__ . '/resources/bookies.php';
  require __DIR__ . '/resources/teams.php';
  require __DIR__ . '/resources/misc.php';
  require __DIR__ . '/resources/deactivate.php';

  /**
   * Setup the api area
   */
  class BBF_Api {
    use BBF_Api_InitTrait;
    /**
     * Holds the API base url
     * @var string
     */
    protected $base_url;
    /**
     * Holds the API base path
     * @var string
     */
    protected $base_path;
    /**
     * Holds the http scheme
     * @var string
     */
    protected $scheme;
    /**
     * Holds the host
     * @var string
     */
    protected $host;
    /**
     * Holds the subdomain
     * @var string
     */
    protected $domain;
    /**
     * Holds the port
     * @var int
     */
    protected $port;

    /**
     * Class constructor
     */
    public function __construct() {
      // $this->base_url = "http://britbetsapi.swipe72.co.uk";
      // $this->base_url = "http://swipe-api.test";
      $this->base_url = 'http://britbets.com/api/';
    }

    /**
     * Array of valid endpoints
     * @return array
     */
    public static function get_endpoints() {
      return array(
        'countries' => BBF_Api_Countries::class,
        'leagues' => BBF_Api_Leagues::class,
        'bookies' => BBF_Api_Bookies::class,
        'events' => BBF_Api_Events::class,
        'teams' => BBF_Api_Teams::class,
        'misc' => BBF_Api_Misc::class,
        'deactivate' => BBF_Api_Deactivate::class
      );
    }

    /**
     * Return the base_url
     * @return string
     */
    public function get_base_url() {
      return $this->base_url;
    }

    /**
     * Return the base path
     * @return string
     */
    public function get_base_path() {
      return $this->base_path . '/';
    }

    /**
     * Set the base_path
     * @param string $path
     */
    public function set_base_path($path) {
      $this->base_path = $path;
    }

    /**
     * Get a response
     * @param string $endpoint
     * @param array $params
     * @return mixed
     */
    public function get($endpoint, $params = array()) {

      $url = $this->get_base_url() . $this->get_base_path() . $endpoint;
      $request = array(
        'headers' => array(
          'Content-Type' => 'application/json; charset=UTF-8'
        ),
        'method' => 'GET'
      );

      if(!empty($params)) {
        $url = add_query_arg($params, $url);
      }

      $remote_request = wp_remote_request($url, $request);
      $response = wp_remote_retrieve_body($remote_request);
      if(!$response || !$json = json_decode($response)) {
        return false;
      }

      return $json;
    }
  }
}
