<?php 
include("../auth.php");

// Get player IDs from AJAX
$Aufzunehmender_Spieler_ID = $_GET['clicked_player1']; 
$Abzugebender_Spieler_ID = $_GET['clicked_player2'];

// User ID from Session
$user = $_SESSION['username'];
$user_id = intval($_SESSION['user_id']);

// Check if user clicked 2 players
if ($Aufzunehmender_Spieler_ID == 0 OR $Abzugebender_Spieler_ID == 0){
	echo "FEHLER: Du musst einen Spieler aufnehmen und einen Spieler abgeben!";
     
} else {

	// Get Meta data from MySQL DB 
  include("../../secrets/mysql_db_connection.php");
	
	$aktueller_spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter") -> fetch_object() -> spieltag;
  $ftsy_owner_type_column = strval($_SESSION['league_id']) . '_ftsy_owner_type';
  $ftsy_owner_id_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
  $ftsy_match_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
  $akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

	$Aufzunehmender_Spieler_Besitzer = mysqli_query($con, "	
		SELECT ".$ftsy_owner_type_column." as Besitzer 
		FROM xa7580_db1.ftsy_player_ownership 
		WHERE player_id = '$Aufzunehmender_Spieler_ID'
		") -> fetch_object() -> Besitzer;

	$Aufzunehmender_Spieler_Besitzer_ID = mysqli_query($con, "
		SELECT ".$ftsy_owner_id_column." as besitzer_id 
		FROM xa7580_db1.ftsy_player_ownership 
		WHERE player_id = '$Aufzunehmender_Spieler_ID'
		") -> fetch_object() -> besitzer_id;

	$Abzugebender_Spieler_Besitzer = mysqli_query($con, "
		SELECT ".$ftsy_owner_type_column." as Besitzer 
		FROM xa7580_db1.ftsy_player_ownership 
		WHERE player_id = '$Abzugebender_Spieler_ID'") -> fetch_object() -> Besitzer;

	$Abzugebender_Spieler_Besitzer_ID = mysqli_query($con, "
		SELECT ".$ftsy_owner_id_column." as besitzer_id 
		FROM xa7580_db1.ftsy_player_ownership 
		WHERE player_id = '$Abzugebender_Spieler_ID'
		") -> fetch_object() -> besitzer_id;

	$Abzugebender_Spieler_Time = mysqli_query($con, "	
		SELECT fix.kickoff_ts as zeit 
		FROM xa7580_db1.sm_playerbase base 
		INNER JOIN xa7580_db1.sm_fixtures_basic_v fix
			ON 	( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id ) 
					AND fix.round_name = '$aktueller_spieltag' 
					AND fix.season_id = '$akt_season_id'
		WHERE base.ID = '$Abzugebender_Spieler_ID'
		") -> fetch_object() -> zeit;

	$Abzugebender_Spieler_Status = mysqli_query($con, "	
		SELECT ".$ftsy_match_status_column." as state 
		FROM xa7580_db1.ftsy_player_ownership 
		WHERE player_id = '$Abzugebender_Spieler_ID'
		") -> fetch_object() -> state;

	// Check if player still belong to its users
	if (($Abzugebender_Spieler_Besitzer_ID == $user_id) and ($Aufzunehmender_Spieler_Besitzer_ID != $user_id or $Aufzunehmender_Spieler_Besitzer_ID == NULL)) {

		/****************/
		/*  FREE AGENT  */
		/****************/
		
		// Check if added player is free agent -> direct add
		if ($Aufzunehmender_Spieler_Besitzer == 'FA'){
			
			// Check if dropped player has already played
			if ($Abzugebender_Spieler_Time <= date('Y-m-d H:i:s')) {

				echo "Der Spieler den du abgeben willst ist für diesen Spieltag festgespielt!";

			} else {

				// Add player
				mysqli_query($con ,"
					UPDATE 	xa7580_db1.ftsy_player_ownership 
					SET 		".$ftsy_owner_type_column." = 'USR'
									, ".$ftsy_owner_id_column." = '".$user_id."'
									, ".$ftsy_match_status_column." = 'NONE' 
					WHERE 	player_id = '$Aufzunehmender_Spieler_ID' 
									AND ".$ftsy_owner_type_column." = 'FA'
					");
				
				// Drop player
				mysqli_query($con ,"
					UPDATE 	xa7580_db1.ftsy_player_ownership 
					SET 		".$ftsy_owner_type_column." = 'WVR'
									, ".$ftsy_owner_id_column." = NULL
									, ".$ftsy_match_status_column." = NULL 
					WHERE 	player_id = '$Abzugebender_Spieler_ID' 
									AND ".$ftsy_owner_type_column." = 'USR'
					");

				// Delete effected waivers and trades
				mysqli_query($con ,"
					DELETE 
					FROM 	xa7580_db1.waiver 
					WHERE 	waiver_drop_id = '$Abzugebender_Spieler_ID' 
									AND owner = '".$user_id."'
					");

				mysqli_query($con ,"
					DELETE 
					FROM 	xa7580_db1.trade 
					WHERE 	( rec_trade_id = '$Abzugebender_Spieler_ID' or ini_trade_id = '$Aufzunehmender_Spieler_ID' )
							AND ( initiator = '".$user_id."' or recipient = '".$user_id."' )
					");
					

				// Write news
				$teamname = $_SESSION['user_teamname'];
				$Aufzunehmender_Spieler_Name = mysqli_query($con ,"SELECT display_name FROM xa7580_db1.sm_playerbase_basic_v WHERE id = '$Aufzunehmender_Spieler_ID'") -> fetch_object() -> display_name;
				$Abzugebender_Spieler_Name = mysqli_query($con ,"SELECT display_name FROM xa7580_db1.sm_playerbase_basic_v WHERE id = '$Abzugebender_Spieler_ID'") -> fetch_object() -> display_name;
				$Aufzunehmender_Spieler_Verein = mysqli_query($con ,"SELECT name FROM xa7580_db1.sm_playerbase_basic_v WHERE id = '$Aufzunehmender_Spieler_ID'") -> fetch_object() -> name;			
				$Abzugebender_Spieler_Verein = mysqli_query($con ,"SELECT name FROM xa7580_db1.sm_playerbase_basic_v WHERE id = '$Abzugebender_Spieler_ID'") -> fetch_object() -> name;					

$headline = <<<EOT
$teamname verplichtet Free Agent
EOT;
						
$story=<<<EOT
$teamname hat <b>$Aufzunehmender_Spieler_Name</b> ($Aufzunehmender_Spieler_Verein) unter Vertrag genommen und <b>$Abzugebender_Spieler_Name</b> ($Abzugebender_Spieler_Verein) aus seinen Diensten entlassen.
EOT;

				mysqli_query($con, "
					INSERT INTO xa7580_db1.news(name, headline, story, timestamp, add_id, drop_id, add_besitzer, drop_besitzer, type) 
					VALUES('System', '".$headline."', '".$story."', NOW(), '".$Aufzunehmender_Spieler_ID."', '".$Abzugebender_Spieler_ID."', 'Free Agent', '".$Abzugebender_Spieler_Besitzer_ID."', 'free_agent')
						");
				
				echo "Free Agent erfolgreich aufgenommen!";
			}
		
		/****************/
		/*    TRADE     */
		/****************/

		} elseif ($Aufzunehmender_Spieler_Besitzer == 'USR') {
		
		
			$Aufzunehmender_Spieler_Name = mysqli_query($con ,"
				SELECT 	display_name 
				FROM 	xa7580_db1.sm_playerbase_basic_v 
				WHERE 	id = '$Aufzunehmender_Spieler_ID'
				") -> fetch_object() -> display_name;

			$Aufzunehmender_Spieler_Besitzer_ID = mysqli_query($con ,"
				SELECT 	".$ftsy_owner_id_column." as Besitzer 
				FROM 	xa7580_db1.ftsy_player_ownership 
				WHERE 	player_id = '$Aufzunehmender_Spieler_ID' 
				") -> fetch_object() -> Besitzer;
			
			$Abzugebender_Spieler_Name = mysqli_query($con ,"	
				SELECT 	display_name 
				FROM 	xa7580_db1.sm_playerbase_basic_v 
				WHERE 	id = '$Abzugebender_Spieler_ID' ") -> fetch_object() -> display_name;

			$trade_id = strval($_SESSION['league_id']) . '_' . strval($user_id) . '_' .  strval($Aufzunehmender_Spieler_ID) . strval($Abzugebender_Spieler_ID);
			
			// Create trade request towards second user
			mysqli_query($con, "INSERT INTO xa7580_db1.trade (ID, initiator, recipient, ini_trade_id, rec_trade_id, ini_player_name, rec_player_name)
								VALUES ('".$trade_id."', '".$user_id."', '".$Aufzunehmender_Spieler_Besitzer_ID."', '".$Aufzunehmender_Spieler_ID."', '".$Abzugebender_Spieler_ID."', '".$Aufzunehmender_Spieler_Name."','".$Abzugebender_Spieler_Name."')
								");

			echo "Trade-Anfrage erfolgreich aufgenommen!";
		

		/****************/
		/*    WAIVER    */
		/****************/

		} elseif ($Aufzunehmender_Spieler_Besitzer == 'WVR') {
			
			$Aufzunehmender_Spieler_Name = mysqli_query($con ,"
				SELECT 	display_name 
				FROM 	xa7580_db1.sm_playerbase_basic_v 
				WHERE 	id = '$Aufzunehmender_Spieler_ID' 
								AND ".$ftsy_owner_type_column." = 'WVR'
				") -> fetch_object() -> display_name;

			$Abzugebender_Spieler_Name = mysqli_query($con ,"
				SELECT 	display_name
				FROM 	xa7580_db1.sm_playerbase_basic_v 
				WHERE 	id = '$Abzugebender_Spieler_ID' 
								AND ".$ftsy_owner_type_column." = 'USR'
				") -> fetch_object() -> display_name;

			$max_prio = mysqli_query($con ,"
				SELECT 	CASE WHEN MAX(prio) IS NULL THEN 1 ELSE MAX(prio) + 1 END AS prio 
				FROM 	xa7580_db1.waiver 
				WHERE 	owner = '".$user_id."'
				") -> fetch_object() -> prio;
			
			$waiver_id = strval($_SESSION['league_id']) . '_' . strval($user_id) . '_' . strval($Aufzunehmender_Spieler_ID) . strval($Abzugebender_Spieler_ID);
			
			// Create waiver request
			mysqli_query($con, "INSERT INTO xa7580_db1.waiver (prio, ID, owner, waiver_add_id, waiver_drop_id, waiver_add_name, waiver_drop_name)
								VALUES ('".$max_prio."', '".$waiver_id."', '".$user_id."', '".$Aufzunehmender_Spieler_ID."', '".$Abzugebender_Spieler_ID."', '".$Aufzunehmender_Spieler_Name."', '".$Abzugebender_Spieler_Name."')
								");

			echo $waiver_id;
			echo 'Prio ' . strval($max_prio);
			echo " Waiver-Anfrage erfolgreich aufgenommen!";
		
		}
	
	} else {
		echo "Der ausgewählte Spieler scheint nicht mehr verfügbar zu sein!";
	}		
}
?>