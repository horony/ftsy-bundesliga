<?php
session_start();
require('/home/www/secrets/mysql_db_connection.php');		
mysqli_query( $con, " UPDATE xa7580_db1.users SET last_login = NOW() WHERE username = '".$_SESSION["username"]."' " );

if(!isset($_SESSION["username"])){
header("Location: /home/www/html/login.php");
exit(); }
?>