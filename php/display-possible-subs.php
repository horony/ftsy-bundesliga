<?php
include("auth.php");
include('../secrets/mysql_db_connection.php');

// user AND season data
$user = mb_convert_encoding($_SESSION['username'], 'UTF-8');
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$akt_spieltag = mysqli_query($con, "SELECT spieltag from xa7580_db1.parameter ") -> fetch_object() -> spieltag;	
$akt_season_id = mysqli_query($con, "SELECT season_id from xa7580_db1.parameter ") -> fetch_object() -> season_id;	

// sub-out-player
$move_to_bench_player = $_GET['clicked_player'];
$move_to_bench_player_pos = mysqli_query($con, "SELECT position_short AS pos from xa7580_db1.sm_playerbase where id = '".$move_to_bench_player."' ") -> fetch_object() -> pos;	
$move_to_bench_player_status = mysqli_query($con, "SELECT ".$ftsy_status_column." AS player_status from xa7580_db1.ftsy_player_ownership where player_id = '".intval($move_to_bench_player)."' ") -> fetch_object() -> player_status;	

// current formation
$akt_aufstellung = mysqli_query($con, "SELECT akt_aufstellung from xa7580_db1.users_gamedata where username = '".$user."' ") -> fetch_object() -> akt_aufstellung;	
$cnt_aufstellung = mysqli_query($con, "SELECT count(*) AS cnt from xa7580_db1.ftsy_player_ownership where ".$ftsy_status_column." != 'NONE' AND ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> cnt;	


if ($cnt_aufstellung < 11) {

    /************************/
    /* INCOMPLETE FORMATION */
    /************************/

    $akt_aufstellung_soll_tw = 1;
    $akt_aufstellung_soll_aw = floor($akt_aufstellung / 100); 
    $akt_aufstellung_soll_mf = floor(($akt_aufstellung - ($akt_aufstellung_soll_aw * 100))/10);
    $akt_aufstellung_soll_st = $akt_aufstellung - $akt_aufstellung_soll_aw - $akt_aufstellung_soll_mf;

    $akt_aufstellung_haben_tw = mysqli_query($con, "
        SELECT COUNT(*) AS cnt 
        FROM xa7580_db1.ftsy_player_ownership own 
        INNER JOIN xa7580_db1.sm_playerbase base
            ON base.id = own.player_id
        WHERE 
            base.position_short = 'TW' 
            AND ".$ftsy_status_column." != 'NONE' 
            AND ".$ftsy_owner_column." = '".$user_id."' 
        ") -> fetch_object() -> cnt;

    $akt_aufstellung_haben_aw = mysqli_query($con, "
        SELECT COUNT(*) AS cnt 
        FROM xa7580_db1.ftsy_player_ownership own 
        INNER JOIN xa7580_db1.sm_playerbase base
            ON base.id = own.player_id
        WHERE 
            base.position_short = 'AW' 
            AND ".$ftsy_status_column." != 'NONE' 
            AND ".$ftsy_owner_column." = '".$user_id."' 
        ") -> fetch_object() -> cnt;

    $akt_aufstellung_haben_mf = mysqli_query($con, "
        SELECT COUNT(*) AS cnt 
        FROM xa7580_db1.ftsy_player_ownership own 
        INNER JOIN xa7580_db1.sm_playerbase base
            ON base.id = own.player_id
        WHERE
            base.position_short = 'MF' 
            AND ".$ftsy_status_column." != 'NONE' 
            AND ".$ftsy_owner_column." = '".$user_id."' 
        ") -> fetch_object() -> cnt;

    $akt_aufstellung_haben_st = mysqli_query($con, "
        SELECT COUNT(*) AS cnt 
        FROM xa7580_db1.ftsy_player_ownership own 
        INNER JOIN xa7580_db1.sm_playerbase base
            ON base.id = own.player_id
        WHERE
            base.position_short = 'ST' 
            AND ".$ftsy_status_column." != 'NONE' 
            AND ".$ftsy_owner_column." = '".$user_id."' 
        ") -> fetch_object() -> cnt;

    if ($move_to_bench_player_status == 'NONE') {

        $kader = mysqli_query($con,"	
            SELECT 	
                base.id
                , base.display_name 
                , base.position_short 
                , base.image_path 
                , base.player_status_logo_path
                , base.sidelined_reason
                , base.team_id
                , base.short_code AS team_code
                , CASE WHEN base.team_id = fix.localteam_id THEN team_away.short_code ELSE team_home.short_code END AS gegner 
                , CASE WHEN base.team_id = fix.localteam_id THEN 'H' ELSE 'A' END AS homeaway         
                , base.1_ftsy_match_status
                , fix.round_name
                , fix.fixture_id
                , fix.kickoff_dt
                , fix.kickoff_ts
                , fix.match_status
                , CASE 
                    WHEN DAYNAME(fix.kickoff_dt) = 'Monday' THEN 'Mo.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Tuesday' THEN 'Di.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Thursday' THEN 'Do.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Friday' THEN 'Fr.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Saturday' THEN 'Sa.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Sunday' THEN 'So.'
                    END AS kickoff_weekday
                , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND AS kickoff_time_trunc
                , MONTH(fix.kickoff_dt) AS kickoff_month
                , DAY(fix.kickoff_dt) AS kickoff_day
                , base.".$ftsy_status_column." AS ftsy_match_status
            FROM sm_playerbase_basic_v base
            LEFT JOIN sm_fixtures fix 
                ON ( base.team_id = fix.localteam_id OR base.team_id = fix.visitorteam_id )
                AND fix.round_name = '".intval($akt_spieltag)."'
                AND fix.season_id = '".intval($akt_season_id)."'
                AND fix.kickoff_ts > NOW()
            LEFT JOIN sm_teams team_home
                ON fix.localteam_id = team_home.id
            LEFT JOIN sm_teams team_away
                ON fix.visitorteam_id = team_away.id
            WHERE 
                ".$ftsy_owner_column." = '".$user_id."'
                AND base.".$ftsy_status_column." = 'NONE'
                AND ( 
                    CASE WHEN '".$akt_aufstellung_haben_tw."' = 0 THEN base.position_short = 'TW' END
                    OR CASE WHEN '".$akt_aufstellung_haben_aw."' < 5 THEN base.position_short = 'AW' END
                    OR CASE WHEN '".$akt_aufstellung_haben_mf."' < 5 THEN base.position_short = 'MF' END
                    OR CASE WHEN '".$akt_aufstellung_haben_st."' < 3 THEN base.position_short = 'ST' END
                    ) 
        ");

        $data = array();

        while ($row = mysqli_fetch_array($kader)) {
            $data[] = $row;
        }

        echo "<div class ='modal_table_wrapper'>";
        echo "<table class='player_table2'>";

        foreach ($data AS $row) {
     
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
                            echo  $row['position_short'] . " - " . utf8_encode($row['verein_short']) . "<img title='" . utf8_encode($row['fitness']) . " " . utf8_encode($row['verletzung']) . "' height='13px' width='auto' src='../img/icons/" . $row['player_status_logo_path'] . "'>";			
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
            SELECT 	
                base.id
                , base.display_name
                , base.position_short
                , base.image_path
                , base.player_status_logo_path
                , base.sidelined_reason
                , base.team_id
                , base.short_code AS team_code
                , CASE WHEN base.team_id = fix.localteam_id THEN team_away.short_code ELSE team_home.short_code END AS gegner 
                , CASE WHEN base.team_id = fix.localteam_id THEN 'H' ELSE 'A' END AS homeaway         
                , base.1_ftsy_match_status
                , fix.round_name
                , fix.fixture_id
                , fix.kickoff_dt
                , fix.kickoff_ts
                , fix.match_status
                , CASE 	
                    WHEN DAYNAME(fix.kickoff_dt) = 'Monday' THEN 'Mo.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Tuesday' THEN 'Di.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Thursday' THEN 'Do.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Friday' THEN 'Fr.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Saturday' THEN 'Sa.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Sunday' THEN 'So.'
                    END AS kickoff_weekday
                , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND AS kickoff_time_trunc
                , MONTH(fix.kickoff_dt) AS kickoff_month
                , DAY(fix.kickoff_dt) AS kickoff_day
                , base.".$ftsy_status_column." AS ftsy_match_status
                , 1 AS bank_zu_aufst_flg
                , CASE WHEN base.id = '".$move_to_bench_player."' THEN 1 ELSE 0 END AS chosen_player_flg
            FROM sm_playerbase_basic_v base
            LEFT JOIN sm_fixtures fix 
                ON ( base.team_id = fix.localteam_id OR base.team_id = fix.visitorteam_id )
                AND fix.round_name = '".$akt_spieltag."'
                AND fix.season_id = '".$akt_season_id."'
            LEFT JOIN sm_teams team_home
                ON fix.localteam_id = team_home.id
            LEFT JOIN sm_teams team_away
                ON fix.visitorteam_id = team_away.id
            WHERE
                ".$ftsy_owner_column." = '".$user_id."'
                AND fix.kickoff_ts > NOW()
                AND ( base.".$ftsy_status_column." != 'NONE' or base.id = '".$move_to_bench_player."' )                   
                AND CASE WHEN '".$move_to_bench_player_pos."' != 'TW' THEN base.position_short != 'TW' ELSE base.position_short IS NOT NULL END 
                AND CASE WHEN '".$move_to_bench_player_pos."' = 'TW' THEN base.position_short = 'TW' ELSE base.position_short IS NOT NULL END 
                AND CASE 
                    WHEN '".$move_to_bench_player_pos."' = 'ST' THEN 
                        CASE 
                            WHEN '".$akt_aufstellung."' in (433, 343) THEN base.position_short = 'ST'
                            WHEN '".$akt_aufstellung."' in (541, 442, 451) THEN base.position_short IS NOT NULL 
                            WHEN '".$akt_aufstellung."' in (532) THEN base.position_short != 'MF'
                            WHEN '".$akt_aufstellung."' in (352) THEN base.position_short != 'AW'
                            END 
                    WHEN '".$move_to_bench_player_pos."' = 'AW' THEN 
                        CASE 
                            WHEN '".$akt_aufstellung."' in (532) THEN base.position_short = 'AW'
                            WHEN '".$akt_aufstellung."' in (442, 343, 352) THEN base.position_short IS NOT NULL 
                            WHEN '".$akt_aufstellung."' in (433) THEN base.position_short != 'MF'
                            WHEN '".$akt_aufstellung."' in (541, 451) THEN base.position_short != 'ST'
                            END
                    WHEN '".$move_to_bench_player_pos."' = 'MF' THEN 
                        CASE 
                            WHEN '".$akt_aufstellung."' in (451, 352) THEN base.position_short = 'MF'
                            WHEN '".$akt_aufstellung."' in (442, 532, 433) THEN base.position_short IS NOT NULL 
                            WHEN '".$akt_aufstellung."' in (343) THEN base.position_short != 'AW'
                            WHEN '".$akt_aufstellung."' in (541) THEN base.position_short != 'ST'
                            END
                    ELSE base.position_short IS NOT NULL
                    END
                ORDER BY 
                    CASE WHEN position_short = 'ST' THEN 4
                        WHEN position_short = 'MF' THEN 3
                        WHEN position_short = 'AW' THEN 2
                        WHEN position_short = 'TW' THEN 1
                        END DESC
            ");

    } else {

        $kader = mysqli_query($con,"	
            SELECT 	
                base.id 
                , base.display_name
                , base.position_short
                , base.image_path
                , base.player_status_logo_path
                , base.sidelined_reason
                , base.team_id
                , base.short_code AS team_code
                , CASE WHEN base.team_id = fix.localteam_id THEN team_away.short_code ELSE team_home.short_code END AS gegner 
                , CASE WHEN base.team_id = fix.localteam_id THEN 'H' ELSE 'A' END AS homeaway         
                , base.1_ftsy_match_status
                , fix.round_name
                , fix.fixture_id
                , fix.kickoff_dt
                , fix.kickoff_ts
                , fix.match_status
                , CASE 	
                    WHEN DAYNAME(fix.kickoff_dt) = 'Monday' THEN 'Mo.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Tuesday' THEN 'Di.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Wednesday' THEN 'Mi.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Thursday' THEN 'Do.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Friday' THEN 'Fr.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Saturday' THEN 'Sa.'
                    WHEN DAYNAME(fix.kickoff_dt) = 'Sunday' THEN 'So.'
                    END AS kickoff_weekday
                , fix.kickoff_time - INTERVAL EXTRACT(SECOND FROM fix.kickoff_time) SECOND AS kickoff_time_trunc
                , MONTH(fix.kickoff_dt) AS kickoff_month
                , DAY(fix.kickoff_dt) AS kickoff_day
                , base.".$ftsy_status_column." AS ftsy_match_status
                , 0 AS bank_zu_aufst_flg
                , CASE WHEN base.id = '".$move_to_bench_player."' THEN 1 ELSE 0 END AS chosen_player_flg
            FROM sm_playerbase_basic_v base
            LEFT JOIN sm_fixtures fix 
                ON ( base.team_id = fix.localteam_id OR base.team_id = fix.visitorteam_id )
                AND fix.round_name = '".$akt_spieltag."'
                AND fix.season_id = '".$akt_season_id."'
            LEFT JOIN sm_teams team_home
                ON fix.localteam_id = team_home.id
            LEFT JOIN sm_teams team_away
                ON fix.visitorteam_id = team_away.id
            WHERE 
                ".$ftsy_owner_column." = '".$user_id."'
                AND fix.kickoff_ts > NOW()										
                AND ( base.".$ftsy_status_column." = 'NONE' or base.id = '".$move_to_bench_player."' )	
                AND CASE WHEN '".$move_to_bench_player_pos."' != 'TW' THEN base.position_short != 'TW' ELSE base.position_short = 'TW' END 
                AND ( CASE WHEN '".$akt_aufstellung."' in (343, 433) AND '".$move_to_bench_player_pos."' != 'ST' THEN base.position_short != 'ST' ELSE base.position_short IS NOT NULL END)
                AND ( CASE WHEN '".$akt_aufstellung."' in (451, 352) AND '".$move_to_bench_player_pos."' != 'MF' THEN base.position_short != 'MF' ELSE base.position_short IS NOT NULL END)
                AND ( CASE WHEN '".$akt_aufstellung."' in (433, 532) AND '".$move_to_bench_player_pos."' = 'MF' THEN base.position_short = 'MF' ELSE base.position_short IS NOT NULL END)
                AND ( CASE WHEN '".$akt_aufstellung."' in (532, 541) AND '".$move_to_bench_player_pos."' != 'AW' THEN base.position_short != 'AW' ELSE base.position_short IS NOT NULL END)
                AND ( CASE WHEN '".$akt_aufstellung."' in (352, 343) AND '".$move_to_bench_player_pos."' = 'AW' THEN base.position_short = 'AW' ELSE base.position_short IS NOT NULL END) 
            ORDER BY 
                CASE 
                    WHEN base.position_short = 'ST' THEN 4
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

    foreach ($data AS $row) {
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
                            echo  $row['position_short'] . " - " . mb_convert_encoding($row['team_code'], 'UTF-8') . "<img title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['sidelined_reason'], 'UTF-8') . "' height='13px' width='auto' src='../img/icons/" . $row['player_status_logo_path'] . "'>";			
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

    foreach ($data AS $row) {
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
                }
            }	

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
                        echo  $row['position_short'] . " - " . mb_convert_encoding($row['team_code'], 'UTF-8') . "<img title='" . mb_convert_encoding($row['fitness'], 'UTF-8') . " " . mb_convert_encoding($row['sidelined_reason'], 'UTF-8') . "' height='13px' width='auto' src='../img/icons/" . $row['player_status_logo_path'] . "'>";			
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