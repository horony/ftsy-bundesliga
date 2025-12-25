DROP TABLE li_sidelined_players
;

CREATE TABLE IF NOT EXISTS li_sidelined_players (
    li_player_name VARCHAR(50)
    , li_player_name_cleaned VARCHAR(50)
    , li_team_name VARCHAR(50)
    , li_sidelined_status VARCHAR(50)
    , li_sidelined_reason VARCHAR(100)
    , sm_player_fullname VARCHAR(100)
    , sm_player_id INTEGER 
    , sm_team_id INTEGER
    , insert_ts TIMESTAMP DEFAULT SYSDATE()
) COMMENT="Sidelined players (injury + suspension) from webscraped source"
;