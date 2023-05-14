<?php session_start();?>
<!DOCTYPE html>

<html>

<head>
    <title>Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=0.86, maximum-scale=1, minimum-scale=0.86, user-scalable=no, minimal-ui">
    <meta name="robots" content="noindex">
    
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/login.css">

</head>

<body>
<?php
    require('../secrets/mysql_db_connection.php');
    // If form submitted, insert values into the database.
    if (isset($_POST['username'])){

        // removes backslashes
	    $username = stripslashes($_REQUEST['username']);

        //escapes special characters in a string
        $username = utf8_decode($username);
	    $username = mysqli_real_escape_string($con,$username);

	    $password = stripslashes($_REQUEST['password']);
	    $password = mysqli_real_escape_string($con,$password);

	    //Checking is user existing in the database or not
        $query = "SELECT * FROM xa7580_db1.users usr WHERE BINARY usr.username='$username' and active_account_flg = 1 and BINARY usr.password = '".md5($password)."'";
	    
        $result = mysqli_query($con,$query) or die(mysql_error());
	    $rows = mysqli_num_rows($result);
            if($rows==1){
	           $_SESSION['username'] = $username;

                while($row = $result->fetch_assoc()) {
                    $_SESSION['user_id'] = $row["id"];
                    $_SESSION['user_teamname'] = $row["teamname"];
                    $_SESSION['league_id'] = $row["league_id"];
                    $_SESSION['user_role'] = $row["user_role"];
                }
                // Redirect user to index.php
                $page = '../index.php';
                echo '<script type="text/javascript">';
                echo 'window.location.href="'.$page.'";';
                echo '</script>';
             } else {
	            echo "<div class='form'>
                <h3>Username/password is incorrect.</h3>
                <br/>Click here to <a href='../index.php'>Login</a></div>";
	         }
    }else{
        ?>
        <div class="main">
	        <p class="sign" align="center">Anmeldung</p>
	        <form action="" class="form1" method="post" name="login">
	        
	        <input class="un " type="text" name="username" algin="center" placeholder="Username" required />

	        <input class="pass" type="password" name="password" align="center" placeholder="Passwort" required />

	        <input class="submit" align="center" name="submit" type="submit" value="Login" />
	        </form>

         	<p class="forgot" align="center"><a href="recover_pw.php">Passwort vergessen?</a> | <a href="registration.php">Kein Account?</a></p>

        </div>
     <?php } ?>
</body>
</html>



     
