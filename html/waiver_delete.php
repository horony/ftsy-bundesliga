<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<html>
<head>
    <title>FANTASY BUNDESLIGA</title> 
    <!-- Meta Tags -->
    <meta name="robots" content="noindex">
    <meta charset="UTF-8">

    <!-- Stylesheets -->
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/overall.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/waivers_trades.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">

    <!-- External Scripts -->
    <script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>
</head>

<body>

    <!-- Header image -->
    <header>
        <?php require "header.php"; ?>
    </header>

    <?php include("navigation.php"); ?>

    <!-- Content -->
    <main>
        <div id = "hilfscontainer">
            <div class="flex-grid-fantasy"> 
                <div class="col">

                    <div id="headline">
                        <h2>WAIVER & TRADE VERWALTUNG</h2>
                    </div>
                        
                    <!-- Incoming Trades -->

                    <?php 
                        include("../secrets/mysql_db_connection.php");
                        $user_id = $_SESSION['user_id']; //User festlegen für Besitzer

                        $trade_cnt1 = mysqli_query($con, "SELECT * FROM xa7580_db1.trade WHERE recipient = '".$user_id."' ") -> num_rows;   
                        if ($trade_cnt1 > 0){
                    ?>

                    <div class="div_wrap">              

                    <?php
                                
                        $result = mysqli_query($con,"   
                            SELECT  
                                trd.*
                                , usr.teamname
                                , base_add.short_code AS add_verein
                                , base_add.position_short AS add_pos
                                , base_drop.short_code AS drop_verein 
                                , base_drop.position_short AS drop_pos
                            FROM xa7580_db1.trade trd
                            INNER JOIN xa7580_db1.users usr
                                ON usr.id = trd.initiator
                            INNER JOIN xa7580_db1.sm_playerbase_basic_v base_add
                                ON trd.ini_trade_id = base_add.id
                            INNER JOIN xa7580_db1.sm_playerbase_basic_v base_drop
                                ON trd.rec_trade_id = base_drop.id
                            WHERE recipient = '".$user_id."'
                            ");
                                
                        echo "<p style='font-weight:bold; color: #4CAF50;'>Trade-Angebote an dich</p>";
                        echo "<table id='myTable'>
                        <tr>
                        <th style='display:none;'>ID</th>
                        <th>Angebot von</th>
                        <th>Du bekommst:</th>
                        <th>Du gibst ab:</th>
                        <th></th>
                        <th></th>
                        </tr>";

                        while($row = mysqli_fetch_array($result)){
                            echo "<tr>";
                                echo "<td style='display:none;'>" . $row['ID'] . "</td>";
                                echo "<td>" . mb_convert_encoding($row['teamname'],'UTF-8') . "</td>";
                                echo "<td><b>" . mb_convert_encoding($row['rec_player_name'], 'UTF-8') . "</b><small> " . mb_convert_encoding($row['drop_verein'], 'UTF-8') . "</small></td>";
                                echo "<td><b>" . mb_convert_encoding($row['ini_player_name'], 'UTF-8') . "</b><small> " . mb_convert_encoding($row['add_verein'], 'UTF-8') . "</small></td>";
                                echo "<td align='right'><a href='php/accept_trade_request_function.php?id=".$row['ID']."'<a title='Trade annehmen' class='submitpos'>&#10004;</a></td>";
                                echo "<td align='right'><a href='php/delete_trade_request_function.php?id=".$row['ID']."'<a title='Trade ablehnen' class='submit'>&#10007;</a></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                            
                    ?>
                    </div>
                    <?php } ?>

                    <!-- Outgoing Trades -->

                    <?php 
                        $trade_cnt2 = mysqli_query($con, "SELECT * FROM xa7580_db1.trade WHERE initiator = '".$user_id."' ") -> num_rows;   
                        if ($trade_cnt2 > 0){
                    ?>
                        
                    <div class="div_wrap">
                    
                    <?php
                        $result = mysqli_query($con,"   
                            SELECT  
                                trd.*
                                , usr.teamname
                                , base_add.short_code AS add_verein
                                , base_add.position_short AS add_pos
                                , base_drop.short_code AS drop_verein 
                                , base_drop.position_short AS drop_pos
                            FROM xa7580_db1.trade trd
                            INNER JOIN xa7580_db1.users usr
                                ON usr.id = trd.recipient
                            INNER JOIN xa7580_db1.sm_playerbase_basic_v base_add
                                ON trd.ini_trade_id = base_add.id
                            INNER JOIN xa7580_db1.sm_playerbase_basic_v base_drop
                                ON trd.rec_trade_id = base_drop.id
                            WHERE initiator = '".$user_id."'
                        ");

                        echo "<p style='font-weight:bold; color: #4CAF50;'>Trade-Angebote von dir</p>";
                        echo "<table id='myTable'>
                        <tr>
                        <th style='display:none;'>ID</th>
                        <th>Angebot an</th>
                        <th>Du bekommst:</th>
                        <th>Du gibst ab:</th>
                        <th></th>
                        <th></th>
                        </tr>";

                        while($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                                echo "<td style='display:none;'>" . $row['ID'] . "</td>";
                                echo "<td>" . utf8_encode($row['teamname']) . "</td>";
                                echo "<td><b>" . mb_convert_encoding($row['ini_player_name'], 'UTF-8') . "</b><small> " . utf8_encode($row['add_verein']) . "</small></td>";
                                echo "<td><b>" . mb_convert_encoding($row['rec_player_name'], 'UTF-8') . "</b><small> " . utf8_encode($row['drop_verein']) . "</small></td>";
                                echo "<td align='right'></td>";
                                echo "<td align='right'><a href='php/delete_trade_request_function.php?id=".$row['ID']."'<a title='Trade ablehnen' class='submit'>&#10007;</a></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        echo "<br>";
                                        
                    ?>

                    </div>
                    <?php } ?>

                    <!-- Waiver requests -->

                    <?php 
                        $waiver_cnt = mysqli_query($con, "SELECT * FROM xa7580_db1.waiver WHERE owner = '".$user_id."' ") -> num_rows;  
                        if ($waiver_cnt > 0){
                    ?>

                    <div class="div_wrap">
        
                    <?php
                        $result = mysqli_query($con,"SELECT ID, waiver_add_name, waiver_drop_name FROM xa7580_db1.waiver WHERE owner = '".$user_id."'");

                        echo "<p style='font-weight:bold; color: #4CAF50;'>Deine aktiven Waiver-Anfragen</p>";
                        echo "<table id='myTable'>  
                        <tr>
                            <th style='display:none;'>Waiver-ID</th>
                            <th>Du bekommst:</th>
                            <th>Du gibst ab:</th>
                            <th></th>
                        </tr>";

                        while($row = mysqli_fetch_array($result)) {
                            echo "<tr>";
                                echo "<td style='display:none;'>" . $row['ID'] . "</td>";
                                echo "<td>" . mb_convert_encoding($row['waiver_add_name'], 'UTF-8') . "</td>";
                                echo "<td>" . mb_convert_encoding($row['waiver_drop_name'], 'UTF-8') . "</td>";
                                echo "<td align='right'><a href='../php/jobs/delete-ftsy-waiver-request.php?id=".$row['ID']."'<a title='Waiver-Anfrage löschen' class='submit'>&#10007;</a></td>";
                            echo "</tr>";
                        }
                        echo "</table>";
                        
                        mysqli_close($con);
                    ?>
                    </div> 
                    <?php } ?>
            </div>
        </div>
    </div>
</main>
</body>
</html>