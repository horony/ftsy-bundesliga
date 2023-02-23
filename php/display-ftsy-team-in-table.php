<?php
include("auth.php");
include('../secrets/mysql_db_connection.php');

// Get user and team ids

if (empty($_GET["show_team"])) {
	$show_team = mb_convert_encoding($_SESSION['username'], 'UTF-8');
	$user = mb_convert_encoding($_SESSION['username'],'UTF-8');
	$user_id = $_SESSION['user_id'];
	$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
	$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
} else {
	$show_team =  mb_convert_encoding($_GET["show_team"], 'UTF-8');	
	$user = mysqli_query($con, "SELECT username from xa7580_db1.users WHERE teamname = '".$show_team."' ") -> fetch_object() -> username;	
	$user_id = mysqli_query($con, "SELECT id from xa7580_db1.users WHERE teamname = '".$show_team."' ") -> fetch_object() -> id;	
	$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
	$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
}

// Define if it is users team

if ($_SESSION['user_id'] == $user_id){
	$is_my_team = 1;
} else {
	$is_my_team = 0;
}

// Get season data

$clicked_spieltag = $_GET['tag'];
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;

/****************/
/* 1.) GET DATA */
/****************/

/******************/
/* SQL FOR SEASON */
/******************/

if ($clicked_spieltag == 'Saison') { 

	// Legacy

} elseif ($clicked_spieltag == 'Aktueller Spieltag') {

/*********************/
/* SQL CURRENT ROUND */
/*********************/

$kader = mysqli_query($con,"	

	SELECT 	base.id #
				, base.display_name #
        , base.position_short #
        , base.image_path #
        , case 	when base.is_suspended is not null then 'rote-karte.png'
        				when base.injured = 1 then 'verletzung.png'
        				when base.is_sidelined = 1 then 'verbannung.png'
        				else 'fit.png'
								end as fitness_img
        , base.injury_reason
        , base.team_id
        , base.short_code as team_code
        , case when base.current_team_id = fix.localteam_id then fix.localteam_score else fix.visitorteam_score end as matchup_score_for 
        , case when base.current_team_id = fix.localteam_id then fix.visitorteam_score else fix.localteam_score end as matchup_score_against
        , case when base.current_team_id = fix.localteam_id then team_away.short_code else team_home.short_code end as gegner 
        , case when base.current_team_id = fix.localteam_id then team_away.name else team_home.name end as gegner_name
        , case when base.current_team_id = fix.localteam_id then 'H' else 'A' end as homeaway         
        , base.1_ftsy_match_status
        , fix.round_name
        , fix.fixture_id
        , fix.kickoff_dt
        , fix.kickoff_ts
        , fix.match_status
        , case 	when dayname(fix.kickoff_dt) = 'Monday' then 'Mo.'
        				when dayname(fix.kickoff_dt) = 'Tuesday' then 'Di.'
		        		when dayname(fix.kickoff_dt) = 'Wednesday' then 'Mi.'
		        		when dayname(fix.kickoff_dt) = 'Thursday' then 'Do.'
		        		when dayname(fix.kickoff_dt) = 'Friday' then 'Fr.'
		        		when dayname(fix.kickoff_dt) = 'Saturday' then 'Sa.'
		        		when dayname(fix.kickoff_dt) = 'Sunday' then 'So.'
		        		end as kickoff_weekday
        , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND as kickoff_time_trunc
        , month(fix.kickoff_dt) as kickoff_month
        , day(fix.kickoff_dt) as kickoff_day

        , ftsy.ftsy_score

        , ftsy.appearance_ftsy
        , case 	when ftsy.minutes_played_stat is null and ftsy.appearance_stat = 1 then '1 Min.' 
		        		when ftsy.minutes_played_stat is not null and ftsy.appearance_stat = 1 then concat(ftsy.minutes_played_stat, ' Min.')
		        		else null
		        		end as appearance_stat
        , ftsy.goals_made_ftsy
        , ftsy.goals_made_stat
        , case when ftsy.appearance_stat = 1 then
        	ftsy.penalties_made_ftsy - ftsy.pen_missed_ftsy
        	else null end as penalties_ftsy
        , case when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.penalties_made_stat, ' ('), ftsy.penalties_made_stat + ftsy.pen_missed_stat), ')') else null end as penalties_stat      
        , ftsy.assists_made_ftsy
        , ftsy.assists_made_stat
        , ftsy.clean_sheet_ftsy
        , case when ftsy.clean_sheet_ftsy > 0 then 'ja' when ftsy.clean_sheet_ftsy = 0 then 'nein' else null end as clean_sheet_stat
        , ftsy.shots_total_ftsy
        , ftsy.shots_total_stat
        , ftsy.shots_on_goal_saved_ftsy
        , ftsy.shots_on_goal_saved_stat
        , ftsy.shots_missed_ftsy
        , ftsy.shots_missed_stat
        , ftsy.hit_woodwork_ftsy
        , ftsy.hit_woodwork_stat
        , case when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.passes_complete_stat,' ('),ftsy.passes_complete_stat+ftsy.passes_incomplete_stat),')') else null 
          end as passes_stat
        , case when ftsy.appearance_stat = 1 then ftsy.passes_complete_ftsy + ftsy.passes_incomplete_ftsy else null 
          end as passes_ftsy
        , case when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.crosses_complete_stat,' ('),ftsy.crosses_complete_stat+ftsy.crosses_incomplete_stat),')') else null 
          end as crosses_stat
        , case when ftsy.appearance_stat = 1 then ftsy.crosses_complete_ftsy + ftsy.crosses_incomplete_ftsy else null 
          end as crosses_ftsy
        , ftsy.passes_key_stat
        , ftsy.passes_key_ftsy
        , case when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.duels_won_stat,' ('),ftsy.duels_won_stat+ftsy.duels_lost_stat),')') else null 
          end as duels_stat
        , case when ftsy.appearance_stat = 1 then ftsy.duels_won_ftsy + ftsy.duels_lost_ftsy else null 
          end as duels_ftsy
        , case when ftsy.appearance_stat = 1 then concat(concat(concat(ftsy.dribble_success_stat,' ('),ftsy.dribble_success_stat+ftsy.dribble_fail_stat),')') else null 
          end as dribble_stat
        , case when ftsy.appearance_stat = 1 then ftsy.dribble_success_ftsy + ftsy.dribble_fail_ftsy else null 
          end as dribble_ftsy
        , ftsy.tackles_stat
        , ftsy.tackles_ftsy
        , ftsy.interceptions_stat
        , ftsy.interceptions_ftsy
        , ftsy.blocks_stat
        , ftsy.blocks_ftsy
        , ftsy.clearances_stat
        , ftsy.clearances_ftsy
        , ftsy.outside_box_saves_ftsy
        , ftsy.outside_box_saves_stat
        , ftsy.inside_box_saves_ftsy
        , ftsy.inside_box_saves_stat
        , ftsy.pen_saved_ftsy
        , ftsy.pen_saved_stat
        , ftsy.redcards_stat
        , ftsy.redcards_ftsy
        , ftsy.yellowredcards_stat
        , ftsy.yellowredcards_ftsy
        , ftsy.pen_committed_stat
        , ftsy.pen_committed_ftsy
        , ftsy.owngoals_stat
        , ftsy.owngoals_ftsy
        , ftsy.dispossessed_stat
        , ftsy.dispossessed_ftsy
        , ftsy.dribbled_past_stat
        , ftsy.dribbled_past_ftsy
        , ftsy.clean_sheet_stat
        , ftsy.clean_sheet_ftsy
        , ftsy.pen_won_stat
        , ftsy.pen_won_ftsy
        , allowed.rank as allowed_rank
        , allowed.avg_allowed as allowed_avg
        , case 	when allowed.rank between 1 and 5 then '#d0001f'
        		when allowed.rank between 14 and 18 then '#079c07'
        		else '#666'
        		end as allowed_color
        , proj.ftsy_score_projected

		FROM sm_playerbase_basic_v base

		LEFT JOIN sm_fixtures fix 
			ON 	( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id )
				AND fix.round_name = '".$akt_spieltag."'
				AND fix.season_id = '".$akt_season_id."'
		        
		LEFT JOIN ftsy_scoring_akt_v ftsy
			ON	ftsy.player_id = base.id
		    	AND ftsy.round_name = '".$akt_spieltag."'

		INNER JOIN sm_teams team_home
			ON fix.localteam_id = team_home.id

		INNER JOIN sm_teams team_away
			ON fix.visitorteam_id = team_away.id

		LEFT JOIN ftsy_points_allowed allowed
			ON 	allowed.opp_team_id = case when base.current_team_id = fix.localteam_id then team_away.id else team_home.id end
				AND allowed.position_short = base.position_short

		LEFT JOIN ftsy_scoring_projection_v proj
			ON proj.player_id = base.id

		WHERE 	".$ftsy_owner_column." = '".$user_id."'

		ORDER BY 	case 	when base.1_ftsy_match_status != 'NONE' then 1 else 0 end DESC,
					case 	when base.position_short = 'TW' then 1
								when base.position_short = 'AW' then 2
								when base.position_short = 'MF' then 3
								when base.position_short = 'ST' then 4
								else 0 end
							DESC,
					base.1_ftsy_match_status DESC

	");


} else {

/*********************/
/* SQL CLICKED ROUND */
/*********************/

$kader = mysqli_query($con,"	

	SELECT 	hist.player_id as id 
					, hist.display_name 
        	, hist.position_short 
        	, hist.image_path 
        	, hist.score_for as matchup_score_for 
        	, hist.score_against as matchup_score_against
        	, hist.opp_team_code as gegner 
        	, hist.1_ftsy_match_status
        	, hist.round_name
        	, hist.team_code
	        , hist.kickoff_dt
        	, hist.kickoff_ts
        	, 'FT' as match_status
        	, case 	when hist.is_suspended is not null then 'rote-karte.png'
			        		when hist.injured = 1 then 'verletzung.png'
			        		when hist.is_sidelined = 1 then 'verbannung.png'
			        		else 'fit.png'
									end as fitness_img
        	, hist.injury_reason
        	, hist.ftsy_score
					, hist.appearance_ftsy
	        , case 	when hist.minutes_played_stat is null and hist.appearance_stat = 1 then '1 Min.' 
			        		when hist.minutes_played_stat is not null and hist.appearance_stat = 1 then concat(hist.minutes_played_stat, ' Min.')
			        		else null
			        		end as appearance_stat
	        , hist.goals_made_ftsy
	        , hist.goals_made_stat
	        , case when hist.appearance_stat = 1 then
	        	hist.penalties_made_ftsy - hist.pen_missed_ftsy
	        	else null end as penalties_ftsy
	        , case when hist.appearance_stat = 1 then
	        	concat(concat(concat(hist.penalties_made_stat, ' ('), hist.penalties_made_stat + hist.pen_missed_stat), ')')
	        	else null end as penalties_stat      
	        , hist.assists_made_ftsy
	        , hist.assists_made_stat
	        , hist.clean_sheet_ftsy
	        , case when hist.clean_sheet_ftsy > 0 then 'ja' when hist.clean_sheet_ftsy = 0 then 'nein' else null end as clean_sheet_stat
	        , hist.shots_total_ftsy
	        , hist.shots_total_stat
	        , hist.shots_on_goal_saved_ftsy
	        , hist.shots_on_goal_saved_stat
	        , hist.shots_missed_ftsy
	        , hist.shots_missed_stat
	        , hist.hit_woodwork_ftsy
	        , hist.hit_woodwork_stat
	        , case when hist.appearance_stat = 1 then concat(concat(concat(hist.passes_complete_stat,' ('),hist.passes_complete_stat+hist.passes_incomplete_stat),')') else null 
	          end as passes_stat
	        , case when hist.appearance_stat = 1 then hist.passes_complete_ftsy + hist.passes_incomplete_ftsy else null 
	          end as passes_ftsy
	        , case when hist.appearance_stat = 1 then concat(concat(concat(hist.crosses_complete_stat,' ('),hist.crosses_complete_stat+hist.crosses_incomplete_stat),')') else null 
	          end as crosses_stat
	        , case when hist.appearance_stat = 1 then hist.crosses_complete_ftsy + hist.crosses_incomplete_ftsy else null 
	          end as crosses_ftsy
	        , hist.passes_key_stat
	        , hist.passes_key_ftsy
	        , case when hist.appearance_stat = 1 then concat(concat(concat(hist.duels_won_stat,' ('),hist.duels_won_stat+hist.duels_lost_stat),')') else null 
	          end as duels_stat
	        , case when hist.appearance_stat = 1 then hist.duels_won_ftsy + hist.duels_lost_ftsy else null 
	          end as duels_ftsy
	        , case when hist.appearance_stat = 1 then concat(concat(concat(hist.dribble_success_stat,' ('),hist.dribble_success_stat+hist.dribble_fail_stat),')') else null 
	          end as dribble_stat
	        , case when hist.appearance_stat = 1 then hist.dribble_success_ftsy + hist.dribble_fail_ftsy else null 
	          end as dribble_ftsy
	        , hist.tackles_stat
	        , hist.tackles_ftsy
	        , hist.interceptions_stat
	        , hist.interceptions_ftsy
	        , hist.blocks_stat
	        , hist.blocks_ftsy
	        , hist.clearances_stat
	        , hist.clearances_ftsy
	        , hist.outside_box_saves_ftsy
	        , hist.outside_box_saves_stat
	        , hist.inside_box_saves_ftsy
	        , hist.inside_box_saves_stat
	        , hist.pen_saved_ftsy
	        , hist.pen_saved_stat
	        , hist.redcards_stat
	        , hist.redcards_ftsy
	        , hist.yellowredcards_stat
	        , hist.yellowredcards_ftsy
	        , hist.pen_committed_stat
	        , hist.pen_committed_ftsy
	        , hist.owngoals_stat
	        , hist.owngoals_ftsy
	        , hist.dispossessed_stat
	        , hist.dispossessed_ftsy
	        , hist.dribbled_past_stat
	        , hist.dribbled_past_ftsy
	        , hist.clean_sheet_stat
	        , hist.clean_sheet_ftsy
	        , hist.pen_won_stat
	        , hist.pen_won_ftsy

		FROM ftsy_scoring_hist hist

		WHERE 	".$ftsy_owner_column." = '".$user_id."'
				and season_id = '".$akt_season_id."'
				and round_name = '".$clicked_spieltag."'

		ORDER BY 	case 	when hist.1_ftsy_match_status != 'NONE' then 1 else 0 end DESC,
					case 	when hist.position_short = 'TW' then 1
							when hist.position_short = 'AW' then 2
							when hist.position_short = 'MF' then 3
							when hist.position_short = 'ST' then 4
							else 0 end
							DESC,
					hist.1_ftsy_match_status DESC

	");
}

/**************************/
/* 2.) SUBSTITUTION MODAL */
/**************************/

echo "<div id='myModal' class='modal'>";

  			/* Modal Inhalt */
  			echo 	"<div class='modal_wrapper'>";

	  			echo 	"<div class='modal_header'>
							<div class='modal_headline'>
								Aufstellung ändern
							</div>
							<div id='modal_close'>
								&times;
							</div>
						</div>
						<div class='modal_subheader'>
							Klicke auf die roten/grünen Symbole um Wechsel in deiner Aufstellung durchzuführen.
						</div>";
	  			
	  			echo "<div id='modal-content' class='modal-content'>";
				echo "</div>"; 

  			echo 	"</div>";
echo "</div>"; 

/******************************/
/*	3.) DISPLAY DATA AS TABLE */
/******************************/

/*********************/
/*	TABLE FOR SEASON */
/*********************/

if ($clicked_spieltag == 'Saison') { 

// Legacy

/****************************/
/*	TABLE FOR CLICKED ROUND */
/****************************/

} elseif ( $clicked_spieltag != 'Saison' ) {

	echo "<table class='player_table'>";
	echo "<thead>";
		echo "<tr class='first'>";
			echo "<th rowspan='2' colspan='2' title='Spieler'>Spieler</th>";
			echo "<th rowspan='2' title='Begegnung des aktuellen Spieltages'>Matchup</th>";
			echo "<th rowspan='2'>Punkte</th>";
			echo "<th colspan='3'>Torbeiteiligungen</th>";
			echo "<th rowspan='2'>Einsatz</th>";
			echo "<th colspan='4'>Schüsse</th>";
			echo "<th colspan='3'>Passspiel</th>";
			echo "<th colspan='6'>Zweikämpfe</th>";
			echo "<th colspan='3'>Paraden</th>";
			echo "<th colspan='6'>Fehler</th>";
			echo "<th colspan='2'>Sonstiges</th>";
			#echo "<th colspan='3' class='hide_mobile'>Avg. Fantasy-Punkte</th>";
		echo "</tr>";

		echo "<tr class='second'>";

			echo "<th title='Tore aus dem Spiel'>Tore</th>";
			echo "<th title='Elfmeter-Tore (Elfmeter-Versuche)'>11er</th>";
			echo "<th title='Direkte Assists'>Assists</th>";

			echo "<th title='Torschüsse insgesamt'>Gesamt</th>";
			echo "<th title='Torschüsse gehalten'>Gehalten</th>";
			echo "<th title='Torschüsse neben das Tor'>Vorbei</th>";
			echo "<th title='Torschüsse an Pfosten oder Latte'>Pfosten</th>";

			echo "<th title='Gespielte Pässe'>Pässe</th>";
			echo "<th title='Gespielte Schlüssel-Pässe'>Key</th>";
			echo "<th title='Gespielte Flanken'>Flanken</th>";

			echo "<th title='Erfolgreiche Duelle'>Duelle</th>";			
			echo "<th title='Erfolgreiche und versuchte Dribblings'>Dribblings</th>";
			echo "<th title='Ergolgreiche Tacklings'>Tackles</th>";
			echo "<th title='Abgefangene Bälle'>Abgefangen</th>";
			echo "<th title='Geblockte Bälle'>Blocks</th>";
			echo "<th title='Befreiungsschläge'>Klärungen</th>";

			echo "<th title='Gehaltene Schüsse außerhalb des 16ers'>Fern</th>";
			echo "<th title='Gehaltene Schüsse innerhalb des 16ers'>Nah</th>";
			echo "<th title='Gehaltene Elfmeter'>11er</th>";

			echo "<th title='Platzverweis Rote Karte'>Rot</th>";
			echo "<th title='Platzverweis Gelb-Rote Karte'>Gelb-Rot</th>";
			echo "<th title='Verursachte Elfmeter'>11er</th>";
			echo "<th title='Eigentore'>Eigentor</th>";
			echo "<th title=''>Ballverlust</th>";			
			echo "<th title='Ausgespielt durch Dribblings'>Ausgespielt</th>";			

			echo "<th title='Kein Gegentor'>Weiße Weste</th>";
			echo "<th title='11er geholt'>11er geholt</th>";

		echo "</tr>";
	echo "</thead>";

	// display players
	while($row = mysqli_fetch_array($kader)) {

		$complete_matchup = $row['matchup_score_for'] . ":" . $row['matchup_score_against'] . " vs. " . $row['gegner'] . "&nbsp;<span class='matchup_status_final'>FINAL</span>";

		echo "<tr class=''>";
			echo "<td class='change'>";
				if ($row['kickoff_ts'] > date('Y-m-d H:i:s') ){

					if ($is_my_team == 1){
					echo "<div class='myBtn' style='cursor: pointer;' data-id='" . $row['id'] . "' onclick='changePlayer(this)'><img height='15px' width='auto' title='Spieler austauschen' src='/images/icons/exchange.png'></div>";
					} else {
					echo "<div class='myBtn' style='cursor:;' data-id='" . $row['id'] . "' onclick='#'><img height='15px' width='auto' title='Spieler austauschen' src='/images/icons/exchange.png'></div>";
					}

				}
			echo "</td>";
			echo "<td class = td_player>";
				echo "<div class ='player_wrapper'>";
					if ($row['1_ftsy_match_status'] != 'NONE'){ 
						echo "<div class='pos_status'>".$row['position_short']."</div>";
					} else {
						echo "<div class='pos_status'>BN</div>";
					}  
					echo "<div class='player_card'>";
						echo "<div>";
							echo "<img height='40px' width='auto' src='" . $row['image_path'] . "'>";
						echo "</div>";
						echo "<div class='player_card_text'>";
							echo "<div class='player_card_name'>";
								$link_datenbank = 'spieler_datenbank.php?click_player=' . strval($row['id']);
								echo "<a href='" . $link_datenbank . "'>" . ($row['display_name']) . "</a>";
							echo "</div>";
							echo "<div class='player_card_detail'>";
								echo  $row['position_short'] . " - " . $row['team_code'] . "<img title='" . mb_convert_encoding($row['injury_reason'], 'UTF-8') . "' height='13px' width='auto' src='/images/fitness/" . $row['fitness_img'] . "'>";			
							echo "</div>";									
						echo "</div>";			
					echo "</div>";
				echo "</div>";	
			echo "</td>";
			echo "<td class='matchup_stat'>";

				if ($row['kickoff_ts'] < date('Y-m-d H:i:s')) {

					echo $row['matchup_score_for'] . ":" . $row['matchup_score_against'] . " vs. " . $row['gegner'];	
					if ($row['match_status'] == 'FT'){
						echo "&nbsp;<span class='matchup_status_final'>FINAL</span>";
					} else {
						echo "&nbsp;<span class='matchup_status_live pulsate'>LIVE</span>";
					}

				} else {
						echo "<span style='' title='".$row['gegner_name'] . " lässt im Schnitt " . $row['allowed_avg'] . " Punkte gegen " .$row['position_short']. " zu (Platz ".$row['allowed_rank']."/18)'>" . $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). "<span style='cursor: help; color:".$row['allowed_color']."'> vs. ".$row['gegner']. "</span> (".$row['homeaway'].")</span>";
					}

			echo "</td>";			
			if ($row['kickoff_ts'] > date('Y-m-d H:i:s')) {
				echo "<td class='stat highlight_stat' title='Projection' style='color: #483D8B'>".$row['ftsy_score_projected']."</td>";
			} else {
				echo "<td class='stat highlight_stat' title=''>".$row['ftsy_score']."</td>";
			}
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['goals_made_ftsy']."'>".$row['goals_made_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['penalties_ftsy']."'>".$row['penalties_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['assists_made_ftsy']."'>".$row['assists_made_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['appearance_ftsy']."'>".$row['appearance_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['shots_total_ftsy']."'>".$row['shots_total_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['shots_on_goal_saved_ftsy']."'>".$row['shots_on_goal_saved_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['shots_missed_ftsy']."'>".$row['shots_missed_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['hit_woodwork_ftsy']."'>".$row['hit_woodwork_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['passes_ftsy']."'>".$row['passes_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['passes_key_ftsy']."'>".$row['passes_key_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['crosses_ftsy']."'>".$row['crosses_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['duels_ftsy']."'>".$row['duels_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['dribble_ftsy']."'>".$row['dribble_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['tackles_ftsy']."'>".$row['tackles_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['interceptions_ftsy']."'>".$row['interceptions_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['blocks_ftsy']."'>".$row['blocks_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['clearances_ftsy']."'>".$row['clearances_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['outside_box_saves_ftsy']."'>".$row['outside_box_saves_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['inside_box_saves_ftsy']."'>".$row['inside_box_saves_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['pen_saved_ftsy']."'>".$row['pen_saved_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['redcards_ftsy']."'>".$row['redcards_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['yellowredcards_ftsy']."'>".$row['yellowredcards_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['pen_committed_ftsy']."'>".$row['pen_committed_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['owngoals_ftsy']."'>".$row['owngoals_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['dispossessed_ftsy']."'>".$row['dispossessed_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['dribbled_past_ftsy']."'>".$row['dribbled_past_stat']."</td>";

			echo "<td class='stat' title='Fantasy-Punkte: ".$row['clean_sheet_ftsy']."'>".$row['clean_sheet_stat']."</td>";
			echo "<td class='stat' title='Fantasy-Punkte: ".$row['pen_won_ftsy']."'>".$row['pen_won_stat']."</td>";

		echo "</tr>";
		}
	echo "</table>";

} else {

	// Legacy

}
?> 
