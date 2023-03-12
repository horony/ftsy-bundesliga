<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include("../../secrets/mysql_db_connection.php");
$user_status = mysqli_query($con, "SELECT fantasy_players_go FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> fantasy_players_go;

echo "data: {$user_status}\n\n";

flush();
?>