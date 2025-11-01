CREATE VIEW ftsy_scoring_all_aggregated_v AS 
SELECT 
	`ftsy_scoring_all_v`.`player_id` AS `player_id`
    , `ftsy_scoring_all_v`.`player_name` AS `player_name`
    , SUM(`ftsy_scoring_all_v`.`ftsy_score`) AS `sum_score`
    , AVG(`ftsy_scoring_all_v`.`ftsy_score`) AS `avg_score`
    , COUNT(`ftsy_scoring_all_v`.`player_id`) AS `anz_spiele` 
FROM `ftsy_scoring_all_v` 
GROUP BY
	`ftsy_scoring_all_v`.`player_id`
    , `ftsy_scoring_all_v`.`player_name` 
ORDER BY `sum_score` DESC