<?php if(!defined('ABSPATH')) exit;
/**
 * Plugin name: Football Odds
 */

if(!class_exists('BBFootball')):
  class BBFootball {
    /**
     * Init the plugin
     * This function will run after WordPress themes & plugins are loaded
     */
    public function init() {
      $this->define('BBF_FILE', __FILE__);
      $this->define('BBF_BASE', plugin_basename(BBF_FILE));
      $this->define('BBF_PATH', plugin_dir_path(BBF_FILE));
      $this->define('BBF_URL', plugin_dir_url(__FILE__));
      $this->define('BBF_SLUG', dirname(BBF_BASE));

      include BBF_PATH . 'includes/helpers.php';
      include BBF_PATH . 'includes/actions.php';
      include BBF_PATH . 'includes/api/api.php';
      include BBF_PATH . 'includes/shortcodes.php';
      include BBF_PATH . 'includes/widgets.php';

      // include BBF_PATH . 'includes/api/rest.php';
      // $this->rest = new BBF_Api_Rest;
      $this->shortcodes = BBF_Shortcodes::get_instance();

      // Admin
      if(is_admin()) {
        include BBF_PATH . 'includes/admin/admin.php';
      }

      add_action('init', array($this, 'register_post_types'));
      add_action('init', array($this, 'add_rewrite_rules'));
      add_action('parse_request', array($this, 'parse_request'));
      add_action('pre_get_posts', array($this, 'increase_post_count'));

      add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
      add_action('widgets_init', array($this, 'register_widgets'));

      add_action('body_class', array($this, 'body_class'));

      // AJAX Elements
      add_action('wp_ajax_bbfootball_ajax_elemet', array($this, 'ajax_element'));
      add_action('wp_ajax_nopriv_bbfootball_ajax_elemet', array($this, 'ajax_element'));

      add_action('wp_ajax_bbfootball_get_market_odds', array($this, 'ajax_get_market_odds'));
      add_action('wp_ajax_nopriv_bbfootball_get_market_odds', array($this, 'ajax_get_market_odds'));

      // HACK
      // Remove Gutenberg fom best-odds
      add_filter('use_block_editor_for_post_type', function($status, $post_type) {
        if($post_type == 'best-odds') {
          return false;
        }

        return $status;
      }, 10, 2);
    }

    /**
     * Define a constant if it's not already defined
     * @param string $key
     * @param string $value
     */
    private function define($key, $value = true) {
      if(!defined($key)) {
        define($key, $value);
      }
    }

    /**
     * Extends the classes added to the <body>
     * @param array $classes
     * @return array
     */
    public function body_class($classes) {
      if(get_query_var('best-odds')) {
        $explode = explode('/', get_query_var('best-odds'));
        if(count($explode) == 1) {
          $classes[] = 'single-country';
        }

        elseif(count($explode) == 2) {
          $classes[] = 'single-league';
        }
      }
      return $classes;
    }

    /**
     * Add the custom rewrite rules
     */
    public function add_rewrite_rules() {
      add_rewrite_tag('%event%', '(^[a-z][-a-z0-9\._]*$)');
      add_rewrite_tag('%team%', '(^[a-z][-a-z0-9\._]*$)');

      $slug = get_option('bbf_best_odds_slug');
      // {$slug}/{country}/{league}/{event}
      add_rewrite_rule($slug . '/([^/]*)/([^/]*)/([^/]*)', 'index.php?best-odds=$matches[1]/$matches[2]&event=$matches[3]', 'top');
      // {$slug}/team/{country}
      add_rewrite_rule($slug . '/team/([^/]*)', 'index.php?best-odds=team&team=$matches[1]', 'top');
    }

    /**
     * Parse the request and check for the event tag
     * @param WP $wp
     * @return mixed
     */
    public function parse_request($wp) {
      if(!isset($wp->query_vars)) {
        return;
      }

      $query_vars = $wp->query_vars;
      if(!isset($query_vars['best-odds'])) {
        return;
      }


      if(isset($query_vars['team'])) {

        if(get_page_by_path("team/{$query_vars['team']}", OBJECT, 'best-odds')) {
          $wp->query_vars['best-odds'] = "team/{$query_vars['team']}";
          $wp->query_vars['page'] = '';
          $wp->query_vars['pagename'] = $query_vars['team'];
          $wp->did_permalink = true;
          return $wp;
        }


        $team = bbfootball()->api()->teams()->find($query_vars['team'], array(
          'events' => true,
          'leagues' => true,
          'countries' => true
        ));

        if(!$team) {
          return $wp;
        }

        // Save the response so we can use it later
        $wp->bbfootball = $team;

        add_filter('pre_get_document_title', function($title) use($team) {
          $format = get_option('bbf_team_meta_placeholder');
          // Change the format
          $format = str_replace("%team_name%", $team->name, $format);
          $format = str_replace('%site_name%', get_bloginfo('name'), $format);
          return $format;
        }, 999);

        // If we've made it here then we can change the template
        add_action('template_include', array($this, 'custom_team_template'));
        return $wp;
      }

      if(!isset($query_vars['event'])) {
        return;
      }
      // Validate the event
      $explode = explode('/', $query_vars['best-odds']);
      $event = bbfootball()->api()->events()->find_in_country_league($explode[0], $explode[1], $query_vars['event'], array(
        'league' => true,
        'teams' => true,
        'country' => true,
        'markets' => true,
        'odds' => true
      ));

      if(!$event) {
        do_action('bbfootball_invalid_event');
        return;
      }

      // If we've made it here then we can change the template
      // Save the response so we can use it later
      $wp->bbfootball = $event;

      /// ================
      /// CLEAN UP THE META
      add_filter('pre_get_document_title', function($title) use($event) {
        $format = get_option('bbf_event_meta_placeholder');
        // Change the format
        $format = str_replace("%event_name%", $event->name, $format);
        if(is_numeric(strpos($format, '%country_name%'))) {
          if(isset($event->country)) {
            $format = str_replace('%country_name%', $event->country->name, $format);
          }
          else {
            $format = str_replace('%country_name%', "", $format);
          }
        }

        if(is_numeric(strpos($format, '%league_name%'))) {
          if(isset($event->league)) {
            $format = str_replace('%league_name%', $event->league->name, $format);
          }
          else {
            $format = str_replace('%league_name%', "", $format);
          }
        }

        $format = str_replace('%site_name%', get_bloginfo('name'), $format);
        // var_dump($format);
        return $format;
      }, 999);

      // Ensure the canonical is right
      add_filter( 'wpseo_canonical', function() use($wp) {
        return home_url($wp->request);
      }, 1);

      add_filter('wpseo_opengraph_image', '__return_false');
      add_filter('wpseo_disable_adjacent_rel_links', '__return_true');
      add_filter('wpseo_twitter_image', '__return_false');
      add_filter('wpseo_twitter_card', '__return_false');
      /// END CLEAN UP THE META
      /// ================

      add_action('template_include', array($this, 'custom_event_template'));
      return $wp;
    }

    /**
     * Register the custom widgets
     */
    public function register_widgets() {
      register_widget('BBF_Menu_Widget');
    }

    /**
     * Enqueue the static CSS and JS files
     */
    public function enqueue_scripts() {
      $enqueue_css = (bool) get_option('bbf_enqueue_css');
      if($enqueue_css) {
        wp_enqueue_style('bbfootball', BBF_URL . '/assets/css/bbfootball.min.css');
      }

      $enqueue_js = (bool) get_option('bbf_enqueue_js');
      if($enqueue_js) {
        wp_enqueue_script('bbfootball-vendors', BBF_URL . '/assets/js/bbfootball-vendors.min.js', array('jquery'), null, true);
        wp_enqueue_script('bbfootball', BBF_URL . '/assets/js/bbfootball.js', array('bbfootball-vendors'), null, true);
        wp_localize_script('bbfootball', 'BBF', array(
          'ajax_url' => esc_url(admin_url('admin-ajax.php')),
          'home_url' => esc_url(home_url()),
          'odds_url' => bbfootball_slug('', false),
          'odds_format' => get_option('bbf_odds_format')
        ));
      }
    }

    /**
     * Register the custom post_type
     */
    public function register_post_types() {
      // Get the slug
      $slug = get_option('bbf_best_odds_slug');

      register_post_type('best-odds', array(
        'labels' => array(
          'name' => 'Odds Pages',
          'singular_name' => 'Page'
        ),
        'public' => true,
        'hierarchical' => true,
        'supports' => array(
          'title',
          'editor',
          'page-attributes'
        ),
        'register_meta_box_cb' => array('BBF_Admin', 'bestodds_post_type_meta_box'),
        'has_archive' => true,
        'rewrite' => array(
          'with_front' => false,
          'slug' => $slug
        ),
        'show_in_rest' => true
      ));

      // Private post_type to hold the bookies
      register_post_type('bbf_bookie', array(
        'labels' => array(
          'name' => 'Bookies',
          'singular_name' => 'Bookie',
          'edit_item' => 'Edit Bookie',
          'view_item' => 'View Bookie',
          'view_items' => 'View Bookies',
          'search_items' => 'Search Bookies',
          'not_found' => 'No Bookies found',
          'not_found_in_trash' => 'No Bookies found in Trash',
          'all_items' => 'All Bookies',
          'featured_image' => 'Logo',
          'set_featured_image' => 'Set Logo',
          'remove_featured_image' => 'Remove Logo',
          'use_featured_image' => 'Remove Logo'
        ),
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'rewrite' => false,
        'supports' => array(
          'thumbnail',
          'editor',
          'title'
        ),
        'show_in_menu' => 'edit.php?post_type=best-odds',
        'show_in_rest' => true,
        'capability_type' => 'post',
        'capabilities' => array(
          'create_posts' => false
        ),
        'map_meta_cap' => true,
        'register_meta_box_cb' => array('BBF_Admin', 'bookies_post_type_meta_box')
      ));
    }

    /**
     * Return the custom template for the parsed event request
     * @param string $template
     * @return string
     */
    public function custom_event_template($template) {
      return bbfootball_locate_template("index-event.php");
    }

    /**
     * Return the custom template for the parsed event request
     * @param string $template
     * @return string
     */
    public function custom_team_template($template) {
      return bbfootball_locate_template("index-team.php");
    }

    /**
     * Increase the number of posts on the best-odds post_type
     * @param WP_Query $query
     */
    public function increase_post_count($query) {
      if(!is_admin() && $query->is_main_query() && is_post_type_archive('best-odds')) {
        $query->set('posts_per_page', -1);
        $query->set('post_parent', 0);
      }
    }

    /**
     * API
     */
    public function api() {
      return new BBF_Api();
    }

    /**
     * AJAX request that is called for each element
     */
    public function ajax_element() {
      $output = $this->shortcodes->odds(array(
        'country' => isset($_POST['data']['country']) ? $_POST['data']['country'] : '',
        'league' => isset($_POST['data']['league']) ? $_POST['data']['league'] : '',
        'event' => isset($_POST['data']['event']) ? $_POST['data']['event'] : '',
        'show_title' => isset($_POST['data']['show_title']) ? $_POST['data']['show_title'] : '',
        'event_summary' => isset($_POST['data']['event_summary']) ? $_POST['data']['event_summary'] : '',
        'last_update' => isset($_POST['data']['last_update']) ? $_POST['data']['last_update'] : ''
      ));
      wp_send_json_success($output);
    }

    /**
     * AJAX request to get odds for an event market
     */
    public function ajax_get_market_odds() {
      $event = isset($_POST['data']['event']) ? $_POST['data']['event'] : false;
      $market = isset($_POST['data']['market']) ? $_POST['data']['market'] : false;
      // We need both
      if(!$event || !$market) {
        wp_send_json_error();
      }

      $event = bbfootball()->api()->events()->find($event, array(
        'teams' => true,
        'odds' => true,
        'market' => $market
      ));

      if(!$event) {
        wp_send_json_error();
      }

      ob_start();
      bbfootball_template('event-odds.php', array(
        'odds' => $event->odds
      ));
      $output = ob_get_clean();
      wp_send_json_success($output);
    }

  } // End of BBFootball Class


  /**
   * Return the instance of the BBFootball class
   * @return BBFootball
   */
  function bbfootball() {
    global $bbfootball;
    if(!isset($bbfootball)) {
      $bbfootball = new BBFootball();
      $bbfootball->init();
    }

    return $bbfootball;
  }

  bbfootball();
endif;
