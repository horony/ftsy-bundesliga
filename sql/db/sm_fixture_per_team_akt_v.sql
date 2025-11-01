/* Creates view with information on the current fixture for every Bundesliga team (total of 18 rows) */

CREATE VIEW sm_fixture_per_team_akt_v AS 
SELECT 
    /* Team */
    `base`.`team_id` AS `team_id`
    , `team`.`short_code` AS `team_code`
    , `team`.`name` AS `team_name`
    /* Opposing team */
    , `base`.`opp_id` AS `opp_id`
    , `opp`.`short_code` AS `opp_code`
    , `opp`.`name` AS `opp_name`
    /* Kickoff */
    , `base`.`kickoff_dt` AS `kickoff_dt`
    , `base`.`kickoff_ts` AS `kickoff_ts`
    , NULL AS `kickoff_time`
    , NULL AS `minute`
    , CASE 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 1) THEN 'Sonntag' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 2) THEN 'Montag' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 3) THEN 'Dienstag' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 4) THEN 'Mittwoch' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 5) THEN 'Donnerstag' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 6) THEN 'Freitag' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 7) THEN 'Samstag' 
        END AS `kickoff_day_long`
    , CASE  
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 1) THEN 'So' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 2) THEN 'Mo' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 3) THEN 'Di' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 4) THEN 'Mi' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 5) THEN 'Do' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 6) THEN 'Fr' 
        WHEN (DAYOFWEEK(`base`.`kickoff_dt`) = 7) THEN 'Sa'
        END AS `kickoff_day_short`
    /* Match status */
    , `base`.`match_status` AS `match_status`
    , `base`.`goals_for` AS `goals_for`
    , `base`.`goals_against` AS `goals_against`
    , CASE
        WHEN (`base`.`goals_for` > `base`.`goals_against`) THEN 'S' 
        WHEN (`base`.`goals_for` = `base`.`goals_against`) THEN 'U' 
        WHEN (`base`.`goals_for` < `base`.`goals_against`) THEN 'N' 
        END AS `match_result` 
FROM (
    /* Home teams */
    SELECT 	
        `sm_fixtures`.`localteam_id` AS `team_id`
        , `sm_fixtures`.`visitorteam_id` AS `opp_id`
        , `sm_fixtures`.`kickoff_dt` AS `kickoff_dt`
        , `sm_fixtures`.`kickoff_ts` AS `kickoff_ts`
        , `sm_fixtures`.`match_status` AS `match_status`
        , `sm_fixtures`.`localteam_score` AS `goals_for`
        , `sm_fixtures`.`visitorteam_score` AS `goals_against` 
    FROM `sm_fixtures` 
    WHERE 	
        `sm_fixtures`.`round_name` = (SELECT `parameter`.`spieltag` FROM `parameter`) 
        AND `sm_fixtures`.`season_id` = (SELECT `parameter`.`season_id` FROM `parameter`)

    UNION ALL

    /* Visitor teams */
    SELECT
        `sm_fixtures`.`visitorteam_id` AS `team_id`
        , `sm_fixtures`.`localteam_id` AS `opp_id`
        , `sm_fixtures`.`kickoff_dt` AS `kickoff_dt`
        , `sm_fixtures`.`kickoff_ts` AS `kickoff_ts`
        , `sm_fixtures`.`match_status` AS `match_status`
        , `sm_fixtures`.`visitorteam_score` AS `goals_for`
        , `sm_fixtures`.`localteam_score` AS `goals_against`
    FROM `sm_fixtures` 
    WHERE
        `sm_fixtures`.`round_name` = (SELECT `parameter`.`spieltag` FROM `parameter`)
        AND `sm_fixtures`.`season_id` = (SELECT `parameter`.`season_id` FROM `parameter`)
    ) `base` 
INNER JOIN `sm_teams` `team` 
    ON 	`base`.`team_id` = `team`.`id`
INNER JOIN `sm_teams` `opp`
    ON 	`base`.`opp_id` = `opp`.`id`