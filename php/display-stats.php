<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get meta-data from session
$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Get stat data from js call
$stat_category = $_GET['stat'];

// Define position data from js call
if ($_GET['q'] != 'undefined' and isset($_GET['q'])){
	$get_player_position = $_GET['q'];
} else {
	$get_player_position = $_GET['ALL'];
}

if ($get_player_position == 'ALL'){
	$equal_sign = 'NOT IN';
} else  {
	$equal_sign = 'IN';	
}

/**************************/
/* GET DATA FROM MYSQL DB */
/**************************/

if ($stat_category == 'FANTASY-TEAMS'){

	// 1. Individual SQLs to get fantasy team stats

	$topscores = mysqli_query($con,"	
		SELECT 	'Top 20-Spieltags-Performances seit 2020/2021' as headline 
						, m.user_id as besitzer 
						, m.teamname
						, m.score as kennzahl_1 
						, concat(concat(concat(concat(s.season_name, ' Spieltag '), m.buli_round_name), ' vs. '), m.opp_teamname) as kennzahl_2
						, 0 as kennzahl_3
						, case when m.user_id = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM (
			SELECT 	season_id
			    		, buli_round_name
			    		, ftsy_home_id as user_id
			    		, ftsy_home_name as teamname
				   		, ftsy_home_score as score
				   		, ftsy_away_id as opp_user_id
				   		, ftsy_away_name as opp_teamname
				  		, ftsy_away_score as opp_score
						    
				FROM `ftsy_schedule`
											    
				UNION ALL 
											    
				SELECT 	season_id
				    		, buli_round_name
				    		, ftsy_away_id as user_id
				    		, ftsy_away_name as teamname
				    		, ftsy_away_score as score
				    		, ftsy_home_id as opp_user_id
				    		, ftsy_home_name as opp_teamname
				    		, ftsy_home_score as opp_score
								    
				FROM `ftsy_schedule`
			) m 

		INNER JOIN sm_seasons s
			ON m.season_id = s.season_id
										    
		ORDER BY score desc 
		LIMIT 20
	");

	$tabelle = mysqli_query($con,"	
		SELECT 'Ewige Tabelle seit 2018/2019' as headline 
						, user_id as besitzer 
						, team_name as teamname
						, sum_punkte as kennzahl_1 
						, concat(concat(concat(concat(anz_siege, '-'),anz_unentschieden), '-'), anz_niederlagen) as kennzahl_2
						, concat(concat(concat(anz_spiele, ' Ligaspiele, '), anz_saisons), ' Saison(s)') as kennzahl_3											
						, case when user_id = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM  	ftsy_ewige_tabelle_v
		ORDER BY sum_punkte desc, anz_siege desc, anz_unentschieden desc, sum_score_for desc
	");

	$meister = mysqli_query($con,"
		SELECT 'Fantasy-Bundesliga Meister seit 2018/2019' as headline 
						, player_id as besitzer 
						, team_name as teamname
						, season_name as kennzahl_1 
						, concat(concat(concat(concat(siege, '-'), unentschieden), '-'), niederlagen) as kennzahl_2
						, concat(punkte, ' Punkte') as kennzahl_3
						, case when player_id = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM  	ftsy_meister_v
		ORDER BY season_id desc
	");

	$pokal = mysqli_query($con,"
		SELECT 	'Fantasy-Bundesliga Pokalsieger seit 2020/2021' as headline 
						, winner_user_id as besitzer 
						, winner_team_name as teamname
						, season_name as kennzahl_1 
						, concat(concat(concat(concat(winner_score, ' - '), looser_score), ' vs. '), looser_team_name) as kennzahl_2
						, 0 as kennzahl_3
						, case when winner_user_id = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM  	ftsy_pokalsieger_v
		ORDER BY season_id desc
	");

	// Types of games

	$most_blowout_wins = mysqli_query($con,"
		SELECT 	'Meiste Blowout-Wins (50+ Punkte)' as headline 
						, case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end as besitzer
						, case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_name else sch.ftsy_away_name end as teamname
						, count(*) as kennzahl_1 
						, concat(cast(sum(case when sch.season_id = pa.season_id then 1 else 0 end) as char), ' diese Saison') as kennzahl_2
						, 0 as kennzahl_3
						, case when case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM 		ftsy_schedule sch

		LEFT JOIN parameter pa 
    	ON pa.season_id = sch.season_id
		
		WHERE 	sch.ftsy_home_score != -20 AND sch.ftsy_away_score != -20
    				AND (ABS(sch.ftsy_home_score - sch.ftsy_away_score) >= 50)

    GROUP BY headline, besitzer, teamname, highlight_flg
		ORDER BY kennzahl_1 desc
	");

	$most_blowout_losses = mysqli_query($con,"
		SELECT 	'Meiste Blowout-Losses (50+ Punkte)' as headline 
						, case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end as besitzer
						, case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_name else sch.ftsy_away_name end as teamname
						, count(*) as kennzahl_1 
						, concat(cast(sum(case when sch.season_id = pa.season_id then 1 else 0 end) as char), ' diese Saison') as kennzahl_2
						, 0 as kennzahl_3
						, case when case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM 		ftsy_schedule sch

		LEFT JOIN parameter pa 
    	ON pa.season_id = sch.season_id
		
		WHERE 	sch.ftsy_home_score != -20 AND sch.ftsy_away_score != -20
    				AND (ABS(sch.ftsy_home_score - sch.ftsy_away_score) >= 50)

    GROUP BY headline, besitzer, teamname, highlight_flg
		ORDER BY kennzahl_1 desc
	");

	$most_close_wins = mysqli_query($con,"
		SELECT 	'Meiste Nailbaiter-Wins (<= 10 Punkte)' as headline 
						, case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end as besitzer
						, case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_name else sch.ftsy_away_name end as teamname
						, count(*) as kennzahl_1 
						, concat(cast(sum(case when sch.season_id = pa.season_id then 1 else 0 end) as char), ' diese Saison') as kennzahl_2
						, 0 as kennzahl_3
						, case when case when sch.ftsy_home_score > sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM 		ftsy_schedule sch

		LEFT JOIN parameter pa 
    	ON pa.season_id = sch.season_id
		
		WHERE 	sch.ftsy_home_score != -20 AND sch.ftsy_away_score != -20
						AND sch.ftsy_home_score != 0 AND sch.ftsy_away_score != 0
    				AND (ABS(sch.ftsy_home_score - sch.ftsy_away_score) <= 10)

    GROUP BY headline, besitzer, teamname, highlight_flg
		ORDER BY kennzahl_1 desc
	");

	$most_close_losses = mysqli_query($con,"
		SELECT 	'Meiste Nailbaiter-Losses (<= 10 Punkte)' as headline 
						, case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end as besitzer
						, case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_name else sch.ftsy_away_name end as teamname
						, count(*) as kennzahl_1 
						, concat(cast(sum(case when sch.season_id = pa.season_id then 1 else 0 end) as char), ' diese Saison') as kennzahl_2
						, 0 as kennzahl_3
						, case when case when sch.ftsy_home_score < sch.ftsy_away_score then sch.ftsy_home_id else sch.ftsy_away_id end = '".$user_id."' then 1 else 0 end as highlight_flg

		FROM 		ftsy_schedule sch

		LEFT JOIN parameter pa 
    	ON pa.season_id = sch.season_id
		
		WHERE 	sch.ftsy_home_score != -20 AND sch.ftsy_away_score != -20
						AND sch.ftsy_home_score != 0 AND sch.ftsy_away_score != 0
    				AND (ABS(sch.ftsy_home_score - sch.ftsy_away_score) <= 10)

    GROUP BY headline, besitzer, teamname, highlight_flg
		ORDER BY kennzahl_1 desc
	");

	// Get SQL body snippet
	
	$sql_body = file_get_contents('../sql/snippets/stats-ftsy-team-formation.sql');

	$starter = mysqli_query($con,"
		SELECT 	'Verschiedene Spieler aufgestellt (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, count(distinct hist.player_id) as kennzahl_1 
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);
	
	$nicht_gespielt = mysqli_query($con,"	
		SELECT 	'Nicht eingesetzte Spieler aufgestellt (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(case when hist.appearance_stat = 0 then 1 else 0 end) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);	

	$minutes = mysqli_query($con,"	
		SELECT 	'Spielminuten aufgestellter Spieler (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.minutes_played_stat,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);	

	$tore = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Toren (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.goals_minus_pen_ftsy,0) + coalesce(hist.pen_scored_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$vorlagen = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Assists (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.assists_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);
	

	$redcard = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Platzverweisen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.redcards_ftsy,0) + coalesce(hist.redyellowards_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$dribbling = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Dribbling-Versuchen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.dribbles_success_ftsy,0) + coalesce(hist.dribbles_failed_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);	

	$duelle = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Duellen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.duels_won_ftsy,0) + coalesce(hist.duels_lost_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$schuesse = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Torschüssen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.shots_total_ftsy,0) + coalesce(hist.shots_on_goal_ftsy,0) + coalesce(hist.shots_missed_ftsy,0) + coalesce(hist.shots_blocked_ftsy,0) + coalesce(hist.hit_woodwork_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$flanken = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Flanken (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.crosses_total_ftsy,0) + coalesce(hist.crosses_complete_ftsy,0) + coalesce(hist.crosses_incomplete_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$paesse = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Pässen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.passes_total_ftsy,0) + coalesce(hist.passes_complete_ftsy,0) + coalesce(hist.passes_incomplete_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$big_chances = mysqli_query($con,"	SELECT 	
		'Fantasy-Punkte aus kreierten Großchancen (Liga)' as headline
		, hist.1_ftsy_owner_id as besitzer
		, user.teamname
		, sum(coalesce(hist.big_chances_created_ftsy,0)) as kennzahl_1
		, 0 as kennzahl_2
		, 0 as kennzahl_3
		, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);	


	$key_paesse = mysqli_query($con,"	SELECT 	
		'Fantasy-Punkte aus Schlüsselpässen (Liga)' as headline
		, hist.1_ftsy_owner_id as besitzer
		, user.teamname
		, sum(coalesce(hist.key_passes_ftsy,0)) as kennzahl_1
		, 0 as kennzahl_2
		, 0 as kennzahl_3
		, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$blocks = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Blocks (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.blocks_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$clear = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus geklärten Bällen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.clearances_ftsy,0) + coalesce(hist.clearances_offline_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$dis = mysqli_query($con,"
		SELECT 	'Fantasy-Punkte aus Ballverlusten (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.dispossessed_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$ints = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus abgefangenen Bällen (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.interceptions_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$tackles = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Tacklings (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.tackles_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$torwart = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Torwarspiel (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(coalesce(hist.inside_box_saves_ftsy,0) + coalesce(hist.outside_box_saves_ftsy,0) + coalesce(hist.saves_ftsy,0) + coalesce(hist.pen_saved_ftsy,0) + coalesce(hist.punches_ftsy,0)) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);


	$abschluesse = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Abschlüssen gesamt (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(
							coalesce(hist.goals_minus_pen_ftsy,0) + coalesce(hist.pen_scored_ftsy,0)
							+ coalesce(hist.shots_total_ftsy,0) + coalesce(hist.shots_missed_ftsy,0) + coalesce(hist.shots_on_goal_ftsy,0) + coalesce(hist.shots_blocked_ftsy,0)
							+ coalesce(hist.pen_missed_ftsy,0) + coalesce(hist.hit_woodwork_ftsy,0) + coalesce(hist.big_chances_missed_ftsy,0)
							) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);
	
	$passspiel = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Passpiel gesamt (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(
							coalesce(hist.assists_ftsy,0)
							+ coalesce(hist.crosses_total_ftsy,0) + coalesce(hist.crosses_complete_ftsy,0) + coalesce(hist.crosses_incomplete_ftsy,0)
							+ coalesce(hist.passes_total_ftsy,0) + coalesce(hist.passes_complete_ftsy,0) + coalesce(hist.passes_incomplete_ftsy,0)
							+ coalesce(hist.key_passes_ftsy,0) + coalesce(hist.big_chances_created_ftsy,0)
							) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$zweikampf = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Zweikämpfen gesamt (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(
							coalesce(hist.dribble_attempts_ftsy,0) + coalesce(hist.dribbles_success_ftsy,0) + coalesce(hist.dribbles_failed_ftsy,0)
							+ coalesce(hist.dribbled_past_ftsy,0)
							+ coalesce(hist.duels_total_ftsy,0) + coalesce(hist.duels_won_ftsy,0) + coalesce(hist.duels_lost_ftsy,0)
							+ coalesce(hist.blocks_ftsy,0)
							+ coalesce(hist.clearances_ftsy,0) + coalesce(hist.clearances_offline_ftsy,0)
							+ coalesce(hist.dispossessed_ftsy,0)
							+ coalesce(hist.interceptions_ftsy,0)
							+ coalesce(hist.tackles_ftsy,0)
							+ coalesce(hist.pen_committed_ftsy,0)
							+ coalesce(hist.pen_won_ftsy,0)
							+ coalesce(hist.punches_ftsy,0)
							+ coalesce(hist.error_lead_to_goal_ftsy,0)
							) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);

	$gegentore = mysqli_query($con,"	
		SELECT 	'Fantasy-Punkte aus Gegentoren (Liga)' as headline
						, hist.1_ftsy_owner_id as besitzer
						, user.teamname
						, sum(
							coalesce(hist.goals_conceded_ftsy,0)
							+ coalesce(hist.goalkeeper_goals_conceded_ftsy,0)
							+ coalesce(hist.clean_sheet_ftsy,0)
							) as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3
						, case when hist.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		" . " " . $sql_body
	);
	

	// Collect all queries in an array
	$stat_array = array($topscores, $tabelle, $meister, $pokal, $most_blowout_wins, $most_blowout_losses, $most_close_wins, $most_blowout_losses, $starter, $nicht_gespielt, $minutes, $tore, $vorlagen, $abschluesse, $passspiel, $zweikampf, $torwart, $gegentore, $duelle, $schuesse, $paesse, $big_chances, $key_paesse, $flanken, $dribbling, $ints, $tackles, $blocks, $clear, $dis, $redcard);

} elseif ($stat_category == 'BUNDESLIGA-TEAMS') {
	
	// 2. Individual SQLs for Bundesliga teams fantasy stats

	$fantasy_score_allowed_tw = mysqli_query($con,"	
		SELECT 	'Avg. zugelassene Fantasy-Punkte: Tor' as headline
						, team_name as verein
						, avg_allowed as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3

		FROM xa7580_db1.ftsy_points_allowed 
		WHERE position_short = 'TW'
		order by kennzahl_1 desc
	");	
	
	$fantasy_score_allowed_aw = mysqli_query($con,"	
		SELECT 	'Avg. zugelassene Fantasy-Punkte: Abwehr' as headline
						, team_name as verein
						, avg_allowed as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3

		FROM xa7580_db1.ftsy_points_allowed 
		WHERE position_short = 'AW'
		order by kennzahl_1 desc
	");	

	$fantasy_score_allowed_mf = mysqli_query($con,"	
		SELECT 	'Avg. zugelassene Fantasy-Punkte: Mittelfeld' as headline
						, team_name as verein
						, avg_allowed as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3

		FROM xa7580_db1.ftsy_points_allowed 
		WHERE position_short = 'MF'
		order by kennzahl_1 desc
	");	

	$fantasy_score_allowed_st = mysqli_query($con,"	
		SELECT 	'Avg. zugelassene Fantasy-Punkte: Sturm' as headline
						, team_name as verein
						, avg_allowed as kennzahl_1
						, 0 as kennzahl_2
						, 0 as kennzahl_3

		FROM xa7580_db1.ftsy_points_allowed 
		WHERE position_short = 'ST'
		order by kennzahl_1 desc
	");	

	// Collect all queries in an array
	$stat_array = array($fantasy_score_allowed_st, $fantasy_score_allowed_mf, $fantasy_score_allowed_aw, $fantasy_score_allowed_tw);

} elseif ($stat_category == 'SPIELER') {

	// 3. Bundesliga player stats

	// Define stats that should be summed up

	$stats_to_iterate=array(
		
		// ftsy-score
		'ftsy_score_sum'
		,'ftsy_score_avg'
		,'ftsy_score_avg_last_5'
		, 'ftsy_score_avg_last_3'
		, 'ftsy_score_last'

		// goals and shots
		, 'goals_total_stat_sum'
		, 'pen_scored_stat_sum'
		, 'shots_total_stat_sum'
		, 'hit_woodwork_stat_sum'

		// assists and passing
		, 'assists_stat_sum'
		, 'big_chances_created_stat_sum'
		, 'pen_won_stat_sum'
		, 'key_passes_stat_sum'
		, 'passes_complete_stat_sum'
		, 'passes_accuracy_stat_avg'
		, 'crosses_complete_stat_sum'

		// duels
		, 'duels_won_stat_sum'
		, 'dribbles_success_stat_sum'
		, 'tackles_stat_sum'
		, 'blocks_stat_sum'
		, 'clearances_stat_sum'
		, 'clearances_offline_stat_sum'
		, 'interceptions_stat_sum'

		// errors
		, 'error_lead_to_goal_stat_sum'
		, 'owngoals_stat_sum'
		, 'pen_committed_stat_sum'
		, 'redcards_stat_sum'
		, 'redyellowcards_stat_sum'
		, 'duels_lost_stat_sum'
		, 'dispossessed_stat_sum'
		, 'dribbled_past_stat_sum'
		, 'pen_missed_stat_sum'
		, 'big_chances_missed_stat_sum'
		, 'passes_incomplete_stat_sum'

		// goalkeeping
		, 'saves_stat_sum' 
		, 'inside_box_saves_stat_sum'
		, 'outside_box_saves_stat_sum'
		, 'pen_saved_stat_sum'
		, 'punches_stat_sum'

		// misc
		, 'goals_conceded_stat_sum'
		, 'clean_sheet_stat_sum'
		, 'appearance_stat_sum'
		, 'minutes_played_stat_sum'
	);

	$headline_array=array(
		// ftsy-score
		'Punkte Saison','Schnitt Saison','Schnitt Last 5','Schnitt Last 3', 'Letztes Spiel'

		// goals and shooting
		, 'Tore Gesamt','Elfmeter-Tore', 'Torschüsse', 'Pfosten'

		// assists and passing
		, 'Assists', 'Großchancen kreiert', 'Elfmeter rausgeholt', 'Key-Pässe', 'Angekommene Pässe', 'Passgenauigkeit', 'Angekommene Flanken'

		# duels
		, 'Gewonnene Duelle', 'Erfolgreiche Dribblings', 'Tacklings', 'Schüsse geblockt', 'Bälle geklärt', 'Bälle auf der Linie geklärt','Abgefangene Bälle'

		// errors
		, 'Gegentore verursacht', 'Eigentore', 'Elfmeter verursacht', 'Rote Karten', 'Gelb-Rote Karten', 'Duelle verloren', 'Ballverluste', 'Ausgedribbelt worden', 'Elfmeter verschossen', 'Großchancen vergeben', 'Fehlpässe'

		// goalkeeping
		, 'Gehaltene Schüsse', 'Gehaltene Schüsse (im 16er)', 'Gehaltene Fernschüsse', 'Elfmeter gehalten', 'Weggefaustete Bälle'

		// misc
		, 'Gegentore'
		, 'Weiße Westen'
		, 'Einsätze'
		, 'Gespielte Minuten'
	);

	// iterate and construct SQL

	foreach ($stats_to_iterate as $index => $element) {

		$headline_here = $headline_array[$index];
		$top_fantasy = 	mysqli_query($con,"		
			SELECT 	'".strval($headline_here)."' as headline
							, owr.1_ftsy_owner_id as besitzer
							, snap.display_name as name
		          , case when owr.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
		          , case when owr.1_ftsy_owner_type in ('WVR', 'FA') then 1 else 0 end as vereinslos_flg
							, ".$element." as kennzahl_1
							, 0 as kennzahl_2
							, 0 as kennzahl_3
			FROM xa7580_db1.ftsy_scoring_snap snap
			LEFT JOIN xa7580_db1.ftsy_player_ownership owr
				ON owr.player_id = snap.id
			WHERE snap.position_short $equal_sign ('".$get_player_position."')
      ORDER BY kennzahl_1 desc
			LIMIT 10
		");	

		echo "<div class='stat_wrapper'>";
			echo "<div class='stat_body'>";	
				echo "<div class='stat_head'>";
					$params = mysqli_fetch_array($top_fantasy);
					$headline = mb_convert_encoding($params['headline'], 'UTF-8');

					$kennzahl_2_flg = $params['kennzahl_2'];
					$kennzahl_3_flg = $params['kennzahl_3'];

					echo $headline;
					mysqli_data_seek($top_fantasy, 0);
				echo "</div>";
			
				echo "<div class='stat_table'>";

					while($row = mysqli_fetch_array($top_fantasy)) {

						if ($row['highlight_flg'] == 1){
							echo "<div class='stat_tr my_team'>";
						} elseif ($row['vereinslos_flg'] == 1) {
							echo "<div class='stat_tr no_team' title='Spieler ist aktuell Free Agent bzw. im Waiver.' style='cursor: help;'>";
						} else {
							echo "<div class='stat_tr'>";
						}

								echo "<div class='stat_td'>";
									echo mb_convert_encoding($row['name'], 'UTF-8');
								echo "</div>";
								echo "<div class='stat_td'>";
									echo utf8_encode($row['kennzahl_1']);
								echo "</div>";

								if ($kennzahl_2_flg != '0') {
								echo "<div class='stat_td detail_info'>";
									echo utf8_encode($row['kennzahl_2']);
								echo "</div>";
								}
							echo "</div>";
					}
				echo "</div>";
			echo "</div>";
		echo "</div>";

	}

	$stat_array = array($top_fantasy);

} elseif ($stat_category == 'TOP-PERFORMANCES') {

	// Forever
	
	$top_forever = 	mysqli_query($con,"		

			SELECT 	'All-Time Fantasy-Punkte' as headline
							, display_name as name 
							, year(min(kickoff_dt)) as min_jahr
			        , year(max(kickoff_dt)) as max_jahr
							, sum(ftsy_score) as kennzahl_1
			        , count(ftsy_score) as kennzahl_2
			        , sum(case when 1_ftsy_owner_id = '".$user_id."' then 1 else 0 end) as highlight_flg
			        
			FROM `ftsy_scoring_hist`
			WHERE position_short $equal_sign ('".$get_player_position."')
			GROUP BY display_name
			ORDER BY sum(ftsy_score) DESC		
			LIMIT 15;
		");	

	echo "<div class='stat_wrapper'>";
		echo "<div class='stat_body'>";	
			echo "<div class='stat_head'>";
				$params = mysqli_fetch_array($top_forever);
				$headline = mb_convert_encoding($params['headline'], 'UTF-8');
				$kennzahl_2_flg = $params['kennzahl_2'];
				echo $headline;
				mysqli_data_seek($top_forever, 0);
			echo "</div>";
		
			echo "<div class='stat_table'>";

				while($row = mysqli_fetch_array($top_forever)) {

					if ($row['highlight_flg'] > 0) {
						echo "<div class='stat_tr my_team'>";
					} elseif ($row['vereinslos_flg'] == 1) {
						echo "<div class='stat_tr no_team' title='Spieler ist aktuell Free Agent bzw. im Waiver.' style='cursor: help;'>";
					} else {
						echo "<div class='stat_tr'>";
					}

							echo "<div class='stat_td'>";
								echo mb_convert_encoding($row['name'], 'UTF-8');
							echo "</div>";
							echo "<div class='stat_td'>";
								echo utf8_encode($row['kennzahl_1']);
							echo "</div>";

							if ($kennzahl_2_flg != '0') {
							echo "<div class='stat_td detail_info'>";
								echo utf8_encode($row['kennzahl_2'] . ' Spiele von ' . $row['min_jahr'] . ' bis ' . $row['max_jahr']);
							echo "</div>";
							}
						echo "</div>";
				}
			echo "</div>";
		echo "</div>";
	echo "</div>";


	$stat_array = array($top_forver);	
	
	// Season

	$top_season = 	mysqli_query($con,"		

			SELECT 	'Saison-Rekorde Fantasy-Punkte' as headline
							, display_name as name 
							, season_name
							, sum(ftsy_score) as kennzahl_1
			        , sum(case when 1_ftsy_owner_id = '".$user_id."' then 1 else 0 end) as highlight_flg
			        , group_concat(1_ftsy_owner_id) as kennzahl_2
			        
			FROM `ftsy_scoring_hist`
			WHERE position_short $equal_sign ('".$get_player_position."')
			GROUP BY display_name, season_name
			ORDER BY sum(ftsy_score) DESC		
			LIMIT 15;
		");	

	echo "<div class='stat_wrapper'>";
		echo "<div class='stat_body'>";	
			echo "<div class='stat_head'>";
				$params = mysqli_fetch_array($top_season);
				$headline = mb_convert_encoding($params['headline'], 'UTF-8');
				$kennzahl_2_flg = $params['kennzahl_2'];
				$kennzahl_3_flg = $params['kennzahl_3'];
				echo $headline;
				mysqli_data_seek($top_season, 0);
			echo "</div>";
		
			echo "<div class='stat_table'>";

				while($row = mysqli_fetch_array($top_season)) {

					if ($row['highlight_flg'] > 0) {
						echo "<div class='stat_tr my_team'>";
					} else {
						echo "<div class='stat_tr'>";
					}

							echo "<div class='stat_td'>";
								echo mb_convert_encoding($row['name'], 'UTF-8');
							echo "</div>";
							echo "<div class='stat_td'>";
								echo utf8_encode($row['kennzahl_1']);
							echo "</div>";

							if ($kennzahl_2_flg != '0') {
							echo "<div class='stat_td detail_info'>";
								echo utf8_encode('Saison ' . $row['season_name']);
							echo "</div>";
							}

							if ($kennzahl_3_flg != '0') {
							echo "<div class='stat_td detail_info'>";
								echo utf8_encode($row['kennzahl_3']);
							echo "</div>";
							}
						
						echo "</div>";
				}
			echo "</div>";
		echo "</div>";
	echo "</div>";

	$stat_array = array_merge($stat_array,array($top_season));

	// Per Game

	$stats_to_iterate=array('ftsy_score', 'goals_total_stat','assists_stat', 'goals_total_stat+assists_stat', 'shots_total_stat', 'key_passes_stat', 'passes_complete_stat', 'crosses_total_stat', 'crosses_complete_stat', 'duels_total_stat', 'duels_won_stat', 'dribbles_success_stat', 'blocks_stat', 'clearances_stat','interceptions_stat','tackles_stat','saves_stat', 'passes_incomplete_stat', 'duels_lost_stat', 'dispossessed_stat', 'dribbled_past_stat');

	$headline_array=array('Spiel-Rekorde Fantasy-Punkte','Tore in einem Spiel','Assists in einem Spiel', 'Scorer-Punkte in einem Spiel', 'Torschüsse in einem Spiel', 'Schlüsselpässe in einem Spiel', 'Angekommene Pässe in einem Spiel', 'Flankenversuche in einem Spiel', 'Angekommene Flanken in einem Spiel', 'Duelle in einem Spiel', 'Gewonnene Duelle in einem Spiel', 'Erfolgreiche Dribblings in einem Spiel', 'Geblockte Schüsse in einem Spiel', 'Geklärte Bälle in einem Spiel', 'Abgefangene Bälle in einem Spiel', 'Tacklings in einem Spiel','Gehaltene Schüsse in einem Spiel', 'Fehlpässe in einem Spiel','Verlorene Duelle in einem Spiel', 'Ballverluste in einem Spiel', 'Ausgedribbelt worden in einem Spiel');

	foreach ($stats_to_iterate as $index => $element) {

		$headline_here = $headline_array[$index];

		$top_fantasy = 	mysqli_query($con,"
			SELECT 	'".strval($headline_here)."' as headline
							, hst.display_name as name
	        		, case when hst.1_ftsy_owner_id = '".$user_id."' then 1 else 0 end as highlight_flg
	        		, case when hst.1_ftsy_owner_type in ('WVR', 'FA') then 0 else 0 end as vereinslos_flg
							, ".$element." as kennzahl_1
							, concat(concat(concat(concat(concat(concat(hst.season_name, ' Spieltag '), hst.round_name), ': '), hst.team_code), ' vs. '), hst.opp_team_code) as kennzahl_2
							, u.teamname as kennzahl_3
			FROM xa7580_db1.ftsy_scoring_hist hst 
			LEFT JOIN xa7580_db1.users u  
				ON u.id = hst.1_ftsy_owner_id
			WHERE hst.position_short $equal_sign ('".$get_player_position."')
			ORDER BY kennzahl_1 desc, ftsy_score desc
			LIMIT 15
		");	

		echo "<div class='stat_wrapper'>";
			echo "<div class='stat_body'>";	
				echo "<div class='stat_head'>";
					$params = mysqli_fetch_array($top_fantasy);
					$headline = mb_convert_encoding($params['headline'], 'UTF-8');

					$kennzahl_2_flg = $params['kennzahl_2'];
					$kennzahl_3_flg = $params['kennzahl_3'];

					echo $headline;
					mysqli_data_seek($top_fantasy, 0);
				echo "</div>";
			
				echo "<div class='stat_table'>";

					while($row = mysqli_fetch_array($top_fantasy)) {

						if ($row['highlight_flg'] == 1){
							echo "<div class='stat_tr my_team'>";
						} elseif ($row['vereinslos_flg'] == 1) {
							echo "<div class='stat_tr no_team' title='Spieler ist aktuell Free Agent bzw. im Waiver.' style='cursor: help;'>";
						} else {
							echo "<div class='stat_tr'>";
						}

								echo "<div class='stat_td'>";
									echo mb_convert_encoding($row['name'], 'UTF-8');
								echo "</div>";
								echo "<div class='stat_td'>";
									echo utf8_encode($row['kennzahl_1']);
								echo "</div>";

								if ($kennzahl_2_flg != '0') {
								echo "<div class='stat_td detail_info'>";
									echo utf8_encode($row['kennzahl_2']);
								echo "</div>";
								}

								if ($kennzahl_3_flg != '0') {
								echo "<div class='stat_td detail_info'>";
									echo utf8_encode($row['kennzahl_3']);
								echo "</div>";
								}
							echo "</div>";
					}
				echo "</div>";
			echo "</div>";
		echo "</div>";

	}

	$stat_array = array_merge($stat_array,array($top_fantasy));
}

/********************/
/* DISPLAY THE DATA */
/********************/

if ($stat_category == 'FANTASY-TEAMS'){

	// 1. Fantasy teams

	foreach ($stat_array as $element) {

		echo "<div class='stat_wrapper'>";
			echo "<div class='stat_body'>";
					echo "<div class='stat_head'>";
						$params = mysqli_fetch_assoc($element);
						$headline = mb_convert_encoding($params['headline'], 'UTF-8');

						$kennzahl_2_flg = $params['kennzahl_2'];
						$kennzahl_3_flg = $params['kennzahl_3'];

						echo $headline;
						mysqli_data_seek($element, 0);
					echo "</div>";
				echo "<div class='stat_table'>";

					while($row = mysqli_fetch_array($element)) {

						if ($row['highlight_flg'] == 1){
							echo "<div class='stat_tr my_team'>";
						} else {
							echo "<div class='stat_tr'>";
						}

						echo "<div class='stat_td'>";
							echo mb_convert_encoding($row['teamname'], 'UTF-8');
						echo "</div>";

						echo "<div class='stat_td'>";
							echo mb_convert_encoding($row['kennzahl_1'], 'UTF-8');
						echo "</div>";

						if ($kennzahl_2_flg != '0' ) {
							echo "<div class='stat_td detail_info'>";
								echo mb_convert_encoding($row['kennzahl_2'], 'UTF-8');
							echo "</div>";
						}

						if ($kennzahl_3_flg != '0' ) {
							echo "<div class='stat_td detail_info'>";
								echo mb_convert_encoding($row['kennzahl_3'], 'UTF-8');
							echo "</div>";
						}

						echo "</div>";
					}
				echo "</div>";
			echo "</div>";
		echo "</div>";
	}

} elseif ($stat_category == 'BUNDESLIGA-TEAMS') {

	// 2. Bundesliga teams

	foreach ($stat_array as $element) {

		echo "<div class='stat_wrapper'>";

			$kennzahl_2_flg = $params['kennzahl_2'];
			$kennzahl_3_flg = $params['kennzahl_3'];
		
			echo "<div class='stat_body'>";
				
				echo "<div class='stat_head'>";
					$params = mysqli_fetch_assoc($element);
					$headline = mb_convert_encoding($params['headline'], 'UTF-8');
					echo $headline;
					mysqli_data_seek($element, 0);
				echo "</div>";
				
				echo "<div class='stat_table'>";

					while($row = mysqli_fetch_array($element)) {

							echo "<div class='stat_tr'>";
						
								echo "<div class='stat_td'>";
									echo mb_convert_encoding($row['verein'], 'UTF-8');
								echo "</div>";

								echo "<div class='stat_td'>";
									echo mb_convert_encoding($row['kennzahl_1'], 'UTF-8');
								echo "</div>";

							echo "</div>";
					}
				echo "</div>";
			echo "</div>";
		echo "</div>";
	}

} elseif ($stat_category == 'SPIELER') {
	// Legacy

}
?> 