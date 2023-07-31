<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<!DOCTYPE html>
<html>

<head>

 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">   

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/matchup.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
 	<!-- Custom scripts -->
	<script type="text/javascript" src="../js/matchup-toggle-player-stats.js"></script>  

</head>

<body>
<header><h1>FANTASY BUNDESLIGA</h1></header>

<!-- Navigation -->

<div id = "hilfscontainer">
	<?php include("navigation.php"); ?>
</div>

<!-- Headline -->

<div id="headline" class="row">
	<?php 
		include ('../secrets/mysql_db_connection.php');
		
		// Get meta data	
		$match_id = $_GET["ID"];
		$user = $_SESSION['username'];
		$user_id = $_SESSION['user_id'];
		$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
		$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
		$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
		$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	
		$clicked_spieltag = mysqli_query($con, "SELECT buli_round_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> buli_round_name;
			
		echo "<h2>GAME CENTER - SPIELTAG " . $clicked_spieltag. "<h2>";
	?>
</div>

<!--Actual Game Center -->

<div class="row">
	<?php
		// User names
		$user =  mysqli_query($con,"
			SELECT username 
			FROM xa7580_db1.ftsy_schedule sch 
			INNER JOIN xa7580_db1.users usr
				ON sch.ftsy_home_id = usr.id
			WHERE sch.ftsy_match_id = '".$match_id."'
			") -> fetch_object() -> username;

		$gegner_manager = mysqli_query($con,"
			SELECT username 
			FROM xa7580_db1.ftsy_schedule sch 
			INNER JOIN xa7580_db1.users usr
				ON sch.ftsy_away_id = usr.id
			WHERE ftsy_match_id = '".$match_id."'
			") -> fetch_object() -> username;

		// Team names
		$mein_team = mysqli_query($con,"SELECT ftsy_home_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_home_name;
		$gegner_team = mysqli_query($con,"SELECT ftsy_away_name FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_away_name;
		$mein_team_id = mysqli_query($con,"SELECT ftsy_home_id FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_home_id;
		$gegner_team_id = mysqli_query($con,"SELECT ftsy_away_id FROM xa7580_db1.ftsy_schedule WHERE ftsy_match_id = '".$match_id."'") -> fetch_object() -> ftsy_away_id;

		$array_home = array("for_id"=>$mein_team_id, "for_name"=>$mein_team, "for_user"=>$user, "opp_id"=>$gegner_team_id, "opp_name"=>$gegner_team, "opp_user"=>$gegner_manager);
		$array_away = array("opp_id"=>$mein_team_id, "opp_name"=>$mein_team, "opp_user"=>$user, "for_id"=>$gegner_team_id, "for_name"=>$gegner_team, "for_user"=>$gegner_manager);
		$teams_to_loop = [$array_home, $array_away];

		// Loop over both teams
		foreach ($teams_to_loop as &$loop_value) {
		
			$team_id =  $loop_value['for_id'];
	  	$team_name = $loop_value['for_name'];
	  	$user_name =  $loop_value['for_user'];
	  	$team_id_opp =  $loop_value['opp_id'];
	  	$team_name_opp = $loop_value['opp_name'];
	  	$user_name_opp =  $loop_value['opp_user'];
	  			
	  	echo "<div class='spieler'>";

	  	/**************************/ 
	  	/* 1.) Get overall Scores */
	  	/**************************/ 

			if ($akt_spieltag == $clicked_spieltag) {

	  		// If round is active
				
				// Overall score user
				$result_score = mysqli_query($con,"
					SELECT SUM(ftsy_score) AS score
					FROM xa7580_db1.ftsy_player_ownership base 
					INNER JOIN xa7580_db1.ftsy_scoring_akt_v akt 
						ON akt.player_id = base.player_id
					WHERE 	".$ftsy_owner_column." = '".$team_id."'
									AND ".$ftsy_status_column." != 'NONE'
									AND ftsy_score IS NOT NULL
					GROUP BY ".$ftsy_owner_column." 
					") -> fetch_object() -> score;
				
				// Full roster user
				$my_score_valid = mysqli_query($con, " SELECT COUNT(player_id) AS anz_spieler FROM xa7580_db1.ftsy_player_ownership WHERE ".$ftsy_owner_column." = '".$team_id."' AND ".$ftsy_status_column." != 'NONE' ") -> fetch_object() -> anz_spieler;

				// Overall score opp
				$result_score_g = mysqli_query($con," 
					SELECT SUM(ftsy_score) AS score
					FROM xa7580_db1.ftsy_player_ownership base 
					INNER JOIN xa7580_db1.ftsy_scoring_akt_v akt 
						ON akt.player_id = base.player_id
					WHERE 	".$ftsy_owner_column." = '".$team_id_opp."'
									AND ".$ftsy_status_column." != 'NONE'
									AND ftsy_score IS NOT NULL
					GROUP BY ".$ftsy_owner_column." ") -> fetch_object() -> score;

				// Full roster opp
				$his_score_valid = mysqli_query($con, " SELECT COUNT(player_id) AS anz_spieler FROM xa7580_db1.ftsy_player_ownership WHERE ".$ftsy_owner_column." = '".$team_id_opp."' AND ".$ftsy_status_column." != 'NONE' ") -> fetch_object() -> anz_spieler;

				// Players played/left
				$players_played = mysqli_query($con, "	
					SELECT COUNT(base.id) as cnt 
					FROM xa7580_db1.sm_playerbase_basic_v base 
					INNER JOIN xa7580_db1.sm_fixtures buli
						ON 	( base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
								AND buli.round_name = '".$akt_spieltag."'
								AND buli.season_id = '".$akt_season_id."'
								AND buli.kickoff_ts <= NOW()
					WHERE ".$ftsy_owner_column." = '".$team_id."'
								AND ".$ftsy_status_column." != 'NONE' 
					") -> fetch_object() -> cnt;
				
				$players_left = 11-$players_played;

			} elseif ($clicked_spieltag > $akt_spieltag) {

				// If round is in the future

				$result_score = NULL;
				$result_score_g = NULL;

			} else {

				// Round is in the past
					
				$result_score = mysqli_query($con," 
					SELECT ROUND(CASE WHEN sch.ftsy_home_id = '".$team_id."' THEN sch.ftsy_home_score else sch.ftsy_away_score end,1) AS score
					FROM xa7580_db1.ftsy_schedule sch 
					WHERE 	( sch.ftsy_home_id = '".$team_id."' or sch.ftsy_away_id = '".$team_id."')
										AND sch.buli_round_name = '".$clicked_spieltag."'
										AND sch.season_id = '".$akt_season_id."'
					") -> fetch_object() -> score;

			}

			// If scores are NULL set them to 0
			
			if ($clicked_spieltag <= $akt_spieltag){
				if($result_score === NULL){
					$result_score = 0;} 
				if($result_score_g === NULL){
					$result_score_g = 0;} 
			
			}

	  	/*******************************/ 
	  	/* 2.) Get head-to-head record */
	  	/*******************************/ 			

			$bilanz = mysqli_query($con, "	
				SELECT 	COALESCE(SUM(
									CASE 	WHEN ftsy_home_id = '".$team_id."' THEN 
													CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END
												WHEN ftsy_away_id = '".$team_id."' THEN 
													CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END
												ELSE 0 END
												),0) AS S,
								COALESCE(SUM(
									CASE 	WHEN ftsy_home_id = '".$team_id."' THEN 
													CASE WHEN ftsy_home_score < ftsy_away_score THEN 1 ELSE 0 END
												WHEN ftsy_away_id = '".$team_id."' THEN 
													CASE WHEN ftsy_away_score < ftsy_home_score THEN 1 ELSE 0 END
												ELSE 0 END
												),0) AS N,
								COALESCE(SUM(
									CASE 	WHEN ftsy_home_id = '".$team_id."' THEN 
													CASE WHEN ftsy_home_score = ftsy_away_score THEN 1 ELSE 0 END
												WHEN ftsy_away_id = '".$team_id."' THEN 
													CASE WHEN ftsy_away_score = ftsy_home_score THEN 1 ELSE 0 END
												ELSE 0 END),0) AS U													 											
				FROM (
					SELECT 	* 
					FROM xa7580_db1.ftsy_schedule
					WHERE	ftsy_home_id = '".$team_id."' 
								AND ftsy_away_id = '".$team_id_opp."' 
								AND buli_round_name BETWEEN 1 AND ('".$akt_spieltag."'-1)
								AND match_type = 'league'

					UNION ALL 

					SELECT * 
					FROM xa7580_db1.ftsy_schedule
					WHERE ftsy_home_id = '".$team_id_opp."' 
								AND ftsy_away_id = '".$team_id."' 
								AND buli_round_name BETWEEN 1 AND ('".$akt_spieltag."'-1)
								AND match_type = 'league'
					) bilanz_table
					") -> fetch_assoc();

			echo "<div class=headscore>";
				echo "<h2>" . mb_convert_encoding(strtoupper($team_name), 'UTF-8') ."</h2>"; 
					if ($akt_spieltag === $clicked_spieltag) {
						if ($my_score_valid == 11){ echo "<p class='scoreboard'>" . $result_score ."</p>"; } else { echo "<p style='color:red; cursor: help' class='scoreboard' title='Eine ungültige Aufstellung wird als automatische Niederlage berechnet.'>" . $result_score . "</p>"; } 
					} else {
						echo "<p class='scoreboard'>" . $result_score . "</p>";
					}
			echo "</div>";

			if ($akt_spieltag === $clicked_spieltag) {
				echo "<h5>Manager: ". mb_convert_encoding($user_name, 'UTF-8') ." | Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . " | Übrige Spieler: " . $players_left . "</h2>";
			} else {
				echo "<h5>Manager: ". mb_convert_encoding($user_name, 'UTF-8') ." | Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . "</h2>";
			} 
				
			echo "<div class='fakeimg'></div>";

			/*************************************/
			/* 3.) Loop bench and lineup players */
			/*************************************/

			// Define variables for loop
			$playertype_to_loop = [$aufstellung = array('headline' => 'AUFSTELLUNG', 'sql_value' => '!=' ), array('headline' => 'BANK', 'sql_value' => '=' )];
	  	
			// Start loop
	  	foreach ($playertype_to_loop as &$playertype_loop_value) {

	  		$sql_value = $playertype_loop_value['sql_value'];
	  		$headline_value = $playertype_loop_value['headline'];

			  echo "<h3>".$headline_value."</h3>";
			  
			  // Get player data

				include ('../secrets/mysql_db_connection.php');

				if ($akt_spieltag == $clicked_spieltag) {
					
					// Active round

					$result = mysqli_query($con,"	
							SELECT 	base.id
											, base.position_short
											, base.logo_path 															 
											, base.display_name
											, buli.kickoff_ts 
											, buli.kickoff_dt
											, buli.fixture_status
											, case when base.team_id = buli.localteam_id then buli.visitorteam_name_code else buli.localteam_name_code end as gegner_code
											, case when base.team_id = buli.localteam_id then buli.localteam_score else buli.visitorteam_score end as score_for
											, case when base.team_id = buli.localteam_id then buli.visitorteam_score else buli.localteam_score end as score_against
											, case when base.team_id = buli.localteam_id then 'H' else 'A' end as homeaway
											, case 	when dayname(buli.kickoff_dt) = 'Monday' then 'Mo.'
											     		when dayname(buli.kickoff_dt) = 'Tuesday' then 'Di.'
											     		when dayname(buli.kickoff_dt) = 'Wednesday' then 'Mi.'
											     		when dayname(buli.kickoff_dt) = 'Thursday' then 'Do.'
											     		when dayname(buli.kickoff_dt) = 'Friday' then 'Fr.'
											     		when dayname(buli.kickoff_dt) = 'Saturday' then 'Sa.'
											    		when dayname(buli.kickoff_dt) = 'Sunday' then 'So.'
											     		end as kickoff_weekday
											, left(buli.kickoff_time,5) as kickoff_time_trunc
											, month(buli.kickoff_dt) as kickoff_month
											, day(buli.kickoff_dt) as kickoff_day
															
											, ftsy.ftsy_score

											, case 	when ftsy.minutes_played_stat is null and ftsy.appearance_stat = 1 then '1 Min.' 
											     		when ftsy.minutes_played_stat is not null and ftsy.appearance_stat = 1 then concat(ftsy.minutes_played_stat, ' Min.')
											     		else null
											     		end as appearance_stat
											, ftsy.goals_total_stat
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
											     		else null 
											     		end as penalties_stat     
											, ftsy.pen_scored_stat + ftsy.pen_missed_stat as penalties_total
											, ftsy.assists_stat
											, ftsy.shots_total_stat
											, ftsy.passes_complete_stat + ftsy.passes_incomplete_stat as passes_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
															else null 
															end as passes_stat
											, ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat as crosses_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
															else null 
															end as crosses_stat				
											, ftsy.key_passes_stat
											, ftsy.big_chances_created_stat
											, ftsy.duels_won_stat + ftsy.duels_lost_stat as duels_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
															else null 
													    end as duels_stat
											, ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat as dribble_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
															else null 
													    end as dribble_stat
											, ftsy.tackles_stat
											, ftsy.interceptions_stat
											, ftsy.blocks_stat
											, ftsy.clearances_stat
											, ftsy.clearances_offline_stat
											, ftsy.outside_box_saves_stat
											, ftsy.inside_box_saves_stat
											, ftsy.pen_saved_stat
											, ftsy.redcards_stat
											, ftsy.redyellowcards_stat
											, ftsy.pen_committed_stat
											, ftsy.owngoals_stat
											, ftsy.dispossessed_stat
											, ftsy.dribbled_past_stat
											, ftsy.pen_won_stat
											, ftsy.big_chances_missed_stat
											, ftsy.error_lead_to_goal_stat
											, ftsy.punches_stat
											, ftsy.goals_conceded_stat
											, ftsy.clean_sheet_stat
							        , proj.ftsy_score_projected 

							FROM xa7580_db1.sm_playerbase_basic_v base 

							LEFT JOIN xa7580_db1.ftsy_scoring_akt_v ftsy 
								ON ftsy.player_id = base.id

							LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj
								ON proj.player_id = base.id		

							INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
								ON 	( base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
										AND buli.round_name = '".$akt_spieltag."'
										AND buli.season_id = '".$akt_season_id."'

							WHERE ".$ftsy_status_column." ".$sql_value." 'NONE' 
										AND ".$ftsy_owner_column." = '".$team_id."'

							ORDER BY CASE WHEN base.position_short = 'ST' THEN 1 
					    							WHEN base.position_short = 'MF' THEN 2 
					    							WHEN base.position_short = 'AW' THEN 3 
					    							WHEN base.position_short = 'TW' THEN 4 
					    							END  
					    ");
				
				} elseif($clicked_spieltag > $akt_spieltag) {
					
					// Round in the future

					$result = mysqli_query($con,"	

						SELECT 	base.id
										, base.position_short
										, base.logo_path 															 
										, base.display_name
										, buli.kickoff_ts 
										, buli.kickoff_dt
										, buli.fixture_status
										, case when base.team_id = buli.localteam_id then buli.visitorteam_name_code else buli.localteam_name_code end as gegner_code
										, case when base.team_id = buli.localteam_id then buli.localteam_score else buli.visitorteam_score end as score_for
										, case when base.team_id = buli.localteam_id then buli.visitorteam_score else buli.localteam_score end as score_against
										, case when base.team_id = buli.localteam_id then 'H' else 'A' end as homeaway
										, case 	when dayname(buli.kickoff_dt) = 'Monday' then 'Mo.'
										     		when dayname(buli.kickoff_dt) = 'Tuesday' then 'Di.'
										     		when dayname(buli.kickoff_dt) = 'Wednesday' then 'Mi.'
										    		when dayname(buli.kickoff_dt) = 'Thursday' then 'Do.'
										     		when dayname(buli.kickoff_dt) = 'Friday' then 'Fr.'
										     		when dayname(buli.kickoff_dt) = 'Saturday' then 'Sa.'
										     		when dayname(buli.kickoff_dt) = 'Sunday' then 'So.'
										     		end as kickoff_weekday
										, left(buli.kickoff_time,5) as kickoff_time_trunc
										, month(buli.kickoff_dt) as kickoff_month
										, day(buli.kickoff_dt) as kickoff_day

						FROM xa7580_db1.sm_playerbase_basic_v base 

						INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
							ON 	( base.team_id = buli.localteam_id OR base.team_id = buli.visitorteam_id) 
									AND buli.round_name = '".$akt_spieltag."'
									AND buli.season_id = '".$akt_season_id."'

						WHERE ".$ftsy_status_column." ".$sql_value." 'NONE' 
									AND ".$ftsy_owner_column." = '".$team_id."'

						ORDER BY CASE WHEN base.position_short = 'ST' THEN 1 
					    						WHEN base.position_short = 'MF' THEN 2 
					    						WHEN base.position_short = 'AW' THEN 3 
					    						WHEN base.position_short = 'TW' THEN 4 
					    						END  
					  ");
					    
				} else {

					// Round in the past

					$result = mysqli_query($con,"	
						SELECT 	base.id
										, ftsy.position_short
										, ftsy.logo_path 															 
										, ftsy.display_name
										, buli.kickoff_ts 
										, buli.kickoff_dt
										, buli.fixture_status
										, case when ftsy.current_team_id = buli.localteam_id then buli.visitorteam_name_code else buli.localteam_name_code end as gegner_code
										, case when ftsy.current_team_id = buli.localteam_id then buli.localteam_score else buli.visitorteam_score end as score_for
										, case when ftsy.current_team_id = buli.localteam_id then buli.visitorteam_score else buli.localteam_score end as score_against
										, case when ftsy.current_team_id = buli.localteam_id then 'H' else 'A' end as homeaway
										, case 	when dayname(buli.kickoff_dt) = 'Monday' then 'Mo.'
													  when dayname(buli.kickoff_dt) = 'Tuesday' then 'Di.'
													  when dayname(buli.kickoff_dt) = 'Wednesday' then 'Mi.'
													  when dayname(buli.kickoff_dt) = 'Thursday' then 'Do.'
													  when dayname(buli.kickoff_dt) = 'Friday' then 'Fr.'
													  when dayname(buli.kickoff_dt) = 'Saturday' then 'Sa.'
													  when dayname(buli.kickoff_dt) = 'Sunday' then 'So.'
													  end as kickoff_weekday
										, left(buli.kickoff_time,5) as kickoff_time_trunc
										, month(buli.kickoff_dt) as kickoff_month
										, day(buli.kickoff_dt) as kickoff_day
										
										, ftsy.ftsy_score

										, case 	when ftsy.minutes_played_stat is null and ftsy.appearance_stat = 1 then '1 Min.' 
										     		when ftsy.minutes_played_stat is not null and ftsy.appearance_stat = 1 then concat(ftsy.minutes_played_stat, ' Min.')
										     		else null
										     		end as appearance_stat
										, ftsy.goals_total_stat
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
										     		else null 
										     		end as penalties_stat     
										, ftsy.pen_scored_stat + ftsy.pen_missed_stat as penalties_total
										, ftsy.assists_stat
										, ftsy.shots_total_stat
										, ftsy.passes_complete_stat + ftsy.passes_incomplete_stat as passes_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
														else null 
														end as passes_stat
										, ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat as crosses_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
														else null 
														end as crosses_stat				
										, ftsy.key_passes_stat
										, ftsy.big_chances_created_stat
										, ftsy.duels_won_stat + ftsy.duels_lost_stat as duels_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
														else null 
												    end as duels_stat
										, ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat as dribble_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
														else null 
												    end as dribble_stat
										, ftsy.tackles_stat
										, ftsy.interceptions_stat
										, ftsy.blocks_stat
										, ftsy.clearances_stat
										, ftsy.clearances_offline_stat
										, ftsy.outside_box_saves_stat
										, ftsy.inside_box_saves_stat
										, ftsy.pen_saved_stat
										, ftsy.redcards_stat
										, ftsy.redyellowcards_stat
										, ftsy.pen_committed_stat
										, ftsy.owngoals_stat
										, ftsy.dispossessed_stat
										, ftsy.dribbled_past_stat
										, ftsy.pen_won_stat
										, ftsy.big_chances_missed_stat
										, ftsy.error_lead_to_goal_stat
										, ftsy.punches_stat
										, ftsy.goals_conceded_stat
										, ftsy.clean_sheet_stat

						FROM xa7580_db1.sm_playerbase base 

						LEFT JOIN xa7580_db1.ftsy_scoring_hist ftsy 
							ON  ftsy.player_id = base.id
									and ftsy.round_name = '".$clicked_spieltag."'
									and ftsy.season_id = '".$akt_season_id."'

						INNER JOIN xa7580_db1.sm_fixtures_basic_v buli
							ON 	( ftsy.current_team_id = buli.localteam_id OR ftsy.current_team_id = buli.visitorteam_id) 
									AND buli.round_name = '".$clicked_spieltag."'
									AND buli.season_id = '".$akt_season_id."'

						WHERE ".$ftsy_status_column." ".$sql_value." 'NONE' 
									AND ".$ftsy_owner_column." = '".$team_id."'

						ORDER BY CASE WHEN base.position_short = 'ST' THEN 1 
					    						WHEN base.position_short = 'MF' THEN 2 
					    						WHEN base.position_short = 'AW' THEN 3 
					    						WHEN base.position_short = 'TW' THEN 4 
					    						END  
						");
				
				}

				// Print out player data

				if($clicked_spieltag > $akt_spieltag){
						
					// Round in future

					echo "<div class='kader'><table id='myTable'>";
						while($row = mysqli_fetch_array($result)) {
							echo "<tr class = 'summary1'>";
							echo "<td style='display:none;'>" . $row['id'] . "</td>";
							echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
							echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
							echo "<td>" . mb_convert_encoding($row['display_name'], 'UTF-8') . "</td>";
							$matchup_to_display = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). " vs. ".$row['gegner_code']. " (".$row['homeaway'] . ")";
							echo "<td style='color: gray;'>" .$matchup_to_display. "</td>";
							echo "<td align='center' class='player_score'></td>";
							echo "</tr>";
						}
					echo "</table></div>";
					
				} elseif(intval($clicked_spieltag) < intval($akt_spieltag)) {

					// Round in the past

					echo "<div class='kader'><table id='myTable'>";
					while($row = mysqli_fetch_array($result)) {
							
						// Summary

						echo "<tr class = 'summary1'>";
								echo "<td style='display:none;'>" . $row['id'] . "</td>";
								echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
								echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
								echo "<td>" . mb_convert_encoding($row['display_name'], 'UTF-8') . "</td>";		
								echo "<td style='color: gray;'>".$matchup_to_display."</td>";
								echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . " vs. " . $row['gegner_code'] . "<span style='color: black' class=''><small><b> FINAL<b/></small></span></td>";
								echo "<td align='center' class='player_score'><span class=''>" . $row['ftsy_score'] . "</span></td>";
						echo "</tr>";

						// Detailed stats

						echo "<tr class= 'player_detail'><td colspan='4'>";
							if ($row['appearance_stat'] != NULL) {
									echo( ($row['appearance_stat'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat'] . ' | ' : NULL);	
									echo( ($row['goals_made_stat'] != NULL AND $row['goals_made_stat'] != 0)? 'Tore: ' . $row['goals_made_stat'] . ' | ' : NULL);	
									echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat'] . ' | ' : NULL);
									echo( ($row['assists_made_stat'] != NULL AND $row['assists_made_stat'] != 0)? 'Vorlagen: ' . $row['assists_made_stat'] . ' | ' : NULL);
									echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' | ' : NULL);	
									echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_total_stat'] . ' | ' : NULL);
									echo( ($row['passes_key_stat'] != NULL AND $row['passes_key_stat'] != 0)? 'Key-Pässe: ' . $row['passes_key_stat'] . ' | ' : NULL);	
									echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat'] . ' | ' : NULL);	
									echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat'] . ' | ' : NULL);	
									echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat'] . ' | ' : NULL);	
									echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat'] . ' | ' : NULL);	
									echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' | ' : NULL);	
									echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' | ' : NULL);
									echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' | ' : NULL);	
									echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' | ' : NULL);	
									echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' | ' : NULL);	
									echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' | ' : NULL);	
									echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' | ' : NULL);	
								
									echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' | ' : NULL);	
									echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' | ' : NULL);	
									echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' | ' : NULL);	
									echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' | ' : NULL);	
										
									echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' | ' : NULL);	
									echo( ($row['yellowredcards_stat'] != NULL AND $row['yellowredcards_stat'] != 0)? 'Gelb-Rot: ' . $row['yellowredcards_stat'] . ' | ' : NULL);	
									
							} else {
										echo 'Kein Einsatz.';
								
							}
							
						echo "<td></tr>";
					}
					echo "</table></div>";
					
				} else {

					// Round is current round
					echo "<div class='kader'><table id='myTable'>";
					
					while($row = mysqli_fetch_array($result)) {

						// Summary

						echo "<tr class = 'summary1'>";
							echo "<td style='display:none;'>" . $row['id'] . "</td>";
							echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
							echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";
							echo "<td>" . mb_convert_encoding($row['display_name'], 'UTF-8') . "</td>";		

							// Differentiate kickoff datetimes

							if ( $row['kickoff_ts'] > date('Y-m-d H:i:s') ) {

								// Kickoff in future

								$matchup_to_display = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). " vs. ".$row['gegner_code']. " (".$row['homeaway'] . ")";

								echo "<td style='color: gray;'>".$matchup_to_display."</td>";
								echo "<td align='center' title='Projection' class='player_score'><span class='pre'>" . $row['ftsy_score_projected'] . "</span></td>";

							} elseif ( $row['kickoff_ts'] <= date('Y-m-d H:i:s') && $row['fixture_status'] != 'FT' ) {

								// Fixture in past and still running

								echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . " vs. " . $row['gegner_code'] . "<span style='color: red' class='pulsate'> <small><b> LIVE</b></small></span></td>";
								echo "<td align='center' class='player_score'><span class='pulsate'>" . $row['ftsy_score'] . "</span></td>";					
							
							} elseif ($row['kickoff_ts'] <= date('Y-m-d H:i:s') && $row['fixture_status'] == 'FT') {
								
								// Fixture in past and finished

								echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . " vs. " . $row['gegner_code'] . "<span style='color: black' class=''><small><b> FINAL<b/></small></span></td>";
									echo "<td align='center' class='player_score'><span class=''>" . $row['ftsy_score'] . "</span></td>";
							
							}					
						
						echo "</tr>";

						// Detailed player stats

						echo "<tr class= 'player_detail'><td colspan='4'>";
							if ($row['appearance_stat'] != NULL) {
								echo( ($row['appearance_stat'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat'] . ' | ' : NULL);	
								echo( ($row['goals_made_stat'] != NULL AND $row['goals_made_stat'] != 0)? 'Tore: ' . $row['goals_made_stat'] . ' | ' : NULL);	
								echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat'] . ' | ' : NULL);
								echo( ($row['assists_made_stat'] != NULL AND $row['assists_made_stat'] != 0)? 'Vorlagen: ' . $row['assists_made_stat'] . ' | ' : NULL);
								echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' | ' : NULL);	
								echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_total_stat'] . ' | ' : NULL);
								echo( ($row['passes_key_stat'] != NULL AND $row['passes_key_stat'] != 0)? 'Key-Pässe: ' . $row['passes_key_stat'] . ' | ' : NULL);	
								echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat'] . ' | ' : NULL);	
								echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat'] . ' | ' : NULL);	
								echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat'] . ' | ' : NULL);	
								echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat'] . ' | ' : NULL);	
								echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' | ' : NULL);	
								echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' | ' : NULL);	
								echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' | ' : NULL);	
								echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' | ' : NULL);	
								echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' | ' : NULL);	
								echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' | ' : NULL);	
								echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' | ' : NULL);	
								echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' | ' : NULL);	
								echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' | ' : NULL);	
								echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' | ' : NULL);	
								echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' | ' : NULL);	
								echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' | ' : NULL);	
								echo( ($row['yellowredcards_stat'] != NULL AND $row['yellowredcards_stat'] != 0)? 'Gelb-Rot: ' . $row['yellowredcards_stat'] . ' | ' : NULL);	
								echo( 'Projection: ' . $row['ftsy_score_projected'] . ' Punkte');	
									
							} else {
								
								echo 'Kein Einsatz.';
							
							}
						
						echo "<td></tr>";
						}
					echo "</table></div>";
				}
				echo "<div class='fakeimg'></div>";
			}
			echo "</div>";
	  } ?>
  </div>
</div>
</body>
</html>