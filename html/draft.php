<?php
//include auth.php file on all secure pages
require("../php/auth.php");
$session_user_id=(isset($_SESSION['user_id']))?$_SESSION['user_id']:'';
?>

<html>
<head>
	<title>FANTASY BUNDESLIGA</title> 
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/draft.css">
	<meta name="robots" content="noindex">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Individual Scripts -->
	<script> var php_session_user_id = "<?php echo $session_user_id; ?>"</script>
	<script type="text/javascript" src="../js/draft-search-function.js"></script> 
	<script type="text/javascript" src="../js/draft-pop-player-carousel-by-sse.js"></script> 
	<script type="text/javascript" src="../js/draft-mark-user-status-by-sse.js"></script> 
	<script type="text/javascript" src="../js/draft-display-countdown-by-sse.js"></script> 
	<script type="text/javascript" src="../js/draft-change-user-status.js"></script> 
	<script type="text/javascript" src="../js/draft-display-player-profile.js"></script> 
	<script type="text/javascript" src="../js/draft-display-team.js"></script> 
	<script type="text/javascript" src="../js/draft-display-grid.js"></script> 
	<script type="text/javascript" src="../js/draft-filter-function.js"></script> 
	<script type="text/javascript" src="../js/draft-toggle-team-dropdown.js"></script> 
	<script type="text/javascript" src="../js/draft-pick-request.js"></script> 
	<script type="text/javascript" src="../js/draft-trigger-autopick.js"></script> 
</head>

<body>

<!-- Header image -->
<header>
  <?php require "header.php"; ?>
</header> 

<!-- Navigation -->
<div id = "hilfscontainer">
	<?php include("navigation.php"); ?>
</div>

<!-- Content -->
<div id="wrapper" class="row">
	<div id="content_wrapper">

		<!-- Header -->
		<div class="top_band">

			<!-- 1. Who is on the clock? -->
			<div id="on_the_clock">
			</div>
		
			<!-- 2. Row on the top shows users before draft has started or most recent picks if the draft is running -->
			<?php 
				include("../secrets/mysql_db_connection.php");
				$draft_status = mysqli_query($con, "SELECT draft_status FROM xa7580_db1.draft_meta WHERE league_id = 1" ) -> fetch_object() -> draft_status;
				
				echo "<div id='top_flow_band'>";
				
					if ($draft_status == 'open'){
						$unique_players = mysqli_query($con, "	SELECT user_id, username, teamname, rank FROM xa7580_db1.draft_order WHERE league_id = 1 order by rank" );
						
						while($row = mysqli_fetch_array($unique_players)) {
							echo "<div class='draft_user' id='user_".mb_convert_encoding($row['user_id'], 'UTF-8')."' >";
							echo "Pick " . $row['rank'] . "<br>"; 
							echo mb_convert_encoding($row['teamname'], 'UTF-8');
							echo "</div>";
						}

					} elseif ($draft_status != 'open'){
						// Populated by draft-pop-player-carousel-by-sse.js
					}

				echo "</div>";
			?>

			<!-- 3. Countdown for active pick -->
			<div id="countdown">
			</div>

		</div>

		<!-- Main content page -->
		<div class="content">

			<!-- Left column shows draftable players -->
			<div class="left_column">
				<div id="left_head">

					<!-- Search function -->
					<div id="search_player_wrapper">
						<input id="search_player" type="text" placeholder="Suche Spieler oder Verein..">
					</div>

					<!-- Filter functions -->
					<div id="player_filters">
						<div id="position_filters">
							<div id='st_filter' class='filter_button' data-active='1'>ST</div>
							<div id='mf_filter' class='filter_button' data-active='1'>MF</div>
							<div id='aw_filter' class='filter_button' data-active='1'>AW</div>
							<div id='tw_filter' class='filter_button' data-active='1'>TW</div>
						</div>

					<div id ="misc_filters">
						<div id='drafted_filter' class='filter_button' data-active='2'>DRFT</div>
						<div id='neuzugang_filter' class='filter_button' data-active='2'>NEW</div>
						<div id='sum_avg_sort' class='filter_button' data-active='0'>AVG 🠗</div>
					</div>
				
				</div>
			</div>

			<div id="selectable_players_list">
			
				<?php
					include("../secrets/mysql_db_connection.php");
					$player_list = mysqli_query($con, "
						SELECT 	base.id
										, base.display_name
										, base.lastname
										, base.teamname_code
										, base.team_logo
										, base.position_short
										, rk.rank_pos_ftsy
										, rk.sum_ftsy as sum_fantasy_punkte
										, rk.avg_ftsy as avg_fantasy_punkte
										, case when base.pick is not null then 1 else 0 end as picked_flg

						FROM xa7580_db1.draft_player_base base

						LEFT JOIN draft_player_ranking rk
							ON rk.player_id = base.id

						ORDER BY rk.avg_ftsy DESC
					");

					while($row = mysqli_fetch_array($player_list)) {

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
								echo $row['position_short'] . " #" . $row['rank_pos_ftsy'];
							echo "</div>";

							echo "<div class='players_td' id='td_fantasy_points'>";
								echo "∅ " . $row['avg_fantasy_punkte'] . " P.";
							echo "</div>";

							echo "<div class='players_td' id='td_fantasy_points'>";
								echo $row['sum_fantasy_punkte'] . " P.";
							echo "</div>";

						echo "</div>";
					}

				?>	
			</div>

		</div>

		<!-- Main frame displaying either draft grid or player profiles -->
		<div class="main">
		</div>

		<!-- Right column showing teams and their picks -->
		<div class="right_column">
			<div id="right_head">
				
				<div class="dropdown">
					<button id="view_team_button" onclick="team_dropdown()" class="dropbtn">Wähle ein Team...</button>
				<div id="fantasy_teams_dropdown_content" class="dropdown-content">
				
				<?php
					include("../secrets/mysql_db_connection.php");
					$dropdown_team_list = mysqli_query($con, "	SELECT user_id, teamname FROM xa7580_db1.draft_order WHERE league_id = 1 order by teamname asc" );

					while($row = mysqli_fetch_array($dropdown_team_list)) {
						echo "<div class='dropdown_fantasy_team' data-user-id='".$row['user_id']."'>".mb_convert_encoding($row['teamname'], 'UTF-8')."</div>";
					}
				?>
			</div>
		</div>
	
	</div>

	<div id="draft_by_team">
	</div>

</div>

</div>
</div>
</div>		
</body>
</html>