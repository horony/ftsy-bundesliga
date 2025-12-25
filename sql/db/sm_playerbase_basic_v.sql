/* Creates VIEW holding some basic information ON players (birth_dt, number, name, team, ftsy team etc.) */

DROP VIEW sm_playerbase_basic_v;

CREATE VIEW sm_playerbase_basic_v AS 
SELECT
    base.id AS id
    -- Name
    , base.fullname AS full_name
    , base.common_name AS common_name
    , base.display_name AS display_name
    , base.lastname AS lastname
    , base.number AS number
    , base.image_path AS image_path
    -- Position
    , base.position_short AS position_short
    , base.position_long AS position_long
    , base.position_detail_name AS position_detail_name
    , base.captain AS captain
    -- Sidelined (Injury/Suspensions)
    , base.sidelined_type_id
    , li.li_sidelined_status AS sidelined_category
    , CASE WHEN li.li_sidelined_status IN ('Aufbautraining','Verletzung') THEN 1 ELSE 0 END AS is_injured
    , CASE WHEN li.li_sidelined_status IN ('5. Gelbe Karte','Nicht im Kader','Rote Karte','Gelb-Rote Karte') THEN 1 ELSE 0 END AS is_suspended
    , CASE WHEN li.li_sidelined_status IS NOT NULL THEN 1 ELSE 0 END AS is_sidelined
    , li.li_sidelined_reason AS sidelined_reason
    , COALESCE(li.li_sidelined_status, 'Fit') AS player_status
    , CASE
        WHEN li.li_sidelined_status IS NULL THEN 'fit.png'
        WHEN li.li_sidelined_status IN ('Verletzung') THEN 'verletzung.png'
        WHEN li.li_sidelined_status IN ('Rote Karte') THEN 'rote-karte.png'
        WHEN li.li_sidelined_status IN ('Gelb-Rote Karte') THEN 'gelb-rote-karte.png'
        WHEN li.li_sidelined_status IN ('5. Gelbe Karte') THEN 'gelbe-karte.png'
        WHEN li.li_sidelined_status IN ('Nicht im Kader') THEN 'verbannung.png'
        WHEN li.li_sidelined_status IN ('Aufbautraining') THEN 'aufbautraining.png'
        WHEN li.li_sidelined_status IN ('tbd') THEN 'angeschlagen-unsure.png'  
        WHEN li.li_sidelined_status IN ('tbd') THEN 'angeschlagen-up.png'
        WHEN li.li_sidelined_status IN ('tbd') THEN 'angeschlagen-down.png'  
        ELSE 'fit.png'
        END AS player_status_logo_path
    -- Misc
    , base.height AS height
    , base.weight AS weight
    , base.birthcountry AS birthcountry
    , base.birthplace AS birthplace
    , base.birth_dt AS birth_dt
    -- Current Bundesliga Team
    , team.id AS team_id
    , team.name AS name
    , team.short_code AS short_code
    , team.logo_path AS logo_path
    -- Current Fantasy Team
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
LEFT JOIN li_sidelined_players li 
    ON base.id = li.sm_player_id
;