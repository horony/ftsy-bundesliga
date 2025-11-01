/* Reset des Drafts */

UPDATE draft_order_full
SET player_id = 0, player_name = NULL
;

UPDATE draft_player_base
SET pick = NULL, round = NULL, pick_by = NULL, autopick_custom_list_flg = NULL, autopick_ranking_flg = NULL
;

UPDATE draft_meta
SET draft_complete_flg = 0, draft_status = 'open', current_pick_no = 1, current_round = 1, on_the_clock = (SELECT teamname FROM draft_order_full WHERE pick = 1)
WHERE league_id = 1
;
