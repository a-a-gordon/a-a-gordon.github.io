<?php  
    //Alexis Gordon
    //ISTE 341 Project 1 Logout Page
    //10-18-2021

    //Removing session variables
    session_unset();

    //Destroy the session
    session_destroy();

    //Redirecting back to the login page
    header('Location: login.php');

    exit;
?>