CREATE VIEW ftsy_scoring_projection_v AS
SELECT  
    `sm_playerbase`.`id` AS `player_id`
    ,`sm_playerbase`.`display_name` AS `display_name`
    /* Collect ftsy scores that are needed for calculation of projection */
    ,COALESCE(`ftsy_scoring_snap`.`ftsy_score_avg`,0) AS `ftsy_score_avg`
    ,COALESCE(`ftsy_scoring_snap`.`ftsy_score_avg_last_5`,0) AS `ftsy_score_avg_last_5`
    ,COALESCE(`ftsy_scoring_snap`.`ftsy_score_avg_last_3`,0) AS `ftsy_score_avg_last_3`
    ,COALESCE(`ftsy_scoring_snap`.`ftsy_score_last`,0) AS `ftsy_score_last`
    ,COALESCE(`ftsy_points_allowed`.`avg_allowed`,0) AS `avg_allowed`
    ,`sm_playerbase`.`injured` AS `injured`
    ,`sm_playerbase`.`is_suspended` AS `is_suspended`
    ,`sm_playerbase`.`is_sidelined` AS `is_sidelined`
    /* Calculate projection */
    ,COALESCE(
        CASE 
            WHEN `parameter_projection`.`faktor_player_active` = 1 AND `sm_playerbase`.`is_sidelined` = 1 THEN 0 
            ELSE 
                ROUND(
                    `ftsy_scoring_snap`.`ftsy_score_avg_last_5` * `parameter_projection`.`faktor_ftsy_points_last_5` 
                    + `ftsy_scoring_snap`.`ftsy_score_avg_last_3` * `parameter_projection`.`faktor_ftsy_points_last_3` 
                    + `ftsy_scoring_snap`.`ftsy_score_last` * `parameter_projection`.`faktor_ftsy_points_last` 
                    + `ftsy_scoring_snap`.`ftsy_score_avg` * `parameter_projection`.`faktor_ftsy_points_season` 
                    + `ftsy_points_allowed`.`avg_allowed` * `parameter_projection`.`faktor_ftsy_points_pos_opponent`
                    ,1) 

            END,0) AS `ftsy_score_projected` 
FROM `sm_playerbase` 
/* Join parameters, which define the weight each stat adds to the projection */
INNER JOIN `parameter_projection` 
    ON (1 = 1)
/* Join information on current opponent per team */
INNER JOIN `sm_fixture_per_team_akt_v` 
    ON `sm_fixture_per_team_akt_v`.`team_id` = `sm_playerbase`.`current_team_id`
/* Join information on how much ftsy points current opponent allows to players position */
INNER JOIN `ftsy_points_allowed` 
    ON `ftsy_points_allowed`.`opp_team_id` = `sm_fixture_per_team_akt_v`.`opp_id` 
    AND `ftsy_points_allowed`.`position_short` = `sm_playerbase`.`position_short` 
/* Join snapshot with information on players avg scores over the past few games */
INNER JOIN `ftsy_scoring_snap` 
    ON `ftsy_scoring_snap`.`id` = `sm_playerbase`.`id`
INNER JOIN `ftsy_player_ownership` 
    ON `ftsy_player_ownership`.`player_id` = `sm_playerbase`.`id`
WHERE 
    `sm_playerbase`.`rostered` = 1