<?php
if(!class_exists('BBF_Admin')) {
  /**
   * Setup the admin area
   */
  class BBF_Admin {
    /**
     * Constructor
     */
    public function __construct() {

      include_once dirname(__FILE__) . '/settings.php';
      $this->settings_page = BBF_Admin_Settings::get_instance();

      add_action('admin_init', array($this, 'maybe_load_everything'));
      add_action('admin_menu', array($this, 'admin_menu'));
      add_action('save_post', array($this, 'save_bookie_options'));
      add_action('save_post', array($this, 'maybe_toggle_status'), 5, 2);
    }

    /**
     * Should we load everything?
     */
    public function maybe_load_everything() {
      if(!get_option('bbf_auto_update')) {
        return;
      }

      // Check for the last update transient
      if(false === ($last_import = get_transient('bbf_last_import'))) {
        $last_import = "true";
        set_transient('bbf_last_import', "false", 10);
      }

      // Stop if we're not importing
      if($last_import !== "true") {
        return;
      }
      // Update the post_type
      // $this->auto_update_post_type();
      // Update the bookies
      $this->auto_update_bookies();
    }

    /**
     * Add items to the admin menu
     */
    public function admin_menu() {
      add_submenu_page('edit.php?post_type=best-odds', 'Settings', 'Settings', 'manage_options', 'best-odds-settings', array($this->settings_page, 'render_page'));
    }

    /**
     * Add a custom meta box for the best-odds post_type
     * @return void
     */
    public static function bestodds_post_type_meta_box() {
      add_meta_box('bbf_debug', 'Debug', function(WP_Post $post) {
        include_once dirname(__FILE__) . '/views/country-debug.php';
      }, 'best-odds', 'side');
    }

    /**
     * Add a custom meta box for the best-odds post_type
     * @return void
     */
    public static function bookies_post_type_meta_box() {
      add_meta_box('bookie', 'Bookie', function(WP_Post $post) {
        include_once dirname(__FILE__) . '/views/bookie-form.php';
      }, 'bbf_bookie', 'advanced', 'high');
    }

    /**
     * This function is triggered automatically and will update the entries
     * in the best-odds post_type
     */
    private function auto_update_post_type() {
      // Get the countries and include the leagues
      $countries = bbfootball()->api()->countries()->find_all(array(
        'leagues' => true,
        'hide_empty' => true
      ));

      if(!$countries) {
        return;
      }
      // Database object
      global $wpdb;

      // Empty array to hold number of updates
      $updates = array();
      // Check if the countries exist and create them if they don't
      foreach($countries as $country) {
        $post_name = $country->slug;
        $sql = "SELECT
                  ID
                FROM
                  $wpdb->posts
                WHERE
                  1=1 AND
                  post_type = 'best-odds' AND
                  post_name = '{$post_name}'";
        $country_id = (int) $wpdb->get_var($sql);

        // If there's no post - create it
        if(!$country_id) {
          $country_id = wp_insert_post(array(
            'post_title' => $country->name,
            'post_name' => $post_name,
            'post_type' => 'best-odds',
            'post_status' => 'publish',
            'post_content' => "[odds country=\"{$post_name}\"]",
            'meta_input' => array(
              'bbf' => $country,
              'bbf_country' => $country->id,
              'bbf_country_name' => $country->name,
              'bbf_flag' => (isset($country->flag)) ? $country->flag : '',
              '_yoast_wpseo_title' => "Best {$country->name} football odds %%sep%% %%sitename%%",
              '_yoast_wpseo_focuskw' => $country->name,
              '_yoast_wpseo_metadesc' => "Best {$country->name} football odds comparisons and free bets"
            )
          ));
          $updates[] = $country_id;
        }

        // Check the nested leagues array to see if they exist
        if(isset($country->leagues)) {
          foreach($country->leagues as $league) {
            $league_name = $league->slug;
            // Same query as above but check the post parent id
            $sql = "SELECT
                      ID
                    FROM
                      $wpdb->posts
                    WHERE
                      1=1 AND
                      post_type = 'best-odds' AND
                      post_name = '{$league_name}' AND
                      post_parent = {$country_id}";

            $league_id = (int) $wpdb->get_var($sql);
            // If it's not created - create it
            if(!$league_id) {
              $league_id = wp_insert_post(array(
                'post_title' => $league->name,
                'post_name' => $league_name,
                'post_type' => 'best-odds',
                'post_status' => 'publish',
                'post_content' => "[odds country=\"{$post_name}\" league=\"{$league_name}\"]",
                'post_parent' => $country_id,
                'meta_input' => array(
                  'bbf' => $league,
                  'bbf_country' => $country->id,
                  'bbf_country_name' => $country->name,
                  'bbf_flag' => (isset($league->flag)) ? $league->flag : '',
                  'bbf_league' => $league->id,
                  'bbf_league_name' => $league->name,
                  '_yoast_wpseo_title' => "Best {$country->name} {$league->name} football odds %%sep%% %%sitename%%",
                  '_yoast_wpseo_focuskw' => "{$country->name} {$league->name}",
                  '_yoast_wpseo_metadesc' => "Best {$country->name} {$league->name} football odds comparisons and free bets"
                )
              ));
              $updates[] = $league_id;
            }
          }
        }
      }

      // If the updates array is not empty - tell the user we've updated
      if(!empty($updates)) {
        add_action('admin_notices', function() use($updates) {
      ?>
      <div class="notice">
        <p>Some pages were automatically updated.</p>
        <p><?php echo implode(',', $updates); ?></p>
      </div>
      <?php
        });
      }
    }

    /**
     * This function is triggered automatically and will update the
     * bookies with any new entries
     */
    private function auto_update_bookies() {
      // Get the bookies
      $bookies = bbfootball()->api()->bookies()->find_all();
      if(!$bookies) {
        return;
      }

      global $wpdb;
      // Empty array to hold number of updates
      $updates = array();
      // Check if the countries exist and create them if they don't
      foreach($bookies as $bookie) {
        $sql = "SELECT
                  ID
                FROM
                  $wpdb->posts
                WHERE
                  1=1 AND
                  post_type = 'bbf_bookie' AND
                  post_title = '{$bookie->name}'";
        $bookie_id = (int) $wpdb->get_var($sql);
        // If there's no bookie - create it
        if(!$bookie_id) {
          $bookie_id = wp_insert_post(array(
            'post_title' => $bookie->name,
            'post_type' => 'bbf_bookie',
            'post_status' => $bookie->active ? 'publish' : 'draft'
          ));
          $updates[] = $bookie_id;
        }
      }

      // If the updates array is not empty - tell the user we've updated
      if(!empty($updates)) {
        add_action('admin_notices', function() use($updates) {
      ?>
      <div class="notice">
        <p>Some bookies were automatically updated.</p>
        <p><?php echo implode(',', $updates); ?></p>
      </div>
      <?php
        });
      }
    }

    /**
     * Update the bookies options when the post is saved
     */
    public function save_bookie_options() {
      if(!isset($_POST['post_type']) || $_POST['post_type'] !== "bbf_bookie") {
        return;
      }

      $affiliate_url = isset($_POST['affiliate_url']) ? $_POST['affiliate_url'] : '';
      update_post_meta($_POST['post_ID'], 'affiliate_url', $affiliate_url);
    }

    /**
     * Maybe update the stats of the bookie post
     * @param int $post_ID
     * @param WP_POST $post
     */
    public function maybe_toggle_status($post_ID, $post) {
      if(!isset($post->post_type) || $post->post_type !== 'bbf_bookie') {
        return;
      }

      if($post->post_status !== "publish") {
        $disable = bbfootball()->api()->bookies()->deactivate($post->post_name);
      }
      else {
        $enable = bbfootball()->api()->bookies()->activate($post->post_name);
      }

      return;
    }


  }

  bbfootball()->admin = new BBF_Admin();
}
