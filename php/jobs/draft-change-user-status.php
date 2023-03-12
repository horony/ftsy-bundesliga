<?php

include '../auth.php';

$username_to_check = $_POST['ready_player']; 
$username_to_check = substr($username_to_check, 5);

if (intval($username_to_check) == $_SESSION['user_id']){

  include("../../secrets/mysql_db_connection.php");

  $result = mysqli_query($con, "SELECT fantasy_players_go FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> fantasy_players_go;
  $json_result = json_decode($result, true);
  
  $check = 0;

  // Check if users id is in json and then set it to 1
  foreach ($json_result as &$value) {
    if ($username_to_check == $value){
      $check = 1;
    }
  }

  if ($check == 0){
    array_push($json_result, $username_to_check);
    var_dump($json_result);
    // Save in database
    mysqli_query($con, "UPDATE xa7580_db1.draft_meta SET fantasy_players_go = '" . json_encode($json_result) . "' WHERE league_id = 1 ");

    // Check if all users are ready and draft should be started
    $draft_meta = mysqli_query($con, "SELECT * FROM draft_meta WHERE league_id = 1 ") -> fetch_assoc();
    $cnt_ready_players = count($json_result);
    
    // Start draft
    if ( $cnt_ready_players == 10 and $draft_meta['draft_status'] == 'open'){
      $seconds_to_add = mysqli_query($con, "  SELECT seconds_first_picks as seconds from draft_meta where league_id = 1 ") -> fetch_object() -> seconds;

      mysqli_query($con, "
        UPDATE  xa7580_db1.draft_meta 
        SET     draft_status = 'running'
                , start_ts = sysdate()
                , expire_ts = DATE_ADD(sysdate(), INTERVAL ('".$seconds_to_add."') SECOND )
        WHERE league_id = 1 
        ");
    }

  } elseif ($check == 1) {
    $json_result = \array_diff($json_result, [$username_to_check]);
    $a = json_encode($json_result);
    mysqli_query($con, "UPDATE xa7580_db1.draft_meta SET fantasy_players_go = '" . $a . "' WHERE league_id = 1 ");
  }
}
?>