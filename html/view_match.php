<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<!DOCTYPE html>
<html>

<head>

 	<title></title> 

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

<?php include('../dev/header.php'); ?>

<!-- Navigation -->

<div id = "hilfscontainer">
	<?php include("../html/navigation.php"); ?>
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
		
$cup_query = mysqli_query($con, "SELECT cup_round, cup_leg, season_id, ftsy_home_id, ftsy_away_id, ftsy_home_score, ftsy_away_score 
                                 FROM xa7580_db1.ftsy_schedule 
                                 WHERE ftsy_match_id = '".$match_id."'");

$cup_match = mysqli_fetch_assoc($cup_query);

$cup_data = null; // Initialize as null to prevent errors

if (!empty($cup_match['cup_round']) && $cup_match['cup_leg'] == 2) {
    $cup_round = $cup_match['cup_round'];
    $season_id = $cup_match['season_id'];
    $home_id = $cup_match['ftsy_home_id'];
    $away_id = $cup_match['ftsy_away_id'];
    $home_score_leg2 = $cup_match['ftsy_home_score']; // Updated column name
    $away_score_leg2 = $cup_match['ftsy_away_score']; // Updated column name

    // Get the first leg match
    $leg1_query = mysqli_query($con, "SELECT ftsy_home_score, ftsy_away_score, ftsy_home_id, ftsy_away_id FROM xa7580_db1.ftsy_schedule 
                                      WHERE season_id = '".$season_id."' 
                                      AND cup_round = '".$cup_round."'
                                      AND cup_leg = 1 
                                      AND ((ftsy_home_id = '".$away_id."' AND ftsy_away_id = '".$home_id."') 
                                          OR (ftsy_home_id = '".$home_id."' AND ftsy_away_id = '".$away_id."'))");

    $leg1_match = mysqli_fetch_assoc($leg1_query);

    if ($leg1_match) {
        $home_score_leg1 = $leg1_match['ftsy_home_score']; // Updated column name
        $away_score_leg1 = $leg1_match['ftsy_away_score']; // Updated column name

        // Aggregate the scores by matching the teams regardless of home/away position
        if ($home_id == $leg1_match['ftsy_home_id']) {
            // The home team for the second leg was the home team for the first leg
            $total_home_score = $home_score_leg1 + $home_score_leg2;
            $total_away_score = $away_score_leg1 + $away_score_leg2;
        } else {
            // The home team for the second leg was the away team for the first leg
            $total_home_score = $away_score_leg1 + $home_score_leg2;
            $total_away_score = $home_score_leg1 + $away_score_leg2;
        }

        // Store in an array for later display
        $cup_data = [
            'home_id' => $home_id,
            'away_id' => $away_id,
            'total_home_score' => $total_home_score,
            'total_away_score' => $total_away_score,
        ];
    }
}


		
echo "<h2>GAME CENTER - SPIELTAG " . $clicked_spieltag;

	// Check if it's a cup round and display the transformed cup round and cup leg
	if (!empty($cup_match['cup_round'])) {
		// Transform the cup round
		$cup_round_transformed = '';
		switch ($cup_match['cup_round']) {
			case 'playoff':
				$cup_round_transformed = 'Qualifikation';
				break;
			case 'quarter':
				$cup_round_transformed = 'Viertelfinale';
				break;
			case 'semi':
				$cup_round_transformed = 'Halbfinale';
				break;
			case 'final':
				$cup_round_transformed = 'Finale';
				break;
			default:
				$cup_round_transformed = $cup_match['cup_round']; // Fallback to the original if not mapped
		}

		echo " - " . $cup_round_transformed;

		// If it's not a "final", display the cup leg
		if ($cup_match['cup_round'] != 'final' && !empty($cup_match['cup_leg'])) {
			// Transform the cup leg
			$cup_leg_transformed = '';
			switch ($cup_match['cup_leg']) {
				case 1:
					$cup_leg_transformed = 'Hinspiel';
					break;
				case 2:
					$cup_leg_transformed = 'Rückspiel';
					break;
				default:
					$cup_leg_transformed = $cup_match['cup_leg']; // Fallback to the original if not mapped
			}

			echo " " . $cup_leg_transformed;  // Removed the "-" and added space instead
		}
	}

echo "</h2>";


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
    $result_score = mysqli_query($con, "
        SELECT SUM(ftsy_score) AS score
        FROM xa7580_db1.ftsy_player_ownership base 
        INNER JOIN xa7580_db1.ftsy_scoring_akt_v akt 
            ON akt.player_id = base.player_id
        WHERE ".$ftsy_owner_column." = '".$team_id."'
            AND ".$ftsy_status_column." != 'NONE'
            AND ftsy_score IS NOT NULL
        GROUP BY ".$ftsy_owner_column."
    ")->fetch_object()->score;

    // Overall projection user	
    $result_score_proj = mysqli_query($con, " 
        SELECT SUM(ftsy_score_projected) AS score_proj
        FROM xa7580_db1.ftsy_player_ownership base 
        INNER JOIN xa7580_db1.ftsy_scoring_projection_v proj		
            ON proj.player_id = base.player_id
        WHERE ".$ftsy_owner_column." = '".$team_id."'
            AND ".$ftsy_status_column." != 'NONE'
        GROUP BY ".$ftsy_owner_column."
    ")->fetch_object()->score_proj;								
    
	$result_total_score = mysqli_query($con, "
		SELECT ROUND(SUM(player_score), 1) AS total_score
		FROM (
			SELECT 
				base.id AS player_id,
				CASE 
					WHEN fix.match_status = 'FT' THEN COALESCE(akt.ftsy_score, 0) 
					WHEN fix.match_status = 'NS' THEN COALESCE(proj.ftsy_score_projected, 0) 
					WHEN fix.match_status IN ('1st', '2nd', 'HT') 
						THEN COALESCE(akt.ftsy_score, 0) + 
							((90 - COALESCE(akt.minutes_played_stat, 0)) / 90) * GREATEST(COALESCE(proj.ftsy_score_projected, 0) - 4, 0)
					ELSE 0 
				END AS player_score
			FROM xa7580_db1.sm_playerbase base
			LEFT JOIN xa7580_db1.ftsy_player_ownership owner
				ON base.id = owner.player_id
			LEFT JOIN xa7580_db1.ftsy_scoring_akt_v akt  
				ON base.id = akt.player_id
			LEFT JOIN xa7580_db1.ftsy_scoring_projection_v proj  
				ON base.id = proj.player_id
			LEFT JOIN xa7580_db1.sm_fixture_per_team_akt_v fix  
				ON (base.current_team_id = fix.team_id OR base.current_team_id = fix.opp_id)
			WHERE owner." . $ftsy_owner_column . " = '" . $team_id . "'
				AND owner." . $ftsy_status_column . " != 'NONE'
				AND fix.match_status IN ('FT', 'NS', '1st', '2nd', 'HT')
			GROUP BY base.id
		) AS player_scores;
	")->fetch_object()->total_score;





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
					
					

echo "<div class='superheadscore'>";
    echo "<div class='headscore'>";
        echo "<div class='left-section'>";

            echo "<div class='team-image' style='display: inline-block;'>";
                $team_images = array(
                    3 => '../img/ftsy-team-logos/3.png',
                    4 => '../img/ftsy-team-logos/4.png', 
                    11 => '../img/ftsy-team-logos/11.png',
                    12 => '../img/ftsy-team-logos/12.png',
                    16 => '../img/ftsy-team-logos/16.png',
                    17 => '../img/ftsy-team-logos/17.png',
                    19 => '../img/ftsy-team-logos/19.png',
                    22 => '../img/ftsy-team-logos/22.png',
                    27 => '../img/ftsy-team-logos/27.png',
                    28 => '../img/ftsy-team-logos/28.png',
                    30 => '../img/ftsy-team-logos/30.png',
                );

                $image_src = isset($team_images[$team_id]) ? $team_images[$team_id] : '';

                if (!empty($image_src)) {
                    echo '<div class="round-image-div">';
                        echo '<img src="' . $image_src . '">';
                    echo '</div>';
                }
            echo "</div>";

        echo "</div>";

        // Live projection chart
        echo "<div class='chart-wrap vertical'></div>";
        echo "<div class='grid'>";
            echo "<div class='chart-section'>";
                $new_width_total_score = $result_total_score * 1.3;
                $new_width_score = $result_score * 1.3;

                echo "<div class='bar_live_prognose' style='width: " . $new_width_total_score . "px;' data-name='Medium' title='Medium " . $result_total_score . "'>";
                    echo "<span class='label' style='white-space: nowrap;'>&#8605; " . $result_total_score . "</span>";
                echo "</div>";

                echo "<div class='bar-container' style='position: relative;'>";
                    echo "<div class='bar_live_punkte' style='width: " . $new_width_score . "px;' data-name='Medium' title='Medium " . $result_score . "'>";
                        echo "<span class='label' style='white-space: nowrap;'>" . $result_score . "</span>";
                    echo "</div>";

                    echo "<div class='ring-container'>";
                        echo "<div class='circle'></div>";
                        echo "<div class='ringring'></div>";
                    echo "</div>"; 

                echo "</div>";
            echo "</div>"; 

            echo "<div class='marker marker-50'></div>";
            echo "<div class='marker marker-100'></div>";
            echo "<div class='marker marker-150'></div>";
            echo "<div class='marker marker-200'></div>";

            echo "<div class='marker-text marker-text-50'>50</div>";
            echo "<div class='marker-text marker-text-100'>100</div>";
            echo "<div class='marker-text marker-text-150'>150</div>";
            echo "<div class='marker-text marker-text-200'>200</div>";

        echo "</div>"; 
    echo "</div>"; 

if ($cup_data) {
    if ($team_id == $cup_data['home_id']) {
        $hinspiel_score = $cup_data['total_home_score']; 
    } elseif ($team_id == $cup_data['away_id']) {
        $hinspiel_score = $cup_data['total_away_score']; 
    } else {
        $hinspiel_score = null; 
    }

    if ($hinspiel_score !== null) {
        $aggregated_score = $hinspiel_score + $result_score;

        echo "<div class='cup-score custom-margin'>";
            echo "<strong>🏆 Hin- & Rückspiel: " . $aggregated_score . " (" . $hinspiel_score . ")</strong>";
        echo "</div>";
    }
}
    echo "<div class='team-name-section'>";
        $achievement_icons = mysqli_query($con, "SELECT achievement_icons FROM xa7580_db1.users_gamedata WHERE username = '".$user_name."'") -> fetch_object() -> achievement_icons;
        echo "<h2>" . mb_convert_encoding(strtoupper($team_name), 'UTF-8') . " " . $achievement_icons . "</h2>";
    echo "</div>"; 

echo "</div>"; 
if ($akt_spieltag === $clicked_spieltag) {
    echo "<h5>Manager: " . mb_convert_encoding($user_name, 'UTF-8') . " | Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . " | Übrige Spieler: " . $players_left . "</h5>";
} else {
    echo "<h5>Manager: " . mb_convert_encoding($user_name, 'UTF-8') . " | Direkter Vergleich: " . $bilanz['S'] . "-" . $bilanz['U'] . "-" . $bilanz['N'] . "</h5>";
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
											     		end as appearance_stat_x
											
											, ftsy.goals_minus_pen_stat

											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
											     		else null 
											     		end as penalties_stat_x   
											, ftsy.pen_scored_stat + ftsy.pen_missed_stat as penalties_total
											, ftsy.assists_stat
											, ftsy.shots_total_stat
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.shots_on_goal_stat,' ('),ftsy.shots_total_stat),')') 
														else null 
														end as shots_stat_x
											, ftsy.hit_woodwork_stat
											, ftsy.passes_complete_stat + ftsy.passes_incomplete_stat as passes_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
															else null 
															end as passes_stat_x
											, ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat as crosses_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
															else null 
															end as crosses_stat_x		
											, ftsy.key_passes_stat
											, ftsy.big_chances_created_stat
											, ftsy.duels_won_stat + ftsy.duels_lost_stat as duels_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
															else null 
													    end as duels_stat_x
											, ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat as dribble_total
											, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
															else null 
													    end as dribble_stat_x
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
											, case 	when ftsy.appearance_stat = 1 and ftsy.big_chances_missed_ftsy < 0 then ftsy.big_chances_missed_stat 
															else null 
															end as big_chances_missed_stat_x
											, ftsy.error_lead_to_goal_stat
											, ftsy.punches_stat
											, ftsy.goals_conceded_stat
											, case 	when ftsy.appearance_stat = 1 and (ftsy.goals_conceded_ftsy < 0 or ftsy.goalkeeper_goals_conceded_ftsy < 0) then ftsy.goals_conceded_stat 
															else null 
															end as goals_conceded_stat_x
											, ftsy.clean_sheet_stat
											, case 	when ftsy.appearance_stat = 1 and ftsy.clean_sheet_ftsy > 0 then ftsy.clean_sheet_stat 
															else null 
															end as clean_sheet_stat_x
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
										     		end as appearance_stat_x
										, ftsy.goals_minus_pen_stat
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.pen_scored_stat, ' ('), ftsy.pen_scored_stat + ftsy.pen_missed_stat), ')')
										     		else null 
										     		end as penalties_stat_x  
										, ftsy.pen_scored_stat + ftsy.pen_missed_stat as penalties_total
										, ftsy.assists_stat

										, ftsy.shots_total_stat
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.shots_on_goal_stat,' ('),ftsy.shots_total_stat),')') 
														else null 
														end as shots_stat_x
										, ftsy.hit_woodwork_stat
										, ftsy.passes_complete_stat + ftsy.passes_incomplete_stat as passes_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') 
														else null 
														end as passes_stat_x
										, ftsy.crosses_complete_stat + ftsy.crosses_incomplete_stat as crosses_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') 
														else null 
														end as crosses_stat_x		
										, ftsy.key_passes_stat
										, ftsy.big_chances_created_stat
										, ftsy.duels_won_stat + ftsy.duels_lost_stat as duels_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') 
														else null 
												    end as duels_stat_x
										, ftsy.dribbles_success_stat + ftsy.dribbles_failed_stat as dribble_total
										, case 	when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.dribbles_success_stat,' ('),ftsy.dribbles_success_stat+ftsy.dribbles_failed_stat),')') 
														else null 
												    end as dribble_stat_x
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
										, case 	when ftsy.appearance_stat = 1 and ftsy.big_chances_missed_ftsy < 0 then ftsy.big_chances_missed_stat 
														else null 
														end as big_chances_missed_stat_x
										, ftsy.error_lead_to_goal_stat
										, ftsy.punches_stat
										, ftsy.goals_conceded_stat
										, case 	when ftsy.appearance_stat = 1 and (ftsy.goals_conceded_ftsy < 0 or ftsy.goalkeeper_goals_conceded_ftsy < 0) then ftsy.goals_conceded_stat 
														else null 
														end as goals_conceded_stat_x
										, ftsy.clean_sheet_stat
										, case 	when ftsy.appearance_stat = 1 and ftsy.clean_sheet_ftsy > 0 then ftsy.clean_sheet_stat 
														else null 
														end as clean_sheet_stat_x

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
							$full_name = $row['display_name'];        // Get the full name
								$name_parts = explode(" ", $full_name);   // Split the name into parts (first and last names)
									$shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts); // Shorten the first name to its initial and keep the last name
										echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>"; // Display the shortened name

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
							$full_name = $row['display_name'];        // Get the full name
							$name_parts = explode(" ", $full_name);   // Split the name into parts (first and last names)
							$shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts); // Shorten the first name to its initial and keep the last name
							echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>"; // Display the shortened name
							
							// Here, add the 'matchup-to-display' class
							echo "<td class='matchup-to-display'>" . $matchup_to_display . "</td>";

							echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . " vs. " . $row['gegner_code'] . "<span style='color: black' class=''><small><b> FINAL<b/></small></span></td>";
							echo "<td align='center' class='player_score'><span class=''>" . $row['ftsy_score'] . "</span></td>";
						echo "</tr>";

						// Detailed stats

						echo "<tr class= 'player_detail'><td colspan='4'>";
							if ($row['appearance_stat_x'] != NULL) {

									/* Appearance */
									echo( ($row['appearance_stat_x'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat_x'] . ' | ' : NULL);	
									
									/* Scoring */
									echo( ($row['goals_minus_pen_stat'] != NULL AND $row['goals_minus_pen_stat'] != 0)? 'Tore: ' . $row['goals_minus_pen_stat'] . ' | ' : NULL);	
									echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat_x'] . ' | ' : NULL);
									echo( ($row['assists_stat'] != NULL AND $row['assists_stat'] != 0)? 'Vorlagen: ' . $row['assists_stat'] . ' | ' : NULL);
									echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' | ' : NULL);	

									/* Gegentore */
									echo( ($row['goals_conceded_stat_x'] != NULL AND $row['goals_conceded_stat_x'] != 0)? 'Gegentore: ' . $row['goals_conceded_stat_x'] . ' | ' : NULL);	
									echo( ($row['clean_sheet_stat_x'] != NULL AND $row['clean_sheet_stat_x'] != 0)? 'Weiße Weste: ' . $row['clean_sheet_stat_x'] . ' | ' : NULL);	

									/* Shots */
									echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_stat_x'] . ' | ' : NULL);
									echo( ($row['hit_woodwork_stat'] != NULL AND $row['hit_woodwork_stat'] != 0)? 'Pfosten: ' . $row['hit_woodwork_stat'] . ' | ' : NULL);
									echo( ($row['big_chances_missed_stat_x'] != NULL AND $row['big_chances_missed_stat_x'] != 0)? 'Großchancen vergeben: ' . $row['big_chances_missed_stat_x'] . ' | ' : NULL);

									/* Passing */
									echo( ($row['big_chances_created_stat'] != NULL AND $row['big_chances_created_stat'] != 0)? 'Großchancen kreiert: ' . $row['big_chances_created_stat'] . ' | ' : NULL);
									echo( ($row['key_passes_stat'] != NULL AND $row['key_passes_stat'] != 0)? 'Key-Pässe: ' . $row['key_passes_stat'] . ' | ' : NULL);	
									echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat_x'] . ' | ' : NULL);	
									echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat_x'] . ' | ' : NULL);	

									/* Duels */
									echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat_x'] . ' | ' : NULL);	
									echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat_x'] . ' | ' : NULL);	
									echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' | ' : NULL);	

									/* Defensive stats */
									echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' | ' : NULL);
									echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' | ' : NULL);	
									echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' | ' : NULL);	
									echo( ($row['clearances_offline_stat'] != NULL AND $row['clearances_offline_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_offline_stat'] . ' | ' : NULL);	
									
									/* Goalkeeping */
									echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' | ' : NULL);	
									echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' | ' : NULL);	
									echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' | ' : NULL);	
									echo( ($row['punches_stat'] != NULL AND $row['punches_stat'] != 0)? 'Bälle gefaustet: ' . $row['punches_stat'] . ' | ' : NULL);	
									
									/* Errors */
									echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' | ' : NULL);	
									echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' | ' : NULL);	
									echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' | ' : NULL);	
									echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' | ' : NULL);	
									echo( ($row['error_lead_to_goal_stat'] != NULL AND $row['error_lead_to_goal_stat'] != 0)? 'Patzer: ' . $row['error_lead_to_goal_stat'] . ' | ' : NULL);	
									
									/* Cards */
									echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' | ' : NULL);	
									echo( ($row['redyellowcards_stat'] != NULL AND $row['redyellowcards_stat'] != 0)? 'Gelb-Rot: ' . $row['redyellowcards_stat'] . ' | ' : NULL);	
									
							} else {
										echo 'Kein Einsatz.';
								
							}
							
						echo "<td></tr>";
					}
					echo "</table></div>";
					
				} else {

					// Round is current round
					echo "<div class='kader'><table id='myTable'>";

					while ($row = mysqli_fetch_array($result)) {
						echo "<tr class='summary1'>";
							echo "<td style='display:none;'>" . $row['id'] . "</td>";
							echo "<td style='color: gray;' align='center'>" . $row['position_short'] . "</td>";
							echo "<td><img height='30px' width='auto' src='" . $row['logo_path'] . "'></td>";

							$full_name = $row['display_name']; 
							$name_parts = explode(" ", $full_name); 
							$shortened_name = substr($name_parts[0], 0, 1) . ". " . end($name_parts);

							echo "<td>" . mb_convert_encoding($shortened_name, 'UTF-8') . "</td>";

							$kickoff_time = date('H:i', strtotime($row['kickoff_ts']));
							$projected_score = $row['ftsy_score_projected'];

							if (strtotime($row['kickoff_ts']) <= time()) {
								$actual_score = isset($row['ftsy_score']) ? number_format($row['ftsy_score'], 1) : '0';
							} else {
								$actual_score = '-';
							}

							$minutes_played = isset($row['appearance_stat_x']) ? $row['appearance_stat_x'] : 0;

							if (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] != 'FT') {
								$remaining_minutes = 90 - $minutes_played;
								$adjusted_projected_score = max($projected_score - 4, 0);
								$live_projection_score = $row['ftsy_score'] + ($adjusted_projected_score / 90 * $remaining_minutes);
								$live_projection_score = number_format($live_projection_score, 1);
							} else {
								$live_projection_score = number_format($projected_score, 1);
							}

							$projection_class = ($live_projection_score == 0) ? 'projection zero-score' : 'projection';

							$actual_score_class = '';
							if ($actual_score !== '-') {
								if ($actual_score < 0) {
									$actual_score_class = 'red';
								} elseif ($actual_score >= 0.1 && $actual_score <= 3.9) {
									$actual_score_class = 'orange';
								} elseif ($actual_score >= 4 && $actual_score <= 9.9) {
									$actual_score_class = 'yellow';
								} elseif ($actual_score >= 10 && $actual_score <= 15) {
									$actual_score_class = 'light-green';
								} elseif ($actual_score >= 15.1) {
									$actual_score_class = 'dark-green';
								}
							}

							$player_score_display = "<div class='player-score-wrapper'>
														<span class='$projection_class'>&#8605 $live_projection_score</span>
														<span class='actual-score $actual_score_class'>$actual_score</span>
													 </div>";

							if (strtotime($row['kickoff_ts']) > time()) {
								$matchup_to_display = $row['kickoff_weekday'] . ", " . 
													  $row['kickoff_day'] . "." . 
													  $row['kickoff_month'] . ". " . 
													  $kickoff_time . " vs. " . 
													  $row['gegner_code'] . " (" . 
													  $row['homeaway'] . ")";

								echo "<td style='color: gray; font-size: 14px;'>" . $matchup_to_display . "</td>";
								echo "<td align='center' title='Projection' class='player_score'>" . $player_score_display . "</td>";
							} elseif (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] != 'FT') {
								echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . 
									 " vs. " . $row['gegner_code'] . 
									 "<span style='color: red' class='pulsate'> <small><b> LIVE</b></small></span></td>";
								echo "<td align='center' class='player_score'>" . $player_score_display . "</td>";
							} elseif (strtotime($row['kickoff_ts']) <= time() && $row['fixture_status'] == 'FT') {
								echo "<td style='color: gray;'>" . $row['score_for'] . ":" . $row['score_against'] . 
									 " vs. " . $row['gegner_code'] . 
									 "<span style='color: black'><small><b> FINAL</b></small></span></td>";
								echo "<td align='center' class='player_score'>" . $player_score_display . "</td>";
							}

						echo "</tr>";


						// Detailed player stats

						echo "<tr class= 'player_detail'><td colspan='4'>";
							if ($row['appearance_stat_x'] != NULL) {

								/* Appearance */
								echo( ($row['appearance_stat_x'] != NULL)? 'Gespielte Minuten: ' . $row['appearance_stat_x'] . ' | ' : NULL);	
								
								/* Scoring */
								echo( ($row['goals_minus_pen_stat'] != NULL AND $row['goals_minus_pen_stat'] != 0)? 'Tore: ' . $row['goals_minus_pen_stat'] . ' | ' : NULL);	
								echo( ($row['penalties_total'] != NULL AND $row['penalties_total'] != 0)? '11er: ' . $row['penalties_stat_x'] . ' | ' : NULL);
								echo( ($row['assists_stat'] != NULL AND $row['assists_stat'] != 0)? 'Vorlagen: ' . $row['assists_stat'] . ' | ' : NULL);
								echo( ($row['pen_won_stat'] != NULL AND $row['pen_won_stat'] != 0)? '11er herausgeholt: ' . $row['pen_won_stat'] . ' | ' : NULL);	

								/* Gegentore */
								echo( ($row['goals_conceded_stat_x'] != NULL AND $row['goals_conceded_stat_x'] != 0)? 'Gegentore: ' . $row['goals_conceded_stat_x'] . ' | ' : NULL);	
								echo( ($row['clean_sheet_stat_x'] != NULL AND $row['clean_sheet_stat_x'] != 0)? 'Weiße Weste: ' . $row['clean_sheet_stat_x'] . ' | ' : NULL);	

								/* Shots */
								echo( ($row['shots_total_stat'] != NULL AND $row['shots_total_stat'] != 0)? 'Torschüsse: ' . $row['shots_stat_x'] . ' | ' : NULL);
								echo( ($row['hit_woodwork_stat'] != NULL AND $row['hit_woodwork_stat'] != 0)? 'Pfosten: ' . $row['hit_woodwork_stat'] . ' | ' : NULL);
								echo( ($row['big_chances_missed_stat_x'] != NULL AND $row['big_chances_missed_stat_x'] != 0)? 'Großchancen vergeben: ' . $row['big_chances_missed_stat_x'] . ' | ' : NULL);

								/* Passing */
								echo( ($row['big_chances_created_stat'] != NULL AND $row['big_chances_created_stat'] != 0)? 'Großchancen kreiert: ' . $row['big_chances_created_stat'] . ' | ' : NULL);
								echo( ($row['key_passes_stat'] != NULL AND $row['key_passes_stat'] != 0)? 'Key-Pässe: ' . $row['key_passes_stat'] . ' | ' : NULL);	
								echo( ($row['passes_total'] != NULL AND $row['passes_total'] != 0)? 'Pässe: ' . $row['passes_stat_x'] . ' | ' : NULL);	
								echo( ($row['crosses_total'] != NULL AND $row['crosses_total'] != 0)? 'Flanken: ' . $row['crosses_stat_x'] . ' | ' : NULL);	

								/* Duels */
								echo( ($row['duels_total'] != NULL AND $row['duels_total'] != 0)? 'Duelle: ' . $row['duels_stat_x'] . ' | ' : NULL);	
								echo( ($row['dribble_total'] != NULL AND $row['dribble_total'] != 0)? 'Dribblings: ' . $row['dribble_stat_x'] . ' | ' : NULL);	
								echo( ($row['tackles_stat'] != NULL AND $row['tackles_stat'] != 0)? 'Tacklings: ' . $row['tackles_stat'] . ' | ' : NULL);	

								/* Defensive stats */
								echo( ($row['interceptions_stat'] != NULL AND $row['interceptions_stat'] != 0)? 'Abgefangene Bälle: ' . $row['interceptions_stat'] . ' | ' : NULL);
								echo( ($row['blocks_stat'] != NULL AND $row['blocks_stat'] != 0)? 'Geblockte Schüsse: ' . $row['blocks_stat'] . ' | ' : NULL);	
								echo( ($row['clearances_stat'] != NULL AND $row['clearances_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_stat'] . ' | ' : NULL);	
								echo( ($row['clearances_offline_stat'] != NULL AND $row['clearances_offline_stat'] != 0)? 'Befreiungsschläge: ' . $row['clearances_offline_stat'] . ' | ' : NULL);	
								
								/* Goalkeeping */
								echo( ($row['outside_box_saves_stat'] != NULL AND $row['outside_box_saves_stat'] != 0)? 'Paraden Fernschüsse: ' . $row['outside_box_saves_stat'] . ' | ' : NULL);	
								echo( ($row['inside_box_saves_stat'] != NULL AND $row['inside_box_saves_stat'] != 0)? 'Paraden innerhalb 16er: ' . $row['inside_box_saves_stat'] . ' | ' : NULL);	
								echo( ($row['pen_saved_stat'] != NULL AND $row['pen_saved_stat'] != 0)? '11er gehalten: ' . $row['pen_saved_stat'] . ' | ' : NULL);	
								echo( ($row['punches_stat'] != NULL AND $row['punches_stat'] != 0)? 'Bälle gefaustet: ' . $row['punches_stat'] . ' | ' : NULL);	
								
								/* Errors */
								echo( ($row['pen_committed_stat'] != NULL AND $row['pen_committed_stat'] != 0)? '11er verursacht: ' . $row['pen_committed_stat'] . ' | ' : NULL);	
								echo( ($row['owngoals_stat'] != NULL AND $row['owngoals_stat'] != 0)? 'Eigentore: ' . $row['owngoals_stat'] . ' | ' : NULL);	
								echo( ($row['dispossessed_stat'] != NULL AND $row['dispossessed_stat'] != 0)? 'Ballverluste: ' . $row['dispossessed_stat'] . ' | ' : NULL);	
								echo( ($row['dribbled_past_stat'] != NULL AND $row['dribbled_past_stat'] != 0)? 'Ausgedribbelt: ' . $row['dribbled_past_stat'] . ' | ' : NULL);	
								echo( ($row['error_lead_to_goal_stat'] != NULL AND $row['error_lead_to_goal_stat'] != 0)? 'Patzer: ' . $row['error_lead_to_goal_stat'] . ' | ' : NULL);	
									
								/* Cards */
								echo( ($row['redcards_stat'] != NULL AND $row['redcards_stat'] != 0)? 'Rot: ' . $row['redcards_stat'] . ' | ' : NULL);	
								echo( ($row['redyellowcards_stat'] != NULL AND $row['redyellowcards_stat'] != 0)? 'Gelb-Rot: ' . $row['redyellowcards_stat'] . ' | ' : NULL);	

								/* Projection */
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
