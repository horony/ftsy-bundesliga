UPDATE xa7580_db1.ftsy_tabelle_2020 tab_akt
LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab_before
    ON tab_before.player_id = tab_akt.player_id 
    AND tab_before.spieltag = (SELECT spieltag FROM parameter)-1 
    AND tab_before.season_id = (SELECT season_id FROM parameter)
SET 
    tab_akt.updown = CASE WHEN (tab_akt.rang > tab_before.rang) THEN '&#9660;' WHEN (tab_akt.rang < tab_before.rang) THEN '&#9650;' ELSE '-' END
WHERE   
    tab_akt.spieltag = (SELECT spieltag FROM parameter)
    AND tab_akt.season_id = (SELECT season_id FROM parameter)