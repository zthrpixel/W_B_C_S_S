<?php
// Loads the logged-in user's children into $children.
// Include this file from a page that has already passed auth_check.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    exit();
}

require_once __DIR__ . "/../config/database.php";

$childrenStmt = $pdo->prepare(
    "SELECT child_id, first_name, last_name, date_of_birth, gender,
            development_domain, notes
     FROM children
     WHERE user_id = :user_id
     ORDER BY first_name, last_name"
);
$childrenStmt->execute([":user_id" => (int) $_SESSION["user_id"]]);
$children = $childrenStmt->fetchAll();
