<?php
include '../auth.php';

// Die User makieren ob sie bereit sind

$username_to_check = $_POST['ready_player']; 
$username_to_check = substr($username_to_check, 5);

if (intval($username_to_check) == $_SESSION['user_id']){

    var_dump($username_to_check);
    include '../db.php';
    
    $result = mysqli_query($con, "SELECT fantasy_players_go FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> fantasy_players_go;

    $json_result = json_decode($result, true);
    $check = 0;

    // Prüfe ob die User ID schon im JSON vorhanden ist. Wenn sie gefunden wird setze check = 1
    foreach ($json_result as &$value) {
        if ($username_to_check == $value){
        	$check = 1;
        }
    }

    // Mache den User grün, weil noch nicht in JSON
    if ($check == 0){
    	array_push($json_result, $username_to_check);
    	var_dump($json_result);
    	mysqli_query($con, "UPDATE xa7580_db1.draft_meta SET fantasy_players_go = '" . json_encode($json_result) . "' WHERE league_id = 1 ");

        // Prüfe ob der Draft gestartet werden soll
        $draft_meta = mysqli_query($con, "SELECT * FROM draft_meta WHERE league_id = 1 ") -> fetch_assoc();
        $cnt_ready_players = count($json_result);

        if ( $cnt_ready_players == 10 and $draft_meta['draft_status'] == 'open'){

            $seconds_to_add = mysqli_query($con, "  SELECT seconds_first_picks as seconds from draft_meta where league_id = 1 ") -> fetch_object() -> seconds;

            mysqli_query($con, "UPDATE  xa7580_db1.draft_meta 
                                SET     draft_status = 'running'
                                        , start_ts = sysdate()
                                        , expire_ts = DATE_ADD(sysdate(), INTERVAL ('".$seconds_to_add."') SECOND )
                                WHERE league_id = 1 ");
        }

    // Entferne den User aus dem JSON
    } elseif ($check == 1) {
        var_dump($json_result);
        $json_result = \array_diff($json_result, [$username_to_check]);
        $a = json_encode($json_result);

        mysqli_query($con, "UPDATE xa7580_db1.draft_meta SET fantasy_players_go = '" . $a . "' WHERE league_id = 1 ")
    }
}

?>