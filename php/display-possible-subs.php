<?php
include("auth.php");
include('../secrets/mysql_db_connection.php');

// user and season data
$user = mb_convert_encoding($_SESSION['username'], 'UTF-8');
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

// sub-out-player
$move_to_bench_player = $_GET['clicked_player'];
$move_to_bench_player_pos = mysqli_query($con, "SELECT position_short as pos from xa7580_db1.sm_playerbase where id = '".$move_to_bench_player."' ") -> fetch_object() -> pos;	
$move_to_bench_player_status = mysqli_query($con, "SELECT ".$ftsy_status_column." as player_status from xa7580_db1.ftsy_player_ownership where player_id = '".intval($move_to_bench_player)."' ") -> fetch_object() -> player_status;	

// current formation
$akt_aufstellung = mysqli_query($con, "SELECT akt_aufstellung from xa7580_db1.users_gamedata where username = '".$user."' ") -> fetch_object() -> akt_aufstellung;	
$cnt_aufstellung = mysqli_query($con, "SELECT count(*) as cnt from xa7580_db1.ftsy_player_ownership where ".$ftsy_status_column." != 'NONE' and ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> cnt;	


if ($cnt_aufstellung < 11) {

	/************************/
	/* INCOMPLETE FORMATION */
	/************************/

	$akt_aufstellung_soll_tw = 1;
	$akt_aufstellung_soll_aw = floor($akt_aufstellung / 100); 
	$akt_aufstellung_soll_mf = floor(($akt_aufstellung - ($akt_aufstellung_soll_aw * 100))/10);
	$akt_aufstellung_soll_st = $akt_aufstellung - $akt_aufstellung_soll_aw - $akt_aufstellung_soll_mf;

	$akt_aufstellung_haben_tw = mysqli_query($con, "
		SELECT COUNT(*) as cnt 
		FROM xa7580_db1.ftsy_player_ownership own 
		INNER JOIN xa7580_db1.sm_playerbase base
			ON base.id = own.player_id
		WHERE 	base.position_short = 'TW' 
						AND ".$ftsy_status_column." != 'NONE' 
						AND ".$ftsy_owner_column." = '".$user_id."' 
		") -> fetch_object() -> cnt;

	$akt_aufstellung_haben_aw = mysqli_query($con, "
		SELECT COUNT(*) as cnt 
		FROM xa7580_db1.ftsy_player_ownership own 
		INNER JOIN xa7580_db1.sm_playerbase base
			ON base.id = own.player_id
		WHERE 	base.position_short = 'AW' 
						AND ".$ftsy_status_column." != 'NONE' 
						AND ".$ftsy_owner_column." = '".$user_id."' 
		") -> fetch_object() -> cnt;

	$akt_aufstellung_haben_mf = mysqli_query($con, "
		SELECT COUNT(*) as cnt 
		FROM xa7580_db1.ftsy_player_ownership own 
		INNER JOIN xa7580_db1.sm_playerbase base
			ON base.id = own.player_id
		WHERE 	base.position_short = 'MF' 
						AND ".$ftsy_status_column." != 'NONE' 
						AND ".$ftsy_owner_column." = '".$user_id."' 
		") -> fetch_object() -> cnt;

	$akt_aufstellung_haben_st = mysqli_query($con, "
		SELECT COUNT(*) as cnt 
		FROM xa7580_db1.ftsy_player_ownership own 
		INNER JOIN xa7580_db1.sm_playerbase base
			ON base.id = own.player_id
		WHERE 	base.position_short = 'ST' 
						AND ".$ftsy_status_column." != 'NONE' 
						AND ".$ftsy_owner_column." = '".$user_id."' 
		") -> fetch_object() -> cnt;


	if ($move_to_bench_player_status == 'NONE') {

		$kader = mysqli_query($con,"	
			SELECT 	base.id #
							, base.display_name #
			        , base.position_short #
			        , base.image_path #
			        , case 	when base.is_suspended is not null then 'rote-karte.png'
			        				when base.injured = 1 then 'verletzung.png'
			        				when base.is_sidelined = 1 then 'verbannung.png'
			        				else 'fit.png'
											end as fitness_img
			        , base.injury_reason
			        , base.team_id
			        , base.short_code as team_code
			        , case when base.current_team_id = fix.localteam_id then team_away.short_code else team_home.short_code end as gegner 
			        , case when base.current_team_id = fix.localteam_id then 'H' else 'A' end as homeaway         
			        , base.1_ftsy_match_status
			        , fix.round_name
			        , fix.fixture_id
			        , fix.kickoff_dt
			        , fix.kickoff_ts
			        , fix.match_status
			        , case 	when dayname(fix.kickoff_dt) = 'Monday' then 'Mo.'
			        		when dayname(fix.kickoff_dt) = 'Tuesday' then 'Di.'
			        		when dayname(fix.kickoff_dt) = 'Wednesday' then 'Mi.'
			        		when dayname(fix.kickoff_dt) = 'Thursday' then 'Do.'
			        		when dayname(fix.kickoff_dt) = 'Friday' then 'Fr.'
			        		when dayname(fix.kickoff_dt) = 'Saturday' then 'Sa.'
			        		when dayname(fix.kickoff_dt) = 'Sunday' then 'So.'
			        		end as kickoff_weekday
			        , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND as kickoff_time_trunc
			        , month(fix.kickoff_dt) as kickoff_month
			        , day(fix.kickoff_dt) as kickoff_day

	        		, base.".$ftsy_status_column." as ftsy_match_status

			FROM sm_playerbase_basic_v base

			LEFT JOIN sm_fixtures fix 
				ON 	( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id )
							AND fix.round_name = '".intval($akt_spieltag)."'
							AND fix.season_id = '".intval($akt_season_id)."'
							AND fix.kickoff_ts > NOW()

			LEFT JOIN sm_teams team_home
				ON fix.localteam_id = team_home.id

			LEFT JOIN sm_teams team_away
				ON fix.visitorteam_id = team_away.id
			
			WHERE 	".$ftsy_owner_column." = '".$user_id."'
							and base.".$ftsy_status_column." = 'NONE'
							and  ( 
								case when '".$akt_aufstellung_haben_tw."' = 0 THEN base.position_short = 'TW' end
								or case when '".$akt_aufstellung_haben_aw."' < 5 then base.position_short = 'AW' end
								or case when '".$akt_aufstellung_haben_mf."' < 5 then base.position_short = 'MF' end
								or case when '".$akt_aufstellung_haben_st."' < 3 then base.position_short = 'ST' end
								) 
		");

		$data = array();

		while ($row = mysqli_fetch_array($kader)) {
		    $data[] = $row;
		}

		echo "<div class ='modal_table_wrapper'>";
		echo "<table class='player_table2'>";

		foreach ($data as $row) {
 	
			$complete_matchup = $row['anstoss'] . $row['matchup'];
			$tore = $row['tore_raw']+$row['elfmeter_raw'];

			echo "<tr class=''>";

			
			echo "<td><div class='player_in player_in_click' onclick='executeChangePlayer(this)' data-id='" . $row['id'] . "'>&#10557;</div></td>";

			echo "<td class = td_player>";
				echo "<div class='player_card'>";
					echo "<div>";
						echo "<img height='40px' width='auto' src='" . $row['image_path'] . "'>";
					echo "</div>";
					echo "<div class='player_card_text'>";
						echo "<div class='player_card_name'>";
							echo mb_convert_encoding($row['display_name'], 'UTF-8');
						echo "</div>";
						echo "<div class='player_card_detail'>";
							echo  $row['position_short'] . " - " . utf8_encode($row['verein_short']) . "<img title='" . utf8_encode($row['fitness']) . " " . utf8_encode($row['verletzung']) . "' height='13px' width='auto' src='../img/icons/" . $row['fitness_img'] . "'>";			
						echo "</div>";									
					echo "</div>";			
				echo "</div>";
			echo "</td>";
			echo "<td>".utf8_encode($complete_matchup)."</td>";
			echo "</tr>";
		}
		 	
		echo "</table>";
		echo "</div>";	

	}


} else {

	/**********************/
	/* COMPLETE FORMATION */
	/**********************/

	
	if ($move_to_bench_player_status == 'NONE'){

		$kader = mysqli_query($con,"	
			SELECT 	base.id #
							, base.display_name #
			        , base.position_short #
			        , base.image_path #
			        , case 	when base.is_suspended is not null then 'rote-karte.png'
					        		when base.injured = 1 then 'verletzung.png'
					        		when base.is_sidelined = 1 then 'verbannung.png'
					        		else 'fit.png'
											end as fitness_img
			        , base.injury_reason
			        , base.team_id
			        , base.short_code as team_code
			        , case when base.current_team_id = fix.localteam_id then team_away.short_code else team_home.short_code end as gegner 
			        , case when base.current_team_id = fix.localteam_id then 'H' else 'A' end as homeaway         
			        , base.1_ftsy_match_status
			        , fix.round_name
			        , fix.fixture_id
			        , fix.kickoff_dt
			        , fix.kickoff_ts
			        , fix.match_status
			        , case 	when dayname(fix.kickoff_dt) = 'Monday' then 'Mo.'
					        		when dayname(fix.kickoff_dt) = 'Tuesday' then 'Di.'
					        		when dayname(fix.kickoff_dt) = 'Wednesday' then 'Mi.'
					        		when dayname(fix.kickoff_dt) = 'Thursday' then 'Do.'
					        		when dayname(fix.kickoff_dt) = 'Friday' then 'Fr.'
					        		when dayname(fix.kickoff_dt) = 'Saturday' then 'Sa.'
					        		when dayname(fix.kickoff_dt) = 'Sunday' then 'So.'
					        		end as kickoff_weekday
			        , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND as kickoff_time_trunc
			        , month(fix.kickoff_dt) as kickoff_month
			        , day(fix.kickoff_dt) as kickoff_day

			        , base.".$ftsy_status_column." as ftsy_match_status

			       	, 1 as bank_zu_aufst_flg
							, case when base.id = '".$move_to_bench_player."' then 1 else 0 end as chosen_player_flg

			FROM sm_playerbase_basic_v base

			LEFT JOIN sm_fixtures fix 
				ON 	( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id )
					AND fix.round_name = '".$akt_spieltag."'
					AND fix.season_id = '".$akt_season_id."'

			LEFT JOIN sm_teams team_home
				ON fix.localteam_id = team_home.id

			LEFT JOIN sm_teams team_away
				ON fix.visitorteam_id = team_away.id

			WHERE 	".$ftsy_owner_column." = '".$user_id."'
					and fix.kickoff_ts > NOW()
					and ( base.".$ftsy_status_column." != 'NONE' or base.id = '".$move_to_bench_player."' )

					
					and case when '".$move_to_bench_player_pos."' != 'TW' then base.position_short != 'TW' else base.position_short is not null end 
					and case when '".$move_to_bench_player_pos."' = 'TW' then base.position_short = 'TW' else base.position_short is not null end 
					and case 	when '".$move_to_bench_player_pos."' = 'ST' then 
								case 	when '".$akt_aufstellung."' in (433, 343) then base.position_short = 'ST'
										when '".$akt_aufstellung."' in (541, 442, 451) then base.position_short is not null 
										when '".$akt_aufstellung."' in (532) then base.position_short != 'MF'
										when '".$akt_aufstellung."' in (352) then base.position_short != 'AW'
										end 
								when '".$move_to_bench_player_pos."' = 'AW' then 
								case 	when '".$akt_aufstellung."' in (532) then base.position_short = 'AW'
										when '".$akt_aufstellung."' in (442, 343, 352) then base.position_short is not null 
										when '".$akt_aufstellung."' in (433) then base.position_short != 'MF'
										when '".$akt_aufstellung."' in (541, 451) then base.position_short != 'ST'
										end
								when '".$move_to_bench_player_pos."' = 'MF' then 
								case 	when '".$akt_aufstellung."' in (451, 352) then base.position_short = 'MF'
										when '".$akt_aufstellung."' in (442, 532, 433) then base.position_short is not null 
										when '".$akt_aufstellung."' in (343) then base.position_short != 'AW'
										when '".$akt_aufstellung."' in (541) then base.position_short != 'ST'
															end
								else base.position_short is not null
						end
				ORDER BY CASE 	WHEN position_short = 'ST' THEN 4
							WHEN position_short = 'MF' THEN 3
							WHEN position_short = 'AW' THEN 2
							WHEN position_short = 'TW' THEN 1
					END DESC

			");

	} else {

		$kader = mysqli_query($con,"	
			SELECT 	base.id #
							, base.display_name #
			        , base.position_short #
			        , base.image_path #
			        , case 	when base.is_suspended is not null then 'rote-karte.png'
					        		when base.injured = 1 then 'verletzung.png'
					        		when base.is_sidelined = 1 then 'verbannung.png'
					        		else 'fit.png'
											end as fitness_img
			        , base.injury_reason
			        , base.team_id
			        , base.short_code as team_code
			        , case when base.current_team_id = fix.localteam_id then team_away.short_code else team_home.short_code end as gegner 
			        , case when base.current_team_id = fix.localteam_id then 'H' else 'A' end as homeaway         
			        , base.1_ftsy_match_status
			        , fix.round_name
			        , fix.fixture_id
			        , fix.kickoff_dt
			        , fix.kickoff_ts
			        , fix.match_status
			        , case 	when dayname(fix.kickoff_dt) = 'Monday' then 'Mo.'
					        		when dayname(fix.kickoff_dt) = 'Tuesday' then 'Di.'
					        		when dayname(fix.kickoff_dt) = 'Wednesday' then 'Mi.'
					        		when dayname(fix.kickoff_dt) = 'Thursday' then 'Do.'
					        		when dayname(fix.kickoff_dt) = 'Friday' then 'Fr.'
					        		when dayname(fix.kickoff_dt) = 'Saturday' then 'Sa.'
					        		when dayname(fix.kickoff_dt) = 'Sunday' then 'So.'
					        		end as kickoff_weekday
			        , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND as kickoff_time_trunc
			        , month(fix.kickoff_dt) as kickoff_month
			        , day(fix.kickoff_dt) as kickoff_day
			        , base.".$ftsy_status_column." as ftsy_match_status
							, 0 as bank_zu_aufst_flg
							, case when base.id = '".$move_to_bench_player."' then 1 else 0 end as chosen_player_flg

			FROM sm_playerbase_basic_v base

			LEFT JOIN sm_fixtures fix 
				ON 	( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id )
					AND fix.round_name = '".$akt_spieltag."'
					AND fix.season_id = '".$akt_season_id."'

			LEFT JOIN sm_teams team_home
				ON fix.localteam_id = team_home.id

			LEFT JOIN sm_teams team_away
				ON fix.visitorteam_id = team_away.id

			WHERE 	".$ftsy_owner_column." = '".$user_id."'
					and fix.kickoff_ts > NOW()										
					and ( base.".$ftsy_status_column." = 'NONE' or base.id = '".$move_to_bench_player."' )	
					and case when '".$move_to_bench_player_pos."' != 'TW' then base.position_short != 'TW' else base.position_short = 'TW' end 
					and ( case when '".$akt_aufstellung."' in (343, 433) and '".$move_to_bench_player_pos."' != 'ST' then base.position_short != 'ST' else base.position_short is not null end)
					and ( case when '".$akt_aufstellung."' in (451, 352) and '".$move_to_bench_player_pos."' != 'MF' then base.position_short != 'MF' else base.position_short is not null end)
					and ( case when '".$akt_aufstellung."' in (433, 532) and '".$move_to_bench_player_pos."' = 'MF' then base.position_short = 'MF' else base.position_short is not null end)
					and ( case when '".$akt_aufstellung."' in (532, 541) and '".$move_to_bench_player_pos."' != 'AW' then base.position_short != 'AW' else base.position_short is not null end)
					and ( case when '".$akt_aufstellung."' in (352, 343) and '".$move_to_bench_player_pos."' = 'AW' then base.position_short = 'AW' else base.position_short is not null end)
					
			ORDER BY CASE 	WHEN base.position_short = 'ST' THEN 4
							WHEN base.position_short = 'MF' THEN 3
							WHEN base.position_short = 'AW' THEN 2
							WHEN base.position_short = 'TW' THEN 1
					END DESC

		");
	
	}

	$data = array();
	while ($row = mysqli_fetch_array($kader)) {
	   $data[] = $row;
	}

	echo "<div class ='modal_table_wrapper'>";
	echo "<table class='player_table2'>";

	foreach ($data as $row) {
		if ($row['chosen_player_flg'] == 1){  
	  	
			$complete_matchup = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). " vs. " .$row['gegner']. " (".$row['homeaway'] . ")";

			echo "<tr class=''>";
		
			if ($row['ftsy_match_status'] == 'NONE'){
				echo "<td><div class='player_in'>&#10557;</div></td>";
			} else {
				echo "<td><div class='player_out'>&#10556;</div></td>";
			}

			echo "<td class = td_player>";
				echo "<div class='player_card'>";
					echo "<div>";
						echo "<img height='40px' width='auto' src='" . $row['image_path'] . "'>";
					echo "</div>";
					echo "<div class='player_card_text'>";
						echo "<div class='player_card_name'>";
							echo mb_convert_encoding($row['display_name'], 'UTF-8');
						echo "</div>";
						echo "<div class='player_card_detail'>";
							echo  $row['position_short'] . " - " . mb_convert_encoding($row['team_code'], 'UTF-8') . "<img title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['injury_reason'], 'UTF-8') . "' height='13px' width='auto' src='../img/icons/" . $row['fitness_img'] . "'>";			
						echo "</div>";									
					echo "</div>";			
				echo "</div>";
			echo "</td>";
			echo "<td>".mb_convert_encoding($complete_matchup, 'UTF-8')."</td>";
			echo "</tr>";
		}
	}
	 	
	echo "</table>";
	echo "</div>";


	echo "<div class ='modal_table_wrapper'>";
	echo "<table class='player_table2'>";

	$cat = '';

	foreach ($data as $row) {
		if ($row['chosen_player_flg'] == 0){

			if ($row['bank_zu_aufst_flg'] == 0) {
				if ($row['position_short'] == $move_to_bench_player_pos){
				$neue_formation = $akt_aufstellung; 
				} elseif ($move_to_bench_player_pos === 'ST'){
					if ($row['position_short'] === 'MF'){
						$neue_formation = $akt_aufstellung - 1 + 10;
					} elseif ($row['position_short'] === 'AW'){
						$neue_formation = $akt_aufstellung - 1 + 100;
					} else { 
					$neue_formation = 999;
						}
				} elseif ($move_to_bench_player_pos === 'AW'){
					if ($row['position_short'] === 'ST'){
						$neue_formation = $akt_aufstellung + 1 - 100;
					} elseif ($row['position_short'] === 'MF'){
						$neue_formation = $akt_aufstellung + 10 - 100;
					} else { 
					$neue_formation = 999;
						}
				} elseif ($move_to_bench_player_pos === 'MF'){
					if ($row['position_short'] === 'ST'){
						$neue_formation = $akt_aufstellung - 10 + 1;
					} elseif ($row['position_short'] === 'AW'){
						$neue_formation = $akt_aufstellung - 10 + 100;
					} else { 
					$neue_formation = 999;
					}
				}
			} elseif ($row['bank_zu_aufst_flg'] == 1) {
				if ($row['position_short'] == $move_to_bench_player_pos){
				$neue_formation = $akt_aufstellung; 
				} elseif ($move_to_bench_player_pos === 'ST'){
					if ($row['position_short'] === 'MF'){
						$neue_formation = $akt_aufstellung + 1 - 10;
					} elseif ($row['position_short'] === 'AW'){
						$neue_formation = $akt_aufstellung + 1 - 100;
					} else { 
					$neue_formation = 999;
						}
				} elseif ($move_to_bench_player_pos === 'AW'){
					if ($row['position_short'] === 'ST'){
						$neue_formation = $akt_aufstellung - 1 + 100;
					} elseif ($row['position_short'] === 'MF'){
						$neue_formation = $akt_aufstellung - 10 + 100;
					} else { 
					$neue_formation = 999;
						}
				} elseif ($move_to_bench_player_pos === 'MF'){
					if ($row['position_short'] === 'ST'){
						$neue_formation = $akt_aufstellung + 10 - 1;
					} elseif ($row['position_short'] === 'AW'){
						$neue_formation = $akt_aufstellung + 10 - 100;
					} else { 
					$neue_formation = 999;
					}
				}		}	

		if ($cat != $row['position_short']) {
			if ($row['bank_zu_aufst_flg'] == 0){
	    	echo "<tr class='tr_cat_change'><td class='td_cat_change' colspan='9'>".$row['position_short']." für ".$move_to_bench_player_pos." (".$akt_aufstellung." zu ".$neue_formation.")</td></tr>";
	    		$cat = $row['position_short'];
	    } else {
	      echo "<tr class='tr_cat_change'><td class='td_cat_change' colspan='9'>".$move_to_bench_player_pos." für ".$row['position_short']." (".$akt_aufstellung." zu ".$neue_formation.")</td></tr>";
	    	$cat = $row['position_short'];	
	    }
	  }

		$complete_matchup = $row['kickoff_weekday'] . ", " . $row['kickoff_day'] . "." . $row['kickoff_month'] . ". " . strval($row['kickoff_time_trunc']). " vs. " .$row['gegner']. " (".$row['homeaway'] . ")";		
		echo "<tr class=''>";
		if ($row['ftsy_match_status'] == 'NONE'){
				echo "<td><div class='player_in player_in_click' onclick='executeChangePlayer(this)' data-id='" . $row['id'] . "'>&#10557;</div></td>";
		} else {
				echo "<td><div class='player_out player_out_click' onclick='executeChangePlayer(this)' data-id='" . $row['id'] . "'>&#10556;</div></td>";
		}	echo "<td class = td_player>";
			echo "<div class='player_card'>";
				echo "<div>";
					echo "<img height='40px' width='auto' src='" . $row['image_path'] . "'>";
				echo "</div>";
				echo "<div class='player_card_text'>";
					echo "<div class='player_card_name'>";
						echo mb_convert_encoding($row['display_name'], 'UTF-8');
					echo "</div>";
					echo "<div class='player_card_detail'>";
						echo  $row['position_short'] . " - " . mb_convert_encoding($row['team_code'], 'UTF-8') . "<img title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['injury_reason'], 'UTF-8') . "' height='13px' width='auto' src='../img/icons/" . $row['fitness_img'] . "'>";			
					echo "</div>";									
				echo "</div>";			
			echo "</div>";
		echo "</td>";
		echo "<td>".mb_convert_encoding($complete_matchup, 'UTF-8')."</td>";
		echo "</tr>";
		}
	}
	 	
	$previousCategory = $thisCategory;
	echo "</table>";
	echo "</div>";

}



?>
