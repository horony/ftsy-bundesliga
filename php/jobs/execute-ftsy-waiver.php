<?php
/********************************************/
/* Step 1: Search for waiver requests       */
/* Step 2: Execute valid waiver request     */
/* Step 3: Recalculate waiver order         */
/********************************************/

// establish and check database connection
include('../../secrets/mysql_db_connection.php');

// get the 2 waiver dates from database
$waiver_1 = mysqli_query($con, "SELECT DATE(waiver_date_1) as waiver_1 FROM xa7580_db1.parameter") -> fetch_object() -> waiver_1;
$waiver_2 = mysqli_query($con, "SELECT DATE(waiver_date_2) as waiver_2 FROM xa7580_db1.parameter") -> fetch_object() -> waiver_2;

// check if execution date of script matches waiver date
if ( (date("Y-m-d") == $waiver_1) OR (date("Y-m-d") == $waiver_2 ) ) { 
    
    echo nl2br ("Waiver date approved \n");

    /**************************************/
    /* Step 1: Search for waiver requests */
    /**************************************/

    // set all waiver flags to 0
    mysqli_query($con, "UPDATE xa7580_db1.users_gamedata SET waiver_safe_flg = 0");
    mysqli_query($con, "UPDATE xa7580_db1.users_gamedata SET waiver_ex_flg = 0");

    // set waiver_sage_flg to 1 if no waiver request exist for a user
    mysqli_query($con, "UPDATE xa7580_db1.users_gamedata user 
                        LEFT JOIN xa7580_db1.waiver waiv 
                            ON user.user_id = waiv.owner 
                        SET user.waiver_safe_flg = 1 
                        WHERE waiv.owner IS NULL");

    // count total waiver requests
    $anzahl_anfragen = mysqli_query($con, "SELECT COUNT(ID) AS anzahl_anfragen FROM xa7580_db1.waiver") -> fetch_object() -> anzahl_anfragen;
    $anzahl_anfragen_start = $anzahl_anfragen;
    $last_user = 'leer';
    $cnt_waiver = 0;

    echo "Waiver requests found: " . $anzahl_anfragen . nl2br("\n");

    /*****************************************/
    /* Step 1: Execute valid waiver request  */
    /*****************************************/

    // set all WVR to FA
    mysqli_query ($con, "UPDATE xa7580_db1.ftsy_player_ownership SET 1_ftsy_owner_type = 'FA' WHERE 1_ftsy_owner_type = 'WVR'");
    mysqli_query ($con, "UPDATE xa7580_db1.ftsy_player_ownership SET 2_ftsy_owner_type = 'FA' WHERE 2_ftsy_owner_type = 'WVR'");

    // create backup of waiver table
    mysqli_query($con, "DROP TABLE xa7580_db1.waiver_safe");
    mysqli_query($con, "CREATE TABLE xa7580_db1.waiver_safe AS SELECT * FROM xa7580_db1.waiver");

    // start looping waiver requests
    while ($anzahl_anfragen > 0) {

        // search for user with waiver request
        $user = mysqli_query($con, "SELECT user_id FROM xa7580_db1.users_gamedata user INNER JOIN xa7580_db1.waiver waiv ON user.user_id = waiv.owner WHERE user.waiver_safe_flg = 0 ORDER BY user.waiver_position ASC LIMIT 1") -> fetch_object() -> user_id;

        // if no user was found end the loop by setting anzahl_anfragen to 0
        if ($user == null){ 
            $anzahl_anfragen = 0;
            mysqli_query ($con, "UPDATE xa7580_db1.users_gamedata SET waiver_safe_flg = 1");

        // if a user was found evaluate his waiver request
        } else {

            // get relevant variables
            $player_add_id = mysqli_query ($con, "SELECT waiver_add_id FROM xa7580_db1.waiver WHERE owner = '".$user."' ORDER BY prio ASC LIMIT 1") -> fetch_object() -> waiver_add_id;
            $player_drop_id = mysqli_query ($con, "SELECT waiver_drop_id FROM xa7580_db1.waiver WHERE owner = '".$user."' ORDER BY prio ASC LIMIT 1") -> fetch_object() -> waiver_drop_id;
            
            $player_add_owner = mysqli_query ($con, "SELECT 1_ftsy_owner_type as Besitzer FROM xa7580_db1.ftsy_player_ownership WHERE player_id = '".$player_add_id."'") -> fetch_object() -> Besitzer;
            $player_drop_owner = mysqli_query ($con, "SELECT 1_ftsy_owner_id as Besitzer FROM xa7580_db1.ftsy_player_ownership WHERE player_id = '".$player_drop_id."'") -> fetch_object() -> Besitzer;

            $player_add_team = mysqli_query ($con, "SELECT usr.teamname as teamname 
                                                    FROM xa7580_db1.ftsy_player_ownership base 
                                                    INNER JOIN xa7580_db1.users usr 
                                                        ON usr.id = base.1_ftsy_owner_id 
                                                    WHERE base.player_id = '".$player_drop_id."'") -> fetch_object() -> teamname;

            echo "Check waiver request by " . $player_add_owner . ": Add player_id " . $player_add_id . " | Drop player_id " . $player_drop_id .  nl2br("\n");

            // check if player is still availab 
            if ($player_drop_owner == $user AND $player_add_owner == 'FA'){

                echo nl2br("Request approved\n");

                // execute waiver request
                mysqli_query ($con, "UPDATE xa7580_db1.ftsy_player_ownership SET 1_ftsy_owner_type = 'WVR', 1_ftsy_owner_id = NULL, 1_ftsy_match_status = NULL WHERE player_id = '".$player_drop_id."'");
                mysqli_query ($con, "UPDATE xa7580_db1.ftsy_player_ownership SET 1_ftsy_owner_type = 'USR', 1_ftsy_owner_id = '".$user."',  1_ftsy_match_status = 'NONE' WHERE player_id = '".$player_add_id."'");

                // create news articel for website
                $player_add_name = mysqli_query ($con, "SELECT display_name FROM xa7580_db1.sm_playerbase WHERE  id = '".$player_add_id."' ") -> fetch_object() -> display_name;
                $player_add_buli_team = mysqli_query ($con, "SELECT name FROM xa7580_db1.sm_playerbase_basic_v WHERE  id = '".$player_add_id."' ") -> fetch_object() -> name;
                $player_drop_name = mysqli_query ($con, "SELECT display_name FROM xa7580_db1.sm_playerbase WHERE  id = '".$player_drop_id."' ") -> fetch_object() -> display_name;
                $player_drop_buli_team = mysqli_query ($con, "SELECT name FROM xa7580_db1.sm_playerbase_basic_v WHERE  id = '".$player_drop_id."' ") -> fetch_object() -> name;

                $headline = 'Waiver-Anfrage durchgeführt';

$story=<<<EOT
$player_add_team verpflichet <b>$player_add_name</b> ($player_add_buli_team) und entlässt <b>$player_drop_name</b> ($player_drop_buli_team).
EOT;
            
                mysqli_query($con, "INSERT INTO xa7580_db1.news(name, headline, story, timestamp, add_id, drop_id, add_besitzer, drop_besitzer, type) VALUES('System', '".utf8_decode($headline)."', '".utf8_decode($story)."', NOW(), '".$player_add_id."', '".$player_drop_id."', 'Waiver', '".$user."', 'waiver_wire')");
            
                // Delete waivers which are now redundant
                mysqli_query ($con, "DELETE FROM xa7580_db1.waiver WHERE waiver_add_id = '".$player_add_id."' OR waiver_drop_id = '".$player_drop_id."'");
                
                // Delete trades which are now redundant
                mysqli_query ($con, "DELETE FROM xa7580_db1.trade WHERE ini_trade_id = '".$player_add_id."' OR rec_trade_id = '".$player_add_id."' OR ini_trade_id = '".$player_drop_id."' OR rec_trade_id = '".$player_drop_id."' ");

                // Calculate new waiver order in current execution
                if ($user != $last_user){   $akt_waiver = mysqli_query($con, "SELECT waiver_position FROM users_gamedata WHERE user_id = '".$user."'")->fetch_object()->waiver_position;
                                            mysqli_query ($con, "UPDATE xa7580_db1.users_gamedata SET waiver_position = waiver_position-1 WHERE user_id != '".$user."' AND waiver_position > '".$akt_waiver."' ");
                                            mysqli_query ($con, "UPDATE xa7580_db1.users_gamedata SET waiver_position = 10, waiver_ex_flg = 1 WHERE user_id = '".$user."'"); 
                                            
                    }
                
                $anzahl_anfragen = mysqli_query($con, "SELECT COUNT(ID) AS anzahl_anfragen FROM xa7580_db1.waiver") -> fetch_object() -> anzahl_anfragen;
                $last_user = $user;

            } else {
                echo nl2br("Request denied\n");
                // Delete denied waiver (usually should not happen)
                $waiver_id = mysqli_query ($con, "SELECT ID FROM xa7580_db1.waiver WHERE owner = '$user' ORDER BY prio ASC LIMIT 1") -> fetch_object() -> ID;
                mysqli_query ($con, "DELETE FROM xa7580_db1.waiver WHERE ID = '$waiver_id' AND owner = '$user'"); 
                $anzahl_anfragen = mysqli_query($con, "SELECT COUNT(ID) AS anzahl_anfragen FROM xa7580_db1.waiver") -> fetch_object() -> anzahl_anfragen;

                }
        }
    }

    /* Step 3: Recalculate waiver order */
  
    // Update waiver flags
    mysqli_query($con, "UPDATE xa7580_db1.users_gamedata SET waiver_safe_flg = 1 WHERE waiver_ex_flg = 0");

    // Calculate new draft order
    mysqli_query($con, "UPDATE  xa7580_db1.users_gamedata game
                        SET waiver_position = 
                            (SELECT new_ranking FROM (
                                SELECT username, waiver_safe_flg, waiver_position, @curRank := @curRank + 1 AS new_ranking
                                FROM xa7580_db1.users_gamedata, (SELECT @curRank := 0) r
                                ORDER BY waiver_safe_flg DESC, waiver_position ASC) tmp 
                            WHERE tmp.username = game.username)
                        ");
}
?>