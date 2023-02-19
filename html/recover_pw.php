<?php
session_start();
include '../secrets/mysql_db_connection.php';
if(isset($_POST['submit'])) {
    $user_id = stripslashes($_POST['user_id']);
    $user_id = mysqli_real_escape_string($con,$user_id);
    $user_mail = stripslashes($_POST['mail']);
    $user_mail = mysqli_real_escape_string($con,$user_mail);

    $result = mysqli_query($con,"SELECT * FROM xa7580_db1.users where username='" . $_POST['user_id'] . "' AND email='" . $_POST['mail'] . "'");
    $row = mysqli_fetch_assoc($result);

	$fetch_user_id=$row['username'];
	$email_id=$row['email'];

	if($user_id==$fetch_user_id AND $user_mail== $email_id) {

        // generate new password
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array(); 
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
              $n = rand(0, $alphaLength);
              $pass[] = $alphabet[$n];
          }
        $new_password = implode($pass);
        
        // set new password
        mysqli_query($con, "UPDATE xa7580_db1.users SET password = '".md5($new_password)."' WHERE username = '".$fetch_user_id."' ");
        
        // mail password to user
		$to = $email_id;
        $subject = "Fantasy Bundesliga - Passwort Reset";
        $txt = "Dein Passwort wurde zurückgesetzt. Dein neues Passwort lautet: $new_password.";
        $headers = "From: admin@fantasy-bundesliga.de";
        mail($to,$subject,$txt,$headers);
	
    } else {
		echo 'User nicht gefunden';
    }
}
?>
<!DOCTYPE HTML>
<meta name="viewport" content="width=device-width, initial-scale=0.86, maximum-scale=1, minimum-scale=0.86, user-scalable=no, minimal-ui">
<meta name="robots" content="noindex">
<html>
<body>
    <div class="main">
        <p class="sign" align="center">Passwort Wiederherstellung</p>
        <form action='' method='post'>
            <input class="un " type="text" name="user_id" algin="center" placeholder="Username" required />
            <input class="un " type="text" name="mail" algin="center" placeholder="E-Mail" required />
            <input class="submit" align="center" name="submit" type="submit" value="Reset" />
            <p class="forgot" align="center">Das neue Passwort wird an deine E-Mail verschickt!</p>
        </form>
    </div>
</body>
</html>
