UPDATE xa7580_db1.users_gamedata game
    SET game.waiver_position = (
        SELECT new_ranking 
        FROM (
            SELECT 
                tab.user_id
                , tab.waiver_position
                , tab.waiver_safe_flg
                , tab.rang
                , @curRank := @curRank + 1 AS new_ranking
            FROM (
                SELECT
                    game.user_id
                    , game.waiver_position
                    , game.waiver_safe_flg
                    , tab.rang
                FROM xa7580_db1.users_gamedata game
                INNER JOIN xa7580_db1.ftsy_tabelle_2020 tab
                    ON game.user_id = tab.player_id
                    AND tab.spieltag = (SELECT max(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = (SELECT season_id FROM parameter))
                    AND tab.season_id = (SELECT season_id FROM parameter)
            ) tab
        , (SELECT @curRank := 0) r
        ORDER BY rang DESC
        ) tmp 
    WHERE 
        game.user_id = tmp.user_id 
    )