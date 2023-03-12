<?php

echo "<div class='draft_grid'>";
	echo "<div class='sub_headline' style='text-align: center;'>DRAFT GRID</div>";

		include("../secrets/mysql_db_connection.php");

		$player_draft_list = mysqli_query($con, "	
			SELECT 		ord.teamname
			FROM 			draft_order_full ord
			WHERE 		ord.league_id = 1 and ord.round = 1
			ORDER BY 	ord.pick asc 
		");

	echo "<div class='draft_grid_row'>";
		echo "<div class='draft_grid_row'>";
			
			while($row = mysqli_fetch_array($player_draft_list)) {
				echo "<div class='draft_grid_cell draft_grid_cell_ftsy_team'>";
					echo mb_convert_encoding($row['teamname'],'UTF-8');
				echo "</div>";
			}

		echo "</div>";

		$max_rounds = mysqli_query($con, "SELECT MAX(round) as max_rnd FROM draft_order_full ord WHERE league_id = 1" ) -> fetch_object() -> max_rnd;
		$cnt_round = 1;

		while ($cnt_round <= $max_rounds){
			if ( ($cnt_round % 2) != 0 ) {
				
				$full_draft_list = mysqli_query($con, "	
					SELECT 	ord.pick
									, ply.lastname
									, case when ply.position_short is null then concat('Runde ',ord.round) else ply.position_short end as position_short
									, ply.teamname_code

					FROM 	draft_order_full ord
					
					LEFT JOIN draft_player_base ply
						ON ply.pick = ord.pick
					
					WHERE ord.league_id = 1 AND ord.round = '".$cnt_round."' 
					ORDER BY ord.pick asc
				");
				
			} else {
					
				$full_draft_list = mysqli_query($con, "	
					SELECT 	ord.pick
									, ply.lastname
									, case when ply.position_short is null then concat('Runde ',ord.round) else ply.position_short end as position_short
									, ply.teamname_code
					
					FROM 	draft_order_full ord
					LEFT JOIN draft_player_base ply
						ON ply.pick = ord.pick
					WHERE ord.league_id = 1 and ord.round = '".$cnt_round."' 
					ORDER BY ord.pick desc 
				");
			}

			echo "<div class='draft_grid_row'>";
				while($row = mysqli_fetch_array($full_draft_list)) {
					echo "<div class='draft_grid_cell draft_grid_cell_coloring_".$row['position_short']."'>";
						echo "<div class='draft_grid_cell_name'>" . mb_convert_encoding($row['lastname'],'UTF-8') . "</div>";
						echo "<div class='draft_grid_cell_info'>";
							echo "<div class='draft_grid_cell_pos'>";
								echo mb_convert_encoding($row['teamname_code'], 'UTF-8')." - ".$row['position_short'];
							echo "</div>";
							echo "<div class='draft_grid_cell_pick'>";
								echo $row['pick'];
							echo "</div>";											
						echo "</div>";
					echo "</div>";
				}						
			echo "</div>";
			$cnt_round++;	
		}
	echo "</div>";
?>