create view ftsy_meister_v as 

/* Data from prod table */
select  
    `tab`.`season_id` AS `season_id`
    ,`s`.`season_name` AS `season_name`
    ,`tab`.`player_id` AS `player_id`
    ,`tab`.`team_name` AS `team_name`
    ,`tab`.`punkte` AS `punkte`
    ,`tab`.`score_for` AS `score_for`
    ,`tab`.`score_against` AS `score_against`
    ,`tab`.`siege` AS `siege`
    ,`tab`.`niederlagen` AS `niederlagen`
    ,`tab`.`unentschieden` AS `unentschieden`
    ,`tab`.`trost` AS `trost` 
from `ftsy_tabelle_2020` `tab` 
inner join `sm_seasons` `s` 
    on `tab`.`season_id` = `s`.`season_id`
where 
    `tab`.`spieltag` = 34 
    and `tab`.`rang` = 1 

union all 

/* Historic data from 2019 */
select 
    2019 AS `season_id`
    ,'2019/2020' AS `season_name`
    ,4 AS `player_id`
    ,`tab`.`team` AS `team_name`
    ,`tab`.`punkte` AS `punkte`
    ,`tab`.`score_for` AS `score_for`
    ,`tab`.`score_against` AS `score_against`
    ,`tab`.`siege` AS `siege`
    ,`tab`.`niederlagen` AS `niederlagen`
    ,`tab`.`unentschieden` AS `unentschieden`
    ,`tab`.`trost` AS `trost` 
from `fantasy_tabelle_2019` `tab` 
where 
    `tab`.`spieltag` = 34 
    and `tab`.`rang` = 1 

union all 

/* Historic data from 2018 */
select 
    2018 AS `season_id`
    ,'2018/2019' AS `season_name`
    ,`u`.`id` AS `player_id`
    ,coalesce(`u`.`teamname`,`tab`.`team`) AS `team_name`
    ,`tab`.`punkte` AS `punkte`
    ,`tab`.`score_for` AS `score_for`
    ,`tab`.`score_against` AS `score_against`
    ,`tab`.`siege` AS `siege`
    ,`tab`.`niederlagen` AS `niederlagen`
    ,`tab`.`unentschieden` AS `unentschieden`
    ,`tab`.`trost` AS `trost` 
from `fantasy_tabelle_2018` `tab` 
left join `xa7580_db1`.`users` `u` 
    on `u`.`id` = `tab`.`player`
where 
    `tab`.`spieltag` = 34 
    and `tab`.`rang` = 1 
order by `season_id` desc