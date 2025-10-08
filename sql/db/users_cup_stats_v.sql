CREATE VIEW users_cup_stats_v AS
WITH cte_schedule AS (
    SELECT
        u.id AS user_id
        , u.teamname
        , p.season_id
        , p.spieltag as round_name
        , CASE WHEN u.id = sch.ftsy_home_id THEN sch.ftsy_home_score ELSE sch.ftsy_away_score END AS ftsy_score_for
        , CASE WHEN u.id = sch.ftsy_home_id THEN sch.ftsy_away_score ELSE sch.ftsy_home_score END AS ftsy_score_against
    FROM users u
    INNER JOIN parameter p
        ON 1 = 1
    LEFT JOIN ftsy_schedule sch 
        ON (u.id = sch.ftsy_home_id OR u.id = sch.ftsy_away_id )
        AND sch.match_type = 'cup'
        AND sch.ftsy_home_score IS NOT NULL
        AND sch.ftsy_away_score IS NOT NULL
        AND sch.season_id = p.season_id
        AND sch.buli_round_name < p.spieltag
    WHERE 
        u.active_account_flg = 1
        AND u.id NOT IN (2)
)
SELECT 
    user_id 
    , teamname 
    , season_id
    , round_name
    , ROUND(AVG(ftsy_score_for),1) AS cup_ftsy_score_for_avg
    , ROUND(AVG(ftsy_score_against),1) AS cup_ftsy_score_against_avg
    , CONCAT(
        SUM(CASE WHEN ftsy_score_for > ftsy_score_against THEN 1 ELSE 0 END)
        , '-'
        , SUM(CASE WHEN ftsy_score_for = ftsy_score_against THEN 1 ELSE 0 END) 
        , '-'
        , SUM(CASE WHEN ftsy_score_for < ftsy_score_against THEN 1 ELSE 0 END)
        ) AS cup_recored
FROM cte_schedule
GROUP by user_id, teamname, season_id, round_name
;