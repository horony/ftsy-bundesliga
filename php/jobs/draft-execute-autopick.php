<?php 
include("../auth.php");
include("../../secrets/mysql_db_connection.php");

date_default_timezone_set('Europe/Amsterdam');

$draft_meta = mysqli_query($con, "SELECT * FROM draft_meta WHERE league_id = 1 ") -> fetch_assoc();

/*
// Uncomment for bug fixing
echo($draft_meta['draft_status']), "\n";
echo($draft_meta['update_lock']), "\n";
echo strtotime("now"), "\n";
echo date("Y-m-d\TH:i:s\Z", strtotime("now")),  "\n";

echo($draft_meta['expire_ts']),  "\n";
echo(strtotime($draft_meta['expire_ts'])),  "\n";
echo(date("Y-m-d\TH:i:s\Z", strtotime($draft_meta['expire_ts']))),  "\n";

echo 'update_lock Check: ', $draft_meta['update_lock'] == 0;
echo 'draft_status Check: ', $draft_meta['draft_status'] == 'running',  "---";
echo 'time Check: ', (date("Y-m-d\TH:i:s\Z", strtotime($draft_meta['expire_ts'])) < date("Y-m-d\TH:i:s\Z", strtotime("now")));
*/

// Check if lock is disabled, draft is running and time is expired
if (	$draft_meta['update_lock'] == 0 and $draft_meta['draft_status'] == 'running' and (date("Y-m-d\TH:i:s\Z", strtotime($draft_meta['expire_ts'])) < date("Y-m-d\TH:i:s\Z", strtotime("now")))	) {
	
	mysqli_query($con, "UPDATE draft_meta SET update_lock = 1 WHERE league_id = 1 ");
	
	$best_available_player = mysqli_query($con, "
		SELECT 
			pb.id
			, pb.display_name 
			, SUM(scr.ftsy_score) as sum_score 

		FROM draft_player_base pb

		LEFT JOIN ftsy_scoring_all_v scr
			ON scr.player_id = pb.id

		WHERE 	
			pb.ftsy_league_id = 1
			AND pb.pick is NULL
			AND scr.fixture_id in (select fixture_id from sm_fixtures where season_id = 23744)
		GROUP BY pb.id, pb.display_name
		ORDER BY SUM(scr.ftsy_score) desc
		LIMIT 1
	") -> fetch_assoc();
	
	
	mysqli_query($con, "
		UPDATE 	draft_order_full 
		SET 	player_id = '".$best_available_player['id']."'
					, player_name = '".$best_available_player['display_name']."'
		WHERE pick = '".$draft_meta['current_pick_no']."'
	");

	$current_user_id = mysqli_query($con, " SELECT user_id from draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' and league_id = 1 " ) -> fetch_object() -> user_id;


	// Update draft_player_base
	mysqli_query($con, "
		UPDATE 	draft_player_base 
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
	$seconds_to_add = mysqli_query($con, "
		SELECT 	CASE WHEN dof.round >= round_time_change THEN seconds_last_picks ELSE seconds_first_picks END as seconds
		FROM 	draft_meta dm
		INNER JOIN 	draft_order_full dof
		ON dof.pick + 1 = dm.current_pick_no + 1
	") -> fetch_object() -> seconds;

	// Check if draft is complete
	if ($draft_meta['current_pick_no'] == ($draft_meta['draft_rounds']*$draft_meta['fantasy_players'])) {
		
		mysqli_query($con, "
			UPDATE 	draft_meta
			SET 	draft_status = 'complete'
						, draft_complete_flg = 1
		");			
	
	} else {
	
		mysqli_query($con, "
			UPDATE 	draft_meta
			SET 	current_round = (SELECT round FROM draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' + 1)
						, current_pick_no = '".$draft_meta['current_pick_no']."' + 1
						, on_the_clock = (SELECT username FROM draft_order_full WHERE pick = '".$draft_meta['current_pick_no']."' + 1)
						, start_ts = sysdate()
						, expire_ts = DATE_ADD(sysdate(), INTERVAL '".$seconds_to_add."' SECOND)
			");	
	}
	
	mysqli_query($con, "UPDATE draft_meta SET update_lock = 0 WHERE league_id = 1 ");


} else {
	echo date("Y-m-d\TH:i:s\Z", strtotime($draft_meta['expire_ts']));
	echo "---";
	echo date("Y-m-d\TH:i:s\Z", strtotime("now"));
}
?>
