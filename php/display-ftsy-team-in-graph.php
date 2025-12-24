<?php
include("auth.php");
include('../secrets/mysql_db_connection.php');

// Get user data
        
if (empty($_GET["show_team"])) {
    $show_team = mb_convert_encoding($_SESSION['username'], 'UTF-8');
    $user = mb_convert_encoding($_SESSION['username'],'UTF-8');
    $user_id = $_SESSION['user_id'];
    $ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
    $ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
} else {
    $show_team =  mb_convert_encoding($_GET["show_team"], 'UTF-8');	
    $user = mysqli_query($con, "SELECT username from xa7580_db1.users WHERE teamname = '".$show_team."' ") -> fetch_object() -> username;	
    $user_id = mysqli_query($con, "SELECT id from xa7580_db1.users WHERE teamname = '".$show_team."' ") -> fetch_object() -> id;	
    $ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
    $ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
}

if ($_SESSION['user_id'] == $user_id){
    $is_my_team = 1;
} else {
    $is_my_team = 0;
}

// Get season data
$clicked_spieltag = $_GET['tag'];
$clicked_spieltag = trim($clicked_spieltag);
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

if ($clicked_spieltag === 'Aktueller Spieltag') {

    /*********************/
    /* SQL CURRENT ROUND */
    /*********************/

    $kader = mysqli_query($con,"		
    SELECT 
        base.id
        , base.display_name
        , base.position_short
        , base.image_path
        , base.player_status_logo_path
        , base.sidelined_reason
        , base.team_id
        , base.short_code AS team_code
        , CASE WHEN base.team_id = fix.localteam_id THEN fix.localteam_score ELSE fix.visitorteam_score END AS matchup_score_for 
        , CASE WHEN base.team_id = fix.localteam_id THEN fix.visitorteam_score ELSE fix.localteam_score END AS matchup_score_against
        , CASE WHEN base.team_id = fix.localteam_id THEN team_away.short_code ELSE team_home.short_code END AS gegner 
        , CASE WHEN base.team_id = fix.localteam_id THEN team_away.name ELSE team_home.name END AS gegner_name        
        , CASE WHEN base.team_id = fix.localteam_id THEN 'H' ELSE 'A' END AS homeaway         
        , base.1_ftsy_match_status
        , fix.round_name
        , fix.fixture_id
        , fix.kickoff_dt
        , fix.kickoff_ts
        , fix.match_status
        , CASE 
            WHEN DAYNAME(fix.kickoff_dt) = 'Monday' THEN 'Mo.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Tuesday' THEN 'Di.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Wednesday' THEN 'Mi.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Thursday' THEN 'Do.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Friday' THEN 'Fr.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Saturday' THEN 'Sa.'
            WHEN DAYNAME(fix.kickoff_dt) = 'Sunday' THEN 'So.'
            END AS kickoff_weekday
        , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND AS kickoff_time_trunc
        , MONTH(fix.kickoff_dt) AS kickoff_month
        , DAY(fix.kickoff_dt) AS kickoff_day
        , ftsy.ftsy_score
        , ftsy.appearance_stat
        , ftsy.appearance_ftsy
        , CASE 
            WHEN ftsy.minutes_played_stat is NULL AND ftsy.appearance_stat = 1 THEN '1 Min.' 
            WHEN ftsy.minutes_played_stat IS NOT NULL AND ftsy.appearance_stat = 1 THEN CONCAT(ftsy.minutes_played_stat, ' Min.')
            ELSE NULL
            END AS appearance_stat_adv
        , ftsy.goals_total_ftsy
        , ftsy.goals_total_stat
        , CASE 
            WHEN ftsy.appearance_stat = 1 THEN ftsy.pen_scored_ftsy - ftsy.pen_missed_ftsy
            ELSE NULL 
            END AS penalties_ftsy
        , CASE 
            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
            ELSE NULL 
            END AS penalties_stat      
        , ftsy.assists_ftsy
        , ftsy.assists_stat
        , ftsy.clean_sheet_ftsy
        , CASE 
            WHEN ftsy.clean_sheet_ftsy > 0 THEN 'ja' 
            WHEN ftsy.clean_sheet_ftsy = 0 THEN 'nein' 
            ELSE NULL 
            END AS clean_sheet_stat
        , ftsy.shots_total_ftsy
        , base.".$ftsy_status_column." AS ftsy_match_status
        , snap.ftsy_score_avg
        , allowed.rank AS allowed_rank
        , allowed.avg_allowed AS allowed_avg
        , CASE 
            WHEN allowed.rank between 1 AND 5 THEN '#d0001f'
            WHEN allowed.rank between 14 AND 18 THEN '#079c07'
            ELSE '#666'
            END AS allowed_color
        , proj.ftsy_score_projected
    FROM sm_playerbase_basic_v base
    LEFT JOIN sm_fixtures fix 
        ON ( base.team_id = fix.localteam_id OR base.team_id = fix.visitorteam_id )
        AND fix.round_name = (SELECT spieltag FROM parameter)
        AND fix.season_id = (SELECT season_id FROM parameter)     
    LEFT JOIN ftsy_scoring_akt_mv ftsy
        ON ftsy.player_id = base.id
        AND fix.fixture_id = ftsy.fixture_id
    LEFT JOIN sm_teams team_home
        ON fix.localteam_id = team_home.id
    LEFT JOIN sm_teams team_away
        ON fix.visitorteam_id = team_away.id
    LEFT JOIN ftsy_scoring_snap snap
        ON snap.id = base.id
    LEFT JOIN ftsy_points_allowed allowed
        ON allowed.opp_team_id = ( CASE WHEN base.team_id = fix.localteam_id THEN team_away.id ELSE team_home.id END )
        AND allowed.position_short = base.position_short
    LEFT JOIN ftsy_scoring_projection_v proj
        ON  base.id = proj.player_id
    WHERE
        ".$ftsy_owner_column." = '".$user_id."'
");

} else {

    /*********************/
    /* SQL CLICKED ROUND */
    /*********************/

    $kader = mysqli_query($con,"	
        SELECT 
            hst.player_id AS id
            , hst.display_name
            , hst.position_short
            , base.image_path
            , hst.score_for AS matchup_score_for 
            , hst.score_against AS matchup_score_against
            , hst.opp_team_code AS gegner 
            , hst.ftsy_score
            , hst.1_ftsy_match_status AS ftsy_match_status
            , hst.kickoff_ts
            , hst.appearance_stat
            , 'FT' AS match_status 
        FROM ftsy_scoring_hist hst
        LEFT JOIN sm_playerbase base
            ON base.id = hst.player_id
        WHERE 
            hst.1_ftsy_owner_id = '".$user_id."' 
            AND hst.season_id = '".$akt_season_id."'
            AND hst.round_name = '".$clicked_spieltag."'
    ");
}

/**********************/
/* MODAL PLAYER SUB   */
/**********************/

echo "<div id='myModal' class='modal'>";
    echo "<div class='modal_wrapper'>";
        echo "<div class='modal_header'>";
            echo "<div class='modal_headline'>Aufstellung ändern</div>";
            echo "<div id='modal_close'>&times;</div>";
        echo "</div>";
        echo "<div class='modal_subheader'>";
            echo "Klicke auf die roten/grünen Symbole um Wechsel in deiner Aufstellung durchzuführen.";
        echo "</div>";
        echo "<div id='modal-content' class='modal-content'>";
        echo "</div>"; 
    echo "</div>";
echo "</div>"; 

/***************************/
/* DISPLAY SQUAD FORMATION */
/***************************/

$data = array();
while ($row = mysqli_fetch_array($kader)) {
    $data[] = $row;
}

echo "<div class='aufstellung_headline'>";
    echo "<div>AUFSTELLUNG";
        $akt_aufstellung = mysqli_query($con, "SELECT akt_aufstellung FROM xa7580_db1.users_gamedata WHERE user_id = '".$user_id."' ") -> fetch_object() -> akt_aufstellung;	
        if ($clicked_spieltag == 'Aktueller Spieltag' or $clicked_spieltag == 'Saison'){ echo "<small> (".$akt_aufstellung.")</small>";}
    echo "</div>";
        $ftsy_score_total = mysqli_query($con, "
            SELECT COALESCE(CASE WHEN ftsy_home_id = '".$user_id."' THEN ftsy_home_score ELSE ftsy_away_score END  ,'NA') AS ftsy_score_total
            FROM xa7580_db1.ftsy_schedule 
            WHERE 
                (ftsy_home_id = '".$user_id."' or ftsy_away_id = '".$user_id."') 
                AND buli_round_name = '".$clicked_spieltag."'
                AND season_id = '".$akt_season_id."'
            ") -> fetch_object() -> ftsy_score_total;	
        if ($ftsy_score_total > -100){
            echo "<div class='match_score'>".$ftsy_score_total." Punkte</div>";
        } elseif ($clicked_spieltag == 'Aktueller Spieltag' ) {
        } else {
            echo "<div class='match_score'>SPIELFREI</div>";			
        }
echo "</div>";

echo "<div class='striped_background'>";
    echo "<div class='aufstellung_wrapper'>";

    // Iteriate positions (forward etc)
    $array = array("st", "mf", "aw", "tw");
    foreach($array AS $value){
        echo "<div class='aufstellung_".$value."'>";

        foreach ($data AS $row) {

            // Check position AND status
            if ($row['position_short'] == strtoupper($value) AND $row['ftsy_match_status'] != 'NONE'){  

                // Check if sub
                if ($row['kickoff_ts'] > date('Y-m-d H:i:s')) {
                    if ($is_my_team == 1){
                        echo "<div class='player_card_item hover_border' style='cursor: pointer;' data-id='" . $row['id'] . "' onclick='changePlayer(this)'>";
                    } else {
                        echo "<div class='player_card_item hover_border' style='cursor: pointer;' data-id='" . $row['id'] . "' onclick='#'>";						
                    }
                } else {
                    echo "<div class='player_card_item'>";
                }	
                         
                // Player image
                echo "<div class='hide_mobile' style='position: relative; left: 0; top: 0;'>";
                    echo "<img style='position: relative; left: 0; top: 0; border-radius: 5px;' height='40px' width='auto' src='" . $row['image_path'] . "'>";
                    // Status
                    if ($clicked_spieltag == 'Aktueller Spieltag' or $clicked_spieltag == 'Saison'){
                        echo "<img style='position: absolute; right: 0.2px; bottom: 0.2px; ' title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['sidelined_reason'], 'UTF-8') . "' height='15px' width='auto' src='../img/icons/" . $row['player_status_logo_path'] . "'>";
                    }
                echo "</div>";		

                // Player text
                echo "<div class='player_card_text'>";
                    echo "<div class='player_card_name'>";
                        $link_datenbank = 'research.php?click_player=' . strval($row['id']);
                        echo "<a href='" . $link_datenbank . "'>" . mb_convert_encoding($row['display_name'], 'UTF-8') . "</a>";
                    echo "</div>";

                    // Matchup
                    if ($clicked_spieltag != 'Saison'){
                            
                        echo "<div class='player_card_detail'>";
                            if ($row['kickoff_ts'] < date('Y-m-d H:i:s')) {
                                echo $row['matchup_score_for'] . ":" . $row['matchup_score_against'] . " vs. " . $row['gegner'];	
                                if ($row['match_status'] == 'FT'){
                                    echo "&nbsp;<span class='matchup_status_final'>FINAL</span>";
                                } else {
                                    echo "&nbsp;<span class='matchup_status_live pulsate'>LIVE</span>";
                                }
                            } else {
                                echo "<span style='' title='". $row['gegner_name'] . " lässt im Schnitt " . $row['allowed_avg'] . " Punkte gegen " .$row['position_short']. " zu (Platz ".$row['allowed_rank']."/18).'><span style='cursor: help; color:".$row['allowed_color'].";'> vs. ".$row['gegner']. "</span> (".$row['homeaway'].")</span>";
                            }
                        echo "</div>";
                                
                    } else {
                        // Legacy
                    }
                echo "</div>";	

                // Score
                if ($clicked_spieltag == 'Saison') {
                    // Legacy
                } else {          
                    if ($row['kickoff_ts'] < date('Y-m-d H:i:s')) {
                        if ($row['appearance_stat'] == 0){
                            $grade_color = '#dddddd';
                        } else {
                            $color_score = (float)$row['ftsy_score'];

                            if ($color_score <= -5) {
                                $grade_color = '#ff0000';
                            } elseif ($color_score > -5 AND $color_score <= 0){
                                $grade_color = '#fb4200';
                            } elseif ($color_score > 0 AND $color_score < 5){
                                $grade_color = '#f56100';
                            } elseif ($color_score > 5 AND $color_score <= 10){
                                $grade_color = '#d4a300';
                            } elseif ($color_score > 10 AND $color_score <= 15){
                                $grade_color = '#9ed500';
                            } elseif ($color_score > 15 AND $color_score <= 20){
                                $grade_color = '#6FB617';
                            } elseif ($color_score > 20 AND $color_score <= 25){
                                $grade_color = '#29A71E';
                            } elseif ($color_score > 25 AND $color_score <= 30){
                                $grade_color = '#06A022';
                            } elseif ($color_score > 30 ){
                                $grade_color = '#06a06f';
                            } else {
                                $grade_color = '#dddddd';
                            }        
                        }
                        
                        echo "<div class='player_card_score live' style='background-color: ".$grade_color." '>";
                            if ($row['appearance_stat'] == 1){ 
                                echo $row['ftsy_score'];
                            } else { 
                                echo '-'; 
                            };
                        echo "</div>";	
                    } else {
                        echo "<div class='player_card_score pre' style=''>" . round($row['ftsy_score_projected'],1);
                        echo "</div>";	
                    }
                }				
                echo "</div>"; 
            }
        }
        echo "</div>"; 
    }
    echo "</div>";
echo "</div>";

/* BENCH */

echo "<div class='bank_headline'>BANK";
echo "</div>";

echo "<div class='bank_wrapper'>";

    echo "<div class='aufstellung_bn'>";

            foreach ($data AS $row) {

                // Position AND status
                if ($row['ftsy_match_status'] == 'NONE'){  

                    // Prüfe Anstop für Wechselfunktion / Wrapper
                    if ($row['kickoff_ts'] > date('Y-m-d H:i:s')) {
                        if ($is_my_team == 1){
                            echo "<div class='player_card_item hover_border' style='cursor: pointer;' data-id='" . $row['id'] . "' onclick='changePlayer(this)'>";
                        } else {
                            echo "<div class='player_card_item hover_border' style='cursor: pointer;' data-id='" . $row['id'] . "' onclick='#'>";						
                        }					
                    } else {
                        echo "<div class='player_card_item'>";
                    }	
                    
                        // Plager image
                        echo "<div class='hide_mobile' style='position: relative; left: 0; top: 0;'>";
                            echo "<img style='position: relative; left: 0; top: 0; border-radius: 5px;' height='40px' width='auto' src='" . $row['image_path'] . "'>";
                            // Status
                            if ($clicked_spieltag == 'Aktueller Spieltag' or $clicked_spieltag == 'Saison'){
                            echo "<img style='position: absolute; right: 0.2px; bottom: 0.2px; ' title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['sidelined_reason'], 'UTF-8') . "' height='15px' width='auto' src='../img/icons/" . $row['player_status_logo_path'] . "'>";
                            }
                        echo "</div>";		

                        // Player text
                        echo "<div class='player_card_text'>";
                            echo "<div class='player_card_name'>";
                                $link_datenbank = 'research.php?click_player=' . strval($row['id']);
                                echo "<a href='" . $link_datenbank . "'>" . mb_convert_encoding($row['display_name'], 'UTF-8') . "</a>";
                            echo "</div>";

                            // Matchup
                            if ($clicked_spieltag != 'Saison'){

                                echo "<div class='player_card_detail'>";
                                    if ($row['kickoff_ts'] < date('Y-m-d H:i:s')) {
                                        echo $row['matchup_score_for'] . ":" . $row['matchup_score_against'] . " vs. " . $row['gegner'];	
                                        if ($row['match_status'] == 'FT'){
                                        echo "&nbsp;<span class='matchup_status_final'>FINAL</span>";
                                        } else {
                                        echo "&nbsp;<span class='matchup_status_live pulsate'>LIVE</span>";
                                        }
                                    } else {
                                        echo "<span style='' title='". $row['gegner_name'] . " lässt im Schnitt " . $row['allowed_avg'] . " Punkte gegen " .$row['position_short']. " zu (Platz ".$row['allowed_rank']."/18).'><span style='cursor: help; color:".$row['allowed_color'].";'> vs. ".$row['gegner']. "</span> (".$row['homeaway'].")</span>";
                                    }	
                                echo "</div>";
                                
                            } else {
                                // Legacy
                            }
                        echo "</div>";	

                        // Score
                        if ($clicked_spieltag == 'Saison') {
                            // Legacy
                        } else {
                            
                            if ($row['kickoff_ts'] < date('Y-m-d H:i:s')) {

                                if ($row['appearance_stat'] == 0){
                                    $grade_color = '#dddddd';

                                } else {

                                    $color_score = (float)$row['ftsy_score'];

                                    if ($color_score <= -5) {
                                        $grade_color = '#ff0000';
                                    } elseif ($color_score > -5 AND $color_score <= 0){
                                        $grade_color = '#fb4200';
                                    } elseif ($color_score > 0 AND $color_score < 5){
                                        $grade_color = '#f56100';
                                    } elseif ($color_score > 5 AND $color_score <= 10){
                                        $grade_color = '#d4a300';
                                    } elseif ($color_score > 10 AND $color_score <= 15){
                                        $grade_color = '#9ed500';
                                    } elseif ($color_score > 15 AND $color_score <= 20){
                                        $grade_color = '#6FB617';
                                    } elseif ($color_score > 20 AND $color_score <= 25){
                                        $grade_color = '#29A71E';
                                    } elseif ($color_score > 25 AND $color_score <= 30){
                                        $grade_color = '#06A022';
                                    } elseif ($color_score > 30 ){
                                        $grade_color = '#06a06f';
                                    } else {
                                        $grade_color = '#dddddd';
                                    }
                                    
                                }
                                echo "<div class='player_card_score live' style='background-color: ".$grade_color." '>";
                                if ($row['appearance_stat'] == 1){ 
                                    echo $row['ftsy_score'];
                                } else { 
                                    echo '-'; 
                                }
                                echo "</div>";
                            } else {
                                echo "<div class='player_card_score pre' style=''>" . round($row['ftsy_score_projected'],1);
                                echo "</div>";	
                            }
                        }				
                    
                    echo "</div>"; 
                }
            }
            echo "</div>"; 
    echo "</div>"; 
echo "</div>"; 
?>