CREATE VIEW users_scoring_akt_v AS
WITH base AS (
    SELECT 
        u.id AS user_id
        , u.teamname
        , param.season_id
        , param.spieltag AS round_name
        , COUNT(own.player_id) AS players_fielded_cnt
        , CASE WHEN COUNT(own.player_id) != 11 THEN -20 ELSE COALESCE(SUM(ftsy_score),0) END AS ftsy_score_sum
        , ROUND(SUM(CASE 
            WHEN fix.fixture_status = 'FT' THEN COALESCE(scr.ftsy_score, 0) 
            WHEN fix.fixture_status = 'NS' THEN COALESCE(proj.ftsy_score_projected, 0) 
            WHEN fix.fixture_status IN ('1st', '2nd', 'HT') THEN COALESCE(scr.ftsy_score, 0) + ((90 - COALESCE(scr.minutes_played_stat, 0)) / 90) * GREATEST(COALESCE(proj.ftsy_score_projected, 0) - 4, 0)
            ELSE 0
            END),1) AS ftsy_score_projected_sum
        , SUM(CASE WHEN fix.fixture_status IN ('1st', '2nd', 'HT') THEN 1 ELSE 0 END) AS players_in_play_cnt
        , SUM(CASE WHEN fix.fixture_status = 'FT' THEN 1 ELSE 0 END) AS players_ft_cnt
        , SUM(CASE WHEN fix.fixture_status = 'NS' THEN 1 ELSE 0 END) AS players_ns_cnt 
    FROM users u
    INNER JOIN parameter param
        ON 1 = 1
    INNER JOIN ftsy_player_ownership own 
        ON own.1_ftsy_owner_id = u.id
        AND own.1_ftsy_owner_type = 'USR'
        AND own.1_ftsy_match_status != 'NONE'
    INNER JOIN sm_playerbase ply
        ON own.player_id = ply.id
    INNER JOIN sm_fixtures_basic_v fix
        ON (ply.current_team_id = fix.localteam_id OR ply.current_team_id = fix.visitorteam_id)
        AND fix.round_name = param.spieltag
        AND fix.season_id = param.season_id
    LEFT JOIN ftsy_scoring_akt_v scr 
        ON own.player_id = scr.player_id
    LEFT JOIN ftsy_scoring_projection_v proj
        ON own.player_id = proj.player_id
    WHERE 
        u.active_account_flg = 1 
        AND u.id NOT IN (2)
    GROUP BY u.id, u.teamname, param.season_id, param.spieltag
)
SELECT 
    user_id
    , teamname
    , season_id
    , round_name
    , players_fielded_cnt
    , ftsy_score_sum
    , ftsy_score_projected_sum
    , players_in_play_cnt
    , players_ft_cnt
    , players_ns_cnt
    , CASE 
        WHEN players_ft_cnt = 0 AND players_in_play_cnt = 0 THEN CONCAT(players_ns_cnt, ' NS')
        WHEN players_ns_cnt = 0 AND players_in_play_cnt = 0 THEN CONCAT(players_ft_cnt, ' FT')
        ELSE CONCAT(players_in_play_cnt, ' LIVE | ', players_ns_cnt, ' NS | ', players_ft_cnt, ' FT')
        END AS players_status
FROM base 
;