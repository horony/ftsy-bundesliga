<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get data from js call
$topic = $_GET['topic'];
$lvl2 = $_GET['lvl2'];

if ($topic == 'FANTASY-BUNDESLIGA'){

	# header of sub_nav

	echo "<div class='lvl2_button_head'><bold>Spieltag:</bold></div>";
	echo "<div class='lvl2_button' style='color: #4caf50' onclick='clickable_lvl2(this); show_topxi(\"FABU\",\"SZN\",\"". $lvl2 ."\",\"0\")'>Alle</div>";

	# populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT round_name FROM topxi_fabu_ovr WHERE topxi_lvl = 'RND' AND season_id = '$lvl2' ORDER BY round_name ASC");
	while($row = mysqli_fetch_assoc($sql_result)) {
		echo "<div class='lvl2_button' onclick='clickable_lvl2(this); show_topxi(\"FABU\",\"RND\",\"" . $lvl2 . "\",\"" . $row['round_name'] . "\")'>" . $row['round_name'] . "</div>";
  }

} elseif ($topic == 'FANTASY-TEAMS'){

	if ($lvl2 == -1){$lvl2 = $_SESSION['user_id'];} # Current user as default

	# header of sub_nav

	echo "<div class='lvl1_button_head'><bold>Saison:</bold></div>";
	echo "<div class='lvl2_button' style='color: #4caf50' onclick='clickable_lvl2(this); show_topxi(\"USER\",\"OVR\",\"". $lvl2 ."\",\"0\")'>All-Time</div>";

	#	 populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT season_name, season_id FROM topxi_ftsy_team WHERE topxi_lvl = 'SZN' AND user_id = '$lvl2' ORDER BY season_id DESC");

	while($row = mysqli_fetch_assoc($sql_result)) {
		echo "<div class='lvl2_button' onclick='clickable_lvl2(this); show_topxi(\"USER\",\"SZN\",\"" . $lvl2 . "\",\"" . $row['season_id'] . "\")'>" . $row['season_name'] . "</div>";
  }

} elseif ($topic == 'BUNDESLIGA-TEAMS'){

	if ($lvl2 == -1){$lvl2 = 3321;}  # Leverkusen as default

	# header of sub_nav

	echo "<div class='lvl1_button_head'><bold>Saison:</bold></div>";
	echo "<div class='lvl2_button' style='color: #4caf50' onclick='clickable_lvl2(this); show_topxi(\"BULI\",\"OVR\",\"". $lvl2 ."\",\"0\")'>All-Time</div>";

	#	 populate remaining sub_nav from database

	$sql_result = mysqli_query($con,"SELECT DISTINCT season_name, season_id FROM topxi_buli_team WHERE topxi_lvl = 'SZN' AND buli_team_id = '$lvl2' ORDER BY season_id DESC");

	while($row = mysqli_fetch_assoc($sql_result)) {
		echo "<div class='lvl2_button' onclick='clickable_lvl2(this); show_topxi(\"BULI\",\"SZN\",\"" . $lvl2 . "\",\"" . $row['season_id'] . "\")'>" . $row['season_name'] . "</div>";
  }
} else {

	echo "";

}

?> 