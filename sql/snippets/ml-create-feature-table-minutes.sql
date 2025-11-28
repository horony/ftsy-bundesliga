CREATE TABLE ml_player_features_minutes AS 
WITH ordered_hist AS (
    SELECT
        hist.season_id
        , hist.round_name
        , hist.player_id
        , hist.position_short
        , hist.current_team_id
        /*, COALESCE(hist.injured,0) AS is_injured
        , COALESCE(hist.is_sidelined,0) AS is_sidelined */
        , COALESCE(hist.appearance_stat,0) AS appearance_stat
        , COALESCE(hist.minutes_played_stat,0) AS minutes_played_stat
        , COALESCE(hist.redcards_stat,0) AS redcards_stat
        , COALESCE(hist.redyellowcards_stat,0) AS redyellowcards_stat
        , COALESCE(hist.is_suspended,0) AS is_suspended
    FROM ftsy_scoring_hist hist
    INNER JOIN (
        SELECT season_id 
        FROM sm_seasons
        ORDER BY season_id DESC 
        LIMIT 3
    ) sea 
        ON sea.season_id = hist.season_id

    UNION ALL 

    SELECT 
        p.season_id  AS season_id
        , p.spieltag AS round_name
        , base.id AS player_id
        , base.position_short
        , base.current_team_id  AS current_team_id
        , NULL 
        , NULL 
        , NULL 
        , NULL 
        , NULL
     FROM sm_playerbase base
     INNER JOIN `parameter` p 
        ON 1 = 1
     INNER JOIN sm_fixture_per_team_akt_v fix
        ON base.current_team_id = fix.team_id
), team_agg AS (
    SELECT
        season_id
        , round_name
        , current_team_id
        , COUNT(CASE WHEN minutes_played_stat > 0 THEN 1 END) AS players_who_played
        , SUM(minutes_played_stat) AS total_minutes_played
        , STDDEV_POP(minutes_played_stat) AS team_minutes_std
        , AVG(minutes_played_stat) AS team_minutes_avg
    FROM ordered_hist
    GROUP BY season_id, round_name, current_team_id
), team_rotation AS (
    SELECT
        *
        , LAG(players_who_played, 1) OVER (
            PARTITION BY season_id, current_team_id ORDER BY round_name
        ) AS players_last_game
        , LAG(team_minutes_std, 1) OVER (
            PARTITION BY season_id, current_team_id ORDER BY round_name
        ) AS team_minutes_std_last
        , AVG(team_minutes_std) OVER (
            PARTITION BY season_id, current_team_id
            ORDER BY round_name
            ROWS BETWEEN 3 PRECEDING AND 1 PRECEDING
        ) AS team_minutes_variance_last3
        , AVG(team_minutes_avg) OVER (
            PARTITION BY season_id, current_team_id
            ORDER BY round_name
            ROWS BETWEEN 3 PRECEDING AND 1 PRECEDING
        ) AS team_avg_minutes_last3
    FROM team_agg
), team_rotation_final AS (
    SELECT
        *
        , players_who_played - players_last_game AS team_player_change_from_last
    FROM team_rotation
), lagged AS (
    SELECT
        oh.*
        , LAG(appearance_stat, 1) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS appearance_lag1
        , LAG(minutes_played_stat, 1) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS minutes_lag1
        , LAG(minutes_played_stat, 2) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS minutes_lag2
        , LAG(minutes_played_stat, 3) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS minutes_lag3
        , LAG(minutes_played_stat, 4) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS minutes_lag4
        , LAG(minutes_played_stat, 5) OVER (PARTITION BY player_id, season_id ORDER BY round_name) AS minutes_lag5
    FROM ordered_hist oh
), rolling AS (
    SELECT
        l.*
        , AVG(minutes_played_stat) OVER (
            PARTITION BY player_id, season_id ORDER BY round_name ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS minutes_roll5_avg
        , STDDEV_POP(minutes_played_stat) OVER (
            PARTITION BY player_id, season_id ORDER BY round_name ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS minutes_roll5_std
        , SUM(appearance_stat) OVER (
            PARTITION BY player_id, season_id ORDER BY round_name ROWS BETWEEN 5 PRECEDING AND 1 PRECEDING
        ) AS played_last5_cnt
    FROM lagged l
), card_events AS (
    SELECT
        season_id
        , player_id
        , round_name AS card_round
        , CASE
            WHEN redcards_stat = 1 THEN 2
            WHEN redyellowcards_stat = 1 THEN 1
            ELSE 0
            END AS suspension_length
    FROM ordered_hist
    WHERE redcards_stat = 1 OR redyellowcards_stat = 1
), suspend_expanded AS (
    SELECT DISTINCT
        r.season_id
        , r.player_id
        , r.round_name
        , CASE
            WHEN EXISTS (
                SELECT 1
                FROM card_events ce
                WHERE ce.season_id = r.season_id
                  AND ce.player_id = r.player_id
                  AND ce.card_round < r.round_name
                  AND ce.card_round + ce.suspension_length >= r.round_name
            )
            THEN 1 ELSE 0 END AS is_card_suspended
    FROM ordered_hist r
), with_suspension AS (
    SELECT
        ro.*
        , se.is_card_suspended
    FROM rolling ro
    LEFT JOIN suspend_expanded se
        ON se.season_id = ro.season_id
        AND se.player_id = ro.player_id
        AND se.round_name = ro.round_name
), with_team_features AS (
    SELECT
        ws.*
        , tr.players_last_game AS team_players_last_round
        , tr.team_minutes_std_last
        , tr.team_minutes_variance_last3
        , tr.team_avg_minutes_last3
        , tr.team_player_change_from_last
    FROM with_suspension ws
    LEFT JOIN team_rotation_final tr
        ON tr.season_id = ws.season_id
        AND tr.round_name = ws.round_name
        AND tr.current_team_id = ws.current_team_id
),

/* ---------------------------------------------------------
   DNP STREAK
   --------------------------------------------------------- */
dnp_calc AS (
    SELECT 
        *
        , CASE WHEN minutes_played_stat = 0 THEN 1 ELSE 0 END AS dnp_flag
    FROM with_team_features
), dnp_streak AS (
    SELECT
        *
        , SUM(
            CASE WHEN dnp_flag = 1 THEN 1 ELSE 0 END
        ) OVER (
            PARTITION BY season_id, player_id
            ORDER BY round_name
            ROWS BETWEEN UNBOUNDED PRECEDING AND 1 PRECEDING
        ) AS dnp_streak
    FROM dnp_calc
)
SELECT
    season_id
    , round_name
    , player_id
    , position_short
    , current_team_id
    , minutes_played_stat AS minutes_target
    , appearance_lag1, minutes_lag1, minutes_lag2, minutes_lag3, minutes_lag4, minutes_lag5
    , minutes_roll5_avg, minutes_roll5_std, played_last5_cnt
    , dnp_streak
    , redcards_stat
    , redyellowcards_stat
    , is_card_suspended
    , team_players_last_round
    , team_minutes_std_last
    , team_minutes_variance_last3
    , team_avg_minutes_last3
    , team_player_change_from_last
FROM dnp_streak
ORDER BY season_id, round_name, player_id
;