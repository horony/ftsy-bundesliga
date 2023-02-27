<?php include("../php/auth.php"); ?>
<html>
<head>
  <title>FANTASY BUNDESLIGA</title> 
  <meta name="robots" content="noindex">
  <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=3, minimum-scale=0.1, user-scalable=no, minimal-ui">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/stats.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Custom scripts -->
	<script>
		/* Default settings */
		$choice = 'FANTASY-TEAMS';
		$(document).ready(function default_load(){
			$choice = 'FANTASY-TEAMS';
    	$(".default").css("color", "#4caf50");
			$('#player_position_nav').css("display", "none");    	    
			showStats();
		});
	</script>
	<script type="text/javascript" src="../js/stats-clickable-elements.js"></script>
	<script type="text/javascript" src="../js/stats-display-stats.js"></script>
</head>

<body onload="default_load()">

<header><h1>FANTASY BUNDESLIGA</h1></header>
	
	<!-- Navigation -->
	<div id = "hilfscontainer">
		<?php include("navigation.php"); ?>
	</div>

	<!-- Content -->
	<div id = "wrapper">
		<div id="content_wrapper">
				<div id="view_nav">
					<ul>
						<li><a class="button default" onclick="clickable(this); showStats();">FANTASY-TEAMS</a></li>
		  			<li><a class="button" onclick="clickable(this); showStats(); ">BUNDESLIGA-TEAMS</a></li>
		  			<li><a class="button" onclick="clickable(this); showStats();">SPIELER</a></li>	
		  			<li><a class="button" onclick="clickable(this); showStats();">TOP-PERFORMANCES</a></li>
	  			</ul>
				</div>

				<div id='player_position_nav' style='display: hidden'>
					<div class='position_button_head'><bold>Wähle Positionsgruppe:</bold></div>
					<div class='position_button' onclick='clickable_pos(this); showPos("ALL")'>Alle Positionen</div>
					<div class='position_button' onclick='clickable_pos(this); showPos("TW")'>Tor</div>
					<div class='position_button' onclick='clickable_pos(this); showPos("AW")'>Abwehr</div>
					<div class='position_button' onclick='clickable_pos(this); showPos("MF")'>Mittelfeld</div>
					<div class='position_button' onclick='clickable_pos(this); showPos("ST")'>Sturm</div>
				</div>
				
				<!-- Functions load data here -->
				<div id="content">
				</div>
			</div>
		</div>
	</div> 
</body>
</html>