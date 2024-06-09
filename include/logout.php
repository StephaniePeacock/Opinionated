<?php

require_once('connect.php'); // Connect to the db.

$_SESSION = array();

// Destroy the session
session_destroy();
setcookie('user_data', '', time() - 3600, '/');
// Redirect to the login page
header("Location: ../index.html");
exit();
?>