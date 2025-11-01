/* Calculate overall AND average ftsy score by player */

CREATE VIEW ftsy_scoring_agg_v AS 

SELECT 	
    `scr`.`player_id` AS `player_id`
    , `scr`.`player_name` AS `player_name`
    , SUM(`scr`.`ftsy_score`) AS `sum_ftsy_season`
    , ROUND(AVG(CASE 
        WHEN `scr`.`appearance_stat` = 1 THEN `scr`.`ftsy_score` 
        ELSE NULL 
        END),1) AS `avg_ftsy_season`
    , CASE 
        WHEN `scr`.`round_name` = (`para`.`spieltag` - 1) THEN `scr`.`ftsy_score` 
        ELSE NULL 
        END AS `ftsy_score_last`
    , ROUND(AVG(CASE
        WHEN `scr`.`appearance_stat` = 1 AND (`para`.`spieltag` BETWEEN (`para`.`spieltag` - 3) AND `para`.`spieltag`) THEN `scr`.`ftsy_score` 
        ELSE NULL 
        END),1) AS `avg_ftsy_3`
    , ROUND(AVG(CASE
        WHEN `scr`.`appearance_stat` = 1 AND (`para`.`spieltag` BETWEEN (`para`.`spieltag` - 5) AND `para`.`spieltag`) THEN `scr`.`ftsy_score` 
        ELSE NULL 
        END),1) AS `avg_ftsy_5`
    , ROUND(AVG(CASE
        WHEN `scr`.`appearance_stat` = 1 AND (`para`.`spieltag` BETWEEN (`para`.`spieltag` - 7) AND `para`.`spieltag`) THEN `scr`.`ftsy_score` 
        ELSE NULL 
        END),1) AS `avg_ftsy_7` 
FROM `ftsy_scoring_all_v` `scr` 
LEFT JOIN `parameter` `para` 
    ON 1 = 1
INNER JOIN `sm_fixtures` `fix` 
    ON	`fix`.`fixture_id` = `scr`.`fixture_id` 
    AND `fix`.`season_id` = `para`.`season_id`
GROUP BY `scr`.`player_id`,`scr`.`player_name` 
ORDER BY `sum_ftsy_season` DESC