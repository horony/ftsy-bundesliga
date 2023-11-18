<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get meta-data from session
$active_user_id = $_SESSION['user_id'];

// Get data from js call
$lvl1 = $_GET['lvl1'];

if ($lvl1 == 'FANTASY-BUNDESLIGA'){

	# header of sub_nav

	echo "<div class='lvl1_button_head'><bold>Saison:</bold></div>";
	echo "<div class='lvl1_button' style='color: #4caf50' onclick='clickable_lvl1(this); show_sub_nav_2(); show_topxi(\"FABU\",\"OVR\",\"21795\",\"0\")'>All-Time</div>";

	# populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT season_name, season_id FROM topxi_fabu_ovr WHERE topxi_lvl = 'SZN' ORDER BY season_id DESC");
	while($row = mysqli_fetch_assoc($sql_result)) {
		echo "<div class='lvl1_button' onclick='clickable_lvl1(this); show_sub_nav_2(\"" . $lvl1 . "\",\"" . $row['season_id'] . "\"); show_topxi(\"FABU\",\"SZN\",\"" . $row['season_id'] . "\",\"0\")'>" . $row['season_name'] . "</div>";
  }

} elseif ($lvl1 == 'FANTASY-TEAMS'){

	# header of sub_nav

	echo "<div class='lvl1_button_head'><bold>Team:</bold></div>";

	# populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT user_id, user_name, user_team_name, user_team_code, user_team_logo_path FROM topxi_ftsy_team WHERE topxi_lvl = 'OVR' ORDER BY user_team_code ASC");
	while($row = mysqli_fetch_assoc($sql_result)) {

		$highlight_text_user = ' ';
		if ($row['user_id'] == $active_user_id){ 
			$highlight_text_user = "style='color: #4caf50'";
		}

		echo "<div class='lvl1_button'" . $highlight_text_user . "onclick='clickable_lvl1(this); show_sub_nav_2(\"" . $lvl1 . "\",\"" . $row['user_id'] . "\"); show_topxi(\"USER\",\"OVR\",\"" . $row['user_id'] . "\",\"0\")'>" . $row['user_team_code'] . "</div>";
  }

} elseif ($lvl1 == 'BUNDESLIGA-TEAMS'){

	# header of sub_nav

	echo "<div class='lvl1_button_head'><bold>Team:</bold></div>";

	# populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT buli_team_id, buli_team_name, buli_team_code, buli_team_logo_path FROM topxi_buli_team WHERE topxi_lvl = 'OVR' ORDER BY buli_team_code ASC");
	while($row = mysqli_fetch_assoc($sql_result)) {

		$highlight_text_buli = ' ';
		if ($row['buli_team_code'] == 'B04'){ 
			$highlight_text_buli = "style='color: #4caf50'";
		}
		echo "<div class='lvl1_button'" . $highlight_text_buli . "onclick='clickable_lvl1(this); show_sub_nav_2(\"" . $lvl1 . "\",\"" . $row['buli_team_id'] . "\"); show_topxi(\"BULI\",\"OVR\",\"" . $row['buli_team_id'] . "\",\"0\")'>" . $row['buli_team_code'] . "</div>";
  }
}

?> 