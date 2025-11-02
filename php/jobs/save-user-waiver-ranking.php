<?php
include("../auth.php");
include("../../secrets/mysql_db_connection.php");

// Get old ranking from MySQL DB and save it into an array
$fetch_ids = mysqli_query($con, "SELECT * FROM xa7580_db1.waiver WHERE owner = '".$_SESSION['user_id']."' ORDER BY prio ASC"); 
$ids_array = array();	

while($row = $fetch_ids->fetch_assoc()){
    $ids_array[] = $row['ID']; 
}

// Get new ranking from the JS POST-Request and save it into a list
$list = $_POST['list']; 
$output = array($list);	
$list = parse_str($list, $output);

// Match the new and old rankings
$counter = 1;
$counter_max = max($output['item']);
$new_array = array();

while ($counter <= $counter_max){
    $new_array[] = array_search($counter, $output['item'])+1;
    $counter++;
}

$waiver_counter = 0;
$waiver_counter_max = $counter_max-1;

// Update MySQL DB with the updated ranking
while ($waiver_counter <= $waiver_counter_max){ 
    mysqli_query($con, "
        UPDATE xa7580_db1.waiver 
        SET prio = '".$new_array[$waiver_counter]."' 
        WHERE 
            owner = '".$_SESSION['user_id']."' 
            AND ID = '".$ids_array[$waiver_counter]."'
        ");
    $waiver_counter++;
}

?>