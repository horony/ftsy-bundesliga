create view ftsy_scoring_projection_v as 

select  `sm_playerbase`.`id` AS `player_id`
        ,`sm_playerbase`.`display_name` AS `display_name`

        /* Collect ftsy scores that are needed for calculation of projection */
        ,coalesce(`ftsy_scoring_snap`.`ftsy_score_avg`,0) AS `ftsy_score_avg`
        ,coalesce(`ftsy_scoring_snap`.`ftsy_score_avg_last_5`,0) AS `ftsy_score_avg_last_5`
        ,coalesce(`ftsy_scoring_snap`.`ftsy_score_avg_last_3`,0) AS `ftsy_score_avg_last_3`
        ,coalesce(`ftsy_scoring_snap`.`ftsy_score_last`,0) AS `ftsy_score_last`
        ,coalesce(`ftsy_points_allowed`.`avg_allowed`,0) AS `avg_allowed`
        ,`sm_playerbase`.`injured` AS `injured`
        ,`sm_playerbase`.`is_suspended` AS `is_suspended`
        ,`sm_playerbase`.`is_sidelined` AS `is_sidelined`

        /* Calculate projection */
        ,coalesce(
          case  when `parameter_projection`.`faktor_player_active` = 1 and `sm_playerbase`.`is_sidelined` = 1 
                  then 0 

                else 
                  round(`ftsy_scoring_snap`.`ftsy_score_avg_last_5` * `parameter_projection`.`faktor_ftsy_points_last_5` 
                    + `ftsy_scoring_snap`.`ftsy_score_avg_last_3` * `parameter_projection`.`faktor_ftsy_points_last_3` 
                    + `ftsy_scoring_snap`.`ftsy_score_last` * `parameter_projection`.`faktor_ftsy_points_last` 
                    + `ftsy_scoring_snap`.`ftsy_score_avg` * `parameter_projection`.`faktor_ftsy_poinst_season` 
                    + `ftsy_points_allowed`.`avg_allowed` * `parameter_projection`.`faktor_ftsy_points_pos_opponent`,1) 
                
                end
                ,0) AS `ftsy_score_projected` 

from `sm_playerbase` 

/* Join parameters, which define the weight each stat adds to the projection */
inner join `parameter_projection` 
  on  (1 = 1)

/* Join information on current opponent per team */
inner join `sm_fixture_per_team_akt_v` 
  on  `sm_fixture_per_team_akt_v`.`team_id` = `sm_playerbase`.`current_team_id`

/* Join information on how much ftsy points current opponent allows to players position */
inner join `ftsy_points_allowed` 
  on  `ftsy_points_allowed`.`opp_team_id` = `sm_fixture_per_team_akt_v`.`opp_id` 
      and `ftsy_points_allowed`.`position_short` = `sm_playerbase`.`position_short` 

/* Join snaphot with information on players avg scores over the past few games */
inner join `ftsy_scoring_snap` 
  on  `ftsy_scoring_snap`.`id` = `sm_playerbase`.`id`

inner join `ftsy_player_ownership` 
  on  `ftsy_player_ownership`.`player_id` = `sm_playerbase`.`id`

where `sm_playerbase`.`rostered` = 1