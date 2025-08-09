<?php include("../php/auth.php"); ?>

<html>
<head>
	<title>FANTASY BUNDESLIGA</title> 
	<meta name="robots" content="noindex">
	<meta name="viewport" content="width=device-width, initial-scale=0.6, maximum-scale=3, minimum-scale=0.1, user-scalable=no, minimal-ui">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
	<link rel="stylesheet" type="text/css" media="screen, projection" href="../dev/css/mein_team.css">

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://code.jquery.com/jquery-1.12.4.js"></script>

	<!-- Custom scripts -->
	<script>
		$clicked_spieltag = 'Aktueller Spieltag';
		window.active_view = 'Graph';
	</script>

	<script type="text/javascript" src="../js/ftsy-team-change-round.js"></script> 
	<script type="text/javascript" src="../js/ftsy-team-change-view.js"></script> 
	<script type="text/javascript" src="../js/ftsy-team-player-subs.js"></script> 
</head>

<body> 

<!-- Header image -->

<header>
  <?php require "header.php"; ?>
</header> 

<!-- Navigation -->

<div id = "hilfscontainer">
	<?php include("../html/navigation.php"); ?>
</div>

<!-- Content -->

<div id = "wrapper">
	<div id="content_wrapper">
		<div id="headline_wrapper">
			<?php 
				include '../secrets/mysql_db_connection.php';

				// Collect data

				if (empty($_GET["show_team"])) {
					$show_team = mb_convert_encoding($_SESSION['username'], 'UTF-8');
				} else {
					$show_team =  mb_convert_encoding($_GET["show_team"], 'UTF-8');	
					$show_team = mysqli_query($con, "SELECT username from xa7580_db1.users WHERE teamname = '".$show_team."' ") -> fetch_object() -> username;	
				}

				$abg_spieltag = mysqli_query($con, "SELECT CAST(spieltag - 1  as unsigned) as spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
				$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
				$abg_spieltag = intval($abg_spieltag);
				
				$team_info = mysqli_query($con, "	
					SELECT 	usr.username as manager 
									, usr.akt_aufstellung
									, usr.waiver_position
									, tab.rang
									, usr_2.teamname as team_name
									, tab.punkte
									, tab.avg_for
									, tab.serie
									, concat(tab.Siege, '-', tab.Unentschieden, '-', tab.Niederlagen) as bilanz
									, case when sch.ftsy_home_id = usr.user_id then sch.ftsy_away_name else sch.ftsy_home_name end as gegner_team
									, opp.rang as gegner_rang
									, opp.serie as gegner_serie
									, opp.avg_for as gegner_avg_for
									, concat(opp.Siege, '-', opp.Unentschieden, '-', opp.Niederlagen) as gegner_bilanz
									, sch.ftsy_match_id as match_id
									, sch.match_type as match_type

						FROM 	xa7580_db1.users_gamedata usr

						INNER JOIN xa7580_db1.users usr_2
							ON  usr_2.id = usr.user_id
									AND usr_2.active_account_flg = 1

						LEFT JOIN xa7580_db1.ftsy_tabelle_2020 tab 
							ON 	usr.user_id = tab.player_id
									AND tab.spieltag = (SELECT max(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = (SELECT season_id FROM xa7580_db1.parameter))
									AND tab.season_id = (SELECT season_id FROM xa7580_db1.parameter)

						LEFT JOIN xa7580_db1.ftsy_schedule sch
							ON 	sch.buli_round_name = '".$akt_spieltag."'
									and ( sch.ftsy_home_id = usr.user_id or sch.ftsy_away_id = usr.user_id )
									and sch.season_id = (SELECT season_id FROM parameter)

						LEFT JOIN xa7580_db1.ftsy_tabelle_2020 opp 
							ON ( case when sch.ftsy_home_id = tab.player_id then sch.ftsy_away_id else sch.ftsy_home_id end ) = opp.player_id
									and opp.spieltag = (SELECT max(spieltag) FROM xa7580_db1.ftsy_tabelle_2020 WHERE season_id = (SELECT season_id FROM xa7580_db1.parameter))
									and opp.season_id = (SELECT season_id FROM xa7580_db1.parameter)

						WHERE usr.username = '".$show_team."' 
					");

				$team_info_array = mysqli_fetch_array($team_info);

						$team_id_query = "SELECT id FROM xa7580_db1.users WHERE username = '".$show_team."'";

						$result = mysqli_query($con, $team_id_query);
						if ($result) {
							$row = mysqli_fetch_assoc($result);
							$team_id = $row['id']; 
						} else {
							$team_id = null; 
}
			
			?>

<div id="top_wrapper">

    <?php
    if (isset($username)) {
        $query = "SELECT id FROM xa7580_db1.users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->bind_result($team_id);
        $stmt->fetch();
        $stmt->close();
    }

    if (!empty($team_id)) {
        echo "<div class='team-image' style='display: inline-block;'>";

        $team_images = array(
            '3' => '../img/ftsy-team-logos/3.png',
            '4' => '../img/ftsy-team-logos/4.png',
            '11' => '../img/ftsy-team-logos/11.png',
            '12' => '../img/ftsy-team-logos/12.png',
            '16' => '../img/ftsy-team-logos/16.png',
            '17' => '../img/ftsy-team-logos/17.png',
            '19' => '../img/ftsy-team-logos/19.png',
            '22' => '../img/ftsy-team-logos/22.png',
            '27' => '../img/ftsy-team-logos/27.png',
            '28' => '../img/ftsy-team-logos/28.png',
            '30' => '../img/ftsy-team-logos/30.png',
        );

        $image_src = isset($team_images[$team_id]) ? $team_images[$team_id] : '';

        if (!empty($image_src)) {
            echo '<div class="round-image-div">';
            echo '<img src="' . $image_src . '" alt="Team Image">';
            echo '</div>';
        } else {
            echo "No image found for this team.";
        }

        echo "</div>";
    } else {
        echo "No team found for this user.";
    }
    ?>

    <div id="info_box_basic">
        <div id="team_name">
            <?php echo $team_info_array['team_name']; ?><br>
            <span id="team_owner_inline">Owner: <?php echo $team_info_array['manager']; ?></span>
        </div>

        <!-- Trophy Case -->
        <div id="trophy_case" class="trophy_case_right">
            <?php
            if (!$con) {
                die("Database connection failed: " . mysqli_connect_error());
            }
            if (!isset($show_team) || empty($show_team)) {
                die("Error: Username not set.");
            }

            $result_season = mysqli_query($con, "
                SELECT fm.season_name 
                FROM xa7580_db1.users u
                INNER JOIN xa7580_db1.ftsy_meister_v fm 
                    ON u.id = fm.player_id
                WHERE u.username = '" . $show_team . "'
            ");

            if (!$result_season) {
                die("Query failed: " . mysqli_error($con));
            }

            $titles_found = false;

            while ($season_data = $result_season->fetch_object()) {
                $season_name = $season_data->season_name ?? null;
                if ($season_name) {
                    $titles_found = true;
                    echo "<div class='trophy_item'>
                            <div class='trophy_icon'>
                                <span>🏅</span> <!-- Medal emoji for Meistertitel -->
                            </div>
                            <div class='trophy_title'>Meister:<br> " . htmlspecialchars($season_name) . "</div>
                        </div>";
                }
            }

            if (!$titles_found) {
                echo "<div class='trophy_item'>
                        <div class='trophy_icon'>
                            <span class='greyed-out'>🏅</span> <!-- Greyed-out Medal emoji -->
                        </div>
                        <div class='trophy_title'>Keine<br> Meistertitel</div>
                    </div>";
            }
            ?>
        </div>

        <div id="trophy_case" class="trophy_case_right">
            <?php
                $result_season = mysqli_query($con, "
                    SELECT fm.season_name 
                    FROM xa7580_db1.users u
                    INNER JOIN xa7580_db1.ftsy_pokalsieger_v fm 
                        ON u.id = fm.winner_user_id
                    WHERE u.username = '".$show_team."'
                ");

                if (!$result_season) {
                    die("Query failed: " . mysqli_error($con));
                }

                $titles_found = false;

                while ($season_data = $result_season->fetch_object()) {
                    $season_name = $season_data->season_name ?? null;
                    if ($season_name) {
                        $titles_found = true;
                        echo "<div class='trophy_item'>
                                <div class='trophy_icon'>
                                    <span>🏆</span> <!-- Cup emoji for Pokalsieger -->
                                </div>
                                <div class='trophy_title'>Pokalsieger: " . htmlspecialchars($season_name) . "</div>
                            </div>";
                    }
                }

                if (!$titles_found) {
                    echo "<div class='trophy_item'>
                            <div class='trophy_icon'>
                                <span class='greyed-out'>🏆</span> <!-- Greyed-out Cup emoji -->
                            </div>
                            <div class='trophy_title'>Keine Pokale</div>
                        </div>";
                }
            ?>
        </div>

    </div>

    <div id="info_box_detail">
        <div id="team_stat_box">
            <div class="team_stat">
                <div class="team_stat_head">
                    Rang
                </div>
                <div class="team_stat_content">
                    #<?php echo $team_info_array['rang']; ?>
                </div>
            </div>
            <div class="team_stat">
                <div class="team_stat_head">
                    Bilanz
                </div>
                <div class="team_stat_content">
                    <?php echo $team_info_array['bilanz']; ?>
                </div>
            </div>
            <div class="team_stat hide_mobile">
                <div class="team_stat_head">
                    Punkte
                </div>
                <div class="team_stat_content">
                    <?php echo $team_info_array['punkte']; ?>
                </div>
            </div>
            <div class="team_stat hide_mobile">
                <div class="team_stat_head">
                    Score
                </div>
                <div class="team_stat_content">
                    <small>&Oslash;</small><?php echo $team_info_array['avg_for']; ?>
                </div>
            </div>
            <div class="team_stat">
                <div class="team_stat_head">
                    Trend
                </div>
                <div class="team_stat_content">
                    <?php echo $team_info_array['serie']; ?>
                </div>
            </div>
            <div class="team_stat">
                <div class="team_stat_head">
                    Waiver
                </div>
                <div class="team_stat_content">
                    #<?php echo $team_info_array['waiver_position']; ?>
                </div>
            </div>
        </div>

        <div id="versus">
            VS
        </div>

        <div id="opponent_box">
            <div class="opponent_team">
                #<?php echo $team_info_array['gegner_rang'] . ' ' . utf8_encode($team_info_array['gegner_team']) ?>
            </div>
            <div class="opponent_stats">
                Bilanz: <?php echo $team_info_array['gegner_bilanz'] . ' | Trend: ' . $team_info_array['gegner_serie'] . ' | Avg. Score:  <small>&Oslash;</small>' . $team_info_array['gegner_avg_for'] ?>
            </div>
            <div class="">
                <?php
                    $link_to_match = 'view_match.php?ID=' . strval($team_info_array['match_id']);
                    echo "<a href='" . $link_to_match . "'>>> Gehe zu Game Center</a>";
                ?>
            </div>
        </div>
    </div>

</div>

	
			
			
			<div id="team_wrapper">
				<div id="view_nav">
					<ul>
		  				<li>
		  					<a id="button_show_grafisch" onclick="showGraphic(); changeColorGraphic()">FORMATION</a>
		  				</li>
		  				<li>
		  					<a id="button_show_tabelle" onclick="showTable(); changeColorTable()">DETAIL</a>
		  				</li>
	  				</ul>
				</div>
				
				<div id="time_nav">

					<!-- akt_spieltag / abg_spieltag -->
					<div class='nav_spieltag_head'>
						<bold>Spieltag:</bold>
					</div>
						<?php
							$k = 1;
							while ($k < $abg_spieltag ){
								echo "<div class='nav_spieltag hide_mobile'>". $k ."</div>";
								$k = $k + 1;
							} 

							if ($abg_spieltag > 0){
								echo "<div class='nav_spieltag '>". $abg_spieltag ."</div>";
							}
						?>
					<div class='nav_spieltag' style='font-weight: bold; color: #4caf50;'>
						Aktueller Spieltag
					</div>					
				</div>				
				<div id="aufstellung_wrapper">
				</div>
			</div>
		</div>
	</div> 
</body>
</html>
