<?php
if(!isset($livedata)){
    return;
}
?>
<div>
<?php
$api_token = 'xAvBO16VMxSnppwPhH55iu7MQZVTVq39WbD7JAUHNO9sHqTFqm88KUM2Lp0f';
$curlConnection = curl_init();
curl_setopt($curlConnection, CURLOPT_URL, 'https://soccer.sportmonks.com/api/v2.0/livescores/now?api_token='.$api_token.'&include=localTeam,visitorTeam,events,tvstations,league');
curl_setopt($curlConnection, CURLOPT_RETURNTRANSFER, true);
$res = curl_exec($curlConnection);
$response_decode = json_decode($res);
$data = $response_decode->data;
curl_close($curlConnection);
//print_r($data[0]);
if(!isset($data[0])){
    echo "Sorry, There is no ongoing game now.";
}else{
    for($i = 0; $i < sizeof($data); $i++){
?>
    <p>
        <?php echo ($i+1);?>. <a href="<?php echo bbfootball_slug("team/".bbfootball()->api()->teams()->find($data[$i]->localTeam->data->id)->slug);?>"><?php echo $data[$i]->localTeam->data->name;?></a>
        vs
        <a href="<?php echo bbfootball_slug("team/".bbfootball()->api()->teams()->find($data[$i]->visitorTeam->data->id)->slug);?>"><?php echo $data[$i]->visitorTeam->data->name;?></a>
    </p>
    <p style="padding-left: 2%;">
        <?php
        if($data[$i]->time !== null && $data[$i]->time->status !==null && ($data[$i]->time->status === 'LIVE' || $data[$i]->time->status === 'FT' || $data[$i]->time->status === 'HT') ) {
            if ($data[$i]->weather_report !== null) { ?>
                Temperature is <?php echo $data[$i]->weather_report->temperature_celcius->temp; ?>°C and humidity is <?php echo $data[$i]->weather_report->humidity; ?> and wind speed is <?php echo $data[$i]->weather_report->wind->speed; ?>.&nbsp;&nbsp;
                <?php
            }
            if ($data[$i]->time->starting_at->time !== null && $data[$i]->time->starting_at->timezone !== null) {
                ?>
                This game was started at <?php echo $data[$i]->time->starting_at->time; ?>(<?php echo $data[$i]->time->starting_at->timezone; ?>)
                <?php if ($data[$i]->time->minute !== null && intval($data[$i]->time->minute)<90) { ?>
                    and it is <?php echo $data[$i]->time->minute; ?> minutes on the game time
                <?php } ?>.&nbsp;&nbsp;
                <?php
            }
            if($data[$i]->time->status === 'FT'){
                echo "This game is over.&nbsp;&nbsp;";
            }
            if ($data[$i]->formations !== null && $data[$i]->formation->localteam_formation !== null && $data[$i]->formation->visitorteam_formation !== null) {
                ?>
                The formation of <?php echo $data[$i]->localTeam->data->name; ?> team is <?php echo $data[$i]->formations->localteam_formation; ?> and <?php echo $data[$i]->visitorTeam->data->name; ?> team is <?php echo $data[$i]->formations->visitorteam_formation; ?>.&nbsp;&nbsp;
                <?php
            }
            if ($data[$i]->scores !== null) {
                ?>
                Current score is <?php echo $data[$i]->scores->localteam_score; ?> : <?php echo $data[$i]->scores->visitorteam_score; ?>.&nbsp;&nbsp;
                <?php
            }
            if (sizeof($data[$i]->events->data) > 0) {
                echo "<br/>";
                for($k=0; $k<sizeof($data[$i]->events->data);$k++){
                    $tt = $data[$i]->events->data[$k]->team_id == $data[$i]->localTeam->data->id ? $data[$i]->localTeam->data->name : $data[$i]->visitorTeam->data->name;
                    if($data[$i]->events->data[$k]->type == 'goal'){
                        echo "On the game time ".$data[$i]->events->data[$k]->minute."minutes, player ".$data[$i]->events->data[$k]->player_name." of ".$tt." team scored.&nbsp;&nbsp;";
                    }
                    if($data[$i]->events->data[$k]->type == 'substitution'){
                        echo "On the game time ".$data[$i]->events->data[$k]->minute."minutes, ".$tt." team substituted player ".$data[$i]->events->data[$k]->player_name." with ".$data[$i]->events->data[$k]->related_player_name.".&nbsp;&nbsp;";
                    }
                    if($data[$i]->events->data[$k]->type == 'yellowcard'){
                        echo "On the game time ".$data[$i]->events->data[$k]->minute."minutes, player ".$data[$i]->events->data[$k]->player_name." of ".$tt." team has been cautioned.&nbsp;&nbsp;";
                    }
                }
            }
            if (sizeof($data[$i]->tvstations->data) == 1) {
                echo "<br/>TV station that broadcast this game is " . $data[$i]->tvstations->data[0]->tvstation . ".&nbsp;&nbsp;";
            }
            if (sizeof($data[$i]->tvstations->data) > 1) {
                ?>
                <br/>
                This game is broadcast on these TV stations:&nbsp;
                <?php
                for ($j = 0; $j < sizeof($data[$i]->tvstations->data) - 1; $j++) {
                    echo $data[$i]->tvstations->data[$j]->tvstation . ",&nbsp;&nbsp;";
                }
                echo $data[$i]->tvstations->data[sizeof($data[$i]->tvstations->data) - 1]->tvstation . ".&nbsp;&nbsp;";
            }
        }
        if($data[$i]->time !== null && $data[$i]->time->status !==null && $data[$i]->time->status == 'NS'){
            if ($data[$i]->weather_report !== null) { ?>
                Reported temperature is <?php echo $data[$i]->weather_report->temperature_celcius->temp; ?>°C and reported humidity is <?php echo $data[$i]->weather_report->humidity; ?> and reported wind speed is <?php echo $data[$i]->weather_report->wind->speed; ?>.&nbsp;&nbsp;
                <?php
            }
            if ($data[$i]->time->starting_at->time !== null && $data[$i]->time->starting_at->timezone !== null) {
                ?>
                Start time of this game is <?php echo $data[$i]->time->starting_at->time; ?>(<?php echo $data[$i]->time->starting_at->timezone; ?>)
                <?php if ($data[$i]->time->minute == 10000) { ?>
                    and performs for <?php echo $data[$i]->time->minute; ?> mins
                <?php } ?>.&nbsp;&nbsp;
                <?php
            }

            if (sizeof($data[$i]->tvstations->data) == 1) {
                echo "<br/>TV station that broadcast this game is " . $data[$i]->tvstations->data[0]->tvstation . ".&nbsp;&nbsp;";
            }
            if (sizeof($data[$i]->tvstations->data) > 1) {
                ?>
                <br/>
                This game is broadcast on these TV stations:&nbsp;
                <?php
                for ($j = 0; $j < sizeof($data[$i]->tvstations->data) - 1; $j++) {
                    echo $data[$i]->tvstations->data[$j]->tvstation . ",&nbsp;&nbsp;";
                }
                echo $data[$i]->tvstations->data[sizeof($data[$i]->tvstations->data) - 1]->tvstation . ".&nbsp;&nbsp;";
            }
        }
            ?>
    </p>

    <?php
    }
}
?>
</div>
