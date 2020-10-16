<?php
/**
 * This template is used in the Debug meta box
 */
?>
<div class="bbfootball-debug">
  <?php
    $meta = get_post_meta($post->ID);
    foreach($meta as $key => $val) {
      $meta[$key] = $val[0];
    }
    switch (get_post_type()) {
      case 'best-odds':
    ?>
    <p><label>Country:</label>
      <?php echo isset($meta['bbf_country']) ? $meta['bbf_country'] : ''; ?>
    </p>
    <p><label>Country Shortname:</label>
      <?php echo isset($meta['bbf_short_name']) ? $meta['bbf_short_name'] : ''; ?>
    </p>
    <p><label>Flag:</label>
      <img src="<?php echo isset($meta['bbf_flag']) ? $meta['bbf_flag'] : ''; ?>" width="30" />
    </p>
    <?php if(isset($meta['bbf_league'])) { ?>
      <p><label>League:</label>
        <?php echo isset($meta['bbf_league']) ? $meta['bbf_league'] : ''; ?>
      </p>
    <?php } ?>
    <?php
        break;
    }
    ?>
</div>
