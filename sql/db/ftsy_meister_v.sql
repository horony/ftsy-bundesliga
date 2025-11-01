CREATE VIEW ftsy_meister_v AS 

/* Data FROM prod TABLE */
SELECT  
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
FROM `ftsy_tabelle_2020` `tab` 
INNER JOIN `sm_seasons` `s` 
    ON `tab`.`season_id` = `s`.`season_id`
WHERE 
    `tab`.`spieltag` = 34 
    AND `tab`.`rang` = 1 

UNION ALL 

/* Historic data FROM 2019 */
SELECT 
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
FROM `fantasy_tabelle_2019` `tab` 
WHERE 
    `tab`.`spieltag` = 34 
    AND `tab`.`rang` = 1 

UNION ALL 

/* Historic data FROM 2018 */
SELECT 
    2018 AS `season_id`
    ,'2018/2019' AS `season_name`
    ,`u`.`id` AS `player_id`
    ,COALESCE(`u`.`teamname`,`tab`.`team`) AS `team_name`
    ,`tab`.`punkte` AS `punkte`
    ,`tab`.`score_for` AS `score_for`
    ,`tab`.`score_against` AS `score_against`
    ,`tab`.`siege` AS `siege`
    ,`tab`.`niederlagen` AS `niederlagen`
    ,`tab`.`unentschieden` AS `unentschieden`
    ,`tab`.`trost` AS `trost` 
FROM `fantasy_tabelle_2018` `tab` 
LEFT JOIN `xa7580_db1`.`users` `u` 
    ON `u`.`id` = `tab`.`player`
WHERE 
    `tab`.`spieltag` = 34 
    AND `tab`.`rang` = 1 
ORDER BY `season_id` DESC