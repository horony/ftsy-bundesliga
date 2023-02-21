<?php
include("auth.php");
include("../secrets/mysql_db_connection.php");
$output = mysqli_query($con ,"SELECT spieltag FROM xa7580_db1.parameter") -> fetch_object() -> spieltag;
echo $output;
?>
