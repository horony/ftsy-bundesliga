/* Step 1: Execute sm_fixturespy. for the round with the postponed fixture, IN order to UPDATE TABLE sm_fixtures */

/* Step 2: Execute sm_player_stats.py for the round with the postponed fixture, IN order to UPDATE TABLE sm_player_stats */

/* Step 3: Evaluate the UPDATE AND get fixture_id */

SELECT * 
FROM ftsy_scoring_all_v
WHERE 
    round_name = 18 -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    AND own_team_code IN ('M05','FCU') -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
ORDER BY fixture_id DESC, ftsy_score DESC
;

/* Step 4: UPDATE TABLE ftsy_scoring_hist */

UPDATE ftsy_scoring_hist hst
INNER JOIN ftsy_scoring_all_v scr
    ON hst.fixture_id = scr.fixture_id
    AND hst.fixture_id = 18863346 -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    AND hst.player_id = scr.player_id
SET   
    hst.round_name = scr.round_name
    , hst.appearance_stat = scr.appearance_stat
    , hst.minutes_played_stat = scr.minutes_played_stat
    , hst.goals_total_stat = scr.goals_total_stat
    , hst.goals_minus_pen_stat = scr.goals_minus_pen_stat
    , hst.assists_stat = scr.assists_stat
    , hst.big_chances_created_stat = scr.big_chances_created_stat
    , hst.key_passes_stat = scr.key_passes_stat
    , hst.passes_total_stat = scr.passes_total_stat
    , hst.passes_complete_stat = scr.passes_complete_stat
    , hst.passes_incomplete_stat = scr.passes_incomplete_stat
    , hst.passes_accuracy_stat = scr.passes_accuracy_stat
    , hst.crosses_total_stat = scr.crosses_total_stat
    , hst.crosses_complete_stat = scr.crosses_complete_stat
    , hst.crosses_incomplete_stat = scr.crosses_incomplete_stat
    , hst.shots_total_stat = scr.shots_total_stat
    , hst.shots_on_goal_stat = scr.shots_on_goal_stat
    , hst.shots_missed_stat = scr.shots_missed_stat
    , hst.shots_blocked_stat = scr.shots_blocked_stat
    , hst.big_chances_missed_stat = scr.big_chances_missed_stat
    , hst.hit_woodwork_stat = scr.hit_woodwork_stat
    , hst.pen_committed_stat = scr.pen_committed_stat
    , hst.pen_missed_stat = scr.pen_missed_stat
    , hst.pen_saved_stat = scr.pen_saved_stat
    , hst.pen_scored_stat = scr.pen_scored_stat
    , hst.pen_won_stat = scr.pen_won_stat
    , hst.duels_total_stat = scr.duels_total_stat
    , hst.duels_won_stat = scr.duels_won_stat
    , hst.duels_lost_stat = scr.duels_lost_stat
    , hst.dribble_attempts_stat = scr.dribble_attempts_stat
    , hst.dribbles_success_stat = scr.dribbles_success_stat
    , hst.dribbles_failed_stat = scr.dribbles_failed_stat
    , hst.clean_sheet_stat = scr.clean_sheet_stat
    , hst.goals_conceded_stat = scr.goals_conceded_stat
    , hst.goalkeeper_goals_conceded_stat = scr.goalkeeper_goals_conceded_stat
    , hst.interceptions_stat = scr.interceptions_stat
    , hst.blocks_stat = scr.blocks_stat
    , hst.clearances_stat = scr.clearances_stat
    , hst.clearances_offline_stat = scr.clearances_offline_stat
    , hst.tackles_stat = scr.tackles_stat
    , hst.error_lead_to_goal_stat = scr.error_lead_to_goal_stat
    , hst.owngoals_stat = scr.owngoals_stat
    , hst.dispossessed_stat = scr.dispossessed_stat
    , hst.dribbled_past_stat = scr.dribbled_past_stat
    , hst.saves_stat = scr.saves_stat
    , hst.inside_box_saves_stat = scr.inside_box_saves_stat
    , hst.outside_box_saves_stat = scr.outside_box_saves_stat
    , hst.punches_stat = scr.punches_stat
    , hst.redcards_stat = scr.redcards_stat
    , hst.redyellowcards_stat = scr.redyellowcards_stat
    , hst.appearance_ftsy = scr.appearance_ftsy
    , hst.minutes_played_ftsy = scr.minutes_played_ftsy
    , hst.goals_total_ftsy = scr.goals_total_ftsy
    , hst.goals_minus_pen_ftsy = scr.goals_minus_pen_ftsy
    , hst.assists_ftsy = scr.assists_ftsy
    , hst.big_chances_created_ftsy = scr.big_chances_created_ftsy
    , hst.key_passes_ftsy = scr.key_passes_ftsy
    , hst.passes_total_ftsy = scr.passes_total_ftsy
    , hst.passes_complete_ftsy = scr.passes_complete_ftsy
    , hst.passes_incomplete_ftsy = scr.passes_incomplete_ftsy
    , hst.passes_accuracy_ftsy = scr.passes_accuracy_ftsy
    , hst.crosses_total_ftsy = scr.crosses_total_ftsy
    , hst.crosses_complete_ftsy = scr.crosses_complete_ftsy
    , hst.crosses_incomplete_ftsy = scr.crosses_incomplete_ftsy
    , hst.shots_total_ftsy = scr.shots_total_ftsy
    , hst.shots_on_goal_ftsy = scr.shots_on_goal_ftsy
    , hst.shots_missed_ftsy = scr.shots_missed_ftsy
    , hst.shots_blocked_ftsy = scr.shots_blocked_ftsy
    , hst.big_chances_missed_ftsy = scr.big_chances_missed_ftsy
    , hst.hit_woodwork_ftsy = scr.hit_woodwork_ftsy
    , hst.pen_committed_ftsy = scr.pen_committed_ftsy
    , hst.pen_missed_ftsy = scr.pen_missed_ftsy
    , hst.pen_saved_ftsy = scr.pen_saved_ftsy
    , hst.pen_scored_ftsy = scr.pen_scored_ftsy
    , hst.pen_won_ftsy = scr.pen_won_ftsy
    , hst.duels_total_ftsy = scr.duels_total_ftsy
    , hst.duels_won_ftsy = scr.duels_won_ftsy
    , hst.duels_lost_ftsy = scr.duels_lost_ftsy
    , hst.dribble_attempts_ftsy = scr.dribble_attempts_ftsy
    , hst.dribbles_success_ftsy = scr.dribbles_success_ftsy
    , hst.dribbles_failed_ftsy = scr.dribbles_failed_ftsy
    , hst.clean_sheet_ftsy = scr.clean_sheet_ftsy
    , hst.goals_conceded_ftsy = scr.goals_conceded_ftsy
    , hst.goalkeeper_goals_conceded_ftsy = scr.goalkeeper_goals_conceded_ftsy
    , hst.interceptions_ftsy = scr.interceptions_ftsy
    , hst.blocks_ftsy = scr.blocks_ftsy
    , hst.clearances_ftsy = scr.clearances_ftsy
    , hst.clearances_offline_ftsy = scr.clearances_offline_ftsy
    , hst.tackles_ftsy = scr.tackles_ftsy
    , hst.error_lead_to_goal_ftsy = scr.error_lead_to_goal_ftsy
    , hst.owngoals_ftsy = scr.owngoals_ftsy
    , hst.dispossessed_ftsy = scr.dispossessed_ftsy
    , hst.dribbled_past_ftsy = scr.dribbled_past_ftsy
    , hst.saves_ftsy = scr.saves_ftsy
    , hst.inside_box_saves_ftsy = scr.inside_box_saves_ftsy
    , hst.outside_box_saves_ftsy = scr.outside_box_saves_ftsy
    , hst.punches_ftsy = scr.punches_ftsy
    , hst.redcards_ftsy = scr.redcards_ftsy
    , hst.redyellowards_ftsy = scr.redcyellowards_ftsy
    , hst.ftsy_score = scr.ftsy_score
    , hst.update_ts = sysdate()
;

/* Step 5: UPDATE TABLE ftsy_schedule */

UPDATE xa7580_db1.ftsy_schedule sch
LEFT JOIN (
    SELECT 
        hst.1_ftsy_owner_id AS `Besitzer1`
        , SUM(COALESCE(hst.ftsy_score,0)) AS fantasy_score1
        , COUNT(hst.player_id) AS anz1
    FROM ftsy_scoring_hist hst
    WHERE  
        hst.1_ftsy_owner_type = 'USR' 
        AND hst.1_ftsy_match_status != 'NONE'
        AND hst.round_name = 18 -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        AND hst.season_id = (SELECT season_id FROM parameter)
    GROUP BY hst.1_ftsy_owner_id
  ) akt_score_1
      ON sch.ftsy_home_id = akt_score_1.Besitzer1
LEFT JOIN (
    SELECT 
        hst.1_ftsy_owner_id AS `Besitzer2`
        , SUM(COALESCE(hst.ftsy_score,0)) AS fantasy_score2
        , COUNT(hst.player_id) AS anz2
    FROM ftsy_scoring_hist hst
    WHERE
        hst.1_ftsy_owner_type = 'USR' 
        AND hst.1_ftsy_match_status != 'NONE'
        AND hst.round_name = 18 -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        AND hst.season_id = (SELECT season_id FROM parameter)
    GROUP BY hst.1_ftsy_owner_id
  ) akt_score_2
    ON sch.ftsy_away_id = akt_score_2.Besitzer2  
SET 
    sch.ftsy_home_score = CASE WHEN anz1 = 11 THEN COALESCE(akt_score_1.fantasy_score1,0) ELSE -20 END
    , sch.ftsy_away_score = CASE WHEN anz2 = 11 THEN COALESCE(akt_score_2.fantasy_score2,0) ELSE -20 END
WHERE  
    sch.buli_round_name = 18 -- Edit here! <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    AND season_id = (SELECT season_id FROM parameter)

/* Step 6: Remove postponed round FROM spieltag_abschluss.php */