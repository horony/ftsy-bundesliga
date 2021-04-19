<?php 
include '../auth.php';
include '../db.php';

$league_id = $_SESSION['league_id'];
$draft_meta = mysqli_query($con, "SELECT * FROM draft_meta WHERE league_id = '".$league_id."'" ) -> fetch_assoc();

if ($draft_meta['update_lock'] == 0){

	/* Locke aktuelle Anfrage um mehrfaches Klicken zu verhindern */
	mysqli_query($con, "UPDATE draft_meta SET update_lock = 1 WHERE league_id = 1 ");

	/* Prüfe ob der Draft von einem Admin gestartet wurde! */
	if ($draft_meta['draft_status'] == 'running'){

		$on_the_clock_username = mysqli_query($con, "SELECT username FROM draft_order_full dof INNER JOIN draft_meta dm ON dof.pick = dm.current_pick_no") -> fetch_object() -> username;
		$on_the_clock_user_id = mysqli_query($con, "SELECT user_id FROM draft_order_full dof INNER JOIN draft_meta dm ON dof.pick = dm.current_pick_no") -> fetch_object() -> user_id;

		$click_player_id = $_GET['click_player_id']; 
		$pick_in_ts = $_GET['pick_in_ts'];
		$pick_by_user = $_SESSION['user_id'];

		/* Prüfe ob der User überhaupt dran ist zu picken */
		if ($on_the_clock_user_id == $pick_by_user){

			$pick_in_unix = date('Y-m-d h:i:s', strtotime($pick_in_ts));
			$pick_in_unix = strtotime($pick_in_unix);
			$pick_expire_unix = strtotime($draft_meta['expire_ts']);

			/* Prüfe ob der Pick rechtzeitig eingangen ist */
			if ($pick_in_unix <= $pick_expire_unix) {   

				$draftable_check = mysqli_query($con, "SELECT COUNT(*) as cnt FROM draft_player_base WHERE pick is null and id = '".$click_player_id."'") -> fetch_object() -> cnt;

				/* Prüfe ob der Spieler noch Vorhanden ist */
				if ($draftable_check > 0) {

						// Update draft_order_full
						mysqli_query($con, "UPDATE 	draft_order_full 
											SET 	player_id = '".$click_player_id."'
													, player_name = (SELECT display_name FROM draft_player_base WHERE id = '".$click_player_id."' ) 
											WHERE pick = '".$draft_meta['current_pick_no']."'
											");

						// Update draft_player_base
						mysqli_query($con, "UPDATE 	draft_player_base 
											SET 	pick = '".$draft_meta['current_pick_no']."' 
													, round = '".$draft_meta['current_round']."'
													, pick_by = '".$pick_by_user."'
													, autopick_custom_list_flg = 0
													, autopick_ranking_flg = 0
													, pick_ts = sysdate()
													, ftsy_league_id = '".$league_id."'
											WHERE id = '".$click_player_id."'
											");

						// Update Draft-Meta
						$seconds_to_add = mysqli_query($con, "	SELECT 	CASE WHEN dof.round >= round_time_change THEN seconds_last_picks ELSE seconds_first_picks END as seconds
																FROM 	draft_meta dm
																INNER JOIN 	draft_order_full dof
																	ON dof.pick + 1 = dm.current_pick_no + 1
															") -> fetch_object() -> seconds;

						// Prüfe ob es sich um den allerletzen Pick handelt...
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

					echo "Spieler erfolgreich gedrafted";

				} else {
					echo "Der Spieler wurde schon gepickt";
					mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");
				}

			} else { 
				echo "Dieser Pick kam leider zu spät!";
				mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");
			}

		} else {
			echo "Das ist nicht dein Pick!";
			mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");
		}

	} else {
		echo "Der Draft ist noch nicht gestartet worden!";
		mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");
	}

} else {
	echo "Eine andere Transaktion wird aktuell verarbeitet!";
}
?>