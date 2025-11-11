<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<html>
<head>

    <!-- Meta tags -->
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/transfermarkt.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css"> 	
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/overall.css">
    
    <!-- External Scripts -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

    <!-- Custom Scripts -->
    <script type="text/javascript" src="../js/tm-transfer-request.js"></script>  
    <script type="text/javascript" src="../js/tm-search-function.js"></script>  
    <script type="text/javascript" src="../js/tm-sort-function.js"></script>  
    <!-- <script type="text/javascript" src="../js/tm-filter-function.js"></script> -->
    
    <!-- Filter Function (inline fix) -->
    <script type="text/javascript">
        // Hide and show specific players  
        $(document).ready(function() {
            $('.filter_button').click(function() {
                var filter_to_change_name = $(this).attr('id');
                var filter_to_change_value = $(this).attr("data-active");
                var filter_value = $(this).attr("data-filter-value");
                var class_to_filter, hiding_class;
                
                // Handle owner filters (usr, wvr, fa) vs position filters (filter_pos_tw, etc.)
                if (filter_value === 'usr' || filter_value === 'wvr' || filter_value === 'fa') {
                    // Owner filters: short names
                    class_to_filter = '.filter_own_' + filter_value;
                    hiding_class = 'inactive_' + filter_value;
                } else if (filter_value.startsWith('filter_pos_')) {
                    // Position filters: full names  
                    class_to_filter = '.' + filter_value;
                    hiding_class = 'inactive_' + filter_value;
                } else {
                    // Fallback for any other pattern
                    class_to_filter = '.' + filter_value;
                    hiding_class = 'inactive_' + filter_value;
                }
                     
                if (filter_to_change_value == "0"){
                    $(this).attr('data-active', '1');
                    $(this).css('background-color', 'green');
                    $(class_to_filter).removeClass(hiding_class);
                } else if (filter_to_change_value == "1") {
                    $(this).attr('data-active', '0');
                    $(this).css('background-color', 'red');
                    $(class_to_filter).addClass(hiding_class);
                }
            });
        });

        // Hide USR players on page load
        function hideUSR(){
            $('.filter_own_usr').addClass('inactive_usr');
        }
    </script>

    <title>FANTASY BUNDESLIGA</title> 
</head>

<body onload="hideUSR()">
<div id = "hilfscontainer">

    <!-- Header image -->
    <header>
        <?php require "header.php"; ?>
    </header> 

    <!-- Navigation --> 
    <?php include("navigation.php"); ?>
    
    <!-- Content -->
    <main>
    <div id = "container" class="flex-grid"> 
        <div class="row_outer">
            
            <!-- Left column: Free Agents and players of other teams -->
            <div id = "free_agent" class="col">
                <div class="sub_header row" align=center>VERFÜGBARE SPIELER</div>
                <div class="filter_wrapper">
                    
                    <!-- Search bar -->
                    <div class="row search_bar_wrapper">
                        <input type="text" id="myInput" onkeyup="search()" placeholder="Suche Spieler oder Besitzer...">
                    </div>

                    <?php
                    include("../secrets/mysql_db_connection.php");
                    mysqli_set_charset($con,"utf8");

                    // prepare and bind
                    $stmt_sql_ava = file_get_contents('../sql/snippets/tm-available-players.sql');
                    $stmt_ava = $con->prepare($stmt_sql_ava);
                    $stmt_ava->bind_param("s", $user_id );

                    // set parameters and execute
                    $user = $_SESSION['username']; 							
                    $user_id = intval($_SESSION['user_id']);

                    // execute
                    $stmt_ava->execute();
                    $result_ava = $stmt_ava->get_result();

                    // Display filter buttons
                    echo "<div class='filter_button_row'>";
                        echo "<div id='tw_filter' class='filter_button' data-active='1' data-filter-value='filter_pos_tw'>TW</div>";
                        echo "<div id='aw_filter' class='filter_button' data-active='1' data-filter-value='filter_pos_aw'>AW</div>";
                        echo "<div id='mf_filter' class='filter_button' data-active='1' data-filter-value='filter_pos_mf'>MF</div>";
                        echo "<div id='st_filter' class='filter_button' data-active='1' data-filter-value='filter_pos_st'>ST</div>";
                        echo "<div id='usr_filter' class='filter_button' data-active='0' data-filter-value='usr'>USR</div>";
                        echo "<div id='wvr_filter' class='filter_button' data-active='1' data-filter-value='wvr'>WVR</div>";
                        echo "<div id='fa_filter' class='filter_button' data-active='1' data-filter-value='fa'>FA</div>";
                    echo "</div>";
                echo "</div>"; //Filterwrapper End

                echo "<div class='kader row'><table id='myTable' border='0'>
                    <tr>
                        <th></th>
                        <th title='Spieler'>Spieler</th>
                        <th title='Fantasy-Position' onclick='sortTable(3)' align='center' style='cursor: pointer;'>Pos</th>				
                        <th title='Matchup aktueller Spieltag'>Matchup</th>
                        <th title='Gesamte Fantasy-Punkte über die Saison' onclick='sortTable(5)' align='center' style='cursor: pointer;'>&udarr; Saison</th>
                        <th title='Durchschnittliche Fantasy-Punkte über die Saison' onclick='sortTable(6)' align='center' style='cursor: pointer;'>&udarr; Avg</th>
                        <th title='Fantasy-Punkte letzter Spieltag' onclick='sortTable(7)' align='center' style='cursor: pointer;'>&udarr; Last</th>
                        <th title='Projection aktueller Spieltag' onclick='sortTable(8)' align='center' style='cursor: pointer;'>&udarr; Proj</th>					
                        <th title='Fitness' align='center'>Status</th>
                        <th title='Aktueller Besitzer des Spielers' onclick='sortTable(9)' style='cursor: pointer;'>Besitzer</th>
                        <th>Add</th>
                    </tr>";

                    // Display SQL results
                    while($row = mysqli_fetch_array($result_ava)) {
                        $filter_class = 'filter_pos_' . strtolower($row['pos']);
                        $filter_own = 'filter_own_' . strtolower($row['Besitzer']);
                        // Prefix owner class to avoid Font Awesome conflicts
                        $owner_class = 'owner_' . strtolower($row['Besitzer']);
                        
                        if ($row['rank_allowed'] <= 5){
                            $opp_color = '#d0001f';
                        } elseif ($row['rank_allowed'] >= 14){
                            $opp_color = '#079c07;';
                        } else {
                            $opp_color = 'black';
                        }

                        $link_datenbank = 'research.php?click_player=' . strval($row['id']);
                        $matchup_expl = mb_convert_encoding($row['opp_name'], 'UTF-8') . ' lässt im Schnitt ' . $row['avg_allowed'] . ' Punkte gegen ' . $row['pos'] . ' zu (Platz ' .$row['rank_allowed']. '/18).';

                        echo "<tr class='" . $owner_class . " " . $filter_class." ".$filter_own. "'>";
                            echo "<td style='display:none;'>" . $row['id'] . "</td>";
                            echo "<td title='Verein' align='center'><img height='15px' width='auto' src='" . $row['verein_logo'] . "'></td>";
                            echo "<td title='Spieler'><a href='" . $link_datenbank . "'>" . mb_convert_encoding($row['name'], 'UTF-8') . "</a></td>";
                            echo "<td title='Fantasy-Position' align='left'>" . $row['pos'] . "</td>";
                            echo "<td title='".$matchup_expl."' align='left'><span style= color:".$opp_color.";'>  vs. " . mb_convert_encoding($row['opp_code'], 'UTF-8') . "</span></td>";			
                            echo "<td title='Gesamte Fantasy-Punkte über die Saison' align='center'>" . $row['total_fb_score'] . "</td>";
                            echo "<td title='Durchschnittliche Fantasy-Punkte über die Saison' align='center'>" . $row['avg_fb_score'] . "</td>";
                            echo "<td title='Fantasy-Punkte letzter Spieltag' align='center'>" . $row['last1_total_fb_score'] . "</td>";
                            echo "<td title='Projection aktueller Spieltag' align='center' style='color: #483D8B'>" . $row['ftsy_score_projected'] . "</td>";										
                            echo "<td align='center'>"; 
                            if ($row['fitness'] == 'fit'){echo "<img title='Fit' height='15px' width='auto' src='../img/icons/fit.png'>";}
                                elseif ($row['fitness'] == 'injured'){echo "<img title='Verletzt'height='15px' width='auto' src='../img/icons/verletzung.png'>";}
                                elseif ($row['fitness'] == 'suspended'){echo "<img title='Gesperrt' height='15px' width='auto' src='../img/icons/gelb-rote-karte.png'>";}
                            echo "</td>";
                            echo "<td>";
                            echo $row['Besitzer'];
                            echo "</td>";
                            echo "<td align='center'><input type='checkbox' class='check1'></td>";
                        echo "</tr>";
                        }
                echo "</table></div>";
                $stmt_ava->close();
                ?>
                <br>
            <div id="selected_players" class="row player">Wähle Spieler</div> 
            </div> 

            <!-- Righ column: Players owned by the user -->
            
            <div id = user_kader class="col">

                <div class="sub_header row" align=center>
                    DEINE SPIELER
                </div>

                <!-- Search function -->
                <div class="row">
                    <input type="text" id="myInput2" onkeyup="search_my()" placeholder="Suche abzugebenden Spieler...">
                </div>

                <?php 
              // Query all players owned by user

                    // prepare and bind
               $stmt_sql_own = file_get_contents('../sql/snippets/tm-owned-players.sql');
                    $stmt_own = $con->prepare($stmt_sql_own);
                    $stmt_own->bind_param("s", $user_id_owned );

                    // set parameters and execute
                    $user_id_owned = intval($_SESSION['user_id']);

                    $stmt_own->execute();
                    $result_own = $stmt_own->get_result();
                
                    echo "<div class='kader row'><table id='myTable2' border='0'>
                    <tr>
                        <th></th>
                        <th title='Spieler'>Spieler</th>
                        <th title='Fantasy-Position' onclick='sortTable(3)' align='center' style='cursor: pointer;'>Pos</th>				
                        <th title='Matchup aktueller Spieltag'>Matchup</th>
                        <th title='Gesamte Fantasy-Punkte über die Saison' onclick='sortTable(5)' align='center' style='cursor: pointer;'>&udarr; Saison</th>
                        <th title='Durchschnittliche Fantasy-Punkte über die Saison' onclick='sortTable(6)' align='center' style='cursor: pointer;'>&udarr; Avg</th>
                        <th title='Fantasy-Punkte letzter Spieltag' onclick='sortTable(7)' align='center' style='cursor: pointer;'>&udarr; Last</th>
                        <th title='Projection aktueller Spieltag' onclick='sortTable(8)' align='center' style='cursor: pointer;'>&udarr; Proj</th>					
                        <th title='Fitness' align='center'>Status</th>
                        <th>Drop</th>
                    </tr>";

                    while($row = mysqli_fetch_array($result_own)) {
                        if ($row['rank_allowed'] <= 5){
                            $opp_color = '#d0001f';
                        } elseif ($row['rank_allowed'] >= 14){
                            $opp_color = '#079c07;';
                        } else {
                            $opp_color = 'black';
                        }
                        
                        $link_datenbank = 'research.php?click_player=' . strval($row['id']);
                        $matchup_expl = mb_convert_encoding($row['opp_name'], 'UTF-8') . ' lässt im Schnitt ' . $row['avg_allowed'] . ' Punkte gegen ' . $row['pos'] . ' zu (Platz ' .$row['rank_allowed']. '/18).';

                        echo "<tr>";
                        echo "<td style='display:none;'>" . $row['id'] . "</td>";
                        echo "<td title='Verein' align='center'><img height='15px' width='auto' src='" . $row['verein_logo'] . "'></td>";
                        echo "<td title='Spieler'><a href='" . $link_datenbank . "'>" . mb_convert_encoding($row['name'], 'UTF-8') . "</a></td>";
                        echo "<td title='Fantasy-Position' align='left'>" . $row['pos'] . "</td>";	
                        echo "<td title='".$matchup_expl."' align='left'><span style= color:".$opp_color.";'> vs. " . mb_convert_encoding($row['opp_code'], 'UTF-8') . "</span></td>";			
                        echo "<td title='Gesamte Fantasy-Punkte über die Saison' align='center'>" . $row['total_fb_score'] . "</td>";
                        echo "<td title='Durchschnittliche Fantasy-Punkte über die Saison' align='center'>" . $row['avg_fb_score'] . "</td>";
                        echo "<td title='Fantasy-Punkte letzter Spieltag' align='center'>" . $row['last1_total_fb_score'] . "</td>";
                        echo "<td title='Projection aktueller Spieltag' align='center' style='color: #483D8B'>" . $row['ftsy_score_projected'] . "</td>";					
                        echo "<td align='center'>"; 
                        if ($row['fitness'] == 'fit'){echo "<img title='Fit' height='15px' width='auto' src='../img/icons/fit.png'>";}
                             elseif ($row['fitness'] == 'injured'){echo "<img title='Verletzt'height='15px' width='auto' src='../img/icons/verletzung.png'>";}
                             elseif ($row['fitness'] == 'suspended'){echo "<img title='Gesperrt' height='15px' width='auto' src='../img/icons/gelb-rote-karte.png'>";}
                        echo "</td>";
                        echo "<td align='center'><input type='checkbox' class='check2'></td>";
                        echo "</tr>";
                    }
                    echo "</table></div>";

                    $stmt_own->close();
                    mysqli_close($con);
                ?>
                <br><div id="dropped_players" class="row player">Wähle Spieler</div> 
            </div>
        </div>

    </div> 
    
    <!-- Transfer request button - outside flex-grid, spans full width -->
    <div id="submit_button_div">
        <!-- Place transfer request -->
        <button id="waiver_speichern" onclick="tranfer_request()">Transferanfrage abschicken</button>
    </div>

    </main>

</div>
</body>
</html>	