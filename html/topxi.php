<?php include("../php/auth.php"); ?>
<html>
<head>
  <title>FANTASY BUNDESLIGA</title> 
  <meta name="robots" content="noindex">
  <meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=3, minimum-scale=0.1, user-scalable=no, minimal-ui">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
  <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/topxi.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Custom scripts -->
	<script type="text/javascript" src="../js/topxi-clickable-elements.js"></script>
	<script type="text/javascript" src="../js/topxi-display-sub-nav.js"></script>	
	<script type="text/javascript" src="../js/topxi-display-formation.js"></script>

	<script>
		/* Default settings when opening page */
		
		$(document).ready(function default_load(){
			/* Reset all values */
			$choice = 'FANTASY-BUNDESLIGA';
    	$(".default").css("color", "#4caf50");
			$('.lvl1_nav').css("display", "none");
			$('.lvl2_nav').css("display", "none");   

			/* Load all-time topix */
			show_topxi('FABU','OVR','','');
			show_sub_nav_1($choice);
			show_sub_nav_2();
		});
	</script>
		
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
	<div id = "wrapper">
		<div id="content_wrapper">
				<div id="view_nav">
					<ul>
						<li><a class="button default" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2(); show_topxi('FABU','OVR','0','0');">FANTASY-BUNDESLIGA</a></li>
		  			<li><a class="button" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2('FANTASY-TEAMS','-1'); show_topxi('USER','OVR','0','0');">FANTASY-TEAMS</a></li>	
		  			<li><a class="button" onclick="clickable(this); show_sub_nav_1(this); show_sub_nav_2('BUNDESLIGA-TEAMS','-1'); show_topxi('BULI','OVR','0','0');">BUNDESLIGA-TEAMS</a></li>
	  			</ul>
				</div>

				<div id="sub_nav_wrapper">

					<div id="sub_nav_1" class="sub_nav">
					</div>

					<div id="sub_nav_2" class="sub_nav">
					</div>

				</div>

				<!-- Functions load data here -->
				<div id="content">
				</div>
			</div>
		</div>
	</div> 
</body>
</html>