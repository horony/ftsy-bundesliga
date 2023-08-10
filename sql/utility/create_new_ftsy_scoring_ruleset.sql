CREATE TABLE dev_ftsy_scoring_ruleset (
    fantasy_league_id integer
    , player_position_en text
    , player_position_de_short text
	, `appearance` float
    , `minutes_played` float
    , `goals_total` float 
    , `goals_minus_pen` float
    , `assists` float
    , `big_chances_created` float
    , `key_passes` float
    , `passes_total` float
    , `passes_complete` float
    , `passes_incomplete` float
    , `passes_accuracy` float
    , `crosses_total` float
    , `crosses_complete` float
    , `crosses_incomplete` float
    , `shots_total` float
    , `shots_on_goal` float
    , `shots_missed` float
    , `shots_blocked` float
    , `big_chances_missed` float
    , `hit_woodwork` float
    , `pen_committed` float
    , `pen_missed` float
    , `pen_saved` float
    , `pen_scored` float
    , `pen_won` float
    , `duels_total` float
    , `duels_won` float
    , `duels_lost` float
    , `dribble_attempts` float
    , `dribbles_success` float
    , `dribbles_failed` float
    , `clean_sheet` float
    , `goals_conceded` float
    , `goalkeeper_goals_conceded` float
    , `interceptions` float
    , `blocks` float
    , `clearances` float
    , `clearance_offline` float
    , `tackles` float
    , `error_lead_to_goal` float
    , `owngoals` float
    , `dispossessed` float
    , `dribbled_past` float
);

INSERT INTO dev_ftsy_scoring_ruleset(`fantasy_league_id`, `player_position_en`, `player_position_de_short`, `appearance`) 
SELECT `fantasy_league_id`, `player_position_en`, `player_position_de_short`, `appearance` 
FROM ftsy_scoring_ruleset;