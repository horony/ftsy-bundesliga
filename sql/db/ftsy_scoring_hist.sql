DROP TABLE IF EXISTS ftsy_scoring_hist_new ;

CREATE TABLE ftsy_scoring_hist_new (
	season_id BIGINT 
	, season_name VARCHAR(9)
  , round_id BIGINT 
	, round_name SMALLINT
	, fixture_id BIGINT 
 	, kickoff_dt DATE
	, kickoff_ts TIMESTAMP
	, kickoff_time VARCHAR(5)
	, player_id BIGINT 
	, display_name VARCHAR(100)
	, common_name VARCHAR(100)
	, `number` SMALLINT
	, position_short VARCHAR(3)
	, injured SMALLINT
	, injury_reason VARCHAR(100)
	, is_suspended VARCHAR(100)
	, is_sidelined SMALLINT
	, image_path TEXT
	, current_team_id BIGINT
	, team_name VARCHAR(50)
	, team_code VARCHAR(3)
	, logo_path TEXT
	, 1_ftsy_owner_type VARCHAR(3)
	, 1_ftsy_owner_id INTEGER
	, 1_ftsy_match_status VARCHAR(20)
	, homeaway VARCHAR(1)
	, opp_team_name VARCHAR(50)
	, opp_team_code VARCHAR(3)
	, opp_team_id BIGINT
	, score_for SMALLINT
	, score_against SMALLINT
    
	/* Playing time */
	, appearance_ftsy DECIMAL(18,1)
	, appearance_stat SMALLINT
	, minutes_played_ftsy DECIMAL(18,1)
	, minutes_played_stat SMALLINT
	/* Goal participation */
	, goals_total_ftsy DECIMAL(18,1)
	, goals_total_stat SMALLINT
	, goals_minus_pen_ftsy DECIMAL(18,1)
	, goals_minus_pen_stat SMALLINT
	, assists_ftsy DECIMAL(18,1)
	, assists_stat SMALLINT
	, big_chances_created_ftsy DECIMAL(18,1)
	, big_chances_created_stat SMALLINT
	, key_passes_ftsy DECIMAL(18,1)
	, key_passes_stat SMALLINT
	/* Passes */	
	, passes_total_ftsy DECIMAL(18,1)
	, passes_total_stat INTEGER
	, passes_complete_ftsy DECIMAL(18,1)
	, passes_complete_stat INTEGER
	, passes_incomplete_ftsy DECIMAL(18,1)
	, passes_incomplete_stat INTEGER			
	, passes_accuracy_ftsy DECIMAL(18,1)
	, passes_accuracy_stat DECIMAL(18,1)
	, crosses_total_ftsy DECIMAL(18,1)
	, crosses_total_stat INTEGER
	, crosses_complete_ftsy DECIMAL(18,1)
	, crosses_complete_stat INTEGER
	, crosses_incomplete_ftsy DECIMAL(18,1)
	, crosses_incomplete_stat INTEGER
	/* Shots */
	, shots_total_ftsy DECIMAL(18,1)
	, shots_total_stat INTEGER
	, shots_on_goal_ftsy DECIMAL(18,1)
	, shots_on_goal_stat INTEGER
	, shots_missed_ftsy DECIMAL(18,1)
	, shots_missed_stat INTEGER
	, shots_blocked_ftsy DECIMAL(18,1)
	, shots_blocked_stat INTEGER
	, big_chances_missed_ftsy DECIMAL(18,1)
	, big_chances_missed_stat INTEGER
	, hit_woodwork_ftsy DECIMAL(18,1)
	, hit_woodwork_stat INTEGER
	/* Penalties */
	, pen_committed_ftsy DECIMAL(18,1)
	, pen_committed_stat SMALLINT
	, pen_missed_ftsy DECIMAL(18,1)
	, pen_missed_stat SMALLINT
	, pen_saved_ftsy DECIMAL(18,1)
	, pen_saved_stat SMALLINT
	, pen_scored_ftsy DECIMAL(18,1)
	, pen_scored_stat	SMALLINT	
	, pen_won_ftsy DECIMAL(18,1)
	, pen_won_stat SMALLINT
	/* Duels */ 
	, duels_total_ftsy DECIMAL(18,1)
	, duels_total_stat INTEGER
	, duels_won_ftsy DECIMAL(18,1)
	, duels_won_stat INTEGER
	, duels_lost_ftsy DECIMAL(18,1)
	, duels_lost_stat	INTEGER
	, dribble_attempts_ftsy DECIMAL(18,1)
	, dribble_attempts_stat INTEGER
	, dribbles_success_ftsy DECIMAL(18,1)
	, dribbles_success_stat INTEGER
	, dribbles_failed_ftsy DECIMAL(18,1)
	, dribbles_failed_stat INTEGER
	/* Clean sheet */
	, clean_sheet_ftsy DECIMAL(18,1)
	, clean_sheet_stat SMALLINT
	, goals_conceded_ftsy DECIMAL(18,1)
	, goals_conceded_stat SMALLINT
	, goalkeeper_goals_conceded_ftsy DECIMAL(18,1)
	, goalkeeper_goals_conceded_stat SMALLINT
	/* Defensive */ 
	, interceptions_ftsy DECIMAL(18,1)
	, interceptions_stat INTEGER
	, blocks_ftsy DECIMAL(18,1)
	, blocks_stat INTEGER
	, clearances_ftsy DECIMAL(18,1)
	, clearances_stat INTEGER
	, clearances_offline_ftsy DECIMAL(18,1)
	, clearances_offline_stat INTEGER
	, tackles_ftsy DECIMAL(18,1)
	, tackles_stat INTEGER
	/* Errors */				
	, error_lead_to_goal_ftsy DECIMAL(18,1)
	, error_lead_to_goal_stat	SMALLINT
	, owngoals_ftsy DECIMAL(18,1)
	, owngoals_stat SMALLINT
	, dispossessed_ftsy DECIMAL(18,1)
	, dispossessed_stat INTEGER
	, dribbled_past_ftsy DECIMAL(18,1)
	, dribbled_past_stat INTEGER
	/* Goal keeping */
	, saves_ftsy DECIMAL(18,1)
	, saves_stat INTEGER
	, inside_box_saves_ftsy DECIMAL(18,1)
	, inside_box_saves_stat INTEGER
	, outside_box_saves_ftsy DECIMAL(18,1)
	, outside_box_saves_stat INTEGER
	, punches_ftsy DECIMAL(18,1)
	, punches_stat INTEGER
	/* Cards */
	, redcards_ftsy DECIMAL(18,1)
	, redcards_stat SMALLINT
	, redyellowards_ftsy DECIMAL(18,1)
	, redyellowcards_stat SMALLINT
	
	, ftsy_score DECIMAL(18,1)
	, ftsy_score_2023 DECIMAL(18,1)
	
	, insert_ts DATETIME
	, update_ts DATETIME

	, PRIMARY KEY (fixture_id, player_id)

	, FOREIGN KEY (season_id) REFERENCES sm_seasons (season_id)
	, FOREIGN KEY (round_id) REFERENCES sm_rounds (id)
	, FOREIGN KEY (player_id) REFERENCES sm_fixures (fixture_id)
	, FOREIGN KEY (fixture_id) REFERENCES sm_playerbase (id)
	, FOREIGN KEY (1_ftsy_owner_id) REFERENCES users (id)
	, FOREIGN KEY (current_team_id) REFERENCES sm_teams (id)
	, FOREIGN KEY (opp_team_id) REFERENCES sm_teams (id)

)