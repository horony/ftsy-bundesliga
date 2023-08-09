<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get player id for .js-call
$id = $_GET["player_id"];
	
// SQL
$player = mysqli_query($con, "	
	SELECT  base.image_path
					, base.display_name
					, base.current_team_name AS teamname
					, base.position_long
					, base.position_detail_name
					, base.height
					, base.weight
					, base.birthplace
					, base.birthcountry
					, base.captain
					, base.number
					, base.captain
					, FLOOR(DATEDIFF(CURRENT_DATE, base.birth_dt)/365) AS age
					, CASE 	WHEN base.injured = 1 AND base.injury_reason IS NOT NULL THEN CONCAT('Verletzt: ', base.injury_reason)
                 	WHEN base.injured = 1 AND base.injury_reason IS NULL THEN 'Verletzt'
                  WHEN base.injured = 0 AND base.is_suspended = 1 THEN 'Gesperrt'
					  			ELSE 'Fit'
                	END AS fitness  
					, base.injured 
					, base.injury_reason
					, base.is_suspended
					, base.number
					, team.logo_path AS team_logo

	FROM sm_playerbase base

	LEFT JOIN sm_teams team 
		ON base.current_team_id = team.id

	WHERE base.id = '".$id."'
");

$player = mysqli_fetch_array($player);

// Start Player profile
echo "<div id='spielerprofil_wrapper' class='spielerprofil'>";
	
	echo "<div id ='spielerprofil_basisdaten'>";

		// Player image
		echo "<div id='spielerprofil_image'>";
			echo "<div style='background-image: url('".$player['team_logo']."');' height='100px'><img id='player_image' src='".$player['image_path']."' width='auto' height='auto'></div>";
		echo "</div>";

		// Player meta-data
		echo "<div id='spielerprofil_metadaten'>";
			echo "<div class='profil_player_name'>";
				echo mb_convert_encoding($player['display_name'], 'UTF-8');
			echo "</div>";

			echo "<div class='metadaten_table'>";
				echo "<div class='left_col'>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Verein</div><div class='meta_value'>".mb_convert_encoding($player['teamname'], 'UTF-8')."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Position</div><div class='meta_value'>".$player['position_long']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Nummer</div><div class='meta_value'>".$player['number']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Fitness</div><div class='meta_value'>".mb_convert_encoding($player['fitness'],'UTF-8')."</div></div>";
				echo "</div>";

				echo "<div class=right_col>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Nationalität</div><div class='meta_value'>".mb_convert_encoding($player['birthcountry'], 'UTF-8')."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Alter</div><div class='meta_value'>".$player['age']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Größe</div><div class='meta_value'>".$player['height']." cm</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Gewicht</div><div class='meta_value'>".$player['weight']." kg</div></div>";
				echo "</div>";
			echo "</div>";
		echo "</div>";
	
	echo "</div>";

echo "</div>";
// End Player Profile

// Seasons to iterate
$season_ids_for_stats = array(21795, 19744, 18444, 17361, 16264);
$season_names_for_stats = array('2023/2024','2022/2023','2021/2022','2020/2021','2019/2020');
$season_counter = 0;
$season_counter_max = count($season_ids_for_stats)-1;

// Player performance aggregated by year

echo "<div id='data_2019'>";

	echo "<div class='sub_headline'>";
		echo "Fantasy-Performance seit 2019/2020";
	echo "</div>";

	$data_2019 = mysqli_query($con, "	
		SELECT 	ses.season_name			        
		        , SUM(COALESCE(scr.ftsy_score,0)) AS ftsy_score_sum
		        , ROUND(AVG(CASE WHEN scr.appearance_stat = 1 THEN scr.ftsy_score ELSE NULL END),1) AS ftsy_score_avg
		        , SUM(CASE WHEN scr.appearance_stat = 1 THEN 1 else 0 end) AS appearances
		        , SUM(scr.goals_total_stat) AS goals
		   			, SUM(scr.assists_stat) AS assists
		        , SUM(scr.shots_total_stat) AS shots
		        , SUM(scr.key_passes_stat) AS passes_key
		        , SUM(scr.passes_complete_stat) AS passes
				    , ROUND(SUM(scr.passes_complete_stat)/SUM(scr.passes_total_stat)*100,0) AS passes_perc
		        , SUM(scr.crosses_complete_stat) AS crosses
				  	, ROUND(SUM(scr.crosses_complete_stat)/SUM(scr.crosses_total_stat)*100,0) AS crosses_perc
						, SUM(scr.dribbles_success_stat) AS dribbles
				    , ROUND(SUM(scr.dribbles_success_stat)/SUM(scr.dribble_attempts_stat)*100,0) AS dribbles_perc
		        , SUM(scr.duels_won_stat) AS duels
			      , ROUND(SUM(scr.duels_won_stat)/SUM(scr.duels_won_stat+scr.duels_lost_stat)*100,0) AS duels_perc
		        , SUM(scr.blocks_stat) AS blocks
		        , SUM(scr.clearances_stat) AS clearances
		        , SUM(scr.interceptions_stat) AS interceptions
		        , SUM(scr.tackles_stat) AS tackles
		        , SUM(scr.saves_stat) AS saves
		        , SUM(scr.pen_saved_stat) AS pen_saved
		
		FROM ftsy_scoring_all_v scr

		INNER JOIN sm_fixtures fix
			ON fix.fixture_id = scr.fixture_id		

		INNER JOIN sm_seasons ses
			ON ses.season_id = fix.season_id										    

		WHERE 	scr.player_id = '".$id."'

		GROUP BY ses.season_name

		ORDER BY ses.season_name DESC 
	");

	echo "<table id='table_2019'>";

		echo "<tr class='first_th'>";
			echo "<th class='' rowspan='2' colspan='1'>Saison</th>";
			echo "<th class='' rowspan='1' colspan='2'>Fantasy-Punkte</th>";
			echo "<th class='' rowspan='2' colspan='1'>Einsätze</th>";
			echo "<th class='' rowspan='1' colspan='2'>Torbeteiligungen</th>";
			echo "<th class='' rowspan='1' colspan='5'>Offensiv-Aktionen</th>";
			echo "<th class='' rowspan='1' colspan='5'>Defensiv-Aktionen</th>";
			echo "<th class='' rowspan='1' colspan='2'>Torwart-Aktionen</th>";
		echo "</tr>";
		
		echo "<tr class='second_th'>";

			echo "<th class=''>Summe</th>";
			echo "<th class=''>Schnitt</th>";

			echo "<th class=''>Tore</th>";
			echo "<th class=''>Vorlagen</th>";

			echo "<th class=''>Schüsse</th>";
			echo "<th class=''>Key-Pässe</th>";
			echo "<th class=''>Pässe</th>";
			echo "<th class=''>Flanken</th>";
			echo "<th class=''>Dribblings</th>";

			echo "<th class=''>Duelle</th>";
			echo "<th class=''>Tackles</th>";
			echo "<th class=''>Blocks</th>";
			echo "<th class=''>Klärungen</th>";
			echo "<th class=''>Abgefangen</th>";

			echo "<th class=''>Gehalten</th>";
			echo "<th class=''>Elfmeter</th>";
		echo "</tr>";

	while($row = mysqli_fetch_array($data_2019)) {
		echo "<tr>";
			echo "<td class='nobreak'>".$row['season_name']."</td>";

			echo "<td class='highlight_td'>".$row['ftsy_score_sum']."</td>";
			echo "<td class='highlight_td'>".$row['ftsy_score_avg']."</td>";

			echo "<td>".$row['appearances']."</td>";

			echo "<td>".$row['goals']."</td>";
			echo "<td>".$row['assists']."</td>";

			echo "<td>".$row['shots']."</td>";
			echo "<td>".$row['passes_key']."</td>";
			echo "<td>".$row['passes']." (". $row['passes_perc'] ."%)</td>";
			echo "<td>".$row['crosses']." (". $row['crosses_perc'] ."%)</td>";
			echo "<td>".$row['dribbles']." (". $row['dribbles_perc'] ."%)</td>";

			echo "<td>".$row['duels']." (". $row['duels_perc'] ."%)</td>";
			echo "<td>".$row['tackles']."</td>";
			echo "<td>".$row['blocks']."</td>";
			echo "<td>".$row['clearances']."</td>";
			echo "<td>".$row['interceptions']."</td>";
			
			echo "<td>".$row['saves']."</td>";
			echo "<td>".$row['pen_saved']."</td>";
		echo "</tr>";
	}
	echo "</table>";
echo "</div>";

// Player performance by match for each year (Bundesliga only)

for ($season_counter = 0; $season_counter <= $season_counter_max; $season_counter++) {

	echo "<div id='data_2019'>";

		echo "<div class='sub_headline'>";
			echo "Statistiken 1. Bundesliga " . $season_names_for_stats[$season_counter];
		echo "</div>";
		
		$data_2019 = mysqli_query($con, "	
			SELECT 	rds.id
							, rds.name
			        , CONCAT(CONCAT(fix.localteam_name_code, ' vs. '), fix.visitorteam_name_code) AS matchup
			        , scr.own_team_code
			        , CONCAT(CONCAT(fix.localteam_score, ':'), fix.visitorteam_score) AS ft_score
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.ftsy_score ELSE NULL END AS ftsy_score
			        , CASE WHEN scr.appearance_stat = 1 THEN CONCAT(CONVERT(scr.minutes_played_stat,CHAR), ' Min.') ELSE NULL END AS minutes_played
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.goals_total_stat ELSE NULL END AS goals
			   			, CASE WHEN scr.appearance_stat = 1 THEN scr.assists_stat ELSE NULL END AS assists
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.shots_total_stat ELSE NULL END AS shots
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.key_passes_stat ELSE NULL END AS passes_key
			        , CASE WHEN scr.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(scr.passes_complete_stat, ' ('), scr.passes_total_stat),')') ELSE NULL END AS passes
			        , CASE WHEN scr.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(scr.crosses_complete_stat, ' ('), scr.crosses_total_stat),')') ELSE NULL END AS crosses_stat
							, CASE WHEN scr.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(scr.dribbles_success_stat, ' ('), scr.dribble_attempts_stat),')') ELSE NULL END AS dribbles		        
			        , CASE WHEN scr.appearance_stat = 1 THEN CONCAT(CONCAT(CONCAT(scr.duels_won_stat, ' ('), scr.duels_total_stat),')') ELSE NULL END AS duels
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.blocks_stat ELSE NULL END AS blocks
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.clearances_stat ELSE NULL END AS clearances
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.interceptions_stat ELSE NULL END AS interceptions
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.tackles_stat ELSE NULL END AS tackles
			        , CASE WHEN scr.appearance_stat = 1 THEN scr.saves_stat ELSE NULL END AS saves
					    , CASE WHEN scr.appearance_stat = 1 THEN scr.pen_saved_stat ELSE NULL END AS pen_saved

					FROM `sm_rounds` rds

					LEFT JOIN ftsy_scoring_all_v scr
						ON 	scr.round_name = rds.name
								AND scr.fixture_id IN (SELECT DISTINCT fixture_id FROM sm_fixtures WHERE season_id = '". $season_ids_for_stats[$season_counter]."') 
					    
					LEFT JOIN sm_fixtures_basic_v fix
						ON fix.fixture_id = scr.fixture_id

					WHERE 	rds.season_id = '". $season_ids_for_stats[$season_counter]."'
									AND scr.player_id = '".$id."'
					        
					ORDER BY `rds`.`name` ASC

		");

		echo "<table id='table_2019'>";

			echo "<tr class='first_th'>";
				echo "<th class='' rowspan='2' colspan='3'>Spieltag</th>";
				echo "<th class='' rowspan='2' colspan='1'>Fantasy-Punkte</th>";
				echo "<th class='' rowspan='2' colspan='1'>Einsatz</th>";
				echo "<th class='' rowspan='1' colspan='2'>Torbeteiligungen</th>";
				echo "<th class='' rowspan='1' colspan='5'>Offensiv-Aktionen</th>";
				echo "<th class='' rowspan='1' colspan='5'>Defensiv-Aktionen</th>";
				echo "<th class='' rowspan='1' colspan='2'>Torwart-Aktionen</th>";
			echo "</tr>";
				
			echo "<tr class='second_th'>";
				echo "<th class=''>Tore</th>";
				echo "<th class=''>Vorlagen</th>";

				echo "<th class=''>Schüsse</th>";
				echo "<th class=''>Key-Pässe</th>";
				echo "<th class=''>Pässe</th>";
				echo "<th class=''>Flanken</th>";
				echo "<th class=''>Dribblings</th>";

				echo "<th class=''>Duelle</th>";
				echo "<th class=''>Tackles</th>";
				echo "<th class=''>Blocks</th>";
				echo "<th class=''>Klärungen</th>";
				echo "<th class=''>Abgefangen</th>";

				echo "<th class=''>Gehalten</th>";
				echo "<th class=''>Elfmeter</th>";
			echo "</tr>";

			while($row = mysqli_fetch_array($data_2019)) {
				echo "<tr>";
					echo "<td class='nobreak'>".$row['name']."</td>";
					echo "<td class='nobreak'>".mb_convert_encoding($row['matchup'], 'UTF-8')."</td>";
					echo "<td class='nobreak'>".$row['ft_score']."</td>";

					echo "<td class='highlight_td'>".$row['ftsy_score']."</td>";
					echo "<td>".$row['minutes_played']."</td>";

					echo "<td>".$row['goals']."</td>";
					echo "<td>".$row['assists']."</td>";

					echo "<td>".$row['shots']."</td>";
					echo "<td>".$row['passes_key']."</td>";
					echo "<td>".$row['passes']."</td>";
					echo "<td>".$row['crosses_stat']."</td>";
					echo "<td>".$row['dribbles']."</td>";

					echo "<td>".$row['duels']."</td>";
					echo "<td>".$row['tackles']."</td>";
					echo "<td>".$row['blocks']."</td>";
					echo "<td>".$row['clearances']."</td>";
					echo "<td>".$row['interceptions']."</td>";
					
					echo "<td>".$row['saves']."</td>";
					echo "<td>".$row['pen_saved']."</td>";
				echo "</tr>";
			}
		echo "</table>";
	echo "</div>";

}		

echo "<div id='transfer_daten'>";

	// Transfer history

	echo "<div class='sub_headline'>";
		echo "Transfer-Historie";
	echo "</div>";

	$tf_data = mysqli_query($con, "	
		SELECT 	YEAR(tf.transfer_dt) AS tf_year
						, COALESCE(abg.name, 'Unbekannt') AS abg_name
		        , abg.logo_path AS abg_logo
		        , COALESCE(auf.name, 'Unbekannt') AS auf_name
		        , auf.logo_path AS auf_logo
		        , tf.transfer_type
		        , CASE WHEN tf.transfer_type = 'Transfer' THEN coalesce(tf.amount, 'Unbekannt') ELSE tf.amount END AS amount
						
		FROM `sm_player_transfers` tf

		LEFT JOIN sm_teams abg
			ON abg.id = tf.from_team_id
		    
		LEFT JOIN sm_teams auf
			ON auf.id = tf.to_team_id

		WHERE tf.player_id =  '".$id."'

		ORDER BY tf.transfer_dt DESC
	");

	echo "<table id='transfer_table'>";		
		echo "<tr>";
			echo "<th>Jahr</th>";
			echo "<th colspan='2'>Aufnehmender Verein</th>";
			echo "<th colspan='2'>Abgebender Verein</th>";
			echo "<th>Transfer-Art</th>";
			echo "<th>Ablösesumme</th>";
		echo "</tr>";

		while($row = mysqli_fetch_array($tf_data)) {
			echo "<tr>";
				echo "<td>".$row['tf_year']."</td>";
				echo "<td><img height='15px' src='".$row['auf_logo']."'></td>";
				echo "<td>".mb_convert_encoding($row['auf_name'], 'UTF-8')."</td>";
				echo "<td><img height='15px' src='".$row['abg_logo']."'></td>";
				echo "<td>".mb_convert_encoding($row['abg_name'], 'UTF-8')."</td>";
				echo "<td>".$row['transfer_type']."</td>";
				echo "<td>".$row['amount']."</td>";
			echo "</tr>";
		}

	echo "</table>";				
echo "</div>";
mysqli_close($con);
?>