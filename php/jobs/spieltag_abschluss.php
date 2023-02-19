<?php
/********************************************************/
/* SCRIPT ZUM SPIELTAGABSCHLUSS				*/
/*  [1] Schreibt aktuelle scoring_akt in scoring_hist 	*/
/*  [2] Update scoring_snap 				*/
/*  [3] Update des Spieltages-Ergebnisses		*/
/*  [4] Update der Tabelle				*/
/*  [5] Update der Waiver nach Spieltag 		*/
/*  [6] Parameter Spieltag überschreiben (+1)		*/
/********************************************************/

// Establish and check DB connection
include 'auth.php';
include '../db.php';

// Hole Parameter
$aktueller_spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter") -> fetch_object() -> spieltag;
$aktueller_spieltag_type = mysqli_query($con, "SELECT match_type FROM xa7580_db1.ftsy_schedule WHERE buli_round_name = (SELECT spieltag FROM xa7580_db1.parameter) AND season_id = (SELECT season_id FROM xa7580_db1.parameter) LIMIT 1") -> fetch_object() -> match_type;
$akt_season_id = mysqli_query($con, "SELECT season_id FROM xa7580_db1.parameter") -> fetch_object() -> season_id;
$akt_round_id = mysqli_query($con, "SELECT id FROM xa7580_db1.sm_rounds WHERE name = '$aktueller_spieltag' and season_id = '$akt_season_id' ") -> fetch_object() -> id;

// Lösche mögliche Doppeleinträge, und historisiere aktuelle fantasy_scoring_akt in fantasy_scoring_hist
mysqli_query($con, "
	DELETE 
	FROM 	xa7580_db1.ftsy_scoring_hist 
	WHERE 	( round_name = '".$aktueller_spieltag."' and season_id = '".$akt_season_id."' ) 
		or round_name is null 
");

mysqli_query($con, "

	INSERT INTO `ftsy_scoring_hist`(`season_id`, `season_name`, `round_name`, `fixture_id`, `player_id`, `display_name`, `common_name`, `number`, `position_short`, `injured`, `injury_reason`, `is_suspended`, `is_sidelined`, `image_path`, `current_team_id`, `team_name`, `team_code`, `logo_path`, `1_ftsy_owner_type`, `1_ftsy_owner_id`, `1_ftsy_match_status`, `2_ftsy_owner_type`, `2_ftsy_owner_id`, `2_ftsy_match_status`, `kickoff_dt`, `kickoff_ts`, `kickoff_time`, `homeaway`, `opp_team_name`, `opp_team_code`, `opp_team_id`, `score_for`, `score_against`, `ftsy_score`, `appearance_ftsy`, `appearance_stat`, `captain_ftsy`, `captain_stats`, `minutes_played_ftsy`, `minutes_played_stat`, `clean_sheet_ftsy`, `clean_sheet_stat`, `goals_made_ftsy`, `goals_made_stat`, `penalties_made_ftsy`, `penalties_made_stat`, `assists_made_ftsy`, `assists_made_stat`, `owngoals_ftsy`, `owngoals_stat`, `redcards_ftsy`, `redcards_stat`, `yellowredcards_ftsy`, `yellowredcards_stat`, `dribble_attempts_ftsy`, `dribble_attempts_stat`, `dribble_success_ftsy`, `dribble_success_stat`, `dribble_fail_ftsy`, `dribble_fail_stat`, `dribbled_past_ftsy`, `dribbled_past_stat`, `duels_total_ftsy`, `duels_total_stat`, `duels_won_ftsy`, `duels_won_stat`, `duels_lost_ftsy`, `duels_lost_stat`, `fouls_drawn_ftsy`, `fouls_drawn_stat`, `fouls_committed_ftsy`, `fouls_committed_stat`, `shots_total_ftsy`, `shots_total_stat`, `shots_on_goal_ftsy`, `shots_on_goal_stat`, `shots_missed_ftsy`, `shots_missed_stat`, `shots_on_goal_saved_ftsy`, `shots_on_goal_saved_stat`, `crosses_total_ftsy`, `crosses_total_stat`, `crosses_complete_ftsy`, `crosses_complete_stat`, `crosses_incomplete_ftsy`, `crosses_incomplete_stat`, `passes_total_ftsy`, `passes_total_stat`, `passes_complete_ftsy`, `passes_complete_stat`, `passes_incomplete_ftsy`, `passes_incomplete_stat`, `passes_key_ftsy`, `passes_key_stat`, `blocks_ftsy`, `blocks_stat`, `clearances_ftsy`, `clearances_stat`, `dispossessed_ftsy`, `dispossessed_stat`, `hit_woodwork_ftsy`, `hit_woodwork_stat`, `interceptions_ftsy`, `interceptions_stat`, `offsides_ftsy`, `offsides_stat`, `tackles_ftsy`, `tackles_stat`, `inside_box_saves_ftsy`, `inside_box_saves_stat`, `outside_box_saves_ftsy`, `outside_box_saves_stat`, `saves_ftsy`, `saves_stat`, `pen_committed_ftsy`, `pen_committed_stat`, `pen_missed_ftsy`, `pen_missed_stat`, `pen_saved_ftsy`, `pen_saved_stat`, `pen_won_ftsy`, `pen_won_stat`) 

	SELECT 	fix.season_id
		, fix.season_name
		, fix.round_name
		, fix.fixture_id
		, base.id
		, base.display_name
		, base.common_name
		, base.number
		, base.position_short
		, base.injured
		, base.injury_reason
		, base.is_suspended
		, base.is_sidelined
		, base.image_path
		, base.current_team_id
		, base.name 
		, base.short_code 
		, base.logo_path 
		, base.1_ftsy_owner_type
		, base.1_ftsy_owner_id
		, base.1_ftsy_match_status
		, base.2_ftsy_owner_type
		, base.2_ftsy_owner_id
		, base.2_ftsy_match_status
		, fix.kickoff_dt
		, fix.kickoff_ts
		, fix.kickoff_time
		, case when fix.localteam_name_code = base.short_code then 'H' else 'A' end 
		, case when fix.localteam_name_code = base.short_code then fix.visitorteam_name else fix.localteam_name end 
		, case when fix.localteam_name_code = base.short_code then fix.visitorteam_name_code else fix.localteam_name_code end 
		, case when fix.localteam_name_code = base.short_code then fix.visitorteam_id else fix.localteam_id end 
		, case when fix.localteam_name_code = base.short_code then fix.localteam_score else fix.visitorteam_score end 
		, case when fix.localteam_name_code = base.short_code then fix.visitorteam_score else fix.localteam_score end 
		, `ftsy_score`
		, `appearance_ftsy`
		, `appearance_stat`
		, `captain_ftsy`
		, `captain_stats`
		, `minutes_played_ftsy`
		, `minutes_played_stat`
		, `clean_sheet_ftsy`
		, `clean_sheet_stat`
		, `goals_made_ftsy`
		, `goals_made_stat`
		, `penalties_made_ftsy`
		, `penalties_made_stat`
		, `assists_made_ftsy`
		, `assists_made_stat`
		, `owngoals_ftsy`
		, `owngoals_stat`
		, `redcards_ftsy`
		, `redcards_stat`
		, `yellowredcards_ftsy`
		, `yellowredcards_stat`
		, `dribble_attempts_ftsy`
		, `dribble_attempts_stat`
		, `dribble_success_ftsy`
		, `dribble_success_stat`
		, `dribble_fail_ftsy`
		, `dribble_fail_stat`
		, `dribbled_past_ftsy`
		, `dribbled_past_stat`
		, `duels_total_ftsy`
		, `duels_total_stat`
		, `duels_won_ftsy`
		, `duels_won_stat`
		, `duels_lost_ftsy`
		, `duels_lost_stat`
		, `fouls_drawn_ftsy`
		, `fouls_drawn_stat`
		, `fouls_committed_ftsy`
		, `fouls_committed_stat`
		, `shots_total_ftsy`
		, `shots_total_stat`
		, `shots_on_goal_ftsy`
		, `shots_on_goal_stat`
		, `shots_missed_ftsy`
		, `shots_missed_stat`
		, `shots_on_goal_saved_ftsy`
		, `shots_on_goal_saved_stat`
		, `crosses_total_ftsy`
		, `crosses_total_stat`
		, `crosses_complete_ftsy`
		, `crosses_complete_stat`
		, `crosses_incomplete_ftsy`
		, `crosses_incomplete_stat`
		, `passes_total_ftsy`
		, `passes_total_stat`
		, `passes_complete_ftsy`
		, `passes_complete_stat`
		, `passes_incomplete_ftsy`
		, `passes_incomplete_stat`
		, `passes_key_ftsy`
		, `passes_key_stat`
		, `blocks_ftsy`
		, `blocks_stat`
		, `clearances_ftsy`
		, `clearances_stat`
		, `dispossessed_ftsy`
		, `dispossessed_stat`
		, `hit_woodwork_ftsy`
		, `hit_woodwork_stat`
		, `interceptions_ftsy`
		, `interceptions_stat`
		, `offsides_ftsy`
		, `offsides_stat`
		, `tackles_ftsy`
		, `tackles_stat`
		, `inside_box_saves_ftsy`
		, `inside_box_saves_stat`
		, `outside_box_saves_ftsy`
		, `outside_box_saves_stat`
		, `saves_ftsy`
		, `saves_stat`
		, `pen_committed_ftsy`
		, `pen_committed_stat`
		, `pen_missed_ftsy`
		, `pen_missed_stat`
		, `pen_saved_ftsy`
		, `pen_saved_stat`
		, `pen_won_ftsy`
		, `pen_won_stat`

	FROM 	`sm_playerbase_basic_v` base

	INNER JOIN sm_fixtures_basic_v fix
		ON 	fix.round_name = (SELECT spieltag from parameter)
	    		and fix.season_id = (SELECT season_id from parameter)
	        	and ( base.current_team_id  = fix.localteam_id or base.current_team_id = fix.visitorteam_id)
	  
	LEFT JOIN ftsy_scoring_akt_v scr
		ON 	scr.player_id = base.id

	WHERE base.current_team_id is not null 
");


sleep(10);

//Lösche alten Snapshot und erstelle neuen Snapshot
include 'create_ftsy_snapshot.php';

//Update Schedule
//if ($aktueller_spieltag != 20) {

	mysqli_query($con, "UPDATE xa7580_db1.ftsy_schedule sch
	    LEFT JOIN (
	        SELECT 	own.1_ftsy_owner_id as Besitzer1
	        		, SUM(COALESCE(akt.ftsy_score,0)) as fantasy_score1
	        		, COUNT(own.player_id) as anz1

			FROM ftsy_player_ownership own
			LEFT JOIN ftsy_scoring_akt_v akt
				ON own.player_id = akt.player_id
			WHERE 	own.1_ftsy_owner_type = 'USR' 
			       	AND own.1_ftsy_match_status != 'NONE'
			GROUP BY own.1_ftsy_owner_id
	    ) akt_score_1
	    ON sch.ftsy_home_id = akt_score_1.Besitzer1
	     LEFT JOIN (
	        SELECT 	own.1_ftsy_owner_id as Besitzer2
	        		, SUM(COALESCE(akt.ftsy_score,0)) as fantasy_score2
	        		, COUNT(own.player_id) as anz2

			FROM ftsy_player_ownership own
			LEFT JOIN ftsy_scoring_akt_v akt
				ON own.player_id = akt.player_id
			WHERE 	own.1_ftsy_owner_type = 'USR' 
			       	AND own.1_ftsy_match_status != 'NONE'
			GROUP BY own.1_ftsy_owner_id
	    ) akt_score_2
	    ON sch.ftsy_away_id = akt_score_2.Besitzer2
	SET 	sch.ftsy_home_score = CASE WHEN anz1 = 11 THEN COALESCE(akt_score_1.fantasy_score1,0) ELSE -20 END
			, sch.ftsy_away_score = CASE WHEN anz2 = 11 THEN COALESCE(akt_score_2.fantasy_score2,0) ELSE -20 END
	WHERE sch.buli_round_name = '".$aktueller_spieltag."' and season_id = '".$akt_season_id."'

	");

//}

//Update Tabelle
if ($aktueller_spieltag_type == 'league'){
	//if ($aktueller_spieltag != 20){
		mysqli_query($con, "DELETE FROM xa7580_db1.ftsy_tabelle_2020 WHERE spieltag = '".$aktueller_spieltag."' and season_id = '".$akt_season_id."' and league_id = 1 ");
		//performt den Insert des neuen Spieltages in die fantasy_tabelle
		mysqli_query($con, "INSERT INTO xa7580_db1.ftsy_tabelle_2020 (season_id, league_id, spieltag, round_id, rang, player_id, team_name, score_for, score_against, differenz, avg_for, avg_against, siege, niederlagen, unentschieden, trost, punkte, h2h, serie, updown)

		SELECT 	'$akt_season_id' as season_id
			, 1 as league_id
			, '$aktueller_spieltag' as spieltag
			, '$akt_round_id' as round_id
			, @curStanding := @curStanding + 1 AS new_standing
			, complete_table.user_id
			, complete_table.user_teamname
			, complete_table.score_for
			, complete_table.score_against
			, complete_table.differenz
			, complete_table.avg_for
			, complete_table.avg_against
			, complete_table.s
			, complete_table.n
			, complete_table.u
			, complete_table.t
			, complete_table.points
			, complete_table.h2h_tiebraker 
		    	, CONCAT(CONCAT(CONCAT(CONCAT(complete_table.Last_3,'-'), complete_table.Last_2),'-'),complete_table.Last_1) as serie
		    	, '-' as updown
		FROM ( 

			SELECT finale_tabelle2.*, 
				# aus [6] 
				CASE WHEN last3.ftsy_home_id = finale_tabelle2.team_id THEN
						CASE WHEN last3.ftsy_home_score > last3.ftsy_away_score THEN 'S'
							 WHEN last3.ftsy_home_score < last3.ftsy_away_score THEN 'N'
							 WHEN last3.ftsy_home_score = last3.ftsy_away_score THEN 'U' END
					 WHEN last3.ftsy_away_id = finale_tabelle2.team_id THEN
						CASE WHEN last3.ftsy_away_score > last3.ftsy_home_score THEN 'S'
							 WHEN last3.ftsy_away_score < last3.ftsy_home_score THEN 'N'
							 WHEN last3.ftsy_away_score = last3.ftsy_home_score THEN 'U' END
					 ELSE '-'
				END AS Last_3,
				CASE WHEN last2.ftsy_home_id = finale_tabelle2.team_id THEN
						CASE WHEN last2.ftsy_home_score > last2.ftsy_away_score THEN 'S'
							 WHEN last2.ftsy_home_score < last2.ftsy_away_score THEN 'N'
							 WHEN last2.ftsy_home_score = last2.ftsy_away_score THEN 'U' END
					 WHEN last2.ftsy_away_id = finale_tabelle2.team_id THEN
						CASE WHEN last2.ftsy_away_score > last2.ftsy_home_score THEN 'S'
							 WHEN last2.ftsy_away_score < last2.ftsy_home_score THEN 'N'
							 WHEN last2.ftsy_away_score = last2.ftsy_home_score THEN 'U' END
				 	 ELSE '-'
				END AS Last_2,
				CASE WHEN last1.ftsy_home_id = finale_tabelle2.team_id THEN
						CASE WHEN last1.ftsy_home_score > last1.ftsy_away_score THEN 'S'
							 WHEN last1.ftsy_home_score < last1.ftsy_away_score THEN 'N'
							 WHEN last1.ftsy_home_score = last1.ftsy_away_score THEN 'U' END
					 WHEN last1.ftsy_away_id = finale_tabelle2.team_id THEN
						CASE WHEN last1.ftsy_away_score > last1.ftsy_home_score THEN 'S'
							 WHEN last1.ftsy_away_score < last1.ftsy_home_score THEN 'N'
							 WHEN last1.ftsy_away_score = last1.ftsy_home_score THEN 'U' END
				 	 ELSE '-'
				END AS Last_1
			 	, users.id as user_id
			 	, users.teamname as user_teamname

			FROM (#FROM FINAL TABELLE2

				SELECT 	#FROM FINAL TABELLE
						 team_id 
						/* aus [1] */
						,s, n, u
						/* aus [2] */
						,t
						/* aus [3] */ 
						, score_for, score_against, differenz, avg_for, avg_against
						/* aus [2] */
						, points 
						
					   	/* aus [5] berechne H2H-Tiebraker */
						,SUM(CASE 	WHEN team_id = ftsy_home_id THEN CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END
						  		WHEN team_id = ftsy_away_id THEN CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END
								ELSE 0
								END) as h2h_tiebraker

				FROM (
					/* [1]+[2] S, U, N, T*/
					SELECT * 
					FROM (

						SELECT 	alle.team_name
								, alle.team_id
								, COUNT(alle.team_id) as games
								, SUM(alle.w) as s
								, SUM(alle.l) as n
								, SUM(alle.d) as u
								, SUM(alle.t) as t
								, SUM(alle.w)*3+SUM(alle.d)+SUM(alle.t) as points 
								, punkte.score_for
								, punkte.score_against
								, punkte.differenz
								, punkte.avg_for
								, punkte.avg_against
						FROM (
								/* [1] Berechne Siege, Niederlagen, Untentschieden für alle Teams für die relevanten Spieltage*/
								SELECT * 
								FROM (
									/* Suche alle Siege wenn das Team das Heim-Team ist*/
									SELECT 	ftsy_home_name as team_name,
											ftsy_home_id as team_id, 
											SUM(CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END) as w, 
											SUM(CASE WHEN ftsy_home_score < ftsy_away_score THEN 1 ELSE 0 END) as l, 
											SUM(CASE WHEN ftsy_home_score = ftsy_away_score THEN 1 ELSE 0 END) as d,
											0 as t
									FROM xa7580_db1.ftsy_schedule
									WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and match_type = 'league'
									GROUP BY ftsy_home_name, ftsy_home_id

									UNION ALL

									/* Suche alle Siege wenn das Team das Away-Team ist*/
									SELECT 	ftsy_away_name as team_name,
											ftsy_away_id as team_id,  
											SUM(CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END) as w, 
											SUM(CASE WHEN ftsy_away_score < ftsy_home_score THEN 1 ELSE 0 END) as l, 
											SUM(CASE WHEN ftsy_away_score = ftsy_home_score THEN 1 ELSE 0 END) as d,
											0 as t
									FROM xa7580_db1.ftsy_schedule
									WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and match_type = 'league'
									GROUP BY ftsy_away_name, ftsy_away_id
								) standard

								UNION ALL

								/* [2] Berechne Trostpreise den Trostpreis und klebe ihn per Union an die Standardtabelle*/
								SELECT * 
								FROM (
									SELECT team_name, team_id, 0 as w, 0 as l, 0 as d, COUNT(team_id) as t
									FROM (
										/* Suche alle Home-Teams die verloren haben */
										SELECT 	ftsy_home_name as team_name,
												ftsy_home_id as team_id,
												ftsy_home_score as score,
												buli_round_name as spieltag
										FROM xa7580_db1.ftsy_schedule
										WHERE 	ftsy_home_score < ftsy_away_score
												and match_type = 'league'
										AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)

										UNION ALL

										/* Suche alle Away-Teams die verloren haben */
										SELECT 	ftsy_away_name as team_name,
												ftsy_away_id as team_id,
												ftsy_away_score as score,
												buli_round_name as spieltag
										FROM xa7580_db1.ftsy_schedule
										WHERE 	ftsy_away_score < ftsy_home_score
												and match_type = 'league'
										AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
									
									) all_losers

								INNER JOIN (
									/* Suche die Max-Scores der Verlierer pro Spieltag und joine an alle Verlierer */
									    SELECT 	bl.spieltag
									    		, MAX(bl.score) as score 
										FROM (
										   	/* Suche alle Home-Teams die verloren haben */
											SELECT 	ftsy_home_name as team_name,
													ftsy_home_id as team_id,
													ftsy_home_score as score,
													buli_round_name as spieltag
											FROM xa7580_db1.ftsy_schedule
											WHERE 	ftsy_home_score < ftsy_away_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'

											UNION ALL

											/* Suche alle Away-Teams die verloren haben */
											SELECT 	ftsy_away_name as team_name,
													ftsy_away_id as team_id,
													ftsy_away_score as score,
													buli_round_name as spieltag
											FROM xa7580_db1.ftsy_schedule
											WHERE 	ftsy_away_score < ftsy_home_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
										) bl

										GROUP BY bl.spieltag 
									) top_scores
										ON top_scores.spieltag = all_losers.spieltag AND top_scores.score = all_losers.score
										
									GROUP BY team_name, team_id
								) best_loosers
								) alle

						/* [3] Joine Punkte, AVG etc. an die Tabelle */
						INNER JOIN (
						/* [3] Suche erneut alle Spiele (Home + Away), kalkuliere AVG & SUM Scores und joine an die Tabelle aus [1] und [2] */
							SELECT 
								standard.team_id as team_id,
								SUM(standard.score_for) as score_for, 
								SUM(standard.score_against) as score_against, 
						        SUM(standard.score_for)-SUM(standard.score_against) as differenz,
						        ROUND(AVG(standard.score_for),0) as avg_for, 
								ROUND(AVG(standard.score_against),0) as avg_against
						    FROM (
						        	/* Suche alle Siege wenn das Team das Heim-Team ist*/
						        	SELECT	ftsy_home_id as team_id, 
											ftsy_home_score as score_for, 
											ftsy_away_score as score_against, 
											ftsy_home_score-ftsy_away_score as differenz
									FROM xa7580_db1.ftsy_schedule
									WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and match_type = 'league'

									UNION ALL

									/* Suche alle Siege wenn das Team das Heim-Team ist*/
									SELECT 	ftsy_away_id as team_id, 
											ftsy_away_score as score_for, 
											ftsy_home_score as score_against, 
											ftsy_away_score-ftsy_home_score as differenz
									FROM xa7580_db1.ftsy_schedule
									WHERE buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and match_type = 'league'
								) standard

							GROUP BY team_id
						) punkte
						ON punkte.team_id = alle.team_id

					GROUP BY team_name, team_id
					ORDER BY points DESC
					) punkteandscore

				/* [4] Für H2H-Tiebraker: Left Join alle Gegner die Punktgleich sind */
				LEFT JOIN (
					/* [4] Berchechne erneut den kompletten Score aus [1] und [2] */
					SELECT 	alle.team_id as team_hth
							, SUM(alle.w)*3+SUM(alle.d)+SUM(alle.t) as points2
					FROM (
						/* Berechne w, l, d*/
						SELECT * 
						FROM (
							/* Suche alle Siege wenn das Team das Heim-Team ist*/
								SELECT 	ftsy_home_name as team_name,
										ftsy_home_id as team_id, 
										SUM(CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END) as w, 
										SUM(CASE WHEN ftsy_home_score < ftsy_away_score THEN 1 ELSE 0 END) as l, 
										SUM(CASE WHEN ftsy_home_score = ftsy_away_score THEN 1 ELSE 0 END) as d,
										0 as t
								FROM xa7580_db1.ftsy_schedule
								WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
										and match_type = 'league'
								GROUP BY ftsy_home_name, ftsy_home_id

								UNION ALL

								/* Suche alle Siege wenn das Team das Away-Team ist*/
								SELECT 	ftsy_away_name as team_name,
										ftsy_away_id as team_id,  
										SUM(CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END) as w, 
										SUM(CASE WHEN ftsy_away_score < ftsy_home_score THEN 1 ELSE 0 END) as l, 
										SUM(CASE WHEN ftsy_away_score = ftsy_home_score THEN 1 ELSE 0 END) as d,
										0 as t
								FROM xa7580_db1.ftsy_schedule
								WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
										and match_type = 'league'
								GROUP BY ftsy_away_name, ftsy_away_id
							) standard

						UNION ALL

						/* Berechne Trostpreise*/
						SELECT * 
						FROM (
							SELECT team_name, team_id, 0 as w, 0 as l, 0 as d, COUNT(team_id) as t
							FROM (
								/* Suche alle Home-Teams die verloren haben */
								SELECT 	ftsy_home_name as team_name,
										ftsy_home_id as team_id,
										ftsy_home_score as score,
										buli_round_name as spieltag
								FROM xa7580_db1.ftsy_schedule
								WHERE 	ftsy_home_score < ftsy_away_score
										and match_type = 'league'
										AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)

								UNION ALL

										/* Suche alle Away-Teams die verloren haben */
								SELECT 	ftsy_away_name as team_name,
										ftsy_away_id as team_id,
										ftsy_away_score as score,
										buli_round_name as spieltag
								FROM xa7580_db1.ftsy_schedule
								WHERE 	ftsy_away_score < ftsy_home_score
										and match_type = 'league'
										AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
									
								) all_losers

								INNER JOIN (
									/* Suche die Max-Scores der Verlierer pro Spieltag und joine an alle Verlierer */
									    SELECT 	bl.spieltag
									    		, MAX(bl.score) as score 
										FROM (
										   	/* Suche alle Home-Teams die verloren haben */
											SELECT 	ftsy_home_name as team_name,
													ftsy_home_id as team_id,
													ftsy_home_score as score,
													buli_round_name as spieltag
											FROM xa7580_db1.ftsy_schedule
											WHERE 	ftsy_home_score < ftsy_away_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'

											UNION ALL

											/* Suche alle Away-Teams die verloren haben */
											SELECT 	ftsy_away_name as team_name,
													ftsy_away_id as team_id,
													ftsy_away_score as score,
													buli_round_name as spieltag
											FROM xa7580_db1.ftsy_schedule
											WHERE 	ftsy_away_score < ftsy_home_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
										) bl

										GROUP BY bl.spieltag 
									) top_scores
										ON top_scores.spieltag = all_losers.spieltag AND top_scores.score = all_losers.score
									GROUP BY team_name, team_id
								) best_loosers
							) alle
					group by team_id 
					order by points2 desc
					) zwei
					ON zwei.points2 = punkteandscore.points	AND zwei.team_hth != punkteandscore.team_id
			    
				/* [5] Joine die Matchdetails zu den Gegnern die Punktgleich sind, Tiebraker wird im Select berechnet*/
				LEFT JOIN xa7580_db1.ftsy_schedule h2h_match
					ON (h2h_match.ftsy_home_id = team_id OR h2h_match.ftsy_away_id = team_id)
						AND (h2h_match.ftsy_away_id = team_hth OR h2h_match.ftsy_home_id = team_hth)
						AND buli_round_name BETWEEN 1 AND  (SELECT spieltag FROM parameter)
						AND match_type = 'league'
					
				) finale_tabelle

				GROUP BY team_name, team_id, games, s, n, u, t, score_for, score_against, differenz, avg_for, avg_against, points
				ORDER BY points DESC, h2h_tiebraker DESC, score_for DESC, score_against DESC
			) finale_tabelle2

			#[6] S,U,N aus der letzten 3 Spielen 
			LEFT JOIN xa7580_db1.ftsy_schedule last1
				ON (finale_tabelle2.team_id = last1.ftsy_home_id OR finale_tabelle2.team_id = last1.ftsy_away_id) AND last1.buli_round_name = (SELECT spieltag FROM parameter)

			LEFT JOIN xa7580_db1.ftsy_schedule last2
				ON (finale_tabelle2.team_id = last2.ftsy_home_id OR finale_tabelle2.team_id = last2.ftsy_away_id) AND last2.buli_round_name = (SELECT spieltag FROM parameter)-1

			LEFT JOIN xa7580_db1.ftsy_schedule last3
				ON (finale_tabelle2.team_id = last3.ftsy_home_id OR finale_tabelle2.team_id = last3.ftsy_away_id) AND last3.buli_round_name = (SELECT spieltag FROM parameter)-2
			 
			LEFT JOIN xa7580_db1.users users 
			 	ON (finale_tabelle2.team_id = users.id)

			ORDER BY points DESC, h2h_tiebraker DESC, score_for DESC, score_against DESC 

		) complete_table, (SELECT @curStanding:= 0) standing

		ORDER BY 	complete_table.points DESC
					, complete_table.h2h_tiebraker DESC
					, complete_table.S DESC
					, complete_table.score_for DESC
					, complete_table.score_against DESC
					, RAND ()
		");
	
		//update Ranking -- WOKkAROUND --
		mysqli_query($con, "	UPDATE 	ftsy_tabelle_2020 tab 
					INNER JOIN (
						SELECT 	tab.*
							, @curRank := @curRank + 1 AS rank
						FROM 	ftsy_tabelle_2020 tab, (SELECT @curRank := 0) r
						WHERE 	tab.season_id = (select season_id from parameter)
							and tab.spieltag = (select spieltag from parameter)
						ORDER BY 	tab.punkte DESC
								, tab.h2h DESC
								, tab.siege DESC
								, tab.score_for DESC
								, tab.score_against DESC
								, RAND ()
						) upd
						ON 	tab.player_id = upd.player_id
							and tab.spieltag = upd.spieltag
							and tab.season_id = upd.season_id

					SET 	tab.rang = upd.rank
							 
					WHERE 	tab.season_id = (select season_id from parameter)
						and tab.spieltag = (select spieltag from parameter)
					");

		//update Updown
		mysqli_query($con, "	UPDATE	xa7580_db1.ftsy_tabelle_2020 tab_akt
					LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab_before
						ON tab_before.player_id = tab_akt.player_id 
						   AND tab_before.spieltag = '$aktueller_spieltag'-1 
					SET tab_akt.updown = CASE WHEN (tab_akt.rang > tab_before.rang) THEN '&#9660;' WHEN (tab_akt.rang < tab_before.rang) THEN '&#9650;' ELSE '-' END
					WHERE tab_akt.spieltag = '$aktueller_spieltag'
				");
	
	}
	//Update Waiver
	mysqli_query($con, "UPDATE xa7580_db1.users_gamedata game
	                    SET game.waiver_position = (SELECT new_ranking FROM (
	                        SELECT tab.user_id, tab.waiver_position, tab.waiver_safe_flg, tab.rang, @curRank := @curRank + 1 AS new_ranking
	                        FROM (
	                            SELECT 	game.user_id
	                            		, game.waiver_position
	                            		, game.waiver_safe_flg
	                            		, tab.rang
	                            FROM xa7580_db1.users_gamedata game
	                            INNER JOIN xa7580_db1.ftsy_tabelle_2020 tab
	                                ON 	game.user_id = tab.player_id
	                            		AND tab.spieltag = (SELECT max(spieltag) FROM xa7580_db1.ftsy_tabelle_2020)
	                            ) tab
	                            , (SELECT @curRank := 0) r
	                        ORDER BY rang DESC
	                    ) tmp 
	                    WHERE game.user_id = tmp.user_id )");

//}


//Update Spieltag-Parameter
mysqli_query($con, "UPDATE xa7580_db1.parameter SET spieltag = '".$aktueller_spieltag."' + 1");

//Update Waivers
mysqli_query($con, "UPDATE xa7580_db1.parameter par
                    SET par.waiver_date_1 = (SELECT waiver_date_1 FROM xa7580_db1.parameter_next),
                    	par.waiver_date_2 = (SELECT waiver_date_2 FROM xa7580_db1.parameter_next)
            ");

//Update Waivers-next
mysqli_query($con, "UPDATE xa7580_db1.parameter_next nxt
                    SET nxt.waiver_date_1 = DATE_ADD(nxt.waiver_date_1, INTERVAL 7 DAY),
                    	nxt.waiver_date_2 = DATE_ADD(nxt.waiver_date_2, INTERVAL 7 DAY)
            ");

//Allowed Points
mysqli_query($con," 	DROP TABLE xa7580_db1.ftsy_points_allowed ");
mysqli_query($con,"		CREATE TABLE xa7580_db1.ftsy_points_allowed as 

							SELECT  base.position_short
								, base.opp_team_id
							        , base.team_name
							        , base.team_code
							        , base.sum_allowed
							        , base.avg_allowed
								, case 	when base.position_short = 'AW' then @rank_aw := @rank_aw + 1 
							        	when base.position_short = 'MF' then @rank_mf := @rank_mf + 1  
							                when base.position_short = 'TW' then @rank_tw := @rank_tw + 1  
							                when base.position_short = 'ST' then @rank_st := @rank_st + 1  
							                end as rank 
							FROM (
							    SELECT  hst.position_short
							            , hst.opp_team_id
							            , hst.opp_team_name as team_name
							            , hst.opp_team_code as team_code
							            , sum(ftsy_score) as sum_allowed
							            , round(avg(ftsy_score),1) as avg_allowed

							    FROM 	xa7580_db1.ftsy_scoring_hist hst

							    WHERE 	hst.season_id = (SELECT season_id from parameter)
							            	and hst.round_name < (SELECT spieltag from parameter)
							            	and hst.minutes_played_stat >= 80

							    group by 	hst.position_short
							                , hst.opp_team_id
							                , hst.opp_team_name 
							                , hst.opp_team_code

							    order by hst.position_short
							             , avg_allowed asc
							) base, (SELECT @rank_aw := 0) r_aw, (SELECT @rank_tw := 0) r_tw, (SELECT @rank_mf := 0) r_mf, (SELECT @rank_st := 0) r_st
	");

//Create News
$story = <<<EOT
Spieltag $aktueller_spieltag abgeschlossen!
EOT;
$headline = 'Spieltag abgeschlossen';
mysqli_query($con, "INSERT INTO xa7580_db1.news(name, headline, story, `timestamp`, add_id, drop_id, add_besitzer, drop_besitzer, type) VALUES('System', '".utf8_decode($headline)."', '".utf8_decode($story)."', NOW(), '', '','','','spieltag_abschluss' )");
?>
