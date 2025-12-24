<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Collect meta data
$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

$result_params_sql = mysqli_query($con, "SELECT spieltag, season_id from xa7580_db1.parameter ") -> fetch_object();
$akt_spieltag = $result_params_sql->spieltag;
$akt_season_id = $result_params_sql->season_id;

$selected_spieltag = $_GET["spieltag"];

$result_schedule_sql = mysqli_query($con,"
    SELECT 
        sch.match_type
        , sch.cup_round
        , sch.cup_leg
    FROM xa7580_db1.ftsy_schedule sch 
    WHERE 
        sch.buli_round_name = '$selected_spieltag' 
        AND sch.season_id = '$akt_season_id'  
") -> fetch_object();

$match_type = $result_schedule_sql->match_type;
$cup_round = $result_schedule_sql->cup_round;
$cup_leg = $result_schedule_sql->cup_leg;

// Display headline based on match type
if ($match_type == 'cup'){
    echo "<div id='headline'><h2>Spieltag " . $selected_spieltag . " 🏆</h2></div>";
    if ($cup_round == 'playoff' and $cup_leg == 1){		
        echo "<div id='subheadline'><h3>Pokal Playoffs - Hinspiel</h3></div>";
    } elseif ($cup_round == 'playoff' and $cup_leg == 2) {	
        echo "<div id='subheadline'><h3>Pokal Playoffs - Rückspiel</h3></div>";
    } elseif ($cup_round == 'quarter' and $cup_leg == 1) {
        echo "<div id='subheadline'><h3>Pokal Viertelfinale - Hinspiel</h3></div>";
    } elseif ($cup_round == 'quarter' and $cup_leg == 2) {
        echo "<div id='subheadline'><h3>Pokal Viertelfinale - Rückspiel</h3></div>";
    } elseif ($cup_round == 'semi' and $cup_leg == 1) {
        echo "<div id='subheadline'><h3>Pokal Halbfinale - Hinspiel</h3></div>";
    } elseif ($cup_round == 'semi' and $cup_leg == 2) {
        echo "<div id='subheadline'><h3>Pokal Halbfinale - Rückspiel</h3></div>";
    } elseif ($cup_round == 'final') {
        echo "<div id='subheadline'><h3>Pokal Finale</h3></div>";
    }
} else {
    echo "<div id='headline'><h2>Spieltag " . $selected_spieltag . "</h2></div>";
}

if($selected_spieltag < $akt_spieltag) {
    // Top XI Link for past rounds
    echo "<div id='topxi-link-container' class='topxi-link-container'>";
        echo "<a href='topxi.php?nav=FABU&season=" . $akt_season_id . "&round=" . $selected_spieltag . "' id='topxi-link' class='topxi-link' title='Zur Elf der Woche'>» Elf der Woche ⭐</a>";
    echo "</div>";
}

// Fetch fixture data based on match type and round status
if ($selected_spieltag < $akt_spieltag and $match_type == 'league') {
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
                sch.ftsy_match_id AS match_id
                , sch.ftsy_home_id
                , sch.ftsy_away_id
                , u1.team_code AS ftsy_home_cd
                , u2.team_code AS ftsy_away_cd
                , sch.ftsy_home_name
                , sch.ftsy_away_name
                , sch.ftsy_home_score
                , sch.ftsy_away_score
                , tab1.avg_for AS ftsy_home_avg 
                , tab2.avg_for AS ftsy_away_avg
                , tab1.rang AS ftsy_home_position
                , tab2.rang AS ftsy_away_position
                , CONCAT(tab1.siege,'-',tab1.unentschieden,'-',tab1.niederlagen) AS ftsy_home_record
                , CONCAT(tab2.siege,'-',tab2.unentschieden,'-',tab2.niederlagen) AS ftsy_away_record
                , tab1.serie AS ftsy_home_serie
                , tab2.serie AS ftsy_away_serie
            FROM xa7580_db1.ftsy_schedule sch
            LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab1
                ON sch.ftsy_home_id = tab1.player_id 
                AND tab1.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE spieltag < '$selected_spieltag' AND season_id = '$akt_season_id')
                AND tab1.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab2
                ON sch.ftsy_away_id = tab2.player_id 
                AND tab2.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE spieltag < '$selected_spieltag' AND season_id = '$akt_season_id')
                AND tab2.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id                
            WHERE 
                sch.buli_round_name = '$selected_spieltag'
                AND sch.season_id = '$akt_season_id'
        )
        SELECT
            match_id
            , ftsy_home_id
            , ftsy_away_id
            , ftsy_home_name
            , ftsy_away_name
            , ftsy_home_cd
            , ftsy_away_cd
            , CASE 
                WHEN ftsy_home_score >= ftsy_away_score 
                    THEN CONCAT('<b>',ftsy_home_score,'</b>') 
                WHEN ftsy_home_score < ftsy_away_score 
                    AND ftsy_home_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT(ftsy_home_score,'<span style=\'display:inline-block; font-size:0.7rem; line-height:1; transform:translateY(-1em);\'>🍀</span>')     
                ELSE ftsy_home_score 
            END AS ftsy_home_score
            , CASE 
                WHEN ftsy_away_score >= ftsy_home_score 
                    THEN CONCAT('<b>',ftsy_away_score,'</b>') 
                WHEN ftsy_away_score < ftsy_home_score 
                    AND ftsy_away_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT(ftsy_away_score,'<span style=\'display:inline-block; font-size:0.7rem; line-height:1; transform:translateY(-1em);\'>🍀</span>')     
                ELSE ftsy_away_score 
            END AS ftsy_away_score
        , CONCAT('⌀',COALESCE(ftsy_home_avg, '-')) AS ftsy_home_avg
        , CONCAT('⌀',COALESCE(ftsy_away_avg, '-')) AS ftsy_away_avg
        , ftsy_home_position AS home_description_1
        , ftsy_away_position AS away_description_1
        , ftsy_home_record AS home_description_2
        , ftsy_away_record AS away_description_2
        , ftsy_home_serie AS home_description_3
        , ftsy_away_serie AS away_description_3
        FROM cte_schedule
    ");
} elseif ($selected_spieltag > $akt_spieltag and $match_type == 'league'){
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
                sch.ftsy_match_id AS match_id
                , sch.ftsy_home_id
                , sch.ftsy_away_id
                , u1.team_code AS ftsy_home_cd
                , u2.team_code AS ftsy_away_cd
                , sch.ftsy_home_name
                , sch.ftsy_away_name
                , sch.ftsy_home_score
                , sch.ftsy_away_score
                , tab1.avg_for AS ftsy_home_avg 
                , tab2.avg_for AS ftsy_away_avg
                , tab1.rang AS ftsy_home_position
                , tab2.rang AS ftsy_away_position
                , CONCAT(tab1.siege,'-',tab1.unentschieden,'-',tab1.niederlagen) AS ftsy_home_record
                , CONCAT(tab2.siege,'-',tab2.unentschieden,'-',tab2.niederlagen) AS ftsy_away_record
                , tab1.serie AS ftsy_home_serie
                , tab2.serie AS ftsy_away_serie
            FROM xa7580_db1.ftsy_schedule sch
                LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab1
                ON sch.ftsy_home_id = tab1.player_id 
                AND tab1.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = '$akt_season_id')
                AND tab1.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab2
                ON sch.ftsy_away_id = tab2.player_id 
                AND tab2.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = '$akt_season_id')
                AND tab2.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id    
            WHERE 
                sch.buli_round_name = '$selected_spieltag'
                AND sch.season_id = '$akt_season_id'
        )
        SELECT
            match_id
            , ftsy_home_id
            , ftsy_away_id
            , ftsy_home_cd
            , ftsy_away_cd
            , ftsy_home_name
            , ftsy_away_name
            , CASE 
                WHEN ftsy_home_score >= ftsy_away_score 
                    THEN CONCAT('<b>',ftsy_home_score,'</b>') 
                WHEN ftsy_home_score < ftsy_away_score 
                    AND ftsy_home_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT('<i>',ftsy_home_score,'</i>') 
                ELSE ftsy_home_score 
                END AS ftsy_home_score       
            , CASE 
                WHEN ftsy_away_score >= ftsy_home_score 
                    THEN CONCAT('<b>',ftsy_away_score,'</b>') 
                WHEN ftsy_away_score < ftsy_home_score 
                    AND ftsy_away_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT('<i>',ftsy_away_score,'</i>')     
                ELSE ftsy_away_score 
                END AS ftsy_away_score
            , CONCAT('⌀', COALESCE(ftsy_home_avg, '-')) AS ftsy_home_avg
            , CONCAT('⌀', COALESCE(ftsy_away_avg, '-')) AS ftsy_away_avg
            , ftsy_home_position AS home_description_1
            , ftsy_away_position AS away_description_1
            , ftsy_home_record AS home_description_2
            , ftsy_away_record AS away_description_2
            , ftsy_home_serie AS home_description_3
            , ftsy_away_serie AS away_description_3
        FROM cte_schedule
    ");
} elseif ($selected_spieltag == $akt_spieltag and $match_type == 'league') {
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
                sch.ftsy_match_id AS match_id
                , sch.ftsy_home_id
                , u1.team_code AS ftsy_home_cd
                , sch.ftsy_away_id
                , u2.team_code AS ftsy_away_cd
                , sch.ftsy_home_name
                , sch.ftsy_away_name
                , scr1.ftsy_score_sum AS ftsy_home_score
                , scr2.ftsy_score_sum AS ftsy_away_score
                , scr1.ftsy_score_projected_sum AS ftsy_home_proj
                , scr2.ftsy_score_projected_sum AS ftsy_away_proj
                , tab1.rang AS ftsy_home_position
                , tab2.rang AS ftsy_away_position
                , CONCAT(tab1.siege,'-',tab1.unentschieden,'-',tab1.niederlagen) AS ftsy_home_record
                , CONCAT(tab2.siege,'-',tab2.unentschieden,'-',tab2.niederlagen) AS ftsy_away_record
                , scr1.players_status AS ftsy_home_players_status
                , scr2.players_status AS ftsy_away_players_status
            FROM xa7580_db1.ftsy_schedule sch
            LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab1
                ON sch.ftsy_home_id = tab1.player_id 
                AND tab1.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = '$akt_season_id')
                AND tab1.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab2
                ON sch.ftsy_away_id = tab2.player_id 
                AND tab2.spieltag = (SELECT MAX(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = '$akt_season_id')
                AND tab2.season_id = '$akt_season_id'
            LEFT JOIN xa7580_db1.users_scoring_akt_v scr1
                ON sch.ftsy_home_id = scr1.user_id 
            LEFT JOIN xa7580_db1.users_scoring_akt_v scr2
                ON sch.ftsy_away_id = scr2.user_id
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id
            WHERE 
                sch.buli_round_name = '$selected_spieltag'
                AND sch.season_id = '$akt_season_id'
        )
        SELECT
            match_id
            , ftsy_home_id
            , ftsy_home_cd
            , ftsy_away_id
            , ftsy_away_cd
            , ftsy_home_name
            , ftsy_away_name
            , CASE 
                WHEN ftsy_home_score >= ftsy_away_score 
                    THEN CONCAT('<b>',ftsy_home_score,'</b>') 
                WHEN ftsy_home_score < ftsy_away_score 
                    AND ftsy_home_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT(ftsy_home_score,'<span style=\"display:inline-block; font-size:0.5rem; line-height:1; transform:translateY(-1em);\">🍀</span>')     
                    ELSE ftsy_home_score 
                    END AS ftsy_home_score       
            , CASE 
                WHEN ftsy_away_score >= ftsy_home_score 
                    THEN CONCAT('<b>',ftsy_away_score,'</b>') 
                WHEN ftsy_away_score < ftsy_home_score 
                    AND ftsy_away_score = (SELECT MAX(LEAST(ftsy_home_score,ftsy_away_score)) FROM cte_schedule) 
                    THEN CONCAT(ftsy_away_score,'<span style=\"display:inline-block; font-size:0.5rem; line-height:1; transform:translateY(-1em);\">🍀</span>')     
                    ELSE ftsy_away_score 
                    END AS ftsy_away_score
            , CONCAT('<p style=\'color:blue\'>','↝',ftsy_home_proj,'</p>') AS ftsy_home_avg
            , CONCAT('<p style=\'color:blue\'>','↝',ftsy_away_proj,'</p>') AS ftsy_away_avg
            , ftsy_home_position AS home_description_1
            , ftsy_away_position AS away_description_1
            , ftsy_home_record AS home_description_2
            , ftsy_away_record AS away_description_2
            , ftsy_home_players_status AS home_description_3
            , ftsy_away_players_status AS away_description_3
        FROM cte_schedule
    ");
} elseif ($selected_spieltag < $akt_spieltag and $match_type == 'cup') {
    // Get fixture data from database
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
                sch.ftsy_match_id AS match_id
                , sch.ftsy_home_id
                , sch.ftsy_away_id
                , u1.team_code AS ftsy_home_cd
                , u2.team_code AS ftsy_away_cd
                , sch.ftsy_home_name
                , sch.ftsy_away_name
                , sch.ftsy_home_score
                , sch.ftsy_away_score
                , sch.cup_leg
                , sch.cup_round
                , CASE WHEN sch.ftsy_home_id = sch2.ftsy_home_id THEN sch2.ftsy_home_score ELSE sch2.ftsy_away_score END AS ftsy_home_score_leg1
                , CASE WHEN sch.ftsy_away_id = sch2.ftsy_away_id THEN sch2.ftsy_away_score ELSE sch2.ftsy_home_score END AS ftsy_away_score_leg1                
            FROM xa7580_db1.ftsy_schedule sch
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id
            LEFT JOIN xa7580_db1.ftsy_schedule sch2
                ON sch2.cup_leg = 1
                AND sch.cup_round = sch2.cup_round 
                AND sch.season_id = sch2.season_id 
                AND sch.ftsy_match_id != sch2.ftsy_match_id
                AND (sch.ftsy_home_id = sch2.ftsy_home_id  OR sch.ftsy_home_id = sch2.ftsy_away_id)               
            WHERE 
                sch.buli_round_name = '$selected_spieltag'
                AND sch.season_id = '$akt_season_id'
        )
        SELECT
            match_id
            , ftsy_home_id
            , ftsy_away_id
            , ftsy_home_cd
            , ftsy_away_cd
            , ftsy_home_name
            , ftsy_away_name
            , CASE 
                WHEN ftsy_home_score >= ftsy_away_score THEN CONCAT('<b>',ftsy_home_score,'</b>') 
                ELSE ftsy_home_score 
                END AS ftsy_home_score       
            , CASE 
                WHEN ftsy_away_score >= ftsy_home_score THEN CONCAT('<b>',ftsy_away_score,'</b>') 
                ELSE ftsy_away_score 
                END AS ftsy_away_score
            , cup_leg
            , cup_round
            , CASE 
                WHEN ftsy_home_score_leg1 >= ftsy_away_score_leg1 THEN CONCAT('<b>',ftsy_home_score_leg1,'</b>') 
                ELSE ftsy_home_score_leg1 
                END AS ftsy_home_score_leg1       
            , CASE 
                WHEN ftsy_away_score_leg1 >= ftsy_home_score_leg1 THEN CONCAT('<b>',ftsy_away_score_leg1,'</b>') 
                ELSE ftsy_away_score_leg1 
                END AS ftsy_away_score_leg1
            , CASE 
                WHEN ftsy_home_score+ftsy_home_score_leg1 >= ftsy_away_score+ftsy_away_score_leg1 THEN CONCAT('<b>',ROUND(COALESCE(ftsy_home_score+ftsy_home_score_leg1,0),1) ,'</b>') 
                ELSE ROUND(COALESCE(ftsy_home_score+ftsy_home_score_leg1,0),1)
                END AS ftsy_home_score_agg       
            , CASE 
                WHEN ftsy_away_score+ftsy_away_score_leg1 >= ftsy_home_score+ftsy_home_score_leg1 THEN CONCAT('<b>',ROUND(COALESCE(ftsy_away_score+ftsy_away_score_leg1,0),1),'</b>') 
                ELSE ROUND(COALESCE(ftsy_away_score+ftsy_away_score_leg1,0),1)
                END AS ftsy_away_score_agg
            , '' AS ftsy_home_avg
            , '' AS ftsy_away_avg
            , '' AS home_description_1
            , '' AS away_description_1
            , '' AS home_description_2
            , '' AS away_description_2
        FROM cte_schedule
        ");

} elseif ($selected_spieltag > $akt_spieltag and $match_type == 'cup') {
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
            sch.ftsy_match_id AS match_id
            , sch.cup_leg
            , sch.cup_round
            , sch.ftsy_home_id
            , sch.ftsy_away_id
            , u1.team_code AS ftsy_home_cd
            , u2.team_code AS ftsy_away_cd
            , sch.ftsy_home_name
            , sch.ftsy_away_name
            , sch.ftsy_home_score
            , sch.ftsy_away_score
            , stats1.cup_ftsy_score_for_avg AS ftsy_home_avg 
            , stats2.cup_ftsy_score_for_avg AS ftsy_away_avg
            , stats1.cup_record AS ftsy_home_record
            , stats2.cup_record AS ftsy_away_record
            , ROUND(CASE WHEN sch.ftsy_home_id = sch2.ftsy_home_id THEN sch2.ftsy_home_score ELSE sch2.ftsy_away_score END,1) AS ftsy_home_score_leg1
            , ROUND(CASE WHEN sch.ftsy_away_id = sch2.ftsy_away_id THEN sch2.ftsy_away_score ELSE sch2.ftsy_home_score END,1) AS ftsy_away_score_leg1
            FROM xa7580_db1.ftsy_schedule sch
            LEFT JOIN xa7580_db1.ftsy_schedule sch2
                ON sch2.cup_leg = 1
                AND sch.cup_round = sch2.cup_round 
                AND sch.season_id = sch2.season_id 
                AND sch.ftsy_match_id != sch2.ftsy_match_id
                AND (sch.ftsy_home_id = sch2.ftsy_home_id  OR sch.ftsy_home_id = sch2.ftsy_away_id)
            LEFT JOIN xa7580_db1.users_cup_stats_v stats1
                ON sch.ftsy_home_id = stats1.user_id 
            LEFT JOIN xa7580_db1.users_cup_stats_v stats2
                ON sch.ftsy_away_id = stats2.user_id
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id    
            WHERE 
                sch.buli_round_name = '$selected_spieltag'
                AND sch.season_id = '$akt_season_id'
    )
    SELECT
        match_id
        , cup_leg
        , cup_round
        , ftsy_home_id
        , ftsy_away_id
        , ftsy_home_cd
        , ftsy_away_cd
        , ftsy_home_name
        , ftsy_away_name
        , ftsy_home_score       
        , ftsy_away_score 
        , ftsy_home_score_leg1
        , ftsy_away_score_leg1
        , COALESCE(ftsy_home_score+ftsy_home_score_leg1,0) AS ftsy_home_score_agg
        , COALESCE(ftsy_away_score+ftsy_away_score_leg1,0) AS ftsy_away_score_agg
        , CONCAT('⌀', COALESCE(ftsy_home_avg, '-')) AS ftsy_home_avg
        , CONCAT('⌀', COALESCE(ftsy_away_avg, '-')) AS ftsy_away_avg
        , ftsy_home_record AS home_description_1
        , ftsy_away_record AS away_description_1
        , '' AS home_description_2
        , '' AS away_description_2
    FROM cte_schedule
    ");
} elseif ($selected_spieltag == $akt_spieltag and $match_type == 'cup') {
    $result = mysqli_query($con,"
        WITH cte_schedule AS (
            SELECT
                sch.ftsy_match_id AS match_id
                , sch.ftsy_home_id
                , sch.ftsy_away_id
                , u1.team_code AS ftsy_home_cd
                , u2.team_code AS ftsy_away_cd
                , sch.ftsy_home_name
                , sch.ftsy_away_name
                , scr1.ftsy_score_sum AS ftsy_home_score
                , scr2.ftsy_score_sum AS ftsy_away_score
                , scr1.ftsy_score_projected_sum AS ftsy_home_proj
                , scr2.ftsy_score_projected_sum AS ftsy_away_proj
                , stats1.cup_record AS ftsy_home_record
                , stats2.cup_record AS ftsy_away_record
                , scr1.players_status AS ftsy_home_players_status
                , scr2.players_status AS ftsy_away_players_status
                , sch.cup_leg
                , sch.cup_round
                , CASE WHEN sch.ftsy_home_id = sch2.ftsy_home_id THEN sch2.ftsy_home_score ELSE sch2.ftsy_away_score END AS ftsy_home_score_leg1
                , CASE WHEN sch.ftsy_away_id = sch2.ftsy_away_id THEN sch2.ftsy_away_score ELSE sch2.ftsy_home_score END AS ftsy_away_score_leg1  
            FROM xa7580_db1.ftsy_schedule sch
            LEFT JOIN xa7580_db1.ftsy_schedule sch2
                ON sch2.cup_leg = 1
                AND sch.cup_round = sch2.cup_round 
                AND sch.season_id = sch2.season_id 
                AND sch.ftsy_match_id != sch2.ftsy_match_id
                AND (sch.ftsy_home_id = sch2.ftsy_home_id  OR sch.ftsy_home_id = sch2.ftsy_away_id)
            LEFT JOIN xa7580_db1.users_cup_stats_v stats1
                ON sch.ftsy_home_id = stats1.user_id
            LEFT JOIN xa7580_db1.users_cup_stats_v stats2
                ON sch.ftsy_away_id = stats2.user_id
            LEFT JOIN xa7580_db1.users_scoring_akt_v scr1
                ON sch.ftsy_home_id = scr1.user_id 
            LEFT JOIN xa7580_db1.users_scoring_akt_v scr2
                ON sch.ftsy_away_id = scr2.user_id
            LEFT JOIN xa7580_db1.users u1
                ON sch.ftsy_home_id = u1.id
            LEFT JOIN xa7580_db1.users u2
                ON sch.ftsy_away_id = u2.id                
            WHERE 
            sch.buli_round_name = '$selected_spieltag'
            AND sch.season_id = '$akt_season_id'
        )
        SELECT
            match_id
            , ftsy_home_id
            , ftsy_away_id
            , ftsy_home_cd
            , ftsy_away_cd
            , ftsy_home_name
            , ftsy_away_name
            , CASE 
                WHEN ftsy_home_score >= ftsy_away_score THEN CONCAT('<b>',ftsy_home_score,'</b>') 
                ELSE ftsy_home_score 
                END AS ftsy_home_score       
            , CASE 
                WHEN ftsy_away_score >= ftsy_home_score THEN CONCAT('<b>',ftsy_away_score,'</b>')  
                ELSE ftsy_away_score 
                END AS ftsy_away_score
            , CONCAT('<p style=\'color:blue\'>','↝',ROUND(ftsy_home_proj + ftsy_home_score_leg1,1),'</p>') AS ftsy_home_avg
            , CONCAT('<p style=\'color:blue\'>','↝',ROUND(ftsy_away_proj + ftsy_away_score_leg1,1),'</p>') AS ftsy_away_avg
            , cup_leg
            , cup_round
            , ROUND(COALESCE(ftsy_home_score+ftsy_home_score_leg1,0),1) AS ftsy_home_score_agg
            , ROUND(COALESCE(ftsy_away_score+ftsy_away_score_leg1,0),1) AS ftsy_away_score_agg
            , NULL AS home_description_1
            , NULL AS away_description_1
            , ftsy_home_record AS home_description_2
            , ftsy_away_record AS away_description_2
            , ftsy_home_players_status AS home_description_3
            , ftsy_away_players_status AS away_description_3
            , ftsy_home_score_leg1
            , ftsy_away_score_leg1
        FROM cte_schedule
    ");
}

// Display fixtures in HTML
while($col = mysqli_fetch_array($result)){
    echo "<div class='league-matchup-row-item'>";
        echo "<div class='matchup-header'>";
            echo "<div class='matchup-row' style='cursor: pointer;' onclick='viewMatch(".strval($col['match_id']).");'>";
                // Home Team
                echo "<div class='user'>";
                    echo "<div class='matchup-owner-item'>";
                        echo "<div class='owner-container'>";
                            echo "<div class='row space-between'>";
                                echo "<div class='row'>";
                                    echo "<div>";
                                        // Team Logo
                                        echo "<div style='background-image: url(../img/ftsy-team-logos/".$col['ftsy_home_id'].".png); border-radius: 50%; width: 40px; height: 40px; flex: 0 0 40px; background-size: contain; position: relative;'></div>";
                                    echo "</div>";
                                    echo "<div class='meta'>";
                                        // Teamname
                                        echo "<div class='teamname'>";
                                            echo "<span class='team-full-name'>".htmlspecialchars($col['ftsy_home_name'])."</span>";
                                            echo "<span class='team-short-code'>".htmlspecialchars($col['ftsy_home_cd'])."</span>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                                // Score
                                echo "<div class='roster-score-and-projection-matchup'>";
                                    if ($match_type == 'cup' && $cup_leg == 2 && !empty($col['ftsy_home_score_agg'])) {
                                        echo "<div class='cup-rematch-score-container'>";
                                            echo "<div class='cup-leg-scores-vertical'>";
                                                echo "<div class='cup-leg-score' title='Hinspiel'>".$col['ftsy_home_score_leg1']."</div>";
                                                echo "<div class='cup-leg-score' title='Rückspiel'>".$col['ftsy_home_score']."</div>";
                                            echo "</div>";
                                            echo "<div class='score' title='Gesamtergebnis'>".$col['ftsy_home_score_agg']."</div>";
                                        echo "</div>";
                                    } else {
                                        echo "<div class='score'>".$col['ftsy_home_score']."</div>";
                                    }
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                        // Bottom row with descriptions and projections
                        echo "<div class='bottom-row space-between'>";
                            echo "<div class='row'>";
                                echo "<div class='description-one'>";
                                    if ($match_type == 'league') {
                                        echo '#' . $col['home_description_1'];
                                    } else {
                                        echo $col['home_description_1'];
                                    }
                                echo "</div>";
                                echo "<div class='description-two'>";
                                    echo $col['home_description_2'];
                                echo "</div>";
                                echo "<div class='description-three'>";
                                    if ($selected_spieltag == $akt_spieltag){
                                        echo "<i class='fa-solid fa-person-running' style='font-size:8px'></i>";
                                        echo " ";
                                    }
                                    echo $col['home_description_3'];
                                echo "</div>";
                            echo "</div>";
                            echo "<div class='roster-score-and-projection-matchup'>";
                                echo "<div class='projections'>".$col['ftsy_home_avg']."</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
                // VS-Label
                echo "<div class='vs-label'><div class='label'>VS</div></div>";
                // Away Team
                echo "<div class='user reverse'>";
                    echo "<div class='matchup-owner-item'>";
                        echo "<div class='owner-container'>";
                            echo "<div class='row flip space-between'>";
                                echo "<div class='row flip'>";
                                    echo "<div>";
                                        // Team Logo
                                        echo "<div style='background-image: url(../img/ftsy-team-logos/".$col['ftsy_away_id'].".png); border-radius: 50%; width: 40px; height: 40px; flex: 0 0 40px; background-size: contain; position: relative;'></div>";
                                    echo "</div>";
                                    echo "<div class='meta flip'>";
                                        // Teamname
                                        echo "<div class='teamname'>";
                                            echo "<span class='team-full-name'>".htmlspecialchars($col['ftsy_away_name'])."</span>";
                                            echo "<span class='team-short-code'>".htmlspecialchars($col['ftsy_away_cd'])."</span>";
                                        echo "</div>";
                                    echo "</div>";
                                echo "</div>";
                                echo "<div class='roster-score-and-projection-matchup flip'>";
                                    // Score
                                    if ($match_type == 'cup' && $cup_leg == 2 && !empty($col['ftsy_away_score_agg'])) {
                                        echo "<div class='cup-rematch-score-container'>";
                                            echo "<div class='score' title='Gesamtergebnis'>".$col['ftsy_away_score_agg']."</div>";
                                            echo "<div class='cup-leg-scores-vertical'>";
                                                echo "<div class='cup-leg-score' title='Hinspiel'>".$col['ftsy_away_score_leg1']."</div>";
                                                echo "<div class='cup-leg-score' title='Rückspiel'>".$col['ftsy_away_score']."</div>";
                                            echo "</div>";
                                        echo "</div>";
                                    } else {
                                        echo "<div class='score'>".$col['ftsy_away_score']."</div>";
                                    }
                                echo "</div>";
                            echo "</div>";
                        echo "</div>";
                        // Bottom row with descriptions and projections
                        echo "<div class='bottom-row flip space-between'>";
                            echo "<div class='row flip'>";
                                echo "<div class='description-one'>";
                                    if ($match_type == 'league') {
                                        echo '#' . $col['away_description_1'];
                                    } else {
                                        echo $col['away_description_1'];
                                    }
                                echo "</div>";
                                echo "<div class='description-two'>";
                                    echo $col['away_description_2'];
                                echo "</div>";
                                echo "<div class='description-three'>";
                                    if ($selected_spieltag == $akt_spieltag){
                                        echo "<i class='fa-solid fa-person-running' style='font-size:8px'></i>";
                                        echo " ";
                                    }
                                    echo $col['away_description_3'];
                                echo "</div>";
                            echo "</div>";
                            echo "<div class='roster-score-and-projection-matchup flip'>";
                                echo "<div class='projections'>".$col['ftsy_away_avg']."</div>";
                            echo "</div>";
                        echo "</div>";
                    echo "</div>";
                echo "</div>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
}

mysqli_close($con);
?>