<?php
include("../secrets/mysql_db_connection.php");

// Data from ajax call
$st_filter = $_GET["st_filter"];
$mf_filter = $_GET["mf_filter"];
$aw_filter = $_GET["aw_filter"];
$tw_filter = $_GET["tw_filter"];
$neuzugang_filter = $_GET["neuzugang_filter"];
$drafted_filter = $_GET["drafted_filter"];
$sum_avg_sort = $_GET["sum_avg_sort"];


$filter_player_list = mysqli_query($con, "
	SELECT 	base.id
					, base.display_name
					, base.lastname
					, base.teamname_code
					, base.team_logo
					, base.position_short
					, rk.sum_ftsy as sum_fantasy_punkte
					, rk.avg_ftsy as avg_fantasy_punkte
					, rk.rank_pos_ftsy as pos_ranking
					, case when base.pick is not null then 1 else 0 end as picked_flg
					, case when tf.player_id is null then 1 else 0 end as neuzugang_flg

	FROM draft_player_base base

	LEFT JOIN draft_player_ranking rk
		ON rk.player_id = base.id

	LEFT JOIN (
		SELECT DISTINCT player_id AS player_id
		FROM sm_player_stats
		) tf
		ON tf.player_id = base.id

	WHERE ( 
		base.position_short = CASE WHEN '".$st_filter."'='1' THEN 'ST' END
		OR base.position_short = CASE WHEN '".$mf_filter."'='1' THEN 'MF' END
		OR base.position_short = CASE WHEN '".$aw_filter."'='1' THEN 'AW' END
		OR base.position_short = CASE WHEN '".$tw_filter."'='1' THEN 'TW' END 
		)
		
		AND 

		( case when tf.player_id is null then 1 else 0 end = CASE WHEN '".$neuzugang_filter."'='0' THEN 0
		WHEN '".$neuzugang_filter."'='1' THEN 1
		ELSE case when tf.player_id is null then 1 else 0 end
		END
		)

		AND 

		( case when base.pick is not null then 1 else 0 end = CASE 	WHEN '".$drafted_filter."'='0' THEN 0
		WHEN '".$drafted_filter."'='1' THEN 1
		ELSE case when base.pick is not null then 1 else 0 end
		END
		)

	ORDER BY CASE WHEN '".$sum_avg_sort."'=1 THEN rk.sum_ftsy WHEN '".$sum_avg_sort."'=0 THEN rk.avg_ftsy END DESC
	
	");

while($row = mysqli_fetch_array($filter_player_list)) {

	if ($row['picked_flg'] == 1){ 
		echo "<div class='players_tr picked'>";
	} else {
		echo "<div class='players_tr unpicked'>";
	}

		echo "<div class='players_td' style='display:none;'>";
			echo utf8_encode($row['id']);
		echo "</div>";

		echo "<div class='players_td' id='td_position_short'>";
			echo utf8_encode($row['position_short']);
		echo "</div>";

		echo "<div class='players_td' id='td_spieler_name'>";
			echo mb_convert_encoding($row['display_name'], 'UTF-8');
		echo "</div>";

		echo "<div class='players_td' id='td_verein_short'>";
			echo mb_convert_encoding($row['teamname_code'], 'UTF-8');
		echo "</div>";

		echo "<div class='players_td' id='td_pos_ranking'>";
			echo $row['position_short'] . " #" . $row['pos_ranking'];
		echo "</div>";

		echo "<div class='players_td' id='td_fantasy_points'>";
			echo "∅ " . $row['avg_fantasy_punkte'] . " P.";
		echo "</div>";

		echo "<div class='players_td' id='td_fantasy_points'>";
			echo $row['sum_fantasy_punkte'] . " P.";
		echo "</div>";

	echo "</div>";

}
mysqli_close($con);
?>