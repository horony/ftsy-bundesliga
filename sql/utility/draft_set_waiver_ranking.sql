UPDATE users_gamedata ug
SET ug.waiver_position = 0
;

UPDATE users_gamedata ug
INNER JOIN draft_order_full drft
	ON 	ug.user_id = drft.user_id
    	AND drft.round = 1
SET	ug.waiver_position = 11 - drft.pick
;