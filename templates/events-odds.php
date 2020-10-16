<ul>
  <li>
    <?php
    if(isset($event->odds)) {
      $string = "1";
      if(isset($event->odds->outcomes->{$string})) {
        echo bbfootball_bookie($event->odds->outcomes->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
      elseif($event->odds->{$string}) {
        echo bbfootball_bookie($event->odds->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
    }
    ?>
  </li>
  <li>
    <?php
    if(isset($event->odds)) {
      $string = "X";
      if(isset($event->odds->outcomes->{$string})) {
        echo bbfootball_bookie($event->odds->outcomes->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
      elseif($event->odds->{$string}) {
        echo bbfootball_bookie($event->odds->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
    }
    ?>
  </li>
  <li>
    <?php
    if(isset($event->odds)) {
      $string = "2";
      if(isset($event->odds->outcomes->{$string})) {
        echo bbfootball_bookie($event->odds->outcomes->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
      elseif($event->odds->{$string}) {
        echo bbfootball_bookie($event->odds->{$string}->bookie->name, array(
          'show_image' => true,
          'odds' => array(
            'decimal' => $event->odds->outcomes->{$string}->price,
            'fractional' => bbfootball_decimal_to_fractional($event->odds->outcomes->{$string}->price)
          )
        ));
      }
    }
    ?>
  </li>
  <li>
    <?php
      if(isset($event->odds)) {
        $slug = isset($event->slug) && is_numeric(strpos($event->slug, '/')) ? $event->slug : "{$event->country->slug}/{$event->league->slug}/{$event->slug}";
    ?>
    <a href="<?php echo bbfootball_slug($slug); ?>" class="button expanded small">
      <span class="alignright align-right dashicons dashicons-arrow-right-alt2" style="font-size:12px;"></span>
      All Odds
    </a>
    <?php
      }
    ?>
  </li>
</ul>
