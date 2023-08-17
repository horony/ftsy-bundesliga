<?php

// Scheduled job that changes a player status from free agent (FA) to waiver (WVR) during a active round, so that users can add these players during the active round but only during the next waiver phase

include("../../secrets/mysql_db_connection.php");

// Get meta data
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag; 
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;  
$current_date = date('Y-m-d H:i:s');

// Change players with kickoff in the past from FA to WVR for ftsy league 1
mysqli_query($con, "
	UPDATE xa7580_db1.ftsy_player_ownership owr
	INNER JOIN xa7580_db1.sm_playerbase base
		ON base.id = owr.player_id
	LEFT JOIN xa7580_db1.sm_fixtures fix
		ON 	(base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id)
				AND	fix.round_name = '".$akt_spieltag."'
				AND	fix.season_id = '".$akt_season_id."'
	SET owr.1_ftsy_owner_type = 'WVR'
	WHERE 	owr.1_ftsy_owner_type = 'FA' 
					AND fix.kickoff_ts <= '".$current_date."'
");
?>