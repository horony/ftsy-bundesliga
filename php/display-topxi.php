<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");

// Get meta-data from session
$active_user_id = $_SESSION['user_id'];

// Get data from js call
$topic = $_GET['topic'];
$var1 = $_GET['q1'];
$var2 = $_GET['q2'];
$var3 = $_GET['q3'];

/**************************/
/* GET DATA FROM DATABASE */
/**************************/

$array_team_info = NULL;

if ($topic == 'FABU'){

    $footer_message = '* Berechnet auf den tatsächlichen Fantasy Scores der Saisons und Spieltage ab Saison 202/2021.';

    if($var1 == 'OVR'){
        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_fabu_ovr 
            WHERE 
                topxi_lvl = 'OVR'
            ");
        
        $sql_team_info = mysqli_query($con,"SELECT 'FANTASY BUNDESLIGA - LEGENDS' AS team_name, SUM(ftsy_score) AS team_score FROM topxi_fabu_ovr WHERE topxi_lvl = 'OVR' GROUP BY 'FANTASY BUNDESLIGA - LEGENDS'");

    } elseif ($var1 == 'SZN'){
        $sql_kader = mysqli_query($con,"
            SELECT 
                xi.player_id
                , xi.player_name
                , xi.player_image_path
                , xi.buli_team_logo_path
                , xi.position_short
                , ROUND(xi.ftsy_score,0) AS ftsy_score
                , CONCAT('⌀ ', CONVERT(ROUND(xi.ftsy_score_avg, 1),CHAR), ' in ', CONVERT(xi.appearance_cnt,CHAR), ' Spielen') AS descr_1				
                , CASE WHEN xi.user_team_code = -1 THEN '' ELSE CONCAT('>> ', xi.user_team_code, ' ', CASE WHEN mst.player_id IS NOT NULL AND pok.winner_user_id IS NOT NULL THEN '&#129351;&#127942;' WHEN mst.player_id IS NOT NULL THEN '&#129351;' WHEN pok.winner_user_id IS NOT NULL THEN '&#127942;' ELSE '' END) END AS descr_2 
            FROM topxi_fabu_ovr xi
            LEFT JOIN ftsy_meister_v mst
                ON 	xi.season_id = mst.season_id
                AND xi.user_id = mst.player_id
            LEFT JOIN ftsy_pokalsieger_v pok
                ON 	xi.season_id = pok.season_id
                AND xi.user_id = pok.winner_user_id
            WHERE 
                xi.topxi_lvl = 'SZN' 
                AND xi.season_id = '$var2'
            ");

        $sql_team_info = mysqli_query($con,"SELECT CONCAT('FANTASY BUNDESLIGA - ELF DER SAISON ', season_name) AS team_name, SUM(ftsy_score) AS team_score FROM topxi_fabu_ovr WHERE topxi_lvl = 'SZN' AND season_id = '$var2' GROUP BY CONCAT('FANTASY BUNDESLIGA - ELF DER SAISON ', season_name)");

    } elseif ($var1 == 'RND') {
        $sql_kader = mysqli_query($con,"
            WITH top_stat AS (
                SELECT 
                    season_id
                    , round_name
                    , player_id
                    , ftsy_score
                    , @var_max_val:= GREATEST(goals_minus_pen_ftsy, pen_scored_ftsy, assists_ftsy, big_chances_created_ftsy, key_passes_ftsy, passes_complete_ftsy, crosses_complete_ftsy, shots_on_goal_ftsy, pen_saved_ftsy, duels_won_ftsy, dribbles_success_ftsy, interceptions_ftsy, clearances_ftsy, clearances_offline_ftsy, tackles_ftsy, inside_box_saves_ftsy, outside_box_saves_ftsy) AS max_value
                    , CASE @var_max_val WHEN goals_minus_pen_ftsy THEN CONCAT(goals_minus_pen_stat, ' Tore aus dem Spiel') WHEN pen_scored_ftsy THEN CONCAT(pen_scored_stat, ' Elfmeter-Tore') WHEN assists_ftsy THEN CONCAT(assists_stat, ' Assists') WHEN big_chances_created_ftsy THEN CONCAT(big_chances_created_stat, ' Großchancen herausgespielt') WHEN key_passes_ftsy THEN CONCAT(key_passes_stat, ' Key-Pässe') WHEN shots_on_goal_ftsy THEN CONCAT(shots_on_goal_stat, ' Torschüsse') WHEN duels_won_ftsy THEN CONCAT(duels_won_stat, ' Duelle gewonnen') WHEN dribbles_success_ftsy THEN CONCAT(dribbles_success_stat, ' erfolgreiche Dribblings') WHEN passes_complete_ftsy THEN CONCAT(passes_complete_stat, ' erfolgreiche Pässe') WHEN crosses_complete_ftsy THEN CONCAT(crosses_complete_stat, ' erfolgreiche Flanken') WHEN interceptions_ftsy THEN CONCAT(interceptions_stat, ' Bälle abgefangen') WHEN tackles_ftsy THEN CONCAT(tackles_stat, ' Tacklings') WHEN clearances_ftsy THEN CONCAT(clearances_stat, ' geklärte Bälle') WHEN clearances_offline_ftsy THEN CONCAT(clearances_offline_stat, ' auf Linie gerettet') WHEN pen_saved_ftsy THEN CONCAT(pen_saved_stat, ' Elfmeter gehalten') WHEN inside_box_saves_ftsy THEN CONCAT(inside_box_saves_stat, ' Schüsse im 16er gehalten') WHEN outside_box_saves_ftsy THEN CONCAT(outside_box_saves_stat, ' Fernschüsse gehalten') END AS top_stat
                FROM `ftsy_scoring_hist`
                WHERE season_id >= 21795 AND ftsy_score > 15
            )	
            SELECT 
                xi.player_id
                , xi.player_name
                , xi.player_image_path
                , xi.buli_team_logo_path
                , xi.position_short
                , ROUND(xi.ftsy_score,1) AS ftsy_score
                , top_stat.top_stat AS descr_1
                , CASE WHEN xi.user_team_code = -1 THEN '' ELSE CONCAT('>> ', xi.user_team_code, ' ', CASE WHEN mst.player_id IS NOT NULL AND pok.winner_user_id IS NOT NULL THEN '&#129351;&#127942;' WHEN mst.player_id IS NOT NULL THEN '&#129351;' WHEN pok.winner_user_id IS NOT NULL THEN '&#127942;' ELSE '' END) END AS descr_2 
            FROM topxi_fabu_ovr xi
            LEFT JOIN ftsy_meister_v mst
                ON 	xi.season_id = mst.season_id
                        AND xi.user_id = mst.player_id
            LEFT JOIN ftsy_pokalsieger_v pok
                ON 	xi.season_id = pok.season_id
                        AND xi.user_id = pok.winner_user_id     
            LEFT JOIN top_stat 
                ON 	top_stat.player_id = xi.player_id
                        AND top_stat.round_name = xi.round_name
                        AND top_stat.season_id = xi.season_id
            WHERE 
                xi.topxi_lvl = 'RND' 
                AND xi.season_id = '$var2' 
                AND xi.round_name = '$var3'
            ");				
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(CONCAT(CONCAT('FANTASY BUNDESLIGA - ELF DES SPIELTAGS ', round_name), ' '), season_name) AS team_name, SUM(ftsy_score) AS team_score FROM topxi_fabu_ovr WHERE topxi_lvl = 'RND' AND season_id = '$var2' AND round_name = '$var3' GROUP BY CONCAT(CONCAT(CONCAT('FANTASY BUNDESLIGA - ELF DES SPIELTAGS ', round_name), ' '), season_name) ");
    }

} elseif ($topic == 'USER'){

    $footer_message = '* Berechnet auf den tatsächlichen Fantasy Scores der Saisons und Spieltage ab Saison 2020/2021. Umfasst alle Liga- und Pokalspielage (auch Bye-Weeks im Pokal). Umfasst sowohl aufgestellte Spieler und Spieler auf der Bank.';

    if($var1 == 'OVR' and $var2 != 0){
        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_ftsy_team 
            WHERE 
                topxi_lvl = 'OVR' 
                AND user_id = '$var2'

            ");				

        $sql_team_info = mysqli_query($con,"SELECT CONCAT(user_team_name, ' - LEGENDS') AS team_name, SUM(ftsy_score) AS team_score FROM topxi_ftsy_team WHERE topxi_lvl = 'OVR' AND user_id = '$var2' GROUP BY CONCAT(user_team_name, ' - LEGENDS')");
        $sql_achievements = mysqli_query($con, "
            SELECT 	
                tab.user_id
                , u.username
                , u.teamname
                , u.team_code
                , tab.anz_saisons
                , CONCAT(tab.anz_siege, '-', tab.anz_unentschieden, '-', tab.anz_niederlagen+tab.anz_trost) AS bilanz
                , tab.anz_siege / tab.anz_spiele AS win_rate
                , GROUP_CONCAT(m.season_name) AS list_meisterschaften
                , GROUP_CONCAT(p.season_name) AS list_pokalsiege
            FROM ftsy_ewige_tabelle_v tab
            INNER JOIN users u
                ON u.id = tab.user_id
            LEFT JOIN ftsy_meister_v m 
                ON tab.user_id = m.player_id
            LEFT JOIN ftsy_pokalsieger_v p 
                ON tab.user_id = p.winner_user_id
            WHERE 
                tab.user_id = '$var2'
            GROUP BY 
                tab.user_id
                , u.username
                , u.teamname
                , u.team_code
                , tab.anz_saisons
                , CONCAT(tab.anz_siege, '-', tab.anz_unentschieden, '-', tab.anz_niederlagen+tab.anz_trost)
                , tab.anz_siege / tab.anz_spiele 
            ;");
        $array_achievements = mysqli_fetch_array($sql_achievements);
            
    } elseif ($var1 == 'SZN' and $var2 != 0){

        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_ftsy_team 
            WHERE 
                topxi_lvl = 'SZN' 
                AND user_id = '$var2' 
                AND season_id = '$var3'
            ");				
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(CONCAT(user_team_name, ' - ELF DER SAISON '), season_name) AS team_name, SUM(ftsy_score) AS team_score FROM topxi_ftsy_team WHERE topxi_lvl = 'SZN' AND user_id = '$var2' AND season_id = '$var3' GROUP BY CONCAT(CONCAT(user_team_name, ' - ELF DER SAISON '), season_name)");
        $sql_achievements = mysqli_query($con, "
            SELECT 
                tab.player_id
                , tab.season_id
                , usr.username
                , tab.rang AS platzierung
                , CONCAT(tab.siege, '-', tab.unentschieden, '-', tab.niederlagen+tab.trost) AS bilanz
                , tab.siege / (tab.siege+tab.unentschieden+tab.niederlagen+tab.trost) AS win_rate
                , CASE WHEN m.season_name IS NOT NULL THEN 1 ELSE 0 END AS meister_flg
                , CASE WHEN p.season_name IS NOT NULL THEN 1 ELSE 0 END AS pokalsieger_flg
            FROM ftsy_tabelle_2020 tab
            LEFT JOIN users usr
                ON tab.player_id = usr.id
            LEFT JOIN ftsy_meister_v m 
                ON tab.season_id = m.season_id
                AND tab.player_id = m.player_id
            LEFT JOIN ftsy_pokalsieger_v p
                ON tab.season_id = p.season_id
                AND tab.player_id = p.winner_user_id
            WHERE 
                spieltag = (SELECT current_round_name FROM sm_seasons WHERE season_id = '$var3') 
                AND tab.player_id = '$var2' 
                AND tab.season_id = '$var3'
            ");
        $array_achievements = mysqli_fetch_array($sql_achievements);
        
    } else {

        /* Default */
        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_ftsy_team 
            WHERE topxi_lvl = 'OVR' AND user_id = '$active_user_id'
            ");				
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(user_team_name, ' - LEGENDS') AS team_name, SUM(ftsy_score) AS team_score FROM topxi_ftsy_team WHERE topxi_lvl = 'OVR' AND user_id = '$active_user_id' GROUP BY CONCAT(user_team_name, ' - LEGENDS')");		
        $sql_achievements = mysqli_query($con, "
            SELECT 	
                tab.user_id
                , u.username
                , u.teamname
                , u.team_code
                , tab.anz_saisons
                , CONCAT(tab.anz_siege, '-', tab.anz_unentschieden, '-', tab.anz_niederlagen+tab.anz_trost) AS bilanz
                , tab.anz_siege / tab.anz_spiele AS win_rate
                , GROUP_CONCAT(m.season_name) AS list_meisterschaften
                , GROUP_CONCAT(p.season_name) AS list_pokalsiege
            FROM ftsy_ewige_tabelle_v tab
            INNER JOIN users u
                ON u.id = tab.user_id
            LEFT JOIN ftsy_meister_v m 
                ON tab.user_id = m.player_id
            LEFT JOIN ftsy_pokalsieger_v p 
                ON tab.user_id = p.winner_user_id
            WHERE 
                tab.user_id = '$active_user_id'
            GROUP BY 
                tab.user_id
                , u.username
                , u.teamname
                , u.team_code
                , tab.anz_saisons
                , CONCAT(tab.anz_siege, '-', tab.anz_unentschieden, '-', tab.anz_niederlagen+tab.anz_trost)
                , tab.anz_siege / tab.anz_spiele
            ;");

        $array_achievements = mysqli_fetch_array($sql_achievements);
    }

} elseif ($topic == 'BULI'){

    $footer_message = '* Berechnet auf den tatsächlichen Fantasy Scores der Saisons und Spieltage ab Saison 2020/2021.';

    if($var1 == 'OVR' and $var2 != 0){
        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_buli_team 
            WHERE 
                topxi_lvl = 'OVR' 
                AND buli_team_id = '$var2'
            ");		
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(buli_team_name, ' - LEGENDS') AS team_name, SUM(ftsy_score) AS team_score FROM topxi_buli_team WHERE topxi_lvl = 'OVR' AND buli_team_id = '$var2' GROUP BY CONCAT(buli_team_name, ' - LEGENDS')");
            
    } elseif ($var1 == 'SZN'){

        $sql_kader = mysqli_query($con,"
            SELECT 
                xi.player_id
                , xi.player_name
                , xi.player_image_path
                , xi.buli_team_logo_path
                , xi.position_short
                , ROUND(xi.ftsy_score,0) AS ftsy_score
                , CONCAT('⌀ ', CONVERT(ROUND(xi.ftsy_score_avg, 1),CHAR), ' in ', CONVERT(xi.appearance_cnt,CHAR), ' Spielen') AS descr_1				
                , CASE WHEN xi.user_team_code = -1 THEN null ELSE CONCAT('>> ', xi.user_team_code, ' ', CASE WHEN mst.player_id IS NOT NULL AND pok.winner_user_id IS NOT NULL THEN '&#129351;&#127942;' WHEN mst.player_id IS NOT NULL THEN '&#129351;' WHEN pok.winner_user_id IS NOT NULL THEN '&#127942;' ELSE '' END) END AS descr_2 
            FROM topxi_buli_team xi
            LEFT JOIN ftsy_meister_v mst
                ON 	xi.season_id = mst.season_id
                AND xi.user_id = mst.player_id
            LEFT JOIN ftsy_pokalsieger_v pok
                ON 	xi.season_id = pok.season_id
                AND xi.user_id = pok.winner_user_id
            WHERE 
                xi.topxi_lvl = 'SZN' 
                AND xi.buli_team_id = '$var2' 
                AND xi.season_id = '$var3'
            ");

        
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(CONCAT(buli_team_name, ' - ELF DER SAISON '), season_name) AS team_name, SUM(ftsy_score) AS team_score FROM topxi_buli_team WHERE topxi_lvl = 'SZN' AND buli_team_id = '$var2' AND season_id = '$var3' GROUP BY CONCAT(CONCAT(buli_team_name, ' - ELF DER SAISON '), season_name)");

    } else {
    
        $sql_kader = mysqli_query($con,"
            SELECT 	
                player_id
                , player_name
                , player_image_path
                , buli_team_logo_path
                , position_short
                , ROUND(ftsy_score,0) AS ftsy_score
                , CONCAT('⌀', CONVERT(ROUND(ftsy_score_avg, 1),CHAR), ' Punkte ') AS descr_1
                , CONCAT(CONVERT(appearance_cnt,CHAR), ' Spiele (', CONVERT(YEAR(appearance_min_dt),CHAR),'-',CONVERT(YEAR(appearance_max_dt),CHAR), ')') AS descr_2
            FROM topxi_buli_team 
            WHERE 
                topxi_lvl = 'OVR' 
                AND buli_team_code = 'SGE'
            ");
        $sql_team_info = mysqli_query($con,"SELECT CONCAT(buli_team_name, ' - LEGENDS') AS team_name, SUM(ftsy_score) AS team_score FROM topxi_buli_team WHERE topxi_lvl = 'OVR' AND buli_team_code = 'B04' GROUP BY CONCAT(buli_team_name, ' - LEGENDS')");		
    
    }

}

$array_team_info = mysqli_fetch_array($sql_team_info);

/********************/
/* DISPLAY THE DATA */
/********************/

/* Set threshold values for coloring of fantasy score */
if ($var1 == 'SZN' or $var1 == 'OVR'){

    /* Get current round and season from database */
    $result = mysqli_query($con, "SELECT season_id, spieltag FROM xa7580_db1.parameter");
    $param_row = $result->fetch_object();
    $current_season_id = $param_row->season_id;
    $current_round_name = $param_row->spieltag;

    if ($var1 == 'SZN'){
        $color_start_val = 300;
        $color_steps = 0.15;
        if ($var2 == $current_season_id or $var3 == $current_season_id){
            $color_start_val = ($color_start_val/28)*($current_round_name-1);
        }
    } elseif ($var1 == 'OVR'){
        $color_start_val = 700;
        $color_steps = 0.15;
        if ($var2 == $current_season_id or $var3 == $current_season_id){
            $color_start_val = ($color_start_val/28)*($current_round_name-1);
        }
    }
}

/* Main content: Top-XI */
while ($row = mysqli_fetch_array($sql_kader)) {
  $data[] = $row;
}

/* Header */
echo "<div class='header_wrapper'>";
    echo "<div class='aufstellung_headline'>";
        echo "<div>" . mb_convert_encoding(strtoupper($array_team_info['team_name']), 'UTF-8') . "</div>";
        echo "<div class='match_score'>" . number_format($array_team_info['team_score'], 0, '.', ',') . " Punkte</div>";
    echo "</div>";

    /* Sub Header */
    /* Only active in fantasy team by user view */

    if ($topic == 'USER'){

        echo "<div class='sub_header'>";
            if ($var1 == 'SZN' and $var2 != 0){
                /* Define Icons */
                $icon_league = '';

                if ($array_achievements['meister_flg'] == 1){
                    $icon_league = '&#129351;';
                } elseif ($array_achievements['platzierung'] == 2 and $array_achievements['season_id'] != $current_season_id){
                    $icon_league = '&#129352;';					
                } elseif ($array_achievements['platzierung'] == 3 and $array_achievements['season_id'] != $current_season_id){
                    $icon_league = '&#129353;';					
                } elseif ($array_achievements['platzierung'] == 10 and $array_achievements['season_id'] != $current_season_id){
                    $icon_league = '&#127982;	';					
                }
                echo "Manager: " . mb_convert_encoding($array_achievements['username'], 'UTF-8') . " • ";
                echo "Liga: #" . $array_achievements['platzierung'] . $icon_league . " • ";
                if ($array_achievements['pokalsieger_flg'] == 1){ echo "Pokalsieger &#127942; • ";}
                echo "Win-Rate: " . ROUND($array_achievements['win_rate']*100,1) . "% • ";							
                echo "Bilanz: " . $array_achievements['bilanz'];   
            } else {
                echo "Manager: " . mb_convert_encoding($array_achievements['username'], 'UTF-8') . " • ";
                if (!empty($array_achievements['list_meisterschaften'] )) { echo "&#129351; " . $array_achievements['list_meisterschaften'] . " • "; }
                if (!empty($array_achievements['list_pokalsiege'] )) { echo "&#127942; " . $array_achievements['list_pokalsiege'] . " • "; }
                echo "Win-Rate: " . ROUND($array_achievements['win_rate']*100,1) . "% • ";							
                echo "Bilanz: " . $array_achievements['bilanz'] . " (" . $array_achievements['anz_saisons'] . " Saisons)";
            }
        echo "</div>";
    }
echo "</div>";

/* Formation */
echo "<div class='striped_background'>";
    echo "<div class='aufstellung_wrapper'>";

    /* Loop positions */
    $array = array("st", "mf", "aw", "tw");

    foreach($array AS $value){
        echo "<div class='aufstellung_". $value . "'>";
            /* Loop players in position */
            foreach ($data AS $row) {
                /* Display player */	
                if ($row['position_short'] == strtoupper($value)){  
                    echo "<div class='player_card_item' style='cursor: pointer;' data-id='" . $row['player_id'] . "' onclick='#'>";
                    /* Player image */
                    echo "<div class='hide_mobile' style='position: relative; left: 0; top: 0;'>";
                        echo "<img style='position: relative; left: 0; top: 0; border-radius: 5px;' height='45px' width='auto' src='" . $row['player_image_path'] . "'>";
                        echo "<img style='position: absolute; right: 0.2px; bottom: 0.2px;' height='15px' width='auto'' src='" . $row['buli_team_logo_path'] . "'>";
                    echo "</div>";		
                    /* Player text */
                    echo "<div class='player_card_text'>";
                        echo "<div class='player_card_name'>";
                            echo "<a href='#'>" . mb_convert_encoding($row['player_name'], 'UTF-8') . "</a>";
                        echo "</div>";
                        echo "<div class='player_card_detail'>";
                            echo "<div class='player_card_detail_line_1'>";
                                echo mb_convert_encoding($row['descr_1'], 'UTF-8');
                            echo "</div>";	
                            echo "<div class='player_card_detail_line_2'>";
                                echo $row['descr_2'];
                            echo "</div>";	
                        echo "</div>";
                    echo "</div>";	
                    /* Player ftsy score */
                    $color_score = (float)$row['ftsy_score'];

                    /* Define colors for ftsy points */
                    if ($var1 == 'RND'){

                        if ($color_score <= -5) {
                            $grade_color = '#ff0000';
                        } elseif ($color_score > -5 and $color_score <= 0){
                            $grade_color = '#fb4200';
                        } elseif ($color_score > 0 and $color_score < 5){
                            $grade_color = '#f56100';
                        } elseif ($color_score > 5 and $color_score <= 10){
                            $grade_color = '#d4a300';
                        } elseif ($color_score > 10 and $color_score <= 15){
                            $grade_color = '#9ed500';
                        } elseif ($color_score > 15 and $color_score <= 20){
                            $grade_color = '#6FB617';
                        } elseif ($color_score > 20 and $color_score <= 25){
                            $grade_color = '#29A71E';
                        } elseif ($color_score > 25 and $color_score <= 30){
                            $grade_color = '#06A022';
                        } elseif ($color_score > 30 ){
                            $grade_color = '#06a06f';
                        } else {
                            $grade_color = '#dddddd';
                        }
                    } else {
                        if ($color_score <= ($color_start_val * pow((1-$color_steps),3))) {
                            $grade_color = '#ff0000';
                        } elseif ($color_score > ($color_start_val * pow((1-$color_steps),3)) and $color_score <= ($color_start_val * pow((1-$color_steps),2))){
                            $grade_color = '#fb4200';
                        } elseif ($color_score > ($color_start_val * pow((1-$color_steps),2)) and $color_score < $color_start_val * (1-$color_steps)){
                            $grade_color = '#f56100';
                        } elseif ($color_score > $color_start_val * (1-$color_steps) and $color_score <= ($color_start_val * (1+$color_steps))){
                            $grade_color = '#d4a300'; #mw
                        } elseif ($color_score > ($color_start_val * (1+$color_steps)) and $color_score <= ($color_start_val * pow((1+$color_steps),2)) ){
                            $grade_color = '#9ed500'; 
                        } elseif ($color_score > ($color_start_val * pow((1+$color_steps),2)) and $color_score <= ($color_start_val * pow((1+$color_steps),3)) ){
                            $grade_color = '#6FB617';
                        } elseif ($color_score > ($color_start_val * pow((1+$color_steps),3)) and $color_score <= ($color_start_val * pow((1+$color_steps),4)) ){
                            $grade_color = '#29A71E';
                        } elseif ($color_score > ($color_start_val * pow((1+$color_steps),4)) and $color_score <= ($color_start_val * pow((1+$color_steps),5)) ){
                            $grade_color = '#06A022';
                        } elseif ($color_score > ($color_start_val * pow((1+$color_steps),5)) ){
                            $grade_color = '#06a06f';
                        } else {
                            $grade_color = '#dddddd';
                        }						
                    }
                    echo "<div class='player_card_score' style='background-color: ".$grade_color." '>" . $row['ftsy_score'];
                    echo "</div>";
                echo "</div>";
                }
            }
        echo "</div>"; 
    }
    echo "</div>";
echo "</div>";

/* Footer */
echo "<div class='aufstellung_footer'>";
    echo mb_convert_encoding($footer_message, 'UTF-8');
echo "</div";
?> 