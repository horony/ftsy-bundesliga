<?php
include("../auth.php");
include("../../secrets/mysql_db_connection.php");
$id = $_GET['id'];

$sql = "DELETE FROM xa7580_db1.waiver WHERE ID = '".$id."' AND owner = '".$_SESSION['user_id']."'"; 

if (mysqli_query($con, $sql)) {
  mysqli_close($con);
  header('Location: ../../html/waiver_delete.php');
  exit;
} else {
  echo "Error deleting record";
}
?>