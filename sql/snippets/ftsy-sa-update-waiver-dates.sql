UPDATE xa7580_db1.parameter par
SET 
    par.waiver_date_1 = (
        SELECT  
            CASE   
                WHEN DAYOFWEEK(start_dt) = 3 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL 0 DAY), DATETIME), 120000) -- Dienstag -> Dienstag 12:00
                WHEN DAYOFWEEK(start_dt) = 6 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL -2 DAY), DATETIME), 120000) -- Freitag -> Mittwoch 12:00
                WHEN DAYOFWEEK(start_dt) = 7 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL -3 DAY), DATETIME), 120000) -- Samstag -> Mitwoch 12:00
                ELSE NULL 
            END 
        FROM sm_rounds
        WHERE 
            season_id = (SELECT season_id FROM parameter)
            AND name = (SELECT spieltag from parameter)
    )
    , par.waiver_date_2 = (
        SELECT 
              CASE  
                  WHEN DAYOFWEEK(start_dt) = 3 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL 0 DAY), DATETIME), 180000) -- Dienstag -> Dienstag 18:00
                  WHEN DAYOFWEEK(start_dt) = 6 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL 0 DAY), DATETIME), 120000) -- Freitag -> Freitag 12:00
                  WHEN DAYOFWEEK(start_dt) = 7 THEN ADDTIME(CONVERT(DATE_ADD(start_dt, INTERVAL -1 DAY), DATETIME), 120000) -- Samstag -> Freitag 12:00
                  ELSE NULL 
              END 
          FROM sm_rounds
          WHERE   
              season_id = (SELECT season_id FROM parameter)
              AND name = (SELECT spieltag from parameter)
    )