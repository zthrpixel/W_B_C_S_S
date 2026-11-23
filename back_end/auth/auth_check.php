<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: /W_B_C_S_S/front_end/index.php");
    exit();
}

function require_role(array $allowedRoles): void {
    if (!in_array($_SESSION["role"] ?? "", $allowedRoles, true)) {
        http_response_code(403);
        exit("Access denied. You do not have permission to view this page.");
    }
}
