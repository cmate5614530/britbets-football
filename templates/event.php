<?php
/**
 * Display a country
 */

// We need an $event variable
if(!isset($event)) {
  return;
}
?>
<div class="bbfootball-event-holder">
  <article <?php post_class(); ?>>
    <header class="entry-header">
      <div class="grid-x">
        <div class="small-12 large-9 cell">
          <h1>
            <a href="<?php echo bbfootball_slug("team/{$event->teams->home_team->slug}"); ?>"><?php echo $event->teams->home_team->name; ?></a>
            vs
            <a href="<?php echo bbfootball_slug("team/{$event->teams->away_team->slug}"); ?>"><?php echo $event->teams->away_team->name; ?></a>
          </h1>
          <ul class="event-meta">
            <li><span class="dashicons dashicons-calendar-alt"></span> <?php echo $event->date; ?></li>
            <li><span class="dashicons dashicons-clock"></span> <?php echo $event->time; ?></li>
            <?php if(isset($event->country)) { ?>
            <li><a href="<?php echo bbfootball_slug($event->country->slug); ?>"><span class="dashicons dashicons-location"></span> <?php echo $event->country->name; ?></a></li>
            <?php } ?>
            <?php if(isset($event->league)) { ?>
            <li><a href="<?php echo bbfootball_slug("{$event->country->slug}/{$event->league->slug}"); ?>"><?php echo $event->league->name; ?></a></li>
            <?php } ?>
          </ul>
        </div>
        <div class="small-12 large-3 cell">
          <div class="markets-dropdown-button">
            <button class="button expanded" data-toggle="markets-dropdown">
              Available Markets
              <span class="dashicons dashicons-arrow-down-alt2"></span>
            </button>
            <div class="dropdown-pane" id="markets-dropdown" data-dropdown>
              <ul data-event-markets>
              <?php foreach($event->available_markets as $market) { ?>
              <li><a href="" data-event="<?php echo $event->id; ?>" data-market="<?php echo $market->id; ?>"><?php echo $market->name; ?></a></li>
              <?php } ?>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </header>
    <div class="entry-content">
      <div data-event-odds>
      <?php
        if(isset($event->odds)) {
          bbfootball_template('event-odds.php', array(
            'odds' => $event->odds
          ));
        }
        else {
          do_action('bbfootball_shortcode_no_odds');
        }
      ?>
      </div>
      <hr />
      <div class="markets-dropdown-button">
        <button class="button expanded" data-toggle="markets-dropdown2">
          Click here for the latest odds on additional Markets
          <span class="dashicons dashicons-arrow-down-alt2"></span>
        </button>
        <div class="dropdown-pane" id="markets-dropdown2" data-dropdown>
          <ul data-event-markets>
          <?php foreach($event->available_markets as $market) { ?>
          <li><a href="" data-event="<?php echo $event->id; ?>" data-market="<?php echo $market->id; ?>"><?php echo $market->name; ?></a></li>
          <?php } ?>
          </ul>
        </div>
      </div>
<!--      preview here bright start-->
        <?php
        $team_name = $event->teams->home_team->name;
        if(isset($event->country) && isset($event->league)) {
            $country_name = $event->country->name;
            $league_name = $event->league->name;

            $cURLConnection = curl_init();

            curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/leagues');
            curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
                'x-api-key: LSVkd6mC969RA3Ph88yx58V5X6hWozGcay6RBMHr'
            ));
            $leagueListRes = curl_exec($cURLConnection);
            $leagueResponse = json_decode($leagueListRes);
            $leagueList = $leagueResponse->leagues; //league list from curl
            for($i = 0; $i < sizeof($leagueList); $i++ ){
                if($leagueList[$i]->name == $league_name && $leagueList[$i]->country == $country_name){
                    $leagueId = $leagueList[$i]->id;
                }
            }
            if(isset($leagueId)){
                curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/teams/'.$leagueId);
                $teamListRes = curl_exec($cURLConnection);
                $teamResponse = json_decode($teamListRes);
                $teamList = $teamResponse->teams; //team list by leagueid
                for($j = 0; $j < sizeof($teamList); $j ++){
                    if($teamList[$j]->name == $team_name && $teamList[$j]->country == $country_name){
                        $teamId = $teamList[$j]->id;
                    }
                }
            }
            if(isset($teamId)){
                curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/matchPreview/'.$teamId);
                $previewListRes = curl_exec($cURLConnection);
                $previewResponse = json_decode($previewListRes);
                $preview = $previewResponse->blurb_full;
                echo $preview;
            }
            curl_close($cURLConnection);

        }
        ?>
<!--      preview here bright end   -->
    </div>
  </article>
</div>
