create view ftsy_pokalsieger_v as
select  
    `s`.`season_id` AS `season_id`
    , `s`.`season_name` AS `season_name`
    , `sch`.`buli_round_name` AS `buli_round_name`
    , `sch`.`ftsy_match_id` AS `ftsy_match_id`
    , case when `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` then `sch`.`ftsy_home_id` else `sch`.`ftsy_away_id` end AS `winner_user_id`
    , case when `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` then `sch`.`ftsy_home_name` else `sch`.`ftsy_away_name` end AS `winner_team_name`
    , case when `sch`.`ftsy_home_score` > `sch`.`ftsy_away_score` then `sch`.`ftsy_home_score` else `sch`.`ftsy_away_score` end AS `winner_score`
    , case when `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` then `sch`.`ftsy_home_id` else `sch`.`ftsy_away_id` end AS `looser_user_id`
    , case when `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` then `sch`.`ftsy_home_name` else `sch`.`ftsy_away_name` end AS `looser_team_name`
    , case when `sch`.`ftsy_home_score` < `sch`.`ftsy_away_score` then `sch`.`ftsy_home_score` else `sch`.`ftsy_away_score` end AS `looser_score` 
from `ftsy_schedule` `sch` 
inner join `xa7580_db1`.`sm_seasons` `s` 
    on `sch`.`season_id` = `s`.`season_id` 
where 
    `sch`.`match_type` = 'cup' 
    and `sch`.`cup_round` = 'final' 
    and `sch`.`ftsy_home_score` + `sch`.`ftsy_away_score` > 0 
order by `s`.`season_id` desc