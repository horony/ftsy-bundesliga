<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get needed meta data

$clicked_round = $_GET['round'];
$user = mb_convert_encoding($_SESSION['username'],'UTF-8');
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

// Display the differents round

if ($clicked_round == 'Quali-Runde'){

	/***************************/
	/* 1.) QUALIFICATION ROUND */
	/***************************/

	/* META-INFOS */

	$round_meta_info = mysqli_query($con, "

		SELECT 		max(case when sch.cup_leg = 1 then sch.buli_round_name else null end) as hin_spieltag
						, max(case when sch.cup_leg = 2 then sch.buli_round_name else null end) as rueck_spieltag
						, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.start_dt else null end))) as hin_start_dt 
						, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.end_dt else null end))) as hin_end_dt
						, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.start_dt else null end))) as rueck_start_dt 
						, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.end_dt else null end))) as rueck_end_dt
		FROM 		xa7580_db1.ftsy_schedule sch
		LEFT JOIN 	xa7580_db1.sm_rounds rnd
			ON rnd.name = sch.buli_round_name
			   and rnd.season_id = '".$akt_season_id."'
		WHERE 	sch.match_type = 'cup'
				and sch.cup_round = 'playoff'
				and sch.season_id = '".$akt_season_id."'
				and sch.ftsy_league_id = 1
		");

	echo "<div id='cup_round_meta_info'>";
		while($row = mysqli_fetch_array($round_meta_info)) {
			echo "&#x1F4C5; Hinspiel: Spieltag " . $row['hin_spieltag'] . " (" . $row['hin_start_dt'] . "-" . $row['hin_end_dt'] . ") | Rückspiel: Spieltag " . $row['rueck_spieltag'] . " (" . $row['rueck_start_dt'] . "-" . $row['rueck_end_dt'] . ")";
		}
	echo "</div>";

	if ($akt_spieltag <= 9) { 
		$quali_spieltag = $akt_spieltag-1;
	} else { 
		$quali_spieltag = 9;
	}

	$qualied_by_tab = mysqli_query($con, "
		SELECT 	tab.team_name
						, tab.rang
						, case when tab.rang <= 6 then 'Viertelfinale' else 'Quali-Runde' end as cup_status
		FROM 		xa7580_db1.ftsy_tabelle_2020 tab
		WHERE 	tab.season_id = '".$akt_season_id."'
						and tab.league_id = 1
						and tab.spieltag = '".$quali_spieltag."'
		ORDER BY tab.rang ASC
		LIMIT 10
		");

	$cup_data = mysqli_query($con, "
		SELECT	hin.cup_round
						, hin.cup_leg as hin_cup_leg
		        , hin.buli_round_name as hin_buli_round_name
		        , hin.ftsy_match_id as hin_ftsy_match_id
						, hin.ftsy_home_id as home_id 
						, coalesce(hin.ftsy_home_name, 'Fantasy-Team') as home_name
		        , hin.ftsy_away_id as away_id 
		        , coalesce(hin.ftsy_away_name, 'Fantasy-Team') as away_name 
		        , hin.ftsy_home_score as hin_home_score
		        , hin.ftsy_away_score as hin_away_score
		        , rue.cup_leg as rue_cup_leg
		        , rue.buli_round_name as rue_buli_round_name
		        , rue.ftsy_match_id as rue_ftsy_match_id
		        , case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end as rue_home_score
		        , case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end as rue_away_score
		        , round(hin.ftsy_home_score + case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end,1) as full_home_score
		        , round(hin.ftsy_away_score + case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end,1) as full_away_score
		        
		FROM xa7580_db1.ftsy_schedule hin

		LEFT JOIN xa7580_db1.ftsy_schedule rue
			ON 	rue.ftsy_home_id in (hin.ftsy_home_id, hin.ftsy_away_id)
		    	and rue.match_type = 'cup'
			  	and rue.cup_round = 'playoff'
		     	and rue.cup_leg = 2
		      and rue.ftsy_league_id = 1
		      and rue.season_id = (select season_id from parameter)

		WHERE hin.match_type = 'cup'
			  	and hin.cup_round = 'playoff'
		      and hin.cup_leg = 1
		      and hin.ftsy_league_id = 1
		      and hin.season_id = (select season_id from parameter)

		ORDER BY hin.ftsy_match_id asc

		");

	echo "<div id='cup_content'>";
		echo "<div id='cup_playoff_table'>";
			echo "<span class='cup_table_headline'>Tabellenstand Spieltag " . $quali_spieltag . "</span>*</br>";

			while($row = mysqli_fetch_array($qualied_by_tab)) {
				echo "<div class='cup_table_tr'>";
					echo "<div class='cup_table_td'>#" . $row['rang'] . "</div><div class='cup_table_td'>" . mb_convert_encoding($row['team_name'], 'UTF-8') . "</div><div class='cup_table_td'> →" . $row['cup_status'] . "</div>";
				echo "</div>";
			}

			echo "<span class='cup_table_footnote'>* Der Tabellenstand nach Spieltag 9 entscheidet welche Teams direkt ins Viertelfinale ziehen und welche in die Pokal-Quali müssen.</span>";
		echo "</div>";

		echo "<div id='cup_matches_wrapper'>";

			$match_num = 0;

			while($row = mysqli_fetch_array($cup_data)) {
				$match_num = $match_num + 1;

				echo "<div class='matchup_bezeichner'>";
					echo "Quali-Match #" . $match_num;
				echo "</div>";

				echo "<div class='matchup_card'>";
					echo "<div class='team_home'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['home_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";
					
						echo "<div class = 'team_score_wrapper'>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . round($row['hin_home_score'],1) . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . round($row['rue_home_score'],1) . " &#187;</a>";
								echo "</div>";
							echo "</div>";
					
							echo "<div class = 'full_score'>";
								echo $row['full_home_score'];
							echo "</div>";
					
						echo "</div>";
					echo "</div>";

					echo "<div class='middle_element'>";
						echo "VS.";
					echo "</div>";

					echo "<div class='team_away'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['away_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";

						echo "<div class = 'team_score_wrapper'>";

							echo "<div class = 'full_score'>";
								echo $row['full_away_score'];
							echo "</div>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . round($row['hin_away_score'],1) . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . round($row['rue_away_score'],1) . " &#187;</a>";								echo "</div>";
							echo "</div>";
					

					
						echo "</div>";
					echo "</div>";

				echo "</div></br>";
			}

		echo "<div class='tiebraker_textbox'>";
			echo "Tiebraker: (1.) Summe Punkte Hin- und Rückspiel (2.) Anzahl Spieler in Hin- und Rückspiel aufgestellt, welche gespielt haben (3.) Summe Tore Hin- und Rückspiel (4.) Schlechtere aktuelle Tabellenposition";
		echo "</div>";

		echo "</div>";


	echo "</div>";

} elseif ($clicked_round == 'Viertelfinale'){

	/****************/
	/* 2.) QUARTERS */
	/****************/

	$round_meta_info = mysqli_query($con, "

		SELECT 		max(case when sch.cup_leg = 1 then sch.buli_round_name else null end) as hin_spieltag
							, max(case when sch.cup_leg = 2 then sch.buli_round_name else null end) as rueck_spieltag
							, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.start_dt else null end))) as hin_start_dt 
							, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.end_dt else null end))) as hin_end_dt
							, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.start_dt else null end))) as rueck_start_dt 
							, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.end_dt else null end))) as rueck_end_dt
		FROM 		xa7580_db1.ftsy_schedule sch
		LEFT JOIN 	xa7580_db1.sm_rounds rnd
			ON rnd.name = sch.buli_round_name
			   and rnd.season_id = '".$akt_season_id."'
		WHERE 	sch.match_type = 'cup'
				and sch.cup_round = 'quarter'
				and sch.season_id = '".$akt_season_id."'
				and sch.ftsy_league_id = 1
	");

	echo "<div id='cup_round_meta_info'>";
		while($row = mysqli_fetch_array($round_meta_info)) {
			echo "&#x1F4C5; Hinspiel: Spieltag " . $row['hin_spieltag'] . " (" . $row['hin_start_dt'] . "-" . $row['hin_end_dt'] . ") | Rückspiel: Spieltag " . $row['rueck_spieltag'] . " (" . $row['rueck_start_dt'] . "-" . $row['rueck_end_dt'] . ")";
		}
	echo "</div>";

	echo "<div class='round_info'>Qualifikation: Top 6 der Tabelle von Spieltag 9 & zwei Sieger aus der Quali-Runde | Auslosung: <a style='color: black;' href='https://youtu.be/7DQgLbLL8DI'>YouTube-Video</a></div>";

	$cup_data = mysqli_query($con, "
		SELECT	hin.cup_round
						, hin.cup_leg as hin_cup_leg
		        , hin.buli_round_name as hin_buli_round_name
		        , hin.ftsy_match_id as hin_ftsy_match_id
						, hin.ftsy_home_id as home_id 
						, coalesce(hin.ftsy_home_name, 'Fantasy-Team') as home_name
		        , hin.ftsy_away_id as away_id 
		        , coalesce(hin.ftsy_away_name, 'Fantasy-Team') as away_name 
		        , hin.ftsy_home_score as hin_home_score
		        , hin.ftsy_away_score as hin_away_score
		        , rue.cup_leg as rue_cup_leg
		        , rue.buli_round_name as rue_buli_round_name
		        , rue.ftsy_match_id as rue_ftsy_match_id
		        , case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end as rue_home_score
		        , case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end as rue_away_score
		        , round(hin.ftsy_home_score + case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end,1) as full_home_score
		        , round(hin.ftsy_away_score + case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end,1) as full_away_score
		        
		FROM xa7580_db1.ftsy_schedule hin

		LEFT JOIN xa7580_db1.ftsy_schedule rue
			ON 	rue.ftsy_home_id in (hin.ftsy_home_id, hin.ftsy_away_id)
		    	and rue.match_type = 'cup'
			  	and rue.cup_round = 'quarter'
		      and rue.cup_leg = 2
		      and rue.ftsy_league_id = 1
		      and rue.season_id = (select season_id from parameter)

		WHERE hin.match_type = 'cup'
			  	and hin.cup_round = 'quarter'
		      and hin.cup_leg = 1
		      and hin.ftsy_league_id = 1
		      and hin.season_id = (select season_id from parameter)

		ORDER BY hin.ftsy_match_id asc

		");

	echo "<div id='cup_content'>";		

		echo "<div id='cup_matches_wrapper'>";

			$match_num = 0;

			while($row = mysqli_fetch_array($cup_data)) {
				$match_num = $match_num + 1;

				echo "<div class='matchup_bezeichner'>";
					echo "Viertelfinale #" . $match_num;
				echo "</div>";

				echo "<div class='matchup_card'>";
					echo "<div class='team_home'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['home_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";
					
						echo "<div class = 'team_score_wrapper'>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_home_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . $row['rue_home_score'] . " &#187;</a>";
								echo "</div>";
							echo "</div>";
					
							echo "<div class = 'full_score'>";
								echo $row['full_home_score'];
							echo "</div>";
					
						echo "</div>";
					echo "</div>";

					echo "<div class='middle_element'>";
						echo "VS.";
					echo "</div>";

					echo "<div class='team_away'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['away_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";

						echo "<div class = 'team_score_wrapper'>";

							echo "<div class = 'full_score'>";
								echo $row['full_away_score'];
							echo "</div>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_away_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . $row['rue_away_score'] . " &#187;</a>";								echo "</div>";
							echo "</div>";
					

					
						echo "</div>";
					echo "</div>";

				echo "</div></br>";
			}

		echo "<div class='tiebraker_textbox'>";
			echo "Tiebreaker: (1.) Summe Punkte Hin- und Rückspiel (2.) Anzahl Spieler in Hin- und Rückspiel aufgestellt, welche gespielt haben (3.) Summe Tore Hin- und Rückspiel (4.) Schlechtere aktuelle Tabellenposition";
		echo "</div>";

		echo "</div>";

	echo "</div>";

} elseif ($clicked_round == 'Halbfinale'){

	/**************/
	/* 3.) SEMIS  */
	/**************/

	$round_meta_info = mysqli_query($con, "

		SELECT 		max(case when sch.cup_leg = 1 then sch.buli_round_name else null end) as hin_spieltag
							, max(case when sch.cup_leg = 2 then sch.buli_round_name else null end) as rueck_spieltag
							, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.start_dt else null end))) as hin_start_dt 
							, concat(concat(extract(day from max(case when sch.cup_leg = 1 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 1 then rnd.end_dt else null end))) as hin_end_dt
							, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.start_dt else null end))) as rueck_start_dt 
							, concat(concat(extract(day from max(case when sch.cup_leg = 2 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 2 then rnd.end_dt else null end))) as rueck_end_dt
		FROM 		xa7580_db1.ftsy_schedule sch
		LEFT JOIN 	xa7580_db1.sm_rounds rnd
			ON rnd.name = sch.buli_round_name
			   and rnd.season_id = '".$akt_season_id."'
		WHERE 	sch.match_type = 'cup'
				and sch.cup_round = 'semi'
				and sch.season_id = '".$akt_season_id."'
				and sch.ftsy_league_id = 1
		");

	echo "<div id='cup_round_meta_info'>";
		while($row = mysqli_fetch_array($round_meta_info)) {
			echo "&#x1F4C5; Hinspiel: Spieltag " . $row['hin_spieltag'] . " (" . $row['hin_start_dt'] . "-" . $row['hin_end_dt'] . ") | Rückspiel: Spieltag " . $row['rueck_spieltag'] . " (" . $row['rueck_start_dt'] . "-" . $row['rueck_end_dt'] . ")";
		}
	echo "</div>";

	echo "<div class='round_info'>Qualifikation: Vier Sieger des Pokal-Viertelfinals.</div>";

	$cup_data = mysqli_query($con, "
		SELECT	hin.cup_round
						, hin.cup_leg as hin_cup_leg
		        , hin.buli_round_name as hin_buli_round_name
		        , hin.ftsy_match_id as hin_ftsy_match_id
						, hin.ftsy_home_id as home_id 
						, coalesce(hin.ftsy_home_name, 'Fantasy-Team') as home_name
		        , hin.ftsy_away_id as away_id 
		        , coalesce(hin.ftsy_away_name, 'Fantasy-Team') as away_name 
		        , hin.ftsy_home_score as hin_home_score
		        , hin.ftsy_away_score as hin_away_score
		        , rue.cup_leg as rue_cup_leg
		        , rue.buli_round_name as rue_buli_round_name
		        , rue.ftsy_match_id as rue_ftsy_match_id
		        , case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end as rue_home_score
		        , case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end as rue_away_score
		        , round(hin.ftsy_home_score + case when hin.ftsy_home_id = rue.ftsy_home_id then rue.ftsy_home_score else rue.ftsy_away_score end,1) as full_home_score
		        , round(hin.ftsy_away_score + case when hin.ftsy_away_id = rue.ftsy_away_id then rue.ftsy_away_score else rue.ftsy_home_score end,1) as full_away_score
		        
		FROM xa7580_db1.ftsy_schedule hin

		LEFT JOIN xa7580_db1.ftsy_schedule rue
			ON 	rue.ftsy_home_id in (hin.ftsy_home_id, hin.ftsy_away_id)
		    	and rue.match_type = 'cup'
			  	and rue.cup_round = 'semi'
		      	and rue.cup_leg = 2
		        and rue.ftsy_league_id = 1
		        and rue.season_id = (select season_id from parameter)

		WHERE hin.match_type = 'cup'
			  and hin.cup_round = 'semi'
		      and hin.cup_leg = 1
		      and hin.ftsy_league_id = 1
		      and hin.season_id = (select season_id from parameter)

		ORDER BY hin.ftsy_match_id asc

	");

	echo "<div id='cup_content'>";		

		echo "<div id='cup_matches_wrapper'>";

			$match_num = 0;

			while($row = mysqli_fetch_array($cup_data)) {
				$match_num = $match_num + 1;

				echo "<div class='matchup_bezeichner'>";
					echo "Halbfinale #" . $match_num;
				echo "</div>";

				echo "<div class='matchup_card'>";
					echo "<div class='team_home'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['home_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";
					
						echo "<div class = 'team_score_wrapper'>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_home_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . $row['rue_home_score'] . " &#187;</a>";
								echo "</div>";
							echo "</div>";
					
							echo "<div class = 'full_score'>";
								echo $row['full_home_score'];
							echo "</div>";
					
						echo "</div>";
					echo "</div>";

					echo "<div class='middle_element'>";
						echo "VS.";
					echo "</div>";

					echo "<div class='team_away'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['away_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";

						echo "<div class = 'team_score_wrapper'>";

							echo "<div class = 'full_score'>";
								echo $row['full_away_score'];
							echo "</div>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_away_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									$link = "view_match.php?ID=" . strval($row['rue_ftsy_match_id']);
									echo "<a href='" . $link . "'>Rückspiel: " . $row['rue_away_score'] . " &#187;</a>";								echo "</div>";
							echo "</div>";
					

					
						echo "</div>";
					echo "</div>";

				echo "</div></br>";
			}

		echo "<div class='tiebraker_textbox'>";
			echo "Tiebreaker: (1.) Summe Punkte Hin- und Rückspiel (2.) Anzahl Spieler in Hin- und Rückspiel aufgestellt, welche gespielt haben (3.) Summe Tore Hin- und Rückspiel (4.) Schlechtere aktuelle Tabellenposition";
		echo "</div>";

		echo "</div>";

	echo "</div>";

} elseif ($clicked_round == 'Finale'){

	/*********/
	/* FINAL */
	/*********/

	$round_meta_info = mysqli_query($con, "

		SELECT 		max(case when sch.cup_leg = 1 then sch.buli_round_name else null end) as hin_spieltag
						, max(case when sch.cup_leg = 2 then sch.buli_round_name else null end) as rueck_spieltag
						, concat(concat(extract(day from max(case when sch.cup_leg = 0 then rnd.start_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 0 then rnd.start_dt else null end))) as hin_start_dt 
						, concat(concat(extract(day from max(case when sch.cup_leg = 0 then rnd.end_dt else null end)),'.'),extract(month from max(case when sch.cup_leg = 0 then rnd.end_dt else null end))) as hin_end_dt

		FROM 		xa7580_db1.ftsy_schedule sch
		LEFT JOIN 	xa7580_db1.sm_rounds rnd
			ON rnd.name = sch.buli_round_name
			   and rnd.season_id = '".$akt_season_id."'
		WHERE 	sch.match_type = 'cup'
				and sch.cup_round = 'final'
				and sch.season_id = '".$akt_season_id."'
				and sch.ftsy_league_id = 1
		");

	echo "<div id='cup_round_meta_info'>";
		while($row = mysqli_fetch_array($round_meta_info)) {
			echo "&#x1F4C5; Finale: Spieltag " . $row['hin_spieltag'] . " (" . $row['hin_start_dt'] . "-" . $row['hin_end_dt'] . ")";
		}
	echo "</div>";

		echo "<div class='round_info'>Qualifikation: Zwei Sieger des Pokal-Halbfinals.</div>";

	$cup_data = mysqli_query($con, "
		SELECT	hin.cup_round
						, hin.cup_leg as hin_cup_leg
		        , hin.buli_round_name as hin_buli_round_name
		        , hin.ftsy_match_id as hin_ftsy_match_id
						, hin.ftsy_home_id as home_id 
						, coalesce(hin.ftsy_home_name, 'Fantasy-Team') as home_name
		        , hin.ftsy_away_id as away_id 
		        , coalesce(hin.ftsy_away_name, 'Fantasy-Team') as away_name 
		        , hin.ftsy_home_score as hin_home_score
		        , hin.ftsy_away_score as hin_away_score
		        , hin.ftsy_home_id as rue_home_score
		        , hin.ftsy_away_score as rue_away_score
		        , hin.ftsy_home_score as full_home_score
		        , hin.ftsy_away_score as full_away_score
		        
		FROM xa7580_db1.ftsy_schedule hin

		WHERE hin.match_type = 'cup'
			  and hin.cup_round = 'final'
		      and hin.ftsy_league_id = 1
		      and hin.season_id = (select season_id from parameter)

		ORDER BY hin.ftsy_match_id asc

		");

	echo "<div id='cup_content'>";		

		echo "<div id='cup_matches_wrapper'>";

			$match_num = 0;

			while($row = mysqli_fetch_array($cup_data)) {
				$match_num = $match_num + 1;

				echo "<div class='matchup_bezeichner'>";
					echo "Fantasy-Bundesliga Pokalfinale Saison 2020/2021";
				echo "</div>";

				echo "<div class='matchup_card'>";
					echo "<div class='team_home'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['home_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";
					
						echo "<div class = 'team_score_wrapper'>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_home_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									echo "Kein Rückspiel";
								echo "</div>";
							echo "</div>";
					
							echo "<div class = 'full_score'>";
								echo $row['full_home_score'];
							echo "</div>";
					
						echo "</div>";
					echo "</div>";

					echo "<div class='middle_element'>";
						echo "VS.";
					echo "</div>";

					echo "<div class='team_away'>";

						echo "<div class = 'teamname'>";
							echo mb_convert_encoding($row['away_name'], 'UTF-8');
							echo "<hr>";
						echo "</div>";

						echo "<div class = 'team_score_wrapper'>";

							echo "<div class = 'full_score'>";
								echo $row['full_away_score'];
							echo "</div>";
					
							echo "<div class = 'leg_scores'>";
								echo "<div class = 'hin_score'>";
									$link = "view_match.php?ID=" . strval($row['hin_ftsy_match_id']);
									echo "<a href='" . $link . "'>Hinspiel: " . $row['hin_away_score'] . " &#187;</a>";
								echo "</div>";
								echo "<div class = 'rue_score'>";
									echo "Kein Rückspiel";
								echo "</div>";
							echo "</div>";
					

					
						echo "</div>";
					echo "</div>";

				echo "</div></br>";
			}

		echo "<div class='tiebraker_textbox'>";
			echo "Tiebreaker: (1.) Summe Punkte Hin- und Rückspiel (2.) Anzahl Spieler in Hin- und Rückspiel aufgestellt, welche gespielt haben (3.) Summe Tore Hin- und Rückspiel (4.) Schlechtere aktuelle Tabellenposition";
		echo "</div>";

		echo "</div>";

	echo "</div>";

}
?> 