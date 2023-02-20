 <?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get current round name and season id from MySQL DB

$akt_round_name = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

// Fetch current bundesliga fixtures

$result = mysqli_query($con,"	SELECT 	fix.localteam_name as home
										, fix.visitorteam_name as away
										, fix.localteam_score as tor_home
										, fix.visitorteam_score as tor_away

								FROM 	xa7580_db1.sm_fixtures_basic_v fix
								WHERE 	fix.round_name = '".$akt_round_name."'
										and fix.season_id = '".$akt_season_id."'

								ORDER BY fix.fixture_id asc
	");

// Print out results

echo "<table class='scores_table'>";

while($row = mysqli_fetch_array($result)) {
	echo "<tr class='tr_home'><td>".mb_convert_encoding($row['home'], 'UTF-8')."</td><td>".$row['tor_home']."</td>";
	echo "<tr class='tr_away'><td>".mb_convert_encoding($row['away'], 'UTF-8')."</td><td>".$row['tor_away']."</td>";
	}

echo "</table>";
?> 