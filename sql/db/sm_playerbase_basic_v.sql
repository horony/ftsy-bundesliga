/* Creates VIEW holding some basic information ON players (birth_dt, number, name, team, ftsy team etc.) */

CREATE VIEW sm_playerbase_basic_v AS 
SELECT
    base.id AS id
    , base.fullname AS full_name
    , base.common_name AS common_name
    , base.display_name AS display_name
    , base.lastname AS lastname
    , base.number AS number
    , base.position_short AS position_short
    , base.position_long AS position_long
    , base.position_detail_name AS position_detail_name
    , base.captain AS captain
    , base.sidelined_type_id
    , base.injured AS injured
    , base.sidelined_category
    , base.is_suspended AS is_suspended
    , base.is_sidelined AS is_sidelined
    , CASE WHEN
    	WHEN t.type_code IN ('red-card-suspension') THEN 'redcard'
    	WHEN t.type_code IN ('redy-card-suspension') THEN 'redyellowcard'
    	WHEN t.type_code IN ('yellow-card-suspension') THEN 'yellowcard'
		WHEN t.type_code IN ('called-up-to-national-team') THEN 'nationalteam'
        WHEN t.type_code IN ('fitness') THEN 'recovery'
        WHEN t.type_code IS NOT NULL AND t.type_code NOT IN ('no-eligibility') THEN 'injury'
        WHEN t.type_code IS NOT NULL THEN 'unknown-sidelined'
        ELSE 'fit'
        END AS sidelined_reason
    , base.current_team_id AS current_team_id
    , base.image_path AS image_path
    , base.height AS height
    , base.weight AS weight
    , base.birthcountry AS birthcountry
    , base.birthplace AS birthplace
    , base.birth_dt AS birth_dt
    /* Infos on the players current team */
    , team.id AS team_id
    , team.name AS name
    , team.short_code AS short_code
    , team.logo_path AS logo_path
    /* Infos on the user owning the player in a ftsy league */
    , own.player_id AS player_id
    , own.player_name AS player_name
    , own.1_ftsy_owner_type AS 1_ftsy_owner_type
    , own.1_ftsy_owner_id AS 1_ftsy_owner_id
    , own.1_ftsy_match_status AS 1_ftsy_match_status
    , usr.teamname AS ftsy_owner_teamname 
    , usr.team_code AS ftsy_owner_teamcode
FROM sm_playerbase base 
LEFT JOIN sm_teams team 
    ON team.id = base.current_team_id
LEFT JOIN ftsy_player_ownership own 
    ON own.player_id = base.id
LEFT JOIN users usr
    ON usr.id = own.1_ftsy_owner_id
LEFT JOIN sm_types t 
    ON base.sidelined_type_id = t.type_id