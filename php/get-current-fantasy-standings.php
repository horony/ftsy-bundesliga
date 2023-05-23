<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get current round name and season id from MySQL DB

$akt_round_name = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	
$league_id = $_SESSION['league_id'];
$vor_spieltag = $akt_round_name - 1;

// Fetch Fantasy standings from MySQL DB

$result = mysqli_query($con,"	SELECT 	rang, team_name as team, siege, niederlagen, punkte

								FROM xa7580_db1.ftsy_tabelle_2020

								WHERE league_id = '".intval($league_id)."'
									  and season_id = '".$akt_season_id."'
									  and spieltag = (select max(spieltag) from ftsy_tabelle_2020 where season_id = '".$akt_season_id."')
								
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