create view ftsy_ewige_tabelle_v as

select	`t`.`user_id` AS `user_id`
				,`t`.`team_name` AS `team_name`
				,sum(`t`.`punkte`) AS `sum_punkte`
				,count(distinct `t`.`season_id`) AS `anz_saisons`
				,sum(`t`.`siege` + `t`.`niederlagen` + `t`.`unentschieden`) AS `anz_spiele`
				,sum(`t`.`score_for`) AS `sum_score_for`,sum(`t`.`score_against`) AS `sum_score_agains`
				,sum(`t`.`siege`) AS `anz_siege`
				,sum(`t`.`niederlagen`) AS `anz_niederlagen`
				,sum(`t`.`unentschieden`) AS `anz_unentschieden`
				,sum(`t`.`trost`) AS `anz_trost` 

from (

	/* Data from the prod table */

	select `ftsy_tabelle_2020`.`player_id` AS `user_id`
					, `ftsy_tabelle_2020`.`team_name` AS `team_name`
					, `ftsy_tabelle_2020`.`punkte` AS `punkte`
					, `ftsy_tabelle_2020`.`season_id` AS `season_id`
					, `ftsy_tabelle_2020`.`score_for` AS `score_for`
					, `ftsy_tabelle_2020`.`score_against` AS `score_against`
					, `ftsy_tabelle_2020`.`siege` AS `siege`
					, `ftsy_tabelle_2020`.`niederlagen` AS `niederlagen`
					, `ftsy_tabelle_2020`.`unentschieden` AS `unentschieden`
					, `ftsy_tabelle_2020`.`trost` AS `trost` 

	from 	`ftsy_tabelle_2020` 
	
	where `ftsy_tabelle_2020`.`spieltag` = 34 
				or 	( `ftsy_tabelle_2020`.`spieltag` = (select `parameter`.`spieltag` - 1 from `parameter`) 
						and `ftsy_tabelle_2020`.`season_id` = (select `parameter`.`season_id` from `parameter`) 

	union all 

	/* Historic data from 2019 */

	select 	`u`.`id` AS `user_id`
					,`u`.`teamname` AS `team_name`
					,`ft19`.`punkte` AS `punkte`
					,2019 AS `season_id`
					,`ft19`.`score_for` AS `score_for`
					,`ft19`.`score_against` AS `score_against`
					,`ft19`.`siege` AS `siege`
					,`ft19`.`niederlagen` AS `niederlagen`
					,`ft19`.`unentschieden` AS `unentschieden`
					,`ft19`.`trost` AS `trost` 

	from	`fantasy_tabelle_2019` `ft19` 

	left join `users` `u` 
		on 	`u`.`username` = `ft19`.`player`

	where `ft19`.`spieltag` = 34 

	union all 

	/* Historic data from 2018 */

	select 	`u`.`id` AS `user_id`
					,coalesce(`u`.`teamname`,`ft18`.`team`) AS `team_name`
					,`ft18`.`punkte` AS `punkte`
					,2018 AS `season_id`
					,`ft18`.`score_for` AS `score_for`
					,`ft18`.`score_against` AS `score_against`
					,`ft18`.`siege` AS `siege`
					,`ft18`.`niederlagen` AS `niederlagen`
					,`ft18`.`unentschieden` AS `unentschieden`
					,`ft18`.`trost` AS `trost` 

	from 	`fantasy_tabelle_2018` `ft18` 

	left join `users` `u` 
		on 	`u`.`id` = `ft18`.`player`

	where `ft18`.`spieltag` = 34

	) `t` 

group by 	`t`.`user_id`,`t`.`team_name` 
order by sum(`t`.`punkte`) desc,sum(`t`.`siege`) desc,sum(`t`.`unentschieden`) desc,sum(`t`.`score_for`) desc