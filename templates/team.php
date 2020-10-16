<?php
/**
 * Display a country
 */

// We need an $team variable
if(!isset($team, $team->id)) {
  return;
}

$stats = bbfootball()->api()->teams()->get_stats($team->id);
?>
<div class="bbfootball-team-holder">
  <article <?php post_class(); ?>>
    <?php if(!isset($atts) || $atts['show_title']) { ?>
    <header class="entry-header">
      <h1><?php echo $team->name; ?></h1>
    </header>
    <?php } ?>
    <div class="entry-content">
        <!--        bright start 7/30/2020-->

        <?php
        //print_r($team);exit;
        $season = $team->leagues[0]->season;
        //var_dump($season) ;exit;
        //echo $team->id;
        $cURLConnection = curl_init();
        curl_setopt($cURLConnection, CURLOPT_URL, 'https://soccer.sportmonks.com/api/v2.0/standings/season/'.$season.'?api_token=xAvBO16VMxSnppwPhH55iu7MQZVTVq39WbD7JAUHNO9sHqTFqm88KUM2Lp0f');
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        $standingRes = curl_exec($cURLConnection);
        $standing = json_decode($standingRes);
        curl_setopt($cURLConnection, CURLOPT_URL, 'https://soccer.sportmonks.com/api/v2.0/topscorers/season/'.$season.'?api_token=xAvBO16VMxSnppwPhH55iu7MQZVTVq39WbD7JAUHNO9sHqTFqm88KUM2Lp0f');
        $topScorerRes = curl_exec($cURLConnection);
        $topScorer = json_decode($topScorerRes);
        //print_r($topScorer->data->goalscorers->data);

        if($standing!==null && sizeof(json_decode(json_encode($standing), true))>0){
            ?>
            <p style="font-size: large; font-weight: bold;">Standing</p>
            <div class="grid-x grid-padding-x">
                <div class="small-12 large-6 cell">
                    <?php
                    if(sizeof($standing->data)){
                    $standing_array = $standing->data[0]->standings->data;
//                    var_dump($standing_array[0]->team_id);
//                    var_dump($team->id);
//                    exit;
                        for ($i = 0; $i < sizeof($standing_array) / 2; $i++) {
                            ?>
                            <p <?php if ($standing_array[$i]->team_id == intval($team->id)){echo " style='color:hsl(355, 100%, 33%);'";}  ?>><?php echo $standing_array[$i]->position; ?>
                                . <?php echo $standing_array[$i]->team_name; ?></p>
                            <?php
                        }
                    ?>
                </div>
                <div class="small-12 large-6 cell">
                    <?php
                        for ($j = ceil(sizeof($standing_array) / 2); $j < sizeof($standing_array); $j++) {
                            ?>
                            <p <?php if ($standing_array[$j]->team_id == intval($team->id)){echo " style='color:hsl(355, 100%, 33%);'";}  ?>><?php echo $standing_array[$j]->position; ?>
                                . <?php echo $standing_array[$j]->team_name; ?></p>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        <?php
        }
        if($topScorer!==null && sizeof(json_decode(json_encode($topScorer), true))>0){
            ?>
            <p style="font-size: large; font-weight: bold;">Top Scorer of <?php echo $team->name;?></p>
            <?php
            $goalscorer_array =  $topScorer->data->goalscorers->data;
            //print_r($goalscorer_array);
            foreach ($goalscorer_array as $item1) {
                if ($item1->team_id == $team->id) {
                    $player_id = $item1->player_id;
                    break;
                }
            }
            if(isset($player_id)){
                curl_setopt($cURLConnection, CURLOPT_URL, 'https://soccer.sportmonks.com/api/v2.0/players/'.$player_id.'?api_token=xAvBO16VMxSnppwPhH55iu7MQZVTVq39WbD7JAUHNO9sHqTFqm88KUM2Lp0f');
                $topScorerResp = curl_exec($cURLConnection);
                curl_close($cURLConnection);
                $topScorerResult = json_decode($topScorerResp);
                //print_r($topScorerResult);
            }
            ?>
            <p style="padding-left: 3%;">Name : <?php echo $topScorerResult->data->display_name;?></p>
            <p style="padding-left: 3%;">Nationality : <?php echo $topScorerResult->data->nationality;?></p>
            <p style="padding-left: 3%;">Birthday : <?php echo $topScorerResult->data->birthdate;?></p>
            <p style="padding-left: 3%;">Birth Country : <?php echo $topScorerResult->data->birthcountry;?></p>
            <p style="padding-left: 3%;">Height : <?php echo $topScorerResult->data->height;?></p>
            <p style="padding-left: 3%;">Weight : <?php echo $topScorerResult->data->weight;?></p>
            <?php

            ?>
            <?php
        }
        ?>

        <!--        bright end 7/30/2020-->
<!--        <div style="padding-left: 2%; padding-right: 2%;">-->
<!--      --><?php
////      bright start 7/24/2020
//      $team_name = $team->name;
//      $team_events = $team->events;
//
//      $cURLConnection = curl_init();
//      curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/leagues');
//      curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
//      curl_setopt($cURLConnection, CURLOPT_HTTPHEADER, array(
//          'x-api-key: LSVkd6mC969RA3Ph88yx58V5X6hWozGcay6RBMHr'
//      ));
//      $leagueListRes = curl_exec($cURLConnection);
//      $leagueResponse = json_decode($leagueListRes);
//      $leagueList = $leagueResponse->leagues; //league list from curl
//
//      for($ind = 0; $ind < sizeof($team_events); $ind++){
//          $league_name = $team_events[$ind]->league->name;
//          $country_name = $team_events[$ind]->country->name;
//          //print_r($league_name); print_r($country_name);exit;
//
//          for($i = 0; $i < sizeof($leagueList); $i++ ){
//              if($leagueList[$i]->name == $league_name && $leagueList[$i]->country == $country_name){
//                  $leagueId = $leagueList[$i]->id;
//              }
//          }
//          if((isset($leagueId) && !isset($leagueId_old)) || (isset($leagueId) && isset($leagueId_old) && $leagueId != $leagueId_old)){
//              curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/teams/'.$leagueId);
//              $teamListRes = curl_exec($cURLConnection);
//              $teamResponse = json_decode($teamListRes);
//              $teamList = $teamResponse->teams; //team list by leagueid
//              for($j = 0; $j < sizeof($teamList); $j ++){
//                  if($teamList[$j]->name == $team_name && $teamList[$j]->country == $country_name){
//                      $teamId = $teamList[$j]->id;
//                  }
//              }
//              $leagueId_old = $leagueId;
//          }
//          if((isset($teamId) && !isset($teamId_old)) || (isset($teamId) && isset($teamId_old) && $teamId != $teamId_old)){
//              curl_setopt($cURLConnection, CURLOPT_URL, 'https://api.sportscribe.co/v1_0/matchPreview/'.$teamId);
//              $previewListRes = curl_exec($cURLConnection);
//              $previewResponse = json_decode($previewListRes);
//              $preview = $previewResponse->blurb_full;
//              $teamId_old = $teamId;
//              echo $preview."<BR/><BR/>";
//          }
//      }
//      curl_close($cURLConnection);
////      bright end
//      ?>
<!--    </div>-->
<!---->
<!--        --><?php
        if($stats) {
      ?>
<!--      <h3 style="font-weight: bold;">Stats</h3>-->
<!--      <div class="grid-x grid-padding-x">-->
<!--        --><?php //foreach($stats as $stat) { ?>
<!--        <div class="small-12 large-3 cell">-->
<!--          <h4><a href="--><?php //echo bbfootball_slug("{$stat->country->slug}"); ?><!--">--><?php //echo $stat->country->name; ?><!--</a> <a href="--><?php //echo bbfootball_slug("{$stat->country->slug}/{$stat->league->slug}"); ?><!--">--><?php //echo $stat->league->name; ?><!--</a></h4>-->
<!--          <ul class="team-stats">-->
<!--            <li>-->
<!--              <label>Won</label>-->
<!--              <span>--><?php //echo $stat->win; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Drawn</label>-->
<!--              <span>--><?php //echo $stat->draw; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Lost</label>-->
<!--              <span>--><?php //echo $stat->lost; ?><!--</span>-->
<!--            </li>-->
<!--          </ul>-->
<!--          <button data-toggle="--><?php //echo $stat->country->slug; ?><!-----><?php //echo $stat->league->slug; ?><!--" class="button small expanded hollow">-->
<!--            Show More Stats-->
<!--          </button>-->
<!--          <ul class="team-stats hide" id="--><?php //echo $stat->country->slug; ?><!-----><?php //echo $stat->league->slug; ?><!--" data-toggler="hide">-->
<!--            <li>-->
<!--              <label>Goals Scored</label>-->
<!--              <span>--><?php //echo $stat->goals_for; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Goals Conceded</label>-->
<!--              <span>--><?php //echo $stat->goals_against; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Clean Sheets</label>-->
<!--              <span>--><?php //echo $stat->clean_sheets; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Yellow Cards</label>-->
<!--              <span>--><?php //echo $stat->yellow_cards; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Red Cards</label>-->
<!--              <span>--><?php //echo $stat->red_cards; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Goals Scored Per Game</label>-->
<!--              <span>--><?php //echo $stat->goals_scored_per_game; ?><!--</span>-->
<!--            </li>-->
<!--            <li>-->
<!--              <label>Goals Conceded Per Game</label>-->
<!--              <span>--><?php //echo $stat->goals_conceded_per_game; ?><!--</span>-->
<!--            </li>-->
<!--          </ul>-->
<!--        </div>-->
<!--        --><?php //} ?>
<!--      </div>-->
      <hr />
      <?php
        }

        if(isset($team->page)) {
          if(has_shortcode($team->page->post_content, 'odds')) {
            // Remove the shortcode
            $before = explode('[odds', $team->page->post_content);
            echo apply_filters('the_content', $before[0]);
            echo do_shortcode("[odds team=\"{$team->slug}\" show_title=\"false\"]");
            $after = explode(']', $team->page->post_content);

            echo apply_filters('the_content', end($after));
          }
          else {
            echo apply_filters('the_content', $team->page->post_content);
          }
        }
        else {
          if(isset($team->events)) {
            if(!isset($atts) || $atts['show_title']) {
            bbfootball_template('odds-format-switcher.php');
            }
            bbfootball_template('events.php', array(
              'events' => (array) $team->events
            ));
          }
        }

      ?>
    </div>
  </article>
</div>
