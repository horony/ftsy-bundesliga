/* Creates view with detailed stats and ftsy score for each player in each fixture he was on a team */

create view ftsy_scoring_all_v as

select 	`stats`.`round_name` AS `round_name`
				,`stats`.`fixture_id` AS `fixture_id`
				,`stats`.`player_id` AS `player_id`
				,`player`.`display_name` AS `player_name`
				,`teams_own`.`short_code` AS `own_team_code`
				, `teams_opp`.`short_code` AS `opp_team_code`

				/* Overall ftsy score */
				,	coalesce(round((`rules`.`appearance` * `stats`.`appearance`),1),0) 
					+ coalesce(round((`rules`.`minutes_played` * `rules`.`minutes_played`),1),0) 
					+ coalesce(round((`rules`.`captain` * `stats`.`captain`),1),0)
					+ coalesce(round((`rules`.`clean_sheet` * `stats`.`clean_sheet`),1),0)
					+ coalesce(round((`rules`.`goals_made` * `stats`.`goals_minus_pen`),1),0)
					+ coalesce(round((`rules`.`penalties_made` * `stats`.`pen_scored`),1),0)
					+ coalesce(round((`rules`.`assists` * `stats`.`assists`),1),0)
					+ coalesce(round((`rules`.`owngoals` * `stats`.`owngoals`),1),0)
					+ coalesce(round((`rules`.`redcards` * `stats`.`redcards`),1),0)
					+ coalesce(round((`rules`.`yellowredcards` * `stats`.`yellowredcards`),1),0)
					+ coalesce(round((`rules`.`dribble_attempts` * `stats`.`dribble_attempts`),1),0)
					+ coalesce(round((`rules`.`dribble_success` * `stats`.`dribbles_success`),1),0)
					+ coalesce(round((`rules`.`dribble_fail` * `stats`.`dribbles_failed`),1),0)
					+ coalesce(round((`rules`.`dribbled_past` * `stats`.`dribbled_past`),1),0)
					+ coalesce(round((`rules`.`duels_total` * `stats`.`duels_total`),1),0) 
					+ coalesce(round((`rules`.`duels_won` * `stats`.`duels_won`),1),0)
					+ coalesce(round((`rules`.`duels_lost` * `stats`.`duels_lost`),1),0)
					+ coalesce(round((`rules`.`fouls_drawn` * `stats`.`fouls_drawn`),1),0)
					+ coalesce(round((`rules`.`fouls_committed` * `stats`.`fouls_committed`),1),0)
					+ coalesce(round((`rules`.`shots_total` * `stats`.`shots_total`),1),0)
					+ coalesce(round((`rules`.`shots_on_goal` * `stats`.`shots_on_goal`),1),0)
					+ coalesce(round((`rules`.`shots_missed` * `stats`.`shots_missed`),1),0)
					+ coalesce(round((`rules`.`shots_on_goal_saved` * `stats`.`shots_on_goal_saved`),1),0)
					+ coalesce(round((`rules`.`crosses_total` * `stats`.`crosses_total`),1),0)
					+ coalesce(round((`rules`.`crosses_complete` * `stats`.`crosses_complete`),1),0)
					+ coalesce(round((`rules`.`crosses_incomplete` * `stats`.`crosses_incomplete`),1),0)
					+ coalesce(round((`rules`.`passes_total` * `stats`.`passes_total`),1),0)
					+ coalesce(round((`rules`.`passes_complete` * `stats`.`passes_complete`),1),0)
					+ coalesce(round((`rules`.`passes_incomplete` * `stats`.`passes_incomplete`),1),0)
					+ coalesce(round((`rules`.`passes_key` * `stats`.`key_passes`),1),0)
					+ coalesce(round((`rules`.`clearances` * `stats`.`clearances`),1),0)
					+ coalesce(round((`rules`.`dispossessed` * `stats`.`dispossessed`),1),0)
					+ coalesce(round((`rules`.`hit_woodwork` * `stats`.`hit_woodwork`),1),0)
					+ coalesce(round((`rules`.`interceptions` * `stats`.`interceptions`),1),0)
					+ coalesce(round((`rules`.`inside_box_saves` * `stats`.`inside_box_saves`),1),0)
					+ coalesce(round((`rules`.`saves` * `stats`.`saves`),1),0)
					+ coalesce(round((`rules`.`offsides` * `stats`.`offsides`),1),0)
					+ coalesce(round((`rules`.`pen_committed` * `stats`.`pen_committed`),1),0)
					+ coalesce(round((`rules`.`pen_missed` * `stats`.`pen_missed`),1),0)
					+ coalesce(round((`rules`.`pen_won` * `stats`.`pen_won`),1),0)
					+ coalesce(round((`rules`.`pen_saved` * `stats`.`pen_saved`),1),0)
					+ coalesce(round((`rules`.`tackles` * `stats`.`tackles`),1),0)
					+ coalesce(round((`rules`.`outside_box_saves` * `stats`.`outside_box_saves`),1),0)
					AS `ftsy_score`

				/* Ftsy scores for specific actions*/

				/* Playing time */
				,round((`rules`.`appearance` * `stats`.`appearance`),1) AS `appearance_ftsy`
				,`stats`.`appearance` AS `appearance_stat`
				,round((`rules`.`captain` * `stats`.`captain`),1) AS `captain_ftsy`
				,`stats`.`captain` AS `captain_stats`
				,round((`rules`.`minutes_played` * `stats`.`minutes_played`),1) AS `minutes_played_ftsy`
				,`stats`.`minutes_played` AS `minutes_played_stat`

				/* Clean sheet */
				,round((`rules`.`clean_sheet` * `stats`.`clean_sheet`),1) AS `clean_sheet_ftsy`
				,`stats`.`clean_sheet` AS `clean_sheet_stat`

				/* Goal participation */
				,round((`rules`.`goals_made` * `stats`.`goals_minus_pen`),1) AS `goals_made_ftsy`
				,`stats`.`goals_minus_pen` AS `goals_made_stat`
				,round((`rules`.`penalties_made` * `stats`.`pen_scored`),1) AS `penalties_made_ftsy`
				,`stats`.`pen_scored` AS `penalties_made_stat`
				,round((`rules`.`assists` * `stats`.`assists`),1) AS `assists_made_ftsy`
				,`stats`.`assists` AS `assists_made_stat`
				,round((`rules`.`owngoals` * `stats`.`owngoals`),1) AS `owngoals_ftsy`
				,`stats`.`owngoals` AS `owngoals_stat`

				/* Cards */
				,round((`rules`.`redcards` * `stats`.`redcards`),1) AS `redcards_ftsy`
				,`stats`.`redcards` AS `redcards_stat`
				,round((`rules`.`yellowredcards` * `stats`.`yellowredcards`),1) AS `yellowredcards_ftsy`
				,`stats`.`yellowredcards` AS `yellowredcards_stat`

				/* Duels */
				,round((`rules`.`dribble_attempts` * `stats`.`dribble_attempts`),1) AS `dribble_attempts_ftsy`
				,`stats`.`dribble_attempts` AS `dribble_attempts_stat`
				,round((`rules`.`dribble_success` * `stats`.`dribbles_success`),1) AS `dribble_success_ftsy`
				,`stats`.`dribbles_success` AS `dribble_success_stat`
				,round((`rules`.`dribble_fail` * `stats`.`dribbles_failed`),1) AS `dribble_fail_ftsy`
				,`stats`.`dribbles_failed` AS `dribble_fail_stat`
				,round((`rules`.`dribbled_past` * `stats`.`dribbled_past`),1) AS `dribbled_past_ftsy`
				,`stats`.`dribbled_past` AS `dribbled_past_stat`
				,round((`rules`.`duels_total` * `stats`.`duels_total`),1) AS `duels_total_ftsy`
				,`stats`.`duels_total` AS `duels_total_stat`
				,round((`rules`.`duels_won` * `stats`.`duels_won`),1) AS `duels_won_ftsy`
				,`stats`.`duels_won` AS `duels_won_stat`
				,round((`rules`.`duels_lost` * `stats`.`duels_lost`),1) AS `duels_lost_ftsy`
				,`stats`.`duels_lost` AS `duels_lost_stat`
				,round((`rules`.`fouls_drawn` * `stats`.`fouls_drawn`),1) AS `fouls_drawn_ftsy`
				,`stats`.`fouls_drawn` AS `fouls_drawn_stat`
				,round((`rules`.`fouls_committed` * `stats`.`fouls_committed`),1) AS `fouls_committed_ftsy`
				,`stats`.`fouls_committed` AS `fouls_committed_stat`

				/* Shots */
				,round((`rules`.`shots_total` * `stats`.`shots_total`),1) AS `shots_total_ftsy`
				,`stats`.`shots_total` AS `shots_total_stat`
				,round((`rules`.`shots_on_goal` * `stats`.`shots_on_goal`),1) AS `shots_on_goal_ftsy`
				,`stats`.`shots_on_goal` AS `shots_on_goal_stat`
				,round((`rules`.`shots_missed` * `stats`.`shots_missed`),1) AS `shots_missed_ftsy`
				,`stats`.`shots_missed` AS `shots_missed_stat`
				,round((`rules`.`shots_on_goal_saved` * `stats`.`shots_on_goal_saved`),1) AS `shots_on_goal_saved_ftsy`
				,`stats`.`shots_on_goal_saved` AS `shots_on_goal_saved_stat`

				/* Passes */
				,round((`rules`.`crosses_total` * `stats`.`crosses_total`),1) AS `crosses_total_ftsy`
				,`stats`.`crosses_total` AS `crosses_total_stat`
				,round((`rules`.`crosses_complete` * `stats`.`crosses_complete`),1) AS `crosses_complete_ftsy`
				,`stats`.`crosses_complete` AS `crosses_complete_stat`
				,round((`rules`.`crosses_incomplete` * `stats`.`crosses_incomplete`),1) AS `crosses_incomplete_ftsy`
				,`stats`.`crosses_incomplete` AS `crosses_incomplete_stat`
				,round((`rules`.`passes_total` * `stats`.`passes_total`),1) AS `passes_total_ftsy`
				,`stats`.`passes_total` AS `passes_total_stat`
				,round((`rules`.`passes_complete` * `stats`.`passes_complete`),1) AS `passes_complete_ftsy`
				,`stats`.`passes_complete` AS `passes_complete_stat`
				,round((`rules`.`passes_incomplete` * `stats`.`passes_incomplete`),1) AS `passes_incomplete_ftsy`
				,`stats`.`passes_incomplete` AS `passes_incomplete_stat`
				,round((`rules`.`passes_key` * `stats`.`key_passes`),1) AS `passes_key_ftsy`
				,`stats`.`key_passes` AS `passes_key_stat`

				/* Misc */
				,round((`rules`.`blocks` * `stats`.`blocks`),1) AS `blocks_ftsy`
				,`stats`.`blocks` AS `blocks_stat`
				,round((`rules`.`clearances` * `stats`.`clearances`),1) AS `clearances_ftsy`
				,`stats`.`clearances` AS `clearances_stat`
				,round((`rules`.`dispossessed` * `stats`.`dispossessed`),1) AS `dispossessed_ftsy`
				,`stats`.`dispossessed` AS `dispossessed_stat`
				,round((`rules`.`hit_woodwork` * `stats`.`hit_woodwork`),1) AS `hit_woodwork_ftsy`
				,`stats`.`hit_woodwork` AS `hit_woodwork_stat`
				,round((`rules`.`interceptions` * `stats`.`interceptions`),1) AS `interceptions_ftsy`
				,`stats`.`interceptions` AS `interceptions_stat`
				,round((`rules`.`offsides` * `stats`.`offsides`),1) AS `offsides_ftsy`
				,`stats`.`offsides` AS `offsides_stat`
				,round((`rules`.`tackles` * `stats`.`tackles`),1) AS `tackles_ftsy`
				,`stats`.`tackles` AS `tackles_stat`

				/* Goal keeping */
				,round((`rules`.`inside_box_saves` * `stats`.`inside_box_saves`),1) AS `inside_box_saves_ftsy`
				,`stats`.`inside_box_saves` AS `inside_box_saves_stat`
				,round((`rules`.`outside_box_saves` * `stats`.`outside_box_saves`),1) AS `outside_box_saves_ftsy`
				,`stats`.`outside_box_saves` AS `outside_box_saves_stat`
				,round((`rules`.`saves` * `stats`.`saves`),1) AS `saves_ftsy`
				,`stats`.`saves` AS `saves_stat`
				
				/* Penalties */
				,round((`rules`.`pen_committed` * `stats`.`pen_committed`),1) AS `pen_committed_ftsy`
				,`stats`.`pen_committed` AS `pen_committed_stat`
				,round((`rules`.`pen_missed` * `stats`.`pen_missed`),1) AS `pen_missed_ftsy`
				,`stats`.`pen_missed` AS `pen_missed_stat`
				,round((`rules`.`pen_saved` * `stats`.`pen_saved`),1) AS `pen_saved_ftsy`
				,`stats`.`pen_saved` AS `pen_saved_stat`
				,round((`rules`.`pen_won` * `stats`.`pen_won`),1) AS `pen_won_ftsy`
				,`stats`.`pen_won` AS `pen_won_stat` 

from 	`sm_player_stats` `stats` 

/* Join the players own team for team info */
left join `sm_teams` `teams_own` 
	on 	`teams_own`.`id` = `stats`.`own_team_id`

/* Join the opposing team for team info */
left join `sm_teams` `teams_opp` 
	on 	`teams_opp`.`id` = `stats`.`opp_team_id`

/* Join playerbase which defines a players position */
left join `sm_playerbase` `player`
	on 	`player`.`id` = `stats`.`player_id` 

/* Join the scoring rule set, which defines which position get how many points for a given stat */
left join `ftsy_scoring_ruleset` `rules` 
	on	`player`.`position_short` = `rules`.`player_position_de_short`