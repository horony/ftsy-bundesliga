/* Creates view with information on the current fixture for every Bundesliga team (total of 18 rows) */

create view sm_fixture_per_team_akt_v as 

select 	/* Team */
				`base`.`team_id` AS `team_id`
				,`team`.`short_code` AS `team_code`
				,`team`.`name` AS `team_name`

				/* Opposing team */
				,`base`.`opp_id` AS `opp_id`
				,`opp`.`short_code` AS `opp_code`
				,`opp`.`name` AS `opp_name`

				/* Kickoff */
				,`base`.`kickoff_dt` AS `kickoff_dt`
				,`base`.`kickoff_ts` AS `kickoff_ts`
				, null AS `kickoff_time`
				, null AS `minute`

				,case 	when (dayofweek(`base`.`kickoff_dt`) = 1) then 'Sonntag' 
								when (dayofweek(`base`.`kickoff_dt`) = 2) then 'Montag' 
								when (dayofweek(`base`.`kickoff_dt`) = 3) then 'Dienstag' 
								when (dayofweek(`base`.`kickoff_dt`) = 4) then 'Mittwoch' 
								when (dayofweek(`base`.`kickoff_dt`) = 5) then 'Donnerstag' 
								when (dayofweek(`base`.`kickoff_dt`) = 6) then 'Freitag' 
								when (dayofweek(`base`.`kickoff_dt`) = 7) then 'Samstag' 
								end AS `kickoff_day_long`

				,case 	when (dayofweek(`base`.`kickoff_dt`) = 1) then 'So' 
								when (dayofweek(`base`.`kickoff_dt`) = 2) then 'Mo' 
								when (dayofweek(`base`.`kickoff_dt`) = 3) then 'Di' 
								when (dayofweek(`base`.`kickoff_dt`) = 4) then 'Mi' 
								when (dayofweek(`base`.`kickoff_dt`) = 5) then 'Do' 
								when (dayofweek(`base`.`kickoff_dt`) = 6) then 'Fr' 
								when (dayofweek(`base`.`kickoff_dt`) = 7) then 'Sa'
								end AS `kickoff_day_short`

				/* Match status */
				,`base`.`match_status` AS `match_status`
				,`base`.`goals_for` AS `goals_for`
				,`base`.`goals_against` AS `goals_against`

				,case 	when (`base`.`goals_for` > `base`.`goals_against`) then 'S' 
								when (`base`.`goals_for` = `base`.`goals_against`) then 'U' 
								when (`base`.`goals_for` < `base`.`goals_against`) then 'N' 
								end AS `match_result` 

from (

	/* Home teams */

	select 	`sm_fixtures`.`localteam_id` AS `team_id`
					,`sm_fixtures`.`visitorteam_id` AS `opp_id`
					,`sm_fixtures`.`kickoff_dt` AS `kickoff_dt`
					,`sm_fixtures`.`kickoff_ts` AS `kickoff_ts`
					,`sm_fixtures`.`match_status` AS `match_status`
					,`sm_fixtures`.`localteam_score` AS `goals_for`
					,`sm_fixtures`.`visitorteam_score` AS `goals_against` 

	from 	`sm_fixtures` 

	where 	`sm_fixtures`.`round_name` = (select `parameter`.`spieltag` from `parameter`) 
					and `sm_fixtures`.`season_id` = (select `parameter`.`season_id` from `parameter`)

	union all 

	/* Visitor teams */

	select 	`sm_fixtures`.`visitorteam_id` AS `team_id`
					,`sm_fixtures`.`localteam_id` AS `opp_id`
					,`sm_fixtures`.`kickoff_dt` AS `kickoff_dt`
					,`sm_fixtures`.`kickoff_ts` AS `kickoff_ts`
					,`sm_fixtures`.`match_status` AS `match_status`
					,`sm_fixtures`.`visitorteam_score` AS `goals_for`
					,`sm_fixtures`.`localteam_score` AS `goals_against` 

	from `sm_fixtures` 

	where 	(
        	`sm_fixtures`.`round_name` = (select `parameter`.`spieltag` from `parameter`)
			and `sm_fixtures`.`season_id` = (select `parameter`.`season_id` from `parameter`)
        	)

	) `base` 

inner join `sm_teams` `team` 
	on 	`base`.`team_id` = `team`.`id`

inner join `sm_teams` `opp` 
	on 	`base`.`opp_id` = `opp`.`id`