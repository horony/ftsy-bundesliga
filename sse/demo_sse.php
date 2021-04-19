<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include '../db.php';			

// Server Side Event: Lasse die Draft Uhrherunterlaufen, synchron auf allen Clients

$draft_status = mysqli_query($con, "SELECT draft_status FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> draft_status;

$on_the_clock = mysqli_query($con, "SELECT 	dof.teamname
											, dof.user_id
											, dof.pick
											, dof.round
											, case when dm.current_round >= dm.round_time_change then seconds_last_picks else seconds_first_picks end as seconds_for_pick
											, dm.start_ts
											, dm.expire_ts

									FROM xa7580_db1.draft_order_full dof

									INNER JOIN xa7580_db1.draft_meta dm 
										ON 	dm.current_pick_no = dof.pick
											AND dm.league_id = dof.league_id
											AND dm.league_id = 1
									" ) -> fetch_assoc();

$on_the_clock_team = utf8_encode($on_the_clock['teamname']);
$on_the_clock_id = $on_the_clock['user_id'];
$on_the_clock_pick = $on_the_clock['pick'];
$on_the_clock_round = $on_the_clock['round'];
$start_ts = $on_the_clock['start_ts'];
$expire_ts = $on_the_clock['expire_ts'];
$seconds_for_pick = $on_the_clock['seconds_for_pick'];

$data = array(
    'draft_status'=>$draft_status,
    'start_ts'=>$start_ts,
    'expire_ts'=>$expire_ts,
    'on_the_clock_team'=>$on_the_clock_team,
    'on_the_clock_id'=>$on_the_clock_id,
    'on_the_clock_pick'=>$on_the_clock_pick,
    'on_the_clock_round'=>$on_the_clock_round,
    'seconds_for_pick'=>$seconds_for_pick,
    'server_time'=>date('r')
);

$str = json_encode($data);

echo "data: {$str}\n\n";

flush();
?>