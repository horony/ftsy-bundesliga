create view ftsy_scoring_all_aggregated_v as 

select 	`ftsy_scoring_all_v`.`player_id` AS `player_id`
				,`ftsy_scoring_all_v`.`player_name` AS `player_name`
				,sum(`ftsy_scoring_all_v`.`ftsy_score`) AS `sum_score`
				,avg(`ftsy_scoring_all_v`.`ftsy_score`) AS `avg_score`
				,count(`ftsy_scoring_all_v`.`player_id`) AS `anz_spiele` 

from 	`ftsy_scoring_all_v` 

group by 	`ftsy_scoring_all_v`.`player_id`
					,`ftsy_scoring_all_v`.`player_name` 

order by 	`sum_score` desc