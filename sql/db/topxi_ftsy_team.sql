CREATE TABLE topxi_ftsy_team (
    topxi_lvl VARCHAR(3) 
    , season_id BIGINT 
    , season_name VARCHAR(9) 
    , formation SMALLINT
    , position_short VARCHAR(2)
    , player_id BIGINT
    , player_name VARCHAR(100)
    , player_image_path TEXT
    , buli_team_id BIGINT 
    , buli_team_code VARCHAR(3)
    , buli_team_name VARCHAR(50)
    , buli_team_logo_path TEXT
    , user_id INTEGER
    , user_name VARCHAR(50)
    , user_team_name VARCHAR(50)
    , user_team_code VARCHAR(10)
    , user_team_logo_path TEXT
    , ftsy_score DECIMAL(18,1)
    , ftsy_score_avg DECIMAL(18,1)
    , appearance_cnt INTEGER
    , appearance_min_dt DATE
    , appearance_max_dt DATE
    , load_ts TIMESTAMP DEFAULT sysdate()
) COMMENT="All-Time Elf und Elf der Saison nach Fantasy-Teams"
;
