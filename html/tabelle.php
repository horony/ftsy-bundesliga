<?php include("../php/auth.php"); ?>
<!DOCTYPE html>
<html>
<head>
     <title>FANTASY BUNDESLIGA</title>

    <!-- Meta Tags -->
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">   
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/overall.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/tabelle.css">

    <!-- External Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
  
    <!-- Custom scripts -->
    <script type="text/javascript" src="../js/tabelle-dynamic-colors.js"></script>
    <script type="text/javascript" src="../js/tabelle-clickable-teams.js"></script>
</head>

<body>

<!-- Header image -->

<header>
    <?php require "header.php"; ?>
</header>

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
                WHERE
                    tab.spieltag = (SELECT MAX(spieltag) FROM ftsy_tabelle_2020 WHERE season_id = (SELECT season_id FROM parameter) ) 
                    AND tab.season_id = (SELECT season_id FROM parameter)
                ORDER BY tab.rang ASC
                ");

            // Print table header
            echo "
                <table id='spielstand' style='margin: 0px auto;''>
                    <tr>
                        <th style='th_position' title='Position'>#</th>
                        <th class='th_centered' title='Veränderung zum letzten Spieltag'>&#8645;</th>
                        <th class='th_left th_seperate_l' title='Team'>Team</th>
                        <th class='th_centered' title='Summe eigner Scores'>+</th>
                        <th class='th_centered' title='Summe gegnerischer Scores'>&minus;</th>
                        <th class='th_centered' title='Score-Differenz'>&plusmn; </th>
                        <th class='th_centered' title='Durchschnittlicher eigener Score'>&empty; + </th>
                        <th class='th_centered th_seperate_l' title='Durchschnittlicher gegnerischer Score'>&empty; &minus;</th>
                        <th class='th_centered' title='Anzahl Siege'>S</th>
                        <th class='th_centered' title='Anzahl Unentschieden'>U</th>
                        <th class='th_centered' title='Anzahl Niederlagen'>N</th>
                        <th class='th_centered th_seperate_l' title='Anzahl Trostpreis (Bester Verlierer)'>T</th>
                        <th class='th_centered th_seperate_l' title='Ergebnisse letzte 3 Spiele'>Trend</th>
                        <th class='th_centered th_seperate_l' title='Head-to-Head (direkter Vergleich): Anzahl Siege gegen Konkurrenten mit gleichvielen Punkten'>H2H</th>
                        <th class='th_centered th_seperate_l' title='Summe Punkte'>Punkte</th>
                        <th class='th_centered th_seperate_s' title='Aktuelle Waiver-Position'>Waiver</th>
                    </tr>";

            // Print out table rows
            while($col = mysqli_fetch_array($result)){
                echo "<tr>";	
                    echo "<td class='td_position'>" . $col['rang'] . "</td>";
                    echo "<td class='updown'>" . $col['updown'] . "</td>";
                    echo "<td class='td_teamname td_seperate_l'>" . utf8_encode($col['team_name']) . ' ' . utf8_encode($col['achievement_icons']) . "</td>";
                    echo "<td class='td_scoring'>" . $col['score_for'] . "</td>";
                    echo "<td class='td_scoring'>" . $col['score_against'] . "</td>";
                    echo "<td class='td_scoring'>" . $col['differenz'] . "</td>";
                    echo "<td class='td_scoring'>" . $col['avg_for'] . "</td>";
                    echo "<td class='td_scoring td_seperate_l'>" . $col['avg_against'] . "</td>";
                    echo "<td class='td_record'>" . $col['siege'] . "</td>";
                    echo "<td class='td_record'>" . $col['unentschieden'] . "</td>";
                    echo "<td class='td_record'>" . $col['niederlagen'] . "</td>";
                    echo "<td class='td_record td_seperate_l'>" . $col['trost'] . "</td>";
                    echo "<td class='serie_color td_seperate_l'>" . $col['serie'] . "</td>";
                    echo "<td class='td_h2h td_seperate_l'>" . $col['h2h'] . "</td>";
                    echo "<td class='td_punkte td_seperate_l'>" . $col['punkte'] . "</td>";
                    echo "<td class='td_waiver'>#" . $col['waiver_position']. "</td>";
                echo "</tr>";
                }
            echo "</table>";
            mysqli_close($con);
            ?>
            
            <!-- Footer -->
            <div style='font-size: 12px; color: black; text-align: center;'>
                <br><b>Tie-Braker-Regeln:</b> (1) Punkte (S: 3P, U: 1P, T: 1P) (2) H2H (3) Anzahl Siege (4) Erzielte Scores Saison (5) Kassierte Scores Saison (6) Zufall
            </div>
        </div>
    </div>
</div>
</body>
</html>