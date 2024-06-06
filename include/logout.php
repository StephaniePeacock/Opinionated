<?php

require_once('connect.php'); // Connect to the db.

$_SESSION = array();

// Destroy the session
session_destroy();
setcookie('cookie_name', '', time() - 3600, '/');
// Redirect to the login page
header("Location: ../login.html");
exit();
?>