<?php
class BBF_Menu_Widget extends WP_Widget {
  /**
   * Set up a new widget instance
   */
  public function __construct() {
    parent::__construct('bbfootball_menu_widget', 'Odds Menu', array(
      'description' => 'Add a list of odds countries and leagues',
      'customize_selective_refresh' => true,
		));
  }

  /**
   * Output the widget
   * @param array $args
   * @param array $instance
   */
  public function widget($args, $instance) {
    $country = isset($instance['country']) ? $instance['country'] : false;
    echo $args['before_widget'];
  ?>
  <ul class="bbfootball-accordion-menu" data-accordion-menu data-submenu-toggle="true">
    <?php
    if(isset($instance['country'])) {
      $countries = get_post($instance['country']);
      if($countries) {
        $countries->leagues = get_children(array(
          'post_parent' => $countries->ID,
          'posts_per_page' => -1,
          'post_status' => 'publish',
          'order' => 'ASC'
        ));
      }
    }
    else {
      $posts_args = array(
        'post_type' => 'best-odds',
        'posts_per_page' => -1,
        'post_parent' => 0,
        'orderby' => 'menu_order',
        'order' => 'ASC'
      );
      $countries = get_posts($posts_args);
    }

    // If countries is an array
    if(is_array($countries)) {
      foreach($countries as $country) {
        $this->output_country_item($country);
      }
    }
    elseif($countries instanceof WP_Post) {
      $this->output_country_item($countries);
    }

    ?>
  </ul>
  <?php
    echo $args['after_widget'];
  }

  /**
   * Output the country item
   */
  private function output_country_item($country) {
?>
<li class="has-submenu">
  <a href="<?php echo get_the_permalink($country->ID); ?>">
    <?php echo $country->post_title; ?>
  </a>
  <?php
    if(isset($country->leagues)) {
  ?>
                                        <!--        Here is side bar                       -->
  <ul class="children submenu">
  <?php foreach($country->leagues as $league) { //print_r($league); ?>
    <li><a href="<?php echo get_the_permalink($league->ID); ?>"><?php echo $league->post_title; ?></a></li>
  <?php } ?>
  </ul>
  <?php } ?>
</li>
<?php
  }

  /**
   * Output the options for the widget
   */
  public function form($instance) {
    $instance = wp_parse_args($instance, array(
      'names' => 'titles',
      'country' => 'ALL'
    ));
  ?>
  <p>
    <label for="<?php echo esc_attr($this->get_field_id('names')); ?>">Display names</label>
    <select name="<?php echo esc_attr($this->get_field_name('names')); ?>" id="<?php echo esc_attr($this->get_field_id('names')); ?>" class="widefat">
      <option value="titles"<?php selected($instance['names'], 'titles'); ?>>Current Titles</option>
      <option value="names"<?php selected($instance['names'], 'names'); ?>>Original Names</option>
    </select>
  </p>
  <p>
    <label for="<?php echo esc_attr($this->get_field_id('country')); ?>">Countries to Include</label>
    <select name="<?php echo esc_attr($this->get_field_name('country')); ?>" id="<?php echo esc_attr($this->get_field_id('country')); ?>" class="widefat">
      <option value="ALL"<?php selected($instance['country'], 'ALL'); ?>>Show ALL Countries</option>
      <?php
        $countries = get_posts(array(
          'post_type' => 'best-odds',
          'post_parent' => 0,
          'post_status' => 'publish',
          'posts_per_page' => -1
        ));
        if($countries) { foreach($countries as $country) { ?>
        <option value="<?php echo $country->ID; ?>"<?php selected($instance['country'], $country->ID); ?>>
          <?php echo $country->post_title; ?>
        </option>
        <?php } } ?>
    </select>
  </p>
  <?php
  }

  /**
   * Update the form
   */
  public function update($new_instance, $old_instance) {
    $instance = $old_instance;
    if(in_array($new_instance['names'], array('names', 'titles'))) {
      $instance['names'] = $new_instance['names'];
    }
    else {
      $instance['names'] = 'titles';
    }

    $instance['country'] = $new_instance['country'];
    return $instance;
  }

}


class BBF_Menu_Widget_Walker extends Walker_Page {

  public function start_el(&$output, $page, $depth = 0, $args = array(), $current_page = 0) {
    // This is copied from the Walker_Page method
    if ( isset( $args['item_spacing'] ) && 'preserve' === $args['item_spacing'] ) {
			$t = "\t";
			$n = "\n";
		} else {
			$t = '';
			$n = '';
		}
		if ( $depth ) {
			$indent = str_repeat( $t, $depth );
		} else {
			$indent = '';
		}

    // Here's the first change - remove all the classes
    $css_class = array();
    if (isset($args['pages_with_children'][$page->ID])) {
			$css_class[] = 'has-submenu';
		}
		if (!empty($current_page)) {
			$_current_page = get_post($current_page);
			if ($page->ID == $current_page || ($_current_page && in_array($page->ID, $_current_page->ancestors))) {
				$css_class[] = 'is-active';
			}
		}
    // Get the flag (if found)
    $flag = get_post_meta($page->ID, 'bbf_flag', true);
    if($flag) {
      $css_class[] = 'has-flag';
      $flag = "<span class=\"bbfootball-flag\"><img src=\"{$flag}\" alt=\"{$page->post_title}\" /></span>";
      $args['link_before'] = isset($flag) ? $flag . $args['link_before'] : $args['link_after'];
    }
    $css_classes = implode( ' ', apply_filters( 'page_css_class', $css_class, $page, $depth, $args, $current_page ) );

    if ( '' === $page->post_title ) {
			/* translators: %d: ID of a post */
			$page->post_title = sprintf( __( '#%d (no title)' ), $page->ID );
		}

    if(isset($args['names']) && $args['names'] == "names") {
      if($page->post_parent == 0) {
        $page->post_title = get_post_meta($page->ID, 'bbf_country_name', true);
      }
      else {
        $page->post_title = get_post_meta($page->ID, 'bbf_league_name', true);
      }
    }

		$args['link_before'] = empty( $args['link_before'] ) ? '' : $args['link_before'];
		$args['link_after'] = empty( $args['link_after'] ) ? '' : $args['link_after'];

		$atts = array();
		$atts['href'] = get_permalink( $page->ID );

    $attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( ! empty( $value ) ) {
				$value = esc_attr( $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		$output .= $indent . sprintf(
			'<li class="%s"><a%s>%s%s%s</a>',
			$css_classes,
			$attributes,
			$args['link_before'],
			/** This filter is documented in wp-includes/post-template.php */
			apply_filters( 'the_title', $page->post_title, $page->ID ),
			$args['link_after']
		);
  }
}
