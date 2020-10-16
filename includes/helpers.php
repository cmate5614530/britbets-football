<?php

/**
 * Locate and include a template
 * @param mixed $template_name
 * @param array $args
 * @return void
 */
function bbfootball_template($template_name, $args = array()) {
  if($args && is_array($args)) {
    // Yuk!
    extract($args);
  }
  include bbfootball_locate_template($template_name);
}

/**
 * Locate a template and return the path
 * @param string $template_name
 * @return string
 */
function bbfootball_locate_template($template_name) {
  // The theme has priority over the plugin
  $template = locate_template(array(
    "templates/{$template_name}",
    "template-parts/{$template_name}",
    $template_name
  ));

  // If we didn't find anything in the theme directory - check the plugin
  if(!$template) {
    $path = BBF_PATH . "/templates/";
    if(file_exists("{$path}/{$template_name}")) {
      $template = "{$path}/{$template_name}";
    }
  }

  return apply_filters('bbfootball_template', $template, $template_name);
}


/**
 * Return the slug to the odds area
 * @param string $append
 * @param bool $full_url
 * @return string
 */
function bbfootball_slug($append = "", $full_url = true) {
  $base_url = get_option('bbf_best_odds_slug');
  if($full_url) {
    return home_url("{$base_url}/{$append}");
  }

  return "{$base_url}/{$append}";
}

/**
 * Return the current event response
 */
function the_event() {
  global $wp;
  if(!isset($wp->bbfootball)) {
    return false;
  }

  bbfootball_template('event.php', array(
    'event' => $wp->bbfootball
  ));
}

/**
 * Return the current team response
 */
function the_team() {
  global $wp;
  if(!isset($wp->bbfootball)) {
    return false;
  }

  bbfootball_template('team.php', array(
    'team' => $wp->bbfootball
  ));
}

/**
 * Returns a bookie button
 * @param string $bookie
 * @param array $args
 * @return string
 */
function bbfootball_bookie($bookie, $args = array()) {
  $args = wp_parse_args($args, array(
    'text' => '',
    'show_image' => true,
    'odds' => array()
  ));

  if($bookie instanceof WP_Post) {
    $post = $bookie;
  }
  else {
    // Get the bookie post
    $post = get_page_by_title($bookie, OBJECT, 'bbf_bookie');
    if(!$post) {
      return $bookie;
    }
  }



  ob_start();
?>
<div class="bbfootball-bookie" data-bookie="<?php echo $bookie->post_title; ?>">
  <a href="<?php echo get_post_meta($post->ID, 'affiliate_url', true); ?>" target="_blank" rel="nofollow">
    <?php if($args['show_image']) { ?>
    <div class="logo">
      <?php
        if(!get_the_post_thumbnail($post->ID)) {
          echo $bookie->post_title;
        }
        else {
          echo get_the_post_thumbnail($post->ID);
        }
      ?>
    </div>
    <?php } ?>
    <?php if($args['text']) { ?>
    <span><?php echo $args['text']; ?></span>
    <?php } elseif(!empty($args['odds'])) { ?>
    <div class="bookie-odds">
      <?php $current = get_option('bbf_odds_format'); ?>
      <?php foreach($args['odds'] as $key => $val) { ?>
      <span class="<?php echo $key; ?> <?php echo ($key == $current) ? '' : 'hide'; ?>"><?php echo $val; ?></span>
      <?php } ?>
    </div>
    <?php } ?>
  </a>
</div>
<?php
  return ob_get_clean();
}

/**
 * Generate the best odds from an array of odds
 * @param array $odds
 * @return array
 */
function bbfootball_best_odds($odds = array()) {
  foreach($odds as $_temp_odds) {
    if(isset($best_odds->decimal_odds)) {
      if($best_odds->decimal_odds < $_temp_odds->decimal_odds) {
        $best_odds = $_temp_odds;
      }
    }
    else {
      $best_odds = $_temp_odds;
    }
  }
  return $best_odds;
}

/**
 * Create an ajax element for the JS to use
 * @param array $args
 * @return string
 */
function bbfootball_ajax_elemet($args = array()) {
  $args = wp_parse_args($args, array(
    'class' => '',
    'id' => '',
    'text' => '',
    'attributes' => array()
  ));

  // Build the attributes string
  $attributes = '';
  foreach($args['attributes'] as $key => $val)  {
    if(!in_array($key, array('class', 'id', 'ajax'))) {
      $attributes .= "{$key}=\"{$val}\" ";
    }
  }

  ob_start();
?>
<div class="bbfootball-ajax"<?php echo $attributes; ?>>
  <?php echo $args['text']; ?>
</div>
<?php
  return ob_get_clean();
}



  /**
   * Convert decimal odds to fractional odds
   * @param mixed $decimal
   */
  function bbfootball_decimal_to_fractional($decimal) {
    if($decimal == 1) {
      return "1/1";
    }
    $decimal = number_format( $decimal, 2 );
    $num     = ( $decimal - 1 ) * 1000;
    $dom     = 1000;
    $num = round( $num );
    $dom = round( $dom );
    $a   = _bbf_reduce( $num, $dom );
    $num = $a[0];
    $dom = $a[1];
    return ( $num . '/' . $dom );
  }

  function _bbf_reduce($a, $b) {
    $n    = [];
    $f    = _bbf_gcd( $a, $b );
    $n[0] = $a / $f;
    $n[1] = $b / $f;
    return $n;
  }
  function _bbf_gcd($a, $b) {
    return ( $a % $b ) ? _bbf_gcd( $b, $a % $b ) : $b;
  }
