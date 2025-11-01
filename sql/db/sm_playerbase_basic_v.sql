/* Creates VIEW holding some basic information ON players (birth_dt, number, name, team, ftsy team etc.) */

CREATE VIEW sm_playerbase_basic_v AS 
SELECT	
    `base`.`id` AS `id`
    ,`base`.`common_name` AS `common_name`
    ,`base`.`display_name` AS `display_name`
    ,`base`.`lastname` AS `lastname`
    ,`base`.`number` AS `number`
    ,`base`.`position_short` AS `position_short`
    ,`base`.`position_long` AS `position_long`
    ,`base`.`captain` AS `captain`
    ,`base`.`injured` AS `injured`
    ,`base`.`injury_reason` AS `injury_reason`
    ,`base`.`is_suspended` AS `is_suspended`
    ,`base`.`is_sidelined` AS `is_sidelined`
    ,`base`.`current_team_id` AS `current_team_id`
    ,`base`.`image_path` AS `image_path`
    ,`base`.`height` AS `height`
    ,`base`.`weight` AS `weight`
    ,`base`.`birthcountry` AS `birthcountry`
    ,`base`.`birthplace` AS `birthplace`
    ,`base`.`birth_dt` AS `birth_dt`
    /* Infos on the players current team */
    ,`team`.`id` AS `team_id`
    ,`team`.`name` AS `name`
    ,`team`.`short_code` AS `short_code`
    ,`team`.`logo_path` AS `logo_path`
    /* Infos on the user owning the player in a ftsy league */
    ,`own`.`player_id` AS `player_id`
    ,`own`.`player_name` AS `player_name`
    ,`own`.`1_ftsy_owner_type` AS `1_ftsy_owner_type`
    ,`own`.`1_ftsy_owner_id` AS `1_ftsy_owner_id`
    ,`own`.`1_ftsy_match_status` AS `1_ftsy_match_status`
    ,`own`.`2_ftsy_owner_type` AS `2_ftsy_owner_type`
    ,`own`.`2_ftsy_owner_id` AS `2_ftsy_owner_id`
    ,`own`.`2_ftsy_match_status` AS `2_ftsy_match_status` 
FROM `sm_playerbase` `base` 
LEFT JOIN `sm_teams` `team` 
    ON `team`.`id` = `base`.`current_team_id`
LEFT JOIN `ftsy_player_ownership` `own` 
    ON `own`.`player_id` = `base`.`id`