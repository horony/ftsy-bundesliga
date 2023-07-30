INSERT INTO ftsy_scoring_hist

SELECT 	fix.season_id
				, fix.season_name
				, fix.round_name
				, fix.round_id
				, fix.fixture_id
        , fix.kickoff_dt
        , fix.kickoff_ts
        , fix.kickoff_time

				, base.id
				, base.display_name
				, base.common_name
        , base.number
        , base.position_short
        , base.injured
        , base.injury_reason
        , base.is_suspended
        , base.is_sidelined
        , base.image_path
        , base.current_team_id
        , base.name 
        , base.short_code 
        , base.logo_path 

        , base.1_ftsy_owner_type
        , base.1_ftsy_owner_id
        , base.1_ftsy_match_status

        , case when fix.localteam_name_code = base.short_code then 'H' else 'A' end 
        , case when fix.localteam_name_code = base.short_code then fix.visitorteam_name else fix.localteam_name end 
				, case when fix.localteam_name_code = base.short_code then fix.visitorteam_name_code else fix.localteam_name_code end 
        , case when fix.localteam_name_code = base.short_code then fix.visitorteam_id else fix.localteam_id end 
        , case when fix.localteam_name_code = base.short_code then fix.localteam_score else fix.visitorteam_score end 
        , case when fix.localteam_name_code = base.short_code then fix.visitorteam_score else fix.localteam_score end 
        
				, appearance_ftsy
				, appearance_stat
				, minutes_played_ftsy
				, minutes_played_stat
				, goals_total_ftsy
				, goals_total_stat
				, goals_minus_pen_ftsy
				, goals_minus_pen_stat
				, assists_ftsy
				, assists_stat
				, big_chances_created_ftsy
				, big_chances_created_stat
				, key_passes_ftsy
				, key_passes_stat
				, passes_total_ftsy
				, passes_total_stat
				, passes_complete_ftsy
				, passes_complete_stat
				, passes_incomplete_ftsy
				, passes_incomplete_stat
				, passes_accuracy_ftsy
				, passes_accuracy_stat
				, crosses_total_ftsy, crosses_total_stat
				, crosses_complete_ftsy
				, crosses_complete_stat
				, crosses_incomplete_ftsy
				, crosses_incomplete_stat
				, shots_total_ftsy
				, shots_total_stat
				, shots_on_goal_ftsy
				, shots_on_goal_stat
				, shots_missed_ftsy
				, shots_missed_stat
				, shots_blocked_ftsy
				, shots_blocked_stat
				, big_chances_missed_ftsy
				, big_chances_missed_stat
				, hit_woodwork_ftsy
				, hit_woodwork_stat
				, pen_committed_ftsy
				, pen_committed_stat
				, pen_missed_ftsy
				, pen_missed_stat
				, pen_saved_ftsy
				, pen_saved_stat
				, pen_scored_ftsy
				, pen_scored_stat
				, pen_won_ftsy
				, pen_won_stat
				, duels_total_ftsy
				, duels_total_stat
				, duels_won_ftsy
				, duels_won_stat
				, duels_lost_ftsy
				, duels_lost_stat
				, dribble_attempts_ftsy
				, dribble_attempts_stat
				, dribbles_success_ftsy
				, dribbles_success_stat
				, dribbles_failed_ftsy
				, dribbles_failed_stat
				, clean_sheet_ftsy
				, clean_sheet_stat
				, goals_conceded_ftsy
				, goals_conceded_stat
				, goalkeeper_goals_conceded_ftsy
				, goalkeeper_goals_conceded_stat
				, interceptions_ftsy
				, interceptions_stat
				, blocks_ftsy
				, blocks_stat
				, clearances_ftsy
				, clearances_stat
				, clearances_offline_ftsy
				, clearances_offline_stat
				, tackles_ftsy
				, tackles_stat
				, error_lead_to_goal_ftsy
				, error_lead_to_goal_stat
				, owngoals_ftsy
				, owngoals_stat
				, dispossessed_ftsy
				, dispossessed_stat
				, dribbled_past_ftsy
				, dribbled_past_stat
				, saves_ftsy
				, saves_stat
				, inside_box_saves_ftsy
				, inside_box_saves_stat
				, outside_box_saves_ftsy
				, outside_box_saves_stat
				, punches_ftsy
				, punches_stat
				, redcards_ftsy
				, redcards_stat
				, redyellowcards_ftsy
				, redyellowcards_stat

        , ftsy_score
        , ftsy_score

        , sysdate()
        , null

FROM sm_playerbase_basic_v base

INNER JOIN sm_fixtures_basic_v fix
	ON 	fix.round_name = (SELECT spieltag from parameter)
    	AND fix.season_id = (SELECT season_id from parameter)
      AND ( base.current_team_id  = fix.localteam_id or base.current_team_id = fix.visitorteam_id)
  
LEFT JOIN ftsy_scoring_akt_v scr
	ON 	scr.player_id = base.id

WHERE base.current_team_id IS NOT NULL /* Only players currently on a roster */
;