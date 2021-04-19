/* Erstelle die Bundesliga Spieler-Datenbasis für einen neuen Draft */

DROP TABLE draft_player_base;

CREATE TABLE `draft_player_base` AS
SELECT	1 as ftsy_league_id
		, base.id
		, base.display_name
        , base.common_name
        , teams.name as teamname
        , teams.short_code as teamname_code
        , teams.logo_path as team_logo
        , base.lastname
        , base.position_short
        , base.position_long
        , base.image_path
        , base.is_sidelined
        , base.is_suspended
        , base.injured
        , base.injury_reason
		, null as pick 
		, null as round
		, null as pick_by
		, null as autopick_custom_list_flg
		, null as autopick_ranking_flg
		, null as pick_ts
		, null as league_id
		, 17361 as season_id


FROM 	sm_playerbase base

INNER JOIN sm_teams teams
	ON teams.id = base.current_team_id

WHERE	rostered = 1;

ALTER TABLE draft_player_base MODIFY ftsy_league_id INTEGER;
ALTER TABLE draft_player_base MODIFY pick INTEGER;
ALTER TABLE draft_player_base MODIFY round INTEGER;
ALTER TABLE draft_player_base MODIFY pick_by INTEGER;
ALTER TABLE draft_player_base MODIFY autopick_custom_list_flg INTEGER;
ALTER TABLE draft_player_base MODIFY autopick_ranking_flg INTEGER;
ALTER TABLE draft_player_base MODIFY pick_ts DATETIME;
ALTER TABLE draft_player_base MODIFY league_id INTEGER;
ALTER TABLE draft_player_base MODIFY season_id INTEGER;
