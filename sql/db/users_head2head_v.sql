CREATE VIEW xa7580_db1.users_head2head_v AS 
WITH cte_schedule_home AS (
    SELECT 
        sch.season_id 
        , sch.match_type 
        , sch.ftsy_home_id AS user_id
        , u1.teamname
        , u1.team_code
        , sch.ftsy_home_score AS score_for
        , sch.ftsy_away_id AS user_id_opp
        , u2.teamname AS teamname_opp
        , u2.team_code AS team_code_opp
        , sch.ftsy_away_score AS score_against
        , CASE WHEN ftsy_home_score > ftsy_away_score THEN 1 ELSE 0 END AS win_flg
        , CASE WHEN ftsy_home_score < ftsy_away_score THEN 1 ELSE 0 END AS loss_flg
        , CASE WHEN ftsy_home_score = ftsy_away_score THEN 1 ELSE 0 END AS draw_flg    
    FROM ftsy_schedule sch 
    INNER JOIN users u1 
        ON u1.id = sch.ftsy_home_id
    INNER JOIN users u2 
        ON u2.id = sch.ftsy_away_id 
    WHERE 
        sch.ftsy_home_score != ''
        AND sch.ftsy_away_score != ''
        AND sch.ftsy_home_score IS NOT NULL
        AND sch.ftsy_away_score IS NOT NULL
), cte_schedule_away AS (
    SELECT 
        sch.season_id 
        , sch.match_type 
        , sch.ftsy_away_id AS user_id
        , u1.teamname
        , u1.team_code
        , sch.ftsy_away_score AS score_for
        , sch.ftsy_home_id AS user_id_opp
        , u2.teamname AS teamname_opp
        , u2.team_code AS team_code_opp
        , sch.ftsy_home_score AS score_against
        , CASE WHEN ftsy_away_score > ftsy_home_score THEN 1 ELSE 0 END AS win_flg
        , CASE WHEN ftsy_away_score < ftsy_home_score THEN 1 ELSE 0 END AS loss_flg
        , CASE WHEN ftsy_away_score = ftsy_home_score THEN 1 ELSE 0 END AS draw_flg    
    FROM ftsy_schedule sch 
    INNER JOIN users u1 
        ON u1.id = sch.ftsy_away_id
    INNER JOIN users u2 
        ON u2.id = sch.ftsy_home_id 
    WHERE 
        sch.ftsy_home_score != ''
        AND sch.ftsy_away_score != ''
        AND sch.ftsy_home_score IS NOT NULL
        AND sch.ftsy_away_score IS NOT NULL
), cte_union AS (
    SELECT *
    FROM cte_schedule_home 
    UNION ALL 
    SELECT *
    FROM cte_schedule_away 
)
SELECT 
    season_id 
    , match_type 
    , user_id 
    , teamname 
    , team_code
    , user_id_opp
    , teamname_opp
    , team_code_opp
    , COUNT(*) AS matchups_cnt
    , SUM(win_flg) AS win_cnt
    , SUM(loss_flg) AS loss_cnt
    , SUM(draw_flg) AS draw_cnt
    , CONCAT(SUM(win_flg),'-',SUM(draw_flg), '-', SUM(loss_flg)) AS record
    , ROUND(AVG(score_for),1) AS score_for_avg
    , ROUND(AVG(score_against),1) AS score_against_avg    
FROM cte_union
GROUP BY season_id, match_type, user_id, teamname, team_code, user_id_opp, teamname_opp, team_code_opp
;