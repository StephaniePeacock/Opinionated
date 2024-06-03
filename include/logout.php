<?php

require_once('connect.php'); // Connect to the db.

$_SESSION = array();

// Destroy the session
session_destroy();

// Redirect to the login page
header("Location: ../login.html");
exit();
?>