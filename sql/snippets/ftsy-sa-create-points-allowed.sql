CREATE TABLE xa7580_db1.ftsy_points_allowed AS 

SELECT  base.position_short
				, base.opp_team_id
				, base.team_name
				, base.team_code
				, base.sum_allowed
				, base.avg_allowed
				-- Calculate rank by position
				, CASE 	WHEN base.position_short = 'AW' THEN @rank_aw := @rank_aw + 1 
								WHEN base.position_short = 'MF' THEN @rank_mf := @rank_mf + 1  
								WHEN base.position_short = 'TW' THEN @rank_tw := @rank_tw + 1  
								WHEN base.position_short = 'ST' THEN @rank_st := @rank_st + 1  
								END AS rank 
FROM (

	SELECT  hst.position_short
					, hst.opp_team_id
					, hst.opp_team_name AS team_name
					, hst.opp_team_code AS team_code
					, sum(ftsy_score) AS sum_allowed
					, round(avg(ftsy_score),1) AS avg_allowed

	FROM 	xa7580_db1.ftsy_scoring_hist hst

	WHERE 	hst.season_id = (SELECT season_id from parameter)
					AND hst.round_name < (SELECT spieltag from parameter)
					AND hst.minutes_played_stat >= 80 -- only include points of players with 80min playing time

	GROUP BY 	hst.position_short
						, hst.opp_team_id
						, hst.opp_team_name 
						, hst.opp_team_code

	ORDER BY 	hst.position_short
						, avg_allowed ASC

	) base, (SELECT @rank_aw := 0) r_aw, (SELECT @rank_tw := 0) r_tw, (SELECT @rank_mf := 0) r_mf, (SELECT @rank_st := 0) r_st