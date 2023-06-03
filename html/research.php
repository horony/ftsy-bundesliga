<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<!DOCTYPE html>
<html>
<head>
 	<title>FANTASY BUNDESLIGA</title> 

	<meta name="robots" content="noindex">
	<meta charset="UTF-8">   

	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/research.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
  <!-- Custom Scripts -->
  <script>
    // Defaul settings
    var geklickter_spieler = '<?php echo $_GET['click_player']; ?>'
  </script>

  <script type="text/javascript" src="../js/research-display-player.js"></script>  
  <script type="text/javascript" src="../js/research-search-function.js"></script>  
</head>

<body>
<header><h1>FANTASY BUNDESLIGA</h1></header>
  
  <!-- Navigation -->
	<div id = "hilfscontainer">
    <?php include("navigation.php"); ?>
	</div>

  <!-- Content -->
	<div id="headline" class="row">
		<h2 style='color:white; text-align: center'>SPIELER DATENBANK<h2>	
	</div>

	<div class="row">
    
    <!-- Column with all players listed -->
		<div class="overview">

			<div class="row" style="background: #f1f1f1">
        <input type="text" id="myInput" onkeyup="search()" placeholder="Spieler oder Besitzer suchen...">
      </div>
			
      <?php 
        include("../secrets/mysql_db_connection.php");
        mysqli_set_charset($con,"utf8");
  			
        // get meta-data
        $user = $_SESSION['username']; 	
        $ftsy_owner_type_column = strval($_SESSION['league_id']) . '_ftsy_owner_type';
        $ftsy_owner_id_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';

  			$result = mysqli_query($con,"	
          SELECT  base.id
                  , base.logo_path AS verein_logo
                  , base.display_name AS name
                  , base.position_short AS pos
                  , ftsy_score_sum AS total_fb_score
                  , ftsy_score_avg AS avg_fb_score
                  , CASE  WHEN ".$ftsy_owner_type_column." = 'WVR' THEN 'Waiver'
                          WHEN ".$ftsy_owner_type_column." = 'FA' THEN 'Free Agent'
                          WHEN ".$ftsy_owner_type_column." = 'USR' THEN 'Spieler'
                          ELSE NULL
                          END AS Besitzer 
          
          FROM xa7580_db1.`sm_playerbase_basic_v` base

          LEFT JOIN xa7580_db1.ftsy_scoring_snap snap
            ON snap.id = base.id
                                                
          WHERE  base.team_id IS NOT NULL 

          ORDER BY  total_fb_score DESC, avg_fb_score DESC
  			");

  			echo "<div class='kader row'><table id='myTable' border='0'>
  			<tr>
    			<th></th>
    			<th align='left'>Spieler</th>
    			<th align='left' title='Fantasy-Position'>Pos</th>
    			<th align='left' title='Summe aller Fantasy-Punkte über die Saison'>Total</th>
          <th align='left' title='Durchschnittliche Fantasy-Punkte über die Saison'>AVG</th>
    			<th align='left'>Besitzer</th>
  			</tr>";

        // Display all players
  			while($row = mysqli_fetch_array($result)) {
    			echo "<tr>";
      			echo "<td style='display:none;'>" . $row['id'] . "</td>";
      			echo "<td><img height='15px' width='auto' src='".$row['verein_logo']."'></td>"; 
            echo "<td>" . mb_convert_encoding($row['name'], 'UTF-8') . "</td>";
      			echo "<td>" . $row['pos'] . "</td>";
            echo "<td>" . utf8_encode($row['total_fb_score']) . "</td>";
            echo "<td>" . utf8_encode($row['avg_fb_score']) . "</td>";
            echo "<td>" . utf8_encode($row['Besitzer']) . "</td>"; 
  			  echo "</tr>";
  			}
  			echo "</table></div>";
  			mysqli_close($con);
			?>

		</div>

		<!-- Player profile -->
		<div class="spieler_spotlight">
		  <p>Wähle einen Spieler!</p>
		</div>
	</div>
</body>
</html>