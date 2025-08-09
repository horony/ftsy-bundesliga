<?php
include("../secrets/mysql_db_connection.php");
$id = $_GET["player_id"];

$drafted_players = mysqli_query($con, "	
	SELECT 	
        display_name
        , teamname_code
        , team_logo
        , position_short
        , position_long
        , round
        , pick			 
	FROM xa7580_db1.draft_player_base 
	WHERE pick_by = '".$id."' 
	ORDER BY pick asc" 
);

$draft_rounds = mysqli_query($con, "SELECT draft_rounds FROM draft_meta WHERE league_id = 1" ) -> fetch_object() -> draft_rounds;

$fantasy_positionen_array = array("Sturm","Mittelfeld", "Abwehr", "Torwart");

echo "<div id='view_team_wrapper'>";
	
	// Overall player count
	echo "<div id='view_team_count'>";
		mysqli_data_seek($drafted_players,0);
		$player_cnt = 0;
		while($row = mysqli_fetch_array($drafted_players)) {
			$player_cnt++;
		}
		echo "Kader: " . $player_cnt."/".$draft_rounds;
	echo "</div>";

	foreach ($fantasy_positionen_array as &$value) {

		// Player count by position
		echo "<div class='view_team_position_head'>";

			echo "<div class='view_team_position_name'>";
				echo $value;
			echo "</div>";
			
			echo "<div class='view_team_position_count'>";
				$player_cnt_position = 0;
				mysqli_data_seek($drafted_players,0);
				while($row = mysqli_fetch_array($drafted_players)) {
					if ($row['position_long'] == $value){
						$player_cnt_position++;
					}
				}
				echo $player_cnt_position;
			echo "</div>";
		echo "</div>";

		// Display drafted players
		echo "<div class='view_team_player_table'>";
			mysqli_data_seek($drafted_players,0);
			while($row = mysqli_fetch_array($drafted_players)) {
				if ($row['position_long'] == $value){
					echo "<div class='view_team_player_tr'>";
						echo "<div class='view_team_player_td'><small>RND ".$row['round']."</small></div>";
						echo "<div class='view_team_player_td'><img src='".$row['team_logo']."' height='10px'></div>";
						echo "<div class='view_team_player_td'>".mb_convert_encoding($row['display_name'], 'UTF-8')."</div>";
					echo "</div>";
				}
			}
		echo "</div>";
	}
echo "</div>";									
mysqli_close($con);
?>