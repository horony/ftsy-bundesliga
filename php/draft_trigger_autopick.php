<?php 
include '../auth.php';
include '../db.php';

// Wenn die Uhr abläuft wird ein Autopick für den betroffenen Spieler durchgeführt

$draft_meta = mysqli_query($con, "SELECT * FROM draft_meta WHERE league_id = 1 ") -> fetch_assoc();

// Prüfe Bedingungen für Autopick
if ($draft_meta['update_lock'] == 0 and $draft_meta['draft_status'] == 'running' and ( strtotime($draft_meta['expire_ts']) < mktime() ) ){

	// Führe Autopick durch
	mysqli_query($con, "UPDATE draft_meta SET update_lock = 1 WHERE league_id = 1 ");

	$best_available_player = mysqli_query($con, "	SELECT pb.id, pb.display_name 
													FROM draft_player_base pb
													LEFT JOIN ftsy_scoring_all_aggregated_v scr
														ON scr.player_id = pb.id
													WHERE 	pb.ftsy_league_id = 1
															AND pb.pick is NULL
													ORDER BY scr.sum_score DESC, scr.avg_score DESC
													LIMIT 1
												") -> fetch_assoc();

	mysqli_query($con, 	"	UPDATE 	draft_order_full 
							SET 	player_id = '".$best_available_player['id']."'
									, player_name = '".$best_available_player['display_name']."'
							WHERE pick = '".$draft_meta['current_pick_no']."'
						");

	$current_user_id = mysqli_query($con, " SELECT user_id from draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' and league_id = 1 " ) -> fetch_object() -> user_id;
				

	// Update draft_player_base
	mysqli_query($con, "UPDATE 	draft_player_base 
						SET 	pick = '".$draft_meta['current_pick_no']."' 
								, round = '".$draft_meta['current_round']."'
								, pick_by = '".$current_user_id."'
								, autopick_custom_list_flg = 0
								, autopick_ranking_flg = 1
								, pick_ts = sysdate()
								, ftsy_league_id = 1
								WHERE id = '".$best_available_player['id']."'
						");

	// Update Draft-Meta
	$seconds_to_add = mysqli_query($con, "	SELECT 	CASE WHEN dof.round >= round_time_change THEN seconds_last_picks ELSE seconds_first_picks END as seconds
											FROM 	draft_meta dm
											INNER JOIN 	draft_order_full dof
												ON dof.pick + 1 = dm.current_pick_no + 1

											") -> fetch_object() -> seconds;

	// Prüfe ob es der allerletzte Pick war...
	if ($draft_meta['current_pick_no'] == ($draft_meta['draft_rounds']*$draft_meta['fantasy_players'])) {
		mysqli_query($con, "UPDATE 	draft_meta
							SET 	draft_status = 'complete'
									, draft_complete_flg = 1
							");		
	//... oder nicht	
	} else {
		mysqli_query($con, "UPDATE 	draft_meta
							SET 	current_round = (SELECT round FROM draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' + 1)
									, current_pick_no = '".$draft_meta['current_pick_no']."' + 1
									, on_the_clock = (SELECT username FROM draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' + 1)
									, start_ts = sysdate()
									, expire_ts = DATE_ADD(sysdate(), INTERVAL '".$seconds_to_add."' SECOND)
					");	
	}

	mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");


} else {
	echo strtotime($draft_meta['expire_ts']);
	echo $draft_meta['expire_ts'];
	echo "---";
 	echo mktime();
}

?>