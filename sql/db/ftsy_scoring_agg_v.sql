/* Calculate overall and average ftsy score by player */

create view ftsy_scoring_agg_v as 

select 	`scr`.`player_id` AS `player_id`
				,`scr`.`player_name` AS `player_name`
				,sum(`scr`.`ftsy_score`) AS `sum_ftsy_season`
				
				,round(avg(
					case 	when `scr`.`appearance_stat` = 1 then `scr`.`ftsy_score` 
								else NULL 
								end),1) AS `avg_ftsy_season`
				
				,case 	when `scr`.`round_name` = (`para`.`spieltag` - 1) then `scr`.`ftsy_score` 
								else NULL 
								end AS `ftsy_score_last`

				,round(avg(
					case 	when `scr`.`appearance_stat` = 1 and (`para`.`spieltag` between (`para`.`spieltag` - 3) and `para`.`spieltag`) then `scr`.`ftsy_score` 
								else NULL 
								end),1) AS `avg_ftsy_3`
				
				,round(avg(
					case 	when `scr`.`appearance_stat` = 1 and (`para`.`spieltag` between (`para`.`spieltag` - 5) and `para`.`spieltag`) then `scr`.`ftsy_score` 
								else NULL 
								end),1) AS `avg_ftsy_5`

				,round(avg(
					case 	when `scr`.`appearance_stat` = 1 and (`para`.`spieltag` between (`para`.`spieltag` - 7) and `para`.`spieltag`) then `scr`.`ftsy_score` 
								else NULL 
								end),1) AS `avg_ftsy_7` 

from `ftsy_scoring_all_v` `scr` 

left join `parameter` `para` 
	on 	1 = 1

inner join `sm_fixtures` `fix` 
	on	`fix`.`fixture_id` = `scr`.`fixture_id` 
			and `fix`.`season_id` = `para`.`season_id`

group by `scr`.`player_id`,`scr`.`player_name` 
order by `sum_ftsy_season` desc