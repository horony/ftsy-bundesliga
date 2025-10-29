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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/matchup_buli.css">
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
    $fixture_id = $_GET["ID"];
    $akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
    $akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;  
    $clicked_spieltag = mysqli_query($con, "SELECT round_name FROM xa7580_db1.sm_fixtures WHERE fixture_id = '".$fixture_id."' LIMIT 1") -> fetch_object() -> round_name;

    echo "<h2>BUNDESLIGA GAME CENTER - SPIELTAG " . $clicked_spieltag . "</h2>";
    ?>
</div>
<!-- Headline End -->

<!--Actual Game Center -->
<div class="row game-center-bg">
    <div class="game-center-container">
    <?php       

    /* Get fixture data including fantasy scores for both Bundesliga teams */
    $cte_sql_ftsy_score = '';

    if($selected_spieltag < $akt_spieltag) {
        $cte_sql_ftsy_score = "
            SELECT 
                current_team_id AS team_id
                , SUM(ftsy_score) AS team_ftsy_score_sum
            FROM xa7580_db1.ftsy_scoring_hist
            WHERE 
                fixture_id = '".$fixture_id."'
            GROUP BY current_team_id
        ";
    } elseif ($selected_spieltag == $akt_spieltag) {
        $cte_sql_ftsy_score = "
            SELECT 
                pb.current_team_id AS team_id
                , ROUND(COALESCE(SUM(akt.ftsy_score),0),1) AS team_ftsy_score_sum
            FROM xa7580_db1.sm_playerbase pb
            LEFT JOIN xa7580_db1.ftsy_scoring_akt_v akt
                ON akt.player_id = pb.id
                AND akt.fixture_id = '".$fixture_id."'
            WHERE 
                pb.current_team_id IS NOT NULL
            GROUP BY pb.current_team_id
        ";
    }

    $fixture_sql = mysqli_query($con, " 
        WITH cte_team_ftsy_score AS (
            ".$cte_sql_ftsy_score."
        )
        SELECT 
            v.localteam_id 
            , v.visitorteam_id
            , v.localteam_name
            , v.visitorteam_name
            , t_local.logo_path AS localteam_logo_path
            , t_away.logo_path AS visitorteam_logo_path
            , COALESCE(v.localteam_score, 0) AS localteam_score
            , COALESCE(v.visitorteam_score, 0) AS visitorteam_score
            , COALESCE(cte_home.team_ftsy_score_sum, 0) AS home_team_ftsy_score
            , COALESCE(cte_away.team_ftsy_score_sum, 0) AS away_team_ftsy_score
            , v.fixture_status
            , v.kickoff_ts
        FROM xa7580_db1.sm_fixtures_basic_v v
        LEFT JOIN xa7580_db1.sm_teams t_local
            ON v.localteam_id = t_local.id
        LEFT JOIN xa7580_db1.sm_teams t_away
            ON v.visitorteam_id = t_away.id
        LEFT JOIN cte_team_ftsy_score cte_home
            ON cte_home.team_id = v.localteam_id
        LEFT JOIN cte_team_ftsy_score cte_away
            ON cte_away.team_id = v.visitorteam_id
        WHERE 
            v.fixture_id = '".$fixture_id."'
        LIMIT 1
        ") -> fetch_assoc();

    
    /* Get MOTM and TOTM player IDs */

    $player_id_motm = '';
    $player_id_totm = '';

    if($selected_spieltag < $akt_spieltag) {
        $motm_sql = mysqli_query($con, " 
            SELECT 
                scr.player_id
            FROM xa7580_db1.ftsy_scoring_hist scr
            WHERE 
                scr.fixture_id = '".$fixture_id."'
            ORDER BY ftsy_score DESC
            LIMIT 1
            ;") -> fetch_assoc();

        $totm_sql = mysqli_query($con, " 
            SELECT 
                scr.player_id
            FROM xa7580_db1.ftsy_scoring_hist scr
            WHERE 
                scr.fixture_id = '".$fixture_id."'
                AND (
                    scr.minutes_played_stat >= 45
                    OR (scr.redcards_stat > 0 OR scr.redyellowcards_stat > 0)
                )
            ORDER BY ftsy_score ASC
            LIMIT 1
            ;") -> fetch_assoc();
    } elseif ($selected_spieltag == $akt_spieltag) {
        $motm_sql = mysqli_query($con, " 
            SELECT 
                scr.player_id
            FROM xa7580_db1.ftsy_scoring_akt_v scr
            WHERE 
                scr.fixture_id = '".$fixture_id."'
                AND scr.ftsy_score > 10
            ORDER BY ftsy_score DESC
            LIMIT 1
            ;") -> fetch_assoc();

        $totm_sql = mysqli_query($con, " 
            SELECT 
                scr.player_id
            FROM xa7580_db1.ftsy_scoring_akt_v scr
            WHERE 
                scr.fixture_id = '".$fixture_id."'
                AND (
                    scr.minutes_played_stat >= 45
                    OR (scr.redcards_stat > 0 OR scr.redyellowcards_stat > 0)
                )
            ORDER BY ftsy_score ASC
            LIMIT 1
            ;") -> fetch_assoc();
    }

    $player_id_motm = $motm_sql['player_id'];
    $player_id_totm = $totm_sql['player_id'];

    /* Prepare team arrays for looping */
    $array_home = array(
        "team_id"=>$fixture_sql['localteam_id']
        , "team_name"=>$fixture_sql['localteam_name']
        , "logo_path"=>$fixture_sql['localteam_logo_path']
        , "score"=>$fixture_sql['localteam_score']
        , "fixture_status"=>$fixture_sql['fixture_status']
        , "kickoff_ts"=>$fixture_sql['kickoff_ts']
        , "ftsy_score"=>$fixture_sql['home_team_ftsy_score']
    );
    
    $array_away = array(
        "team_id"=>$fixture_sql['visitorteam_id']
        , "team_name"=>$fixture_sql['visitorteam_name']
        , "logo_path"=>$fixture_sql['visitorteam_logo_path']
        , "score"=>$fixture_sql['visitorteam_score']
        , "fixture_status"=>$fixture_sql['fixture_status']
        , "kickoff_ts"=>$fixture_sql['kickoff_ts']
        , "ftsy_score"=>$fixture_sql['away_team_ftsy_score']
    );

    $teams_to_loop = [$array_home, $array_away];

    /* Function to format ftsy values with colors and signs */
    function formatFtsyValue($value) {
        if ($value > 0) {
            return '<span style="color: darkgreen;">+' . number_format($value, 1) . '</span>';
        } elseif ($value < 0) {
            return '<span style="color: darkred;">-' . number_format(abs($value), 1) . '</span>';
        } else {
            return '<span style="color: gray;">+' . number_format($value, 1) . '</span>';
        }
    }

    /* Create 2-column layout: Home Team | Away Team */
    echo "<div class='teams-container'>";
    
    /************************************************/
    /* Loop over both fantasy teams (home and away) */
    /************************************************/

    foreach ($teams_to_loop AS $index => &$loop_value) {
        
        $team_id =  $loop_value['team_id'];
        $team_name = $loop_value['team_name'];
        $score = $loop_value['score'];
        $ftsy_score = $loop_value['ftsy_score'];
        $fixture_status = $loop_value['fixture_status'];
        $logo_path = $loop_value['logo_path'];
        $kickoff_ts = $loop_value['kickoff_ts'];

        /* Team Header */
        echo "<div class='team-column'>";
            echo "<div class='spieler team-header'>";
                /* Team Logo */
                echo "<div class='team-logo'>";
                    if (!empty($logo_path)) {
                        echo "<img src='" . $logo_path . "' alt='Team Logo'>";
                    } else {
                        echo "<div class='team-logo-placeholder'>No Logo</div>";
                    }
                echo "</div>";
            echo "<div class='team-info'>";
                /* Row 1: Team name (left) + Score (right) */
                echo "<div class='team-info-row1'>";
                    echo "<h2 class='team-name'>" . mb_convert_encoding(strtoupper($team_name), 'UTF-8') . "</h2>";
                    echo "<div class='team-score'>" . $score . "</div>";
                echo "</div>";
                /* Row 2: Kickoff + Status (left) + Fantasy Score (right) */
                echo "<div class='team-info-row2'>";
                    echo "<div class='team-match-info'>";
                        if (!empty($kickoff_ts)) {
                            echo date('d.m.Y H:i', strtotime($kickoff_ts)) . " • " . $fixture_status;
                        } else {
                            echo $fixture_status;
                        }
                    echo "</div>";
                    echo "<div class='team-placeholder'>" . $ftsy_score . "</div>";
                echo "</div>";
            echo "</div>";                    
        echo "</div>";

        /*********************************/
        /* Loop bench and lineup players */
        /*********************************/

        $playertype_to_loop = [array('headline' => 'EINGESETZE SPIELER', 'sql_value' => '1' ), array('headline' => 'BANK', 'sql_value' => '0' )];
            
        foreach ($playertype_to_loop AS $section_index => &$playertype_loop_value) {
            $sql_value = $playertype_loop_value['sql_value'];
            
            // Add wrapper div for section alignment
            if ($section_index == 0) {
                echo "<div class='team-section-lineup'>";
            } else {
                echo "<div class='team-section-bench'>";
            }
            
            // Section Headline
            echo "<h3>".$playertype_loop_value['headline']."</h3>";
                
            // Construct SQLs that differ for current rounds & past rounds
            $join_scoring_table = "";
            $join_ownership_tables = "";
            $join_projection_table = "";
            $where_team_condition = "";
            $select_projection = "";
            $select_redyellowcards = "";

            if ($clicked_spieltag == $akt_spieltag) {
                // Current round
                $join_scoring_table = "
                    LEFT JOIN xa7580_db1.ftsy_scoring_akt_v ftsy
                        ON ftsy.player_id = base.id
                ";
                $join_ownership_tables = "
                    LEFT JOIN xa7580_db1.ftsy_player_ownership own 
                        ON own.player_id = base.id
                    LEFT JOIN xa7580_db1.users u
                        ON u.id = own.`1_ftsy_owner_id`
                ";
                $join_projection_table = "
                    LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj
                        ON proj.player_id = base.id
                ";
                $where_team_condition = "AND base.current_team_id = '".$team_id."'";
                $select_projection = ", proj.ftsy_score_projected";
                $select_redyellowcards = "                    
                    , ftsy.redyellowcards_stat
                    , COALESCE(ftsy.redyellowcards_ftsy,0) AS redyellowcards_ftsy
                    ";
            } elseif ($clicked_spieltag < $akt_spieltag) {
                // Round in the past
                $join_scoring_table = "
                    LEFT JOIN xa7580_db1.ftsy_scoring_hist ftsy 
                        ON ftsy.player_id = base.id
                        AND ftsy.round_name = '".$clicked_spieltag."'
                        AND ftsy.season_id = '".$akt_season_id."'
                ";
                $join_ownership_tables = "
                    LEFT JOIN xa7580_db1.users u
                        ON u.id = ftsy.`1_ftsy_owner_id`
                ";
                $join_projection_table = "";
                $where_team_condition = "AND ftsy.current_team_id = '".$team_id."'";
                $select_projection = ", NULL AS ftsy_score_projected";
                $select_redyellowcards = "                    
                    , ftsy.redyellowcards_stat
                    , COALESCE(ftsy.redyellowards_ftsy,0) AS redyellowcards_ftsy
                    ";
            }
            
            // Actual SQL query to fetch players for the given team and lineup/bench status
            $result = mysqli_query($con,"   
                SELECT  
                    base.id
                    , COALESCE(ftsy.position_short, base.position_short) AS position_short
                    , base.image_path                                                             
                    , base.display_name
                    , u.teamname AS user_teamname
                    , CONCAT('@', u.team_code) AS user_teamcode
                    , ftsy.ftsy_score
                    , ftsy.minutes_played_stat
                    , CASE WHEN ftsy.minutes_played_stat IS NULL and ftsy.appearance_stat = 1 THEN '1 Min.' 
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
                    , COALESCE(ftsy.hit_woodwork_ftsy,0) AS hit_woodwork_ftsy
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
                    ".$select_redyellowcards."
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
                    , CASE WHEN topxi.player_id IS NOT NULL THEN 1 ELSE 0 END AS topxi_flg
                    ".$select_projection."
                FROM xa7580_db1.sm_playerbase base 
                ".$join_scoring_table."
                ".$join_ownership_tables."
                ".$join_projection_table."
                LEFT JOIN xa7580_db1.topxi_fabu_ovr topxi
                    ON topxi.player_id = base.id
                    AND topxi.season_id = '".$akt_season_id."'
                    AND topxi.round_name = '".$clicked_spieltag."'
                    AND topxi_lvl = 'RND'
                WHERE 
                    COALESCE(ftsy.appearance_stat, 0) = '".$sql_value."'
                    ".$where_team_condition."   
                ORDER BY 
                    CASE 
                        WHEN base.position_short = 'ST' THEN 1 
                        WHEN base.position_short = 'MF' THEN 2 
                        WHEN base.position_short = 'AW' THEN 3 
                        WHEN base.position_short = 'TW' THEN 4 
                        END
                    , ftsy.minutes_played_stat DESC
                ;");        

            echo "<div class='kader'><table class='roster-table'>";

            /**************************************/
            /* Loop over players and display rows */
            /**************************************/

            while($row = mysqli_fetch_array($result)) {

                /* Determine actual score display with color coding */
                $actual_score = ($row['ftsy_score'] !== NULL) ? number_format($row['ftsy_score'], 1) : '-';
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

                /* Determine projected score display for current round */
                if ($clicked_spieltag == $akt_spieltag) {
                    $projected_score = $row['ftsy_score_projected'];
                        if ($kickoff_ts <= time()) {
                            $actual_score = isset($row['ftsy_score']) ? number_format($row['ftsy_score'], 1) : '0';
                        } else {
                            $actual_score = '-';
                        }
                        $minutes_played = isset($row['appearance_stat_x']) ? $row['appearance_stat_x'] : 0;
                        if (strtotime($kickoff_ts) <= time() && $fixture_status != 'FT') {
                            $remaining_minutes = 90 - $minutes_played;
                            $adjusted_projected_score = max($projected_score - 4, 0);
                            $live_projection_score = $row['ftsy_score'] + ($adjusted_projected_score / 90 * $remaining_minutes);
                            $live_projection_score = number_format($live_projection_score, 1);
                        } else {
                            $live_projection_score = number_format($projected_score, 1);
                        }
                        $projection_class = ($live_projection_score == 0) ? 'projection zero-score' : 'projection';
                    $player_score_display = "<div class='player-score-wrapper'><span class='$projection_class'>&#8605 $live_projection_score</span><span class='actual-score $actual_score_class'>$actual_score</span></div>";
                } else {
                    $player_score_display = "<div class='player-score-wrapper'><span class='actual-score $actual_score_class'>$actual_score</span></div>";
                }

                /* Player row */
                echo "<tr class='roster-row'>";
                    echo "<td style='display:none;'>" . $row['id'] . "</td>";
                    echo "<td class='player-position'>" . $row['position_short'] . "</td>";
                    echo "<td class='player-image'><img height='30px' width='auto' src='" . $row['image_path'] . "'></td>";
                    $full_name = $row['display_name'];       
                    $name_parts = explode(" ", $full_name);  
                    $shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts); 
                    if ($row['id'] == $player_id_motm) {
                        $shortened_name =  $shortened_name . '<span title="Man of the Match"> 👑</span>';
                    } elseif ($row['id'] == $player_id_totm) {
                        $shortened_name =  $shortened_name . '<span title="Turd of the Match"> 💩</span>';
                    }
                    if ($row['topxi_flg'] == 1) {
                        $shortened_name =  $shortened_name . '<span title="Elf der Woche"> ⭐</span>';
                    }
                    echo "<td class='player-name'>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>"; 
                    echo "<td class='player-owner' title='" . $row['user_teamname'] . "'>" . $row['user_teamcode'] . "</td>";
                    echo "<td class='player-minutes'>" . $row['appearance_stat_x'] . "</td>";
                    echo "<td class='player-score'>" . $player_score_display . "</td>";
                echo "</tr>";

                // Detailed stats in hidden popup row
                echo "<tr class='player-detail'><td class='player-detail-cell' colspan='6'>";
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
                        /* Projection */
                        if( $clicked_spieltag == $akt_spieltag) {
                            echo( 'Projection: <span style="color: blue">' . $row['ftsy_score_projected'] . ' </span>' );   
                        }
                    } else {
                        echo "<span>Keine Einsatzdaten vorhanden.</span>";
                    }
                echo "</td></tr>";
            }
            echo "</table></div>";
            echo "</div>"; 
        }
        echo "</div>"; // Close team column
    }
    echo "</div>"; // Close 2-column container
    ?>
    </div> 
</div>

<script>
// Function to adjust colspan based on screen size
function adjustColspan() {
    const isMobile = window.innerWidth <= 768;
    const detailCells = document.querySelectorAll('.player-detail-cell');
    
    detailCells.forEach(cell => {
        cell.setAttribute('colspan', isMobile ? '3' : '6');
    });
}

// Run on page load
document.addEventListener('DOMContentLoaded', adjustColspan);

// Run on window resize
window.addEventListener('resize', adjustColspan);
</script>

</body>
</html>