UPDATE xa7580_db1.ftsy_schedule sch
LEFT JOIN (
    SELECT   
        own.1_ftsy_owner_id AS Besitzer1
        , SUM(COALESCE(akt.ftsy_score,0)) AS fantasy_score1
        , COUNT(own.player_id) AS anz1
    FROM ftsy_player_ownership own
    LEFT JOIN ftsy_scoring_akt_v akt
        ON own.player_id = akt.player_id
    WHERE   
        own.1_ftsy_owner_type = 'USR' 
        AND own.1_ftsy_match_status != 'NONE'
    GROUP BY own.1_ftsy_owner_id
    ) akt_score_1
    ON sch.ftsy_home_id = akt_score_1.Besitzer1
LEFT JOIN (
    SELECT   
        own.1_ftsy_owner_id AS Besitzer2
        , SUM(COALESCE(akt.ftsy_score,0)) AS fantasy_score2
        , COUNT(own.player_id) AS anz2
    FROM ftsy_player_ownership own
    LEFT JOIN ftsy_scoring_akt_v akt
        ON own.player_id = akt.player_id
    WHERE 
        own.1_ftsy_owner_type = 'USR' 
        AND own.1_ftsy_match_status != 'NONE'
    GROUP BY own.1_ftsy_owner_id
    ) akt_score_2
    ON sch.ftsy_away_id = akt_score_2.Besitzer2
SET 
    sch.ftsy_home_score = CASE WHEN anz1 = 11 THEN COALESCE(akt_score_1.fantasy_score1,0) ELSE -20 END
    , sch.ftsy_away_score = CASE WHEN anz2 = 11 THEN COALESCE(akt_score_2.fantasy_score2,0) ELSE -20 END
WHERE   
    sch.buli_round_name = (SELECT spieltag FROM parameter)
    AND season_id = (SELECT season_id FROM parameter)