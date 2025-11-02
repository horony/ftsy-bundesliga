<?php
session_start();
// Destroying all sessions
if(session_destroy()){
    // Redirect to home
    header("Location: login.php");
}
?>