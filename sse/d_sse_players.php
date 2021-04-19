<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

include '../db.php';		

// Server Side Event: Markiere ob User ready sind um den Draft zu starten

$result = mysqli_query($con, "SELECT fantasy_players_go FROM xa7580_db1.draft_meta WHERE league_id = 1 ") -> fetch_object() -> fantasy_players_go;
$str = $result;

echo "data: {$str}\n\n";

flush();
?>