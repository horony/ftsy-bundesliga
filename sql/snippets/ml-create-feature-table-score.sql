CREATE TABLE ml_player_features_score AS
WITH cte_seasons AS (
    SELECT season_id
    FROM sm_seasons 
    ORDER BY season_id DESC
    LIMIT 3
), cte_fixtures AS (
    SELECT 
        home.season_id
        , home.round_name
        , home.fixture_id
        , home.localteam_id AS team_id
        , home.visitorteam_id AS opp_team_id
        , home.localteam_score AS team_goals_for
        , home.visitorteam_score AS team_goals_against
    FROM sm_fixtures home
    INNER JOIN cte_seasons hsea
        ON home.season_id = hsea.season_id
    UNION ALL 
    SELECT 
        away.season_id
        , away.round_name
        , away.fixture_id
        , away.visitorteam_id AS team_id
        , away.localteam_id AS opp_team_id 
        , away.visitorteam_score AS team_goals_for
        , away.localteam_score AS team_goals_against 
    FROM sm_fixtures away
    INNER JOIN cte_seasons asea
        ON away.season_id = asea.season_id
), cte_own_team_stats AS (
    SELECT  
        fix.season_id 
        , fix.round_name 
        , fix.team_id
        , SUM(COALESCE(hst.shots_total_stat,0)) AS team_shots_for
        , SUM(COALESCE(hst.passes_complete_stat,0) + COALESCE(hst.passes_incomplete_stat,0) ) AS team_passes_for
        , SUM(COALESCE(hst.duels_won_stat,0) + COALESCE(hst.duels_lost_stat,0)) AS team_duel_total_for
        , SUM(COALESCE(hst.duels_won_stat,0)) AS team_duel_won_for        
    FROM cte_fixtures fix
    LEFT JOIN ftsy_scoring_hist hst 
        ON hst.fixture_id = fix.fixture_id
        AND hst.current_team_id = fix.team_id 
    GROUP BY fix.season_id 
        , fix.round_name 
        , fix.team_id
), cte_opp_team_stats AS (
    SELECT  
        fix.season_id 
        , fix.round_name 
        , fix.team_id
        , SUM(COALESCE(hst.shots_total_stat,0)) AS team_shots_against
        , SUM(COALESCE(hst.passes_complete_stat,0) + COALESCE(hst.passes_incomplete_stat,0) ) AS team_passes_against
        , SUM(COALESCE(hst.duels_won_stat,0) + COALESCE(hst.duels_lost_stat,0)) AS team_duel_total_against
        , SUM(COALESCE(hst.duels_won_stat,0)) AS team_duel_won_against
    FROM cte_fixtures fix
    LEFT JOIN ftsy_scoring_hist hst 
        ON hst.fixture_id = fix.fixture_id
        AND hst.current_team_id = fix.opp_team_id 
    GROUP BY fix.season_id 
        , fix.round_name 
        , fix.team_id
), cte_team_strength AS (
SELECT 
    fix.season_id 
    , fix.round_name 
    , fix.team_id
    , fix.team_goals_for
    , fix.team_goals_against
    , own.team_shots_for
    , opp.team_shots_against
    , own.team_passes_for
    /*, opp.team_passes_against*/
    , CONVERT(own.team_passes_for / (own.team_passes_for + opp.team_passes_against), DOUBLE) AS team_possession_pct_for
    , CONVERT(opp.team_passes_against / (own.team_passes_for + opp.team_passes_against), DOUBLE) AS team_possession_pct_against  
    , own.team_duel_won_for 
    /*, opp.team_duel_total_against
    , own.team_duel_won_for
    , opp.team_duel_won_against*/
    , CONVERT(own.team_duel_won_for / (own.team_duel_total_for), DOUBLE) AS team_duel_pct_for
    , CONVERT(opp.team_duel_won_against / (opp.team_duel_total_against), DOUBLE) AS team_duel_pct_against      
FROM cte_fixtures fix 
LEFT JOIN cte_own_team_stats own 
    ON own.team_id = fix.team_id 
    AND own.round_name = fix.round_name 
    AND own.season_id = fix.season_id
LEFT JOIN cte_opp_team_stats opp 
    ON opp.team_id = fix.team_id 
    AND opp.round_name = fix.round_name 
    AND opp.season_id = fix.season_id 
), cte_team_strength_rolling AS (
SELECT 
    team.season_id
    , team.round_name
    , team.team_id
    , AVG(COALESCE(team.team_goals_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_goals_scored_avg_5
    , AVG(COALESCE(team.team_goals_against,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_goals_allowed_avg_5
    , AVG(COALESCE(team.team_shots_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_shots_attempted_avg_5
    , AVG(COALESCE(team.team_shots_against,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_shots_allowed_avg_5
    , AVG(COALESCE(team.team_passes_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_passes_avg_5          
    , AVG(COALESCE(team.team_possession_pct_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_possession_share_avg_5  
    , AVG(COALESCE(team.team_duel_won_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_duel_won_for_avg_5           
    , AVG(COALESCE(team.team_duel_pct_for,0)) OVER (
        PARTITION BY team.team_id
        ORDER BY team.season_id, team.round_name
        ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS team_strength_feat_duel_won_share_avg_5      
FROM cte_team_strength team
), cte_players AS (
    SELECT  
        0 AS target_flg
        , hist.player_id
        , hist.position_short
        , hist.season_id
        , hist.round_name
        , hist.current_team_id AS team_id
        , hist.opp_team_id
        , hist.minutes_played_stat AS actual_minutes_played_stat
        , hist.ftsy_score AS actual_ftsy_score
        /* Appearance */
        , COALESCE(hist.appearance_stat, 0) AS appearance_stat
        /* Scoring */
        , COALESCE(hist.pen_scored_stat, 0) 
            + COALESCE(hist.goals_minus_pen_stat, 0) 
            + COALESCE(hist.assists_stat, 0) AS scoring_stat
        /* Shots */
        , COALESCE(hist.shots_total_stat, 0) AS shots_stat
        /* Key Passes */
        , COALESCE(hist.key_passes_stat, 0) AS key_passes_stat
        /* Passing */
        , COALESCE(hist.passes_complete_stat, 0)
            - COALESCE(hist.passes_incomplete_stat, 0) AS passes_stat
        , COALESCE(hist.crosses_complete_stat, 0) 
            + COALESCE(hist.crosses_incomplete_stat, 0) AS crosses_stat
        /* Duels */
        , COALESCE(hist.duels_won_ftsy, 0) 
            - COALESCE(hist.duels_lost_stat, 0)  AS duels_stat 
        , COALESCE(hist.dribbles_success_stat, 0) 
            - COALESCE(hist.dribbles_failed_stat, 0) AS dribbles_stat
        , COALESCE(hist.interceptions_stat, 0) 
            + COALESCE(hist.blocks_stat, 0) 
            + COALESCE(hist.tackles_stat, 0) AS int_block_tackle_stat
        , COALESCE(hist.clearances_stat, 0) AS clearances_stat
        /* Defense */
        , COALESCE(hist.clean_sheet_stat, 0) AS clean_sheet_stat
        , COALESCE(hist.goals_conceded_stat, 0) AS goals_conceded_stat
        , COALESCE(hist.goalkeeper_goals_conceded_stat, 0) AS goals_gk_conceded_stat
        /* Goalkeeping */
        , COALESCE(hist.pen_saved_stat, 0) 
            + COALESCE(hist.saves_stat, 0) AS saves_stat
        /* Errors */
        , COALESCE(hist.error_lead_to_goal_stat, 0) 
            + COALESCE(hist.pen_missed_stat,0)
            + COALESCE(hist.owngoals_stat, 0) AS big_errors_stat
        , COALESCE(hist.dispossessed_stat, 0)
            + COALESCE(hist.pen_committed_stat,0) 
            + COALESCE(hist.big_chances_missed_stat, 0) AS small_errors_stat
    FROM ftsy_scoring_hist hist
    INNER JOIN cte_seasons sea
        ON hist.season_id = sea.season_id
    WHERE 
        hist.minutes_played_stat >= 75
    UNION ALL 
    SELECT 
        1 AS target_flg
        , base.id 
        , base.position_short
        , p.season_id  AS season_id
        , p.spieltag AS round_name
        , base.current_team_id  AS current_team_id
        , fix.opp_id  AS opp_team_id
        , NULL 
        , NULL 
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
        , NULL 
        , NULL
     FROM sm_playerbase base
     CROSS JOIN `parameter` p 
     INNER JOIN sm_fixture_per_team_akt_v fix
        ON base.current_team_id = fix.team_id
     WHERE 1=1
), cte_players_rolling AS (
    SELECT 
        target_flg
        , p.player_id
        , p.position_short
        , p.season_id
        , p.round_name
        , p.team_id
        , p.opp_team_id
        , p.actual_minutes_played_stat
        , p.actual_ftsy_score
        /* Ftsy Score */
        , CONVERT(AVG(p.actual_ftsy_score) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_ftsy_score_avg_5 
        /* Appearance */
        , CONVERT(AVG(p.appearance_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_appearance_avg_5
        /* Scoring */ 
        , CONVERT(AVG(p.scoring_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_scoring_avg_5
        /* Shots */
        , CONVERT(AVG(p.shots_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_shots_avg_5
        /* Key Passes */
        , CONVERT(AVG(p.key_passes_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_keypasses_avg_5
        /* Passes */
        , CONVERT(AVG(p.passes_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_passing_avg_5
        /* Crosses */
        , CONVERT(AVG(p.crosses_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_crosses_avg_5
        /* Duels */
        , CONVERT(AVG(p.duels_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_duels_avg_5
        /* Dribbles */
        , CONVERT(AVG(p.dribbles_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_dribbles_avg_5
        /* Interception + Blocks + Tackles */
        , CONVERT(AVG(p.int_block_tackle_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_intblocktackle_avg_5
        /* Clearances */
        , CONVERT(AVG(p.clearances_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_clearances_avg_5
        /* Clean Sheet */
        , CONVERT(AVG(p.clean_sheet_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_cleansheet_avg_5
        , CONVERT(AVG(p.goals_conceded_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_goalsagainst_avg_5
        /* Goal Conceded GK */
        , CONVERT(AVG(p.goals_gk_conceded_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_goalsagainstgk_avg_5
    /* Goalkeeping */
        , CONVERT(AVG(p.saves_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_gksaves_avg_5
        /* Big Errors */
        , CONVERT(AVG(p.big_errors_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_big_errors_avg_5
        /* Small Errors */
        , CONVERT(AVG(p.small_errors_stat) OVER (
            PARTITION BY p.player_id
            ORDER BY p.season_id, p.round_name
            ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
            ) , DOUBLE ) AS player_feat_stat_small_errors_avg_5
    FROM cte_players p
)
SELECT 
    p.* 
    , team.team_strength_feat_goals_scored_avg_5 AS own_team_strength_feat_goals_scored_avg_5
    , team.team_strength_feat_goals_allowed_avg_5 AS own_team_strength_feat_goals_allowed_avg_5
    , team.team_strength_feat_shots_attempted_avg_5 AS own_team_strength_feat_shots_attempted_avg_5
    , team.team_strength_feat_shots_allowed_avg_5 AS own_team_strength_feat_shots_allowed_avg_5
    , team.team_strength_feat_possession_share_avg_5 AS own_team_strength_feat_possession_share_avg_5
    , team.team_strength_feat_duel_won_share_avg_5 AS own_team_strength_feat_duel_won_share_avg_5
    , opp.team_strength_feat_goals_scored_avg_5 AS opp_team_strength_feat_goals_scored_avg_5
    , opp.team_strength_feat_goals_allowed_avg_5 AS opp_team_strength_feat_goals_allowed_avg_5
    , opp.team_strength_feat_shots_attempted_avg_5 AS opp_team_strength_feat_shots_attempted_avg_5
    , opp.team_strength_feat_shots_allowed_avg_5 AS opp_team_strength_feat_shots_allowed_avg_5
    , opp.team_strength_feat_possession_share_avg_5 AS opp_team_strength_feat_possession_share_avg_5
    , opp.team_strength_feat_duel_won_share_avg_5 AS opp_team_strength_feat_duel_won_share_avg_5
    , CONVERT(p.player_feat_stat_shots_avg_5 / team.team_strength_feat_shots_attempted_avg_5, DOUBLE) AS player_feat_stat_shot_teamshare_avg
    , CONVERT(p.player_feat_stat_passing_avg_5 / team.team_strength_feat_passes_avg_5, DOUBLE) AS player_feat_stat_passes_teamshare_avg
    , CONVERT(p.player_feat_stat_duels_avg_5 / team.team_strength_feat_duel_won_for_avg_5, DOUBLE) AS player_feat_stat_duels_teamshare_avg
    , CONVERT(p.player_feat_stat_goalsagainstgk_avg_5 / team.team_strength_feat_goals_allowed_avg_5, DOUBLE) AS player_feat_stat_goalsagainstgk_teamshare_avg
    , SYSDATE() AS insert_ts
FROM cte_players_rolling p
LEFT JOIN cte_team_strength_rolling team
    ON p.team_id = team.team_id
    AND p.season_id = team.season_id
    AND p.round_name = team.round_name
LEFT JOIN cte_team_strength_rolling opp
    ON p.opp_team_id = opp.team_id
    AND p.season_id = opp.season_id
    AND p.round_name = opp.round_name
WHERE 
    1 = 1 
    AND 1 = CASE WHEN team.team_strength_feat_goals_scored_avg_5 IS NULL AND target_flg = 0 THEN 0 ELSE 1 END    
;
