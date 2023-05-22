<?php
require("auth.php");
require("../secrets/mysql_db_connection.php");

// Get actual meta-data from MySQL DB

$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;  

// Fetch Fantasy fixtures from MySQL DB

$result = mysqli_query($con,"	SELECT 	sch.ftsy_home_name as home
										, sch.ftsy_away_name as away
										, sch.ftsy_match_id as match_id
										, case when h.aufgestellt_home != 11 or h.aufgestellt_home is null then -20 else coalesce(h.score_home,0) end as score_home
										, case when a.aufgestellt_away != 11 or a.aufgestellt_away is null then -20 else coalesce(a.score_away,0) end as score_away
								
								FROM xa7580_db1.ftsy_schedule sch

								LEFT JOIN (
									select 	base.".$ftsy_owner_column." as besitzer
											, sum(akt.ftsy_score) as score_home
											, count(base.player_id) as aufgestellt_home

									from xa7580_db1.ftsy_player_ownership base
									left join xa7580_db1.ftsy_scoring_akt_v akt
										on base.player_id = akt.player_id
									where base.".$ftsy_status_column." != 'NONE'
									group by base.".$ftsy_owner_column."
									) h
									on h.besitzer = sch.ftsy_home_id

								LEFT JOIN (
									select 	base.".$ftsy_owner_column." as besitzer
											, sum(akt.ftsy_score) as score_away
											, count(base.player_id) as aufgestellt_away

									from xa7580_db1.ftsy_player_ownership base
									left join xa7580_db1.ftsy_scoring_akt_v akt
										on base.player_id = akt.player_id
									where base.".$ftsy_status_column." != 'NONE'
									group by base.".$ftsy_owner_column."
									) a
									on a.besitzer = sch.ftsy_away_id									

								WHERE sch.buli_round_name = '".$akt_spieltag."'
									  and sch.season_id = '".$akt_season_id."'
	");

// Print out results

echo "<table class='scores_table'>";

while($row = mysqli_fetch_array($result)) {
	$link = 'view_match.php?ID=' . strval($row['match_id']);
	echo "<tr class='tr_home'><td><a href='" . $link . "' >" . mb_convert_encoding($row['home'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . mb_convert_encoding($row['score_home'],'UTF-8') . "</a></td></tr>";
	echo "<tr class='tr_away'><td><a href='" . $link . "' >" . mb_convert_encoding($row['away'], 'UTF-8') . "</a></td><td><a href='" . $link . "' >" . mb_convert_encoding($row['score_away'],'UTF-8') . "</a></td></tr>";
	}
echo "</table>";

?> 