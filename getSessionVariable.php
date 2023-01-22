<?php
    // This script just simply gets variables saved in the sessions, its like cookiees, its saved on the users device but cannot be changed by the user. 
    session_start();
    $session_variable_name = $_GET["name"];
    echo isset($_SESSION[$session_variable_name]) ? $_SESSION[$session_variable_name] : '';
?>
