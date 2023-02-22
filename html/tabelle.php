<?php include("../php/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">   
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=3.0, minimum-scale=0.1, user-scalable=no, minimal-ui">

	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/tabelle.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
	<!-- Custom scripts -->
	<script type="text/javascript" src="../js/tabelle-dynamic-colors.js"></script>  
	<script type="text/javascript" src="../js/tabelle-clickable-teams.js"></script>  
</head>

<body>

<header><h1>FANTASY BUNDESLIGA</h1></header>
<?php include("navigation.php"); ?>

<div id = "hilfscontainer">
	<div class="flex-grid-fantasy"> 
		<div class="col">

			<!-- Display headline -->
			
			<div id="headline">
				<?php 
					include '../secrets/mysql_db_connection.php';
					$aktueller_spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter") -> fetch_object() -> spieltag;
					$vorheriger_spieltag = $aktueller_spieltag-1;
					echo "<h2>FANTASY TABELLE<br><small>STAND SPIELTAG " . $vorheriger_spieltag . "</small></h2>";
					?>
			</div>
			
			<!-- Display Fantasy standings -->

				<?php
					$result = mysqli_query($con,"	
						SELECT * 
						FROM xa7580_db1.ftsy_tabelle_2020 tab 

						INNER JOIN xa7580_db1.users_gamedata usr 
							ON usr.user_id = tab.player_id 
												 	
						WHERE 	tab.spieltag = (select max(spieltag) from ftsy_tabelle_2020 where season_id = (SELECT season_id FROM parameter) ) 
							 			and tab.season_id = (SELECT season_id FROM parameter)

						ORDER BY tab.rang ASC
						");

					// Print table header

					echo "
					<table id='spielstand' style='margin: 0px auto;''>
						<tr>
							<th style='text-align: center; padding-right:10px; padding-left:10px;' title='Position'>#</th>
							<th style='text-align: center;' title='Veränderung zum letzten Spieltag'>&#8645;</th>
							<th style='padding-right:35px;'>Team</th>
							<th style='text-align: center;' title='Summe eigner Scores'>+</th>
							<th style='text-align: center;' title='Summe gegnerischer Scores'>&minus;</th>
							<th style='text-align: center;' title='Score-Differenz'>&plusmn; </th>
							<th style='text-align: center;' title='Durchschnittlicher eigener Score'>&empty; + </th>
							<th style='text-align: center; padding-right:35px;' title='Durchschnittlicher gegnerischer Score'>&empty; &minus;</th>
							<th style='text-align: center;' title='Anzahl Siege'>S</th>
							<th style='text-align: center;' title='Anzahl Unentschieden'>U</th>
							<th style='text-align: center;' title='Anzahl Niederlagen'>N</th>
							<th style='text-align: center;' title='Anzahl Trostpreis (Bester Verlierer)'>T</th>
							<th style='padding-right:35px; text-align: center;' title='Ergebnisse letzte 3 Spiele'>Trend</th>
							<th style='padding-right:35px; text-align: center;' title='Head-to-Head (direkter Vergleich): Anzahl Siege gegen Konkurrenten mit gleichvielen Punkten'>H2H</th>
							<th style='padding-right:35px; text-align:center;' title='Summe Punkte'>Punkte</th>
							<th style='text-align: center; padding-right:10px;' title='Aktuelle Waiver-Position'>Waiver</th>
						</tr>";

					// Print out table rows

					while($col = mysqli_fetch_array($result)){
						echo "<tr>";	
						echo "<td style='text-align: center; font-weight:bold; padding-right:10px; padding-left:10px;'>" . $col['rang'] . "</td>";
						echo "<td style='text-align: center;' class='updown'>" . $col['updown'] . "</td>";
						echo "<td style='padding-right:35px; font-weight:bold'>" . utf8_encode($col['team_name']) . ' ' . utf8_encode($col['achievement_icons']) . "</td>";
						echo "<td style='text-align: right;'>" . $col['score_for'] . "</td>";
						echo "<td style='text-align: right;'>" . $col['score_against'] . "</td>";
						echo "<td style='text-align: right;'>" . $col['differenz'] . "</td>";
						echo "<td style='text-align: right;'>" . $col['avg_for'] . "</td>";
						echo "<td style='padding-right:35px; text-align: right;'>" . $col['avg_against'] . "</td>";
						echo "<td style='text-align: center;'>" . $col['siege'] . "</td>";
						echo "<td style='text-align: center;'>" . $col['unentschieden'] . "</td>";
						echo "<td style='text-align: center;'>" . $col['niederlagen'] . "</td>";
						echo "<td style='text-align: center;'>" . $col['trost'] . "</td>";
						echo "<td class='serie_color' style='padding-right:35px; color:gray;'>" . $col['serie'] . "</td>";
						echo "<td class='serie_color' style='padding-right:35px; text-align: center;'>" . $col['h2h'] . "</td>";
						echo "<td style='padding-right:35px; font-weight:bold; text-align: center;'>" . $col['punkte'] . "</td>";
						echo "<td style='text-align: center; padding-right:10px;'>#" . $col['waiver_position']. "</td>";
						echo "</tr>";
						}
					echo "</table>";
					mysqli_close($con);
				?>
			
			<!-- Footer -->
			
			<div style='font-size: 12px; color: black; text-align: center;'>
				<br><b>Tabellen-Regeln:</b> (1) Punkte (S: 3P, U: 1P, T: 1P) (2) H2H (3) Anzahl Siege (4) Erzielte Scores Saison (5) Kassierte Scores Saison (6) Zufall
			</div>
		
		</div>
	</div>
</div>
</body>
</html>