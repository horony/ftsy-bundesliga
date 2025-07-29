UPDATE ftsy_player_ownership o 
INNER JOIN draft_order_full d
    ON o.player_id = d.player_id
SET 
    o.1_ftsy_owner_type = 'USR'
    , o.1_ftsy_owner_id = d.user_id
    , o.1_ftsy_match_status = 'NONE'
;