<?php

// Start the session if one hasn't been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check whether a user is logged in
if (!isset($_SESSION["user_id"])) {

    // Return the user to the login page using a relative path
    header("Location: index.php");
    exit();
}

// Check whether the logged-in user has an allowed role
function require_role(array $allowedRoles): void {

    if (!in_array($_SESSION["role"] ?? "", $allowedRoles, true)) {
        http_response_code(403);
        exit("Access denied. You do not have permission to view this page.");
    }
}

?>
