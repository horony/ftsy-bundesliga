<?php 
include("auth.php");
include("../secrets/mysql_db_connection.php");
$user_id = $_SESSION['user_id'];    

$spieltag = mysqli_query($con, "SELECT spieltag FROM xa7580_db1.parameter") -> fetch_object() -> spieltag;

$match = mysqli_query($con, "
    SELECT 
        ftsy_match_id 
    FROM xa7580_db1.ftsy_schedule 
    WHERE 
        buli_round_name = '".$spieltag."' 
        AND season_id = (SELECT season_id FROM parameter) and (ftsy_home_id = '".$user_id."' OR ftsy_away_id = '".$user_id."')
    ") -> fetch_object() -> ftsy_match_id;

$newURL = "https://fantasy-bundesliga.de/html/view_match.php?ID=".$match;

header('Location: '.$newURL);
?>
