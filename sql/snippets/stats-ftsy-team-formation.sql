FROM xa7580_db1.ftsy_scoring_hist hist

INNER JOIN xa7580_db1.users user 
	ON user.id = hist.1_ftsy_owner_id

INNER JOIN ( 
	SELECT buli_round_name
  FROM `ftsy_schedule`
  WHERE season_id = (SELECT season_id from parameter)
        AND match_type = 'league' 
  GROUP BY buli_round_name
  ) sch 
  ON sch.buli_round_name = hist.round_name
                           
WHERE hist.1_ftsy_match_status != 'NONE'
      AND hist.season_id = (SELECT season_id FROM parameter)
GROUP BY 	hist.1_ftsy_owner_id, user.teamname
ORDER BY kennzahl_1 DESC