<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include '../db.php';

// Server Side Event: Lasse die Draft Uhrherunterlaufen, synchron auf allen Clients

$draft_status = mysqli_query($con, "SELECT draft_status FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> draft_status;

$sql_result = mysqli_query($con, "	SELECT 		dof.round
												, dof.pick
												, dof.teamname
												, dof.user_id
												, dpb.common_name
												, dpb.lastname
												, case when dpb.pick is null then '/images/icons/questionmarks.png' else dpb.image_path end as image_path

									FROM 		xa7580_db1.draft_order_full dof
									LEFT JOIN	xa7580_db1.draft_player_base dpb
										ON dof.pick = dpb.pick
									WHERE 		dof.pick BETWEEN (SELECT current_pick_no-5 FROM xa7580_db1.draft_meta) AND (SELECT current_pick_no+4 FROM xa7580_db1.draft_meta)
									ORDER BY 	dof.pick ASC
									");

$round_array = array(); 

while($row = mysqli_fetch_array($sql_result)) {
    $round_array[] = array($row["round"], $row["pick"], mb_convert_encoding($row["teamname"], 'UTF-8'), mb_convert_encoding($row["lastname"], 'UTF-8'), $bild_array[] = $row["image_path"], $row['user_id']);
}

array_unshift($round_array , $draft_status);

$picked_players = $round_array;

$str = json_encode($picked_players);
echo "data: {$str}\n\n";

flush();
?>