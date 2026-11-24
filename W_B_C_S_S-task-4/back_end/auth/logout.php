<?php
session_start();

// Remove all session variables
$_SESSION = [];

// Destroy the current session
session_destroy();

// Return to the login page
header("Location: ../../front_end/index.php?logout=success");
exit();
