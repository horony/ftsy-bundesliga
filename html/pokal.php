<?php include("../php/auth.php"); ?>
<html>
<head>
  <title>FANTASY BUNDESLIGA</title> 
  <meta name="robots" content="noindex">
  <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=3, minimum-scale=0.1, user-scalable=no, minimal-ui">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/stylesheet_nav.css">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/stylesheet_pokal.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Custom scripts -->
	<script type="text/javascript" src="../js/cup-change-round.js"></script>  
	<script type="text/javascript" src="../js/cup-display-fixtures.js"></script>  
</head>

<body onload="show_cup_round('Gesamt')">
<header><h1>FANTASY BUNDESLIGA</h1></header>
	
	<!-- Navigation -->
	<div id = "hilfscontainer">
		<?php include("navigation.php"); ?>
	</div>

	<!-- Content -->

	<div id = "wrapper">
		<div id="content_wrapper">

			<!-- Headline -->
			<div id="cup_headline">
				POKAL
			</div>

			<!-- Navigation cup -->
			<div id="cup_nav_wrapper">
				<div class='nav_spieltag_head'><bold>Runde:</bold></div>
				<div class='nav_spieltag'>Quali-Runde</div>
				<div class='nav_spieltag'>Viertelfinale</div>		
				<div class='nav_spieltag'>Halbfinale</div>						
				<div class='nav_spieltag'>Finale</div>	
			</div>
				
			<!-- Diplay content of round -->
			<div id="cup_wrapper">
			</div>
			
		</div>

	</div> 
</body>
</html>