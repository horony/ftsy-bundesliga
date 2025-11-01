CREATE VIEW ftsy_pokalsieger_v AS
SELECT  
    `s`.`season_id` AS `season_id`
    , `s`.`season_name` AS `season_name`
    , `sch`.`buli_round_name` AS `buli_round_name`
    , `sch`.`ftsy_match_id` AS `ftsy_match_id`
    , CASE WHEN `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_id` ELSE `sch`.`ftsy_away_id` END AS `winner_user_id`
    , CASE WHEN `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_name` ELSE `sch`.`ftsy_away_name` END AS `winner_team_name`
    , CASE WHEN `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_score` ELSE `sch`.`ftsy_away_score` END AS `winner_score`
    , CASE WHEN `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_id` ELSE `sch`.`ftsy_away_id` END AS `looser_user_id`
    , CASE WHEN `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_name` ELSE `sch`.`ftsy_away_name` END AS `looser_team_name`
    , CASE WHEN `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` THEN `sch`.`ftsy_home_score` ELSE `sch`.`ftsy_away_score` END AS `looser_score` 
FROM `ftsy_schedule` `sch` 
INNER JOIN `xa7580_db1`.`sm_seasons` `s` 
    ON `sch`.`season_id` = `s`.`season_id` 
WHERE 
    `sch`.`match_type` = 'cup' 
    AND `sch`.`cup_round` = 'final' 
    AND `sch`.`ftsy_home_score` + `sch`.`ftsy_away_score` > 0 
ORDER BY `s`.`season_id` DESC