<?php
session_start(); // Start the session

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the homepage or login page
header("Location: index.php"); // Change "index.php" to "login.php" if you want to redirect to the login page
exit();
?>
