<?php
include("../auth.php");
include('../../secrets/mysql_db_connection.php');

// get needed meta data

$click_1 = intval($_POST['click_1']); 
$click_2 = intval($_POST['click_2']); 

$user = $_SESSION['username'];
$user_id = $_SESSION['user_id'];
$ftsy_owner_column = strval($_SESSION['league_id']) . '_ftsy_owner_id';
$ftsy_status_column = strval($_SESSION['league_id']) . '_ftsy_match_status';
$result = mysqli_query($con, "SELECT spieltag, season_id FROM xa7580_db1.parameter");
$param_row = $result->fetch_object();
$akt_spieltag = $param_row->spieltag;
$akt_season_id = $param_row->season_id;	

// Check if both players are still owned by user
$cnt_clicked_players = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM xa7580_db1.ftsy_player_ownership WHERE player_id IN ('".$click_1."', '".$click_2."') AND ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> cnt;
$cnt_aufstellung = mysqli_query($con, "SELECT COUNT(*) as cnt from xa7580_db1.ftsy_player_ownership where ".$ftsy_status_column." != 'NONE' and ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> cnt;	

if ($cnt_clicked_players == 2 or $cnt_aufstellung < 11){

    // Check if one players is currently in formation and one in bench
    $cnt_bench_players = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM xa7580_db1.ftsy_player_ownership WHERE player_id IN ('".intval($click_1)."', '".intval($click_2)."') AND ".$ftsy_owner_column." = '".$user_id."' AND ".$ftsy_status_column." = 'NONE' ") -> fetch_object() -> cnt;
    $cnt_aufstellung_players = mysqli_query($con, "SELECT COUNT(*) AS cnt FROM xa7580_db1.ftsy_player_ownership WHERE player_id IN ('".intval($click_1)."', '".intval($click_2)."') AND ".$ftsy_owner_column." = '".$user_id."' AND ".$ftsy_status_column." != 'NONE' ") -> fetch_object() -> cnt;

        if ($cnt_aufstellung_players == 1 and $cnt_bench_players == 1){

            // Check fixture kickoffs
            $cnt_valid_antoss = mysqli_query($con, "
                SELECT COUNT(*) AS cnt 
                FROM xa7580_db1.sm_playerbase_basic_v base
                INNER JOIN xa7580_db1.sm_fixtures fix	
                    ON ( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id) 
                    AND fix.round_name = '".$akt_spieltag."'	
                AND fix.season_id = '".$akt_season_id."'
                WHERE 
                    base.id IN ('".$click_1."', '".$click_2."') 
                    AND ".$ftsy_owner_column." = '".$user_id."'
                    AND fix.kickoff_ts > now()
                ") -> fetch_object() -> cnt;

            if ($cnt_valid_antoss == 2){

                // Define which player goes in and which out
                $click_1_status = mysqli_query($con, "SELECT ".$ftsy_status_column." as player_status FROM xa7580_db1.ftsy_player_ownership WHERE player_id = '".$click_1."' AND ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> player_status;

                if ($click_1_status == 'NONE'){
                    $einwechsel_spieler_id = $click_1;
                    $auswechsel_spieler_id = $click_2;
                } else {
                    $einwechsel_spieler_id = $click_2;
                    $auswechsel_spieler_id = $click_1;
                }

                $einwechsel_spieler_pos = mysqli_query($con, "SELECT position_short FROM xa7580_db1.sm_playerbase WHERE id = '".$einwechsel_spieler_id."' ") -> fetch_object() -> position_short;
                $auswechsel_spieler_pos = mysqli_query($con, "SELECT position_short FROM xa7580_db1.sm_playerbase WHERE id = '".$auswechsel_spieler_id."' ") -> fetch_object() -> position_short;

                // Calculate if future formation is valid or not
                $aufstellung_fehlerhaft_flg = 0;

                $cnt_spieler_einwechsel_pos = mysqli_query($con, "  
                    SELECT COUNT(*) as cnt 
                    FROM sm_playerbase base
                    INNER JOIN ftsy_player_ownership owner
                        ON owner.player_id = base.id
                    WHERE 
                        base.position_short = '".$einwechsel_spieler_pos."' 
                        AND ".$ftsy_owner_column." = '".$user_id."'
                        AND ".$ftsy_status_column." != 'NONE' 
                    ") -> fetch_object() -> cnt;

                if ($einwechsel_spieler_pos == 'ST'){
                    if ( $auswechsel_spieler_pos == 'ST' or ($cnt_spieler_einwechsel_pos > 0 and $cnt_spieler_einwechsel_pos < 3) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                } elseif ($einwechsel_spieler_pos == 'AW') {
                    if ( $auswechsel_spieler_pos == 'AW' or ($cnt_spieler_einwechsel_pos > 2 and $cnt_spieler_einwechsel_pos < 5) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                } elseif ($einwechsel_spieler_pos == 'MF') {
                    if ( $auswechsel_spieler_pos == 'MF' or ($cnt_spieler_einwechsel_pos > 2 and $cnt_spieler_einwechsel_pos < 5) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                } elseif ($einwechsel_spieler_pos == 'TW'){
                    if ( $auswechsel_spieler_pos == 'TW' ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                }

                $cnt_spieler_auswechsel_pos = mysqli_query($con, "
                    SELECT COUNT(*) as cnt 
                    FROM sm_playerbase base
                    INNER JOIN ftsy_player_ownership owner
                        ON owner.player_id = base.id
                    WHERE
                        base.position_short = '".$auswechsel_spieler_pos."' 
                        AND ".$ftsy_owner_column." = '".$user_id."'
                        AND ".$ftsy_status_column." != 'NONE' 
                    ") -> fetch_object() -> cnt;                

                if ($auswechsel_spieler_pos == 'ST'){
                    if ( $einwechsel_spieler_pos == 'ST' or ($cnt_spieler_auswechsel_pos > 1 and $cnt_spieler_auswechsel_pos <= 3) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }

                } elseif ($auswechsel_spieler_pos == 'AW') {
                    if ( $einwechsel_spieler_pos == 'AW' or ($cnt_spieler_auswechsel_pos > 3 and $cnt_spieler_auswechsel_pos <= 5) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }

                } elseif ($auswechsel_spieler_pos == 'MF') {
                    if ( $einwechsel_spieler_pos == 'MF' or ($cnt_spieler_auswechsel_pos > 3 and $cnt_spieler_auswechsel_pos <= 5) ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                } elseif ($auswechsel_spieler_pos == 'TW'){
                    if ( $einwechsel_spieler_pos == 'TW' ){
                        $aufstellung_fehlerhaft_flg = 0;
                    } else {
                        $aufstellung_fehlerhaft_flg =  $aufstellung_fehlerhaft_flg + 1;
                    }
                }


                if ($aufstellung_fehlerhaft_flg == 0) {

                    /************************/
                    /* EXECUTE SUBSTITUTION */
                    /************************/

                    // Sub out
                    mysqli_query($con, "
                        UPDATE xa7580_db1.ftsy_player_ownership 
                        SET ".$ftsy_status_column." = 'NONE' 
                        WHERE
                            player_id = '".$auswechsel_spieler_id."' 
                            AND ".$ftsy_owner_column." = '".$user_id."';
                        ");

                    // Sub in
                    $neuer_status_einwechsel = mysqli_query($con, "
                        SELECT
                            h.`status` as new_pos
                        FROM xa7580_db1.help_all_positions h
                        LEFT JOIN xa7580_db1.sm_playerbase_basic_v base
                            ON base.".$ftsy_status_column." = h.`status`
                            AND ".$ftsy_owner_column." = '".$user_id."'
                            AND base.".$ftsy_status_column." != 'NONE'
                        WHERE 
                            h.pos = '".$einwechsel_spieler_pos."'
                            AND base.id is null
                        ORDER BY h.status ASC
                        LIMIT 1
                        ;") -> fetch_object() -> new_pos;

                    mysqli_query($con, "
                        UPDATE xa7580_db1.ftsy_player_ownership 
                        SET ".$ftsy_status_column." = '".$neuer_status_einwechsel."' 
                        WHERE player_id = '".$einwechsel_spieler_id."'; 
                        ");

                    // Update formation

                    $new_formation	= mysqli_query($con, 	"
                        SELECT cnt_aw * 100 + cnt_mf * 10 + cnt_st as formation
                        FROM (   
                            SELECT
                                SUM(CASE WHEN base.position_short = 'ST' THEN 1 ELSE 0 END) as cnt_st
                                , SUM(CASE WHEN base.position_short = 'MF' THEN 1 ELSE 0 END) as cnt_mf
                                , SUM(CASE WHEN base.position_short = 'AW' THEN 1 ELSE 0 END) as cnt_aw
                            FROM xa7580_db1.sm_playerbase_basic_v base
                            WHERE 
                                ".$ftsy_owner_column." = '".$user_id."'
                                AND ".$ftsy_status_column." != 'NONE'
                            ) cnt
                        ;") -> fetch_object() -> formation;

                    $valid_formations = array(343,352,433,442,451,532,541);
            
                    if (in_array($new_formation, $valid_formations)) {
                        mysqli_query($con, "UPDATE xa7580_db1.users_gamedata SET akt_aufstellung = '".strval($new_formation)."' where username = '".$user."' ; ");
                        echo "Spieler eingewechselt!";
                    } else {
                        // Extract current formation and reset user data accordingly
                        $current_formation = mysqli_query($con, "
                            SELECT 
                                CONCAT(
                                    SUM(CASE WHEN pb.position_short = 'AW' THEN 1 ELSE 0 END),
                                    SUM(CASE WHEN pb.position_short = 'MF' THEN 1 ELSE 0 END),
                                    SUM(CASE WHEN pb.position_short = 'ST' THEN 1 ELSE 0 END)
                                ) AS formation
                            FROM ftsy_player_ownership own
                            INNER JOIN sm_playerbase pb 
                                ON own.player_id = pb.id
                            WHERE 
                                own.1_ftsy_owner_id = '".$user_id."'
                                AND own.1_ftsy_match_status != 'NONE'
                                AND pb.position_short != 'TW'
                        ") -> fetch_object() -> formation;

                        mysqli_query($con, "
                            UPDATE xa7580_db1.users_gamedata 
                            SET akt_aufstellung = '".strval($current_formation)."' 
                            WHERE user_id = '".$user_id."'
                        ");

                        echo "Dieser Wechsel hat zu einer ungültigen Formation geführt. Formation wurde zurückgesetzt!";
                    }
                } else {
                    echo "ERROR: Dieser Wechsel führt zu einer ungültigen Aufstellung;";
                }
            } else {
                echo "ERROR: Mindestens einer der Spieler hat schon gespielt / spielt.";
            }
        } elseif ($cnt_aufstellung < 11) {

            $cnt_valid_antoss = mysqli_query($con, "
                SELECT 
                    COUNT(*) AS cnt 
                FROM xa7580_db1.sm_playerbase base
                INNER JOIN xa7580_db1.sm_fixtures fix	
                    ON ( base.current_team_id = fix.localteam_id OR base.current_team_id = fix.visitorteam_id) 
                    AND fix.round_name = '".$akt_spieltag."'
                INNER JOIN xa7580_db1.ftsy_player_ownership owner
                    ON owner.player_id = base.id
                WHERE 
                    id = '".$click_2."'
                    AND ".$ftsy_owner_column." = '".$user_id."'
                    AND fix.kickoff_ts > now()
                ") -> fetch_object() -> cnt;
            
            if ($cnt_valid_antoss == 1){

                $click_2_status = mysqli_query($con, "SELECT ".$ftsy_status_column." as player_status FROM xa7580_db1.ftsy_player_ownership WHERE player_id = '".$click_2."' AND ".$ftsy_owner_column." = '".$user_id."' ") -> fetch_object() -> player_status;

                if ($click_2_status == 'NONE'){
                    
                    $einwechsel_spieler_pos = mysqli_query($con, "SELECT position_short FROM xa7580_db1.sm_playerbase WHERE id = '".$click_2."' ") -> fetch_object() -> position_short;

                    // Sub in
                    $neuer_status_einwechsel = mysqli_query($con, 	"	
                        SELECT 
                            h.`status` AS new_pos
                        FROM xa7580_db1.help_all_positions h
                        LEFT JOIN xa7580_db1.sm_playerbase_basic_v base
                            ON base.".$ftsy_status_column." = h.`status`
                            AND ".$ftsy_owner_column." = '".$user_id."'
                            AND base.".$ftsy_status_column." != 'NONE'
                        WHERE 
                            h.pos = '".$einwechsel_spieler_pos."'
                            AND base.id IS NULL
                        ORDER BY h.status ASC
                        LIMIT 1
                        ;") -> fetch_object() -> new_pos;

                    mysqli_query($con, "
                        UPDATE xa7580_db1.ftsy_player_ownership 
                        SET ".$ftsy_status_column."  = '".$neuer_status_einwechsel."' 
                        WHERE player_id = '".$click_2."' ; ");

                    // Update formation
                    if ($cnt_aufstellung < 11) {

                        $new_formation	= mysqli_query($con, " 	
                            SELECT cnt_aw * 100 + cnt_mf * 10 + cnt_st AS formation
                            FROM ( 	
                                SELECT
                                    SUM(CASE WHEN base.position_short = 'ST' THEN 1 ELSE 0 END) as cnt_st
                                    , SUM(CASE WHEN base.position_short = 'MF' THEN 1 ELSE 0 END) as cnt_mf
                                    , SUM(CASE WHEN base.position_short = 'AW' THEN 1 ELSE 0 END) as cnt_aw
                                FROM xa7580_db1.sm_playerbase_basic_v base
                                WHERE 
                                    ".$ftsy_owner_column." = '".$user_id."'
                                    AND ".$ftsy_status_column." != 'NONE'
                                ) cnt
                            ") -> fetch_object() -> formation;

                        mysqli_query($con, "
                            UPDATE xa7580_db1.users_gamedata 
                            SET akt_aufstellung = '".strval($new_formation)."' 
                            WHERE username = '".$user."' ; 
                            ");
                    }

                echo "Spieler eingewechselt!";

                } else {
                    echo "ERROR";
                }
            } else {
                echo "ERROR: Mindestens einer der Spieler hat schon gespielt / spielt.";
            }
        } else {
             echo "ERROR: Beide Spieler sind auf deiner Bank oder Aufstellung.";  		
        }
    } else {
        echo "ERROR: Die beiden Spieler befinden sich nicht in deinem Kader.";
    }
?>