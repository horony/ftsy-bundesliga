CREATE VIEW ftsy_schedule_cup_v AS 
WITH cte_two_legs AS (
    /* Cup matches with two legs */
    SELECT 
        leg1.season_id
        , leg1.match_type
        , leg1.cup_round
        , leg1.buli_round_name AS buli_round_name_leg1
        , leg1.ftsy_home_id
        , leg1.ftsy_away_id
        , leg1.ftsy_home_name
        , leg1.ftsy_away_name
        , leg1.ftsy_home_score AS ftsy_home_score_leg1
        , leg1.ftsy_away_score AS ftsy_away_score_leg1
        , true AS has_legs_flg
        , leg2.buli_round_name AS buli_round_name_leg2
        , ROUND(CASE WHEN leg1.ftsy_home_id = leg2.ftsy_home_id THEN leg2.ftsy_home_score ELSE leg2.ftsy_away_score END, 1) AS ftsy_home_score_leg2
        , ROUND(CASE WHEN leg1.ftsy_away_id = leg2.ftsy_away_id THEN leg2.ftsy_away_score ELSE leg2.ftsy_home_score END, 1) AS ftsy_away_score_leg2
    FROM `ftsy_schedule` leg1
    LEFT JOIN `ftsy_schedule` leg2
        ON leg1.season_id = leg2.season_id
        AND leg1.cup_round = leg2.cup_round
        AND (leg1.ftsy_home_id = leg2.ftsy_home_id OR leg1.ftsy_home_id = leg2.ftsy_away_id)
        AND leg2.match_type = 'cup'
        AND leg2.cup_leg = 2
    WHERE 
        leg1.match_type = 'cup'
        AND leg1.cup_leg = 1
        AND leg1.ftsy_home_id IS NOT NULL 
        AND leg1.ftsy_home_id != ''
        AND leg1.ftsy_away_id IS NOT NULL
        AND leg1.ftsy_away_id != ''
), cte_one_leg AS (
    /* Cup matches with one leg only */
    SELECT 
        leg1.season_id
        , leg1.match_type
        , leg1.cup_round
        , leg1.buli_round_name AS buli_round_name_leg1
        , leg1.ftsy_home_id
        , leg1.ftsy_away_id
        , leg1.ftsy_home_name
        , leg1.ftsy_away_name
        , leg1.ftsy_home_score AS ftsy_home_score_leg1
        , leg1.ftsy_away_score AS ftsy_away_score_leg1
        , FALSE AS has_legs_flg
        , NULL AS buli_round_name_leg2
        , 0 AS ftsy_home_score_leg2
        , 0 AS ftsy_away_score_leg2
    FROM `ftsy_schedule` leg1
    WHERE 
        leg1.match_type = 'cup'
        AND leg1.cup_leg = 0
        AND leg1.ftsy_home_id IS NOT NULL 
        AND leg1.ftsy_home_id != ''
        AND leg1.ftsy_away_id IS NOT NULL
        AND leg1.ftsy_away_id != ''

), cte_both AS (
    /* Combine both one-leg and two-leg cup matches */
    SELECT * 
    FROM cte_two_legs
    
    UNION ALL
    
    SELECT * 
    FROM cte_one_leg
)
SELECT 
    /* Apply final business logic and output */
    cte_both.season_id
    , cte_both.match_type
    , cte_both.cup_round
    , cte_both.buli_round_name_leg1
    , cte_both.ftsy_home_id
    , cte_both.ftsy_away_id
    , cte_both.ftsy_home_name
    , cte_both.ftsy_away_name
    , cte_both.ftsy_home_score_leg1
    , cte_both.ftsy_away_score_leg1
    , cte_both.has_legs_flg
    , cte_both.buli_round_name_leg2
    , cte_both.ftsy_home_score_leg2
    , cte_both.ftsy_away_score_leg2
    , ROUND(cte_both.ftsy_home_score_leg1 + cte_both.ftsy_home_score_leg2, 1) AS ftsy_home_score_agg
    , ROUND(cte_both.ftsy_away_score_leg1 + cte_both.ftsy_away_score_leg2, 1) AS ftsy_away_score_agg
FROM cte_both