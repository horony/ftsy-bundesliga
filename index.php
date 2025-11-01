<?php require('php/auth.php'); ?>
<!DOCTYPE html>
<html>

<head>
    <title>FANTASY BUNDESLIGA</title> 
    <link rel="stylesheet" type="text/css" media="screen, projection" href="css/nav.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="css/index.css">

    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>

    <!-- Custom scripts -->
    <script type="text/javascript" src="js/home-display-fixtures.js"></script>
    <script type="text/javascript" src="js/home-display-standings.js"></script>
    <script type="text/javascript" src="js/home-clickable-elements.js"></script>	

</head>

<body onload="showFantasy(); changeColorScores1(); showFantasyTabelle(); changeColorTabelle1()">

<!-- Header image -->
<header>
    <?php require "html/header.php"; ?>
</header>

<!-- Navigation menu -->
<div id = "hilfscontainer">
    <?php require "html/navigation.php"; ?>
</div>

<div id = "wrapper">
    <div id="headline" class="row">
        <h3><h3>	
    </div>

    <!-- Actual page content -->

    <div class="row" id="main_section">
        <div id="feed">
            <div id="feed_header">NEUIGKEITEN AUS DER LIGA</div>
            <div class="main_bar">

                <!-- Rolling news ticker -->
                <div class="tickerv-wrap">
                    <ul>
                        <?php
                        include 'secrets/mysql_db_connection.php';
                        $result = mysqli_query($con, "SELECT * FROM news_ligainsider ORDER BY load_ts DESC LIMIT 5");	
                        $row_cnt = $result->num_rows;
                        if ($row_cnt > 0){
                            while($row = mysqli_fetch_array($result)) {
                                echo "<li>";
                                echo "<a href='".$row['li_link']."'>".mb_convert_encoding($row['headline'], 'UTF-8')."</a>";
                                echo "</li>";	
                            }
                        }
                        ?>
                    </ul>
                </div>

                <!-- Column [1]: News -->
                <div class="">
                    <?php
                    include 'secrets/mysql_db_connection.php';
                    mysqli_set_charset($con,"utf8");
            
                    $result = mysqli_query($con, "	
                        SELECT 	
                            news.headline
                            , news.name
                            , news.`timestamp` as create_dt
                            , news.add_id 
                            , news.drop_id 
                            , news.add_besitzer
                            , news.drop_besitzer 
                            , news.`type` AS news_type
                            , user.teamname
                            , COALESCE(base_add.display_name, buli_home.name) AS added_player
                            , base_add.name AS added_verein
                            , base_add.image_path AS added_img
                            , COALESCE(base_add.logo_path, buli_home.logo_path) AS added_team_img
                            , base_add.position_short AS added_pos
                            , COALESCE(base_drop.display_name, buli_away.name) AS dropped_player
                            , base_drop.name AS dropped_verein
                            , base_drop.image_path AS dropped_img
                            , COALESCE(base_drop.logo_path, buli_away.logo_path) AS dropped_team_img
                            , base_drop.position_short AS dropped_pos
                            , news.story
                        FROM xa7580_db1.news news
                        LEFT JOIN users user
                            ON user.id = news.drop_besitzer
                        LEFT JOIN sm_playerbase_basic_v base_add
                            ON news.add_id = base_add.id 
                            AND news.`type` != 'buli_ergebnis'
                        LEFT JOIN sm_playerbase_basic_v base_drop
                            ON news.drop_id = base_drop.id 
                            AND news.`type` != 'buli_ergebnis'
                        LEFT JOIN sm_teams buli_home
                            ON news.add_id = buli_home.id 
                            AND news.`type` = 'buli_ergebnis'
                        LEFT JOIN sm_teams buli_away
                            ON news.drop_id = buli_away.id 
                            AND news.`type` = 'buli_ergebnis'
                        WHERE 	
                            news.league_id = 0 
                            OR news.league_id = '".$_SESSION['league_id']."'
                        ORDER BY news.id DESC 
                        LIMIT 30
                    ");	

                    $row_cnt = $result->num_rows;

                    if ($row_cnt > 0){
                        while($row = mysqli_fetch_array($result)) {
                            /* Highlight particular news */
                            if ($row['news_type'] == 'neuzugang' or $row['news_type'] == 'abgang' or $row['news_type'] == 'news' or $row['news_type'] == 'spieltag_abschluss' or $row['news_type'] == 'li_news'){
                                echo "<div class='news_article_wrapper news_highlight'>";
                            } else {
                                echo "<div class='news_article_wrapper'>";
                            }

                            /***********************************/
                            /*	Define different news types    */
                            /***********************************/

                            if ($row['news_type'] == 'free_agent') {
                                /* User adds player from free agency */

                                $link = 'html/mein_team.php?show_team=' . strval(mb_convert_encoding($row['teamname'], 'UTF-8'));
                                $link_player_add = 'html/research.php?click_player=' . strval($row['add_id']);
                                $link_player_drop = 'html/research.php?click_player=' . strval($row['drop_id']);

                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['added_img'] . "'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                    echo "<img class='news_player_img' src='" . $row['dropped_img'] . "'>";
                                echo "</div>";
                                echo "<div class='news_article_text'>";
                                    echo "<b>Free Agency: </b><a href='" . $link . "' class='news_team' data-id='" . $row['team'] . "'>" . mb_convert_encoding($row['teamname'], 'UTF-8') . "</a> verpflichtet Free Agent <a href='" . $link_player_add . "' class='news_player' data-id='" . $row['add_id'] . "'>" . mb_convert_encoding($row['added_player'], 'UTF-8') . "</a> (" . mb_convert_encoding($row['added_verein'], 'UTF-8') . ") und entlässt <a href='" . $link_player_drop . "' class='news_player' data-id='" . $row['drop_id'] . "'>" . mb_convert_encoding($row['dropped_player'], 'UTF-8') . "</a> (" . mb_convert_encoding($row['dropped_verein'], 'UTF-8') . ").";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";
                                echo "</div>";

                            } elseif ($row['news_type'] == 'waiver_wire') {
                                /* User adds player from waiver */

                                $link = 'html/mein_team.php?show_team=' . strval(mb_convert_encoding($row['teamname'], 'UTF-8'));
                                $link_player_add = 'html/research.php?click_player=' . strval($row['add_id']);
                                $link_player_drop = 'html/research.php?click_player=' . strval($row['drop_id']);

                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['added_img'] . "'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                        echo "<img class='news_player_img' src='" . $row['dropped_img'] . "'>";
                                echo "</div>";
                                echo "<div class='news_article_text'>";
                                    echo "<b>Waiver-Wire: </b><a href='" . $link . "' class='news_team' data-id='" . $row['team'] . "'>" . mb_convert_encoding($row['teamname'], 'UTF-8') . "</a> verpflichtet  <a href='" . $link_player_add . "' class='news_player' data-id='" . $row['add_id'] . "'>" . mb_convert_encoding($row['added_player'],'UTF-8') . "</a> (" . mb_convert_encoding($row['added_verein'], 'UTF-8') . ") vom Waiver und entlässt <a href='" . $link_player_drop . "' class='news_player' data-id='" . $row['drop_id'] . "'>" . mb_convert_encoding($row['dropped_player'], 'UTF-8') . "</a> (" . mb_convert_encoding($row['dropped_verein'], 'UTF-8') . ").";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";
                                echo "</div>";	
                            } elseif ($row['news_type'] == 'neuzugang') {
                                /* Bundesliga team added new player (transfer) */

                                $link_player_add = 'html/research.php?click_player=' . strval($row['add_id']);

                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['added_team_img'] . "'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                        echo "<img class='news_player_img' src='" . $row['added_img'] . "'>";
                                echo "</div>";
                                echo "<div class='news_article_text'>";
                                    echo "<b>Neuzugang! </b>" . mb_convert_encoding($row['added_verein'], 'UTF-8') . "</a> hat <a href='" . $link_player_add . "' class='news_player' data-id='" . $row['add_id'] . "'>" . mb_convert_encoding($row['added_player'], 'UTF-8') . "</a> (" .$row['added_pos']. ") unter Vertrag genommen. Der Spieler ist jetzt auf dem Waiver-Wire verfügbar.";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";
                                echo "</div>";	
                            } elseif ($row['news_type'] == 'abgang') {
                                /* Bundesliga team drops player (transfer) */
                                /* Currently deprecated 
                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['dropped_team_img'] . "'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                        echo "<img class='news_player_img' src='" . $row['dropped_img'] . "'>";
                                echo "</div>";
                                echo "<div class='news_article_text'>";
                                    echo "<b>Abgang! </b>" . mb_convert_encoding($row['dropped_verein'], 'UTF-8') . " hat sich von  <span class='news_player'>" . mb_convert_encoding($row['dropped_player'], 'UTF-8') . "</span> (" .$row['dropped_pos']. ") getrennt. Der Spieler sucht sein Glück nun außerhalb der 1. Bundesliga!";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";

                                echo "</div>";	
                                */
                            } elseif ($row['news_type'] == 'trade') {
                                /* Trade between two users */

                                // Define team links
                                $link = 'html/view_team.php?click_team=' . strval(utf8_encode($row['teamname']));
                                $second_team = mysqli_query($con, "SELECT teamname from xa7580_db1.users where id = '". $row['add_besitzer'] ."'") -> fetch_object() -> teamname;
                                $link_2 = 'html/view_team.php?click_team=' . strval(utf8_encode($second_team));

                                // Define player links
                                $link_player_add = 'html/research.php?click_player=' . strval($row['add_id']);
                                $link_player_drop = 'html/research.php?click_player=' . strval($row['drop_id']);

                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['added_img'] . "'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                    echo "<img class='news_player_img' src='" . $row['dropped_img'] . "'>";
                                echo "</div>";
                                echo "<div class='news_article_text'>";
                                    echo "<b>Trade: </b><a href='" . $link . "' class='news_team'>" . mb_convert_encoding($row['teamname'], 'UTF-8') . "</a> gibt <a href='" . $link_player_drop . "' class='news_player' data-id='" . $row['drop_id'] . "'>" . mb_convert_encoding($row['dropped_player'], 'UTF-8') . "</a> (" . mb_convert_encoding($row['dropped_verein'], 'UTF-8') . ") an <a href='" . $link_2 . "' class='news_team'>" . mb_convert_encoding($second_team, 'UTF-8') . "</a> ab und erhält im Gegenzug <a href='" . $link_player_add . "' class='news_player' data-id='" . $row['add_id'] . "'>" . mb_convert_encoding($row['added_player'], 'UTF-8') . "</a> (" . mb_convert_encoding($row['added_verein'], 'UTF-8') . ").";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";
                                echo "</div>";		
                            } elseif ($row['news_type'] == 'spieltag_abschluss') {
                                /* Fantasy Round finalized by admin */

                                echo "<div class='news_spieltag_abschluss'>";
                                echo mb_convert_encoding($row['story'], 'UTF-8');
                                echo "</div>";
                            } elseif ($row['news_type'] == 'news') {
                                /* Admin news */
                                echo "<div class='news_news'>";
                                    echo "<h4>" . mb_convert_encoding($row['headline'], 'UTF-8') . "</h4>";
                                    echo mb_convert_encoding($row['story'], 'UTF-8');
                                    echo "<br><br><span class='news_create_dt'>" . $row['create_dt'] . "</span>";
                                echo "</div>";
                            } elseif ($row['news_type'] == 'li_news') {
                                /* LigaInsider news */
                                $link = strval(mb_convert_encoding($row['add_besitzer'], 'UTF-8'));
                                echo "<div class='news_news'>";
                                    echo "<b>LigaInsider: </b>".mb_convert_encoding($row['story'], 'UTF-8').". <a href='" . $link . "'>Zum Artikel »</a></br>";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . " by " . $row['name'] . "</span>";
                                echo "</div>";	
                            } elseif ($row['news_type'] == 'buli_ergebnis') {
                                /* Bundesliga fixture final score */ 
                                echo "<div class='img_player added_player'>";
                                    echo "<img class='news_player_img' src='" . $row['added_team_img'] . "' style='max-width: 60px; max-height: 60px;'>";
                                echo "</div>";
                                echo "<div class='img_player dropped_player'>";
                                    echo "<img class='news_player_img' src='" . $row['dropped_team_img'] . "' style='max-width: 60px; max-height: 60px;'>";
                                echo "</div>";
                                echo "<div class='news_news'>";
                                    echo "<b>Bundesliga: </b>"  . mb_convert_encoding($row['story'], 'UTF-8') . ".";
                                    $link = 'html/view_match_buli.php?ID=' . strval($row['headline']) . ' ';
                                    echo "<a href='" . $link . "'>Zum Spiel »</a></br>";
                                    echo "<br><span class='news_create_dt'>" . $row['create_dt'] . " by " . $row['name'] . "</span>";
                                echo "</div>";	
                            }
                            echo "</div>";
                        };
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Sidebar on the right [2]: Infos like current formation or fixtures -->

        <div class="side_bar">
            <!-- Collect data for status box -->
            <?php
            $akt_spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter ") -> fetch_object() -> spieltag;	

            // Get open trades for user 
            $cnt_trades = mysqli_query($con, "SELECT COUNT(*) AS cnt_trades FROM xa7580_db1.trade WHERE recipient = '".$_SESSION['user_id']."' OR initiator = '".$_SESSION['user_id']."' ") -> fetch_object() -> cnt_trades;	

            // Get open waiver requests for user 
            $cnt_waiver = mysqli_query($con, "SELECT COUNT(*) AS cnt_waiver FROM xa7580_db1.waiver WHERE owner = '".$_SESSION['user_id']."' ") -> fetch_object() -> cnt_waiver;	
            $ftsy_owner_id_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
            $ftsy_match_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';

            // Get number of players in current formation for user
            $cnt_aufstellung = mysqli_query($con, "SELECT COUNT(*) AS cnt_aufstellung FROM xa7580_db1.ftsy_player_ownership WHERE ".$ftsy_owner_id_column." = '".$_SESSION['user_id']."' AND ".$ftsy_match_status_column." != 'NONE' ") -> fetch_object() -> cnt_aufstellung;	

            // Get number of injured players in current formation for user
            $cnt_aufstellung_verletzt = mysqli_query($con, "
                SELECT
                    COUNT(*) as cnt_aufstellung_verletzt 
                FROM xa7580_db1.sm_playerbase_basic_v base
                INNER JOIN xa7580_db1.sm_fixtures fix
                    ON fix.round_name = '".$akt_spieltag."'
                    AND ( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id )
                WHERE 
                    ".$ftsy_owner_id_column." = '".$_SESSION['user_id']."' 
                    AND base.".$ftsy_match_status_column." != 'NONE' 
                    AND base.is_sidelined = 1
                    AND fix.kickoff_ts >= NOW()
            ") -> fetch_object() -> cnt_aufstellung_verletzt;
            ?>

            <!-- Display collected data in status box -->
            <?php if ($cnt_trades > 0 or $cnt_trades > 0 or $cnt_aufstellung != 11 or $cnt_aufstellung_verletzt > 0){?>
                <div class="row graybox" id="notifications">
                    <div class="graybox_header">ALERTS</div>

                <!-- Show alerts -->
                <div>
                    <?php
                    // Formation
                    if ($cnt_aufstellung != 11){
                        echo "<div class='alert_wrap'><div class='alert_badge'><span class='red_badge'>1</span></div><div class='alert_text'><b>Aufstellung: </b>Du hast ".$cnt_aufstellung." Spieler aufgestellt.<br><br><a href='html/mein_team.php?show_team='".mb_convert_encoding($_SESSION["user_teamname"],'UTF-8')."''>>> Bearbeite Aufstellung</a></div></div>";
                    } elseif ($cnt_aufstellung_verletzt > 0) {
                        echo "<div class='alert_wrap'><div class='alert_badge'><span class='red_badge'>".$cnt_aufstellung_verletzt."</span></div><div class='alert_text'><b>Aufstellung: </b>Du hast aktuell nicht fitte Spieler aufgestellt.<br><br><a href='html/mein_team.php?show_team='".mb_convert_encoding($_SESSION["user_teamname"],'UTF-8')."''>>> Bearbeite Aufstellung</a></div></div>";
                    }
                    // Trades
                    if ($cnt_trades > 0){
                        echo "<div class='alert_wrap'><div class='alert_badge'><span class='red_badge'>".$cnt_trades."</span></div><div class='alert_text'><b>Trades:</b> Du hast ".$cnt_trades." aktive Trade-Anfragen.<br><br><a href='html/waiver_delete.php'>>> Verwalte Trades</a></div></div>";
                    }
                    // Waivers
                    if ($cnt_waiver > 0){
                        echo "<div class='alert_wrap'><div class='alert_badge'><span class='red_badge'>".$cnt_waiver."</span></div><div class='alert_text'><b>Waiver:</b> Du hast ".$cnt_waiver." aktive Waiver-Anfragen.<br><br><a href='html/waiver_delete.php'>>> Verwalte Waiver</a></div></div>";
                    }
                    ?>
                </div>
                </div>
            <?php } ?>

            <!-- Next waiver-box -->
            <div class="row graybox" id="next_waiver">
                <div class="graybox_header">NÄCHSTER WAIVER</div>
                <div id="next_waiver_date">
                <?php
                include("secrets/mysql_db_connection.php");
                      
                //Datetime of next waiver
                $next_waiver = mysqli_query($con, "
                    SELECT 	
                        CASE 
                            WHEN waiver_date_1 > NOW() THEN 
                                CONCAT(
                                    CASE
                                        WHEN DAYOFWEEK(waiver_date_1) = 1 THEN 'Sonntag, ' 
                                        WHEN DAYOFWEEK(waiver_date_1) = 2 THEN 'Montag, '
                                        WHEN DAYOFWEEK(waiver_date_1) = 3 THEN 'Dienstag, '
                                        WHEN DAYOFWEEK(waiver_date_1) = 4 THEN 'Mittwoch, '
                                        WHEN DAYOFWEEK(waiver_date_1) = 5 THEN 'Donnerstag, '
                                        WHEN DAYOFWEEK(waiver_date_1) = 6 THEN 'Freitag, '
                                        WHEN DAYOFWEEK(waiver_date_1) = 7 THEN 'Samstag, ' 
                                        ELSE ''
                                        END
                                    , DATE_FORMAT(waiver_date_1, '%e.%m %H:%i')
                                    )
                            WHEN waiver_date_1 <= NOW() AND waiver_date_2 > NOW() THEN 
                                CONCAT(
                                    CASE
                                        WHEN DAYOFWEEK(waiver_date_2) = 1 THEN 'Sonntag, ' 
                                        WHEN DAYOFWEEK(waiver_date_2) = 2 THEN 'Montag, '
                                        WHEN DAYOFWEEK(waiver_date_2) = 3 THEN 'Dienstag, '
                                        WHEN DAYOFWEEK(waiver_date_2) = 4 THEN 'Mittwoch, '
                                        WHEN DAYOFWEEK(waiver_date_2) = 5 THEN 'Donnerstag, '
                                        WHEN DAYOFWEEK(waiver_date_2) = 6 THEN 'Freitag, '
                                        WHEN DAYOFWEEK(waiver_date_2) = 7 THEN 'Samstag, ' 
                                        ELSE ''
                                        END
                                    , DATE_FORMAT(waiver_date_2, '%e.%m %H:%i')
                                    )
                            ELSE 'Nach Spieltagabschluss' 
                        END AS waiver_datum 
                FROM xa7580_db1.parameter") -> fetch_object() -> waiver_datum; 

                echo $next_waiver;
                ?>
                    <div id="waiver_nav">
                        <a id="" href='html/transfermarkt.php'>» Waiver-Anfrage erstellen</a>
                    </div>
                </div>
            </div>

            <!-- Fixture box -->
            <div class="row graybox" id="scores">
                <div class="graybox_header">ERGEBNISSE</div>
                <div id="scores_nav">
                    <ul>
                        <li><a id="button_fantasy_scores" onclick="showFantasy(); changeColorScores1()">FANTASY</a></li>
                        <li><a id="button_bundesliga_scores" onclick="showBundesliga(); changeColorScores2()">BUNDESLIGA</a></li>
                    </ul>
                </div>  
                <div id="boxscores">
                    <!-- Content loaded by get-current-fantasy-fixtures.php and get-current-bundesliga-fixtures.php -->
                </div>
                <div></div>
            </div>

            <!-- Standings -->
            <div class="row graybox" id="standings">
                <div class="graybox_header">TABELLE</div>
                <div id="scores_nav">
                  <ul>
                      <li><a id="button_fantasy_tabelle" onclick="showFantasyTabelle(); changeColorTabelle1()">FANTASY</a></li>
                      <!-- <li><a id="button_bundesliga_tabelle" onclick="changeColorTabelle2()">BUNDESLIGA</a></li> -->
                  </ul>
                </div>
                <div id="tabellen">
                    <!-- Content loaded by get-current-fantasy-standings.php -->
                </div>
                <div id="table_nav">
                    <a id="" href='html/tabelle.php'>» Zur Tabelle</a>
                </div>
                <div></div>	
            </div>
        </div>
    </div>
</body>
</html>
