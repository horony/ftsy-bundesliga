CREATE TABLE dev_ftsy_scoring_ruleset (
    fantasy_league_id integer
    , player_position_en TEXT
    , player_position_de_short TEXT
    , `appearance` FLOAT
    , `minutes_played` FLOAT
    , `goals_total` FLOAT 
    , `goals_minus_pen` FLOAT
    , `assists` FLOAT
    , `big_chances_created` FLOAT
    , `key_passes` FLOAT
    , `passes_total` FLOAT
    , `passes_complete` FLOAT
    , `passes_incomplete` FLOAT
    , `passes_accuracy` FLOAT
    , `crosses_total` FLOAT
    , `crosses_complete` FLOAT
    , `crosses_incomplete` FLOAT
    , `shots_total` FLOAT
    , `shots_on_goal` FLOAT
    , `shots_missed` FLOAT
    , `shots_blocked` FLOAT
    , `big_chances_missed` FLOAT
    , `hit_woodwork` FLOAT
    , `pen_committed` FLOAT
    , `pen_missed` FLOAT
    , `pen_saved` FLOAT
    , `pen_scored` FLOAT
    , `pen_won` FLOAT
    , `duels_total` FLOAT
    , `duels_won` FLOAT
    , `duels_lost` FLOAT
    , `dribble_attempts` FLOAT
    , `dribbles_success` FLOAT
    , `dribbles_failed` FLOAT
    , `clean_sheet` FLOAT
    , `goals_conceded` FLOAT
    , `goalkeeper_goals_conceded` FLOAT
    , `interceptions` FLOAT
    , `blocks` FLOAT
    , `clearances` FLOAT
    , `clearance_offline` FLOAT
    , `tackles` FLOAT
    , `error_lead_to_goal` FLOAT
    , `owngoals` FLOAT
    , `dispossessed` FLOAT
    , `dribbled_past` FLOAT
);

INSERT INTO dev_ftsy_scoring_ruleset(`fantasy_league_id`, `player_position_en`, `player_position_de_short`, `appearance`) 
SELECT `fantasy_league_id`, `player_position_en`, `player_position_de_short`, `appearance` 
FROM ftsy_scoring_ruleset;