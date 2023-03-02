<?php  include 'auth.php'; ?>
<html>
<head>

 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">  
	<meta http-equiv="cache-control" content="no-cache" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="-1" /> 

	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/waiver.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

  <!-- Custom Scripts -->
  <script type="text/javascript" src="../js/waiver-sort-ranking.js"></script>  
	
</head>

<body>
	<header><h1>FANTASY BUNDESLIGA</h1></header>
	
	<?php include("navigation.php"); ?>

	<!-- Content -->
	<div id = "hilfscontainer">
		<div class="flex-grid-fantasy"> 
			<div class="col">

				<div id="headline">
					<h2>DEINE AKTIVEN WAIVER</h2>
				</div>
				
				<div style='font-size: 12px; color: black; text-align: center;'>
					<p>Verändere die Priorität deiner abgegebenen Waiver per Drag-and-Drop.</p>
				</div>

				<!-- Waiver-Window -->
				<div class="content">
					<ul id="active_waivers">	
								
						<?php 	
							// Get active waivers of user
							$toReturn = '';
							include("../secrets/mysql_db_connection.php");
							$user = $_SESSION['username'];

							$result1 = mysqli_query($con, "	
								SELECT 	waiv.*
												, base1.short_code as add_verein
												, base2.short_code as drop_verein
								
								FROM 	xa7580_db1.waiver waiv

								INNER JOIN xa7580_db1.sm_playerbase_basic_v base1
									ON waiv.waiver_add_id = base1.id
								
								INNER JOIN xa7580_db1.sm_playerbase_basic_v base2
									ON waiv.waiver_drop_id = base2.id	
								
								WHERE owner = '".$_SESSION['user_id']."' 
								ORDER BY prio ASC
								");
							
							// Display waivers

							$i = 0;
							while ($r=$result1->fetch_assoc()){
								$i++;
								$toReturn=$toReturn . "<li class='ui-state-default' id = 'item_".$i."'>&Xi;&nbsp;" . $i . "&nbsp;&nbsp;&nbsp;<b>" .
									mb_convert_encoding(($r["waiver_add_name"]), 'UTF-8') . "</b><small> " . utf8_encode($r["add_verein"]) . "</small>&nbsp;&harr;&nbsp;<b>" . 
									mb_convert_encoding(($r["waiver_drop_name"]), 'UTF-8') . "</b><small> " . utf8_encode($r["drop_verein"]) . "</small></li>";			
							}
							echo $toReturn;			
						?>

					</ul>
					<br>

					<!-- Save waiver ranking -->
					<button id="waiver_speichern" onclick="save_waiver_ranking()">Speichern</button>
				</div>

				<!-- Footer -->
  			<div id="footer" class="">
  				<?php
  					// Date and time of next waivers
  					$next_waiver = mysqli_query($con, "
  						SELECT 	CASE 	WHEN waiver_date_1 > NOW() THEN DATE_FORMAT(waiver_date_1, '%e.%m %H:%i') 
  													WHEN waiver_date_1 <= NOW() AND waiver_date_2 > NOW() THEN DATE_FORMAT(waiver_date_2, '%e.%m %H:%i') 
  													ELSE 'Nächste Woche' 
  													END AS waiver_datum 
  						FROM xa7580_db1.parameter
  						") -> fetch_object() -> waiver_datum;
					
						// Current waiver priority
  					$waiver_prio = mysqli_query($con, "
  						SELECT waiver_position 
  						FROM xa7580_db1.users_gamedata 
  						WHERE user_id = '".$_SESSION['user_id']."' 
  						") -> fetch_object() -> waiver_position;
  				?>

  				<!-- Display waiver position -->
  				<div class="footer_box">
  					Waiver-Position
  					<br>
  					<span style="font-size: 18px; text-decoration: bold;">
  						<?php echo "#" . $waiver_prio; ?>
  					</span>
  				</div>

  				<!-- Date next waiver -->
  				<div class="footer_box">
  					Nächster Waiver
  					<br>
  					<span style="font-size: 18px; text-decoration: bold;">
  						<?php echo $next_waiver; ?>
  					</span>
  				</div>

  				<!-- Link: Create new waiver -->
  				<div class="footer_box" style="cursor: pointer; "onclick="window.location='transfermarkt.php';">
  					<span style="font-size: 12px; text-decoration: bold;">
  						Erstelle neue Waiver
  					</span>
  				</div>

  				<!-- Link: Delete waiver -->
  				<div class="footer_box" style="cursor: pointer; "onclick="window.location='waiver_delete.php';">
  					<span style="font-size: 12px; text-decoration: bold;">
  						Lösche bestehende Waiver
  					</span>
  				</div>
				</div>
			</div>
		</div>	
	</div> 
</body>
</html>