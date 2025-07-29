UPDATE ftsy_tabelle_2020 tab 

INNER JOIN (
  SELECT  
      tab.*
      , @curRank := @curRank + 1 AS rank
  FROM ftsy_tabelle_2020 tab, (SELECT @curRank := 0) r
  WHERE 
      tab.season_id = (SELECT season_id FROM parameter)
      AND tab.spieltag = (SELECT spieltag FROM parameter)
  ORDER BY    
      tab.punkte DESC
      , tab.h2h DESC
      , tab.siege DESC
      , tab.score_for DESC
      , tab.score_against DESC
      , RAND ()
  ) upd
  ON tab.player_id = upd.player_id
  AND tab.spieltag = upd.spieltag
  AND tab.season_id = upd.season_id
SET tab.rang = upd.rank
WHERE 
    tab.season_id = (SELECT season_id FROM parameter)
    AND tab.spieltag = (SELECT spieltag FROM parameter)