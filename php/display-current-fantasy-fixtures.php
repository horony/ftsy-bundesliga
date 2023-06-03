<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Collect meta data

$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';

$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;  

// Round is already finished, display historical data

if ( $_GET["spieltag"] != $akt_spieltag ) { 
	
	$spieltag_neu = $_GET["spieltag"];

	echo "<div id='headline'><h2>Spieltag " . $spieltag_neu . "</h2></div>";
	
	// If current round is cup round, add additional cup headline

	$match_type = mysqli_query($con, "SELECT match_type from xa7580_db1.ftsy_schedule sch WHERE sch.buli_round_name = '$spieltag_neu' AND sch.season_id =  '$akt_season_id'  ") -> fetch_object() -> match_type;  
	
	if ($match_type == 'cup'){
		echo "<div id='headline'><h2>Pokal</h2></div>";			
	}

	// Get fixture data from MySQL DB
	
	$result = mysqli_query($con,"
		SELECT 	sch.ftsy_match_id as match_id
						, sch.ftsy_home_name
						, sch.ftsy_away_name
						,	sch.ftsy_home_score
						, sch.ftsy_away_score
						, sch.match_type
						, tab1.rang as pos_1
						, tab2.rang as pos_2

		FROM xa7580_db1.ftsy_schedule sch

		LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab1
			ON sch.ftsy_home_id = tab1.player_id AND tab1.spieltag = '$spieltag_neu' AND tab1.season_id = (SELECT season_id FROM parameter)

		LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab2
			ON sch.ftsy_away_id = tab2.player_id AND tab2.spieltag = '$spieltag_neu' AND tab2.season_id = (SELECT season_id FROM parameter)

		WHERE sch.buli_round_name = '$spieltag_neu'
		      and sch.season_id = (SELECT season_id FROM parameter)
	");

	// Display fixtures in table

	echo "<table id='myTable'>";
		while($col = mysqli_fetch_array($result)){
			echo "<tr onclick='viewMatch(".$col['match_id'].");'>";
			echo "<td style='display:none;'>" . $col['match_id'] . "</td>";	
			echo "<td align='right'><small>#" . $col['pos_1'] . "</small> " . mb_convert_encoding($col['ftsy_home_name'], 'UTF-8') . "</td>";
			echo "<td style='font-size: 26px; padding: 5px;' align='center' class='scr'>" . $col['ftsy_home_score'] . "</td>";
			echo "<td style='font-size: 26px; padding: 5px;' align='center' class='scr'>" . $col['ftsy_away_score'] . "</td>";
			echo "<td align='left'><small>#" . $col['pos_2'] . "</small> " . mb_convert_encoding($col['ftsy_away_name'], 'UTF-8') . "</td>";
			echo "</tr>";
		}
	echo "</table>";

// If round is active, current round display live scores

} else {

		$spieltag_neu = $akt_spieltag;

		echo "<div id='headline'><h2>Spieltag " . $spieltag_neu . "</h2></div>";

		// If current round is cup round, add additional cup headline

		$match_type = mysqli_query($con, "SELECT match_type from xa7580_db1.ftsy_schedule sch WHERE sch.buli_round_name = '$spieltag_neu' AND sch.season_id =  '$akt_season_id'  ") -> fetch_object() -> match_type;  
		if ($match_type == 'cup'){
			echo "<div id='headline'><h2>Pokal</h2></div>";			
		}

		// Get fixture data from MySQL DB

		$result = mysqli_query($con, "
			SELECT 	sch.ftsy_match_id as match_id
							, sch.ftsy_home_name
							, sch.ftsy_away_name
							, sch.match_type
							, COALESCE(score_home,0) AS fantasy_score1
							, COALESCE(score_away,0) AS fantasy_score2
							, tab1.rang as pos_1
							, tab2.rang as pos_2
							, COALESCE(aufgestellt_home, 0) as anz_spieler1
							, COALESCE(aufgestellt_away,0) as anz_spieler2

			FROM xa7580_db1.ftsy_schedule sch

			LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab1
				ON sch.ftsy_home_id = tab1.player_id AND tab1.spieltag = '$spieltag_neu'-1 AND tab1.season_id = (SELECT season_id FROM parameter)

			LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab2
				ON sch.ftsy_away_id = tab2.player_id AND tab2.spieltag = '$spieltag_neu'-1 AND tab2.season_id = (SELECT season_id FROM parameter)

			LEFT JOIN (
				SELECT 	base.".$ftsy_owner_column." as besitzer
								, SUM(akt.ftsy_score) as score_home
								, COUNT(base.player_id) as aufgestellt_home

				FROM xa7580_db1.ftsy_player_ownership base
			
				LEFT JOIN xa7580_db1.ftsy_scoring_akt_v akt
					ON base.player_id = akt.player_id
				WHERE base.".$ftsy_status_column." != 'NONE'
				GROUP BY base.".$ftsy_owner_column."
				) h
				ON h.besitzer = sch.ftsy_home_id

			LEFT JOIN (
				SELECT 	base.".$ftsy_owner_column." as besitzer
						, SUM(akt.ftsy_score) as score_away
						, COUNT(base.player_id) as aufgestellt_away

				FROM xa7580_db1.ftsy_player_ownership base

				LEFT JOIN xa7580_db1.ftsy_scoring_akt_v akt
					ON base.player_id = akt.player_id

				WHERE base.".$ftsy_status_column." != 'NONE'
				GROUP BY base.".$ftsy_owner_column."
				) a
				ON a.besitzer = sch.ftsy_away_id	

			WHERE sch.buli_round_name = '$spieltag_neu' 
			      and sch.season_id = (SELECT season_id FROM parameter)

			");

		// Display fixtures in table

		echo "<table id='myTable'>";
			while($col = mysqli_fetch_array($result)) {
				echo "<tr onclick='viewMatch(".strval($col['match_id']).");'>";
				echo "<td style='display:none;'>" . $col['match_id'] . "</td>";	
				echo "<td align='right'><small>#" . $col['pos_1'] . "</small> " . mb_convert_encoding($col['ftsy_home_name'], 'UTF-8') . "</td>";

				if ( $col['anz_spieler1'] == 11 ) {
					echo "<td style='font-size: 26px;' align='center' class='scr'>" . $col['fantasy_score1'] . "</td>";
				} else {
					echo "<td style='font-size: 26px; color: red; cursor: help' align='center' class='scr' title='Ungültige Aufstellungen werden als Niederlage gewertet'>" . $col['fantasy_score1'] . "</td>";
				}
				
				if ( $col['anz_spieler2'] == 11 ) {
					echo "<td style='font-size: 26px;' align='center' class='scr'>" . $col['fantasy_score2'] . "</td>";
				} else {
					echo "<td style='font-size: 26px; color: red; cursor: help' align='center' class='scr' title='Ungültige Aufstellungen werden als Niederlage gewertet'>" . $col['fantasy_score2'] . "</td>";
				}			

				echo "<td align='left'><small>#" . $col['pos_2'] . "</small> " . mb_convert_encoding($col['ftsy_away_name'], 'UTF-8') . "</td>";
				echo "</tr>";
			}
		echo "</table>";
	}	
mysqli_close($con);
?>