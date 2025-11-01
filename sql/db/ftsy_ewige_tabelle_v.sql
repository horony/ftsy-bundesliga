CREATE VIEW ftsy_ewige_tabelle_v AS
SELECT    
    `t`.`user_id` AS `user_id`
    ,`t`.`team_name` AS `team_name`
    ,SUM(`t`.`punkte`) AS `sum_punkte`
    ,COUNT(DISTINCT `t`.`season_id`) AS `anz_saisons`
    ,SUM(`t`.`siege` + `t`.`niederlagen` + `t`.`unentschieden`) AS `anz_spiele`
    ,SUM(`t`.`score_for`) AS `sum_score_for`,SUM(`t`.`score_against`) AS `sum_score_agains`
    ,SUM(`t`.`siege`) AS `anz_siege`
    ,SUM(`t`.`niederlagen`) AS `anz_niederlagen`
    ,SUM(`t`.`unentschieden`) AS `anz_unentschieden`
    ,SUM(`t`.`trost`) AS `anz_trost` 
FROM (
    /* Data FROM the prod TABLE */
    SELECT 
        `f`.`player_id` AS `user_id`
        , `u`.`teamname` AS `team_name`
        , `f`.`punkte` AS `punkte`
        , `f`.`season_id` AS `season_id`
        , `f`.`score_for` AS `score_for`
        , `f`.`score_against` AS `score_against`
        , `f`.`siege` AS `siege`
        , `f`.`niederlagen` AS `niederlagen`
        , `f`.`unentschieden` AS `unentschieden`
        , `f`.`trost` AS `trost` 
    FROM `ftsy_tabelle_2020` `f`
    LEFT JOIN `users` `u` 
        ON `u`.`id` = `f`.`player_id`
    WHERE 
        `f`.`spieltag` = 34 
        OR (`f`.`spieltag` = (SELECT `parameter`.`spieltag` - 1 FROM `parameter`) 
        AND `f`.`season_id` = (SELECT `parameter`.`season_id` FROM `parameter`))

    UNION ALL 

    /* Historic data FROM 2019 */
    SELECT  
        `u`.`id` AS `user_id`
        ,`u`.`teamname` AS `team_name`
        ,`ft19`.`punkte` AS `punkte`
        ,2019 AS `season_id`
        ,`ft19`.`score_for` AS `score_for`
        ,`ft19`.`score_against` AS `score_against`
        ,`ft19`.`siege` AS `siege`
        ,`ft19`.`niederlagen` AS `niederlagen`
        ,`ft19`.`unentschieden` AS `unentschieden`
        ,`ft19`.`trost` AS `trost` 
    FROM `fantasy_tabelle_2019` `ft19` 
    LEFT JOIN `users` `u` 
        ON `u`.`username` = `ft19`.`player`
    WHERE 
        `ft19`.`spieltag` = 34 

    UNION ALL 

    /* Historic data FROM 2018 */
    SELECT  
        `u`.`id` AS `user_id`
        ,COALESCE(`u`.`teamname`,`ft18`.`team`) AS `team_name`
        ,`ft18`.`punkte` AS `punkte`
        ,2018 AS `season_id`
        ,`ft18`.`score_for` AS `score_for`
        ,`ft18`.`score_against` AS `score_against`
        ,`ft18`.`siege` AS `siege`
        ,`ft18`.`niederlagen` AS `niederlagen`
        ,`ft18`.`unentschieden` AS `unentschieden`
        ,`ft18`.`trost` AS `trost` 
    FROM `fantasy_tabelle_2018` `ft18` 
    LEFT JOIN `users` `u` 
        ON `u`.`id` = `ft18`.`player`
    WHERE 
        `ft18`.`spieltag` = 34
    ) `t` 
GROUP BY `t`.`user_id`,`t`.`team_name` 
ORDER BY SUM(`t`.`punkte`) DESC, SUM(`t`.`siege`) DESC, SUM(`t`.`unentschieden`) DESC, SUM(`t`.`score_for`) DESC