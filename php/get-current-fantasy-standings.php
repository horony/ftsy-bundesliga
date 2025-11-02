<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get current round name and season id from database
$result = mysqli_query($con, "SELECT spieltag, season_id FROM xa7580_db1.parameter");
$param_row = $result->fetch_object();
$akt_round_name = $param_row->spieltag;
$akt_season_id = $param_row->season_id;
$league_id = $_SESSION['league_id'];
$vor_spieltag = $akt_round_name - 1;

// Fetch Fantasy standings from database
$result = mysqli_query($con,"
    SELECT 	
        rang
        , team_name AS team
        , siege
        , niederlagen, punkte
    FROM xa7580_db1.ftsy_tabelle_2020
    WHERE 
        league_id = '".intval($league_id)."'
        AND season_id = '".$akt_season_id."'
        AND spieltag = (SELECT max(spieltag) FROM ftsy_tabelle_2020 WHERE season_id = '".$akt_season_id."')
    ORDER BY rang ASC
    ");

// Print out results
echo "<table class='scores_table'>";
    while($row = mysqli_fetch_array($result)) {
        $link = 'html/mein_team.php?show_team=' . mb_convert_encoding($row['team'], 'UTF-8');
        echo "<tr class='tr_home'><td>".$row['rang']."</td><td><a href='" . $link . "' class='news_team' data-id='" . mb_convert_encoding($row['team'], 'UTF-8') . "'>" . mb_convert_encoding($row['team'], 'UTF-8') . "</td><td>".$row['siege'].":".$row['niederlagen']."</td><td>".$row['punkte']."</td>";
        }
echo "</table>";
?> 