<?php

class BBF_Admin_Settings {
  /**
   * Holds the current instance of the class
   * @var null
   */
  private static $instance = null;
  /**
   * Holds the current settings
   * @var array
   */
  protected $settings = array();

  /**
   * Return the instance of the class
   * @return BBF_Admin_Settings
   */
  public static function get_instance() {
    if(is_null(self::$instance)) {
      self::$instance = new self();
    }
    return self::$instance;
  }

  /**
   * Class constructor
   */
  public function __construct() {
    $this->settings_group = 'bbfootball';
    add_action('admin_init', array($this, 'register_settings'));
  }

  /**
   * Init the settings
   */
  protected function init_settings() {
    $this->settings = array(
      'general' => array(
        'General', array(
          array(
            'name' => 'bbf_best_odds_slug',
            'label' => 'Slug',
            'type' => 'text',
            'std' => 'best-odds',
            'description' => 'Change the page to the best odds area'
          ),
          array(
            'name' => 'bbf_auto_update',
            'std' => 1,
            'label' => 'Auto Import',
            'type' => 'checkbox',
            'description' => 'Automatically import countries, leagues and bookies from the feed.'
          ),
          array(
            'name' => 'bbf_enqueue_css',
            'std' => 1,
            'label' => 'Enqueue CSS',
            'type' => 'checkbox',
            'description' => 'Enqueue the CSS on the frontend.'
          ),
          array(
            'name' => 'bbf_enqueue_js',
            'std' => 1,
            'label' => 'Enqueue JS',
            'type' => 'checkbox',
            'description' => 'Enqueue the JS on the frontend.'
          ),
          array(
            'name' => 'bbf_odds_format',
            'std' => 'fractional',
            'label' => 'Odds format',
            'type' => 'select',
            'description' => 'The default odds format.',
            'options' => array(
              'fractional' => 'Fractional',
              'decimal' => 'Decimal'
            )
          ),
          array(
            'name' => 'bbf_event_meta_placeholder',
            'label' => 'Event Titles',
            'type' => 'text',
            'std' => '%event_name% | %country_name% %league_name% | %site_name%',
            'description' => 'Change the default title for the dynamic event pages'
          ),
          array(
            'name' => 'bbf_team_meta_placeholder',
            'label' => 'Team Titles',
            'type' => 'text',
            'std' => '%team_name% | %site_name%',
            'description' => 'Change the default title for the dynamic team pages'
          )
        )
      )
    );
  }

  /**
   * Register the plugin settings with WordPress
   */
  public function register_settings() {
    $this->init_settings();
    foreach($this->settings as $section) {
      foreach($section[1] as $option) {
        if(isset($option['std'])) {
          add_option($option['name'], $option['std']);
        }
        register_setting($this->settings_group, $option['name']);
      }
    }
  }

  /**
   * Render the settings page
   */
  public function render_page() {
    $this->init_settings();
  ?>
  <div class="wrap" id="bbf-settings-wrap">
    <form accept-charset="bbf-options" method="post" action="options.php">
      <?php settings_fields($this->settings_group); ?>
      <h2 class="nav-tab-wrapper">
        <?php foreach($this->settings as $key => $section) { ?>
        <a href="#setting-<?php echo esc_attr(sanitize_title($key)); ?>" class="nav-tab"><?php echo esc_html($section[0]); ?></a>
        <?php } ?>
      </h2>

      <?php
        // If the settings are updated
        if(!empty($_GET['settings-updated'])) {
          flush_rewrite_rules();
        ?>
        <div class="updated"><p>Settings successfully saved.</p></div>
        <?php } ?>

        <?php
          foreach($this->settings as $key => $section) {
            $section_args = isset($section[2]) ? (array) $section[2] : array();
          ?>
          <div class="settings_panel" id="setting-<?php echo esc_attr(sanitize_title($key)); ?>">
            <table class="form-table settings parent-settings">
              <?php
                foreach($section[1] as $option) {
                  $value = get_option($option['name']);
              ?>
              <tr valign="top" class="">
                <?php if(!empty($option['label'])) { ?>
                <th scope="row">
                  <label for="setting-<?php echo esc_attr($option['name']); ?>">
                    <?php echo esc_html($option['label']); ?>
                  </label>
                </th>
                <td>
                <?php } else { ?>
                <td colspan="2">
                <?php
                  }

                  $method = "input_{$option['type']}";
                  if(method_exists($this, $method)) {
                    $this->$method($option, $value);
                  }
                ?>
                </td>
              </tr>
              <?php
                }
              ?>
            </table>
          </div>
          <?php
          }
        ?>
        <p class="submit">
          <input type="submit" class="button-primary" value="Save Changes" />
        </p>
    </form>
  </div>
  <?php
  }

  /**
   * Text field
   * @param array $option
   * @param mixed $value
   */
  protected function input_text($option, $value) {
  ?>
    <input
      type="text"
      class="regular-text"
      id="setting-<?php echo esc_attr($option['name']); ?>"
      name="<?php echo $option['name']; ?>"
      value="<?php echo esc_attr($value); ?>"
    />
  <?php
    if(!empty($option['description'])) {
      echo '<p class="desription">' . wp_kses_post($option['description']) . '</p>';
    }
  }

  /**
   * Checkbox field
   * @param array $option
   * @param mixed $value
   */
  protected function input_checkbox($option, $value) {
  ?>
    <label>
      <input type="hidden" name="<?php echo esc_attr($option['name']); ?>" value="0" />
      <input
        type="checkbox"
        name="<?php echo esc_attr($option['name']); ?>"
        value="1"
        id="setting-<?php echo esc_attr($option['name']); ?>"
        <?php checked('1', $value); ?>
      />
    </label>
    <?php
      if(!empty($option['description'])) {
        echo '<p class="desription">' . wp_kses_post($option['description']) . '</p>';
      }
  }

  /**
   * Select field
   * @param array $option
   * @param mixed $value
   */
   protected function input_select($option, $value) {
  ?>
  <select
    id="setting-<?php echo esc_attr($option['name']); ?>"
    class="regular-text"
    name="<?php echo esc_attr($option['name']); ?>"
  >
  <?php
    foreach($option['options'] as $key => $name) {
      echo '<option value="' . esc_attr($key) . '" ' . selected($value, $key, false) . '>' . esc_html($name) . '</option>';
    }
  ?>
  </select>
  <?php
    if(!empty($option['description'])) {
      echo '<p class="desription">' . wp_kses_post($option['description']) . '</p>';
    }
   }
}
