<!DOCTYPE html>
<html>
<head>
    <title>Registration</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.86, maximum-scale=1, minimum-scale=0.86, user-scalable=no, minimal-ui">
    <meta name="robots" content="noindex">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/registration.css">
    <link rel="stylesheet" href="../css/style.css" />
</head>
<body>

<?php
    require('../secrets/mysql_db_connection.php');

    // If form submitted, insert values into the database.
    if (isset($_REQUEST['username'])){
        
        $getligapass = $_REQUEST['ligapass'];

        include '../secrets/league_password.php';
        if ($getligapass != $league_pw){ echo "<script type='text/javascript'>alert('Falsches Liga-Passwort!'); window.location = 'registration.php';</script>"; }
            // removes backslashes
	    else { 
            $username = stripslashes($_REQUEST['username']);
            //escapes special characters in a string
        	$username = mysqli_real_escape_string($con,$username); 
            $teamname = stripslashes($_REQUEST['teamname']);
            //escapes special characters in a string
            $teamname = mysqli_real_escape_string($con,$teamname); 
        	$email = stripslashes($_REQUEST['email']);
        	$email = mysqli_real_escape_string($con,$email);
        	$password = stripslashes($_REQUEST['password']);
        	$password = mysqli_real_escape_string($con,$password);
        	$trn_date = date("Y-m-d H:i:s");

            $duplicate_username = mysqli_query($con,"SELECT * FROM xa7580_db1.users WHERE username = '".$username."'");
            $duplicate_mail = mysqli_query($con,"SELECT * FROM xa7580_db1.users WHERE teamname = '".$teamname."'");
            $duplicate_teamname = mysqli_query($con,"SELECT * FROM xa7580_db1.users WHERE email = '".$email."'");

            if (mysqli_num_rows($duplicate_username)>0 or mysqli_num_rows($duplicate_mail)>0 or mysqli_num_rows($duplicate_teamname)>0 ){
                echo "<script type='text/javascript'>alert('Username oder E-Mail oder Teamname schon vergeben!'); window.location = 'registration.php';</script>"; 
            } 

            if (mysqli_num_rows($duplicate_username) == 0 and mysqli_num_rows($duplicate_mail) == 0 and mysqli_num_rows($duplicate_teamname) == 0){
                
                $query = "INSERT INTO xa7580_db1.users (username, teamname, password, email, trn_date) VALUES ('$username', '$teamname', '".md5($password)."', '$email', '$trn_date')";
                $result = mysqli_query($con,$query);

                if($result){

                    $query2 = "INSERT into xa7580_db1.users_gamedata (username, akt_aufstellung, waiver_position, waiver_safe_flg, waiver_ex_flg) VALUES ('$username', '442', '4', '0', '0')";
                    $result2 = mysqli_query($con,$query2);

                    echo $result2;

                    if($result2){
                        echo "<div class='form'><h3>You are registered successfully.</h3><br/>Click here to <a href='login.php'>Login</a></div>";
                    } 
                }
            } 

        }
    } else {
    ?>
    <div class="main">
    <p class="sign" align="center">Neuer Account</p>
    <form class="form1" name="registration" action="" method="post">
        <input class="un " align="center" type="text" id="username" name="username" placeholder="Username" required />
        <input class="un " align="center" type="text" name="teamname" placeholder="Teamname" required />
        <input class="un " align="center" type="email" name="email" placeholder="Email" required />
        <input class="pass" align="center" type="password" name="password" placeholder="Passwort" required />
        <input class="pass" align="center" type="password" name="ligapass" placeholder="Liga-Passwort" required/>
        <input class="submit" align="center" type="submit" name="submit" value="Register"/>
    </form>
    </div>
<?php } ?>
</body>
</html>
