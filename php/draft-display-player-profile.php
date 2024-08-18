<?php

$id = $_GET["to_draft_player_id"];
include("../secrets/mysql_db_connection.php");

// Basic player profile
$player = mysqli_query($con, "	
	SELECT 	drft.*
					, base.height
					, base.weight
					, base.birthplace
					, base.birthcountry
					, base.captain
					, base.number
					, base.captain
					, FLOOR(DATEDIFF(CURRENT_DATE, base.birth_dt)/365) as age
					, case 	when base.injured = 1 and base.injury_reason is not NULL then concat('Verletzt: ', base.injury_reason)
									when base.injured = 1 and base.injury_reason is NULL then 'Verletzt'
									when base.injured = 0 and base.is_suspended = 1 then 'Gesperrt'
									else 'Fit'
									end as fitness  
					, base.injured 
					, base.injury_reason
					, base.is_suspended
					, base.number
					, ord.teamname as fantasy_team_name

	FROM draft_player_base drft
	
	INNER JOIN sm_playerbase base
		ON 	base.id = drft.id
	
	LEFT JOIN draft_order_full ord
		ON 	drft.pick = ord.pick
	
	WHERE drft.id = '".$id."'
	");

$player = mysqli_fetch_array($player);

// Ranking infos
$query = mysqli_query($con, " SELECT * FROM draft_player_ranking WHERE player_id = '".$id."' ");
$query = mysqli_fetch_array($query);

echo "<div id='spielerprofil_wrapper' class='spielerprofil'>";

	echo "<div class='back_to_grid_div'>";
		echo "<span class='back_to_grid_button'>Zurück zum Draft-Grid ⮌</span>";
	echo "</div>";

	echo "<div id='spielerprofil_headline'>";
		echo "SPIELERPROFIL";
	echo "</div>";

	echo "<div id ='spielerprofil_basisdaten'>";

		echo "<div id='spielerprofil_image'>";
			echo "<div style='background-image: url('".$player['team_logo']."');'><img id='player_image' src='".$player['image_path']."' width='auto' height='auto'></div>";
		echo "</div>";

		echo "<div id='spielerprofil_metadaten'>";

			echo "<div class='profil_player_name'>";
				echo $player['display_name'];
			echo "</div>";

			echo "<div class='metadaten_table'>";

				echo "<div class='left_col'>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Verein</div><div class='meta_value'>".mb_convert_encoding($player['teamname'], 'UTF-8')."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Position</div><div class='meta_value'>".$player['position_long']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Nummer</div><div class='meta_value'>".$player['number']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Fitness</div><div class='meta_value'>".mb_convert_encoding($player['fitness'],'UTF-8')."</div></div>";
				echo "</div>";

				echo "<div class=right_col>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Herkunft</div><div class='meta_value'>".mb_convert_encoding($player['birthplace'], 'UTF-8').", ".mb_convert_encoding($player['birthcountry'], 'UTF-8')."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Alter</div><div class='meta_value'>".$player['age']."</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Größe</div><div class='meta_value'>".$player['height']." cm</div></div>";
					echo "<div class='meta_stat_row'><div class='meta_stat'>Gewicht</div><div class='meta_value'>".$player['weight']." kg</div></div>";
				echo "</div>";

			echo "</div>";

		echo "</div>";

		echo "<div id='spielerprofil_fantasy'>";

			echo "<div id='spielerprofil_fantasy_head'>";
				echo "BuLi 22/23";
			echo "</div>";

			echo "<div id='spielerprofil_fantasy_score'>";

			echo "<div class='gesamt_fantasy_score'>";
				echo "<div class='gesamt_fantasy_score_head'>Gesamt</div>";
				echo "<div class='gesamt_fantasy_score_value'>".$query['sum_ftsy']."</div>";
			echo "</div>";

			echo "<div class='gesamt_fantasy_score'>";
				echo "<div class='gesamt_fantasy_score_head'>Schnitt</div>";
				echo "<div class='gesamt_fantasy_score_value'>".$query['avg_ftsy']."</div>";
			echo "</div>";

		echo "</div>";

		echo "<div id='spielerprofil_fantasy_rank'>";

			echo "<div>";
				echo "OVR #" . $query['rank_ovr_ftsy']; 
			echo "</div>";
			echo "<div>";
				echo $query['position_short'] . " #" . $query['rank_pos_ftsy'];
			echo "</div>";

		echo "</div>";

	echo "</div>";			

echo "</div>";

echo "<div id='highlights_2019'>";

	if ($player['position_short'] == 'TW') {
		$relevant_stats_array = ['min_played', 'saves', 'penalties_saved', 'passes', 'clearances'];
	} elseif ($player['position_short'] == 'AW') {
		$relevant_stats_array = ['min_played', 'goal', 'penalties', 'assists', 'passes', 'keypasses', 'shots', 'blocks', 'clearances', 'crosses', 'dribbles', 'duel_won', 'ints', 'tackles'];
	} elseif ($player['position_short'] == 'MF') {
		$relevant_stats_array = ['min_played', 'goal', 'penalties', 'assists', 'passes', 'keypasses', 'shots', 'crosses', 'dribbles', 'duel_won', 'ints'];
	} elseif ($player['position_short'] == 'ST') {
		$relevant_stats_array = ['min_played', 'goal', 'penalties', 'assists', 'passes', 'keypasses', 'shots', 'crosses', 'dribbles', 'duel_won'];
	}

	// Loop different stats and display them

	foreach ($relevant_stats_array as &$relevant_stat)  {

		if ($relevant_stat == 'min_played'){
			$stat_display_name = 'Minuten';

		} elseif ($relevant_stat == 'saves'){
			$stat_display_name = 'Saves';

		} elseif ($relevant_stat == 'penalites_saved'){
			$stat_display_name = '11er Saves';

		} elseif ($relevant_stat == 'passes'){
			$stat_display_name = 'Pässe';

		} elseif ($relevant_stat == 'clearnaces'){
			$stat_display_name = 'Klärungen';

		} elseif ($relevant_stat == 'goal'){
			$stat_display_name = 'Tore';

		} elseif ($relevant_stat == 'penalties'){
			$stat_display_name = '11er Tore';

		} elseif ($relevant_stat == 'assists'){
			$stat_display_name = 'Vorlagen';

		} elseif ($relevant_stat == 'keypasses'){
			$stat_display_name = 'Key-Pässe';

		} elseif ($relevant_stat == 'shots'){
			$stat_display_name = 'Schüsse';

		} elseif ($relevant_stat == 'blocks'){
			$stat_display_name = 'Blocks';

		} elseif ($relevant_stat == 'crosses'){
			$stat_display_name = 'Flanken';

		} elseif ($relevant_stat == 'dribbles'){
			$stat_display_name = 'Dribblings';

		} elseif ($relevant_stat == 'duel_won'){
			$stat_display_name = 'Duelle gewonnen';

		} elseif ($relevant_stat == 'ints'){
			$stat_display_name = 'Abgefangen';

		} elseif ($relevant_stat == 'tackles'){
			$stat_display_name = 'Tackles';
		} 

		// Construct variables
		$rank_ovr = 'rank_ovr_' . $relevant_stat;
		$rank_pos = 'rank_pos_' . $relevant_stat;
		$sum_stat = 'sum_' . $relevant_stat;
		$avg_stat = 'avg_' . $relevant_stat;


		echo "<div class='highlight_stat'>";

			echo "<div class='highlight_stat_headline'>";
				echo $stat_display_name;
			echo "</div>";

			echo "<div class='higlight_stat_value'>";
				echo $query[$sum_stat];
			echo "</div>";

			echo "<div class='higlight_stat_ranks'>";

				echo "<div class='highlight_stat_rank_ovr'>";
					echo 'OVR ' . $query[$rank_ovr];
				echo "</div>";

				echo "<div class='highlight_stat_rank_pos'>";
					echo  $query['position_short'] . ' ' . $query[$rank_pos];
				echo "</div>";

			echo "</div>";
		echo "</div>";
	}

echo "</div>";

// Draft button

if (is_null($player['pick']) == True) {

	echo "<div id ='draft_button_div'>";
		echo "<div id='draft_me_button' data-playerid='".$id."'>» Draft ".$player['display_name']." «</div>";
	echo "</div>";

} elseif (is_null($player['pick']) == False) {

	echo "<div id ='draft_button_div'>";
		echo "<div id='player_is_drafted'>Gedrafted von ".mb_convert_encoding($player['fantasy_team_name'],'UTF-8')." (Runde: ".$player['round'].", Pick: ".$player['pick'].")</div>";
	echo "</div>";		

}

// Transfer data

echo "<div id='transfer_daten'>";

	echo "<div class='sub_headline'>";
		echo "Transfer-Historie";
	echo "</div>";

	$tf_data = mysqli_query($con, "	
		SELECT 	year(tf.transfer_dt) as tf_year
						, coalesce(abg.name, 'Unbekannt') as abg_name
						, abg.logo_path as abg_logo
						, coalesce(auf.name, 'Unbekannt') as auf_name
						, auf.logo_path as auf_logo
						, tf.transfer_type
						, case when tf.transfer_type = 'Transfer' then coalesce(tf.amount, 'Unbekannt') else tf.amount end as amount

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

// Display match data from past seasons

$season_ids_for_stats = array(21795, 19744, 18444);
$season_names_for_stats = array('2023/2024','2022/2023','2021/2022');
$season_counter = 0;
$season_counter_max = count($season_ids_for_stats)-1;

// Aggregated data

echo "<div id='data_2019'>";

	echo "<div class='sub_headline'>";
		echo "Fantasy-Performance seit 2019/2020";
	echo "</div>";

	$data_2019 = mysqli_query($con, "	

		SELECT 	ses.season_name
						, sum(scr.ftsy_score) as ftsy_score_sum
						, round(avg(case when scr.appearance_stat = 1 then scr.ftsy_score else null end),1) as ftsy_score_avg
						, sum(case when scr.appearance_stat = 1 then 1 else 0 end) as appearances
						, sum(scr.goals_total_stat) as goals
						, sum(scr.assists_stat) as assists
						, sum(scr.shots_total_stat) as shots
						, sum(scr.key_passes_stat) as passes_key
						, sum(scr.passes_complete_stat) as passes
						, round((sum(scr.passes_complete_stat)/sum(scr.passes_total_stat))*100,0) as passes_perc
						, sum(scr.crosses_complete_stat) as crosses
						, round((sum(scr.crosses_complete_stat)/sum(scr.crosses_total_stat))*100,0) as crosses_perc
						, sum(scr.dribbles_success_stat) as dribbles
						, round((sum(scr.dribbles_success_stat)/sum(scr.dribble_attempts_stat))*100,0) as dribbles_perc
						, sum(scr.duels_won_stat) as duels
						, round((sum(scr.duels_won_stat)/sum(scr.duels_won_stat+scr.duels_lost_stat))*100,0) as duels_perc
						, sum(scr.blocks_stat) as blocks
						, sum(scr.clearances_stat) as clearances
						, sum(scr.interceptions_stat) as interceptions
						, sum(scr.tackles_stat) as tackles
						, sum(scr.saves_stat) as saves
						, sum(scr.pen_saved_stat) as pen_saved

		FROM ftsy_scoring_all_v scr

		INNER JOIN sm_fixtures fix
			ON fix.fixture_id = scr.fixture_id		

		INNER JOIN sm_seasons ses
			ON ses.season_id = fix.season_id										    

		WHERE scr.player_id = '".$id."'

		GROUP BY ses.season_name

		ORDER BY ses.season_name desc 
		
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

// Loop seasons

for ($season_counter = 0; $season_counter <= $season_counter_max; $season_counter++) {

	echo "<div id='data_2019'>";

		echo "<div class='sub_headline'>";
			echo "Statistiken 1. Bundesliga " . $season_names_for_stats[$season_counter];
		echo "</div>";

		$data_2019 = mysqli_query($con, "	
			SELECT 	rds.id
							, rds.name
							, concat(concat(fix.localteam_name_code, ' vs. '), fix.visitorteam_name_code) as matchup
							, scr.own_team_code
							, concat(concat(fix.localteam_score, ':'), fix.visitorteam_score) as ft_score
							, case when scr.appearance_stat = 1 then scr.ftsy_score else null end as ftsy_score
							, case when scr.appearance_stat = 1 then concat(CONVERT(scr.minutes_played_stat,CHAR), ' Min.') else null end as minutes_played
							, case when scr.appearance_stat = 1 then scr.goals_total_stat else null end as goals
							, case when scr.appearance_stat = 1 then scr.assists_stat else null end as assists
							, case when scr.appearance_stat = 1 then scr.shots_total_stat else null end as shots
							, case when scr.appearance_stat = 1 then scr.key_passes_stat else null end as passes_key
							, case when scr.appearance_stat = 1 then concat(concat(concat(scr.passes_complete_stat, ' ('), scr.passes_total_stat),')') else null end as passes
							, case when scr.appearance_stat = 1 then concat(concat(concat(scr.crosses_complete_stat, ' ('), scr.crosses_total_stat),')') else null end as crosses_stat
							, case when scr.appearance_stat = 1 then concat(concat(concat(scr.dribbles_success_stat, ' ('), scr.dribble_attempts_stat),')') else null end as dribbles
							, case when scr.appearance_stat = 1 then concat(concat(concat(scr.duels_won_stat, ' ('), scr.duels_total_stat),')') else null end as duels
							, case when scr.appearance_stat = 1 then scr.blocks_stat else null end as blocks
							, case when scr.appearance_stat = 1 then scr.clearances_stat else null end as clearances
							, case when scr.appearance_stat = 1 then scr.interceptions_stat else null end as interceptions
							, case when scr.appearance_stat = 1 then scr.tackles_stat else null end as tackles
							, case when scr.appearance_stat = 1 then scr.saves_stat else null end as saves
							, case when scr.appearance_stat = 1 then scr.pen_saved_stat else null end as pen_saved

			FROM `sm_rounds` rds

			LEFT JOIN ftsy_scoring_all_v scr
				ON 	scr.round_name = rds.name
						AND scr.fixture_id IN (SELECT DISTINCT fixture_id FROM sm_fixtures WHERE season_id = '". $season_ids_for_stats[$season_counter]."') 

			LEFT JOIN sm_fixtures_basic_v fix
				ON 	fix.fixture_id = scr.fixture_id

			WHERE 	rds.season_id = '". $season_ids_for_stats[$season_counter]."'
							AND scr.player_id = '".$id."'

			ORDER BY `rds`.`name`  ASC
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

echo "<div class='back_to_grid_div'>";
	echo "<span class='back_to_grid_button'>Zurück zum Draft-Grid ⮌</span>";
echo "</div>";							


echo "</div>";
mysqli_close($con);
?>
