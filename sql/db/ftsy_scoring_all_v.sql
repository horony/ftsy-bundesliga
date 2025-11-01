/* Creates view with detailed stats and ftsy score for each player in each fixture he was on a team */

CREATE VIEW ftsy_scoring_all_v AS

WITH ftsy_stats AS (
    SELECT 	
        stats.round_name AS round_name
        ,stats.fixture_id
        ,stats.player_id
        ,player.display_name AS player_name
        ,player.position_short
        ,teams_own.short_code AS own_team_code
        ,teams_opp.short_code AS opp_team_code
        /* Playing time */
        ,ROUND((rules.appearance * stats.appearance),1) AS appearance_ftsy
        ,stats.appearance AS appearance_stat
        ,ROUND((rules.minutes_played * stats.minutes_played),1) AS minutes_played_ftsy
        ,stats.minutes_played AS minutes_played_stat
        /* Goal participation */
        ,ROUND((rules.goals_total * stats.goals_total),1) AS goals_total_ftsy
        ,stats.goals_total AS goals_total_stat
        ,ROUND((rules.goals_minus_pen * stats.goals_minus_pen),1) AS goals_minus_pen_ftsy
        ,stats.goals_minus_pen AS goals_minus_pen_stat
        ,ROUND((rules.assists * stats.assists),1) AS ASsists_ftsy
        ,stats.assists AS ASsists_stat
        ,ROUND((rules.big_chances_created * stats.big_chances_created),1) AS big_chances_created_ftsy
        ,stats.big_chances_created AS big_chances_created_stat
        ,ROUND((rules.key_passes * stats.key_passes),1) AS key_passes_ftsy
        ,stats.key_passes AS key_passes_stat
        /* Passes */
        ,ROUND((rules.passes_total * stats.passes_total),1) AS passes_total_ftsy
        ,stats.passes_total AS passes_total_stat
        ,ROUND((rules.passes_complete * stats.passes_complete),1) AS passes_complete_ftsy
        ,stats.passes_complete AS passes_complete_stat
        ,ROUND((rules.passes_incomplete * stats.passes_incomplete),1) AS passes_incomplete_ftsy
        ,stats.passes_incomplete AS passes_incomplete_stat				
        ,ROUND((rules.passes_accuracy * stats.passes_accuracy),1) AS passes_accuracy_ftsy
        ,stats.passes_accuracy AS passes_accuracy_stat	
        ,ROUND((rules.crosses_total * stats.crosses_total),1) AS crosses_total_ftsy
        ,stats.crosses_total AS crosses_total_stat
        ,ROUND((rules.crosses_complete * stats.crosses_complete),1) AS crosses_complete_ftsy
        ,stats.crosses_complete AS crosses_complete_stat
        ,ROUND((rules.crosses_incomplete * stats.crosses_incomplete),1) AS crosses_incomplete_ftsy
        ,stats.crosses_incomplete AS crosses_incomplete_stat
        /* Shots */
        ,ROUND((rules.shots_total * stats.shots_total),1) AS shots_total_ftsy
        ,stats.shots_total AS shots_total_stat
        ,ROUND((rules.shots_on_goal * stats.shots_on_goal),1) AS shots_on_goal_ftsy
        ,stats.shots_on_goal AS shots_on_goal_stat
        ,ROUND((rules.shots_missed * stats.shots_missed),1) AS shots_missed_ftsy
        ,stats.shots_missed AS shots_missed_stat
        ,ROUND((rules.shots_blocked * stats.shots_blocked),1) AS shots_blocked_ftsy
        ,stats.shots_blocked AS shots_blocked_stat
        ,ROUND((rules.big_chances_missed * stats.big_chances_missed),1) AS big_chances_missed_ftsy
        ,stats.big_chances_missed AS big_chances_missed_stat
        ,ROUND((rules.hit_woodwork * stats.hit_woodwork),1) AS hit_woodwork_ftsy
        ,stats.hit_woodwork AS hit_woodwork_stat
        /* Penalties */
        ,ROUND((rules.pen_committed * stats.pen_committed),1) AS pen_committed_ftsy
        ,stats.pen_committed AS pen_committed_stat
        ,ROUND((rules.pen_missed * stats.pen_missed),1) AS pen_missed_ftsy
        ,stats.pen_missed AS pen_missed_stat
        ,ROUND((rules.pen_saved * stats.pen_saved),1) AS pen_saved_ftsy
        ,stats.pen_saved AS pen_saved_stat
        ,ROUND((rules.pen_scored * stats.pen_scored),1) AS pen_scored_ftsy
        ,stats.pen_scored AS pen_scored_stat				
        ,ROUND((rules.pen_won * stats.pen_won),1) AS pen_won_ftsy
        ,stats.pen_won AS pen_won_stat 
        /* Duels */
        ,ROUND((rules.duels_total * stats.duels_total),1) AS duels_total_ftsy
        ,stats.duels_total AS duels_total_stat
        ,ROUND((rules.duels_won * stats.duels_won),1) AS duels_won_ftsy
        ,stats.duels_won AS duels_won_stat
        ,ROUND((rules.duels_lost * stats.duels_lost),1) AS duels_lost_ftsy
        ,stats.duels_lost AS duels_lost_stat				

        ,ROUND((rules.dribble_attempts * stats.dribble_attempts),1) AS dribble_attempts_ftsy
        ,stats.dribble_attempts AS dribble_attempts_stat
        ,ROUND((rules.dribbles_success * stats.dribbles_success),1) AS dribbles_success_ftsy
        ,stats.dribbles_success AS dribbles_success_stat
        ,ROUND((rules.dribbles_failed * stats.dribbles_failed),1) AS dribbles_failed_ftsy
        ,stats.dribbles_failed AS dribbles_failed_stat
        /* Clean sheet */
        ,ROUND((rules.clean_sheet * stats.clean_sheet),1) AS clean_sheet_ftsy
        ,stats.clean_sheet AS clean_sheet_stat
        ,ROUND((rules.goals_conceded * stats.goals_conceded),1) AS goals_conceded_ftsy
        ,stats.goals_conceded AS goals_conceded_stat
        ,ROUND((rules.goalkeeper_goals_conceded * stats.goalkeeper_goals_conceded),1) AS goalkeeper_goals_conceded_ftsy
        ,stats.goalkeeper_goals_conceded AS goalkeeper_goals_conceded_stat
        /* Defensive */
        ,ROUND((rules.interceptions * stats.interceptions),1) AS interceptions_ftsy
        ,stats.interceptions AS interceptions_stat
        ,ROUND((rules.blocks * stats.blocks),1) AS blocks_ftsy
        ,stats.blocks AS blocks_stat
        ,ROUND((rules.clearances * stats.clearances),1) AS clearances_ftsy
        ,stats.clearances AS clearances_stat
        ,ROUND((rules.clearance_offline * stats.clearance_offline),1) AS clearances_offline_ftsy
        ,stats.clearance_offline AS clearances_offline_stat
        ,ROUND((rules.tackles * stats.tackles),1) AS tackles_ftsy
        ,stats.tackles AS tackles_stat
        /* Errors */				
        ,ROUND((rules.error_lead_to_goal * stats.error_lead_to_goal),1) AS error_lead_to_goal_ftsy
        ,stats.error_lead_to_goal AS error_lead_to_goal_stat		
        ,ROUND((rules.owngoals * stats.owngoals),1) AS owngoals_ftsy
        ,stats.owngoals AS owngoals_stat
        ,ROUND((rules.dispossessed * stats.dispossessed),1) AS dispossessed_ftsy
        ,stats.dispossessed AS dispossessed_stat
        ,ROUND((rules.dribbled_past * stats.dribbled_past),1) AS dribbled_past_ftsy
        ,stats.dribbled_past AS dribbled_past_stat
        /* Goal keeping */
        ,ROUND((rules.saves * stats.saves),1) AS saves_ftsy
        ,stats.saves AS saves_stat
        ,ROUND((rules.inside_box_saves * stats.inside_box_saves),1) AS inside_box_saves_ftsy
        ,stats.inside_box_saves AS inside_box_saves_stat
        ,ROUND((rules.outside_box_saves * stats.outside_box_saves),1) AS outside_box_saves_ftsy
        ,stats.outside_box_saves AS outside_box_saves_stat
        ,ROUND((rules.punches * stats.punches),1) AS punches_ftsy
        ,stats.punches AS punches_stat
        /* Cards */
        ,ROUND((rules.redcards * stats.redcards),1) AS redcards_ftsy
        ,stats.redcards AS redcards_stat
        ,ROUND((rules.redyellowcards * stats.redyellowcards),1) AS redcyellowards_ftsy
        ,stats.redyellowcards AS redyellowcards_stat
    FROM sm_player_stats stats 
    /* Join player information */
    LEFT JOIN sm_playerbase player
        ON stats.player_id = player.id
    /* Join team information */
    LEFT JOIN sm_teams teams_own 
        ON teams_own.id = stats.own_team_id
    LEFT JOIN sm_teams teams_opp
        ON teams_opp.id = stats.opp_team_id
    /* Join rules to calculate ftsy scores according to players position */
    LEFT JOIN ftsy_scoring_ruleset rules 
        ON player.position_short = rules.player_position_de_short
) 
SELECT
    ftsy_stats.*
    , COALESCE(appearance_ftsy,0)
        + COALESCE(minutes_played_ftsy,0)
        + COALESCE(goals_total_ftsy,0)
        + COALESCE(goals_minus_pen_ftsy,0)
        + COALESCE(assists_ftsy,0)
        + COALESCE(big_chances_created_ftsy,0)
        + COALESCE(key_passes_ftsy,0)
        + COALESCE(passes_total_ftsy,0)
        + COALESCE(passes_complete_ftsy,0)
        + COALESCE(passes_incomplete_ftsy,0)
        + COALESCE(passes_accuracy_ftsy,0)
        + COALESCE(crosses_total_ftsy,0)
        + COALESCE(crosses_complete_ftsy,0)
        + COALESCE(crosses_incomplete_ftsy,0)
        + COALESCE(shots_total_ftsy,0)
        + COALESCE(shots_on_goal_ftsy,0)
        + COALESCE(shots_missed_ftsy,0)
        + COALESCE(shots_blocked_ftsy,0)
        + COALESCE(big_chances_missed_ftsy,0)
        + COALESCE(hit_woodwork_ftsy,0)
        + COALESCE(pen_committed_ftsy,0)
        + COALESCE(pen_missed_ftsy,0)
        + COALESCE(pen_saved_ftsy,0)
        + COALESCE(pen_scored_ftsy,0)
        + COALESCE(pen_won_ftsy,0)
        + COALESCE(duels_total_ftsy,0)
        + COALESCE(duels_won_ftsy,0)
        + COALESCE(duels_lost_ftsy,0)
        + COALESCE(dribble_attempts_ftsy,0)
        + COALESCE(dribbles_success_ftsy,0)
        + COALESCE(dribbles_failed_ftsy,0)
        + COALESCE(clean_sheet_ftsy,0)
        + COALESCE(goals_conceded_ftsy,0)
        + COALESCE(goalkeeper_goals_conceded_ftsy,0)
        + COALESCE(interceptions_ftsy,0)
        + COALESCE(blocks_ftsy,0)
        + COALESCE(clearances_ftsy,0)
        + COALESCE(clearances_offline_ftsy,0)
        + COALESCE(tackles_ftsy,0)
        + COALESCE(error_lead_to_goal_ftsy,0)
        + COALESCE(owngoals_ftsy,0)
        + COALESCE(dispossessed_ftsy,0)
        + COALESCE(dribbled_past_ftsy,0)
        + COALESCE(saves_ftsy,0)
        + COALESCE(inside_box_saves_ftsy,0)
        + COALESCE(outside_box_saves_ftsy,0)
        + COALESCE(punches_ftsy,0)
        + COALESCE(redcards_ftsy,0)
        + COALESCE(redcyellowards_ftsy,0)
        AS ftsy_score
FROM ftsy_stats