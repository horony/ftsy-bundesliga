<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<!DOCTYPE html>
<html>
<head>
	<title>FANTASY BUNDESLIGA</title> 
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">   
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/matchup.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/matchup-toggle-player-stats.js"></script>  
</head>
<body>
<!-- Header -->
<?php include('../html/header.php'); ?>
<!-- Navigation -->
<div id = "hilfscontainer">
    <?php include("../html/navigation.php"); ?>
</div>

<!-- Headline Start -->
<div id="headline" class="row">
    <?php 
    include ('../secrets/mysql_db_connection.php');
    // Get meta data    
    $match_id = $_GET["ID"];
    $user = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    $ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
    $ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
    $akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
    $akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;  
    $clicked_spieltag = mysqli_query($con, "SELECT buli_round_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> buli_round_name;
    
    $cup_query = mysqli_query($con, "
        SELECT cup_round, cup_leg, season_id, ftsy_home_id, ftsy_away_id, ftsy_home_score, ftsy_away_score 
        FROM xa7580_db1.ftsy_schedule 
        WHERE ftsy_match_id = '".$match_id."'
    ");

    $cup_match = mysqli_fetch_assoc($cup_query);
    $cup_data = null; // Initialize AS null to prevent errors

    if (!empty($cup_match['cup_round']) && $cup_match['cup_leg'] == 2) {
        $cup_round = $cup_match['cup_round'];
        $season_id = $cup_match['season_id'];
        $home_id = $cup_match['ftsy_home_id'];
        $away_id = $cup_match['ftsy_away_id'];
        $home_score_leg2 = $cup_match['ftsy_home_score']; // Updated column name
        $away_score_leg2 = $cup_match['ftsy_away_score']; // Updated column name

        // Get the first leg match
        $leg1_query = mysqli_query($con, "
            SELECT ftsy_home_score, ftsy_away_score, ftsy_home_id, ftsy_away_id 
            FROM xa7580_db1.ftsy_schedule 
            WHERE 
                season_id = '".$season_id."' 
                AND cup_round = '".$cup_round."'
                AND cup_leg = 1 
                AND ((ftsy_home_id = '".$away_id."' AND ftsy_away_id = '".$home_id."') OR (ftsy_home_id = '".$home_id."' AND ftsy_away_id = '".$away_id."'))
        ");

        $leg1_match = mysqli_fetch_assoc($leg1_query);

        if ($leg1_match) {
            $home_score_leg1 = $leg1_match['ftsy_home_score']; // Updated column name
            $away_score_leg1 = $leg1_match['ftsy_away_score']; // Updated column name

            // Aggregate the scores by matching the teams regardless of home/away position
            if ($home_id == $leg1_match['ftsy_home_id']) {
                // The home team for the second leg was the home team for the first leg
                $total_home_score = $home_score_leg1 + $home_score_leg2;
                $total_away_score = $away_score_leg1 + $away_score_leg2;
            } else {
                // The home team for the second leg was the away team for the first leg
                $total_home_score = $away_score_leg1 + $home_score_leg2;
                $total_away_score = $home_score_leg1 + $away_score_leg2;
            }

            // Store in an array for later display
            $cup_data = [
                'home_id' => $home_id,
                'away_id' => $away_id,
                'total_home_score' => $total_home_score,
                'total_away_score' => $total_away_score,
            ];
        }
    }

    echo "<h2>GAME CENTER - SPIELTAG " . $clicked_spieltag;

    // Check if its a cup round and display the transformed cup round and cup leg
    if (!empty($cup_match['cup_round'])) {
        // Transform the cup round
        $cup_round_transformed = '';
        switch ($cup_match['cup_round']) {
            case 'playoff':
                $cup_round_transformed = 'Qualifikation';
                break;
            case 'quarter':
                $cup_round_transformed = 'Viertelfinale';
                break;
            case 'semi':
                $cup_round_transformed = 'Halbfinale';
                break;
            case 'final':
                $cup_round_transformed = 'Finale';
                break;
            default:
                $cup_round_transformed = $cup_match['cup_round']; // Fallback to the original if not mapped
        }

        echo " - " . $cup_round_transformed;

        // If it's not a "final", display the cup leg
        if ($cup_match['cup_round'] != 'final' && !empty($cup_match['cup_leg'])) {
            // Transform the cup leg
            $cup_leg_transformed = '';
            switch ($cup_match['cup_leg']) {
                case 1:
                    $cup_leg_transformed = 'Hinspiel';
                    break;
                case 2:
                    $cup_leg_transformed = 'Rückspiel';
                    break;
                default:
                    $cup_leg_transformed = $cup_match['cup_leg']; // Fallback to the original if not mapped
            }

            echo " " . $cup_leg_transformed;  // Removed the "-" and added space instead
        }
    }

    echo "</h2>";
    ?>
</div>
<!-- Headline End -->

<!--Actual Game Center -->
<div class="row">
    <?php
    // User names
    $user =  mysqli_query($con,"
        SELECT username 
        FROM xa7580_db1.ftsy_schedule sch 
        INNER JOIN xa7580_db1.users usr
            ON sch.ftsy_home_id = usr.id
        WHERE sch.ftsy_match_id = '".$match_id."'
        ") -> fetch_object() -> username;

    $gegner_manager = mysqli_query($con,"
        SELECT username 
        FROM xa7580_db1.ftsy_schedule sch 
        INNER JOIN xa7580_db1.users usr
            ON sch.ftsy_away_id = usr.id
        WHERE ftsy_match_id = '".$match_id."'
        ") -> fetch_object() -> username;

    // Team names
    $mein_team = mysqli_query($con,"SELECT ftsy_home_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_home_name;
    $gegner_team = mysqli_query($con,"SELECT ftsy_away_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_away_name;
    $mein_team_id = mysqli_query($con,"SELECT ftsy_home_id FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_home_id;
    $gegner_team_id = mysqli_query($con,"SELECT ftsy_away_id FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_away_id;

    $array_home = array("for_id"=>$mein_team_id, "for_name"=>$mein_team, "for_user"=>$user, "opp_id"=>$gegner_team_id, "opp_name"=>$gegner_team, "opp_user"=>$gegner_manager);
    $array_away = array("opp_id"=>$mein_team_id, "opp_name"=>$mein_team, "opp_user"=>$user, "for_id"=>$gegner_team_id, "for_name"=>$gegner_team, "for_user"=>$gegner_manager);
    $teams_to_loop = [$array_home, $array_away];

    // Function to format ftsy values with colors and signs
    function formatFtsyValue($value) {
        if ($value > 0) {
            return '<span style="color: darkgreen;">+' . number_format($value, 1) . '</span>';
        } elseif ($value < 0) {
            return '<span style="color: darkred;">-' . number_format(abs($value), 1) . '</span>';
        } else {
            return '<span style="color: gray;">+' . number_format($value, 1) . '</span>';
        }
    }

    // Loop over both fantasy teams (home and away)
    foreach ($teams_to_loop AS &$loop_value) {
        
        $team_id =  $loop_value['for_id'];
        $team_name = $loop_value['for_name'];
        $user_name =  $loop_value['for_user'];
        $team_id_opp =  $loop_value['opp_id'];
        $team_name_opp = $loop_value['opp_name'];
        $user_name_opp =  $loop_value['opp_user'];
                
        echo "<div class='spieler'>";
        
            /**************************/ 
            /* 1.) Get overall Scores */
            /**************************/ 

            if ($akt_spieltag == $clicked_spieltag) {
                // If round is active
                // Overall score user
                $result_score = mysqli_query($con, "
                    SELECT SUM(ftsy_score) AS score
                    FROM xa7580_db1.ftsy_player_ownership base 
                    INNER JOIN xa7580_db1.ftsy_scoring_akt_v akt 
                        ON akt.player_id = base.player_id
                    WHERE ".$ftsy_owner_column." = '".$team_id."'
                        AND ".$ftsy_status_column." != 'NONE'
                        AND ftsy_score IS NOT NULL
                    GROUP BY ".$ftsy_owner_column."
                ")->fetch_object()->score;

                // Overall projection user  
                $result_score_proj = mysqli_query($con, " 
                    SELECT SUM(ftsy_score_projected) AS score_proj
                    FROM xa7580_db1.ftsy_player_ownership base 
                    INNER JOIN xa7580_db1.ftsy_scoring_projection_v proj        
                        ON proj.player_id = base.player_id
                    WHERE ".$ftsy_owner_column." = '".$team_id."'
                        AND ".$ftsy_status_column." != 'NONE'
                    GROUP BY ".$ftsy_owner_column."
                ")->fetch_object()->score_proj;                             
    
                $result_total_score = mysqli_query($con, "
                    SELECT ROUND(SUM(player_score), 1) AS total_score
                    FROM (
                        SELECT 
                            base.id AS player_id,
                            CASE 
                                WHEN fix.match_status = 'FT' THEN COALESCE(akt.ftsy_score, 0) 
                                WHEN fix.match_status = 'NS' THEN COALESCE(proj.ftsy_score_projected, 0) 
                                WHEN fix.match_status IN ('1st', '2nd', 'HT') THEN COALESCE(akt.ftsy_score, 0) + ((90 - COALESCE(akt.minutes_played_stat, 0)) / 90) * GREATEST(COALESCE(proj.ftsy_score_projected, 0) - 4, 0)
                                ELSE 0 
                            END AS player_score
                        FROM xa7580_db1.sm_playerbase base
                        LEFT JOIN xa7580_db1.ftsy_player_ownership owner
                            ON base.id = owner.player_id
                        LEFT JOIN xa7580_db1.ftsy_scoring_akt_v akt  
                            ON base.id = akt.player_id
                        LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj  
                            ON base.id = proj.player_id
                        LEFT JOIN xa7580_db1.sm_fixture_per_team_akt_v fix  
                            ON (base.current_team_id = fix.team_id OR base.current_team_id = fix.opp_id)
                        WHERE owner." . $ftsy_owner_column . " = '" . $team_id . "'
                            AND owner." . $ftsy_status_column . " != 'NONE'
                            AND fix.match_status IN ('FT', 'NS', '1st', '2nd', 'HT')
                        GROUP BY base.id
                    ) AS player_scores;
                ")->fetch_object()->total_score;

                // Full roster user
                $my_score_valid = mysqli_query($con, " SELECT COUNT(player_id) AS anz_spieler FROM xa7580_db1.ftsy_player_ownership WHERE ".$ftsy_owner_column." = '".$team_id."' AND ".$ftsy_status_column." != 'NONE' ") -> fetch_object() -> anz_spieler;

                // Overall score opp
                $result_score_g = mysqli_query($con," 
                    SELECT SUM(ftsy_score) AS score
                    FROM xa7580_db1.ftsy_player_ownership base 
                    INNER JOIN xa7580_db1.ftsy_scoring_akt_v akt 
                        ON akt.player_id = base.player_id
                    WHERE   
                        ".$ftsy_owner_column." = '".$team_id_opp."'
                        AND ".$ftsy_status_column." != 'NONE'
                        AND ftsy_score IS NOT NULL 
                    GROUP BY ".$ftsy_owner_column." 
                ") -> fetch_object() -> score;

                // Full roster opp
                $his_score_valid = mysqli_query($con, " SELECT COUNT(player_id) AS anz_spieler FROM xa7580_db1.ftsy_player_ownership WHERE ".$ftsy_owner_column." = '".$team_id_opp."' AND ".$ftsy_status_column." != 'NONE' ") -> fetch_object() -> anz_spieler;

                // Players played/left
                $players_played = mysqli_query($con, "  
                    SELECT COUNT(base.id) AS cnt 
                    FROM xa7580_db1.sm_playerbase_basic_v base 
                    INNER JOIN xa7580_db1.sm_fixtures buli
                        ON  ( base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
                                AND buli.round_name = '".$akt_spieltag."'
                                AND buli.season_id = '".$akt_season_id."'
                                AND buli.kickoff_ts <= NOW()
                    WHERE ".$ftsy_owner_column." = '".$team_id."'
                                AND ".$ftsy_status_column." != 'NONE' 
                    ") -> fetch_object() -> cnt;
    
                $players_left = 11-$players_played;

            } elseif ($clicked_spieltag > $akt_spieltag) {
                // If round is in the future
                $result_score = NULL;
                $result_score_g = NULL;

            } else {
                // Round is in the past
                    
                $result_score = mysqli_query($con," 
                    SELECT ROUND(CASE WHEN sch.ftsy_home_id = '".$team_id."' THEN sch.ftsy_home_score ELSE sch.ftsy_away_score end,1) AS score
                    FROM xa7580_db1.ftsy_schedule sch 
                    WHERE   
                        ( sch.ftsy_home_id = '".$team_id."' or sch.ftsy_away_id = '".$team_id."')
                        AND sch.buli_round_name = '".$clicked_spieltag."'
                        AND sch.season_id = '".$akt_season_id."'
                ") -> fetch_object() -> score;
            }           
            
            // If scores are NULL set them to 0
            if ($clicked_spieltag <= $akt_spieltag){
                if($result_score === NULL){$result_score = 0;} 
                if($result_score_g === NULL){$result_score_g = 0;} 
            }

            /*******************************/ 
            /* 2.) Get head-to-head record */
            /*******************************/           

            $bilanz = mysqli_query($con, "  
                SELECT 
                    COALESCE(win_cnt,0) AS S
                    , COALESCE(loss_cnt,0) AS N
                    , COALESCE(draw_cnt,0) AS U
                FROM xa7580_db1.users_head2head_v
                WHERE 
                    user_id = '".$team_id."' 
                    AND user_id_opp = '".$team_id_opp."'
                    AND season_id = '".$akt_season_id."'
                    AND match_type = 'league'
            ") -> fetch_assoc();

            // Set default values if no result is returned
            if (!$bilanz) {
                $bilanz = array('S' => '0', 'N' => '0', 'U' => '0');
            }

            $bilanz_alltime = mysqli_query($con, "  
                SELECT 
                    SUM(COALESCE(win_cnt,0)) AS S
                    , SUM(COALESCE(loss_cnt,0)) AS N
                    , SUM(COALESCE(draw_cnt,0)) AS U
                FROM xa7580_db1.users_head2head_v
                WHERE 
                    user_id = '".$team_id."' 
                    AND user_id_opp = '".$team_id_opp."'
            ") -> fetch_assoc();

            // Set default values if no result is returned
            if (!$bilanz_alltime) {
                $bilanz_alltime = array('S' => '0', 'N' => '0', 'U' => '0');
            }

            echo "<div class='superheadscore'>";
                echo "<div class='headscore'>";
                    echo "<div class='left-section'>";
                        echo "<div class='team-image' style='display: inline-block;'>";
                            // Directly build the image path using team_id
                            $image_src = '../img/ftsy-team-logos/' . $team_id . '.png';

                            // Check if the file exists before displaying
                            if (file_exists($image_src)) {
                                echo '<div class="round-image-div">';
                                    echo '<img src="' . $image_src . '">';
                                echo '</div>';
                            }
                        echo "</div>";
                    echo "</div>";

                    // Live projection chart
                    echo "<div class='chart-wrap vertical'></div>";
                    echo "<div class='grid'>";
                        echo "<div class='chart-section'>";
                            $new_width_total_score = $result_total_score * 1.3;
                            $new_width_score = $result_score * 1.3;
                            echo "<div class='bar_live_prognose' style='width: " . $new_width_total_score . "px;' data-name='Medium' title='Medium " . $result_total_score . "'>";
                                echo "<span class='label' style='white-space: nowrap;'>&#8605; " . $result_total_score . "</span>";
                            echo "</div>";
                            echo "<div class='bar-container' style='position: relative;'>";
                                echo "<div class='bar_live_punkte' style='width: " . $new_width_score . "px;' data-name='Medium' title='Medium " . $result_score . "'>";
                                    echo "<span class='label' style='white-space: nowrap;'>" . $result_score . "</span>";
                                echo "</div>";
                                echo "<div class='ring-container'>";
                                    echo "<div class='circle'></div>";
                                    echo "<div class='ringring'></div>";
                                echo "</div>"; 
                            echo "</div>";
                        echo "</div>"; 
                        echo "<div class='marker marker-50'></div>";
                        echo "<div class='marker marker-100'></div>";
                        echo "<div class='marker marker-150'></div>";
                        echo "<div class='marker marker-200'></div>";
                        echo "<div class='marker-text marker-text-50'>50</div>";
                        echo "<div class='marker-text marker-text-100'>100</div>";
                        echo "<div class='marker-text marker-text-150'>150</div>";
                        echo "<div class='marker-text marker-text-200'>200</div>";
                    echo "</div>"; 
                echo "</div>"; 
        
        // Display aggregated cup score if applicable
        if ($cup_data) {
            if ($team_id == $cup_data['home_id']) {
                $hinspiel_score = $cup_data['total_home_score']; 
            } elseif ($team_id == $cup_data['away_id']) {
                $hinspiel_score = $cup_data['total_away_score']; 
            } else {
                $hinspiel_score = null; 
            }

            if ($hinspiel_score !== null) {
                $aggregated_score = $hinspiel_score + $result_score;
                echo "<div class='cup-score custom-margin'>";
                    echo "<strong>🏆 Hin- & Rückspiel: " . $aggregated_score . " (" . $hinspiel_score . ")</strong>";
                echo "</div>";
            }
        }
    
            echo "<div class='team-name-section'>";
                $achievement_icons = mysqli_query($con, "SELECT achievement_icons FROM xa7580_db1.users_gamedata WHERE username = '".$user_name."'") -> fetch_object() -> achievement_icons;
                echo "<h2>" . mb_convert_encoding(strtoupper($team_name), 'UTF-8') . " " . $achievement_icons . "</h2>";
            echo "</div>"; 
        echo "</div>"; 

        if ($akt_spieltag === $clicked_spieltag) {
            echo "<h5>Manager: " . mb_convert_encoding($user_name, 'UTF-8') . " • Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . " (<span title='Direkter Vergleich All-Time'>" . $bilanz_alltime['S'] . "-" . $bilanz_alltime['U'] . "-" . $bilanz_alltime['N'] . "</span>) • Übrige Spieler: " . $players_left . "</h5>";
        } else {
            echo "<h5>Manager: " . mb_convert_encoding($user_name, 'UTF-8') . " • Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . " (<span title='Direkter Vergleich All-Time'>" . $bilanz_alltime['S'] . "-" . $bilanz_alltime['U'] . "-" . $bilanz_alltime['N'] . "</span>)</h5>";
        } 

        echo "<div class='fakeimg'></div>";

        /*************************************/
        /* 3.) Loop bench and lineup players */
        /*************************************/

        // Define variables for loop
        $playertype_to_loop = [$aufstellung = array('headline' => 'AUFSTELLUNG', 'sql_value' => '!=' ), array('headline' => 'BANK', 'sql_value' => '=' )];
            
        // Start loop (lineup and bench)
        foreach ($playertype_to_loop AS &$playertype_loop_value) {
            $sql_value = $playertype_loop_value['sql_value'];
            $headline_value = $playertype_loop_value['headline'];

            echo "<h3>".$headline_value."</h3>";
                
            // Get player data
            include ('../secrets/mysql_db_connection.php');

            if ($akt_spieltag == $clicked_spieltag) {     
                // Active round
                $result = mysqli_query($con,"   
                    SELECT  
                        base.id
                        , base.position_short
                        , base.logo_path                                                             
                        , base.display_name
                        , buli.kickoff_ts 
                        , buli.kickoff_dt
                        , buli.fixture_status
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.visitorteam_name_code ELSE buli.localteam_name_code END AS gegner_code
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.localteam_score ELSE buli.visitorteam_score END AS score_for
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.visitorteam_score ELSE buli.localteam_score END AS score_against
                        , CASE WHEN base.team_id = buli.localteam_id THEN 'H' ELSE 'A' END AS homeaway
                        , CASE 
                            WHEN DAYNAME(buli.kickoff_dt) = 'Monday' THEN 'Mo.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Tuesday' THEN 'Di.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Thursday' THEN 'Do.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Friday' THEN 'Fr.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Saturday' THEN 'Sa.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Sunday' THEN 'So.'
                            END AS kickoff_weekday
                        , LEFT(buli.kickoff_time,5) AS kickoff_time_trunc
                        , MONTH(buli.kickoff_dt) AS kickoff_month
                        , DAY(buli.kickoff_dt) AS kickoff_day      
                        , ftsy.ftsy_score
                        , CASE 
                            WHEN ftsy.minutes_played_stat is null and ftsy.appearance_stat = 1 THEN '1 Min.' 
                            WHEN ftsy.minutes_played_stat is not null and ftsy.appearance_stat = 1 THEN CONCAT(ftsy.minutes_played_stat, ' Min.')
                            ELSE NULL
                            END AS appearance_stat_x
                        , COALESCE(ftsy.appearance_ftsy,0) + COALESCE(ftsy.minutes_played_ftsy,0) AS appearance_ftsy
                        , ftsy.goals_minus_pen_stat
                        , ftsy.goals_minus_pen_ftsy
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
                                    ELSE NULL 
                                    END AS penalties_stat_x   
                        , ftsy.pen_scored_stat + ftsy.pen_missed_stat AS penalties_total
                        , COALESCE(ftsy.pen_scored_ftsy,0) + COALESCE(ftsy.pen_missed_ftsy,0) AS penalties_ftsy
                        , ftsy.assists_stat
                        , ftsy.assists_ftsy
                        , ftsy.shots_total_stat
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.shots_on_goal_stat,' ('),ftsy.shots_total_stat),')') 
                            ELSE NULL 
                            END AS shots_stat_x
                        , COALESCE(ftsy.shots_on_goal_ftsy,0) + COALESCE(ftsy.shots_total_ftsy,0) + COALESCE(ftsy.shots_blocked_ftsy,0) + COALESCE(ftsy.shots_missed_ftsy,0) AS shots_ftsy
                        , ftsy.hit_woodwork_stat
                        , COALESCE(hit_woodwork_ftsy,0) AS hit_woodwork_ftsy
                        , ftsy.passes_complete_stat + ftsy.passes_incomplete_stat AS passes_total
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
                            ELSE NULL 
                            END AS passes_stat_x
                        , COALESCE(ftsy.passes_complete_ftsy,0) + COALESCE(ftsy.passes_incomplete_ftsy,0) AS passes_ftsy
                        , ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat AS crosses_total
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
                            ELSE NULL 
                            END AS crosses_stat_x
                        , COALESCE(ftsy.crosses_complete_ftsy,0) + COALESCE(ftsy.crosses_incomplete_ftsy,0) AS crosses_ftsy
                        , ftsy.key_passes_stat
                        , COALESCE(ftsy.key_passes_ftsy,0) AS key_passes_ftsy
                        , ftsy.big_chances_created_stat
                        , COALESCE(ftsy.big_chances_created_ftsy,0) AS big_chances_created_ftsy
                        , ftsy.duels_won_stat + ftsy.duels_lost_stat AS duels_total
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
                            ELSE NULL 
                            END AS duels_stat_x
                        , COALESCE(ftsy.duels_won_ftsy,0) + COALESCE(ftsy.duels_lost_ftsy,0) AS duels_ftsy
                        , ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat AS dribble_total
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
                            ELSE NULL 
                            END AS dribble_stat_x
                        , COALESCE(ftsy.dribbles_success_ftsy,0) + COALESCE(ftsy.dribbles_failed_ftsy,0) + COALESCE(ftsy.dribble_attempts_ftsy,0) AS dribble_ftsy
                        , ftsy.tackles_stat
                        , COALESCE(ftsy.tackles_ftsy,0) AS tackles_ftsy
                        , ftsy.interceptions_stat
                        , COALESCE(ftsy.interceptions_ftsy,0) AS interceptions_ftsy
                        , ftsy.blocks_stat
                        , COALESCE(ftsy.blocks_ftsy,0) AS blocks_ftsy
                        , ftsy.clearances_stat
                        , COALESCE(ftsy.clearances_ftsy,0) AS clearances_ftsy
                        , ftsy.clearances_offline_stat
                        , COALESCE(ftsy.clearances_offline_ftsy,0) AS clearances_offline_ftsy
                        , ftsy.outside_box_saves_stat
                        , COALESCE(ftsy.outside_box_saves_ftsy,0) AS outside_box_saves_ftsy
                        , ftsy.inside_box_saves_stat
                        , COALESCE(ftsy.inside_box_saves_ftsy,0) AS inside_box_saves_ftsy
                        , ftsy.saves_stat
                        , COALESCE(ftsy.saves_ftsy,0) AS saves_ftsy
                        , ftsy.pen_saved_stat
                        , COALESCE(ftsy.pen_saved_ftsy,0) AS pen_saved_ftsy
                        , ftsy.redcards_stat
                        , COALESCE(ftsy.redcards_ftsy,0) AS redcards_ftsy
                        , ftsy.redyellowcards_stat
                        , COALESCE(ftsy.redyellowcards_ftsy,0) AS redyellowcards_ftsy
                        , ftsy.pen_committed_stat
                        , COALESCE(ftsy.pen_committed_ftsy,0) AS pen_committed_ftsy
                        , ftsy.owngoals_stat
                        , COALESCE(ftsy.owngoals_ftsy,0) AS owngoals_ftsy
                        , ftsy.dispossessed_stat
                        , COALESCE(ftsy.dispossessed_ftsy,0) AS dispossessed_ftsy
                        , ftsy.dribbled_past_stat
                        , COALESCE(ftsy.dribbled_past_ftsy,0) AS dribbled_past_ftsy
                        , ftsy.pen_won_stat
                        , COALESCE(ftsy.pen_won_ftsy,0) AS pen_won_ftsy
                        , ftsy.big_chances_missed_stat
                        , COALESCE(ftsy.big_chances_missed_ftsy,0) AS big_chances_missed_ftsy
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 and ftsy.big_chances_missed_ftsy < 0 THEN ftsy.big_chances_missed_stat 
                            ELSE NULL 
                            END AS big_chances_missed_stat_x
                        , ftsy.error_lead_to_goal_stat
                        , COALESCE(ftsy.error_lead_to_goal_ftsy,0) AS error_lead_to_goal_ftsy
                        , ftsy.punches_stat
                        , COALESCE(ftsy.punches_ftsy,0) AS punches_ftsy
                        , ftsy.goals_conceded_stat
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 and (ftsy.goals_conceded_ftsy < 0 or ftsy.goalkeeper_goals_conceded_ftsy < 0) THEN ftsy.goals_conceded_stat 
                            ELSE NULL 
                            END AS goals_conceded_stat_x
                        , COALESCE(ftsy.goals_conceded_ftsy,0) + COALESCE(ftsy.goalkeeper_goals_conceded_ftsy,0) AS goals_conceded_ftsy
                        , ftsy.clean_sheet_stat
                        , COALESCE(ftsy.clean_sheet_ftsy,0) AS clean_sheet_ftsy
                        , CASE 
                            WHEN ftsy.appearance_stat = 1 and ftsy.clean_sheet_ftsy > 0 THEN ftsy.clean_sheet_stat 
                            ELSE NULL 
                            END AS clean_sheet_stat_x
                        , proj.ftsy_score_projected 
                    FROM xa7580_db1.sm_playerbase_basic_v base 
                    LEFT JOIN xa7580_db1.ftsy_scoring_akt_v ftsy 
                        ON ftsy.player_id = base.id
                    LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj
                        ON proj.player_id = base.id     
                    INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
                        ON (base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
                        AND buli.round_name = '".$akt_spieltag."'
                        AND buli.season_id = '".$akt_season_id."'
                    WHERE 
                        ".$ftsy_status_column." ".$sql_value." 'NONE' 
                        AND ".$ftsy_owner_column." = '".$team_id."'
                    ORDER BY CASE 
                        WHEN base.position_short = 'ST' THEN 1 
                        WHEN base.position_short = 'MF' THEN 2 
                        WHEN base.position_short = 'AW' THEN 3 
                        WHEN base.position_short = 'TW' THEN 4 
                        END  
                    ");
            } elseif($clicked_spieltag > $akt_spieltag) {
                // Round in the future
                $result = mysqli_query($con,"   
                    SELECT  
                        base.id
                        , base.position_short
                        , base.logo_path                                                             
                        , base.display_name
                        , buli.kickoff_ts 
                        , buli.kickoff_dt
                        , buli.fixture_status
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.visitorteam_name_code ELSE buli.localteam_name_code END AS gegner_code
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.localteam_score ELSE buli.visitorteam_score END AS score_for
                        , CASE WHEN base.team_id = buli.localteam_id THEN buli.visitorteam_score ELSE buli.localteam_score END AS score_against
                        , CASE WHEN base.team_id = buli.localteam_id THEN 'H' ELSE 'A' END AS homeaway
                        , CASE 
                            WHEN DAYNAME(buli.kickoff_dt) = 'Monday' THEN 'Mo.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Tuesday' THEN 'Di.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Thursday' THEN 'Do.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Friday' THEN 'Fr.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Saturday' THEN 'Sa.'
                            WHEN DAYNAME(buli.kickoff_dt) = 'Sunday' THEN 'So.'
                            END AS kickoff_weekday
                        , LEFT(buli.kickoff_time,5) AS kickoff_time_trunc
                        , MONTH(buli.kickoff_dt) AS kickoff_month
                        , DAY(buli.kickoff_dt) AS kickoff_day
                    FROM xa7580_db1.sm_playerbase_basic_v base 
                    INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
                        ON  ( base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
                                AND buli.round_name = '".$akt_spieltag."'
                                AND buli.season_id = '".$akt_season_id."'
                    WHERE ".$ftsy_status_column." ".$sql_value." 'NONE' 
                                AND ".$ftsy_owner_column." = '".$team_id."'
                    ORDER BY 
                        CASE 
                            WHEN base.position_short = 'ST' THEN 1 
                            WHEN base.position_short = 'MF' THEN 2 
                            WHEN base.position_short = 'AW' THEN 3 
                            WHEN base.position_short = 'TW' THEN 4 
                            END  
                ");
            } else {
                // Round in the past
                $result = mysqli_query($con,"   
                    SELECT  
                        base.id
                        , ftsy.position_short
                        , ftsy.logo_path                                                             
                        , ftsy.display_name
                        , buli.kickoff_ts 
                        , buli.kickoff_dt
                        , buli.fixture_status
                        , CASE WHEN ftsy.current_team_id = buli.localteam_id THEN buli.visitorteam_name_code ELSE buli.localteam_name_code END AS gegner_code
                        , CASE WHEN ftsy.current_team_id = buli.localteam_id THEN buli.localteam_score ELSE buli.visitorteam_score END AS score_for
                        , CASE WHEN ftsy.current_team_id = buli.localteam_id THEN buli.visitorteam_score ELSE buli.localteam_score END AS score_against
                        , CASE WHEN ftsy.current_team_id = buli.localteam_id THEN 'H' ELSE 'A' END AS homeaway
                        , CASE WHEN DAYNAME(buli.kickoff_dt) = 'Monday' THEN 'Mo.'
                                    when DAYNAME(buli.kickoff_dt) = 'Tuesday' THEN 'Di.'
                                    when DAYNAME(buli.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                                    when DAYNAME(buli.kickoff_dt) = 'Thursday' THEN 'Do.'
                                    when DAYNAME(buli.kickoff_dt) = 'Friday' THEN 'Fr.'
                                    when DAYNAME(buli.kickoff_dt) = 'Saturday' THEN 'Sa.'
                                    when DAYNAME(buli.kickoff_dt) = 'Sunday' THEN 'So.'
                                    END AS kickoff_weekday
                        , LEFT(buli.kickoff_time,5) AS kickoff_time_trunc
                        , MONTH(buli.kickoff_dt) AS kickoff_month
                        , DAY(buli.kickoff_dt) AS kickoff_day
                        , ftsy.ftsy_score
                        , CASE WHEN ftsy.minutes_played_stat is null and ftsy.appearance_stat = 1 THEN '1 Min.' 
                                    when ftsy.minutes_played_stat is not null and ftsy.appearance_stat = 1 THEN CONCAT(ftsy.minutes_played_stat, ' Min.')
                                    ELSE NULL
                                    END AS appearance_stat_x
                        , COALESCE(ftsy.appearance_ftsy,0) + COALESCE(ftsy.minutes_played_ftsy,0) AS appearance_ftsy
                        , ftsy.goals_minus_pen_stat
                        , ftsy.goals_minus_pen_ftsy
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
                                    ELSE NULL 
                                    END AS penalties_stat_x  
                        , ftsy.pen_scored_stat + ftsy.pen_missed_stat AS penalties_total
                        , COALESCE(ftsy.pen_scored_ftsy,0) + COALESCE(ftsy.pen_missed_ftsy,0) AS penalties_ftsy
                        , ftsy.assists_stat
                        , ftsy.assists_ftsy

                        , ftsy.shots_total_stat
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.shots_on_goal_stat,' ('),ftsy.shots_total_stat),')') 
                                        ELSE NULL 
                                        END AS shots_stat_x
                        , COALESCE(ftsy.shots_on_goal_ftsy,0) + COALESCE(ftsy.shots_total_ftsy,0) + COALESCE(ftsy.shots_blocked_ftsy,0) + COALESCE(ftsy.shots_missed_ftsy,0) AS shots_ftsy
                        , ftsy.hit_woodwork_stat
                        , COALESCE(hit_woodwork_ftsy,0) AS hit_woodwork_ftsy
                        , ftsy.passes_complete_stat + ftsy.passes_incomplete_stat AS passes_total
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
                                        ELSE NULL 
                                        END AS passes_stat_x
                        , COALESCE(ftsy.passes_complete_ftsy,0) + COALESCE(ftsy.passes_incomplete_ftsy,0) AS passes_ftsy
                        , ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat AS crosses_total
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
                                        ELSE NULL 
                                        END AS crosses_stat_x
                        , COALESCE(ftsy.crosses_complete_ftsy,0) + COALESCE(ftsy.crosses_incomplete_ftsy,0) AS crosses_ftsy       
                        , ftsy.key_passes_stat
                        , COALESCE(ftsy.key_passes_ftsy,0) AS key_passes_ftsy
                        , ftsy.big_chances_created_stat
                        , COALESCE(ftsy.big_chances_created_ftsy,0) AS big_chances_created_ftsy
                        , ftsy.duels_won_stat + ftsy.duels_lost_stat AS duels_total
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
                                        ELSE NULL 
                                    END AS duels_stat_x
                        , COALESCE(ftsy.duels_won_ftsy,0) + COALESCE(ftsy.duels_lost_ftsy,0) AS duels_ftsy
                        , ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat AS dribble_total
                        , CASE WHEN ftsy.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
                                        ELSE NULL 
                                    END AS dribble_stat_x
                        , COALESCE(ftsy.dribbles_success_ftsy,0) + COALESCE(ftsy.dribbles_failed_ftsy,0) + COALESCE(ftsy.dribble_attempts_ftsy,0) AS dribble_ftsy
                        , ftsy.tackles_stat
                        , COALESCE(ftsy.tackles_ftsy,0) AS tackles_ftsy
                        , ftsy.interceptions_stat
                        , COALESCE(ftsy.interceptions_ftsy,0) AS interceptions_ftsy
                        , ftsy.blocks_stat
                        , COALESCE(ftsy.blocks_ftsy,0) AS blocks_ftsy
                        , ftsy.clearances_stat
                        , COALESCE(ftsy.clearances_ftsy,0) AS clearances_ftsy
                        , ftsy.clearances_offline_stat
                        , COALESCE(ftsy.clearances_offline_ftsy,0) AS clearances_offline_ftsy
                        , ftsy.outside_box_saves_stat
                        , COALESCE(ftsy.outside_box_saves_ftsy,0) AS outside_box_saves_ftsy
                        , ftsy.inside_box_saves_stat
                        , COALESCE(ftsy.inside_box_saves_ftsy,0) AS inside_box_saves_ftsy
                        , ftsy.saves_stat
                        , COALESCE(ftsy.saves_ftsy,0) AS saves_ftsy
                        , ftsy.pen_saved_stat
                        , COALESCE(ftsy.pen_saved_ftsy,0) AS pen_saved_ftsy
                        , ftsy.redcards_stat
                        , COALESCE(ftsy.redcards_ftsy,0) AS redcards_ftsy
                        , ftsy.redyellowcards_stat
                        , COALESCE(ftsy.redyellowards_ftsy,0) AS redyellowcards_ftsy
                        , ftsy.pen_committed_stat
                        , COALESCE(ftsy.pen_committed_ftsy,0) AS pen_committed_ftsy
                        , ftsy.owngoals_stat
                        , COALESCE(ftsy.owngoals_ftsy,0) AS owngoals_ftsy
                        , ftsy.dispossessed_stat
                        , COALESCE(ftsy.dispossessed_ftsy,0) AS dispossessed_ftsy
                        , ftsy.dribbled_past_stat
                        , COALESCE(ftsy.dribbled_past_ftsy,0) AS dribbled_past_ftsy
                        , ftsy.pen_won_stat
                        , COALESCE(ftsy.pen_won_ftsy,0) AS pen_won_ftsy
                        , ftsy.big_chances_missed_stat
                        , COALESCE(ftsy.big_chances_missed_ftsy,0) AS big_chances_missed_ftsy
                        , CASE WHEN ftsy.appearance_stat = 1 and ftsy.big_chances_missed_ftsy < 0 THEN ftsy.big_chances_missed_stat 
                                        ELSE NULL 
                                        END AS big_chances_missed_stat_x
                        , ftsy.error_lead_to_goal_stat
                        , COALESCE(ftsy.error_lead_to_goal_ftsy,0) AS error_lead_to_goal_ftsy
                        , ftsy.punches_stat
                        , COALESCE(ftsy.punches_ftsy,0) AS punches_ftsy
                        , ftsy.goals_conceded_stat
                        , CASE WHEN ftsy.appearance_stat = 1 and (ftsy.goals_conceded_ftsy < 0 or ftsy.goalkeeper_goals_conceded_ftsy < 0) THEN ftsy.goals_conceded_stat 
                                        ELSE NULL 
                                        END AS goals_conceded_stat_x
                        , COALESCE(ftsy.goals_conceded_ftsy,0) + COALESCE(ftsy.goalkeeper_goals_conceded_ftsy,0) AS goals_conceded_ftsy
                        , ftsy.clean_sheet_stat
                        , COALESCE(ftsy.clean_sheet_ftsy,0) AS clean_sheet_ftsy
                        , CASE WHEN ftsy.appearance_stat = 1 and ftsy.clean_sheet_ftsy > 0 THEN ftsy.clean_sheet_stat 
                                        ELSE NULL 
                                        END AS clean_sheet_stat_x
                    FROM xa7580_db1.sm_playerbase base 
                    LEFT JOIN xa7580_db1.ftsy_scoring_hist ftsy 
                        ON ftsy.player_id = base.id
                        AND ftsy.round_name = '".$clicked_spieltag."'
                        AND ftsy.season_id = '".$akt_season_id."'
                    INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
                        ON ( ftsy.current_team_id = buli.localteam_id OR ftsy.current_team_id = buli.visitorteam_id) 
                        AND buli.round_name = '".$clicked_spieltag."'
                        AND buli.season_id = '".$akt_season_id."'
                    WHERE 
                        ".$ftsy_status_column." ".$sql_value." 'NONE' 
                        AND ".$ftsy_owner_column." = '".$team_id."'
                    ORDER BY CASE WHEN base.position_short = 'ST' THEN 1 
                                            WHEN base.position_short = 'MF' THEN 2 
                                            WHEN base.position_short = 'AW' THEN 3 
                                            WHEN base.position_short = 'TW' THEN 4 
                                            END  
                ");        
            }

            // Print out player data
            if($clicked_spieltag > $akt_spieltag){
                // Round in future
                echo "<div class='kader'><table id='myTable'>";
                    while($row = mysqli_fetch_array($result)) {
                        echo "<tr class = 'summary1'>";
                            echo "<td style='display:none;'>" . $row['id'] . "</td>";
                            echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
                            echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
                            $full_name = $row['display_name'];        // Get the full name
                            $name_parts = explode(" ", $full_name);   // Split the name into parts (first and last names)
                            $shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts); // Shorten the first name to its initial and keep the last name
                            echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>"; // Display the shortened name
                            $matchup_to_display = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). " vs. ".$row['gegner_code']. " (".$row['homeaway'] . ")";
                            echo "<td style='color: gray;'>" .$matchup_to_display. "</td>";
                            echo "<td align='center' class='player_score'></td>";
                        echo "</tr>";
                    }
                echo "</table></div>";
            } elseif(intval($clicked_spieltag) < intval($akt_spieltag)) {
                // Round in the past
                echo "<div class='kader'><table id='myTable'>";
                while($row = mysqli_fetch_array($result)) {
                    echo "<tr class = 'summary1'>";
                        echo "<td style='display:none;'>" . $row['id'] . "</td>";
                        echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
                        echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
                        $full_name = $row['display_name'];        // Get the full name
                        $name_parts = explode(" ", $full_name);   // Split the name into parts (first and last names)
                        $shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts); // Shorten the first name to its initial and keep the last name
                        echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>"; // Display the shortened name
                        echo "<td class='matchup-to-display'>" . $matchup_to_display . "</td>";
                        echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . " vs. " . $row['gegner_code'] . "<span style='color: black' class=''><small><b> FINAL<b/></small></span></td>";
                        echo "<td align='center' class='player_score'><span class=''>" . $row['ftsy_score'] . "</span></td>";
                    echo "</tr>";

                    // Detailed stats in hidden popup row
                    echo "<tr class= 'player_detail'><td colspan='5'>";
                        if ($row['appearance_stat_x'] != NULL) {
                            /* Appearance */
                            echo( ($row['appearance_stat_x'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat_x'] . ' ' . formatFtsyValue($row['appearance_ftsy']) . ' • ' : NULL);                                  
                            /* Scoring */
                            echo( ($row['goals_minus_pen_stat'] != NULL AND $row['goals_minus_pen_stat'] != 0)? 'Tore: ' . $row['goals_minus_pen_stat'] . ' ' . formatFtsyValue($row['goals_minus_pen_ftsy']) . ' • ' : NULL);    
                            echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat_x'] . ' ' . formatFtsyValue($row['penalties_ftsy']) . ' • ' : NULL);
                            echo( ($row['assists_stat'] != NULL AND $row['assists_stat'] != 0)? 'Vorlagen: ' . $row['assists_stat'] . ' ' . formatFtsyValue($row['assists_ftsy']) . ' • ' : NULL);
                            echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' ' . formatFtsyValue($row['pen_won_ftsy']) . ' • ' : NULL);   
                            /* Gegentore */
                            echo( ($row['goals_conceded_stat_x'] != NULL AND $row['goals_conceded_stat_x'] != 0)? 'Gegentore: ' . $row['goals_conceded_stat_x'] . ' ' . formatFtsyValue($row['goals_conceded_ftsy']) . ' • ' : NULL);    
                            echo( ($row['clean_sheet_stat_x'] != NULL AND $row['clean_sheet_stat_x'] != 0)? 'Weiße Weste: ' . $row['clean_sheet_stat_x'] . ' ' . formatFtsyValue($row['clean_sheet_ftsy']) . ' • ' : NULL);   
                            /* Shots */
                            echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_stat_x'] . ' ' . formatFtsyValue($row['shots_ftsy']) . ' • ' : NULL);
                            echo( ($row['hit_woodwork_stat'] != NULL AND $row['hit_woodwork_stat'] != 0)? 'Pfosten: ' . $row['hit_woodwork_stat'] . ' ' . formatFtsyValue($row['hit_woodwork_ftsy']) . ' • ' : NULL);
                            echo( ($row['big_chances_missed_stat_x'] != NULL AND $row['big_chances_missed_stat_x'] != 0)? 'Großchancen vergeben: ' . $row['big_chances_missed_stat_x'] . ' ' . formatFtsyValue($row['big_chances_missed_ftsy']) . ' • ' : NULL);
                            /* Passing */
                            echo( ($row['big_chances_created_stat'] != NULL AND $row['big_chances_created_stat'] != 0)? 'Großchancen kreiert: ' . $row['big_chances_created_stat'] . ' ' . formatFtsyValue($row['big_chances_created_ftsy']) . ' • ' : NULL);
                            echo( ($row['key_passes_stat'] != NULL AND $row['key_passes_stat'] != 0)? 'Key-Pässe: ' . $row['key_passes_stat'] . ' ' . formatFtsyValue($row['key_passes_ftsy']) . ' • ' : NULL);  
                            echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat_x'] . ' ' . formatFtsyValue($row['passes_ftsy']) . ' • ' : NULL);  
                            echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat_x'] . ' ' . formatFtsyValue($row['crosses_ftsy']) . ' • ' : NULL); 
                            /* Duels */
                            echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat_x'] . ' ' . formatFtsyValue($row['duels_ftsy']) . ' • ' : NULL);    
                            echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat_x'] . ' ' . formatFtsyValue($row['dribble_ftsy']) . ' • ' : NULL);  
                            echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' ' . formatFtsyValue($row['tackles_ftsy']) . ' • ' : NULL);   
                            /* Defensive stats */
                            echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' ' . formatFtsyValue($row['interceptions_ftsy']) . ' • ' : NULL);
                            echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' ' . formatFtsyValue($row['blocks_ftsy']) . ' • ' : NULL);  
                            echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' ' . formatFtsyValue($row['clearances_ftsy']) . ' • ' : NULL);  
                            echo( ($row['clearances_offline_stat'] != NULL AND $row['clearances_offline_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_offline_stat'] . ' ' . formatFtsyValue($row['clearances_offline_ftsy']) . ' • ' : NULL);  
                            /* Goalkeeping */
                            echo( ($row['saves_stat'] != NULL AND $row['saves_stat'] != 0)? 'Paraden: ' . $row['saves_stat'] . ' ' . formatFtsyValue($row['saves_ftsy']) . ' • ' : NULL);                           
                            echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' ' . formatFtsyValue($row['outside_box_saves_ftsy']) . ' • ' : NULL);   
                            echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' ' . formatFtsyValue($row['inside_box_saves_ftsy']) . ' • ' : NULL);   
                            echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' ' . formatFtsyValue($row['pen_saved_ftsy']) . ' • ' : NULL); 
                            echo( ($row['punches_stat'] != NULL AND $row['punches_stat'] != 0)? 'Bälle gefaustet: ' . $row['punches_stat'] . ' ' . formatFtsyValue($row['punches_ftsy']) . ' • ' : NULL);                            
                            /* Errors */
                            echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' ' . formatFtsyValue($row['pen_committed_ftsy']) . ' • ' : NULL);   
                            echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' ' . formatFtsyValue($row['owngoals_ftsy']) . ' • ' : NULL);    
                            echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' ' . formatFtsyValue($row['dispossessed_ftsy']) . ' • ' : NULL); 
                            echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' ' . formatFtsyValue($row['dribbled_past_ftsy']) . ' • ' : NULL); 
                            echo( ($row['error_lead_to_goal_stat'] != NULL AND $row['error_lead_to_goal_stat'] != 0)? 'Patzer: ' . $row['error_lead_to_goal_stat'] . ' ' . formatFtsyValue($row['error_lead_to_goal_ftsy']) . ' • ' : NULL);                           
                            /* Cards */
                            echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' ' . formatFtsyValue($row['redcards_ftsy']) : NULL);  
                            echo( ($row['redyellowcards_stat'] != NULL AND $row['redyellowcards_stat'] != 0)? 'Gelb-Rot: ' . $row['redyellowcards_stat'] . ' ' . formatFtsyValue($row['redyellowcards_ftsy']) : NULL);   

                        } else {
                            echo 'Kein Einsatz.';
                        }
                    echo "<td></tr>";
                }
                echo "</table></div>";
            } else {
                // Round is current round
                echo "<div class='kader'><table id='myTable'>";
                while ($row = mysqli_fetch_array($result)) {
                    echo "<tr class='summary1'>";
                        echo "<td style='display:none;'>" . $row['id'] . "</td>";
                        echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
                        echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
                        $full_name = $row['display_name']; 
                        $name_parts = explode(" ", $full_name); 
                        $shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts);
                        echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>";
                        $kickoff_time = date('H:i', strtotime($row['kickoff_ts']));
                        $projected_score = $row['ftsy_score_projected'];
                        if (strtotime($row['kickoff_ts']) <= time()) {
                            $actual_score = isset($row['ftsy_score']) ? number_format($row['ftsy_score'], 1) : '0';
                        } else {
                            $actual_score = '-';
                        }
                        $minutes_played = isset($row['appearance_stat_x']) ? $row['appearance_stat_x'] : 0;
                        if (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] != 'FT') {
                            $remaining_minutes = 90 - $minutes_played;
                            $adjusted_projected_score = max($projected_score - 4, 0);
                            $live_projection_score = $row['ftsy_score'] + ($adjusted_projected_score / 90 * $remaining_minutes);
                            $live_projection_score = number_format($live_projection_score, 1);
                        } else {
                            $live_projection_score = number_format($projected_score, 1);
                        }
                        $projection_class = ($live_projection_score == 0) ? 'projection zero-score' : 'projection';

                        $actual_score_class = '';
                        if ($actual_score !== '-') {
                            if ($actual_score < 0) {
                                $actual_score_class = 'red';
                            } elseif ($actual_score >= 0.1 && $actual_score <= 3.9) {
                                $actual_score_class = 'orange';
                            } elseif ($actual_score >= 4 && $actual_score <= 9.9) {
                                $actual_score_class = 'yellow';
                            } elseif ($actual_score >= 10 && $actual_score <= 15) {
                                $actual_score_class = 'light-green';
                            } elseif ($actual_score >= 15.1) {
                                $actual_score_class = 'dark-green';
                            }
                        }

                        $player_score_display = "<div class='player-score-wrapper'><span class='$projection_class'>&#8605 $live_projection_score</span><span class='actual-score $actual_score_class'>$actual_score</span></div>";

                        if (strtotime($row['kickoff_ts']) > time()) {
                            $matchup_to_display = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . $kickoff_time . " vs. " . $row['gegner_code'] . " (" . $row['homeaway'] . ")";
                            echo "<td style='color: gray; font-size: 14px;'>" . $matchup_to_display . "</td>";
                            echo "<td align='center' title='Projection' class='player_score'>" . $player_score_display . "</td>";
                        } elseif (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] != 'FT') {
                            echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . 
                                " vs. " . $row['gegner_code'] . 
                                "<span style='color: red' class='pulsate'> <small><b> LIVE</b></small></span></td>";
                            echo "<td align='center' class='player_score'>" . $player_score_display . "</td>";
                        } elseif (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] == 'FT') {
                            echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . 
                                " vs. " . $row['gegner_code'] . 
                                "<span style='color: black'><small><b> FINAL</b></small></span></td>";
                            echo "<td align='center' class='player_score'>" . $player_score_display . "</td>";
                        }
                    echo "</tr>";
                    
                    // Detailed player stats in hidden popup row
                    echo "<tr class= 'player_detail'><td colspan='5'>";
                        if ($row['appearance_stat_x'] != NULL) {

                            /* Appearance */
                            echo( ($row['appearance_stat_x'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat_x'] . ' ' . formatFtsyValue($row['appearance_ftsy']) . ' • ' : NULL);
                            /* Scoring */
                            echo( ($row['goals_minus_pen_stat'] != NULL AND $row['goals_minus_pen_stat'] != 0)? 'Tore: ' . $row['goals_minus_pen_stat'] . ' ' . formatFtsyValue($row['goals_minus_pen_ftsy']) . ' • ' : NULL);
                            echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat_x'] . ' ' . formatFtsyValue($row['penalties_ftsy']) . ' • ' : NULL);
                            echo( ($row['assists_stat'] != NULL AND $row['assists_stat'] != 0)? 'Vorlagen: ' . $row['assists_stat'] . ' ' . formatFtsyValue($row['assists_ftsy']) . ' • ' : NULL);
                            echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' ' . formatFtsyValue($row['pen_won_ftsy']) . ' • ' : NULL);
                            /* Gegentore */
                            echo( ($row['goals_conceded_stat_x'] != NULL AND $row['goals_conceded_stat_x'] != 0)? 'Gegentore: ' . $row['goals_conceded_stat_x'] . ' ' . formatFtsyValue($row['goals_conceded_ftsy']) . ' • ' : NULL);
                            echo( ($row['clean_sheet_stat_x'] != NULL AND $row['clean_sheet_stat_x'] != 0)? 'Weiße Weste: ' . $row['clean_sheet_stat_x'] . ' ' . formatFtsyValue($row['clean_sheet_ftsy']) . ' • ' : NULL);
                            /* Shots */
                            echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_stat_x'] . ' ' . formatFtsyValue($row['shots_ftsy']) . ' • ' : NULL);
                            echo( ($row['hit_woodwork_stat'] != NULL AND $row['hit_woodwork_stat'] != 0)? 'Pfosten: ' . $row['hit_woodwork_stat'] . ' ' . formatFtsyValue($row['hit_woodwork_ftsy']) . ' • ' : NULL);
                            echo( ($row['big_chances_missed_stat_x'] != NULL AND $row['big_chances_missed_stat_x'] != 0)? 'Großchancen vergeben: ' . $row['big_chances_missed_stat_x'] . ' ' . formatFtsyValue($row['big_chances_missed_ftsy']) . ' • ' : NULL);
                            /* Passing */
                            echo( ($row['big_chances_created_stat'] != NULL AND $row['big_chances_created_stat'] != 0)? 'Großchancen kreiert: ' . $row['big_chances_created_stat'] . ' ' . formatFtsyValue($row['big_chances_created_ftsy']) . ' • ' : NULL);
                            echo( ($row['key_passes_stat'] != NULL AND $row['key_passes_stat'] != 0)? 'Key-Pässe: ' . $row['key_passes_stat'] . ' ' . formatFtsyValue($row['key_passes_ftsy']) . ' • ' : NULL);
                            echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat_x'] . ' ' . formatFtsyValue($row['passes_ftsy']) . ' • ' : NULL);  
                            echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat_x'] . ' ' . formatFtsyValue($row['crosses_ftsy']) . ' • ' : NULL);
                            /* Duels */
                            echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat_x'] . ' ' . formatFtsyValue($row['duels_ftsy']) . ' • ' : NULL);    
                            echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat_x'] . ' ' . formatFtsyValue($row['dribble_ftsy']) .' • ' : NULL);  
                            echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' ' . formatFtsyValue($row['tackles_ftsy']) . ' • ' : NULL);   
                            /* Defensive stats */
                            echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' ' . formatFtsyValue($row['interceptions_ftsy']) . ' • ' : NULL);
                            echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' ' . formatFtsyValue($row['blocks_ftsy']) . ' • ' : NULL);  
                            echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' ' . formatFtsyValue($row['clearances_ftsy']) . ' • ' : NULL);  
                            echo( ($row['clearances_offline_stat'] != NULL AND $row['clearances_offline_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_offline_stat'] . ' ' . formatFtsyValue($row['clearances_offline_ftsy']) . ' • ' : NULL);  
                            /* Goalkeeping */
                            echo( ($row['saves_stat'] != NULL AND $row['saves_stat'] != 0)? 'Paraden: ' . $row['saves_stat'] . ' ' . formatFtsyValue($row['saves_ftsy']) . ' • ' : NULL);                           
                            echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' ' . formatFtsyValue($row['outside_box_saves_ftsy']) . ' • ' : NULL);   
                            echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' ' . formatFtsyValue($row['inside_box_saves_ftsy']) . ' • ' : NULL);   
                            echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' ' . formatFtsyValue($row['pen_saved_ftsy']) . ' • ' : NULL); 
                            echo( ($row['punches_stat'] != NULL AND $row['punches_stat'] != 0)? 'Bälle gefaustet: ' . $row['punches_stat'] . ' ' . formatFtsyValue($row['punches_ftsy']) . ' • ' : NULL);
                            /* Errors */
                            echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' ' . formatFtsyValue($row['pen_committed_ftsy']) . ' • ' : NULL);
                            echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' ' . formatFtsyValue($row['owngoals_ftsy']) . ' • ' : NULL);
                            echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' ' . formatFtsyValue($row['dispossessed_ftsy']) . ' • ' : NULL);
                            echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' ' . formatFtsyValue($row['dribbled_past_ftsy']) . ' • ' : NULL);
                            echo( ($row['error_lead_to_goal_stat'] != NULL AND $row['error_lead_to_goal_stat'] != 0)? 'Patzer: ' . $row['error_lead_to_goal_stat'] . ' ' . formatFtsyValue($row['error_lead_to_goal_ftsy']) . ' • ' : NULL);
                            /* Cards */
                            echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' ' . formatFtsyValue($row['redcards_ftsy']) . ' • ' : NULL);
                            echo( ($row['redyellowcards_stat'] != NULL AND $row['redyellowcards_stat'] != 0)? 'Gelb-Rot: ' . $row['redyellowcards_stat'] . ' ' . formatFtsyValue($row['redyellowcards_ftsy']) . ' • ' : NULL);
                            /* Projection */
                            echo( 'Projection: <span style="color: blue">' . $row['ftsy_score_projected'] . ' </span>' );   
                        } else {
                            echo 'Kein Einsatz.';
                        }
                    echo "<td></tr>";
                    }
                echo "</table></div>";
            }
            echo "<div class='fakeimg'></div>";
        }
        echo "</div>";
        } ?>
    </div>
</div>
</body>
</html>