create view sm_fixtures_basic_v as 

select 	`sea`.`league_id` AS `league_id`
				,`sea`.`league_name` AS `league_name`
				,`fix`.`season_id` AS `season_id`
				,`sea`.`season_name` AS `season_name`
				,`fix`.`round_id` AS `round_id`
				,`fix`.`round_name` AS `round_name`
				,`fix`.`fixture_id` AS `fixture_id`
				,`home`.`name` AS `localteam_name`
				,`home`.`short_code` AS `localteam_name_code`
				,`away`.`name` AS `visitorteam_name`
				,`away`.`short_code` AS `visitorteam_name_code`
				,`fix`.`kickoff_dt` AS `kickoff_dt`
				,`fix`.`kickoff_ts` AS `kickoff_ts`
				,`fix`.`kickoff_time` AS `kickoff_time`
				,`fix`.`fixture_status` AS `fixture_status`
				,`fix`.`localteam_id` AS `localteam_id`
				,`fix`.`visitorteam_id` AS `visitorteam_id`
				,`fix`.`localteam_score` AS `localteam_score`
				,`fix`.`visitorteam_score` AS `visitorteam_score`
				,`fix`.`ft_score` AS `ft_score`
				,`fix`.`ht_score` AS `ht_score` 

from 	sm_fixtures fix 

left join sm_seasons sea
	on 	`sea`.`season_id` = `fix`.`season_id`

left join sm_teams home 
	on 	`fix`.`localteam_id` = `home`.`id`

left join sm_teams away 
	on `fix`.`visitorteam_id` = `away`.`id`