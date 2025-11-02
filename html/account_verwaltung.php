<?php
//include auth.php file on all secure pages
require("../php/auth.php");
?>
<!DOCTYPE html>
<html>

<head>
    <title>FANTASY BUNDESLIGA</title> 

    <meta name="robots" content="noindex">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/account_verwaltung.css">
    <link rel="stylesheet" type="text/css" media="screen, projection" href="../css/nav.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.12.4.js"></script>
</head>

<body>
    <!-- Header image -->
    <header>
        <?php require "header.php"; ?>
    </header>
    
    <!-- Navigation -->
    <div id = "hilfscontainer">
        <?php include("navigation.php"); ?>
    </div>

    <!-- Content -->
    <div class='wrapper'>
        <div class='main_section'>
            <div>
                <h2>ACCOUNT VERWALTUNG</h2>
            </div>
            <!-- Logout -->
            <div>
                <a href="logout.php">Ausloggen</a>
            </div>
            <!-- Change password -->
            <div>
                <h5>PASSWORT ÄNDERN</h5>
                    <form class="form1" name="registration" action="" method="post">
                        <input class="pass" align="left" type="password" name="password_old" placeholder="Dein altes Passwort" required />
                        <input class="pass" align="left" type="password" name="password_new1" placeholder="Dein neues Passwort" required />
                        <input class="pass" align="left" type="password" name="password_new2" placeholder="Wiederhole neues Passwort" required />
                        <input class="submit" align="center" type="submit" name="submit" value="Passwort ändern"/>
                    </form>
                    <?php 
                    require('../secrets/mysql_db_connection.php');

                    $password_old = stripslashes($_REQUEST['password_old']);
                    $password_old = mysqli_real_escape_string($con,$password_old);
                    $query = "SELECT * FROM xa7580_db1.users usr WHERE BINARY usr.username= '".$_SESSION['username']."' and BINARY usr.password = '".md5($password_old)."'";
                    $result = mysqli_query($con,$query) or die(mysql_error());
                    $rows = mysqli_num_rows($result);
                
                    if($rows==1){
                        $password_new1 = stripslashes($_REQUEST['password_new1']);
                        $password_new1 = mysqli_real_escape_string($con,$password_new1);
                        $password_new2 = stripslashes($_REQUEST['password_new2']);
                        $password_new2 = mysqli_real_escape_string($con,$password_new2);

                        if ($password_new1 === $password_new2){
                            $query = "UPDATE xa7580_db1.users SET password = '".md5($password_new1)."' WHERE username = '".$_SESSION['username']."'";
                            $result2 = mysqli_query($con,$query) or die(mysql_error());
                        
                            if ($result2){
                                echo "<div class='form'><h3>Passwort geändert.</h3></div>";
                            } else {
                                echo "<div class='form'><h3>Datenbank-Fehler.</h3></div>";
                        }

                        } else {
                            echo "<div class='form'><h3>Fehler: Neue Passwörter sind nicht gleich.</h3></div>";					
                        }
                    } else {
                        echo "<div class='form'><h3>Fehler: Altes Passwort falsch.</h3></div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>