<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get current round name and season id from database
$parameter_result = mysqli_query($con, "SELECT spieltag, season_id from xa7580_db1.parameter ") -> fetch_object();
$akt_round_name = $parameter_result -> spieltag;	
$akt_season_id = $parameter_result -> season_id;	

// Fetch current bundesliga fixtures
$result = mysqli_query($con,"	
    SELECT 	
        fix.fixture_id
        , fix.localteam_name AS home
        , fix.visitorteam_name AS away
        , fix.localteam_score AS tor_home
        , fix.visitorteam_score AS tor_away
    FROM xa7580_db1.sm_fixtures_basic_v fix
    WHERE 	
        fix.round_name = '".$akt_round_name."'
        AND fix.season_id = '".$akt_season_id."'
    ORDER BY fix.kickoff_ts ASC
    ");

// Print out results as a table
echo "<table class='scores_table'>";
    while($row = mysqli_fetch_array($result)) {
        $link = 'html/view_match_buli.php?ID=' . strval($row['fixture_id']);
        echo "<tbody class='fixture_row'>";
            echo "<tr class='tr_home'><td><a href='" . $link . "' >" . mb_convert_encoding($row['home'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . $row['tor_home'] . "</a></td></tr>";
            echo "<tr class='tr_away'><td><a href='" . $link . "' >" . mb_convert_encoding($row['away'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . $row['tor_away'] . "</a></td></tr>";
        echo "</tbody>";
    }
echo "</table>";

echo "<div id='table_nav'><a id='' href='html/spieltag_buli.php'>» Zum Bundesliga-Spieltag</a></div>";
?> 