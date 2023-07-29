/* Creates view with detailed stats and ftsy score for each player in each fixture he was on a team */

create view ftsy_scoring_all_v as

with ftsy_stats as (
	select 	stats.round_name AS round_name
					,stats.fixture_id
					,stats.player_id
					,player.display_name as player_name
					,player.position_short
					,teams_own.short_code AS own_team_code
					,teams_opp.short_code AS opp_team_code

					/* Playing time */
					,round((rules.appearance * stats.appearance),1) AS appearance_ftsy
					,stats.appearance AS appearance_stat
					,round((rules.minutes_played * stats.minutes_played),1) AS minutes_played_ftsy
					,stats.minutes_played AS minutes_played_stat

					/* Goal participation */
					,round((rules.goals_total * stats.goals_total),1) AS goals_total_ftsy
					,stats.goals_total AS goals_total_stat
					,round((rules.goals_minus_pen * stats.goals_minus_pen),1) AS goals_minus_pen_ftsy
					,stats.goals_minus_pen AS goals_minus_pen_stat
					,round((rules.assists * stats.assists),1) AS assists_ftsy
					,stats.assists AS assists_stat
					,round((rules.big_chances_created * stats.big_chances_created),1) AS big_chances_created_ftsy
					,stats.big_chances_created AS big_chances_created_stat
					,round((rules.key_passes * stats.key_passes),1) AS key_passes_ftsy
					,stats.key_passes AS key_passes_stat

					/* Passes */
					
					,round((rules.passes_total * stats.passes_total),1) AS passes_total_ftsy
					,stats.passes_total AS passes_total_stat
					,round((rules.passes_complete * stats.passes_complete),1) AS passes_complete_ftsy
					,stats.passes_complete AS passes_complete_stat
					,round((rules.passes_incomplete * stats.passes_incomplete),1) AS passes_incomplete_ftsy
					,stats.passes_incomplete AS passes_incomplete_stat				
					,round((rules.passes_accuracy * stats.passes_accuracy),1) AS passes_accuracy_ftsy
					,stats.passes_accuracy AS passes_accuracy_stat	

					,round((rules.crosses_total * stats.crosses_total),1) AS crosses_total_ftsy
					,stats.crosses_total AS crosses_total_stat
					,round((rules.crosses_complete * stats.crosses_complete),1) AS crosses_complete_ftsy
					,stats.crosses_complete AS crosses_complete_stat
					,round((rules.crosses_incomplete * stats.crosses_incomplete),1) AS crosses_incomplete_ftsy
					,stats.crosses_incomplete AS crosses_incomplete_stat

					/* Shots */
					,round((rules.shots_total * stats.shots_total),1) AS shots_total_ftsy
					,stats.shots_total AS shots_total_stat
					,round((rules.shots_on_goal * stats.shots_on_goal),1) AS shots_on_goal_ftsy
					,stats.shots_on_goal AS shots_on_goal_stat
					,round((rules.shots_missed * stats.shots_missed),1) AS shots_missed_ftsy
					,stats.shots_missed AS shots_missed_stat
					,round((rules.shots_blocked * stats.shots_blocked),1) AS shots_blocked_ftsy
					,stats.shots_blocked AS shots_blocked_stat
					,round((rules.big_chances_missed * stats.big_chances_missed),1) AS big_chances_missed_ftsy
					,stats.big_chances_missed AS big_chances_missed_stat
					,round((rules.hit_woodwork * stats.hit_woodwork),1) AS hit_woodwork_ftsy
					,stats.hit_woodwork AS hit_woodwork_stat

					/* Penalties */
					,round((rules.pen_committed * stats.pen_committed),1) AS pen_committed_ftsy
					,stats.pen_committed AS pen_committed_stat
					,round((rules.pen_missed * stats.pen_missed),1) AS pen_missed_ftsy
					,stats.pen_missed AS pen_missed_stat
					,round((rules.pen_saved * stats.pen_saved),1) AS pen_saved_ftsy
					,stats.pen_saved AS pen_saved_stat
					,round((rules.pen_scored * stats.pen_scored),1) AS pen_scored_ftsy
					,stats.pen_scored AS pen_scored_stat				
					,round((rules.pen_won * stats.pen_won),1) AS pen_won_ftsy
					,stats.pen_won AS pen_won_stat 

					/* Duels */
					,round((rules.duels_total * stats.duels_total),1) AS duels_total_ftsy
					,stats.duels_total AS duels_total_stat
					,round((rules.duels_won * stats.duels_won),1) AS duels_won_ftsy
					,stats.duels_won AS duels_won_stat
					,round((rules.duels_lost * stats.duels_lost),1) AS duels_lost_ftsy
					,stats.duels_lost AS duels_lost_stat				

					,round((rules.dribble_attempts * stats.dribble_attempts),1) AS dribble_attempts_ftsy
					,stats.dribble_attempts AS dribble_attempts_stat
					,round((rules.dribbles_success * stats.dribbles_success),1) AS dribbles_success_ftsy
					,stats.dribbles_success AS dribbles_success_stat
					,round((rules.dribbles_failed * stats.dribbles_failed),1) AS dribbles_failed_ftsy
					,stats.dribbles_failed AS dribbles_failed_stat

					/* Clean sheet */
					,round((rules.clean_sheet * stats.clean_sheet),1) AS clean_sheet_ftsy
					,stats.clean_sheet AS clean_sheet_stat
					,round((rules.goals_conceded * stats.goals_conceded),1) AS goals_conceded_ftsy
					,stats.goals_conceded AS goals_conceded_stat
					,round((rules.goalkeeper_goals_conceded * stats.goalkeeper_goals_conceded),1) AS goalkeeper_goals_conceded_ftsy
					,stats.goalkeeper_goals_conceded AS goalkeeper_goals_conceded_stat

					/* Defensive */
					,round((rules.interceptions * stats.interceptions),1) AS interceptions_ftsy
					,stats.interceptions AS interceptions_stat
					,round((rules.blocks * stats.blocks),1) AS blocks_ftsy
					,stats.blocks AS blocks_stat
					,round((rules.clearances * stats.clearances),1) AS clearances_ftsy
					,stats.clearances AS clearances_stat
					,round((rules.clearance_offline * stats.clearance_offline),1) AS clearances_offline_ftsy
					,stats.clearance_offline AS clearances_offline_stat
					,round((rules.tackles * stats.tackles),1) AS tackles_ftsy
					,stats.tackles AS tackles_stat

					/* Errors */				
					,round((rules.error_lead_to_goal * stats.error_lead_to_goal),1) AS error_lead_to_goal_ftsy
					,stats.error_lead_to_goal AS error_lead_to_goal_stat		
					,round((rules.owngoals * stats.owngoals),1) AS owngoals_ftsy
					,stats.owngoals AS owngoals_stat
					,round((rules.dispossessed * stats.dispossessed),1) AS dispossessed_ftsy
					,stats.dispossessed AS dispossessed_stat
					,round((rules.dribbled_past * stats.dribbled_past),1) AS dribbled_past_ftsy
					,stats.dribbled_past AS dribbled_past_stat

					/* Goal keeping */
					,round((rules.saves * stats.saves),1) AS saves_ftsy
					,stats.saves AS saves_stat
					,round((rules.inside_box_saves * stats.inside_box_saves),1) AS inside_box_saves_ftsy
					,stats.inside_box_saves AS inside_box_saves_stat
					,round((rules.outside_box_saves * stats.outside_box_saves),1) AS outside_box_saves_ftsy
					,stats.outside_box_saves AS outside_box_saves_stat
					,round((rules.punches * stats.punches),1) AS punches_ftsy
					,stats.punches AS punches_stat

					/* Cards */
					,round((rules.redcards * stats.redcards),1) AS redcards_ftsy
					,stats.redcards AS redcards_stat
					,round((rules.redyellowcards * stats.redyellowcards),1) AS redcyellowards_ftsy
					,stats.redyellowcards AS redyellowcards_stat
	
	from 	sm_player_stats stats 

	/* Join player information */
	left join sm_playerbase player
		on 	stats.player_id = player.id

	/* Join team information */
	left join sm_teams teams_own 
		on 	teams_own.id = stats.own_team_id

	left join sm_teams teams_opp
		on 	teams_opp.id = stats.opp_team_id

	/* Join rules to calculate ftsy scores according to players position */
	left join ftsy_scoring_ruleset rules 
		on 	player.position_short = rules.player_position_de_short

) 

select 	ftsy_stats.*
				, coalesce(appearance_ftsy,0)
					+ coalesce(minutes_played_ftsy,0)
					+ coalesce(goals_total_ftsy,0)
					+ coalesce(goals_minus_pen_ftsy,0)
					+ coalesce(assists_ftsy,0)
					+ coalesce(big_chances_created_ftsy,0)
					+ coalesce(key_passes_ftsy,0)
					+ coalesce(passes_total_ftsy,0)
					+ coalesce(passes_complete_ftsy,0)
					+ coalesce(passes_incomplete_ftsy,0)
					+ coalesce(passes_accuracy_ftsy,0)
					+ coalesce(crosses_total_ftsy,0)
					+ coalesce(crosses_complete_ftsy,0)
					+ coalesce(crosses_incomplete_ftsy,0)
					+ coalesce(shots_total_ftsy,0)
					+ coalesce(shots_on_goal_ftsy,0)
					+ coalesce(shots_missed_ftsy,0)
					+ coalesce(shots_blocked_ftsy,0)
					+ coalesce(big_chances_missed_ftsy,0)
					+ coalesce(hit_woodwork_ftsy,0)
					+ coalesce(pen_committed_ftsy,0)
					+ coalesce(pen_missed_ftsy,0)
					+ coalesce(pen_saved_ftsy,0)
					+ coalesce(pen_scored_ftsy,0)
					+ coalesce(pen_won_ftsy,0)
					+ coalesce(duels_total_ftsy,0)
					+ coalesce(duels_won_ftsy,0)
					+ coalesce(duels_lost_ftsy,0)
					+ coalesce(dribble_attempts_ftsy,0)
					+ coalesce(dribbles_success_ftsy,0)
					+ coalesce(dribbles_failed_ftsy,0)
					+ coalesce(clean_sheet_ftsy,0)
					+ coalesce(goals_conceded_ftsy,0)
					+ coalesce(goalkeeper_goals_conceded_ftsy,0)
					+ coalesce(interceptions_ftsy,0)
					+ coalesce(blocks_ftsy,0)
					+ coalesce(clearances_ftsy,0)
					+ coalesce(clearances_offline_ftsy,0)
					+ coalesce(tackles_ftsy,0)
					+ coalesce(error_lead_to_goal_ftsy,0)
					+ coalesce(owngoals_ftsy,0)
					+ coalesce(dispossessed_ftsy,0)
					+ coalesce(dribbled_past_ftsy,0)
					+ coalesce(saves_ftsy,0)
					+ coalesce(inside_box_saves_ftsy,0)
					+ coalesce(outside_box_saves_ftsy,0)
					+ coalesce(punches_ftsy,0)
					+ coalesce(redcards_ftsy,0)
					+ coalesce(redcyellowards_ftsy,0)
					as ftsy_score

from ftsy_stats