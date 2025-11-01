DELIMITER $$

CREATE TRIGGER ft_trigger
    AFTER UPDATE ON sm_fixtures
    FOR EACH ROW
BEGIN
    -- Prüfen ob sich der Status zu 'FT' geändert hat und das Spiel heute stattfindet
    IF (OLD.match_status != 'FT' AND NEW.match_status = 'FT') 
       AND (DATE(NEW.kickoff_dt) = CURDATE()) THEN
        INSERT INTO news (
            headline
            , story
            , add_id
            , drop_id
            , name
            , `type`
            , `TIMESTAMP`
        )
        SELECT 
            fix.fixture_id
            , CONCAT('Abpfiff IN ', COALESCE(ven.city, 'unbekannter Stadt'), '. <b>', COALESCE(t_home.name, 'Unbekanntes Team'), '</b> und <b>', COALESCE(t_away.name, 'Unbekanntes Team'), '</b> trennen sich <b>', COALESCE(fix.localteam_score, '?'), ':', COALESCE(fix.visitorteam_score, '?'), '</b>')
            , fix.visitorteam_id
            , 'FT-TRIGGER'
            , `buli_ergebnis`
            , NOW()
        FROM sm_fixtures fix
        LEFT JOIN sm_teams t_home 
        	ON fix.localteam_id = t_home.id
        LEFT JOIN sm_venues ven
        	ON t_home.venue_id = ven.id
        LEFT JOIN sm_teams t_away 
        	ON fix.visitorteam_id = t_away.id
        WHERE fix.fixture_id = NEW.fixture_id;
    END IF;
END$$

DELIMITER ;