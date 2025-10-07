INSERT INTO xa7580_db1.ftsy_tabelle_2020 (
	season_id
	, league_id
	, spieltag
	, round_id
	, rang
	, player_id
	, team_name
	, score_for
	, score_against
	, differenz
	, avg_for
	, avg_against
	, siege
	, niederlagen
	, unentschieden
	, trost
	, punkte
	, h2h
	, serie
	, updown
	)

SELECT 	par.season_id as season_id
				, 1 as league_id
				, par.spieltag as spieltag
				, rnd.id as round_id
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

	SELECT 	finale_tabelle2.*, 
					# aus [6] 
					CASE WHEN last3.ftsy_home_id = finale_tabelle2.team_id THEN
							CASE WHEN last3.ftsy_home_score > last3.ftsy_away_score THEN 'S'
								 WHEN last3.ftsy_home_score < last3.ftsy_away_score THEN 'N'
								 WHEN last3.ftsy_home_score = last3.ftsy_away_score THEN 'U' END
						 WHEN last3.ftsy_away_id = finale_tabelle2.team_id THEN
							CASE WHEN last3.ftsy_away_score > last3.ftsy_home_score THEN 'S'
								 WHEN last3.ftsy_away_score < last3.ftsy_home_score THEN 'N'
								 WHEN last3.ftsy_away_score = last3.ftsy_home_score THEN 'U' END
						 ELSE 'B'
					END AS Last_3,
					CASE WHEN last2.ftsy_home_id = finale_tabelle2.team_id THEN
							CASE WHEN last2.ftsy_home_score > last2.ftsy_away_score THEN 'S'
								 WHEN last2.ftsy_home_score < last2.ftsy_away_score THEN 'N'
								 WHEN last2.ftsy_home_score = last2.ftsy_away_score THEN 'U' END
						 WHEN last2.ftsy_away_id = finale_tabelle2.team_id THEN
							CASE WHEN last2.ftsy_away_score > last2.ftsy_home_score THEN 'S'
								 WHEN last2.ftsy_away_score < last2.ftsy_home_score THEN 'N'
								 WHEN last2.ftsy_away_score = last2.ftsy_home_score THEN 'U' END
					 	 ELSE 'B'
					END AS Last_2,
					CASE WHEN last1.ftsy_home_id = finale_tabelle2.team_id THEN
							CASE WHEN last1.ftsy_home_score > last1.ftsy_away_score THEN 'S'
								 WHEN last1.ftsy_home_score < last1.ftsy_away_score THEN 'N'
								 WHEN last1.ftsy_home_score = last1.ftsy_away_score THEN 'U' END
						 WHEN last1.ftsy_away_id = finale_tabelle2.team_id THEN
							CASE WHEN last1.ftsy_away_score > last1.ftsy_home_score THEN 'S'
								 WHEN last1.ftsy_away_score < last1.ftsy_home_score THEN 'N'
								 WHEN last1.ftsy_away_score = last1.ftsy_home_score THEN 'U' END
					 	 ELSE 'B'
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
				,SUM(CASE WHEN team_id = ftsy_home_id THEN
							CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END
				  		 WHEN team_id = ftsy_away_id THEN
							CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END
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
							FROM 		xa7580_db1.ftsy_schedule
							WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and season_id = (SELECT season_id FROM parameter)
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
							FROM 		xa7580_db1.ftsy_schedule
							WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
											and season_id = (SELECT season_id FROM parameter)
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
								FROM 		xa7580_db1.ftsy_schedule
								WHERE 	ftsy_home_score < ftsy_away_score
												and match_type = 'league'
												and buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
												and season_id = (SELECT season_id FROM parameter)

								UNION ALL

								/* Suche alle Away-Teams die verloren haben */
								SELECT 	ftsy_away_name as team_name,
												ftsy_away_id as team_id,
												ftsy_away_score as score,
												buli_round_name as spieltag
								FROM 		xa7580_db1.ftsy_schedule
								WHERE 	ftsy_away_score < ftsy_home_score
												and match_type = 'league'
												and buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
												and season_id = (SELECT season_id FROM parameter)
							
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
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	ftsy_home_score < ftsy_away_score
													and buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)

									UNION ALL

									/* Suche alle Away-Teams die verloren haben */
									SELECT 	ftsy_away_name as team_name,
													ftsy_away_id as team_id,
													ftsy_away_score as score,
													buli_round_name as spieltag
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	ftsy_away_score < ftsy_home_score
													and buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)
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
					SELECT 	standard.team_id as team_id,
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
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)

									UNION ALL

									/* Suche alle Siege wenn das Team das Heim-Team ist*/
									SELECT 	ftsy_away_id as team_id, 
													ftsy_away_score as score_for, 
													ftsy_home_score as score_against, 
													ftsy_away_score-ftsy_home_score as differenz
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)
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
										and season_id = (SELECT season_id FROM parameter)
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
										and season_id = (SELECT season_id FROM parameter)
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
										and season_id = (SELECT season_id FROM parameter)

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
										and season_id = (SELECT season_id FROM parameter)
							
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
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	ftsy_home_score < ftsy_away_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)

									UNION ALL

									/* Suche alle Away-Teams die verloren haben */
									SELECT 	ftsy_away_name as team_name,
													ftsy_away_id as team_id,
													ftsy_away_score as score,
													buli_round_name as spieltag
									FROM 		xa7580_db1.ftsy_schedule
									WHERE 	ftsy_away_score < ftsy_home_score
													AND buli_round_name BETWEEN 1 AND (SELECT spieltag FROM parameter)
													and match_type = 'league'
													and season_id = (SELECT season_id FROM parameter)
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
				AND season_id = (SELECT season_id FROM parameter)
			
		) finale_tabelle

		GROUP BY team_name, team_id, games, s, n, u, t, score_for, score_against, differenz, avg_for, avg_against, points
		ORDER BY points DESC, h2h_tiebraker DESC, score_for DESC, score_against DESC
	) finale_tabelle2

	#[6] S,U,N aus der letzten 3 Spielen 
	LEFT JOIN xa7580_db1.ftsy_schedule last1
		ON (finale_tabelle2.team_id = last1.ftsy_home_id OR finale_tabelle2.team_id = last1.ftsy_away_id) AND last1.buli_round_name = (SELECT spieltag FROM parameter) and last1.season_id = (SELECT season_id FROM parameter)

	LEFT JOIN xa7580_db1.ftsy_schedule last2
		ON (finale_tabelle2.team_id = last2.ftsy_home_id OR finale_tabelle2.team_id = last2.ftsy_away_id) AND last2.buli_round_name = (SELECT spieltag FROM parameter)-1 and last2.season_id = (SELECT season_id FROM parameter)

	LEFT JOIN xa7580_db1.ftsy_schedule last3
		ON (finale_tabelle2.team_id = last3.ftsy_home_id OR finale_tabelle2.team_id = last3.ftsy_away_id) AND last3.buli_round_name = (SELECT spieltag FROM parameter)-2 and last3.season_id = (SELECT season_id FROM parameter)
	 
	LEFT JOIN xa7580_db1.users users 
	 	ON (finale_tabelle2.team_id = users.id)

	ORDER BY points DESC, h2h_tiebraker DESC, score_for DESC, score_against DESC 

) complete_table, (SELECT @curStanding:= 0) standing

INNER JOIN parameter par  
	ON 	1 = 1

INNER JOIN sm_rounds rnd 
	ON 	par.season_id = rnd.season_id
			AND par.spieltag = rnd.name

ORDER BY 	complete_table.points DESC
					, complete_table.h2h_tiebraker DESC
					, complete_table.S DESC
					, complete_table.score_for DESC
					, complete_table.score_against DESC
					, RAND ()