<?php
require("auth.php");
require("../secrets/mysql_db_connection.php");

// Get meta-data
$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$parameter_result = mysqli_query($con, "SELECT spieltag, season_id FROM xa7580_db1.parameter ") -> fetch_object();
$akt_spieltag = $parameter_result -> spieltag;	
$akt_season_id = $parameter_result -> season_id;

// Fetch Fantasy fixtures from DB
$result = mysqli_query($con,"
    SELECT 	
        sch.ftsy_home_name AS home
        , sch.ftsy_away_name AS away
        , sch.ftsy_match_id AS match_id
        , CASE WHEN h.aufgestellt_home != 11 OR h.aufgestellt_home IS NULL THEN -20 ELSE COALESCE(h.score_home,0) END AS score_home
        , CASE WHEN a.aufgestellt_away != 11 OR a.aufgestellt_away IS NULL THEN -20 ELSE COALESCE(a.score_away,0) END AS score_away
    FROM xa7580_db1.ftsy_schedule sch
    LEFT JOIN (
        SELECT
            base.".$ftsy_owner_column." AS besitzer
            , SUM(akt.ftsy_score) AS score_home
            , COUNT(base.player_id) AS aufgestellt_home
        FROM xa7580_db1.ftsy_player_ownership base
        LEFT JOIN xa7580_db1.ftsy_scoring_akt_mv akt
            ON base.player_id = akt.player_id
        WHERE 
            base.".$ftsy_status_column." != 'NONE'
        GROUP BY base.".$ftsy_owner_column."
        ) h
        ON h.besitzer = sch.ftsy_home_id
    LEFT JOIN (
        SELECT 	
            base.".$ftsy_owner_column." AS besitzer
            , SUM(akt.ftsy_score) AS score_away
            , COUNT(base.player_id) AS aufgestellt_away
        FROM xa7580_db1.ftsy_player_ownership base
        LEFT JOIN xa7580_db1.ftsy_scoring_akt_mv akt
            ON base.player_id = akt.player_id
        WHERE 
            base.".$ftsy_status_column." != 'NONE'
        GROUP BY base.".$ftsy_owner_column."
        ) a
        ON a.besitzer = sch.ftsy_away_id
    WHERE 
        sch.buli_round_name = '".$akt_spieltag."'
        AND sch.season_id = '".$akt_season_id."'
");

// Print out results as a table
echo "<table class='scores_table'>";
    while($row = mysqli_fetch_array($result)) {
        $link = 'html/view_match.php?ID=' . strval($row['match_id']);
        echo "<tbody class='fixture_row'>";
            echo "<tr class='tr_home'><td><a href='" . $link . "' >" . mb_convert_encoding($row['home'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . mb_convert_encoding($row['score_home'],'UTF-8') . "</a></td></tr>";
            echo "<tr class='tr_away'><td><a href='" . $link . "' >" . mb_convert_encoding($row['away'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . mb_convert_encoding($row['score_away'],'UTF-8') . "</a></td></tr>";
        echo "</tbody>";
    }
echo "</table>";

echo "<div id='table_nav'><a id='' href='html/spieltag.php'>» Zum Fantasy-Spieltag</a></div>";
?>